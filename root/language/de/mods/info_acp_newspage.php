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
	'NEWS_TITLE'				=> 'NV newspage',
	'NEWS_CONFIG'				=> 'Konfiguration',

	'NEWS'						=> 'News',
	'NEWS_ARCHIVE_PER_YEAR'				=> 'Archivblocks pro Jahr',
	'NEWS_ARCHIVE_PER_YEAR_EXPLAIN'		=> 'Zeigt pro Jahr je einen seperaten Archivblock',
	'NEWS_ARCHIVE'				=> 'Archiv',
	'NEWS_ARCHIVE_OF'			=> 'Archiv vom %s',
	'NEWS_ATTACH_SHOW'			=> 'Anhänge anzeigen',
	'NEWS_ATTACH_SHOW_EXPLAIN'	=> 'Anhänge auf der Newspage mit anzeigen',
	'NEWS_CAT'					=> 'Kategorien',
	'NEWS_CAT_SHOW'				=> 'Kategorien anzeigen',
	'NEWS_CAT_SHOW_EXPLAIN'		=> 'Die gewählte Foren werden als Kategorien angezeigt.',
	'NEWS_CHAR_LIMIT'			=> 'Textlänge auf der News-page',
	'NEWS_COMMENTS'				=> 'Kommentare',
	'NEWS_FORUMS'				=> 'Foren, aus denen die News angezeigt werden.',
	'NEWS_GO_TO_TOPIC'			=> 'Link zum Thema',
	'NEWS_NONE'					=> 'keine News',
	'NEWS_NUMBER'				=> 'Anzahl der News pro Seite',
	'NEWS_PAGES'				=> 'Anzahl der News-Seite',
	'NEWS_PAGES_EXPLAIN'		=> 'Archiv-Links benutzen immer soviele Seiten wie benötigt werden.',
	'NEWS_POLL'					=> 'Umfrage',
	'NEWS_POLL_GOTO'			=> 'Klicke hier um abzustimmen!',
	'NEWS_POST_BUTTONS'			=> 'Beitrags-Buttons anzeigen',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> '(Zitieren, Ändern, ...)',
	'NEWS_READ_FULL'			=> 'News ganz lesen',
	'NEWS_READ_HERE'			=> 'Hier',
	'NEWS_SAVED'				=> 'Einstellung gespeichert.',
	'NEWS_USER_INFO'			=> 'Benutzerinformation anzeigen',
	'NEWS_SHADOW_SHOW'			=> 'Schatten-Themen anzeigen',
	'NEWS_SHADOW_SHOW_EXPLAIN'	=> 'Zeigt die News, wenn ein Link im alten Forum beibehalten wurde',
	'NEWS_USER_INFO_EXPLAIN'	=> '(Beiträge, Wohnort, ...)',

	'NEWSPAGE'					=> 'NV Newspage',
	'INSTALL_NEWSPAGE'			=> 'Newspage installieren',
	'INSTALL_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage installieren möchtest?',
	'UPDATE_NEWSPAGE'			=> 'Newspage aktualisieren',
	'UPDATE_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage aktualisieren möchtest?',
	'UNINSTALL_NEWSPAGE'		=> 'Newspage deinstallieren',
	'UNINSTALL_NEWSPAGE_CONFIRM'	=> 'Bist du dir sicher, dass du die Newspage deinstallieren möchtest?',
));
