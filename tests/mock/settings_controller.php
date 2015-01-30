<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
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
