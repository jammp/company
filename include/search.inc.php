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

if (!defined('IN_DOUCO'))
{
	die('Hacking attempt');
}

/* 产品列表 */
$page = $check->is_number($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$limit = $dou->pager('product', $_CFG['display_product'], $page, $cat_id, $keyword);

$sql = "SELECT id, cat_id, product_name, price, content, product_image, add_time, description FROM " . $dou->table('product') . " WHERE product_name LIKE '%$keyword%' ORDER BY id DESC" . $limit;
$query = $dou->query($sql);

while ($row = $dou->fetch_array($query))
{
	$cat_name = $dou->get_one("SELECT cat_name FROM " . $dou->table('product_category') . " WHERE cat_id = '$row[cat_id]'");
	$url = $dou->rewrite_url('product', $row['id']);
	$add_time = date("Y-m-d", $row['add_time']);
	
	$description = $row['description'] ? $row['description'] : $dou->dou_substr($row['content'], 150);

	/* 生成缩略图的文件名 */
	$image = explode(".", $row[product_image]);
	$thumb = ROOT_URL . $image[0] . "_thumb." . $image[1];
	if ($row['price'] > 0)
	{
		$price = $dou->price_format($row['price']);
	}
	else
	{
		$price = $_LANG['price_discuss'];
	}

	$product_list[] = array (
		"id" => $row['id'],
		"cat_id" => $row['cat_id'],
		"name" => $row['product_name'],
		"price" => $price,
		"thumb" => $thumb,
		"cat_name" => $cat_name,
		"add_time" => $add_time,
		"description" => $description,
		"url" => $url
	);
}

$title = preg_replace('/d%/Ums', $keyword, $_LANG['search_results']);

/* 获取meta和title信息 */
$smarty->assign('page_title', $dou->page_title('onepage', '', $title));
$smarty->assign('keywords', $_CFG['site_keywords']);
$smarty->assign('description', $_CFG['site_description']);

/* 初始化导航栏 */
$data = $dou->fetch_array_all('nav', 'sort ASC');
$smarty->assign('nav_top_list', $dou->get_nav($data, 'top'));
$smarty->assign('nav_middle_list', $dou->get_nav($data, 'middle', 0, 'product_category', $cat_id));
$smarty->assign('nav_bottom_list', $dou->get_nav($data, 'bottom'));

/* 初始化数据 */
$smarty->assign('ur_here', $dou->ur_here('onepage', '', $title));
$smarty->assign('keyword', $keyword);
$smarty->assign('product_category', $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'), $cat_id));
$smarty->assign('product_list', $product_list);

$smarty->display('search.dwt');

/* 终止执行文件外的程序 */
exit;


?>