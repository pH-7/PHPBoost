<?php
/*##################################################
 *                   FormBuilderDisabledFieldException.class.php
 *                            -------------------
 *   begin                : April 10, 2010
 *   copyright            : (C) 2010 Benoit Sautel
 *   email                : ben.popeye@phpboost.com
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
 * 
 * @author Benoit Sautel <ben.popeye@phpboost.com>
 * @package {@package}
 *
 */
class FormBuilderDisabledFieldException extends FormBuilderException
{
	private $value;
	
	public function __construct($field, $value)
	{
		parent::__construct('You cannot retrieve the value of the field ' . $field . ' which is disabled.');
		$this->value = $value;
	}
	
	public function get_value()
	{
		return $this->value;
	}
}
?>