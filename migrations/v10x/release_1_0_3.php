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

namespace nickvergessen\newspage\migrations\v10x;

use phpbb\db\migration\migration;

/**
 * @package nickvergessen\newspage\migrations\v10x
 */
class release_1_0_3 extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return version_compare($this->config['newspage_mod_version'], '1.0.3', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\nickvergessen\newspage\migrations\v10x\release_1_0_2');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('news_attach_show', 1)),
			array('config.add', array('news_cat_show', 1)),
			array('config.add', array('news_archive_per_year', 1)),

			array('config.update', array('newspage_mod_version', '1.0.3')),
		);
	}
}
