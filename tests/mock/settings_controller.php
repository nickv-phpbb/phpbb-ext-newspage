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

namespace nickvergessen\newspage\tests\mock;

use nickvergessen\newspage\controller\settings;

/**
 * Settings Controller Mock
 * @package phpBB3
 */
class settings_controller extends settings
{
	/**
	 * Need to overwrite this in order not to output something
	 */
	public function meta_refresh()
	{
	}
}
