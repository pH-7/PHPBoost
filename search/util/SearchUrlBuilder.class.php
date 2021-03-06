<?php
/*##################################################
 *   SearchUrlBuilder.class.php
 *   ---------------------------
 *   begin                :  2017/02/24
 *   copyright            : (C) 2017 Xela
 *   email                : xela@phpboost.com
 *
 *
 *###################################################
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
 *###################################################
 */

class SearchUrlBuilder
{
	private static $dispatcher = '/search';
	
	/**
	 * @return Url
	 */
	public static function documentation()
	{
		return new Url (ModulesManager::get_module('search')->get_configuration()->get_documentation());
	}
}
?>
