<?php
/*##################################################
 *                        BloggerSocialNetwork.class.php
 *                            -------------------
 *   begin                : April 19, 2018
 *   copyright            : (C) 2018 Julien BRISWALTER
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

class BloggerSocialNetwork extends AbstractSocialNetwork
{
	const SOCIAL_NETWORK_ID = 'blogger';
	
	public function get_name()
	{
		return 'Blogger';
	}
	
	public function get_icon_name()
	{
		return self::SOCIAL_NETWORK_ID . '-b';
	}
	
	public function get_content_sharing_url()
	{
		return 'https://www.blogger.com/blog-this.g?u=' . (rawurlencode(HOST . REWRITED_SCRIPT)) . (defined('TITLE') ? '&n=' . rawurlencode(TITLE) : '');
	}
}
?>
