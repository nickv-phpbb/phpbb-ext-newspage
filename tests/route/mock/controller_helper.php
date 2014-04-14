<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\tests\route\mock;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

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
		$anchor = '';
		if (isset($params['#']))
		{
			$anchor = '#' . $params['#'];
			unset($params['#']);
		}
		$url_generator = new UrlGenerator($this->route_collection, new RequestContext());
		$route_url = $url_generator->generate($route, $params);

		if (strpos($route_url, '/') === 0)
		{
			$route_url = substr($route_url, 1);
		}

		if ($is_amp)
		{
			$route_url = str_replace(array('&amp;', '&'), array('&', '&amp;'), $route_url);
		}

		// If enable_mod_rewrite is false, we need to include app.php
		$route_prefix = $this->phpbb_root_path;
		if (empty($this->config['enable_mod_rewrite']))
		{
			$route_prefix .= 'app.' . $this->php_ext . '/';
		}

		return append_sid($route_prefix . $route_url . $anchor, false, $is_amp, $session_id);
	}
}
