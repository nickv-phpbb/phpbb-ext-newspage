<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace nickvergessen\newspage\migrations\v11x;

class release_1_1_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.1.0', '>=');
	}

	static public function depends_on()
	{
		return array('\nickvergessen\newspage\migrations\v10x\release_1_0_8');
	}

	public function update_data()
	{
		return array(
			array('if', array(
				array('module.exists', array('acp', 'ACP_NEWSPAGE_TITLE', 'ACP_NEWSPAGE_CONFIG')),
				array('module.remove', array('acp', 'ACP_NEWSPAGE_TITLE', 'ACP_NEWSPAGE_CONFIG')),
			)),

			array('module.add', array(
				'acp',
				'ACP_NEWSPAGE_TITLE',
				array(
					'module_basename'	=> '\nickvergessen\newspage\acp\main_module',
					'modes'				=> array('config_newspage'),
				),
			)),

			array('config.update', array('newspage_mod_version', '1.1.0')),
		);
	}
}
