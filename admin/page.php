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
$smarty->assign('cur', 'page');

/**
 +----------------------------------------------------------
 * 单页面列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['page_list']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['page_add'],
		'href' => 'page.php?rec=add'
	));

	$page_list = $dou->get_page_list($dou->fetch_array_all('page'));

	$smarty->assign('page_list', $page_list);
	$smarty->display('page.htm');
}

/**
 +----------------------------------------------------------
 * 单页面添加
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	$smarty->assign('ur_here', $_LANG['page_add']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['page_list'],
		'href' => 'page.php'
	));

	$page_list = $dou->get_page_list($dou->fetch_array_all('page'));

	$smarty->assign('form_action', 'insert');
	$smarty->assign('page_list', $page_list);
	$smarty->display('page.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	$sql = "INSERT INTO " . $dou->table('page') . " (id, unique_id, parent_id, page_name, content ,keywords, description)" .
	" VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[page_name]', '$_POST[content]', '$_POST[keywords]', '$_POST[description]')";
	$dou->query($sql);
	
	if (!$check->is_unique_id($_POST['unique_id']))
	{
		$dou->dou_msg($_LANG['unique_id_wrong'], 'page.php?rec=edit&id=' . mysql_insert_id(), '', '5');
	}

	$dou->create_admin_log($_LANG['page_add'] . ": " . $_POST[page_name]);
	$dou->dou_msg($_LANG['page_add_succes'], 'page.php');
}

/**
 +----------------------------------------------------------
 * 单页面编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['page_edit']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['page_list'],
		'href' => 'page.php'
	));

	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table(page), '*', '`id` = \'' . $id . '\'');
	$page = $dou->fetch_array($query);

	$page_list = $dou->get_page_list($dou->fetch_array_all('page'), '0', '0', $id);

	$smarty->assign('form_action', 'update');
	$smarty->assign('page_list', $page_list);
	$smarty->assign('page', $page);
	$smarty->display('page.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	$sql = "UPDATE " . $dou->table('page') . " SET unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', page_name = '$_POST[page_name]', content = '$_POST[content]', keywords = '$_POST[keywords]', description = '$_POST[description]' WHERE id = '$_POST[id]'";
	$dou->query($sql);
	
	if (!$check->is_unique_id($_POST['unique_id']))
	{
		$dou->dou_msg($_LANG['unique_id_wrong'], 'page.php?rec=edit&id=' . $_POST['id'], '', '5');
	}

	$dou->create_admin_log($_LANG['page_edit'] . ": " . $_POST['page_name']);
	$dou->dou_msg($_LANG['page_edit_succes'], 'page.php', '', '3');
}

/**
 +----------------------------------------------------------
 * 单页面删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$id = trim($_REQUEST['id']);
	$page_name = $dou->get_one("SELECT page_name FROM " . $dou->table('page') . " WHERE id = '$id'");
	$is_parent = $dou->get_one("SELECT id FROM " . $dou->table('page') . " WHERE parent_id = '$id'");

	if ($id == '1')
	{
		$dou->dou_msg($_LANG['page_del_wrong'], 'page.php', '', '3');
	}
	elseif ($is_parent)
	{
		$_LANG['page_del_is_parent'] = preg_replace('/d%/Ums', $page_name, $_LANG['page_del_is_parent']);
		$dou->dou_msg($_LANG['page_del_is_parent'], 'page.php', '', '3');
	}
	else
	{
		if ($_POST['confirm'])
		{
			$dou->create_admin_log($_LANG['page_del'] . ": " . $page_name);
			$dou->delete($dou->table('page'), "id = $id", 'page.php');
		}
		else
		{
		  $_LANG['del_check'] = preg_replace('/d%/Ums', $page_name, $_LANG['del_check']);
		  $dou->dou_msg($_LANG['del_check'], 'page.php', '', '30', "page.php?rec=del&id=$id");
		}
	}
}



?>