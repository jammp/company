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

require (dirname(__FILE__) . '/include/init.php');

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
$smarty->assign('cur', 'product_category');

/**
 +----------------------------------------------------------
 * 分类列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['product_category']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product_category_add'],
		'href' => 'product_category.php?rec=add'
	));

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));

	$smarty->assign('product_category', $product_category);
	$smarty->display('product_category.htm');
}

/**
 +----------------------------------------------------------
 * 分类添加
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	$smarty->assign('ur_here', $_LANG['product_category_add']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product_category'],
		'href' => 'product_category.php'
	));

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));

	$smarty->assign('form_action', 'insert');
	$smarty->assign('product_category', $product_category);
	$smarty->display('product_category.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	$sql = "INSERT INTO " . $dou->table('product_category') . " (cat_id, unique_id, parent_id, cat_name, keywords, description, sort)" .
	" VALUES (NULL, '$_POST[unique_id]', '$_POST[parent_id]', '$_POST[cat_name]', '$_POST[keywords]', '$_POST[description]', '$_POST[sort]')";
	$dou->query($sql);

	if (!$check->is_unique_id($_POST['unique_id']))
	{
		$dou->dou_msg($_LANG['unique_id_wrong'], 'product_category.php?rec=edit&cat_id=' . mysql_insert_id(), '', '5');
	}

	$dou->create_admin_log($_LANG['product_category_add'] . ": " . $_POST[cat_name]);
	$dou->dou_msg($_LANG['product_category_add_succes'], 'product_category.php');
}

/**
 +----------------------------------------------------------
 * 分类编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['product_category_edit']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product_category'],
		'href' => 'product_category.php'
	));

	$cat_id = trim($_REQUEST['cat_id']);

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'), '0', '0', $cat_id);

	$query = $dou->select($dou->table('product_category'), '*', '`cat_id` = \'' . $cat_id . '\'');
	$cat_info = $dou->fetch_array($query);

	$smarty->assign('form_action', 'update');
	$smarty->assign('product_category', $product_category);
	$smarty->assign('cat_info', $cat_info);
	$smarty->display('product_category.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	$sql = "update " . $dou->table('product_category') . " SET cat_name = '$_POST[cat_name]', unique_id = '$_POST[unique_id]', parent_id = '$_POST[parent_id]', keywords = '$_POST[keywords]', description = '$_POST[description]', sort = '$_POST[sort]' WHERE cat_id = '$_POST[cat_id]'";
	$dou->query($sql);

	if (!$check->is_unique_id($_POST['unique_id']))
	{
		$dou->dou_msg($_LANG['unique_id_wrong'], 'product_category.php?rec=edit&cat_id=' . $_POST['cat_id'], '', '5');
	}

	$dou->create_admin_log($_LANG['product_category_edit'] . ": " . $_POST[cat_name]);
	$dou->dou_msg($_LANG['product_category_edit_succes'], 'product_category.php');

}

/**
 +----------------------------------------------------------
 * 分类删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$cat_id = trim($_REQUEST['cat_id']);

	$cat_name = $dou->get_one("SELECT cat_name FROM " . $dou->table('product_category') . " WHERE cat_id = '$cat_id'");
	$is_parent = $dou->get_one("SELECT id FROM " . $dou->table('product') . " WHERE cat_id = '$cat_id'") . $dou->get_one("SELECT cat_id FROM " . $dou->table('product_category') . " WHERE parent_id = '$cat_id'");

	if ($is_parent)
	{
		$_LANG['product_category_del_is_parent'] = preg_replace('/d%/Ums', $cat_name, $_LANG['product_category_del_is_parent']);
		$dou->dou_msg($_LANG['product_category_del_is_parent'], 'product_category.php', '', '3');
	}
	else
	{
		if ($_POST['confirm'])
		{
			$dou->create_admin_log($_LANG['product_category_del'] . ": " . $cat_name);
			$dou->delete($dou->table(product_category), "cat_id = $cat_id", 'product_category.php');
		}
		else
		{
			$_LANG['del_check'] = preg_replace('/d%/Ums', $cat_name, $_LANG['del_check']);
			$dou->dou_msg($_LANG['del_check'], 'product_category.php', '', '30', "product_category.php?rec=del&cat_id=$cat_id");
		}
	}

}
?>