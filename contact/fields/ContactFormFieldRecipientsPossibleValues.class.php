<?php
/*##################################################
 *		               ContactFormFieldRecipientsPossibleValues.class.php
 *                            -------------------
 *   begin                : September 29, 2013
 *   copyright            : (C) 2013 Julien BRISWALTER
 *   email                : j1.seth@phpboost.com
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
 * @author Julien BRISWALTER <j1.seth@phpboost.com>
 */

class ContactFormFieldRecipientsPossibleValues extends AbstractFormField
{
	private $max_input = 30;
	
	public function __construct($id, $label = '', array $value = array(), array $field_options = array(), array $constraints = array())
	{
		parent::__construct($id, $label, $value, $field_options, $constraints);
	}
	
	function display()
	{
		$template = $this->get_template_to_use();
		$lang = LangLoader::get('admin-user-common');
		
		$tpl = new FileTemplate('contact/ContactFormFieldRecipientsPossibleValues.tpl');
		$tpl->add_lang($lang);
		
		$this->assign_common_template_variables($template);
		
		$i = 0;
		foreach ($this->get_value() as $name => $options)
		{
			if (!empty($options))
			{
				$tpl->assign_block_vars('fieldelements', array(
					'C_DELETABLE' => $i > 0,
					'ID' => $i,
					'NAME' => stripslashes($options['title']),
					'IS_DEFAULT' => (int)$options['is_default'],
					'EMAIL' => $i > 0 ? stripslashes($options['email']) : implode(',', MailServiceConfig::load()->get_administrators_mails())
				));
				$i++;
			}
		}
		
		$tpl->put_all(array(
			'NAME' => $this->get_html_id(),
			'HTML_ID' => $this->get_html_id(),
			'C_DISABLED' => $this->is_disabled(),
			'MAX_INPUT' => $this->max_input,
		 	'NBR_FIELDS' => $i,
		));
		
		$template->assign_block_vars('fieldelements', array(
			'ELEMENT' => $tpl->render()
		));
		
		return $template;
	}
	
	public function retrieve_value()
	{
		$request = AppContext::get_request();
		
		$config = ContactConfig::load();
		$fields = $config->get_fields();
		$recipients_field_id = $config->get_field_id_by_name('f_recipients');
		$recipients_field = new ContactField();
		$recipients_field->set_properties($fields[$recipients_field_id]);
		$recipients = $recipients_field->get_possible_values();
		$nb_recipients = count($recipients);
		$recipients_keys = array_keys($recipients);
		
		$values = array();
		for ($i = 0; $i <= $this->max_input; $i++)
		{
			$field_name = 'field_name_' . $this->get_html_id() . '_' . $i;
			if ($request->has_postparameter($field_name))
			{
				$field_is_default = 'field_is_default_' . $this->get_html_id() . '_' . $i;
				$field_title = 'field_name_' . $this->get_html_id() . '_' . $i;
				$field_email = 'field_email_' . $this->get_html_id() . '_' . $i;
				
				$email = $i > 0 ? $request->get_poststring($field_email) : true;
				
				if ($request->get_poststring($field_title) && $email)
				{
					$id = $i < $nb_recipients ? $recipients_keys[$i] : preg_replace('/\s+/', '', $request->get_poststring($field_name));
					$values[$id] = array(
						'is_default' => $request->get_postint($field_is_default, 0),
						'title' => addslashes($request->get_poststring($field_title)),
						'email' => $request->get_poststring($field_email, '')
					);
				}
			}
		}
		$this->set_value($values);
	}
	
	protected function compute_options(array &$field_options)
	{
		foreach($field_options as $attribute => $value)
		{
			$attribute = strtolower($attribute);
			switch ($attribute)
			{
				 case 'max_input':
					$this->max_input = $value;
					unset($field_options['max_input']);
					break;
			}
		}
		parent::compute_options($field_options);
	}
	
	protected function get_default_template()
	{
		return new FileTemplate('framework/builder/form/FormField.tpl');
	}
}
?>
