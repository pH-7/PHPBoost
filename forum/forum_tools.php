<?php
/* ##################################################
 *							 forum_tools.php
 *							-------------------
 *   begin				: March 26, 2008
 *   copyright			: (C) 2008 Viarre régis
 *   email				: crowkait@phpboost.com
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
  ################################################### */

if (defined('PHPBOOST') !== true)
	exit;

$request = AppContext::get_request();

############### Header du forum ################
$tpl_top = new FileTemplate('forum/forum_top.tpl');
$tpl_bottom = new FileTemplate('forum/forum_bottom.tpl');

$is_guest = AppContext::get_current_user()->get_id() == -1;
$is_connected = AppContext::get_current_user()->check_level(User::MEMBER_LEVEL);
$nbr_msg_not_read = 0;
if (!$is_guest) {
	//Calcul du temps de péremption, ou de dernière vue des messages par rapport à la configuration.
	$max_time_msg = forum_limit_time_msg();

	//Vérification des autorisations.
	$authorized_categories = ForumService::get_authorized_categories(Category::ROOT_CATEGORY);

	//Si on est sur un topic, on le supprime dans la requête => si ce topic n'était pas lu il ne sera plus dans la liste car désormais lu.
	$clause_topic = '';
	if (TextHelper::strpos(SCRIPT, '/forum/topic.php') !== false) {
		$id_get = $request->get_getint('id', 0);
		$clause_topic = " AND t.id != '" . $id_get . "'";
	}

	$nbr_msg_not_read = 0;

	//Requête pour compter le nombre de messages non lus.
	try {
		$row = PersistenceContext::get_querier()->select_single_row_query("SELECT COUNT(*) as nbr_msg_not_read
		FROM " . PREFIX . "forum_topics t
		LEFT JOIN " . PREFIX . "forum_cats c ON c.id = t.idcat
		LEFT JOIN " . PREFIX . "forum_view v ON v.idtopic = t.id AND v.user_id = :user_id
		WHERE t.last_timestamp >= :last_timestamp AND (v.last_view_id != t.last_msg_id OR v.last_view_id IS NULL)" . $clause_topic . " AND c.id IN :authorized_categories", array(
			'authorized_categories' => $authorized_categories,
			'last_timestamp' => $max_time_msg,
			'user_id' => AppContext::get_current_user()->get_id()
		));
		$nbr_msg_not_read = $row['nbr_msg_not_read'];
	} catch (RowNotFoundException $e) {
		
	}
}

//Formulaire de connexion sur le forum.
if ($config->is_connexion_form_displayed()) {
	$display_connexion = array(
		'C_USER_NOTCONNECTED' => !$is_connected,
		'C_FORUM_CONNEXION'   => true,
		'L_CONNECT'           => LangLoader::get_message('connection', 'user-common'),
		'L_DISCONNECT'        => LangLoader::get_message('disconnect', 'user-common'),
		'L_AUTOCONNECT'       => LangLoader::get_message('autoconnect', 'user-common'),
		'L_REGISTER'          => LangLoader::get_message('register', 'user-common')
	);
	$tpl_top->put_all($display_connexion);
	$tpl_bottom->put_all($display_connexion);
}

$vars_tpl = array(
	'C_USER_CONNECTED'         => $is_connected,
	'C_DISPLAY_UNREAD_DETAILS' => !$is_guest,
	'C_MODERATION_PANEL'       => AppContext::get_current_user()->check_level(1),
	'FORUM_NAME'               => $config->get_forum_name(),
	'U_TOPIC_TRACK'            => Url::to_rel('/forum/track.php'),
	'U_LAST_MSG_READ'          => Url::to_rel('/forum/lastread.php'),
	'U_MSG_NOT_READ'           => Url::to_rel('/forum/unread.php'),
	'U_MSG_SET_VIEW'           => Url::to_rel('/forum/action' . url('.php?read=1', '')),
	'L_SHOW_TOPIC_TRACK'       => $LANG['show_topic_track'],
	'L_SHOW_LAST_READ'         => $LANG['show_last_read'],
	'L_SHOW_NOT_READS'         => $LANG['show_not_reads'],
	'L_MARK_AS_READ'           => $LANG['mark_as_read'],
	'C_IS_GUEST'               => !$is_guest,
	'NBR_MSG_NOT_READ'         => $nbr_msg_not_read,
	'L_FORUM_INDEX'            => $LANG['forum_index'],
	'L_MODERATION_PANEL'       => $LANG['moderation_panel'],
	'L_CONFIRM_READ_TOPICS'    => $LANG['confirm_mark_as_read'],
	'L_AUTH_ERROR'             => LangLoader::get_message('error.auth', 'status-messages-common'),
	'L_SHOW_MY_MSG'            => $LANG['show_my_msg'],
	'U_SHOW_MY_MSG'            => Url::to_rel('/forum/membermsg.php?id=' . AppContext::get_current_user()->get_id())
);

$tpl_top->put_all($vars_tpl);
$tpl_bottom->put_all($vars_tpl);

?>
