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
 * 管理员列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['manager']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['manager_add'],
		'href' => 'manager.php?rec=add'
	));
	$smarty->assign('cur', 'manager');

	$sql = "SELECT * FROM " . $dou->table('admin') . " ORDER BY user_id ASC";
	$query = $dou->query($sql);
	while ($row = $dou->fetch_array($query))
	{
		$add_time = date("Y-m-d", $row[add_time]);
		$last_login = date("Y-m-d H:i:s", $row[last_login]);

		$manager_list[] = array (
			"user_id" => $row['user_id'],
			"user_name" => $row['user_name'],
			"email" => $row['email'],
			"action_list" => $row['action_list'],
			"add_time" => $add_time,
			"last_login" => $last_login
		);
	}

	$smarty->assign('manager_list', $manager_list);
	$smarty->display('manager.htm');
}

/**
 +----------------------------------------------------------
 * 管理员添加处理
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	if ($user['action_list'] != 'ALL')
	{
		$dou->dou_msg($_LANG['without'], 'manager.php');
	}

	$smarty->assign('ur_here', $_LANG['manager']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['manager_list'],
		'href' => 'manager.php'
	));

	//CSRF防御令牌生成
	$smarty->assign('token', $token = $firewall->set_token());
	$smarty->display('manager.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	//跨站请求伪造CSRF的防御
	if ($firewall->check_token($_POST['token']))
	{
		if (!$_POST['user_name'])
		{
			$dou->dou_msg($_LANG['manager_username_empty']);
		}
		elseif (!$check->is_username($_POST['user_name']))
		{
			$dou->dou_msg($_LANG['manager_username_range']);
		}
		elseif (!$_POST['password'])
		{
			$dou->dou_msg($_LANG['manager_password_empty']);
		}
		elseif (!$check->is_password($_POST['password']))
		{
			$dou->dou_msg($_LANG['manager_password_range']);
		}
		elseif (!$_POST['password_confirm'])
		{
			$dou->dou_msg($_LANG['manager_password_confirm_empty']);
		}
		elseif ($_POST['password'] != $_POST['password_confirm'])
		{
			$dou->dou_msg($_LANG['manager_password_confirm_wrong']);
		}
	
		$password = md5($_POST[password]);
		$add_time = time();
	
		$sql = "INSERT INTO " . $dou->table('admin') . " (user_id, user_name, password, action_list, add_time)" .
		" VALUES (NULL, '$_POST[user_name]', '$password', 'ADMIN', '$add_time')";
		$dou->query($sql);
	
		$dou->create_admin_log($_LANG['manager_add'] . ": " . $_POST[user_name]);
	
		$dou->dou_msg($_LANG['manager_add_succes'], 'manager.php');
	}
	else
	{
		$dou->dou_msg($_LANG['illegal'], 'manager.php');
	}
}

/**
 +----------------------------------------------------------
 * 管理员编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['manager']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['manager_list'],
		'href' => 'manager.php'
	));

	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table('admin'), '*', '`user_id` = \'' . $id . '\'');
	$manager_info = $dou->fetch_array($query);

	if ($user['action_list'] != 'ALL' && $manager_info['user_name'] != $_SESSION['user_name'])
	{
		$dou->dou_msg($_LANG['without'], 'manager.php');
	}
	
	//是超级管理员且修改的不是修改本身
	if ($user['action_list'] == 'ALL' && $manager_info['user_name'] != $_SESSION['user_name'])
	{
		$_SESSION['if_check'] = false;
	}
	else
	{
		$_SESSION['if_check'] = true;
	}

	//CSRF防御令牌生成
	$smarty->assign('token', $token = $firewall->set_token());
	$smarty->assign('if_check', $_SESSION['if_check']);
	$smarty->assign('manager_info', $manager_info);
	$smarty->display('manager.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	//跨站请求伪造CSRF的防御
	if ($firewall->check_token($_POST['token']))
	{
		$user_id = trim($_POST[id]);
		$query = $dou->select($dou->table('admin'), '*', '`user_id` = \'' . $user_id . '\'');
		$manager_info = $dou->fetch_array($query);
	
		$new_password = md5($_POST[new_password]);
	
		//判断管理员账号是否符合规范
		if (!$check->is_username($_POST['user_name']))
		{
			$dou->dou_msg($_LANG['manager_username_range']);
		}
	
		//超级管理员修改普通管理员密码，无需旧密码
		if ($_SESSION['if_check'])
		{
			if (!$_POST['old_password'])
			{
				$dou->dou_msg($_LANG['manager_old_password_empty']);
			}
			elseif (md5($_POST[old_password]) != $manager_info['password'])
			{
				$dou->create_admin_log($_LANG['manager_edit'] . ": " . $_POST[user_name] . " ( " . $_LANG['manager_old_password_wrong'] . " )");
				$dou->dou_msg($_LANG['manager_old_password_wrong']);
			}
		}
	
		//如果有输入新密码，则验证新密码
		if ($_POST[new_password])
		{
			if (!$check->is_password($_POST['new_password']))
			{
				$dou->dou_msg($_LANG['manager_password_confirm_range']);
			}
			elseif (!$_POST['new_password_confirm'])
			{
				$dou->dou_msg($_LANG['manager_new_password_confirm_empty']);
			}
			elseif ($_POST['new_password'] != $_POST['new_password_confirm'])
			{
				$dou->dou_msg($_LANG['manager_new_password_confirm_wrong']);
			}
	
			$update_password = ", password = '$new_password'";
		}
	
	
		$sql = "UPDATE " . $dou->table('admin') . " SET user_name = '$_POST[user_name]'" . $update_password . " WHERE user_id = '$_POST[id]'";
		$dou->query($sql);
	
		$dou->create_admin_log($_LANG['manager_edit'] . ": " . $_POST[user_name]);
	
		$dou->dou_msg($_LANG['manager_edit_succes'], 'manager.php');
	}
	else
	{
		$dou->dou_msg($_LANG['illegal'], 'manager.php');
	}
}

/**
 +----------------------------------------------------------
 * 管理员删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$user_id = trim($_REQUEST['id']);
	$user_name = $dou->get_one("SELECT user_name FROM " . $dou->table('admin') . " WHERE user_id = '$user_id'");
	
	if ($user_name == $_SESSION['user_name'])
	{
		$dou->dou_msg($_LANG['manager_del_wrong'], 'manager.php', '', '3');
	}
	else
	{
		if ($_POST['confirm'])
		{
			$dou->create_admin_log($_LANG['manager_del'] . ": " . $user_name);
			$dou->delete($dou->table('admin'), "user_id = $user_id", 'manager.php');
		}
		else
		{
		 $_LANG['del_check'] = preg_replace('/d%/Ums', $user_name, $_LANG['del_check']);
			$dou->dou_msg($_LANG['del_check'], 'manager.php', '', '30', "manager.php?rec=del&id=$user_id");
		}
	}
}

/**
 +----------------------------------------------------------
 * 操作记录
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'manager_log')
{
	$smarty->assign('ur_here', $_LANG['manager_log']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['manager_list'],
		'href' => 'manager.php'
	));
	$smarty->assign('cur', 'manager_log');

	/* 分页信息 */
	$page = trim($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
	$limit = $dou->pager(admin_log, 15, $page);
	
	$sql = "SELECT * FROM " . $dou->table('admin_log') . $where . " ORDER BY id DESC" . $limit;
	$query = $dou->query($sql);
	while ($row = $dou->fetch_array($query))
	{
		$create_time = date("Y-m-d", $row[create_time]);
		$user_name = $dou->get_one("SELECT user_name FROM " . $dou->table('admin') . " WHERE user_id = " . $row['user_id']);

		$log_list[] = array (
			"id" => $row['id'],
			"create_time" => $create_time,
			"user_name" => $user_name,
			"action" => $row['action'],
			"ip" => $row['ip']
		);
	}

	$smarty->assign('log_list', $log_list);
	$smarty->display('manager.htm');
}




?>