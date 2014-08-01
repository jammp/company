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
define('NO_CHECK', true);

require(dirname(__FILE__) . '/include/init.php');

/* rec操作项的初始化 */
if (empty ($_REQUEST['rec']))
{
	$_REQUEST['rec'] = 'default';
}
else
{
	$_REQUEST['rec'] = trim($_REQUEST['rec']);
}

$smarty->assign('rec', $_REQUEST['rec']);

/**
 +----------------------------------------------------------
 * 文章列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	$smarty->display('login.htm');
}

/**
 +----------------------------------------------------------
 * 登陆验证
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'login')
{
	if($check->is_captcha(trim($_POST['vcode'])) && $_CFG['captcha'])
	{
		$_POST['vcode'] = strtoupper(trim($_POST['vcode']));
	}

	if (!$_POST['user_name'])
	{
		$dou->dou_msg($_LANG['login_input_wrong'], 'login.php', 'out');
		exit;
	}
	elseif (md5($_POST['vcode'] . DOU_SHELL) != $_SESSION['captcha'] && $_CFG['captcha'])
	{
		$dou->dou_msg($_LANG['login_vcode_wrong'], 'login.php', 'out');
		exit;
	}
	
	$_POST['user_name'] = $check->is_username(trim($_POST['user_name'])) ? trim($_POST['user_name']) : '';
	$_POST['password'] = $check->is_password(trim($_POST['password'])) ? trim($_POST['password']) : '';
	
	$query = $dou->select($dou->table(admin), '*', "user_name = '$_POST[user_name]'");
	$user = $dou->fetch_array($query);
	
	if (!is_array($user))
	{
		$dou->create_admin_log($_LANG['login_action'] . ": " . $_POST['user_name'] . " ( " . $_LANG['login_user_name_wrong'] . " ) ");
		$dou->dou_msg($_LANG['login_input_wrong'], 'login.php', 'out');
		exit;
	}
	elseif (md5($_POST['password']) != $user['password'])
	{
		if ($_POST['password'])
		{
		  $dou->create_admin_log($_LANG['login_action'] . ": " . $_POST['user_name'] . " ( " . $_LANG['login_password_wrong'] . " ) ");
		}
		$dou->dou_msg($_LANG['login_input_wrong'], 'login.php', 'out');
		exit;
	}

	$_SESSION['user_id'] = $user['user_id'];
	$_SESSION['user_name'] = $user['user_name'];
	$_SESSION['shell'] = md5($user['user_name'] . $user['password'] . DOU_SHELL);
	$_SESSION['ontime'] = time();

	$last_login = time();
	$last_ip = $dou->get_ip();
	$sql = "update " . $dou->table('admin') . " SET last_login = '$last_login', last_ip = '$last_ip' WHERE user_id = " . $user['user_id'];
	$dou->query($sql);

	$dou->create_admin_log($_LANG['login_action'] . ": " . $_LANG['login_success']);

	header("Location: " . ROOT_URL . ADMIN_PATH . "/index.php\n");
	exit();
}

/**
 +----------------------------------------------------------
 * 退出登录
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'logout')
{
	session_destroy();
	header("Location: " . ROOT_URL . ADMIN_PATH . "/login.php\n");
}
?>