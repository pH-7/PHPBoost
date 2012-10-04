<?php
/*##################################################
 *                     CaptchaService.class.php
 *                            -------------------
 *   begin                : September 04, 2012
 *   copyright            : (C) 2012 Kevin MASSY
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
 * @package {@package}
 * @author Kevin MASSY <kevin.massy@phpboost.com>
 */
class CaptchaService
{
	private $default_factory;

	public function get_default_factory()
	{
		if ($this->default_factory === null)
		{
			//TODO captcha in configuration
			$captcha = '';
			$this->default_factory = $this->create_factory($captcha);
		}
		return $this->default_factory;
	}

	public function create_factory($identifier = '')
	{
		$captcha = $this->get_existing_captcha($identifier);
		return CaptchaProvidersService::create_factory($captcha);
	}

	public function is_available()
	{
		return $this->get_default_factory()->is_available();
	}

	public function is_valid()
	{
		return $this->get_default_factory()->is_valid();
	}

	public function display()
	{
		return $this->get_default_factory()->display();
	}

    private function get_existing_captcha($captcha)
    {
        if (in_array($captcha, self::get_captchas_identifier()))
        {
            return $captcha;
        }
        else
        {
        	// TODO default captcha => PHPBoost
            return '';
        }
    }
    
    public function get_captchas_identifier()
    {
    	return array_keys(CaptchaProvidersService::get_captchas());
    }
    
    public function get_available_captchas()
    {
    	$available_captchas = array();
    	foreach (CaptchaProvidersService::get_captchas() as $id => $provider)
    	{
    		$available_captchas[$id] = $provider->get_name();
    	}
    	return $available_captchas;
    }
}
?>