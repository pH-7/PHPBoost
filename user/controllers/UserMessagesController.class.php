<?php
/*##################################################
 *                       UserMessagesController.class.php
 *                            -------------------
 *   begin                : October 07, 2011
 *   copyright            : (C) 2011 Kevin MASSY
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

class UserMessagesController extends AbstractController
{
	private $lang;
	private $tpl;
	private $user;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();

		$user_id = $request->get_getint('user_id', 0);

		if (empty($user_id))
		{
			AppContext::get_response()->redirect(UserUrlBuilder::home());
		}
		else if ($user_id != $this->user->get_id())
		{
			if (UserService::user_exists('WHERE user_id = :id', array('id' => $user_id)))
				$this->user = UserService::get_user($user_id);
			else
			{
				$error_controller = PHPBoostErrors::unexisting_element();
				DispatchManager::redirect($error_controller);
			}
		}
		
		$this->build_form();
		return $this->build_response($this->tpl);
	}

	private function init()
	{
		$this->user = AppContext::get_current_user();
		$this->tpl = new FileTemplate('user/UserMessagesController.tpl');
		$this->lang = LangLoader::get('user-common');
		$this->tpl->add_lang($this->lang);
	}
	
	private function build_form()
	{
		$modules = AppContext::get_extension_provider_service()->get_extension_point(UserExtensionPoint::EXTENSION_POINT);
		foreach ($modules as $module)
		{
			$img = $module->get_messages_list_link_img();
			$this->tpl->assign_block_vars('available_modules_msg', array(
				'NAME_USER_MSG'   => $module->get_messages_list_link_name(),
				'IMG_USER_MSG'    => $img,
				'C_IMG_USER_MSG'  => !empty($img),
				'U_LINK_USER_MSG' => $module->get_messages_list_url($this->user->get_id())
			));
		}
		
		$this->tpl->put_all(array(
			'L_MESSAGES' => $this->lang['messages'],
		));
	}

	private function build_response(View $view)
	{
		$title = $this->lang['messages'];
		$response = new SiteDisplayResponse($view);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($title);
		$graphical_environment->get_seo_meta_data()->set_description(StringVars::replace_vars($this->lang['seo.user.messages'], array('name' => $this->user->get_display_name())));
		$graphical_environment->get_seo_meta_data()->set_canonical_url(UserUrlBuilder::messages($this->user->get_id()));
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['user'], UserUrlBuilder::home()->rel());
		$breadcrumb->add($title, UserUrlBuilder::messages($this->user->get_id())->rel());
		
		return $response;
	}
	
	public function get_right_controller_regarding_authorizations()
	{
		if (!AppContext::get_current_user()->check_auth(UserAccountsConfig::load()->get_auth_read_members(), UserAccountsConfig::AUTH_READ_MEMBERS_BIT))
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
		return $this;
	}
}
?>