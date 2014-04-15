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
 * Controller helper class, contains methods that do things for controllers
 * @package phpBB3
 */
class controller_helper extends \phpbb\controller\helper
{
	public function __construct()
	{
	}

	/**
	 * Generate a URL to a route
	 *
	 * @param string	$route		Name of the route to travel
	 * @param array	$params		String or array of additional url parameters
	 * @param bool	$is_amp		Is url using &amp; (true) or & (false)
	 * @param string	$session_id	Possibility to use a custom session id instead of the global one
	 * @return string The URL already passed through append_sid()
	 */
	public function route($route, array $params = array(), $is_amp = true, $session_id = false)
	{
		return $route . '#' . serialize($params);
	}
}
