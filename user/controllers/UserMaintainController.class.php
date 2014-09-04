<?php
/*##################################################
 *                      UserMaintainController.class.php
 *                            -------------------
 *   begin                : October 07, 2011
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

class UserMaintainController extends AbstractController
{
	private $tpl;
	private $lang;
	private $main_lang;
	private $maintain_config;
	
	private $form;
	private $submit_button;
	
	private $has_error = 0;
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		$this->build_error_message();
		$this->build_form();
		$this->init_vars_template();
		$this->init_delay();
		
		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$username = $this->form->get_value('login');
			$password = $this->form->get_value('password');
			$autoconnect = $this->form->get_value('autoconnect');
			$this->authenticate($username, $password, $autoconnect);
		}
		
		return $this->build_reponse();
	}
	
	private function init()
	{
		$this->request = AppContext::get_request();
		$this->tpl = new FileTemplate('user/UserMaintainController.tpl');
		$this->lang = LangLoader::get('user-common');
		$this->main_lang = LangLoader::get('main');
		$this->tpl->add_lang($this->lang);
		$this->maintain_config = MaintenanceConfig::load();
	}
	
	private function init_vars_template()
	{
		$general_config = GeneralConfig::load();
		
		$theme = ThemesManager::get_theme(get_utheme());
		$customize_interface = $theme->get_customize_interface();
		$header_logo_path = $customize_interface->get_header_logo_path();

		$customization_config = CustomizationConfig::load();
		
		$this->tpl->put_all(array(
			'SITE_NAME' => $general_config->get_site_name(),
			'C_FAVICON' => $customization_config->favicon_exists(),
			'FAVICON' => TPL_PATH_TO_ROOT . $customization_config->get_favicon_path(),
			'FAVICON_TYPE' => $customization_config->favicon_type(),
			'C_HEADER_LOGO' => !empty($header_logo_path),
			'HEADER_LOGO' => TPL_PATH_TO_ROOT . $header_logo_path,
			'TITLE' => $this->lang['maintain'],
			'SITE_DESCRIPTION' => $general_config->get_site_description(),
			'SITE_KEYWORD' => $general_config->get_site_keywords(),
			'L_XML_LANGUAGE' => $this->main_lang['xml_lang'],
		));
		
		$date_lang = LangLoader::get('date-common');
		$this->tpl->put_all(array(
			'L_MAINTAIN' => FormatingHelper::second_parse($this->maintain_config->get_message()),
			'L_CONNECT' => $this->lang['connect'],
			'U_CONNECT' => UserUrlBuilder::connect()->rel(),
			'L_MAINTAIN_DELAY' => $this->main_lang['maintain_delay'],
			'L_LOADING' => $this->main_lang['loading'],
			'L_DAYS' => $date_lang['days'],
			'L_HOURS' => $date_lang['hours'],
			'L_MIN' => $date_lang['minutes'],
			'L_SEC' => $date_lang['seconds'],
			'LOGIN_FORM' => $this->form->display(),
		));
	}
	
	private function init_delay()
	{
		$date_lang = LangLoader::get('date-common');
		$array_time = array(0 => '-1', 1 => '0', 2 => '60', 3 => '300', 4 => '900', 5 => '1800', 6 => '3600', 7 => '7200', 8 => '86400', 9 => '172800', 10 => '604800'); 
		$array_delay = array(0 => $this->main_lang['unspecified'], 1 => '', 2 => '1 ' . $date_lang['minute'], 3 => '5 ' . $date_lang['minutes'], 4 => '15 ' . $date_lang['minutes'], 5 => '30 ' . $date_lang['minutes'], 6 => '1 ' . $date_lang['hour'], 7 => '2 ' . $date_lang['hours'], 8 => '1 ' . $date_lang['day'], 9 => '2 ' . $date_lang['days'], 10 => '1 ' . $date_lang['week']);
		
		if (!$this->maintain_config->is_unlimited_maintenance())
		{
			$key = 0;
			$current_time = time();
			$end_timestamp = $this->maintain_config->get_end_date()->get_timestamp();
			for ($i = 10; $i >= 0; $i--)
			{
				$delay = ($end_timestamp - $current_time) - $array_time[$i];
				if ($delay >= $array_time[$i]) 
				{
					$key = $i;
					break;
				}
			}
			
			//Calcul du format de la date
			$seconds = gmdate_format('s', $end_timestamp, Timezone::SITE_TIMEZONE);
			$array_release = array(
			gmdate_format('Y', $end_timestamp, Timezone::SITE_TIMEZONE), (gmdate_format('n', $end_timestamp, Timezone::SITE_TIMEZONE) - 1), gmdate_format('j', $end_timestamp, Timezone::SITE_TIMEZONE), 
			gmdate_format('G', $end_timestamp, Timezone::SITE_TIMEZONE), gmdate_format('i', $end_timestamp, Timezone::SITE_TIMEZONE), ($seconds < 10) ? trim($seconds, 0) : $seconds );
		
			$seconds = gmdate_format('s', time(), Timezone::SITE_TIMEZONE);
		    $array_now = array(
		    gmdate_format('Y', time(), Timezone::SITE_TIMEZONE), (gmdate_format('n', time(), Timezone::SITE_TIMEZONE) - 1), gmdate_format('j', time(), Timezone::SITE_TIMEZONE),
		    gmdate_format('G', time(), Timezone::SITE_TIMEZONE), gmdate_format('i', time(), Timezone::SITE_TIMEZONE), ($seconds < 10) ? trim($seconds, 0) : $seconds);
		}
		else
		{
			$key = -1;
			$array_release = array('0', '0', '0', '0', '0', '0');
			$array_now = array('0', '0', '0', '0', '0', '0');
		}
		
		$this->tpl->put_all(array(
			'MAINTAIN_NOW_FORMAT' => implode(',', $array_now),
			'MAINTAIN_RELEASE_FORMAT' => implode(',', $array_release)
		));
		
		if ($this->maintain_config->get_display_duration() && !$this->maintain_config->is_unlimited_maintenance())
		{
			$this->tpl->put_all(array(
				'C_DISPLAY_DELAY' => true,
				'DELAY' => isset($array_delay[$key + 1]) ? $array_delay[$key + 1] : '0'
			));
		}
	}
	
	private function build_form()
	{
		$lang = LangLoader::get('user-common');
		
		$this->form = new HTMLForm('loginForm', '', false);
		$this->form->set_css_class('fieldset-content');

		$fieldset = new FormFieldsetHTML('loginFieldset', $lang['connect']);
		$login = new FormFieldTextEditor('login', $this->lang['pseudo'], '', array('required' => true));
		$fieldset->add_field($login);
		$password = new FormFieldPasswordEditor('password', $lang['password'], '', array('required' => true));
		$fieldset->add_field($password);
		$autoconnect = new FormFieldCheckbox('autoconnect', $lang['autoconnect'], true);
		$fieldset->add_field($autoconnect);

		$this->form->add_fieldset($fieldset);

		$this->submit_button = new FormButtonSubmit($lang['connect'], 'authenticate');
		$this->form->add_button($this->submit_button);
	}
	
	private function authenticate($login, $password, $autoconnect)
	{
		$errors_lang = LangLoader::get('errors');
		$session = AppContext::get_session();
		$user_id = PersistenceContext::get_querier()->get_column_value(DB_TABLE_MEMBER, 'user_id', 'WHERE login = :login', array('login' => $login));
		if (!empty($user_id)) //Membre existant.
		{
			$info_connect = PersistenceContext::get_querier()->select_single_row(DB_TABLE_MEMBER, array('level', 'user_warning', 'last_connect', 'test_connect', 'user_ban', 'user_aprob', 'password'), 'WHERE id = :id', array('id' => $user_id));
			$delay_connect = (time() - $info_connect['last_connect']); //D�lai entre deux essais de connexion.
			$delay_ban = (time() - $info_connect['delay_banned']); //V�rification si le membre est banni.

			if ($delay_ban >= 0 && $info_connect['user_aprob'] == '1' && $info_connect['warning_percentage'] < '100') //Utilisateur non (plus) banni.
			{
				if ($delay_connect >= 600) //5 nouveau essais, 10 minutes apr�s.
				{
					PersistenceContext::get_querier()->update(DB_TABLE_MEMBER, array('last_connect' => time(), 'test_connect' => 0), 'WHERE user_id = :id', array('id' => $user_id)); //Remise � z�ro du compteur d'essais.
					$error_report = $session->start($user_id, $password, $info_connect['level'], REWRITED_SCRIPT, '', '', $autoconnect); //On lance la session.
				}
				elseif ($delay_connect >= 300) //2 essais 5 minutes apr�s
				{
					PersistenceContext::get_querier()->update(DB_TABLE_MEMBER, array('last_connect' => time(), 'test_connect' => 3), 'WHERE user_id = :id', array('id' => $user_id)); //Redonne 2 essais.
					$error_report = $session->start($user_id, $password, $info_connect['level'], REWRITED_SCRIPT, '', '', $autoconnect); //On lance la session.
				}
				elseif ($info_connect['test_connect'] < 5) //Succ�s.
				{
					$error_report = $session->start($user_id, $password, $info_connect['level'], REWRITED_SCRIPT, '', '', $autoconnect); //On lance la session.
				}
				else //plus d'essais
				{
					AppContext::get_response()->redirect(UserUrlBuilder::maintain('flood'));
				}
			}
			elseif ($info_connect['user_aprob'] == '0')
			{
				AppContext::get_response()->redirect(UserUrlBuilder::maintain('not_enabled'));
			}
			elseif ($info_connect['warning_percentage'] == '100')
			{
				AppContext::get_response()->redirect(UserUrlBuilder::maintain('banned'));
			}
			else
			{
				$delay_ban = ceil((0 - $delay_ban)/60);
				AppContext::get_response()->redirect(UserUrlBuilder::maintain('banned', $delay_ban));
			}

			if (!empty($error_report)) //Erreur
			{
				PersistenceContext::get_querier()->update(DB_TABLE_MEMBER, array('last_connect' => time(), 'test_connect' => 'test_connect + 1'), 'WHERE user_id = :id', array('id' => $user_id));
				$info_connect['test_connect']++;
				$info_connect['test_connect'] = 5 - $info_connect['test_connect'];
				AppContext::get_response()->redirect(UserUrlBuilder::maintain('flood/' . $info_connect['test_connect']));
			}
			elseif ($info_connect['test_connect'] > 0) //Succ�s redonne tous les essais.
			{
				PersistenceContext::get_querier()->update(DB_TABLE_MEMBER, array('last_connect' => time(), 'test_connect' => 0), 'WHERE user_id = :id', array('id' => $user_id)); //Remise � z�ro du compteur d'essais.
			}
		}
		else
		{
			AppContext::get_response()->redirect(UserUrlBuilder::maintain('unexisting'));
		}
		
		if (!empty($url_to_redirect))
		{
			AppContext::get_response()->redirect($url_to_redirect);
		}
		AppContext::get_response()->redirect(Environment::get_home_page());
	}
	
	private function build_error_message()
	{
		$date_lang = LangLoader::get('date-common');
		$errors_lang = LangLoader::get('errors');
		
		$error_type = $this->request->get_string('error_type', '');
		$error_value = $this->request->get_string('error_value', '');
		switch ($error_type) {
			case 'flood':
				if (!empty($error_value))
				{
					$flood = ($error_value > 0 && $error_value <= 5) ? $error_value : 0;
					$this->display_error_message($errors_lang['e_wrong_password'] . '. ' . sprintf($errors_lang['e_test_connect'], $flood));
				}
				else
				{
					$this->display_error_message($errors_lang['e_nomore_test_connect']);
				}		
			break;
			case 'not_enabled':
				$this->display_error_message($errors_lang['e_unactiv_member']);				
			break;
			case 'wrong_password':
				$this->display_error_message($errors_lang['e_wrong_password']);
			break;
			case 'banned':
				if (!empty($error_value))
				{
					$delay = $error_value;
					if ($delay > 0)
					{
						if ($delay < 60)
							$message = $delay . ' ' . (($delay > 1) ? $date_lang['minutes'] : $date_lang['minute']);
						elseif ($delay < 1440)
						{
							$delay_ban = NumberHelper::round($delay/60, 0);
							$message = $delay_ban . ' ' . (($delay_ban > 1) ? $date_lang['hours'] : $date_lang['hour']);
						}
						elseif ($delay < 10080)
						{
							$delay_ban = NumberHelper::round($delay/1440, 0);
							$message = $delay_ban . ' ' . (($delay_ban > 1) ? $date_lang['days'] : $date_lang['day']);
						}
						elseif ($delay < 43200)
						{
							$delay_ban = NumberHelper::round($delay/10080, 0);
							$message = $delay_ban . ' ' . (($delay_ban > 1) ? $date_lang['weeks'] : $date_lang['week']);
						}
						elseif ($delay < 525600)
						{
							$delay_ban = NumberHelper::round($delay/43200, 0);
							$message = $delay_ban . ' ' . (($delay_ban > 1) ? $date_lang['months'] : $date_lang['month']);
						}
						else
						{
							$delay_ban = NumberHelper::round($delay/525600, 0);
							$message = $delay_ban . ' ' . (($delay_ban > 1) ? $date_lang['years'] : $date_lang['year']);
						}
						$message = $errors_lang['e_member_ban'] . ' ' . $message;
					}
					$this->display_error_message($message);
				}
				else
				{
					$this->display_error_message($errors_lang['e_member_ban_w']);
				}
			break;
			case 'unexisting':
				$this->display_error_message(LangLoader::get_message('user.not_exists', 'status-messages-common'));
			break;
			case 'not_authorized':
				$this->display_error_message($errors_lang['e_auth']);
			break;
		}
	}
	
	private function display_error_message($message, $error_type = 'notice')
	{
		$this->tpl->put('ERROR_MESSAGE', MessageHelper::display($message, $error_type));
	}
	
	private function build_reponse()
	{
		$response = new SiteDisplayFrameResponse($this->tpl);
		
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title(LangLoader::get_message('title_maintain', 'main'));
		$graphical_environment->get_seo_meta_data()->set_canonical_url(UserUrlBuilder::maintain());
		
		return $response;
	}
	
	public function get_right_controller_regarding_authorizations()
	{
		if (!MaintenanceConfig::load()->is_under_maintenance())
		{
			AppContext::get_response()->redirect(Environment::get_home_page());
		}
		return $this;
	}
}
?>