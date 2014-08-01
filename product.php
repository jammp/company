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

/* REQUEST获取 */
$id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$rewrite = $check->is_number($_REQUEST['rewrite']) ? $_REQUEST['rewrite'] : 0;
$_REQUEST['unique_id'] = $check->is_unique_id($_REQUEST['unique_id']) ? $_REQUEST['unique_id'] : 0; 

$cat_id = $dou->get_one("SELECT cat_id FROM " . $dou->table('product') . " WHERE id = '$id'");
$unique_id = $dou->get_one("SELECT unique_id FROM " . $dou->table('product_category') . " WHERE cat_id = '$cat_id'");

/* 伪静态别名验证 */
if ($_REQUEST['unique_id'] && $unique_id != $_REQUEST['unique_id'])
{
	header("Location: " . ROOT_URL . "\n");
	exit();
}
elseif ($_REQUEST['rewrite'] && $unique_id && !$_REQUEST['unique_id'])
{
	header("Location: " . ROOT_URL . "\n");
	exit();
}

$query = $dou->select($dou->table(product), '*', '`id` = \'' . $id . '\'');
$product = $dou->fetch_array($query);

/* 判断商品ID是否存在 */
if (!$product)
{
	header("Location: " . ROOT_URL . "\n");
	exit();
}

/* 预处理数据 */
$product['add_time'] = date("Y-m-d", $product['add_time']);

/* 生成缩略图的文件名 */
$image = explode(".", $product[product_image]);
$product['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
$product['product_image'] = ROOT_URL . $product['product_image'];

/* 格式化价格 */
if ($product['price'] > 0)
{
	$product['price'] = $dou->price_format($product['price']);
}
else
{
	$product['price'] = $_LANG['price_discuss'];
}

/* 格式化自定义参数 */
$defined_product = explode(',', $product['defined']);
foreach ($defined_product as $row)
{
	$row = explode('：', str_replace(":", "：", $row));

	if ($row['1'])
	{
		$defined[] = array (
			"arr" => $row['0'],
			"value" => $row['1']
		);
	}
}

/* 获取meta和title信息 */
$smarty->assign('page_title', $dou->page_title('product_category', $cat_id, $product['product_name']));
$smarty->assign('keywords', $product['keywords']);
$smarty->assign('description', $product['description']);

/* 初始化导航栏 */
$data = $dou->fetch_array_all('nav', 'sort ASC');
$smarty->assign('nav_top_list', $dou->get_nav($data, 'top'));
$smarty->assign('nav_middle_list', $dou->get_nav($data, 'middle', '0', 'product_category', $cat_id));
$smarty->assign('nav_bottom_list', $dou->get_nav($data, 'bottom'));

/* 初始化数据 */
$smarty->assign('ur_here', $dou->ur_here('product_category', $cat_id, $product['product_name']));
$smarty->assign('product_category', $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'), $cat_id));
$smarty->assign('product', $product);
$smarty->assign('defined', $defined);

$smarty->display('product.dwt');
?>