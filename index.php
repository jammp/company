<?php
/**
 * DOUCO TEAM
 * ============================================================================
 * COPYRIGHT DOUCO 2014-2015.
 * http://www.douco.com;
 * ----------------------------------------------------------------------------
 * Author:DouCo
 * Release Date: 2014-06-05
 */

define('IN_DOUCO', true);

require(dirname(__FILE__) . '/include/init.php');

/* 如果存在搜索词则转入搜索页面 */
if ($_REQUEST['s'])
{
 if ($check->is_text($keyword = trim($_REQUEST['s'])))
	{
		require(ROOT_PATH . 'include/search.inc.php');
	}
	else
	{
		$dou->dou_msg($_LANG['search_keyword_wrong']);
	}
}

/* 获取关于我们信息 */
$sql = "SELECT * FROM " . $dou->table('page') . " WHERE id = '1'";
$query = $dou->query($sql);
$about = $dou->fetch_array($query);

/* 写入到index数组 */
$index['about_name'] = $about['page_name'];
$index['about_content'] = $dou->dou_substr($about['content'], 300);
$index['about_link'] = $dou->rewrite_url('page', '1');
$index['product_link'] = $dou->rewrite_url('product_category');
$index['article_link'] = $dou->rewrite_url('article_category');

/* 获取meta和title信息 */
$smarty->assign('page_title', $_CFG['site_title']);
$smarty->assign('keywords', $_CFG['site_keywords']);
$smarty->assign('description', $_CFG['site_description']);

/* 初始化导航栏 */
$data = $dou->fetch_array_all('nav', 'sort ASC');
$smarty->assign('nav_top_list', $dou->get_nav($data, 'top'));
$smarty->assign('nav_middle_list', $dou->get_nav($data));
$smarty->assign('nav_bottom_list', $dou->get_nav($data, 'bottom'));

/* 初始化数据 */
$smarty->assign('index', 'index'); // 是否为首页的标志
$smarty->assign('show_list', get_show_list());
$smarty->assign('link', get_link_list());
$smarty->assign('index', $index);
$smarty->assign('recommend_product', $dou->get_recommend_product('', $_CFG['home_display_product']));
$smarty->assign('recommend_article', $dou->get_recommend_article('', $_CFG['home_display_article']));

$smarty->display('index.dwt');

/**
 +----------------------------------------------------------
 * 获取下级幻灯列表
 +----------------------------------------------------------
 */
function get_show_list()
{
	$sql = "SELECT * FROM " . $GLOBALS['dou']->table('show') . " ORDER BY sort ASC, id ASC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		$show_list[] = array (
			"id" => $row['id'],
			"show_name" => $row['show_name'],
			"show_link" => $row['show_link'],
			"show_img" => ROOT_URL . $row['show_img'],
			"sort" => $row['sort']
		);
	}

	return $show_list;
}

/**
 +----------------------------------------------------------
 * 获取下级幻灯列表
 +----------------------------------------------------------
 */
function get_link_list()
{
	$sql = "SELECT * FROM " . $GLOBALS['dou']->table('link') . " ORDER BY sort ASC, id ASC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		$link_list[] = array (
			"id" => $row['id'],
			"link_name" => $row['link_name'],
			"link_url" => $row['link_url'],
			"sort" => $row['sort']
		);
	}

	return $link_list;
}






?>