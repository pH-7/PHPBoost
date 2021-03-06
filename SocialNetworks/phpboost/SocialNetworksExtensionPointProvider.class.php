<?php
/*##################################################
 *                    SocialNetworksExtensionPointProvider.class.php
 *                            -------------------
 *   begin                : January 08, 2018
 *   copyright            : (C) 2018 Kévin MASSY
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
class SocialNetworksExtensionPointProvider extends ExtensionPointProvider
{
	public function __construct()
	{
		parent::__construct('SocialNetworks');
	}
	
	public function content_sharing_actions_menu_links()
	{
		$social_networks_list = new SocialNetworksList();
		return $social_networks_list->get_sharing_links_list();
	}
	
	public function css_files()
	{
		$module_css_files = new ModuleCssFiles();
		$module_css_files->adding_always_displayed_file('SocialNetworks.css');
		return $module_css_files;
	}
	
	public function external_authentications()
	{
		$social_networks_list = new SocialNetworksList();
		return new ExternalAuthenticationsExtensionPoint($social_networks_list->get_external_authentications_list());
	}
	
	public function menus()
	{
		return new ModuleMenus(array(new SocialNetworksModuleMiniMenu()));
	}
	
	public function url_mappings()
	{
		return new UrlMappings(array(new DispatcherUrlMapping('/SocialNetworks/index.php')));
	}
}
?>