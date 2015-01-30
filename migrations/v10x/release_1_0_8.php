<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace nickvergessen\newspage\migrations\v10x;

use phpbb\db\migration\migration;

/**
 * @package nickvergessen\newspage\migrations\v10x
 */
class release_1_0_8 extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.0.8', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\nickvergessen\newspage\migrations\v10x\release_1_0_7');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.update', array('newspage_mod_version', '1.0.8')),
		);
	}
}
