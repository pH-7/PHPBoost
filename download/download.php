<?php
/*##################################################
 *                               download.php
 *                            -------------------
 *   begin                : July 27, 2005
 *   copyright          : (C) 2005 Viarre R�gis
 *   email                : crowkait@phpboost.com
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

require_once('../includes/begin.php'); 
require_once('../download/download_begin.php');
require_once('../includes/header.php'); 

if( $file_id > 0 ) //Contenu
{
	$Template->Set_filenames(array('download' => '../templates/' . $CONFIG['theme'] . '/download/download.tpl'));

	//Commentaires
	$link_pop = "<a class=\"com\" href=\"#\" onclick=\"popup('" . HOST . DIR . transid("/includes/com.php?i=" . $file_id . "download") . "', 'download');\">";
	$link_current = '<a class="com" href="' . HOST . DIR . '/download/download' . transid('.php?cat=' . $category_id . '&amp;id=' . $file_id . '&amp;i=0', '-' . $category_id . '-' . $file_id . '.php?i=0') . '#download">';	
	$link = ($CONFIG['com_popup'] == '0') ? $link_current : $link_pop;
	
	$com_true = ($download_info['nbr_com'] > 1) ? $LANG['com_s'] : $LANG['com'];
	$com_false = $LANG['post_com'] . '</a>';
	$l_com = !empty($download_info['nbr_com']) ? $com_true . ' (' . $download_info['nbr_com'] . ')</a>' : $com_false;
	
	$Template->Assign_vars(array(
		'C_DISPLAY_DOWNLOAD' => true,
		'MODULE_DATA_PATH' => $Template->Module_data_path('download'),
		'IDURL' => $download_info['id'],
		'NAME' => $download_info['title'],
		'CONTENTS' => $download_info['contents'],
		'URL' => $download_info['url'],
		'DATE' => gmdate_format('date_format_short', $download_info['timestamp']),
		'SIZE' => ($download_info['size'] >= 1) ? $download_info['size'] . ' ' . $LANG['unit_megabytes'] : ($download_info['size'] * 1024) . ' ' . $LANG['unit_kilobytes'],
		'COUNT' => $download_info['count'],
		'THEME' => $CONFIG['theme'],
		'COM' => $link . $l_com,
		'LANG' => $CONFIG['lang'],
		'L_DESC' => $LANG['description'],
		'L_CAT' => $LANG['category'],
		'L_DATE' => $LANG['date'],
		'L_SIZE' => $LANG['size'],
		'L_TIMES' => $LANG['n_time'],
		'L_DOWNLOAD' => $DOWNLOAD_LANG['download'],
		'L_ALREADY_VOTED' => $LANG['already_vote']
	));
	
	//Affichage notation.
	include_once('../includes/note.class.php'); 
	$Note = new Note('download', $file_id, transid('download.php?cat=' . $category_id . '&amp;id=' . $file_id, 'category-' . $category_id . '-' . $file_id . '.php'), $CONFIG_DOWNLOAD['note_max'], '', NOTE_DISPLAY_NOTE);
	include_once('../includes/note.php');
	
	//Affichage commentaires.
	if( isset($_GET['i']) )
	{
		include_once('../includes/com.class.php'); 
		$Comments = new Comments('download', $file_id, transid('download.php?cat=' . $category_id . '&amp;id=' . $file_id . '&amp;i=%s', 'category-' . $category_id . '-' . $file_id . '.php?i=%s'));
		include_once('../includes/com.php');
	}
	
	$Template->Pparse('download');
}
else
{
	$Template->Set_filenames(array('download' => '../templates/' . $CONFIG['theme'] . '/download/download.tpl'));
	
	$Template->Assign_vars(array(
		'C_ADMIN' => $Member->Check_level(ADMIN_LEVEL),
		'U_ADMIN_CAT' => $category_id > 0 ? transid('admin_download_cat.php?edit=' . $category_id) : transid('admin_download_cat.php'),
		'C_DOWNLOAD_CAT' => true,
		'TITLE' => sprintf($DOWNLOAD_LANG['title_download'] . ($category_id > 0 ? ' - ' . $DOWNLOAD_CATS[$category_id]['name'] : ''))
	));
	
	//let's check if there are some subcategories
	$num_subcats = 0;
	foreach( $DOWNLOAD_CATS as $id => $value )
	{
		if( $id != 0 && $value['id_parent'] == $category_id )
			$num_subcats ++;
	}

	//listing of subcategories
	if( $num_subcats > 0 )
	{
		$Template->Assign_vars(array(
			'C_SUB_CATS' => true
		));	
		
		$i = 1;
		
		foreach( $DOWNLOAD_CATS as $id => $value )
		{
			//List of children categories
			if( $id != 0 && $value['visible'] && $value['id_parent'] == $category_id && (empty($value['auth']) || $Member->Check_auth($value['auth'], READ_CAT_DOWNLOAD)) )
			{
				if ( $i % $CONFIG_DOWNLOAD['nbr_column'] == 1 )
					$Template->Assign_block_vars('row', array());
				$Template->Assign_block_vars('row.list_cats', array(
					'ID' => $id,
					'NAME' => $value['name'],
					'WIDTH' => floor(100 / (float)$CONFIG_DOWNLOAD['nbr_column']),
					'SRC' => $value['icon'],
					'IMG_NAME' => addslashes($value['name']),
					'NUM_FILES' => sprintf(((int)$value['num_files'] > 1 ? $DOWNLOAD_LANG['num_files_plural'] : $DOWNLOAD_LANG['num_files_singular']), (int)$value['num_files']),
					'U_CAT' => transid('faq.php?id=' . $id, 'category-' . $id . '+' . url_encode_rewrite($value['name']) . '.php'),
					'U_ADMIN_CAT' => transid('admin_download_cat.php?edit=' . $id)
				));
				
				if( !empty($value['icon']) )
					$Template->Assign_vars(array(
						'C_CAT_IMG' => true
					));
					
				$i++;
			}
		}
	}
	
	//Contenu de la cat�gorie	
	$nbr_files = (int)$Sql->Query("SELECT COUNT(*) FROM ".PREFIX."download WHERE visible = 1 AND idcat = '" . $category_id . "'", __LINE__, __FILE__);
	
	if( $nbr_files > 0 )
	{
		$rewrited_title = $category_id > 0 ? url_encode_rewrite($DOWNLOAD_CATS[$category_id]['name']) : '';
		
		$Template->Assign_vars(array(
			'L_FILE' => $DOWNLOAD_LANG['file'],
			'L_SIZE' => $LANG['size'],
			'L_DATE' => $LANG['date'],
			'L_DOWNLOAD' => $DOWNLOAD_LANG['download'],
			'L_NOTE' => $LANG['note'],
			'L_COM' => $LANG['com'],
			'L_NOTE' => $DOWNLOAD_LANG['this_note'],
			'U_DOWNLOAD_ALPHA_TOP' => transid('.php?sort=alpha&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=alpha&amp;mode=desc'),
			'U_DOWNLOAD_ALPHA_BOTTOM' => transid('.php?sort=alpha&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?alpha&amp;mode=asc'),
			'U_DOWNLOAD_SIZE_TOP' => transid('.php?sort=size&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=size&amp;mode=desc'),
			'U_DOWNLOAD_SIZE_BOTTOM' => transid('.php?sort=size&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=size&amp;mode=asc'),
			'U_DOWNLOAD_DATE_TOP' => transid('.php?sort=date&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=date&amp;mode=desc'),
			'U_DOWNLOAD_DATE_BOTTOM' => transid('.php?sort=date&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=date&amp;mode=asc'),
			'U_DOWNLOAD_VIEW_TOP' => transid('.php?sort=view&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=view&amp;mode=desc'),
			'U_DOWNLOAD_VIEW_BOTTOM' => transid('.php?sort=view&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=view&amp;mode=asc'),
			'U_DOWNLOAD_NOTE_TOP' => transid('.php?sort=note&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=note&amp;mode=desc'),
			'U_DOWNLOAD_NOTE_BOTTOM' => transid('.php?sort=note&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=note&amp;mode=asc'),
			'U_DOWNLOAD_COM_TOP' => transid('.php?sort=com&amp;mode=desc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=com&amp;mode=desc'),
			'U_DOWNLOAD_COM_BOTTOM' => transid('.php?sort=com&amp;mode=asc&amp;cat=' . $category_id, '-' . $category_id . '+' . $rewrited_title . '.php?sort=com&amp;mode=asc')
		));		
		
		$get_sort = !empty($_GET['sort']) ? trim($_GET['sort']) : '';	
		switch($get_sort)
		{
			case 'alpha' : 
			$sort = 'title';
			break;	
			case 'size' : 
			$sort = 'size';
			break;			
			case 'date' : 
			$sort = 'timestamp';
			break;		
			case 'view' : 
			$sort = 'count';
			break;		
			case 'note' :
			$sort = 'note/' . $CONFIG_DOWNLOAD['note_max'];
			break;
			case 'com' :
			$sort = 'nbr_com';
			break;		
			default :
			$sort = 'timestamp';
		}
		
		$get_mode = !empty($_GET['mode']) ? trim($_GET['mode']) : '';
		$mode = ($get_mode == 'asc' || $get_mode == 'desc') ? strtoupper(trim($_GET['mode'])) : 'DESC';	
		$unget = (!empty($get_sort) && !empty($mode)) ? '?sort=' . $get_sort . '&amp;mode=' . $get_mode : '';
			
		//On cr�e une pagination si le nombre de fichiers est trop important.
		include_once('../includes/pagination.class.php'); 
		$Pagination = new Pagination();
		
		//Notes
		include_once('../includes/note.class.php');
		$Note = new Note(null, null, null, null, '', NOTE_NO_CONSTRUCT);
			
		$Template->Assign_vars(array(
			'PAGINATION' => $Pagination->Display_pagination('download' . transid('.php' . (!empty($unget) ? $unget . '&amp;' : '?') . 'cat=' . $category_id . '&amp;p=%d', '-' . $category_id . '-0-%d+' . $rewrited_title . '.php' . $unget), $nbr_files, 'p', $CONFIG_DOWNLOAD['nbr_file_max'], 3),
			'C_FILES' => true,
			'C_DESCRIPTION' => !empty($DOWNLOAD_CATS[$category_id]['contents']) || ($category_id == 0 && !empty($CONFIG_DOWNLOAD['root_contents'])),
			'DESCRIPTION' => $category_id > 0 ? second_parse($DOWNLOAD_CATS[$category_id]['contents']) : second_parse($CONFIG_DOWNLOAD['root_contents'])
			));

		$result = $Sql->Query_while("SELECT id, title, timestamp, size, count, note, nbrnote, nbr_com, image, short_contents
		FROM ".PREFIX."download
		WHERE visible = 1 AND idcat = '" . $category_id . "'
		ORDER BY " . $sort . " " . $mode . 
		$Sql->Sql_limit($Pagination->First_msg($CONFIG_DOWNLOAD['nbr_file_max'], 'p'), $CONFIG_DOWNLOAD['nbr_file_max']), __LINE__, __FILE__);
		while( $row = $Sql->Sql_fetch_assoc($result) )
		{
			$Template->Assign_block_vars('file', array(			
				'NAME' => $row['title'],
				'IMG_NAME' => str_replace('"', '\"', $row['title']),
				'C_DESCRIPTION' => !empty($row['short_contents']),
				'DESCRIPTION' => second_parse($row['short_contents']),
				'DATE' => sprintf($DOWNLOAD_LANG['add_on_date'], gmdate_format('date_format_short', $row['timestamp'])),
				'COUNT_DL' => sprintf($DOWNLOAD_LANG['downloaded_n_times'], $row['count']),
				'NOTE' => $row['nbrnote'] > 0 ? $Note->Display_note((int)$row['note'], $CONFIG_DOWNLOAD['note_max'], 5) : '<em>' . $LANG['no_note'] . '</em>',
				'SIZE' => ($row['size'] >= 1) ? number_round($row['size'], 1) . ' ' . $LANG['unit_megabytes'] : (number_round($row['size'], 1) * 1024) . ' ' . $LANG['unit_kilobytes'],
				'COMS' => (int)$row['nbr_com'] > 1 ? sprintf($DOWNLOAD_LANG['num_coms'], $row['com']) : sprintf($DOWNLOAD_LANG['num_com'], $row['nbr_com']),
				'C_IMG' => !empty($row['image']),
				'IMG' => $row['image'],
				'U_DOWNLOAD_LINK' => transid('download/admin_download.php?cat=' . $category_id . '&amp;id=' . $row['id'], 'download-' . $row['id'] . '+' . url_encode_rewrite($row['title']) . '.php')
			));
		}
		$Sql->Close($result);
	}
	else
	{
		$Template->Assign_vars(array(
			'L_NO_FILE_THIS_CATEGORY' => $DOWNLOAD_LANG['none_download'],
			'C_NO_FILE' => true
		));
	}
		
	$Template->Pparse('download');
}
	
require_once('../includes/footer.php'); 

?>
