<?php
/*##################################################
 *                          UserUrlBuilder.class.php
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

/**
 * @author Kevin MASSY <kevin.massy@phpboost.com>
 */
class UserUrlBuilder
{
	private static $dispatcher = '/user';

	public static function maintain($parameters = '')
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/login/' . $parameters);
	}
	
	public static function forget_password()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/password/lost/');
	}
	
	public static function change_password($key)
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/password/change/' . $key);
	}
	
	public static function home_profile()
	{
		return DispatchManager::get_url(self::$dispatcher, '/profile/home/');
	}
	
	public static function profile($user_id)
	{
		return DispatchManager::get_url(self::$dispatcher, '/profile/' . $user_id);
	}
	
	public static function registration()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/registration/');
	}
	
	public static function confirm_registration($key)
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/registration/confirm/' . $key);
	}
	
	public static function contribution_panel($id = 0)
	{
		$id = !empty($id) ? '?id=' . $id : '';
		return new Url(self::$dispatcher . '/contribution_panel.php' . $id);
	}
	
	public static function moderation_panel($type = '', $user_id = 0)
	{
		$param = !empty($type) ? '?action=' . $type . '&id=' . $user_id : '';
		return new Url(self::$dispatcher . '/moderation_panel.php' . $param);
	}
	
	public static function personnal_message($user_id = 0)
	{
		$param = !empty($user_id) ? url('.php?pm=' . $user_id, '-' . $user_id) : '.php';
		return new Url(self::$dispatcher . '/pm' . $param);
	}
	
	public static function upload_files_panel()
	{
		return new Url(self::$dispatcher . '/upload.php');
	}
	
	public static function upload_popup()
	{
		return new Url(self::$dispatcher . '/upload_popup.php');
	}
	
	public static function edit_profile($user_id = 0, $action = null, $authentication_method = null)
	{
		if (empty($user_id))
		{
			$user_id = AppContext::get_current_user()->get_id();
		}
		$action = $action !== null && $authentication_method !== null ? '?' . $action . '=' . $authentication_method : ($action == 'delete-account' ? '?delete-account=1' : '');
		
		return DispatchManager::get_url(self::$dispatcher, '/profile/'. $user_id .'/edit/' . $action);
	}
	
	public static function home()
	{
		return DispatchManager::get_url(self::$dispatcher, '/');
	}
	
	public static function error_403()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/error/403/');
	}
	
	public static function error_404()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/error/404/');
	}
	
	public static function messages($user_id)
	{
		return DispatchManager::get_url(self::$dispatcher, '/messages/'. $user_id);
	}
	
	public static function comments($module = '', $user_id = null, $page = 1)
	{
		$page = $page !== 1 ? $page : '';
		if (!empty($user_id))
		{
			return DispatchManager::get_url(self::$dispatcher, '/messages/'. $user_id . '/comments/' . (!empty($module) ? $module . '/' : '') . $page);
		}
		return DispatchManager::get_url(self::$dispatcher, '/messages/comments/' . (!empty($module) ? $module . '/' : '')  . $page);
	}
	
	public static function group($id)
	{
		return DispatchManager::get_url(self::$dispatcher, '/groups/' . $id);
	}
	
	public static function connect($authenticate_type = null)
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/login/' . ($authenticate_type !== null ? '?authenticate=' . $authenticate_type : ''));
	}
	
	public static function disconnect()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/login/?disconnect=true&amp;token=' . AppContext::get_session()->get_token());
	}
	
	public static function aboutcookie()
	{
		$dispatch = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '/' : self::$dispatcher;
		return DispatchManager::get_url($dispatch, '/aboutcookie/');
	}
	
	public static function administration()
	{
		return new Url('/admin/');
	}
	
	public static function groups()
	{
		return DispatchManager::get_url(self::$dispatcher, '/groups/');
	}
}
?>