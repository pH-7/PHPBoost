<?php
/*##################################################
 *                               FaqAuthorizationsService.class.php
 *                            -------------------
 *   begin                : September 2, 2014
 *   copyright            : (C) 2014 Julien BRISWALTER
 *   email                : j1.seth@phpboost.com
 *
 *
 ###################################################
 *
 * This program is a free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

 /**
 * @author Julien BRISWALTER <j1.seth@phpboost.com>
 */

class FaqAuthorizationsService
{
	public $id_category;
	
	public static function check_authorizations($id_category = Category::ROOT_CATEGORY)
	{
		$instance = new self();
		$instance->id_category = $id_category;
		return $instance;
	}
	
	public function read()
	{
		return $this->is_authorized(Category::READ_AUTHORIZATIONS, Authorizations::AUTH_PARENT_PRIORITY);
	}
	
	public function write()
	{
		return $this->is_authorized(Category::WRITE_AUTHORIZATIONS);
	}
	
	public function contribution()
	{
		return $this->is_authorized(Category::CONTRIBUTION_AUTHORIZATIONS);
	}
	
	public function moderation()
	{
		return $this->is_authorized(Category::MODERATION_AUTHORIZATIONS);
	}
	
	public function manage_categories()
	{
		return $this->is_authorized(Category::CATEGORIES_MANAGEMENT_AUTHORIZATIONS);
	}
	
	private function is_authorized($bit, $mode = Authorizations::AUTH_CHILD_PRIORITY)
	{
		$auth = FaqService::get_categories_manager()->get_heritated_authorizations($this->id_category, $bit, $mode);
		return AppContext::get_current_user()->check_auth($auth, $bit);
	}
}
?>
