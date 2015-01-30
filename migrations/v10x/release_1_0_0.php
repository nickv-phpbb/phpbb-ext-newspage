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
class release_1_0_0 extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return isset($this->config['newspage_mod_version']) && version_compare($this->config['newspage_mod_version'], '1.0.0', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('news_number', 5)),
			array('config.add', array('news_forums', '0')),
			array('config.add', array('news_char_limit', 500)),
			array('config.add', array('news_user_info', 1)),
			array('config.add', array('news_post_buttons', 1)),

			array('config.add', array('newspage_mod_version', '1.0.0')),
		);
	}
}
