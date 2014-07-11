<?php


/**
*
* @package NV Newspage Extension
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\migrations\v11x;

class release_1_1_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.1.1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\nickvergessen\newspage\migrations\v11x\release_1_1_0',
			'\nickvergessen\newspage\migrations\v11x\better_archive_config_option',
			'\nickvergessen\newspage\migrations\v11x\replace_acp_module_with_controller',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('newspage_mod_version', '1.1.1')),
		);
	}
}
