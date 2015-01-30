<?php
/**
*
* @package NV Newspage Extension
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\migrations\v11x;

use phpbb\db\migration\migration;

/**
 * @package nickvergessen\newspage\migrations\v11x
 */
class release_1_1_0 extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.1.0', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\nickvergessen\newspage\migrations\v10x\release_1_0_8');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.update', array('newspage_mod_version', '1.1.0')),
		);
	}
}
