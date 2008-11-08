<?php
/*##################################################
*                           tinymce_parser.class.php
*                            -------------------
*   begin                : July 3 2008
*   copyright            : (C) 2008 Benoit Sautel
*   email                :  ben.popeye@phpboost.com
*
*   
###################################################
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
* 
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
###################################################*/

require_once(PATH_TO_ROOT . '/kernel/framework/content/content_parser.class.php');

class TinyMCEParser extends ContentParser
{
	function TinyMCEParser()
	{
		parent::ContentParser();
	}
	
	//Fonction qui parse le contenu
	function parse()
	{
		global $User;
		
		$this->parsed_content = $this->content;
		
		//On supprime d'abord toutes les occurences de balises CODE que nous r�injecterons � la fin pour ne pas y toucher
		if( !in_array('code', $this->forbidden_tags) )
			$this->_pick_up_tag('code', '=[a-z0-9-]+(?:,(?:0|1)(?:,0|1)?)?');
		
		//On pr�l�ve tout le code HTML afin de ne pas l'alt�rer
		if( $User->check_auth($this->html_auth, 1) )
			$this->_pick_up_tag('html');
		
		//On enl�ve toutes les entit�s HTML rajout�es par TinyMCE
		$this->parsed_content = html_entity_decode($this->parsed_content);
			
		//On casse toutes les balises HTML (sauf celles qui ont �t� pr�lev�es dans le code et la balise HTML)
		$this->parsed_content = htmlspecialchars($this->parsed_content, ENT_NOQUOTES);
		
		//Parse the HTML code generated by TinyMCE
		$this->_parse_tinymce_formatting();
		
		//Parse the HTML tables generated by TinyMCE
		if( !in_array('table', $this->forbidden_tags) )
			$this->_parse_tables();
		
		//Replace smilies code by smilies images
		$this->_parse_smilies();
		
		//Parse the tags which are not supported by TinyMCE but expected in BBCode
		$this->_parse_bbcode_tags();
		
		//If it remains </p> (it seems to be a bug in TinyMCE)
		$this->parsed_content = str_replace('&lt;/p&gt;', '', $this->parsed_content);
		
		//Si on n'est pas � la racine du site plus un dossier, on remplace les liens relatifs g�n�r�s par le BBCode
		if( PATH_TO_ROOT != '..' )
			$this->parsed_content = str_replace('"../', '"' . PATH_TO_ROOT . '/', $this->parsed_content);
		
		//On remet le code HTML mis de c�t�
		if( !empty($this->array_tags['html']) )
		{
			$this->array_tags['html'] = array_map(create_function('$string', 'return str_replace("[html]", "<!-- START HTML -->\n", str_replace("[/html]", "\n<!-- END HTML -->", $string));'), $this->array_tags['html']);
			
			//If we don't protect the HTML code inserted into the tags code and HTML TinyMCE will parse it!
			$this->array_tags['html'] = array_map(array('TinyMCEParser', '_clear_html_and_code_tag'), $this->array_tags['html']);
		
			$this->_reimplant_tag('html');
		}
		
		//On r�ins�re les fragments de code qui ont �t� pr�velev�s pour ne pas les consid�rer
		if( !empty($this->array_tags['code']) )
		{
			$this->array_tags['code'] = array_map(create_function('$string', 'return preg_replace(\'`^\[code(=.+)?\](.+)\[/code\]$`isU\', \'[[CODE$1]]$2[[/CODE]]\', $string);'), $this->array_tags['code']);
			
			//If we don't protect the HTML code inserted into the tags code and HTML TinyMCE will parse it!
			$this->array_tags['code'] = array_map(array('TinyMCEParser', '_clear_html_and_code_tag'), $this->array_tags['code']);
			
			$this->_reimplant_tag('code');
		}
	}
	
	## Protected ##
	//Parse la balise table de tinymce pour le bbcode.
	function _parse_table_tag($matches)
	{
		$table_properties = $matches[1];
		$style_properties = '';
		
		$temp_array = array();
		
		//Border ?
		if( strpos($table_properties, 'border') !== false )
		{
			preg_match('`border="([0-9]+)"`iU', $table_properties, $temp_array);
			$style_properties .= 'border:' . $temp_array[1] . 'px;';
		}
		
		//Width ?
		if( strpos($table_properties, 'width') !== false )
		{
			preg_match('`width="([0-9]+)"`iU', $table_properties, $temp_array);
			$style_properties .= 'width:' . $temp_array[1] . 'px;';
		}
		
		//Height ?
		if( strpos($table_properties, 'height') !== false )
		{
			preg_match('`height="([0-9]+)"`iU', $table_properties, $temp_array);
			$style_properties .= 'height:' . $temp_array[1] . 'px;';
		}
		
		//Alignment
		if( strpos($table_properties, 'align') !== false )
		{
			preg_match('`align="([^"]+)"`iU', $table_properties, $temp_array);
			if( $temp_array[1] == 'center' )
				$style_properties .= 'margin:auto;';
			elseif( $temp_array[1] == 'right' )
				$style_properties .= 'margin-left:auto;';
		}
		
		//Style ?
		if( strpos($table_properties, 'style') !== false )
		{
			if( preg_match('`style="([^"])"`iU', $table_properties, $temp_array) )
				$style_properties .= $temp_array[1];
		}
		
		return '<table class="bb_table"' . (!empty($style_properties) ? ' style="' . $style_properties . '"' : '') . '>' . $matches[2] . '</table>';
	}
	
	//Parse la balise table de tinymce pour le bbcode.
	function _parse_col_tag($matches)
	{
		$tag = $matches[1] == 'th' ? 'th' : 'td';
		$bbcode_tag = $tag == 'th' ? 'head' : 'col';
		$col_properties = $matches[2];
		$col_new_properties = '';
		$col_style = '';
		
		$temp_array = array();
		
		//Colspan ?
		if( strpos($col_properties, 'colspan') !== false )
		{
			preg_match('`colspan="([0-9]+)"`iU', $col_properties, $temp_array);
			$col_new_properties .= ' colspan="' . $temp_array[1] . '"';
		}
		
		//Rowspan ?
		if( strpos($col_properties, 'rowspan') !== false )
		{
			preg_match('`rowspan="([0-9]+)"`iU', $col_properties, $temp_array);
			$col_new_properties .= ' rowspan="' . $temp_array[1] . '"';
		}
		
		//Alignment
		if( strpos($col_properties, 'align') !== false )
		{
			preg_match('`align="([^"]+)"`iU', $col_properties, $temp_array);
			$col_style .= 'text-align:' . $temp_array[1] . ';';
		}
		
		//Style ?
		if( strpos($col_properties, 'style') !== false )
		{
			preg_match('`style="([^"])"`iU', $col_properties, $temp_array);
			$col_style .= ' style="' . $temp_array[1] . ' ' . $col_style . '"';
		}
		elseif( !empty($col_style) )
			$col_style = ' style="' . $col_style . '"';
		
		return '<' . $tag . ' class="bb_table_' . $bbcode_tag . '"' . $col_new_properties . $col_style . '>' . $matches[3] . '</' . $tag . '>';
	}
	
	//Function which parses all the features provided by TinyMCE
	function _parse_tinymce_formatting()
	{
		global $LANG;
		
		//Modification de quelques tags HTML envoy�s par TinyMCE
		$this->parsed_content = str_replace(array('&amp;nbsp;&amp;nbsp;&amp;nbsp;', '&amp;gt;', '&amp;lt;', '&lt;br /&gt;', '&lt;br&gt;', '&amp;nbsp;'), array("\t", '&gt;', '&lt;', "<br />\r\n", "<br />\r\n", ' '), $this->parsed_content);
		
		$array_preg = array(
			'`&lt;div&gt;(.+)&lt;/div&gt;`isU',
			'`&lt;p&gt;(.+)&lt;/p&gt;`isU',
			'`&lt;h5&gt;(.+)&lt;/h5&gt;`isU',
			'`&lt;h6&gt;(.+)&lt;/h6&gt;`isU'
		);
		$array_preg_replace = array(
			'$1' . "\n<br />",
			'$1' . "\n<br />",
			'<span style="font-size: 10px;">$1</span><br />',
			'<span style="font-size: 8px;">$1</span><br />',
		);
		
		//Strong tag
		if( !in_array('b', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;strong&gt;(.+)&lt;/strong&gt;`isU');
			array_push($array_preg_replace, '<strong>$1</strong>');
		}
		//italic tag
	   	if( !in_array('i', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;em&gt;(.+)&lt;/em&gt;`isU');
			array_push($array_preg_replace, '<em>$1</em>');
		}
		//Underline tag
		if( !in_array('u', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;span style="text-decoration: underline;"&gt;(.+)&lt;/span&gt;`isU');
			array_push($array_preg_replace, '<span style="text-decoration: underline;">$1</span>');
		}
		//Strike tag
		if( !in_array('s', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;span style="text-decoration: line-through;"&gt;(.+)&lt;/span&gt;`isU');
			array_push($array_preg_replace, '<strike>$1</strike>');
		}
		//Link tag
		if( !in_array('url', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;a href="([^"]+)"&gt;(.+)&lt;/a&gt;`isU');
			array_push($array_preg_replace, '<a href="$1">$2</a>');
		}
		//Sub tag
		if( !in_array('sub', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;sub&gt;(.+)&lt;/sub&gt;`isU');
			array_push($array_preg_replace, '<sub>$1</sub>');
		}
		//Sup tag
		if( !in_array('sup', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;sup&gt;(.+)&lt;/sup&gt;`isU');
			array_push($array_preg_replace, '<sup>$1</sup>');
		}
		//Pre tag
		if( !in_array('pre', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;pre&gt;(.+)(<br />[\s]*)*&lt;/pre&gt;`isU');
			array_push($array_preg_replace, '<pre>$1</pre>');
		}
		//Font tag
		if( !in_array('color', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;span style="color: ([^"]+);"&gt;(.+)&lt;/span&gt;`isU');
			array_push($array_preg_replace, '<span style="color:$1;">$2</span>');
		}
		//Background color tag
		if( !in_array('bgcolor', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;span style="background-color: ([^"]+)"&gt;(.+)&lt;/span&gt;`isU');
			array_push($array_preg_replace, '<span style="background-color:$1;">$2</span>');
		}
		//Font tag
		if( !in_array('font', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;font face="([^"]+)"&gt;(.+)&lt;/font&gt;`isU');
			array_push($array_preg_replace, '<span style="font-family: $1;">$2</span>');
		}
		//Align tag
		if( !in_array('align', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;p style="text-align: (left|right|center|justify);"&gt;(.+)&lt;/p&gt;`isU');
			array_push($array_preg_replace, '<p style="text-align:$1">$2</p>' . "\n");
		}
		//Anchor tag
		if( !in_array('anchor', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;a(?: class="[^"]+")?(?: title="[^"]+" )? name="([^"]+)"&gt;(.*)&lt;/a&gt;`isU');
			array_push($array_preg_replace, '<span id="$1">$2</span>');
		}
		//List tag
		if( !in_array('list', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;ul&gt;(.+)&lt;/ul&gt;`isU');
			array_push($array_preg_replace, '<ul class="bb_ul">' . "\n" .'$1</ul>');
			array_push($array_preg, '`&lt;ol&gt;(.+)&lt;/ol&gt;`isU');
			array_push($array_preg_replace, '<ol class="bb_ol">' . "\n" .'$1</ol>');
			array_push($array_preg, '`&lt;li&gt;(.*)&lt;/li&gt;`isU');
			array_push($array_preg_replace, '<li class="bb_li">$1</li>' . "\n");
		}
		//Title tag
		if( !in_array('title', $this->forbidden_tags) )
		{
			//Title 1
			array_push($array_preg, '`&lt;h1[^&]*&gt;(.+)&lt;/h1&gt;`isU');
			array_push($array_preg_replace, '<h3 class="title1">$1</h3>' . "\r\n<br />");
			//Title 2
			array_push($array_preg, '`&lt;h2[^&]*&gt;(.+)&lt;/h2&gt;`isU');
			array_push($array_preg_replace, '<h3 class="title2">$1</h3>' . "\r\n<br />");
			//Title 3
			array_push($array_preg, '`&lt;h3[^&]*&gt;(.+)(<br />[\s]*)?&lt;/h3&gt;`isU');
			array_push($array_preg_replace, '<br /><h4 class="stitle1">$1</h4><br />' . "\r\n<br />");
			//Title 4
			array_push($array_preg, '`&lt;h4[^&]*&gt;(.+)(<br />[\s]*)?&lt;/h4&gt;`isU');
			array_push($array_preg_replace, '<br /><h4 class="stitle2">$1</h4><br />' . "\r\n<br />");
		}
		//Flash tag
		if( !in_array('swf', $this->forbidden_tags) )
		{
			array_push($array_preg, '`&lt;object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="([^"]+)%?" height="([^"]+)%?"&gt;&lt;param name="movie" value="([^"]+)"(.*)&lt;/object&gt;`isU');
			array_push($array_preg_replace, '<object type="application/x-shockwave-flash" data="$3" width="$1" height="$2">
		<param name="allowScriptAccess" value="never" />
		<param name="play" value="true" />
		<param name="movie" value="$3" />
		<param name="menu" value="false" />
		<param name="quality" value="high" />
		<param name="scalemode" value="noborder" />
		<param name="wmode" value="transparent" />
		<param name="bgcolor" value="#000000" />
		</object>');
		}
		
		//Replacement
		$this->parsed_content = preg_replace($array_preg, $array_preg_replace, $this->parsed_content);	
		
		//Tags which are useless
		$array_str = array( 
			'&lt;address&gt;', '&lt;/address&gt;', '&lt;caption&gt;', '&lt;/caption&gt;', '&lt;tbody&gt;', '&lt;/tbody&gt;'
		);
		
		$this->parsed_content = str_replace($array_str, '', $this->parsed_content);
		
		//callback replacements
		// size tag
		if( !in_array('size', $this->forbidden_tags) )
			$this->parsed_content = preg_replace_callback('`&lt;span style="font-size: ([a-z0-9-]+);"&gt;(.+)&lt;/span&gt;`isU', array(&$this, '_parse_size_tag'), $this->parsed_content);
		
		//image tag
		if( !in_array('image', $this->forbidden_tags) )
			$this->parsed_content = preg_replace_callback('`&lt;img src="([^"]+)"(?: border="[^"]*")? alt="[^"]*"(?: hspace="[^"]*")?(?: vspace="[^"]*")?(?: width="[^"]*")?(?: height="[^"]*")?(?: align="(top|middle|bottom)")? /&gt;`is', create_function('$img', '$align = \'\'; if( !empty($img[2]) ) $align = \'=\' . $img[2]; return \'<img src="\' . $img[1] . \'" alt="" class="valign_"\' . $align . \' />\';'), $this->parsed_content);
		
		//indent tag
		if( !in_array('indent', $this->forbidden_tags) )
			$this->parsed_content = preg_replace_callback('`&lt;p style="padding-left: ([0-9]+)px;"&gt;(.+)&lt;/p&gt;`isU', array(&$this, '_parse_indent_tag'), $this->parsed_content);
		
		//Line tag
		if( !in_array('line', $this->forbidden_tags) )
			$this->parsed_content = str_replace('&lt;hr /&gt;', '<hr class="bb_hr" />', $this->parsed_content);
		
		//Quote tag
		if( !in_array('quote', $this->forbidden_tags) )
			$this->parsed_content = preg_replace('`&lt;blockquote&gt;(.+)(?:<br />[\s]*)*&lt;/blockquote&gt;`isU', '<span class="text_blockquote">' . $LANG['quotation'] . ':</span><div class="blockquote">$1</div>', $this->parsed_content);
		
		//Font tag
		if( !in_array('font', $this->forbidden_tags) )
			$this->parsed_content = preg_replace_callback('`&lt;span style="font-family: ([a-z, 0-9-]+);"&gt;(.*)&lt;/span&gt;`isU', array(&$this, '_parse_font_tag'), $this->parsed_content );
	}
	
	//Function which parses tables
	function _parse_tables()
	{
		//Tables
		$content_contains_table = false;
		while( preg_match('`&lt;table([^&]*)&gt;(.+)&lt;/table&gt;`is', $this->parsed_content) )
		{
			$this->parsed_content = preg_replace_callback('`&lt;table([^&]*)&gt;(.+)&lt;/table&gt;`isU', array(&$this, '_parse_table_tag'), $this->parsed_content);
			$content_contains_table = true;
		}
		
		if( $content_contains_table )
		{
			$this->parsed_content = preg_replace('`&lt;tr&gt;(.*)&lt;/tr&gt;`isU', '<tr class="bb_table_row">$1</tr>', $this->parsed_content);
			
			//Rows
			while( preg_match('`&lt;td|h([^&]*)&gt;(.+)&lt;/td|h&gt;`is', $this->parsed_content) )
			{
				$this->parsed_content = preg_replace_callback('`&lt;(td)([^&]*)&gt;(.+)&lt;/td&gt;`isU', array(&$this, '_parse_col_tag'), $this->parsed_content);
				$this->parsed_content = preg_replace_callback('`&lt;(th)([^&]*)&gt;(.+)&lt;/th&gt;`isU', array(&$this, '_parse_col_tag'), $this->parsed_content);
				$content_contains_table = true;
			}
		}
	}

	//Function which parses smilies
	function _parse_smilies()
	{
		//Smilies
		@include(PATH_TO_ROOT . '/cache/smileys.php');
		if( !empty($_array_smiley_code) )
		{
			//Cr�ation du tableau de remplacement.
			foreach($_array_smiley_code as $code => $img)
			{
				$smiley_code[] = '`(?<!&[a-z]{4}|&[a-z]{5}|&[a-z]{6}|")(' . str_replace('\'', '\\\\\\\'', preg_quote($code)) . ')`';
				$smiley_img_url[] = '<img src="../images/smileys/' . $img . '" alt="' . addslashes($code) . '" class="smiley" />';
			}
			$this->parsed_content = preg_replace($smiley_code, $smiley_img_url, $this->parsed_content);
		}
	}
	
	//function which parses the bbcode tags (which correspond to the tags not supported by TinyMCE)
	function _parse_bbcode_tags()
	{
		global $LANG;
		
		$array_preg = array(
			'pre' => '`\[pre\](.+)\[/pre\]`isU',
			'float' => '`\[float=(left|right)\](.+)\[/float\]`isU',
			'acronym' => '`\[acronym=([^\n[\]<]+)\](.*)\[/acronym\]`isU',
			'style' => '`\[style=(success|question|notice|warning|error)\](.+)\[/style\]`isU',
			'movie' => '`\[movie=([0-6][0-9]{0,2}),([0-6][0-9]{0,2})\](((?:(?:\.?\./)+|(?:https?|ftps?)+://([a-z0-9-]+\.)*[a-z0-9-]+\.[a-z]{2,4})+(?:[a-z0-9~_-]+/)*[a-z0-9_+.:?/=#%@&;,-]*))\[/movie\]`iU',
			'sound' => '`\[sound\]((?:(?:\.?\./)+|(?:https?|ftps?)+://([a-z0-9-]+\.)*[a-z0-9-]+\.[a-z]{2,4})+(?:[a-z0-9~_-]+/)*[a-z0-9_-]+\.mp3)\[/sound\]`iU',
			'math' => '`\[math\](.+)\[/math\]`iU',
			'url' => '`(\s+)((?:https?|ftps?)+://(?:[a-z0-9-]+\.)*[a-z0-9-]+(?:\.[a-z]{2,4})?(?::[0-9]{1,5})?/?(?:[a-z0-9~_-]+/)*[a-z0-9_+.:?/=#%@&;,-]*)(\s+)`isU', 
			'url2' => '`(\s+)((?:www\.(?:[a-z0-9-]+\.)*[a-z0-9-]+(?:\.[a-z]{2,4})?(?::[0-9]{1,5})?/?)(?:[a-z0-9~_-]+/)*[a-z0-9_+.:?/=#%@&;,-]*)(\s+)`i',
			'mail' => '`(\s+)([a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})(\s+)`i',
		);
		
		$array_preg_replace = array(
			'pre' => "<pre>$1</pre>",
			'float' => "<p class=\"float_$1\">$2</p>",	
			'acronym' => "<acronym title=\"$1\" class=\"bb_acronym\">$2</acronym>",
			'style' => "<span class=\"$1\">$2</span>",
			'movie' => "<object type=\"application/x-shockwave-flash\" data=\"../kernel/data/movieplayer.swf\" width=\"$1\" height=\"$2\">
		<param name=\"FlashVars\" value=\"flv=$3&width=$1&height=$2\" />
		<param name=\"allowScriptAccess\" value=\"never\" />
		<param name=\"play\" value=\"true\" />
		<param name=\"movie\" value=\"$1\" />
		<param name=\"menu\" value=\"false\" />
		<param name=\"quality\" value=\"high\" />
		<param name=\"scalemode\" value=\"noborder\" />
		<param name=\"wmode\" value=\"transparent\" />
		<param name=\"bgcolor\" value=\"#FFFFFF\" />
		</object>",
			'sound' => "<object type=\"application/x-shockwave-flash\" data=\"../kernel/data/dewplayer.swf?son=$1\" width=\"200\" height=\"20\">
		<param name=\"allowScriptAccess\" value=\"never\" />
		<param name=\"play\" value=\"true\" />
		<param name=\"movie\" value=\"../kernel/data/dewplayer.swf?son=$1\" />
		<param name=\"menu\" value=\"false\" />
		<param name=\"quality\" value=\"high\" />
		<param name=\"scalemode\" value=\"noborder\" />
		<param name=\"wmode\" value=\"transparent\" />
		<param name=\"bgcolor\" value=\"#FFFFFF\" />
		</object>",
			'math' => '[[MATH]]$1[[/MATH]]',
			'url' => "$1<a href=\"$2\">$2</a>$3", 
			'url2' => "$1<a href=\"http://$2\">$2</a>$3",
			'mail' => "$1<a href=\"mailto:$2\">$2</a>$3",
		);
		
		//Suppression des remplacements des balises interdites.
		if( !empty($this->forbidden_tags) )
		{
			//Si on interdit les liens, on ajoute toutes les mani�res par lesquelles elles peuvent passer
			if( in_array('url', $this->forbidden_tags) )
				$this->forbidden_tags[] = 'url2';
			
			$other_tags = array('table', 'quote', 'hide', 'indent', 'list'); 
			foreach($this->forbidden_tags as $key => $tag)
			{
				//Balise interdite : on la supprime
				if( in_array($tag, $other_tags) )
				{
					$array_preg[$tag] = '`\[' . $tag . '.*\](.+)\[/' . $tag . '\]`isU';
					$array_preg_replace[$tag] = "$1";
				}
				else
				{	
					unset($array_preg[$tag]);
					unset($array_preg_replace[$tag]);
				}
			}	
		}
		
		//Remplacement : on parse les balises classiques
		$this->parsed_content = preg_replace($array_preg, $array_preg_replace, $this->parsed_content);
		
			
		##Parsage des balises imbriqu�es.
		//Texte cach�
		$this->_parse_imbricated('[hide]', '`\[hide\](.+)\[/hide\]`sU', '<span class="text_hide">' . $LANG['hide'] . ':</span><div class="hide" onclick="bb_hide(this)"><div class="hide2">$1</div></div>', $this->parsed_content);
		
		//Bloc HTML
		$this->_parse_imbricated('[block]', '`\[block\](.+)\[/block\]`sU', '<div class="bb_block">$1</div>', $this->parsed_content);
		$this->_parse_imbricated('[block style=', '`\[block style="([^"]+)"\](.+)\[/block\]`sU', '<div class="bb_block" style="$1">$2</div>', $this->parsed_content);
		
		//Bloc de formulaire
		$this->_parse_imbricated('[fieldset', '`\[fieldset(?: legend="(.*)")?(?: style="([^"]*)")?\](.+)\[/fieldset\]`sU', '<fieldset class="bb_fieldset" style="$2"><legend>$1</legend>$3</fieldset>', $this->parsed_content);

		//Liens vers des articles de Wikip�dia
		$this->parsed_content = preg_replace_callback('`\[wikipedia(?: page="([^"]+)")?(?: lang="([a-z]+)")?\](.+)\[/wikipedia\]`isU', array(&$this, '_parse_wikipedia_links'), $this->parsed_content);
	}
	
	//Interpr�te les liens vers des pages de wikip�dia
	function _parse_wikipedia_links($matches)
	{
		global $LANG;
		
		//Langue
		$lang = $LANG['wikipedia_subdomain'];
		if( !empty($matches[2]))
			$lang = $matches[2];
		
		$page_url = !empty($matches[1]) ? $matches[1] : $matches[3];
		
		return '<a href="http://' . $lang . '.wikipedia.org/wiki/' . $page_url . '" class="wikipedia_link">' . $matches[3] . '</a>';	
	}
	
	// Handler which processes the indentation tag
	function _parse_indent_tag($matches)
	{
		if( (int)$matches[1] > 0 )
		{
			$nbr_indent = (int)$matches[1] / 30;
			return str_repeat('<div class="indent">', $nbr_indent) . $matches[2] . str_repeat('</div>', $nbr_indent);
		}
		else
			return $matches[2];
	}
	
	//Handler which processes the size tag
	function _parse_size_tag($matches)
	{
		$size = 0;
		//We retrieve the size (in pt)
		switch($matches[1])
		{
			case 'xx-small':
				$size = 8;
				break;
			case 'x-small':
				$size = 10;
				break;
			case 'small':
				$size = 12;
				break;
			case 'medium':
				$size = 14;
				break;
			case 'large':
				$size = 18;
				break;
			case 'x-large':
				$size = 24;
				break;
			case 'xx-large':
				$size = 36;
				break;
			default:
				$size = 0;
		}
		//If the size is known, we put the HTML code and convert the size into pixels
		if( $size > 0 )
			return '<span style="font-size: ' . ($size / 0.75) . 'px;">' . $matches[2] . '</span>';
		else
			return $matches[2];
	}
	
	// Handler which treats the font size
	function _parse_font_tag($matches)
	{
		$fonts_array = array(
			'trebuchet ms,geneva' => 'geneva',
			'comic sans ms,sans-serif', 'optima',
			'andale mono,times' => 'times',
			'arial,helvetica,sans-serif' => 'arial',
			'arial black,avant garde' => 'arial',
			'book antiqua,palatino' => 'optima',
			'courier new,courier' => 'courier new',
			'georgia,palatino' => 'optima',
			'helvetica' => 'arial',
			'impact,chicago' => 'arial',
			'symbol' => 'times',
			'tahoma,arial,helvetica,sans-serif' => 'arial',
			'terminal,monaco' => 'courier new',
			'times new roman,times' => 'times',
			'verdana,geneva' => 'arial',
			'webdings' => 'times',
			'wingdings,zapf dingbats' => 'times'
		);
		
		if( !empty($fonts_array[$matches[1]]) )
			return '<span style="font-family: ' . $fonts_array[$matches[1]] . ';">' . $matches[2] . '</span>';
		else
			return $matches[2];
	}
	
	//Handler which clears the HTML code which is in the code and HTML tags
	function _clear_html_and_code_tag($var)
	{
		$var = preg_replace('`</p>\s*<p>`i', "\r\n", $var);
		$var = str_replace('<br />', "\r\n", $var);
		$var = html_entity_decode($var);
		return $var;
	}
}

?>