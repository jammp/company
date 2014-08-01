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
$smarty->assign('cur', 'nav');

/**
 +----------------------------------------------------------
 * 导航列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['nav']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['nav_add'],
		'href' => 'nav.php?rec=add'
	));

	/* 获得请求的导航类型 */
	$type = $_REQUEST['type'] ? trim($_REQUEST['type']) : 'middle';
	
	$nav_list = $dou->get_nav($dou->fetch_array_all('nav', 'sort ASC'), $type);

	$smarty->assign('type', $type);
	$smarty->assign('nav_list', $nav_list);
	$smarty->display('nav.htm');
}

/**
 +----------------------------------------------------------
 * 导航添加处理
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	$smarty->assign('ur_here', $_LANG['nav']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['nav_list'],
		'href' => 'nav.php'
	));

	//初始化导航调用到的数据
	$page_list = $dou->get_page_list($dou->fetch_array_all('page'));
	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));
	$article_category = $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'));
	$nav_list = $dou->get_nav($dou->fetch_array_all('nav', 'sort ASC'));

	$smarty->assign('page_list', $page_list);
	$smarty->assign('product_category', $product_category);
	$smarty->assign('article_category', $article_category);
	$smarty->assign('nav_list', $nav_list);
	$smarty->display('nav.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	$nav_menu = explode(",", $_POST[nav_menu]);
	$module = $nav_menu[0];
	
	if ($module == 'nav')
	{
		$guide = $dou->auto_http(trim($_POST['guide']));
		$nav_name = $_POST[nav_name];
	}
	else
	{
		$guide = $nav_menu[1];
		$nav_name = $nav_menu[2];
	}

	$sql = "INSERT INTO " . $dou->table('nav') . " (id, module, nav_name, guide, parent_id, type, sort)" .
	" VALUES (NULL, '$module', '$nav_name', '$guide', '$_POST[parent_id]', '$_POST[type]', '$_POST[sort]')";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['nav_add'] . ": " . $nav_name);

	$dou->dou_msg($_LANG['nav_add_succes'], "nav.php?type=" . $_POST[type]);
}

/**
 +----------------------------------------------------------
 * 导航编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['nav']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['nav_list'],
		'href' => 'nav.php'
	));

	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table('nav'), '*', '`id` = \'' . $id . '\'');
	$nav_info = $dou->fetch_array($query);

	if ($nav_info['module'] == 'nav')
	{
		$nav_info['guide'] = $nav_info['guide'];
	}
	else
	{
		$nav_info['guide'] = $dou->rewrite_url($nav_info['module'], $nav_info['guide']);
	}

	//初始化导航调用到的数据
	$page_list = $dou->get_page_list($dou->fetch_array_all('page'));
	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));
	$article_category = $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'));
	$nav_list = $dou->get_nav($dou->fetch_array_all('nav', 'sort ASC'), $nav_info['type'], '0', '0', $id);

	$smarty->assign('page_list', $page_list);
	$smarty->assign('product_category', $product_category);
	$smarty->assign('article_category', $article_category);
	$smarty->assign('nav_list', $nav_list);
	$smarty->assign('nav_info', $nav_info);
	$smarty->display('nav.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	/* 上传图片生成 */
	if ($_POST['guide'])
	{
		$_POST['guide'] = $dou->auto_http(trim($_POST['guide']));
		$guide = ", guide='$_POST[guide]'";
	}

	$sql = "update " . $dou->table('nav') . " SET nav_name = '$_POST[nav_name]'" . $guide . ",parent_id = '$_POST[parent_id]', type = '$_POST[type]', sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['nav_edit'] . ": " . $_POST[nav_name]);

	$dou->dou_msg($_LANG['nav_edit_succes'], "nav.php?type=" . $_POST[type]);
}

/**
 +----------------------------------------------------------
 * 导航编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'nav_list')
{
	$type = $_GET['type'] ? trim($_GET['type']) : 'middle';
	$id = trim($_REQUEST['id']);
	$parent_id = $dou->get_one("SELECT parent_id FROM " . $dou->table('nav') . " WHERE id = '$id'");

	$nav_list = $dou->get_nav($dou->fetch_array_all('nav', 'sort ASC'), $type, '0', '0', $id);
	$select .= '<select name="parent_id">';
	$select .= '<option value="0">' . $_LANG['empty'] . '</option>';
	foreach ($nav_list as $value)
	{
		$select .= '<option value="' . $value['id'] . '" ';
		$select .= ($value['id'] == $parent_id) ? "selected='ture'" : '';
		$select .= '>' . $value['mark'] . ' ';
		$select .= htmlspecialchars($value['nav_name'], ENT_QUOTES) . '</option>';
	}
	$select .= '</select>';
	
	echo $select;
}

/**
 +----------------------------------------------------------
 * 导航删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$id = trim($_REQUEST['id']);

	$nav_name = $dou->get_one("SELECT nav_name FROM " . $dou->table('nav') . " WHERE id = '$id'");
	$is_parent = $dou->get_one("SELECT id FROM " . $dou->table('nav') . " WHERE parent_id = '$id'");
	
	if ($is_parent)
	{
		$_LANG['nav_del_is_parent'] = preg_replace('/d%/Ums', $nav_name, $_LANG['nav_del_is_parent']);
		$dou->dou_msg($_LANG['nav_del_is_parent'], 'nav.php', '', '3');
	}
	else
	{
		if ($_POST['confirm'])
		{
			$dou->create_admin_log($_LANG['nav_del'] . ": " . $nav_name);
			$dou->delete($dou->table('nav'), "id = $id", 'nav.php');
		}
		else
		{
			$_LANG['del_check'] = preg_replace('/d%/Ums', $nav_name, $_LANG['del_check']);
			$dou->dou_msg($_LANG['del_check'], 'nav.php', '', '30', "nav.php?rec=del&id=$id");
		}
	}

}



?>