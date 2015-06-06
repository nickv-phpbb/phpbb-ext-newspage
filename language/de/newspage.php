<?php

/**
 * This file is part of the NV Newspage Extension package.
 *
 * @copyright (c) nickvergessen <https://github.com/nickvergessen>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the license.txt file.
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
	'NEWS'						=> 'News',
	'NEWS_ADD_NEW'				=> 'Neu',
	'NEWS_ADD_NEW_TITLE'		=> 'Neuen News-Eintrag erstellen',
	'NEWS_ARCHIVE_SHOW'			=> 'Erlaube das Datum einzuschränken',
	'NEWS_ARCHIVE'				=> 'Archiv',
	'NEWS_ARCHIVE_OF'			=> 'Archiv vom %s',
	'NEWS_ATTACH_SHOW'			=> 'Anhänge anzeigen',
	'NEWS_ATTACH_SHOW_EXPLAIN'	=> 'Anhänge die in Beiträge eingefügt wurden, werden immer angezeigt.',
	'NEWS_CAT'					=> 'Kategorien',
	'NEWS_CAT_SHOW'				=> 'Erlaube die Foren einzuschränken',
	'NEWS_CHAR_LIMIT'			=> 'News-Einträge kürzen',
	'NEWS_CHAR_LIMIT_EXPLAIN'	=> 'Textlänge, „0“ um Kürzen zu unterbinden',
	'NEWS_COMMENTS'				=> 'Kommentare',
	'NEWS_FILTER_ARCHIVE'		=> 'Datum filtern',
	'NEWS_FILTER_BY_ARCHIVE'	=> 'Filter News-Einträge nach Jahr und Monat',
	'NEWS_FILTER_BY_CATEGORY'	=> 'Filter News-Einträge nach Forum',
	'NEWS_FILTER_CATEGORY'		=> 'Kategorie filtern',
	'NEWS_FILTER_REMOVE'		=> 'Filter entfernen',
	'NEWS_FORUMS'				=> 'News-Foren auswählen',
	'NEWS_GO_TO_TOPIC'			=> 'Link zum Thema',
	'NEWS_NUMBER'				=> 'News pro Seite',
	'NEWS_PAGES'				=> 'Anzahl der News-Seiten',
	'NEWS_POLL'					=> 'Umfrage',
	'NEWS_POLL_GOTO'			=> 'Klicke hier um abzustimmen!',
	'NEWS_POST_BUTTONS'			=> 'Beitrags-Buttons anzeigen',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> 'Zitat, Ändern, etc.',
	'NEWS_READ_FULL'			=> 'News ganz lesen',
	'NEWS_READ_HERE'			=> 'Hier',
	'NEWS_SAVED'				=> 'Die Einstellung wurden erfolgreich gespeichert.',
	'NEWS_SHADOW_SHOW'			=> 'Verschobene Themen anzeigen',
	'NEWS_USER_INFO'			=> 'Benutzerinformation anzeigen',
	'NEWS_USER_INFO_EXPLAIN'	=> 'Avatar, Profilefelder, etc.',

	'NO_NEWS'					=> 'Es gibt keine News-Einträge.',
	'NO_NEWS_ARCHIVE'			=> 'In diesem Archiv gibt es keine News-Einträge.',
	'NO_NEWS_CATEGORY'			=> 'In dieser Kategorien gibt es keine News-Einträge.',

	'NEWSPAGE'					=> 'News',

	'VIEW_NEWS_POSTS'			=> array(
		0	=> 'Keine News',
		1	=> '1 News',
		2	=> '%d News',
	),

	'VIEWONLINE_NEWS'			=> 'Liest News',
	'VIEWONLINE_NEWS_ARCHIVE'	=> 'Liest News von %s',
	'VIEWONLINE_NEWS_CATEGORY'	=> 'Liest News in %s',
	'VIEWONLINE_NEWS_CATEGORY_ARCHIVE'	=> 'Liest News in %s von %s',
));
