<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\mock;

/**
 * Controller helper Mock
 * @package phpBB3
 */
class controller_helper extends \phpbb\controller\helper
{
	public function __construct()
	{
	}

	public function route($route, array $params = array(), $is_amp = true, $session_id = false)
	{
		return $route . '#' . serialize($params);
	}
}
