<?php
/*##################################################
 *                                search.php
 *                            -------------------
 *   begin                : May 18 2006
 *   copyright          : (C) 2006 Viarre R�gis
 *   email                : crowkait@phpboost.com
 *
 *   
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
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

require_once('../includes/begin.php'); 
require_once('../forum/forum_begin.php');
require_once('../forum/forum_tools.php');

$Speed_bar->Add_link($CONFIG_FORUM['forum_name'], 'index.php' . SID);
$Speed_bar->Add_link($LANG['title_search'], '');
define('TITLE', $LANG['title_forum'] . ' - ' . $LANG['title_search']);
require_once('../includes/header.php');

$search = !empty($_POST['search']) ? securit($_POST['search']) : '';
$idcat = !empty($_POST['idcat']) ? numeric($_POST['idcat']) : -1;
$time = !empty($_POST['time']) ? numeric($_POST['time']) * 3600 * 24 : 0; //X jour en secondes.
$where = !empty($_POST['where']) ? securit($_POST['where']) : '';
$colorate_result = !empty($_POST['colorate_result']) ? true : false;
$valid_search = !empty($_POST['valid_search']) ? securit($_POST['valid_search']) : '';

$Template->Set_filenames(array(
	'search' => '../templates/' . $CONFIG['theme'] . '/forum/forum_search.tpl',
	'forum_top' => '../templates/' . $CONFIG['theme'] . '/forum/forum_top.tpl',
	'forum_bottom' => '../templates/' . $CONFIG['theme'] . '/forum/forum_bottom.tpl'
));

$Template->Assign_vars(array(
	'FORUM_NAME' => $CONFIG_FORUM['forum_name'],
	'MODULE_DATA_PATH' => $Template->Module_data_path('forum'),
	'LANG' => $CONFIG['lang'],
	'SID' => SID,
	'SEARCH' => $search,
	'SELECT_CAT' => forum_list_cat(), //Retourne la liste des cat�gories, avec les v�rifications d'acc�s qui s'imposent.
	'CONTENTS_CHECKED' => ($where == 'contents' || empty($where)) ? 'checked="checked"' : '',
	'TITLE_CHECKED' => ($where == 'title') ? 'checked="checked"' : '',
	'ALL_CHECKED' => ($where == 'all') ? 'checked="checked"' : '',
	'COLORATE_RESULT' => ($colorate_result || empty($where)) ? 'checked="checked"' : '',
	'U_FORUM_CAT' => '<a href="search.php' . SID . '">' . $LANG['search'] . '</a>',
	'U_CHANGE_CAT' => 'search.php' . SID,	
	'U_ONCHANGE' => "'forum" . transid(".php?id=' + this.options[this.selectedIndex].value + '", "-' + this.options[this.selectedIndex].value + '.php") . "'",	
	'L_FORUM_INDEX' => $LANG['forum_index'],
	'L_REQUIRE_TEXT' => $LANG['require_text'],
	'L_SEARCH_FORUM' => $LANG['search_forum'],
	'L_KEYWORDS' => $LANG['keywords'],
	'L_OPTIONS' => $LANG['options'],
	'L_DATE' => $LANG['date'],
	'L_DAY' => $LANG['day'],
	'L_DAYS' => $LANG['day_s'],
	'L_ALL' => $LANG['all'],
	'L_MONTH' => $LANG['month'],
	'L_TOPIC' => $LANG['topic'],
	'L_YEAR' => $LANG['year'],
	'L_CATEGORY' => $LANG['category'],
	'L_TITLE' => $LANG['title'],
	'L_CONTENTS' => $LANG['contents'],
	'L_RELEVANCE' => $LANG['relevance'],
	'L_COLORATE_RESULT' => $LANG['colorate_result'],
	'L_SEARCH' => $LANG['search'],
	'L_ADVANCED_SEARCH' => $LANG['advanced_search'],
	'L_ON' => $LANG['on']	
));

$auth_cats = '';
if( is_array($CAT_FORUM) )
{	
	foreach($CAT_FORUM as $id => $key)
	{
		if( !$Member->Check_auth($CAT_FORUM[$id]['auth'], READ_CAT_FORUM) )
			$auth_cats .= $id . ',';
	}
}
$auth_cats_select = !empty($auth_cats) ? " AND id NOT IN (" . trim($auth_cats, ',') . ")" : '';

$selected = ($idcat == '-1') ? ' selected="selected"' : '';	
$Template->Assign_block_vars('cat', array(
	'CAT' => '<option value="-1"' . $selected . '>' . $LANG['all'] . '</option>'
));	
$result = $Sql->Query_while("SELECT id, name, level
FROM ".PREFIX."forum_cats 
WHERE aprob = 1 " . $auth_cats_select . "
ORDER BY id_left", __LINE__, __FILE__);
while( $row = $Sql->Sql_fetch_assoc($result) )
{	
	$margin = ($row['level'] > 0) ? str_repeat('----------', $row['level']) : '----';
	$selected = ($row['id'] == $idcat) ? ' selected="selected"' : '';	
	$Template->Assign_block_vars('cat', array(
		'CAT' => '<option value="' . $row['id'] . '"' . $selected . '>' . $margin . ' ' . $row['name'] . '</option>'
	));	
}
$Sql->Close($result);

require_once('../forum/forum_functions.php');

if( !empty($valid_search) && !empty($search) )
{
	if( $idcat == '-1' )
		$idcat = 0;
	
	if( strlen($search) >= 4 )
	{
		$auth_cats = !empty($auth_cats) ? " AND c1.id NOT IN (" . trim($auth_cats, ',') . ")" : '';
		
		$req_msg = "SELECT msg.id as msgid, msg.user_id, msg.idtopic, msg.timestamp, t.title, c.id, c.auth, m.login, s.user_id AS connect, msg.contents, MATCH(msg.contents) AGAINST('" . $search . "') AS relevance, 0 AS relevance2
		FROM ".PREFIX."forum_msg msg
		LEFT JOIN ".PREFIX."sessions s ON s.user_id = msg.user_id AND s.session_time > '" . (time() - $CONFIG['site_session_invit']) . "' AND s.user_id != -1
		LEFT JOIN ".PREFIX."member m ON m.user_id = msg.user_id
		JOIN ".PREFIX."forum_topics t ON t.id = msg.idtopic
		JOIN ".PREFIX."forum_cats c1 ON c1.id = t.idcat
		JOIN ".PREFIX."forum_cats c ON c.level != 0 AND c.aprob = 1
		WHERE MATCH(msg.contents) AGAINST('" . $search . "') AND msg.timestamp > '" . (time() - $time) . "'
		" . (!empty($idcat) ? " AND t.idcat = '" . $idcat . "'" : '') . $auth_cats . "
		GROUP BY msg.id
		ORDER BY relevance DESC
		" . $Sql->Sql_limit(0, 24);

		$req_title = "SELECT msg.id as msgid, msg.user_id, msg.idtopic, msg.timestamp, t.title, c.id, c.auth, m.login, s.user_id AS connect, msg.contents, MATCH(t.title) AGAINST('" . $search . "') AS relevance, 0 AS relevance2
		FROM ".PREFIX."forum_msg msg
		LEFT JOIN ".PREFIX."sessions s ON s.user_id = msg.user_id AND s.session_time > '" . (time() - $CONFIG['site_session_invit']) . "' AND s.user_id != -1
		LEFT JOIN ".PREFIX."member m ON m.user_id = msg.user_id
		JOIN ".PREFIX."forum_topics t ON t.id = msg.idtopic
		JOIN ".PREFIX."forum_cats c1 ON c1.id = t.idcat
		JOIN ".PREFIX."forum_cats c ON c.level != 0 AND c.aprob = 1
		WHERE MATCH(t.title) AGAINST('" . $search . "') AND msg.timestamp > '" . (time() - $time) . "'
		" . (!empty($idcat) ? " AND t.idcat = '" . $idcat . "'" : '') . $auth_cats . "
		GROUP BY t.id
		ORDER BY relevance DESC
		" . $Sql->Sql_limit(0, 24);
		
		$req_all = "SELECT msg.id as msgid, msg.user_id, msg.idtopic, msg.timestamp, t.title, c.id, c.auth, m.login, s.user_id AS connect, msg.contents, MATCH(t.title) AGAINST('" . $search . "') AS relevance, MATCH(msg.contents) AGAINST('" . $search . "') AS relevance2
		FROM ".PREFIX."forum_msg msg
		LEFT JOIN ".PREFIX."sessions s ON s.user_id = msg.user_id AND s.session_time > '" . (time() - $CONFIG['site_session_invit']) . "' AND s.user_id != -1
		LEFT JOIN ".PREFIX."member m ON m.user_id = msg.user_id
		JOIN ".PREFIX."forum_topics t ON t.id = msg.idtopic
		JOIN ".PREFIX."forum_cats c1 ON c1.id = t.idcat
		JOIN ".PREFIX."forum_cats c ON c.level != 0 AND c.aprob = 1
		WHERE (MATCH(t.title) AGAINST('" . $search . "') OR MATCH(msg.contents) AGAINST('" . $search . "')) AND msg.timestamp > '" . (time() - $time) . "'
		" . (!empty($idcat) ? " AND t.idcat = '" . $idcat . "'" : '') . $auth_cats . "
		GROUP BY t.id
		ORDER BY relevance DESC
		" . $Sql->Sql_limit(0, 24);
		
		switch($where)
		{
			case 'title':
			$req = $req_title;
			break;
			case 'all':
			$req = $req_all;
			break;
			default:
			$req = $req_msg;
		}

		$max_relevance = 4.5;		
		$check_result = false;
		$result = $Sql->Query_while($req, __LINE__, __FILE__);
		while( $row = $Sql->Sql_fetch_assoc($result) ) //On execute la requ�te dans une boucle pour afficher tout les r�sultats.
		{ 
			$title = $row['title'];
			if( !empty($row['title']) )
				$title = (strlen(html_entity_decode($row['title'])) > 45 ) ? substr_html($row['title'], 0, 45) . '...' : $row['title'];
			
			//On encode l'url pour un �ventuel rewriting, c'est une op�ration assez gourmande
			$rewrited_title = ($CONFIG['rewrite'] == 1) ? '+' . url_encode_rewrite($row['title']) : '';
			
			//Pertinance du r�sultat.
			$relevance = max($row['relevance'], $row['relevance2']);
			
			$contents = $row['contents'];
			if( $colorate_result )
			{
				$array_search = explode(' ', $search);
				foreach($array_search as $token)
				{
					$contents =  preg_replace_callback('`(.*)(' . preg_quote($token) . ')(.*)`isU', 'token_colorate', $contents);
					$title = preg_replace_callback('`(.*)(' . preg_quote($token) . ')(.*)`isU', 'token_colorate', $title);
				}
			}
			
			$Template->Assign_block_vars('list', array(
				'USER_ONLINE' => '<img src="../templates/' . $CONFIG['theme'] . '/images/' . ((!empty($row['connect']) && $row['user_id'] !== -1) ? 'online' : 'offline') . '.png" alt="" class="valign_middle" />',
				'USER_PSEUDO' => !empty($row['login']) ? '<a class="msg_link_pseudo" href="../member/member' . transid('.php?id=' . $row['user_id'], '-' . $row['user_id'] . '.php') . '">' . wordwrap_html($row['login'], 13) . '</a>' : '<em>' . $LANG['guest'] . '</em>',			
				'CONTENTS' => second_parse($contents),
				'RELEVANCE' => ($relevance > $max_relevance ) ? '100' : number_round(($relevance * 100) / $max_relevance, 2),
				'DATE' => gmdate_format('d/m/y', $row['timestamp']),
				'U_TITLE'  => '<a class="small_link" href="../forum/topic' . transid('.php?id=' . $row['idtopic'], '-' . $row['idtopic'] . $rewrited_title . '.php') . '#m' . $row['msgid'] . '">' . ucfirst($title) . '</a>'				
			));
			
			$check_result = true;
		}	
		$Sql->Close($result);
		
		if( $check_result !== true )
			$Errorh->Error_handler($LANG['no_result'], E_USER_NOTICE);
		else
		{
			$Template->Assign_vars(array(
				'C_FORUM_SEARCH'  => true
			));
		}
	}
	else //Gestion erreur.
		$Errorh->Error_handler($LANG['invalid_req'], E_USER_NOTICE);
}
elseif( !empty($valid_search) )
	$Errorh->Error_handler($LANG['invalid_req'], E_USER_WARNING);
	
//Listes les utilisateurs en lignes.
list($total_admin, $total_modo, $total_member, $total_visit, $users_list) = array(0, 0, 0, 0, '');
$result = $Sql->Query_while("SELECT s.user_id, s.level, m.login 
FROM ".PREFIX."sessions s 
LEFT JOIN ".PREFIX."member m ON m.user_id = s.user_id 
WHERE s.session_time > '" . (time() - $CONFIG['site_session_invit']) . "' AND s.session_script = '/forum/search.php'
ORDER BY s.session_time DESC", __LINE__, __FILE__);
while( $row = $Sql->Sql_fetch_assoc($result) )
{
	switch( $row['level'] ) //Coloration du membre suivant son level d'autorisation. 
	{ 		
		case -1:
		$status = 'visiteur';
		$total_visit++;
		break;			
		case 0:
		$status = 'member';
		$total_member++;
		break;			
		case 1: 
		$status = 'modo';
		$total_modo++;
		break;			
		case 2: 
		$status = 'admin';
		$total_admin++;
		break;
	} 
	$coma = !empty($users_list) && $row['level'] != -1 ? ', ' : '';
	$users_list .= (!empty($row['login']) && $row['level'] != -1) ?  $coma . '<a href="../member/member' . transid('.php?id=' . $row['user_id'], '-' . $row['user_id'] . '.php') . '" class="' . $status . '">' . $row['login'] . '</a>' : '';
}
$Sql->Close($result);

$total_online = $total_admin + $total_modo + $total_member + $total_visit;
$Template->Assign_vars(array(
	'TOTAL_ONLINE' => $total_online,
	'USERS_ONLINE' => (($total_online - $total_visit) == 0) ? '<em>' . $LANG['no_member_online'] . '</em>' : $users_list,
	'ADMIN' => $total_admin,
	'MODO' => $total_modo,
	'MEMBER' => $total_member,
	'GUEST' => $total_visit,
	'L_USER' => ($total_online > 1) ? $LANG['user_s'] : $LANG['user'],
	'L_ADMIN' => ($total_admin > 1) ? $LANG['admin_s'] : $LANG['admin'],
	'L_MODO' => ($total_modo > 1) ? $LANG['modo_s'] : $LANG['modo'],
	'L_MEMBER' => ($total_member > 1) ? $LANG['member_s'] : $LANG['member'],
	'L_GUEST' => ($total_visit > 1) ? $LANG['guest_s'] : $LANG['guest'],
	'L_AND' => $LANG['and'],
	'L_ONLINE' => strtolower($LANG['online']),
	'L_SEARCH' => $LANG['search'],
	'L_ADVANCED_SEARCH' => $LANG['advanced_search']
));

$Template->Pparse('search');

include('../includes/footer.php');

?>