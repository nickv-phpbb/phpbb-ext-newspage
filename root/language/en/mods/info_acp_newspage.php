<?php
/**
*
* newspage [British English]
*
* @package language
* @version $Id: info_acp_newspage.php 4 2007-06-02
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NEWS_TITLE'				=> 'NV newspage',
	'NEWS_CONFIG'				=> 'Configuration',

	'NEWS'						=> 'News',
	'NEWS_ARCHIVE'				=> 'Archive',
	'NEWS_NONE'					=> 'no News',
	'NEWS_GO_TO_TOPIC'			=> 'Link to topic',
	'NEWS_READ_FULL'			=> 'Read full news',
	'NEWS_READ_HERE'			=> 'Here',
	'NEWS_COMMENTS'				=> 'Comments',
	'NEWS_SAVED'				=> 'saved setup',
	'NEWS_NUMBER'				=> 'number of news',
	'NEWS_CHAR_LIMIT'			=> 'number of chars, displayed on the news-page',
	'NEWS_USER_INFO'			=> 'show user info (posts, location, ...)',
	'NEWS_POST_BUTTONS'			=> 'show post buttons (quote, edit, ...)',
	'NEWS_FORUMS'				=> 'forums, where the news are posted.<br />(seperated by "," )',

	'INSTALLER_INTRO'					=> 'Intro',
	'INSTALLER_INTRO_WELCOME'			=> 'Welcome to the MOD Installation',
	'INSTALLER_INTRO_WELCOME_NOTE'		=> 'Please choose what you want to do.',

	'INSTALLER_INSTALL'					=> 'Install',
	'INSTALLER_INSTALL_MENU'			=> 'Installmenu',
	'INSTALLER_INSTALL_SUCCESSFUL'		=> 'Installation of the MOD v%s was successful. You may delete the install-folder now.',
	'INSTALLER_INSTALL_UNSUCCESSFUL'	=> 'Installation of the MOD v%s was <strong>not</strong> successful.',
	'INSTALLER_INSTALL_VERSION'			=> 'Install MOD v%s',
	'INSTALLER_INSTALL_WELCOME'			=> 'Welcome to the Installationmenu',
	'INSTALLER_INSTALL_WELCOME_NOTE'	=> 'When you choose to install the MOD, any database of previous versions will be dropped.',

	'INSTALLER_NEEDS_FOUNDER'			=> 'You must be logged in as a founder.',

	'INSTALLER_UPDATE'					=> 'Update',
	'INSTALLER_UPDATE_MENU'				=> 'Updatemenu',
	'INSTALLER_UPDATE_NOTE'				=> 'Update MOD from v%s to v%s',
	'INSTALLER_UPDATE_SUCCESSFUL'		=> 'Update of the MOD from v%s to v%s was successful. You may delete the install-folder now.',
	'INSTALLER_UPDATE_UNSUCCESSFUL'		=> 'Update of the MOD from v%s to v%s was <strong>not</strong> successful.',
	'INSTALLER_UPDATE_VERSION'			=> 'Update MOD from v',
	'INSTALLER_UPDATE_WELCOME'			=> 'Welcome to the Updatemenu',

	'WARNING'							=> 'Warning',
));

?>