<?php
/**
*
* news-page [Deutsch — Du]
*
* @package language
* @version $Id: lang_wwh.php 4 2007-06-02
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
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
	'NEWS_ARCHIVE'				=> 'Archiv',
	'NEWS_NONE'					=> 'keine News',
	'NEWS_GO_TO_TOPIC'			=> 'Link zum Thema',
	'NEWS_READ_FULL'			=> 'News ganz lesen',
	'NEWS_READ_HERE'			=> 'Hier',
	'NEWS_COMMENTS'				=> 'Kommentare',
	'NEWS_SAVED'				=> 'Einstellung gespeichert.',
	'NEWS_NUMBER'				=> 'Anzahl der News',
	'NEWS_CHAR_LIMIT'			=> 'Textlänge auf der News-page',
	'NEWS_USER_INFO'			=> 'Benutzerinformation anzeigen (Beiträge, Wohnort, ...)',
	'NEWS_POST_BUTTONS'			=> 'Beitrags-Buttons anzeigen (Zitieren, Ändern, ...)',
	'NEWS_FORUMS'				=> 'Foren, aus denen die News angezeigt werden.<br />(getrennt durch "," )',

	'INSTALLER_INTRO'					=> 'Intro',
	'INSTALLER_INTRO_WELCOME'			=> 'Willkommen zur MOD-Installation',
	'INSTALLER_INTRO_WELCOME_NOTE'		=> 'Bitte wähle aus, was du tun möchtest.',

	'INSTALLER_INSTALL'					=> 'Installieren',
	'INSTALLER_INSTALL_MENU'			=> 'Installation',
	'INSTALLER_INSTALL_SUCCESSFUL'		=> 'Installation der MOD v%s war erfolgreich.',
	'INSTALLER_INSTALL_UNSUCCESSFUL'	=> 'Installation der MOD v%s war <strong>nicht</strong> erfolgreich.',
	'INSTALLER_INSTALL_VERSION'			=> 'Installiere MOD v%s',
	'INSTALLER_INSTALL_WELCOME'			=> 'Willkommen zur Installation',
	'INSTALLER_INSTALL_WELCOME_NOTE'	=> 'Wenn du den MOD installierst, werden möglicherweise vorhandene Datenbanktabellen mit gleichem Namen gelöscht.',

	'INSTALLER_NEEDS_FOUNDER'			=> 'Du musst als Gründer eingeloggt sein.',

	'INSTALLER_UPDATE'					=> 'Update',
	'INSTALLER_UPDATE_MENU'				=> 'Updatemenü',
	'INSTALLER_UPDATE_NOTE'				=> 'Update MOD von v%s nach v%s',
	'INSTALLER_UPDATE_SUCCESSFUL'		=> 'Update der MOD von v%s nach v%s war erfolgreich.',
	'INSTALLER_UPDATE_UNSUCCESSFUL'		=> 'Update der MOD von v%s nach v%s war <strong>nicht</strong> erfolgreich.',
	'INSTALLER_UPDATE_VERSION'			=> 'Update MOD von v',
	'INSTALLER_UPDATE_WELCOME'			=> 'Willkommen zum Update',

	'WARNING'							=> 'Warnung',
));

?>