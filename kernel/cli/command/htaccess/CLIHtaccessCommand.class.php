<?php
/*##################################################
 *                          CLIHtaccessCommand.class.php
 *                            -------------------
 *   begin                : October 11, 2011
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

class CLIHtaccessCommand extends CLIMultipleGoalsCommand
{
	private static $name = 'htaccess';
	private static $goals = array('content' => 'CLIHtaccessContentCommand', 'rewriting' => 'CLIHtaccessRewritingCommand');

	public function __construct()
	{
		parent::__construct(self::$name, self::$goals);
	}
	
	public function short_description()
	{
		return 'manages the htaccess file';
	}
}
?>