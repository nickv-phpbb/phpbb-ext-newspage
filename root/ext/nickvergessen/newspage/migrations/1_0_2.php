<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_ext_nickvergessen_newspage_migrations_1_0_2 extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.0.2', '>=');
	}

	static public function depends_on()
	{
		return array('phpbb_ext_nickvergessen_newspage_migrations_1_0_1');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('newspage_mod_version', '1.0.2')),
		);
	}
}
