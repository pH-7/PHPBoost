<?php
/*##################################################
 *                       ContentSecondParser.class.php
 *                            -------------------
 *   begin                : August 10, 2008
 *   copyright            : (C) 2008 Benoit Sautel
 *   email                : ben.popeye@phpboost.com
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
 * @desc This class ensures the real time processing of the content. The major part of the processing is saved in the database to minimize as much as possible the treatment
 * when the content is displayed. However, some tags cannot be cached, because we cannot have return to the original code. It's for instance the case of the code tag
 * which replaces the code by a lot of html code which formats the code.
 * This kind of tag is treated in real time by this class.
 * The content you put in that parser must come from a ContentFormattingParser class (BBCodeParser or TinyMCEParser) (it can have been saved in a database between the first parsing and the real time parsing).
 * @author Benoît Sautel <ben.popeye@phpboost.com>
 */
class ContentSecondParser extends AbstractParser
{
	/**
	 * Maximal number of characters that can be inserted in the [code] tag. After that, GeSHi has many difficulties to highligth and has the PHP execution stop (error 500).
	 */
	const MAX_CODE_LENGTH = 40000;
	/**
	 * @desc Builds a ContentSecondParser object
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @desc Parses the content of the parser. The result will be ready to be displayed.
	 */
	public function parse()
	{
		//Relative url parsing
		$this->content = preg_replace_callback('`(src|href)="/([A-Za-z0-9#+-_\./]+)"`suU', array($this, 'callbackrelative_url'), $this->content);

		//Balise code
		if (TextHelper::strpos($this->content, '[[CODE') !== false)
		{
			$this->content = preg_replace_callback('`\[\[CODE(?:=([^,\s]+))?(?:,(0|1)(?:,(0|1))?)?\]\](.+)\[\[/CODE\]\]`suU', array($this, 'callbackhighlight_code'), $this->content);
		}

		//Balise member
		if (stripos($this->content, '[[MEMBER]]') !== false)
		{
			$this->content = preg_replace_callback('`\[\[MEMBER\]\](.+)\[\[/MEMBER\]\]`suU', array($this, 'callback_member_tag'), $this->content);
		}

		//Balise moderator
		if (stripos($this->content, '[[MODERATOR]]') !== false)
		{
			$this->content = preg_replace_callback('`\[\[MODERATOR\]\](.+)\[\[/MODERATOR\]\]`suU', array($this, 'callback_moderator_tag'), $this->content);
		}

		//Media
		if (TextHelper::strpos($this->content, '[[MEDIA]]') !== false)
		{
			$this->process_media_insertion();
		}

		//Balise latex.
		if (TextHelper::strpos($this->content, '[[MATH]]') !== false)
		{
			$server_config = new ServerConfiguration();
			if ($server_config->has_gd_library())
			{
				require_once PATH_TO_ROOT . '/kernel/lib/php/mathpublisher/mathpublisher.php';
				$this->content = preg_replace_callback('`\[\[MATH\]\](.+)\[\[/MATH\]\]`suU', array($this, 'math_code'), $this->content);
			}
		}

		$this->parse_feed_tag();
	}

	/**
	 * @desc Transforms a PHPBoost HTML content to make it exportable and usable every where in the web.
	 * @param string $html Content to transform
	 * @return string The exportable content
	 */
	public static function export_html_text($html_content)
	{
		//Balise vidéo
		$html_content = preg_replace('`<a href="([^"]+)" style="display:block;margin:auto;width:([0-9]+)px;height:([0-9]+)px;" id="movie_[0-9]+"></a><br /><script><!--\s*insertMoviePlayer\(\'movie_[0-9]+\'\);\s*--></script>`isU',
			'<object type="application/x-shockwave-flash" width="$2" height="$3">
				<param name="FlashVars" value="flv=$1&width=$2&height=$3" />
				<param name="allowScriptAccess" value="never" />
				<param name="play" value="true" />
				<param name="movie" value="$1" />
				<param name="menu" value="false" />
				<param name="quality" value="high" />
				<param name="scalemode" value="noborder" />
				<param name="wmode" value="transparent" />
				<param name="bgcolor" value="#FFFFFF" />
			</object>',
		$html_content);

		return Url::html_convert_root_relative2absolute($html_content);
	}

	/**
	 * @desc Highlights a content in a supported language using the appropriate syntax highlighter.
	 * The highlighted languages are numerous: actionscript, asm, asp, bash, c, cpp, csharp, css, d, delphi, fortran, html,
	 * java, javascript, latex, lua, matlab, mysql, pascal, perl, php, python, rails, ruby, sql, text, vb, xml,
	 * PHPBoost templates and PHPBoost BBCode.
	 * @param string $contents Content to highlight
	 * @param string $language Language name
	 * @param bool $line_number Indicate whether or not the line number must be added to the code.
	 * @param bool $inline_code Indicate if the code is multi line.
	 */
	private static function highlight_code($contents, $language, $line_number, $inline_code)
	{
		$contents = TextHelper::htmlspecialchars_decode($contents);

		//BBCode PHPBoost
		if (TextHelper::strtolower($language) == 'bbcode')
		{
			$bbcode_highlighter = new BBCodeHighlighter();
			$bbcode_highlighter->set_content($contents);
			$bbcode_highlighter->parse($inline_code);
			$contents = $bbcode_highlighter->get_content();
		}
		//Templates PHPBoost
		elseif (TextHelper::strtolower($language) == 'tpl' || TextHelper::strtolower($language) == 'template')
		{
			require_once(PATH_TO_ROOT . '/kernel/lib/php/geshi/geshi.php');

			$template_highlighter = new TemplateHighlighter();
			$template_highlighter->set_content($contents);
			$template_highlighter->parse($line_number ? GESHI_NORMAL_LINE_NUMBERS : GESHI_NO_LINE_NUMBERS, $inline_code);
			$contents = $template_highlighter->get_content();
		}
		elseif ( TextHelper::strtolower($language) == 'plain')
		{
			$plain_code_highlighter = new PlainCodeHighlighter();
			$plain_code_highlighter->set_content($contents);
			$plain_code_highlighter->parse();
			$contents = $plain_code_highlighter->get_content();
		}
		elseif ($language != '')
		{
			require_once(PATH_TO_ROOT . '/kernel/lib/php/geshi/geshi.php');
			$Geshi = new GeSHi($contents, $language);

			if ($line_number) //Affichage des numéros de lignes.
			{
				$Geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			}

			//No container if we are in an inline tag
			if ($inline_code)
			{
				$Geshi->set_header_type(GESHI_HEADER_NONE);
			}

			$contents = '<pre style="display:inline;">' . $Geshi->parse_code() . '</pre>';
		}
		else
		{
			$highlight = highlight_string($contents, true);
			$font_replace = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $highlight);
			$contents = preg_replace('`color="(.*?)"`', 'style="color:$1"', $font_replace);
		}

		return $contents;
	}

	/**
	 * @static
	 * @desc Displays the url correctly when PHPBoost is installed in a sub-folder
	 * @return string the relative url
	 */
	private function callbackrelative_url($matches)
	{
		return $matches[1] . '="' . Url::to_rel('/' . $matches[2]) . '"';
	}

	/**
	 * @static
	 * @desc Handler which highlights a string matched by the preg_replace_callback function.
	 * @param string[] $matches The matched contents: 0 => the whole string, 1 => the language, 2 => number count?,
	 * 3 => multi line?, 4 => the code to highlight.
	 * @return string the colored content
	 */
	private function callbackhighlight_code($matches)
	{
		$extension = "";
		$line_number = !empty($matches[2]);
		$inline_code = !empty($matches[3]);

		$content_to_highlight = $matches[4];

		if (TextHelper::strlen($content_to_highlight) > self::MAX_CODE_LENGTH)
		{
			return '<div class="message-helper error">' . LangLoader::get_message('code_too_long_error', 'editor-common') . '</div>';
		}

		if (!empty($matches[1])) {
			$info = new SplFileInfo($matches[1]);
			$extension = $info->getExtension();
			$extension = TextHelper::strtolower($extension);

			if ($extension == 'js' || $extension == 'jquery')
			{
				$extension = "javascript";
			}
			else if ($extension && TextHelper::strtolower($extension)!='tpl')
			{
				require_once(PATH_TO_ROOT . '/kernel/lib/php/geshi/geshi.php');
				$Geshi = new GeSHi();
				$Geshi->set_language($extension);
				if ($Geshi->error())
				{
					$extension = "text";
				}
			}
		}

		if ($extension != "")
		{
			$typecode = TextHelper::strtoupper($extension);
			$title = $matches[1] .' : ';
		}
		else
		{
			$typecode = TextHelper::strtoupper($matches[1]);
			$title = sprintf(LangLoader::get_message('code_langage', 'main'), TextHelper::strtoupper($matches[1]));
		}

		$contents = $this->highlight_code($content_to_highlight, $typecode, $line_number, $inline_code);

		if (!$inline_code && !empty($matches[1]))
		{
			$contents = '<div class="formatter-container formatter-code code-' . $typecode . '"><span class="formatter-title">' . $title . '</span><div class="formatter-content">' . $contents .'</div></div>';
		}
		else if (!$inline_code && empty($matches[1]))
		{
			$contents = '<div class="formatter-container formatter-code"><span class="formatter-title">' . LangLoader::get_message('code_tag', 'main') . '</span><div class="formatter-content">' . $contents . '</div></div>';
		}

		return $contents;
	}

	/**
	 * @static
	 * @desc Display the content only if it's a connected user.
	 * @param string[] 1 => the content inside member tag.
	 * @return string The content if it's a member or a generic message.
	 */
	private function callback_member_tag($matches)
	{
		if (AppContext::get_current_user()->check_level(User::MEMBER_LEVEL))
		{
			return $matches[1];
		}
		return MessageHelper::display(LangLoader::get_message('bbcode_member', 'status-messages-common'), MessageHelper::MEMBER_ONLY)->render();
	}

	/**
	 * @static
	 * @desc Display the content only if it's a moderator user.
	 * @param string[] 1 => the content inside moderator tag.
	 * @return string The content if it's a moderator or a generic message.
	 */
	private function callback_moderator_tag($matches)
	{
		if (AppContext::get_current_user()->check_level(User::MODERATOR_LEVEL))
		{
			return $matches[1];
		}
		return MessageHelper::display(LangLoader::get_message('bbcode_moderator', 'status-messages-common'), MessageHelper::MODERATOR_ONLY)->render();
	}

	/**
	 * @static
	 * @desc Parses the latex code and replaces it by an image containing the mathematic formula.
	 * @param string[] $matches 0 => the whole tag, 1 => the latex code to parse.
	 * @return string The code of the image containing the formula.
	 */
	private function math_code($matches)
	{
		$matches[1] = str_replace('<br />', '', $matches[1]);
		$code = mathimage($matches[1], 12, '/images/maths/');
		return $code;
	}

	/**
	 * Processes the media insertion it replaces the [[MEDIA]]tag[[/MEDIA]] by the Javascript API correspondig calls.
	 */
	private function process_media_insertion()
	{
		//Swf
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertSwfPlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isuU', array('ContentSecondParser', 'process_swf_tag'), $this->content);
		//Movie
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertMoviePlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isuU', array('ContentSecondParser', 'process_movie_tag'), $this->content);
		//Movie with poster
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertMoviePlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+), ([^\']+)\);\[\[/MEDIA\]\]`isuU', array('ContentSecondParser', 'process_movie_tag'), $this->content);
		//Sound
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertSoundPlayer\(\'([^\']+)\'\);\[\[/MEDIA\]\]`isuU', array('ContentSecondParser', 'process_sound_tag'), $this->content);
		//Youtube
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertYoutubePlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isuU', array('ContentSecondParser', 'process_youtube_tag'), $this->content);
	}

	/**
	 * Inserts the javascript calls for the swf tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascrpt calls
	 */
	private static function process_swf_tag($matches)
	{
		if (pathinfo($matches[1], PATHINFO_EXTENSION) == 'flv')
		{
			$id = 'movie_' . AppContext::get_uid();
			return '<div class="media-content"><a class="video-player" href="' . Url::to_rel($matches[1]) . '" style="display:block;margin:auto;width:' . $matches[2] . 'px;height:' . $matches[3] . 'px;" id="' . $id .  '"></a></div><br />' .
			'<script><!--' . "\n" .
			'insertMoviePlayer(\'' . $id . '\');' .
			"\n" . '--></script>';
		}
		else
		{
			return "<div class=\"media-content\"><object type=\"application/x-shockwave-flash\" data=\"" . $matches[1] . "\" width=\"" . $matches[2] . "\" height=\"" . $matches[3] . "\">" .
			"<param name=\"allowScriptAccess\" value=\"never\" />" .
			"<param name=\"play\" value=\"true\" />" .
			"<param name=\"movie\" value=\"" . Url::to_rel($matches[1]) . "\" />" .
			"<param name=\"menu\" value=\"false\" />" .
			"<param name=\"quality\" value=\"high\" />" .
			"<param name=\"scalemode\" value=\"noborder\" />" .
			"<param name=\"wmode\" value=\"transparent\" />" .
			"<param name=\"bgcolor\" value=\"#000000\" />" .
			"</object></div>";
		}
	}

	/**
	 * Inserts the javascript calls for the movie tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascrpt calls
	 */
	private static function process_movie_tag($matches)
	{
		$sources = '';
		$video_files = explode(',', $matches[1]);

		if (pathinfo($video_files[0], PATHINFO_EXTENSION) == 'flv')
		{
			$id = 'movie_' . AppContext::get_uid();
			return '<div class="media-content"><a class="video-player" href="' . Url::to_rel($matches[1]) . '" style="display:block;margin:auto;width:' . $matches[2] . 'px;height:' . $matches[3] . 'px;" id="' . $id .  '"></a></div><br />' .
			'<script><!--' . "\n" .
			'insertMoviePlayer(\'' . $id . '\');' .
			"\n" . '--></script>';
		}
		else
		{
			$poster = '';
			if (!empty($matches[4]))
			{
				$poster_type = new FileType(new File($matches[4]));
				$poster = $poster_type->is_picture() ? ' poster="' . $matches[4] . '"' : '';
			}

			foreach ($video_files as $video)
			{
				$video_file = new File($video);
				$type = new FileType($video_file);
				if ($type->is_video())
					$sources .= '<source src="' . Url::to_rel($video) . '" type="video/' . pathinfo($video, PATHINFO_EXTENSION) . '" />';
			}

			return '<div class="media-content"><video class="video-player" width="' . $matches[2] . '" height="' . $matches[3] . '"' . $poster . ' controls>' . $sources . '</video></div>';
		}
	}

	/**
	 * Inserts the javascript calls for the sound tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascript calls
	 */
	private static function process_sound_tag($matches)
	{
		return '<audio class="audio-player" controls><source src="' . Url::to_rel($matches[1]) . '" /></audio>';
	}

	private static function process_youtube_tag($matches)
	{
		preg_match('#(?<=docid=)[a-zA-Z0-9-]+(?=&)|(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=e\/)[^&\n]+(?=\?)|(?<=embed/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#', $matches[1], $url_matches);
		$video_id = isset($url_matches[0]) ? $url_matches[0] : '';
		return $video_id ? '<div class="media-content"><iframe class="youtube-player" type="text/html" width="' . $matches[2] . '" height="' . $matches[3] . '" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe></div>' : '';
	}

	private function parse_feed_tag()
	{
		$this->content = preg_replace_callback('`\[\[FEED((?: [a-z]+="[^"]+")*)\]\]([a-z]+)\[\[/FEED\]\]`uU', array(__CLASS__, 'inject_feed'), $this->content);
	}

	private static function inject_feed(array $matches)
	{
		$module = $matches[2];
		$args = self::parse_feed_tag_args($matches[1]);
		$name = !empty($args['name']) ? $args['name'] : Feed::DEFAULT_FEED_NAME;
		$cat = !empty($args['cat']) ? $args['cat'] : 0;
		$tpl = false;
		$number = !empty($args['number']) ? $args['number'] : 10;

		$result = '';

		try
		{
			$result = Feed::get_parsed($module, $name, $cat, $tpl, $number);
		}
		catch (Exception $e)
		{
		}

		if (!empty($result))
		{
			return $result;
		}
		else
		{
			$error = StringVars::replace_vars(LangLoader::get_message('feed_tag_error', 'editor-common'), array('module' => $module));
			return '<div class="message-helper error">' . $error . '</div>';
		}
	}

	private static function parse_feed_tag_args($matches)
	{
		$args = explode(' ', trim($matches));
		$result = array();

		foreach ($args as $arg)
		{
			$param = array();

			if (!preg_match('`([a-z]+)="([^"]+)"`uU', $arg, $param))
			{
				break;
			}

			$name = $param[1];
			$value = $param[2];

			if (in_array($name, array('name', 'cat', 'number')))
			{
				$result[$name] = $value;
			}
		}

		return $result;
	}
}
?>
