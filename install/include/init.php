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

/* 开启SESSION */
session_start();

/* 取得当前站点所在的根目录 */
define('ROOT_PATH', str_replace('include/init.php', '', str_replace('\\', '/', __FILE__)));
define('DOUPHP_PATH', dirname(dirname(dirname(__FILE__))) . '/');
define('ROOT_URL', preg_replace('/install\//Ums', '', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . '/'));

require (DOUPHP_PATH . '/include/smarty/Smarty.class.php');
require (DOUPHP_PATH . '/include/mysql.class.php');
require (ROOT_PATH . 'include/install.class.php');
require (ROOT_PATH . 'include/language.class.php');

/* 初始化 */
$install = new Install('utf8');
$douphp_config = DOUPHP_PATH . 'data/config.php';
$file_lock = DOUPHP_PATH . 'data/install.lock';

//SMARTY配置
$smarty = new smarty();
$smarty->config_dir = DOUPHP_PATH . "/include/smarty/Config_File.class.php"; //目录变量
$smarty->caching = false; //是否使用缓存
$smarty->template_dir = ROOT_PATH . "template"; //模板存放目录
$smarty->compile_dir = ROOT_PATH . "data/cache"; //编译目录
$smarty->cache_dir = ROOT_PATH . "data/cache/static"; //缓存目录
$smarty->left_delimiter = "{"; //左定界符
$smarty->right_delimiter = "}"; //右定界符

/* 载入语言文件 */
require (ROOT_PATH . 'include/language.class.php');

//通用信息调用
$smarty->assign("lang", $_LANG);

//Smarty 过滤器
function remove_html_comments($source, & $smarty)
{
	return $source = preg_replace('/<!--.*{(.*)}.*-->/U', '{$1}', $source);
}
$smarty->register_prefilter('remove_html_comments');
?>