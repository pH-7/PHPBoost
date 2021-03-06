<?php
/*##################################################
 *                           LangLoader.class.php
 *                            -------------------
 *   begin                : October 29, 2009
 *   copyright            : (C) 2009 Loic Rouchon
 *   email                : loic.rouchon@phpboost.com
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
 * @author loic rouchon <loic.rouchon@phpboost.com>
 * @desc
 * @package {@package}
 */
class LangLoader
{
	private static $locale = '';

	/**
	 * @var CacheFactory
	 */
	private static $ram_cache = null;

	/**
	 * @desc sets the language locale
	 * @param string $locale the locale
	 */
	public static function set_locale($locale)
	{
		self::$locale = in_array($locale, self::get_available_langs()) ? $locale : self::get_default_lang();
	}
	
	/**
	 * @desc Returns the current language locale
	 * @return string the current language locale
	 */
	public static function get_locale()
	{
		return self::$locale ? self::$locale : self::get_default_lang();
	}

	public static function get_available_langs()
	{
		$langs_folder = new Folder(PATH_TO_ROOT . '/lang');
		$langs_list = $langs_folder->get_folders();
		
		$available_langs = array();
		foreach ($langs_list as $lang)
		{
			$available_langs[] = $lang->get_name();
		}
		
		return $available_langs;
	}

	public static function get_default_lang()
	{
		$langs = self::get_available_langs();
		return $langs[0];
	}

	/**
	 * @param string $message_id the language message identifier
	 * @param string $filename the language filename
	 * @param string $module the module to look for languages files in
	 * @return string the localized message
	 */
	public static function get_message($message_id, $filename, $module = '')
	{
		$lang = self::get($filename, $module);
		if (!isset($lang[$message_id]))
		{
			$lang = self::get($filename, $module, 'real_lang');
			if (!isset($lang[$message_id]))
				$lang = self::get($filename, $module, 'default');
		}
		return $lang[$message_id];
	}

	/**
	 * @desc Retrieves the language file <code>$filename</code> in
	 * <code>/$module/lang/$locale/$filename.php</code>
	 * If module is empty, the kernel lang folder will be used
	 * @param string $filename the language filename
	 * @param string $module the module to look for languages files in
	 * @param string $forced_file the language filename to return inevitably
	 * @return string[string] the lang array which keys are languages identifiers and values the
	 * translated messages
	 */
	public static function get($filename, $module = '', $forced_file = '')
	{
		$module_name = trim($module, '/');
		return self::get_raw($module_name, $filename, $forced_file);
	}

	private static function get_raw($folder, $filename, $forced_file = '')
	{
		$lang_id = $folder . '/' . $filename;
		$ram_cache = self::get_ram_cache();
		if (!$ram_cache->contains($lang_id))
		{
			self::load($lang_id, $folder, $filename, $forced_file);
		}
		return $ram_cache->get($lang_id);
	}

	private static function load($lang_id, $folder, $filename, $forced_file = '')
	{
		$lang = array();
		include self::get_real_lang_path($folder, $filename, $forced_file);
		if (empty($lang) && !empty($LANG) && is_array($LANG))
		{
			$lang = $LANG;
		}
		self::get_ram_cache()->store($lang_id, $lang);
	}

	/**
	 * @desc returns the real language file path, trying first to load the localized language file
	 * and if it's not possible, use the default locale one.
	 * @param string $folder the folder to look in
	 * @param string $filename the language filename
	 * @param string $forced_file the language filename to return inevitably
	 * @return string the real language file path
	 */
	private static function get_real_lang_path($folder, $filename, $forced_file = '')
	{
		$real_folder = PATH_TO_ROOT . (!empty($folder) ? '/' . $folder : '') . '/lang/';
		$filename_with_extension = '/' . $filename . '.php';

		if (!empty($folder) && empty($forced_file))
		{
			// Module - Langs priority order
			//      /lang/$lang/modules/$module/$file.php
			//      /$module/lang/$lang/$file.php
			$real_lang_file = PATH_TO_ROOT . '/lang/' . self::$locale . '/modules/' . $folder . $filename_with_extension;
			if (file_exists($real_lang_file))
			{
				return $real_lang_file;
			}
		}

		if (empty($forced_file) || $forced_file = 'real_lang')
		{
			$real_lang_file = $real_folder . self::$locale . $filename_with_extension;
			if (file_exists($real_lang_file))
			{
				return $real_lang_file;
			}
		}

		// Get default lang file if nothing else is found
		if (empty($forced_file) || $forced_file = 'default')
		{
			$real_lang_file = $real_folder . self::get_default_lang() . $filename_with_extension;
			if (file_exists($real_lang_file))
			{
				return $real_lang_file;
			}
		}

		throw new LangNotFoundException($folder, $filename);
	}

	/**
	 * @desc clear the lang cache (for unit test only)
	 */
	public static function clear_lang_cache()
	{
		self::$ram_cache = null;
		self::get_ram_cache();
	}

	/**
	 * @return RAMDataStore
	 */
	private static function get_ram_cache()
	{
		if (self::$ram_cache === null)
		{
			self::$ram_cache = new RAMDataStore('lang');
		}
		return self::$ram_cache;
	}
}
?>