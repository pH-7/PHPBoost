<?php
/*##################################################
 *                        TwitterSocialNetwork.class.php
 *                            -------------------
 *   begin                : April 10, 2018
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

class TwitterSocialNetwork extends AbstractSocialNetwork
{
	const SOCIAL_NETWORK_ID = 'twitter';
	
	public function get_name()
	{
		return 'Twitter';
	}
	
	public function get_content_sharing_url()
	{
		return 'https://twitter.com/share?url=' . (rawurlencode(HOST . REWRITED_SCRIPT)) . (defined('TITLE') ? '&text=' . rawurlencode(TITLE) : '');
	}
	
	public function get_external_authentication()
	{
		return new TwitterExternalAuthentication();
	}
	
	public function get_identifiers_creation_url()
	{
		return 'http://twitter.com/apps';
	}
}
?>
