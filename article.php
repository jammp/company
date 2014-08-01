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
$cat_id = $dou->get_one("SELECT cat_id FROM " . $dou->table('article') . " WHERE id = '$id'");
$unique_id = $dou->get_one("SELECT unique_id FROM " . $dou->table('article_category') . " WHERE cat_id = '$cat_id'");

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

$query = $dou->select($dou->table(article), '*', '`id` = \'' . $id . '\'');
$article = $dou->fetch_array($query);

/* 判断文章ID是否存在 */
if (!$article)
{
	header("Location: " . ROOT_URL . "\n");
	exit();
}

/* 预处理数据 */
$article['add_time'] = date("Y-m-d", $article['add_time']);

/* 格式化自定义参数 */
$defined_article = explode(',', $article['defined']);
foreach ($defined_article as $row)
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

/* 访问统计 */
$click = $article['click'] + 1;
$sql = "update " . $dou->table('article') . " SET click = '$click' WHERE id = '$id'";
$dou->query($sql);

/* 获取meta和title信息 */
$smarty->assign('page_title', $dou->page_title('article_category', $cat_id, $article['title']));
$smarty->assign('keywords', $article['keywords']);
$smarty->assign('description', $article['description']);

/* 初始化导航栏 */
$data = $dou->fetch_array_all('nav', 'sort ASC');
$smarty->assign('nav_top_list', $dou->get_nav($data, 'top'));
$smarty->assign('nav_middle_list', $dou->get_nav($data, 'middle', '0', 'article_category', $cat_id));
$smarty->assign('nav_bottom_list', $dou->get_nav($data, 'bottom'));

/* 初始化数据 */
$smarty->assign('ur_here', $dou->ur_here('article_category', $cat_id, $article['title']));
$smarty->assign('article_category', $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'), $cat_id));
$smarty->assign('article', $article);
$smarty->assign('defined', $defined);

$smarty->display('article.dwt', $id);
?>