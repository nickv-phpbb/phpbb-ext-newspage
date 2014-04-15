<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\tests\mock;

/**
 * User Mock
 * @package phpBB3
 */
class user extends \phpbb\user
{
	public function __construct()
	{
	}

	public function lang()
	{
		return implode(' ', func_get_args());
	}
}
