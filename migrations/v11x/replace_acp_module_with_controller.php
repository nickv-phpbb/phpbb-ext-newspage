<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\migrations\v11x;

class replace_acp_module_with_controller extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\nickvergessen\newspage\migrations\v11x\release_1_1_0');
	}

	public function update_data()
	{
		return array(
			array('if', array(
				array('module.exists', array('acp', 'ACP_NEWSPAGE_TITLE', 'ACP_NEWSPAGE_CONFIG')),
				array('module.remove', array('acp', 'ACP_NEWSPAGE_TITLE', 'ACP_NEWSPAGE_CONFIG')),
			)),

			array('if', array(
				array('module.exists', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_NEWSPAGE_TITLE')),
				array('module.remove', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_NEWSPAGE_TITLE')),
			)),
		);
	}
}
