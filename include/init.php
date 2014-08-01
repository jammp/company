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

/* error_reporting */
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

/* 关闭 set_magic_quotes_runtime*/
@ set_magic_quotes_runtime(0);

/* 调整时区 */
if (PHP_VERSION >= '5.1')
{
	date_default_timezone_set('PRC');
}

/* 取得当前站点所在的根目录 */
define('ROOT_PATH', str_replace('include/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_URL', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . "/");

if (!file_exists(ROOT_PATH . "data/install.lock"))
{
	header("Location: " . ROOT_URL . "install/index.php\n");
	exit ();
}

require (ROOT_PATH . 'data/config.php');
require (ROOT_PATH . 'include/smarty/Smarty.class.php');
require (ROOT_PATH . 'include/mysql.class.php');
require (ROOT_PATH . 'include/common.class.php');
require (ROOT_PATH . 'include/action.class.php');
require (ROOT_PATH . 'include/check.class.php');
require (ROOT_PATH . 'include/firewall.class.php');

/* 实例化类 */
$dou = new Action($dbhost, $dbuser, $dbpass, $dbname, $prefix, DOU_CHARSET);
$check = new Check();
$firewall = new Firewall();

/* 定义DOU_SHELL */
define('DOU_SHELL', $dou->get_one("SELECT value FROM " . $dou->table('config') . " WHERE name = 'hash_code'"));

if (!defined('EXIT_INIT'))
{
	/* 豆壳防火墙 */
	$firewall->dou_firewall();

	//读取站点信息
	$_CFG = $dou->get_config();
	$theme = $_CFG['site_theme'];
	if ($_CFG['qq'])
	{
		$_CFG['qq'] = explode(',', $_CFG['qq']);
	}

	//SMARTY配置
	$smarty = new smarty();
	$smarty->config_dir = ROOT_PATH . "include/smarty/Config_File.class.php"; //目录变量
	$smarty->caching = false; //是否使用缓存
	$smarty->template_dir = ROOT_PATH . "theme/" . $theme; //模板存放目录
	$smarty->compile_dir = ROOT_PATH . "cache/compile"; //编译目录
	$smarty->cache_dir = ROOT_PATH . "cache/static"; //缓存目录
	$smarty->left_delimiter = "{"; //左定界符
	$smarty->right_delimiter = "}"; //右定界符

	//载入语言文件
	require (ROOT_PATH . 'languages/' . $_CFG['language'] . '/web.lang.php');
	$_LANG['copyright'] = preg_replace('/d%/Ums', $_CFG['site_name'], $_LANG['copyright']);

	//判断是否关闭站点
	if ($_CFG['site_closed'])
	{
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><div style=\"margin: 200px; text-align: center; font-size: 14px\"><p>" . $_LANG['site_closed'] . "</p><p></p></div>";
		exit ();
	}

	//通用信息调用
	$smarty->assign("lang", $_LANG);
	$smarty->assign("site", $_CFG);
	$smarty->assign("root_url", ROOT_URL);

	//Smarty 过滤器
	function remove_html_comments($source, & $smarty)
	{
		global $theme;
		$theme_path = ROOT_URL . 'theme';
		$source = preg_replace('/images\//Ums', "theme/$theme/images/", $source);
		$source = preg_replace('/\.*\/theme\//Ums', "theme/", $source);
		$source = preg_replace('/link href\=\"style\.css/Ums', "link href=\"theme/$theme/style.css", $source);
		$source = preg_replace('/theme\//Ums', "$theme_path/", $source);
		$source = preg_replace('/^<meta\shttp-equiv=["|\']Content-Type["|\']\scontent=["|\']text\/html;\scharset=(?:.*?)["|\'][^>]*?>\r?\n?/i', '', $source);
		return $source = preg_replace('/<!--.*{(.*)}.*-->/U', '{$1}', $source);
	}
	$smarty->register_prefilter('remove_html_comments');
}







?>