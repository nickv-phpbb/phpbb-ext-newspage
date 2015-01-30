<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage;
use phpbb\config\config;

/**
 * Class helper
 *
 * @package nickvergessen\newspage
 */
class helper
{
	/**
	 * Controller helper object
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * Config object
	 * @var config
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper $helper
	 * @param config $config
	 */
	public function __construct(\phpbb\controller\helper $helper, config $config)
	{
		$this->helper = $helper;
		$this->config = $config;
	}

	/**
	* Generate the pagination for the news list
	*
	* @param	mixed	$force_category		Overwrites the category, false for disabled, integer otherwise
	* @param	mixed	$force_archive		Overwrites the archive, false for disabled, string otherwise
	* @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	* @return		route		Full URL with append_sid performed on it
	*/
	public function generate_route($force_category = false, $force_archive = false, $force_page = false)
	{
		/** @var route $route */
		$route = new route($this->helper, $this->config);
		if ($this->config['news_cat_show'] && $force_category)
		{
			$route->set_category($force_category);
		}

		if ($this->config['news_archive_show'] && is_array($force_archive))
		{
			$route->set_archive_year($force_archive['y'])
				->set_archive_month($force_archive['m']);
		}
		else if ($this->config['news_archive_show'] && is_string($force_archive))
		{
			list($archive_year, $archive_month) = explode('/', $force_archive, 2);
			$route->set_archive_year($archive_year)
				->set_archive_month($archive_month);
		}

		if ($force_page)
		{
			$route->set_page($force_page);
		}
		return $route;
	}
}
