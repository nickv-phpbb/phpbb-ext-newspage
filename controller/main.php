<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2013 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_ext_nickvergessen_newspage_controller_main
{
	/* @var phpbb_config */
	protected $config;

	/* @var phpbb_controller_helper */
	protected $helper;
	/* @var phpbb_ext_nickvergessen_newspage */
	protected $newspage;

	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param phpbb_config	$config		Config object
	* @param phpbb_controller_helper		$helper				Controller helper object
	* @param phpbb_ext_nickvergessen_newspage		$newspage	Newspage object
	* @param string			$root_path	phpBB root path
	* @param string			$php_ext	phpEx
	*/
	public function __construct(phpbb_config $config, phpbb_controller_helper $helper, phpbb_ext_nickvergessen_newspage $newspage, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->newspage = $newspage;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('bbcode'))
		{
			include($this->root_path . 'includes/bbcode.' . $this->php_ext);
		}
		if (!function_exists('get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
	}

	/**
	* Newspage controller to display multiple news
	*
	* Route must be a sequence of the following substrings,
	* the order is mandatory:
	*	/news							[mandatory]
	*		/category/{forum_id}		[optional]
	*		/archive/{year}/{month}		[optional]
	*		/page/{page}				[optional]
	*
	* @param int	$forum_id		Forum ID of the category to display
	* @param int	$year			Limit the news to a certain year
	* @param int	$month			Limit the news to a certain month
	* @param int	$page			Page to display
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function newspage($forum_id, $year, $month, $page)
	{

		$this->newspage->set_category($forum_id)
			->set_archive($year, $month)
			->set_start(($page - 1) * $this->config['news_number']);

		return $this->base();
	}

	/**
	* News controller to be accessed with the URL /news/{topic_id} to display a single news
	*
	* @param int	$topic_id		Topic ID of the news to display
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function single_news($topic_id)
	{
		$this->newspage->set_news($topic_id);

		return $this->base(false);
	}

	/**
	* Base controller to be accessed with the URL /news/{id}
	*
	* @param	bool	$display_pagination		Force to hide the pagination
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base($display_pagination = true)
	{
		$this->newspage->generate_archive_list();
		if ($display_pagination)
		{
			$this->newspage->generate_pagination();
		}
		if ($this->config['news_cat_show'])
		{
			$this->newspage->generate_category_list();
		}

		$this->newspage->base();

		return $this->helper->render('newspage_body.html', $this->newspage->get_page_title());
	}
}