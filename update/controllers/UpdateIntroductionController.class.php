<?php
/*##################################################
 *                         UpdateIntroductionController.class.php
 *                            -------------------
 *   begin                : March 11, 2012
 *   copyright            : (C) 2012 Kevin MASSY
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

class UpdateIntroductionController extends UpdateController
{
	public function execute(HTTPRequestCustom $request)
	{
		parent::load_lang($request);
		$view = new FileTemplate('update/introduction.tpl');
		$this->add_navigation($view);
		return $this->create_response($view);
	}

	/**
	 * @param Template $view
	 * @return UpdateDisplayResponse
	 */
	private function create_response(Template $view)
	{
		$step_title = $this->lang['step.introduction.title'];
		$response = new UpdateDisplayResponse(0, $step_title, $view);
		return $response;
	}

	private function add_navigation(Template $view)
	{
		$form = new HTMLForm('preambleForm', UpdateUrlBuilder::server_configuration()->rel(), false);
		
		$action_fieldset = new FormFieldsetSubmit('actions');
		$next = new FormButtonSubmitCssImg($this->lang['step.next'], 'fa fa-arrow-right', 'introduction');
		$action_fieldset->add_element($next);
		$form->add_fieldset($action_fieldset);
		$view->put_all(array(
			'C_PUT_UNDER_MAINTENANCE' => !MaintenanceConfig::load()->is_under_maintenance(),
			'SERVER_FORM' => $form->display()
		));
	}
}
?>