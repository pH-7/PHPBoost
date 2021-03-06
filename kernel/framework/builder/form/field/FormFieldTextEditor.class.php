<?php
/*##################################################
 *                             FormFieldTextEditor.class.php
 *                            -------------------
 *   begin                : April 28, 2009
 *   copyright            : (C) 2009 Viarre Régis
 *   email                : crowkait@phpboost.com
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
 * @author Régis Viarre <crowkait@phpboost.com>
 * @desc This class manage single-line text fields.
 * @package {@package}
 */
class FormFieldTextEditor extends AbstractFormField
{
	protected $type = 'text';
	protected $size = 30;
	protected $maxlength = 255;

	/**
	 * @desc Constructs a FormFieldTextEditor.
	 * It has these options in addition to the AbstractFormField ones:
	 * <ul>
	 * 	<li>size: The size (width) of the HTML field</li>
	 * 	<li>maxlength: The maximum length for the field</li>
	 * </ul>
	 * @param string $id Field identifier
	 * @param string $label Field label
	 * @param string $value Default value
	 * @param string[] $field_options Map containing the options
	 * @param FormFieldConstraint[] $constraints The constraints checked during the validation
	 */
	public function __construct($id, $label, $value, array $field_options = array(), array $constraints = array())
	{
		parent::__construct($id, $label, $value, $field_options, $constraints);
		$this->set_css_form_field_class('form-field-text');
	}

	/**
	 * @return string The html code for the input.
	 */
	public function display()
	{
		$template = $this->get_template_to_use();

		$field = new FileTemplate('framework/builder/form/fieldelements/FormFieldTextEditor.tpl');

		$field->put_all(array(
			'SIZE' => $this->size,
			'MAX_LENGTH' => $this->maxlength,
			'NAME' => $this->get_html_id(),
			'ID' => $this->get_id(),
			'HTML_ID' => $this->get_html_id(),
			'TYPE' => $this->type,
			'VALUE' => $this->get_value(),
			'CLASS' => $this->get_css_class(),
			'C_DISABLED' => $this->is_disabled(),
			'C_READONLY' => $this->is_readonly(),
			'C_PATTERN' => $this->has_pattern(),
			'PATTERN' => $this->pattern,
			'C_PLACEHOLDER' => $this->has_placeholder(),
			'PLACEHOLDER' => $this->placeholder
		));

		$this->assign_common_template_variables($template);

		$template->assign_block_vars('fieldelements', array(
			'ELEMENT' => $field->render()
		));

		return $template;
	}

	protected function compute_options(array &$field_options)
	{
		foreach ($field_options as $attribute => $value)
		{
			$attribute = TextHelper::strtolower($attribute);
			switch ($attribute)
			{
				case 'size':
					$this->size = $value;
					unset($field_options['size']);
					break;
				case 'maxlength':
					$this->maxlength = $value;
					unset($field_options['maxlength']);
					break;
			}
		}
		parent::compute_options($field_options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value()
	{
		return TextHelper::substr($this->value, 0, $this->maxlength);
	}

	protected function get_default_template()
	{
		return new FileTemplate('framework/builder/form/FormField.tpl');
	}
}
?>