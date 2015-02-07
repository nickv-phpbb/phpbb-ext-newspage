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

namespace nickvergessen\newspage;

/**
 * Class route
 *
 * @package nickvergessen\newspage
 */
class route
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var int */
	protected $page;
	/** @var int */
	protected $category;
	/** @var int */
	protected $archive_year;
	/** @var int */
	protected $archive_month;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\config\config $config
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\config\config $config)
	{
		$this->helper = $helper;
		$this->config = $config;
	}

	/**
	 * @param mixed $archive_month
	 * @return $this
	 */
	public function set_archive_month($archive_month)
	{
		if ($this->config['news_archive_show'])
		{
			$this->archive_month = (int) $archive_month;
		}
		return $this;
	}

	/**
	 * @param mixed $archive_year
	 * @return $this
	 */
	public function set_archive_year($archive_year)
	{
		if ($this->config['news_archive_show'])
		{
			$this->archive_year = (int) $archive_year;
		}
		return $this;
	}

	/**
	 * @param mixed $category
	 * @return $this
	 */
	public function set_category($category)
	{
		if ($this->config['news_cat_show'])
		{
			$this->category = (int) $category;
		}
		return $this;
	}

	/**
	 * @param mixed $page
	 * @return $this
	 */
	public function set_page($page)
	{
		$this->page = (int) $page;
		return $this;
	}

	/**
	 * Generate the pagination for the news list
	 *
	 * @param	mixed	$force_category		Overwrites the category, false for disabled, integer otherwise
	 * @param	mixed	$force_archive		Overwrites the archive, false for disabled, string otherwise
	 * @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	 * @return		string		Full URL with append_sid performed on it
	 */
	public function get_url($force_category = false, $force_archive = false, $force_page = false)
	{
		return $this->helper->route(
			$this->get_route($force_category, $force_archive, $force_page),
			$this->get_params($force_category, $force_archive, $force_page)
		);
	}

	/**
	 * Returns the name of the route we should use
	 *
	 * @param	mixed	$force_category	Overwrites the category,
	 *							false for disabled, true to skip, integer otherwise
	 * @param	mixed	$force_archive	Overwrites the archive,
	 *							false for disabled, true to skip, string otherwise
	 * @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	 * @return		string
	 */
	public function get_route($force_category = false, $force_archive = false, $force_page = false)
	{
		$route = 'nickvergessen_newspage';
		if ($this->config['news_cat_show'])
		{
			$route .= $this->get_route_category($force_category);
		}

		if ($this->config['news_archive_show'])
		{
			$route .= $this->get_route_archive($force_archive);
		}

		$route .= $this->get_route_page($force_page);

		return $route . '_controller';
	}

	/**
	 * Returns the category part of the route we should use
	 *
	 * @param	mixed	$force_category	Overwrites the category,
	 *							false for disabled, true to skip, integer otherwise
	 * @return		string
	 */
	protected function get_route_category($force_category)
	{
		if ($force_category !== true && ($force_category || $this->category))
		{
			return '_category';
		}
		return '';
	}

	/**
	 * Returns the archive part of the route we should use
	 *
	 * @param	mixed	$force_archive	Overwrites the archive,
	 *							false for disabled, true to skip, string otherwise
	 * @return		string
	 */
	protected function get_route_archive($force_archive)
	{
		if ($force_archive !== true && ($force_archive || ($this->archive_year && $this->archive_month)))
		{
			return '_archive';
		}
		return '';
	}

	/**
	 * Returns the page part of the route we should use
	 *
	 * @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	 * @return		string
	 */
	protected function get_route_page($force_page)
	{
		if ($force_page && $force_page > 1)
		{
			return '_page';
		}
		if (!$force_page && $this->page > 1)
		{
			return '_page';
		}
		return '';
	}

	/**
	 * Returns the list of parameters of the route we should use
	 *
	 * @param	mixed	$force_category		Overwrites the category, false for disabled, integer otherwise
	 * @param	mixed	$force_archive		Overwrites the archive, false for disabled, string otherwise
	 * @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	 * @return		array
	 */
	public function get_params($force_category = false, $force_archive = false, $force_page = false)
	{
		$params = array();
		if ($this->config['news_cat_show'])
		{
			$params = array_merge($params, $this->get_param_category($force_category));
		}

		if ($this->config['news_archive_show'])
		{
			$params = array_merge($params, $this->get_params_archive($force_archive));
		}

		$params = array_merge($params, $this->get_param_page($force_page));

		return $params;
	}

	/**
	 * Returns the category parameter of the route we should use
	 *
	 * @param	mixed	$force_category		Overwrites the category, false for disabled, integer otherwise
	 * @return		array
	 */
	protected function get_param_category($force_category)
	{
		$params = array();
		if ($force_category !== true && $force_category)
		{
			$params['forum_id'] = $force_category;
		}
		else if ($force_category !== true && $this->category)
		{
			$params['forum_id'] = $this->category;
		}

		return $params;
	}

	/**
	 * Returns the list of archive parameters of the route we should use
	 *
	 * @param	mixed	$force_archive		Overwrites the archive, false for disabled, string otherwise
	 * @return		array
	 */
	protected function get_params_archive($force_archive = false)
	{
		$params = array();
		if ($force_archive !== true && $force_archive)
		{
			list($year, $month) = explode('/', $force_archive, 2);
			$params['year'] = (int) $year;
			$params['month'] = sprintf('%02d', (int) $month);
		}
		else if ($force_archive !== true && $this->archive_year && $this->archive_month)
		{
			$params['year'] = (int) $this->archive_year;
			$params['month'] = sprintf('%02d', (int) $this->archive_month);
		}

		return $params;
	}

	/**
	 * Returns the page parameter of the route we should use
	 *
	 * @param	mixed	$force_page			Overwrites the page, false for disabled, string otherwise
	 * @return		array
	 */
	protected function get_param_page($force_page = false)
	{
		$params = array();
		if ($force_page && $force_page > 1)
		{
			$params['page'] = $force_page;
		}
		else if (!$force_page && $this->page > 1)
		{
			$params['page'] = $this->page;
		}

		return $params;
	}
}
