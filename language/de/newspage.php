<?php

/**
*
* news-page [Deutsch — Du]
*
* @package language
* @version $Id$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_NEWSPAGE_TITLE'		=> 'NV Newspage',
	'ACP_NEWSPAGE_CONFIG'		=> 'Newspage-Einstellungen',

	'NEWS'						=> 'News',
	'NEWS_ARCHIVE_SHOW'			=> 'Archiv anzeigen',
	'NEWS_ARCHIVE_SHOW_EXPLAIN'	=> 'Zeigt eine Liste mit den Monaten und der Anzahl der darin geschriebenen News.',
	'NEWS_ARCHIVE_SHOW_PER_YEAR'=> 'Einen Block pro Jahr anzeigen',
	'NEWS_ARCHIVE'				=> 'Archiv',
	'NEWS_ARCHIVE_OF'			=> 'Archiv vom %s',
	'NEWS_ATTACH_SHOW'			=> 'Anhänge anzeigen',
	'NEWS_ATTACH_SHOW_EXPLAIN'	=> 'Anhänge auf der Newspage mit anzeigen<br /><strong>Hinweis:</strong> Anhänge die in Beiträge eingefügt wurden, werden immer angezeigt.',
	'NEWS_CAT'					=> 'Kategorien',
	'NEWS_CAT_SHOW'				=> 'Kategorien anzeigen',
	'NEWS_CAT_SHOW_EXPLAIN'		=> 'Die gewählte Foren werden als Kategorien angezeigt.',
	'NEWS_CHAR_LIMIT'			=> 'Textlänge auf der News-page',
	'NEWS_COMMENTS'				=> 'Kommentare',
	'NEWS_FORUMS'				=> 'Foren, aus denen die News angezeigt werden.',
	'NEWS_GO_TO_TOPIC'			=> 'Link zum Thema',
	'NEWS_NUMBER'				=> 'Anzahl der News pro Seite',
	'NEWS_PAGES'				=> 'Anzahl der News-Seite',
	'NEWS_PAGES_EXPLAIN'		=> 'Archiv-Links benutzen immer soviele Seiten wie benötigt werden.',
	'NEWS_POLL'					=> 'Umfrage',
	'NEWS_POLL_GOTO'			=> 'Klicke hier um abzustimmen!',
	'NEWS_POST_BUTTONS'			=> 'Beitrags-Buttons anzeigen',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> '(Zitieren, Ändern, ...)',
	'NEWS_READ_FULL'			=> 'News ganz lesen',
	'NEWS_READ_HERE'			=> 'Hier',
	'NEWS_SAVED'				=> 'Einstellung erfolgreich aktualisiert.',
	'NEWS_USER_INFO'			=> 'Benutzerinformation anzeigen',
	'NEWS_SHADOW_SHOW'			=> 'Schatten-Themen anzeigen',
	'NEWS_SHADOW_SHOW_EXPLAIN'	=> 'Zeigt die News, wenn ein Link im alten Forum beibehalten wurde',
	'NEWS_USER_INFO_EXPLAIN'	=> '(Beiträge, Wohnort, ...)',

	'NO_NEWS'					=> 'Es gibt keine News-Einträge.',
	'NO_NEWS_ARCHIVE'			=> 'In diesem Archiv gibt es keine News-Einträge.',
	'NO_NEWS_CATEGORY'			=> 'In dieser Kategorien gibt es keine News-Einträge.',

	'NEWSPAGE'					=> 'NV Newspage',
	'INSTALL_NEWSPAGE'			=> 'Newspage installieren',
	'INSTALL_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage installieren möchtest?',
	'UPDATE_NEWSPAGE'			=> 'Newspage aktualisieren',
	'UPDATE_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage aktualisieren möchtest?',
	'UNINSTALL_NEWSPAGE'		=> 'Newspage deinstallieren',
	'UNINSTALL_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage deinstallieren möchtest?',
));
