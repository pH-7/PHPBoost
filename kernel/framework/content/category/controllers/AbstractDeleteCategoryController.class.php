<?php
/*##################################################
 *                             AbstractDeleteCategoryController.class.php
 *                            -------------------
 *   begin                : February 06, 2013
 *   copyright            : (C) 2013 Kévin MASSY
 *   email                : kevin.massy@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

/**
 * @package {@package}
 * @author Kévin MASSY
 * @desc
 */
abstract class AbstractDeleteCategoryController extends ModuleController
{
	/**
	 * @var HTMLForm
	 */
	protected $form;
	/**
	 * @var FormButtonSubmit
	 */
	private $submit_button;
	
	private $lang;

	public function execute(HTTPRequestCustom $request)
	{
		$this->check_authorizations();
		
		$this->init();
		
		try {
			$category = $this->get_category();
		} catch (CategoryNotFoundException $e) {
			$controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($controller);
		}

		$children = $this->get_category_children($category);
		if (empty($children) && !$this->get_category_items_exists($category))
		{
			$this->get_categories_manager()->delete($this->get_category()->get_id());
			$this->clear_cache();
			AppContext::get_response()->redirect($this->get_categories_management_url(), StringVars::replace_vars($this->get_success_message(), array('name' => $this->get_category()->get_name())));
		}
	
		$this->build_form();
		$tpl = new StringTemplate('# INCLUDE FORM #');
		$tpl->add_lang($this->lang);
		
		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			if ($this->form->get_value('delete_category_and_content'))
			{
				$this->get_categories_manager()->delete($this->get_category()->get_id());
				foreach ($children as $id => $category)
				{
					$this->get_categories_manager()->delete($id);
				}
			}
			else
			{
				$id_parent = $this->form->get_value('move_in_other_cat')->get_raw_value();
				$this->get_categories_manager()->move_items_into_another($category, $id_parent);
				
				$children = $this->get_category_children($category, false);
				foreach ($children as $id => $category)
				{
					$this->get_categories_manager()->move_into_another($category, $id_parent);
				}
				
				$this->get_categories_manager()->delete($this->get_category()->get_id());
				$categories_cache = $this->get_categories_manager()->get_categories_cache()->get_class();
				$categories_cache::invalidate();
			}
			$this->clear_cache();
			AppContext::get_response()->redirect($this->get_categories_management_url(), StringVars::replace_vars($this->get_success_message(), array('name' => $this->get_category()->get_name())));
		}
		
		$tpl->put('FORM', $this->form->display());
		
		return $this->generate_response($tpl);
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('categories-common');
	}
	
	private function build_form()
	{
		$form = new HTMLForm(__CLASS__);
		
		$fieldset = new FormFieldsetHTMLHeading('delete_category', $this->get_title());
		$fieldset->set_description($this->get_description());
		$form->add_fieldset($fieldset);
		
		$fieldset->add_field(new FormFieldCheckbox('delete_category_and_content', $this->lang['delete.category_and_content'], FormFieldCheckbox::UNCHECKED, array('events' => array('click' => '
		if (HTMLForms.getField("delete_category_and_content").getValue()) {
			HTMLForms.getField("move_in_other_cat").disable();
		} else { 
			HTMLForms.getField("move_in_other_cat").enable();
		}')
		)));
		
		
		$options = new SearchCategoryChildrensOptions();
		$options->add_category_in_excluded_categories($this->get_category()->get_id());
		$fieldset->add_field($this->get_categories_manager()->get_select_categories_form_field('move_in_other_cat', $this->lang['delete.move_in_other_cat'], $this->get_category()->get_id_parent(), $options));
		
		
		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());
		
		$this->form = $form;
	}
	
	private function get_category_children(Category $category, $enable_recursive_exploration = true)
	{
		$options = new SearchCategoryChildrensOptions();
		$options->add_category_in_excluded_categories($category->get_id());
		$options->set_enable_recursive_exploration($enable_recursive_exploration);
		return $this->get_categories_manager()->get_children($category->get_id(), $options);
	}
	
	private function get_category_items_exists(Category $category)
	{
		return PersistenceContext::get_querier()->row_exists(
		$this->get_categories_manager()->get_categories_items_parameters()->get_table_name_contains_items(), 
		'WHERE '.$this->get_categories_manager()->get_categories_items_parameters()->get_field_name_id_category().'=:id_category', 
		array('id_category' => $category->get_id()
		)); 
	}
	
	private function get_category()
	{
		$id_category = $this->get_id_category();
		if (!empty($id_category) && $this->get_categories_manager()->get_categories_cache()->category_exists($id_category))
		{
			return $this->get_categories_manager()->get_categories_cache()->get_category($id_category);
		}
		throw new CategoryNotFoundException($id_category);
	}
	
	/**
	 * @return string Page title
	 */
	protected function get_title()
	{
		return $this->lang['category.delete'];
	}
	
	/**
	 * @return string delete description
	 */
	protected function get_description()
	{
		return $this->lang['delete.description'];
	}
	
	/**
	 * @return string delete success message
	 */
	protected function get_success_message()
	{
		return $this->lang['category.message.success.delete'];
	}
	
	/**
	 * @return Clear elements cache if any
	 */
	protected function clear_cache()
	{
		return true;
	}
	
	/**
	 * @param View $view
	 * @return Response
	 */
	protected function generate_response(View $view)
	{
		$response = new SiteDisplayResponse($view);

		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->get_title(), $this->get_module_home_page_title());
		$graphical_environment->get_seo_meta_data()->set_canonical_url($this->get_delete_category_url($this->get_category()));
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->get_module_home_page_title(), $this->get_module_home_page_url());
		
		$breadcrumb->add($this->get_title(), $this->get_delete_category_url($this->get_category()));
		
		return $response;
	}
	
	/**
	 * @return string id of the category to edit / delete
	 */
	abstract protected function get_id_category();
	
	/**
	 * @return CategoriesManager
	 */
	abstract protected function get_categories_manager();
	
	/**
	 * @return Url
	 */
	abstract protected function get_categories_management_url();
	
	/**
	 * @param int $category Category
	 * @return Url
	 */
	abstract protected function get_delete_category_url(Category $category);
	
	/**
	 * @return Url
	 */
	abstract protected function get_module_home_page_url();
	
	/**
	 * @return string module home page title
	 */
	abstract protected function get_module_home_page_title();
	
	/**
	 * @return boolean Authorization to manage categories
	 */
	abstract protected function check_authorizations();
}
?>