<?php
/*##################################################
 *                               DownloadFile.class.php
 *                            -------------------
 *   begin                : August 24, 2014
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

class DownloadFile
{
	private $id;
	private $id_category;
	private $name;
	private $rewrited_name;
	private $url;
	private $size;
	private $formated_size;
	private $contents;
	private $short_contents;
	
	private $approbation_type;
	private $start_date;
	private $end_date;
	private $end_date_enabled;
	
	private $creation_date;
	private $updated_date;
	private $number_view;
	private $author_user;
	private $author_custom_name;
	private $author_custom_name_enabled;
	
	private $picture_url;
	private $software_version;
	private $number_downloads;
	private $notation;
	private $keywords;
	
	const SORT_ALPHABETIC       = 'name';
	const SORT_AUTHOR           = 'display_name';
	const SORT_DATE             = 'creation_date';
	const SORT_UPDATED_DATE     = 'updated_date';
	const SORT_NOTATION         = 'average_notes';
	const SORT_NUMBER_COMMENTS  = 'number_comments';
	const SORT_NUMBER_DOWNLOADS = 'number_downloads';
	const SORT_NUMBER_VIEWS 	= 'number_view';
	
	const SORT_FIELDS_URL_VALUES = array(
		self::SORT_ALPHABETIC => 'name',
		self::SORT_AUTHOR => 'author',
		self::SORT_DATE => 'date',
		self::SORT_UPDATED_DATE => 'updated',
		self::SORT_NUMBER_DOWNLOADS => 'download',
		self::SORT_NUMBER_VIEWS => 'views',
		self::SORT_NOTATION => 'notes',
		self::SORT_NUMBER_COMMENTS => 'comments'
	);
	
	const ASC  = 'ASC';
	const DESC = 'DESC';
	
	const NOT_APPROVAL = 0;
	const APPROVAL_NOW = 1;
	const APPROVAL_DATE = 2;
	
	const DEFAULT_PICTURE = '/download/download.png';
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id_category()
	{
		return $this->id_category;
	}
	
	public function set_id_category($id_category)
	{
		$this->id_category = $id_category;
	}
	
	public function get_category()
	{
		return DownloadService::get_categories_manager()->get_categories_cache()->get_category($this->id_category);
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function set_name($name)
	{
		$this->name = $name;
	}
	
	public function get_rewrited_name()
	{
		return $this->rewrited_name;
	}
	
	public function set_rewrited_name($rewrited_name)
	{
		$this->rewrited_name = $rewrited_name;
	}
	
	public function get_url()
	{
		if (!$this->url instanceof Url)
			return new Url('');
		
		return $this->url;
	}
	
	public function set_url(Url $url)
	{
		$this->url = $url;
	}
	
	public function get_size()
	{
		return $this->size;
	}
	
	public function set_size($size)
	{
		$this->size = $size;
	}
	
	public function get_formated_size()
	{
		return $this->formated_size;
	}
	
	public function set_formated_size($formated_size)
	{
		$this->formated_size = $formated_size;
	}
	
	public function get_contents()
	{
		return $this->contents;
	}
	
	public function set_contents($contents)
	{
		$this->contents = $contents;
	}
	
	public function get_short_contents()
	{
		return $this->short_contents;
	}
	
	public function set_short_contents($short_contents)
	{
		$this->short_contents = $short_contents;
	}
	
	public function is_short_contents_enabled()
	{
		return !empty($this->short_contents);
	}
	
	public function get_real_short_contents()
	{
		if ($this->is_short_contents_enabled())
		{
			return FormatingHelper::second_parse($this->short_contents);
		}
		return TextHelper::cut_string(@strip_tags(FormatingHelper::second_parse($this->contents), '<br><br/>'), (int)DownloadConfig::NUMBER_CARACTERS_BEFORE_CUT);
	}
	
	public function get_approbation_type()
	{
		return $this->approbation_type;
	}
	
	public function set_approbation_type($approbation_type)
	{
		$this->approbation_type = $approbation_type;
	}
	
	public function is_visible()
	{
		$now = new Date();
		return DownloadAuthorizationsService::check_authorizations($this->id_category)->read() && ($this->get_approbation_type() == self::APPROVAL_NOW || ($this->get_approbation_type() == self::APPROVAL_DATE && $this->get_start_date()->is_anterior_to($now) && ($this->end_date_enabled ? $this->get_end_date()->is_posterior_to($now) : true)));
	}
	
	public function get_status()
	{
		switch ($this->approbation_type) {
			case self::APPROVAL_NOW:
				return LangLoader::get_message('status.approved.now', 'common');
			break;
			case self::APPROVAL_DATE:
				return LangLoader::get_message('status.approved.date', 'common');
			break;
			case self::NOT_APPROVAL:
				return LangLoader::get_message('status.approved.not', 'common');
			break;
		}
	}
	
	public function get_start_date()
	{
		return $this->start_date;
	}
	
	public function set_start_date(Date $start_date)
	{
		$this->start_date = $start_date;
	}
	
	public function get_end_date()
	{
		return $this->end_date;
	}
	
	public function set_end_date(Date $end_date)
	{
		$this->end_date = $end_date;
		$this->end_date_enabled = true;
	}
	
	public function is_end_date_enabled()
	{
		return $this->end_date_enabled;
	}
	
	public function get_creation_date()
	{
		return $this->creation_date;
	}
	
	public function set_creation_date(Date $creation_date)
	{
		$this->creation_date = $creation_date;
	}
	
	public function get_updated_date()
	{
		return $this->updated_date;
	}
	
	public function set_updated_date(Date $updated_date)
	{
		$this->updated_date = $updated_date;
	}
	
	public function has_updated_date()
	{
		return $this->updated_date !== null && $this->updated_date->get_timestamp() !== $this->creation_date->get_timestamp();
	}
	
	public function get_author_user()
	{
		return $this->author_user;
	}
	
	public function set_author_user(User $user)
	{
		$this->author_user = $user;
	}
	
	public function get_author_custom_name()
	{
		return $this->author_custom_name;
	}
	
	public function set_author_custom_name($author_custom_name)
	{
		$this->author_custom_name = $author_custom_name;
	}
	
	public function is_author_custom_name_enabled()
	{
		return $this->author_custom_name_enabled;
	}
	
	public function set_number_view($number_view)
	{
		$this->number_view = $number_view;
	}

	public function get_number_view()
	{
		return $this->number_view;
	}
	
	public function get_picture()
	{
		return $this->picture_url;
	}
	
	public function set_picture(Url $picture)
	{
		$this->picture_url = $picture;
	}
	
	public function has_picture()
	{
		$picture = $this->picture_url->rel();
		return !empty($picture);
	}
	
	public function get_software_version()
	{
		return $this->software_version;
	}
	
	public function set_software_version($software_version)
	{
		$this->software_version = $software_version;
	}
	
	public function get_number_downloads()
	{
		return $this->number_downloads;
	}
	
	public function set_number_downloads($number_downloads)
	{
		$this->number_downloads = $number_downloads;
	}
	
	public function get_notation()
	{
		return $this->notation;
	}
	
	public function set_notation(Notation $notation)
	{
		$this->notation = $notation;
	}
	
	public function get_keywords()
	{
		if ($this->keywords === null)
		{
			$this->keywords = DownloadService::get_keywords_manager()->get_keywords($this->id);
		}
		return $this->keywords;
	}
	
	public function get_keywords_name()
	{
		return array_keys($this->get_keywords());
	}
	
	public function is_authorized_to_add()
	{
		return DownloadAuthorizationsService::check_authorizations($this->id_category)->write() || DownloadAuthorizationsService::check_authorizations($this->id_category)->contribution();
	}
	
	public function is_authorized_to_edit()
	{
		return DownloadAuthorizationsService::check_authorizations($this->id_category)->moderation() || ((DownloadAuthorizationsService::check_authorizations($this->id_category)->write() || (DownloadAuthorizationsService::check_authorizations($this->id_category)->contribution() && !$this->is_visible())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}
	
	public function is_authorized_to_delete()
	{
		return DownloadAuthorizationsService::check_authorizations($this->id_category)->moderation() || ((DownloadAuthorizationsService::check_authorizations($this->id_category)->write() || (DownloadAuthorizationsService::check_authorizations($this->id_category)->contribution() && !$this->is_visible())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}
	
	public function get_properties()
	{
		return array(
			'id' => $this->get_id(),
			'id_category' => $this->get_id_category(),
			'name' => $this->get_name(),
			'rewrited_name' => $this->get_rewrited_name(),
			'url' => $this->get_url()->relative(),
			'size' => $this->get_size(),
			'contents' => $this->get_contents(),
			'short_contents' => $this->get_short_contents(),
			'approbation_type' => $this->get_approbation_type(),
			'start_date' => $this->get_start_date() !== null ? $this->get_start_date()->get_timestamp() : 0,
			'end_date' => $this->get_end_date() !== null ? $this->get_end_date()->get_timestamp() : 0,
			'creation_date' => $this->get_creation_date()->get_timestamp(),
			'updated_date' => $this->get_updated_date() !== null ? $this->get_updated_date()->get_timestamp() : $this->get_creation_date()->get_timestamp(),
			'author_custom_name' => $this->get_author_custom_name(),
			'author_user_id' => $this->get_author_user()->get_id(),
			'number_downloads' => $this->get_number_downloads(),
			'number_view' => $this->get_number_view(),
			'picture_url' => $this->get_picture()->relative(),
			'software_version' => $this->get_software_version()
		);
	}
	
	public function set_properties(array $properties)
	{
		$this->id = $properties['id'];
		$this->id_category = $properties['id_category'];
		$this->name = $properties['name'];
		$this->rewrited_name = $properties['rewrited_name'];
		$this->url = new Url($properties['url']);
		$this->size = $properties['size'];
		$this->contents = $properties['contents'];
		$this->short_contents = $properties['short_contents'];
		$this->number_view = $properties['number_view'];
		$this->approbation_type = $properties['approbation_type'];
		$this->start_date = !empty($properties['start_date']) ? new Date($properties['start_date'], Timezone::SERVER_TIMEZONE) : null;
		$this->end_date = !empty($properties['end_date']) ? new Date($properties['end_date'], Timezone::SERVER_TIMEZONE) : null;
		$this->end_date_enabled = !empty($properties['end_date']);
		$this->creation_date = new Date($properties['creation_date'], Timezone::SERVER_TIMEZONE);
		$this->updated_date = !empty($properties['updated_date']) ? new Date($properties['updated_date'], Timezone::SERVER_TIMEZONE) : null;
		$this->number_downloads = $properties['number_downloads'];
		$this->picture_url = new Url($properties['picture_url']);
		$this->software_version = $properties['software_version'];
		
		$user = new User();
		if (!empty($properties['user_id']))
			$user->set_properties($properties);
		else
			$user->init_visitor_user();
		
		$this->set_author_user($user);
		
		$this->author_custom_name = !empty($properties['author_custom_name']) ? $properties['author_custom_name'] : $this->author_user->get_display_name();
		$this->author_custom_name_enabled = !empty($properties['author_custom_name']);
		
		$notation = new Notation();
		$notation->set_module_name('download');
		$notation->set_id_in_module($properties['id']);
		$notation->set_number_notes($properties['number_notes']);
		$notation->set_average_notes($properties['average_notes']);
		$notation->set_user_already_noted(!empty($properties['note']));
		$this->notation = $notation;
		
		$units = array(LangLoader::get_message('unit.bytes', 'common'), LangLoader::get_message('unit.kilobytes', 'common'), LangLoader::get_message('unit.megabytes', 'common'), LangLoader::get_message('unit.gigabytes', 'common'));
		$power = $this->size > 0 ? floor(log($this->size, 1024)) : 0;
		$this->formated_size = (float)number_format($this->size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
	}
	
	public function init_default_properties($id_category = Category::ROOT_CATEGORY)
	{
		$this->id_category = $id_category;
		$this->url = new Url('');
		$this->size = 0;
		$this->approbation_type = self::APPROVAL_NOW;
		$this->author_user = AppContext::get_current_user();
		$this->start_date = new Date();
		$this->end_date = new Date();
		$this->creation_date = new Date();
		$this->number_downloads = 0;
		$this->number_view = 0;
		$this->picture_url = new Url(self::DEFAULT_PICTURE);
		$this->software_version = '';
		$this->end_date_enabled = false;
		$this->author_custom_name = $this->author_user->get_display_name();
		$this->author_custom_name_enabled = false;
	}
	
	public function clean_start_and_end_date()
	{
		$this->start_date = null;
		$this->end_date = null;
		$this->end_date_enabled = false;
	}
	
	public function clean_end_date()
	{
		$this->end_date = null;
		$this->end_date_enabled = false;
	}
	
	public function get_array_tpl_vars()
	{
		$category = $this->get_category();
		$contents = FormatingHelper::second_parse($this->contents);
		$description = $this->get_real_short_contents();
		$user = $this->get_author_user();
		$user_group_color = User::get_group_color($user->get_groups(), $user->get_level(), true);
		$number_comments = CommentsService::get_number_comments('download', $this->id);
		$config = DownloadConfig::load();
		
		return array_merge(
			Date::get_array_tpl_vars($this->creation_date, 'date'),
			Date::get_array_tpl_vars($this->updated_date, 'updated_date'),
			Date::get_array_tpl_vars($this->start_date, 'differed_start_date'),
			array(
 			'C_VISIBLE' => $this->is_visible(),
			'C_EDIT' => $this->is_authorized_to_edit(),
			'C_DELETE' => $this->is_authorized_to_delete(),
			'C_READ_MORE' => !$this->is_short_contents_enabled() && TextHelper::strlen($contents) > DownloadConfig::NUMBER_CARACTERS_BEFORE_CUT && $description != @strip_tags($contents, '<br><br/>'),
			'C_SIZE' => !empty($this->size),
			'C_PICTURE' => $this->has_picture(),
			'C_SOFTWARE_VERSION' => !empty($this->software_version),
			'C_AUTHOR_CUSTOM_NAME' => $this->is_author_custom_name_enabled(),
			'C_NB_VIEW_ENABLED' => $config->get_nb_view_enabled(),
			'C_USER_GROUP_COLOR' => !empty($user_group_color),
			'C_UPDATED_DATE' => $this->has_updated_date(),
			'C_DIFFERED' => $this->approbation_type == self::APPROVAL_DATE,
			'C_NEW_CONTENT' => ContentManagementConfig::load()->module_new_content_is_enabled_and_check_date('download', $this->get_start_date() != null ? $this->get_start_date()->get_timestamp() : $this->get_creation_date()->get_timestamp()) && $this->is_visible(),
			
			//Downloadlink
			'ID' => $this->id,
			'NAME' => $this->name,
			'SIZE' => $this->formated_size,
			'CONTENTS' => $contents,
			'DESCRIPTION' => $description,
			'STATUS' => $this->get_status(),
			'AUTHOR_CUSTOM_NAME' => $this->author_custom_name,
			'C_AUTHOR_EXIST' => $user->get_id() !== User::VISITOR_LEVEL,
			'PSEUDO' => $user->get_display_name(),
			'USER_LEVEL_CLASS' => UserService::get_level_class($user->get_level()),
			'USER_GROUP_COLOR' => $user_group_color,
			'SOFTWARE_VERSION' => $this->software_version,
			'NUMBER_DOWNLOADS' => $this->number_downloads,
			'NUMBER_VIEW' => $this->get_number_view(),
			'L_DOWNLOADED_TIMES' => StringVars::replace_vars(LangLoader::get_message('downloaded_times', 'common', 'download'), array('number_downloads' => $this->number_downloads)),
			'STATIC_NOTATION' => NotationService::display_static_image($this->get_notation()),
			'NOTATION' => NotationService::display_active_image($this->get_notation()),
			
			'C_COMMENTS' => !empty($number_comments),
			'L_COMMENTS' => CommentsService::get_lang_comments('download', $this->id),
			'NUMBER_COMMENTS' => $number_comments,
			
			//Category
			'C_ROOT_CATEGORY' => $category->get_id() == Category::ROOT_CATEGORY,
			'CATEGORY_ID' => $category->get_id(),
			'CATEGORY_NAME' => $category->get_name(),
			'CATEGORY_DESCRIPTION' => $category->get_description(),
			'CATEGORY_IMAGE' => $category->get_image()->rel(),
			'U_EDIT_CATEGORY' => $category->get_id() == Category::ROOT_CATEGORY ? DownloadUrlBuilder::configuration()->rel() : DownloadUrlBuilder::edit_category($category->get_id())->rel(),
			
			'U_SYNDICATION' => SyndicationUrlBuilder::rss('download', $this->id_category)->rel(),
			'U_AUTHOR_PROFILE' => UserUrlBuilder::profile($this->get_author_user()->get_id())->rel(),
			'U_LINK' => DownloadUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $this->id, $this->rewrited_name)->rel(),
			'U_DOWNLOAD' => DownloadUrlBuilder::download($this->id)->rel(),
			'U_DEADLINK' => DownloadUrlBuilder::dead_link($this->id)->rel(),
			'U_CATEGORY' => DownloadUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name())->rel(),
			'U_EDIT' => DownloadUrlBuilder::edit($this->id)->rel(),
			'U_DELETE' => DownloadUrlBuilder::delete($this->id)->rel(),
			'U_PICTURE' => $this->get_picture()->rel(),
			'U_COMMENTS' => DownloadUrlBuilder::display_comments($category->get_id(), $category->get_rewrited_name(), $this->id, $this->rewrited_name)->rel()
			)
		);
	}
}
?>
