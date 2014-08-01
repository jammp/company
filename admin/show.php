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
include_once(ROOT_PATH . 'include/upload.class.php');

$images_dir = 'data/slide/'; //文件上传路径 结尾加斜杠
$thumb_dir = 'thumb/'; //缩略图路径（必须在$images_dir下建立） 结尾加斜杠
$img = new Upload(ROOT_PATH . $images_dir, $thumb_dir); //实例化类文件

/* rec操作项的初始化 */
if (empty ($_REQUEST['rec']))
{
	$_REQUEST['rec'] = 'default';
}
else
{
	$_REQUEST['rec'] = trim($_REQUEST['rec']);
}

/* rec操作项的初始化 */
$rec = trim($_REQUEST['rec']);
$smarty->assign('cur', 'show');

/* 对页面进行相应赋值 */
$smarty->assign('rec', $rec);
$smarty->assign('ur_here', $_LANG['show']);
$smarty->assign('show_list', get_show_list());

/**
 +----------------------------------------------------------
 * 幻灯列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
  $smarty->display('show.htm');
}

/**
 +----------------------------------------------------------
 * 幻灯添加处理
 +----------------------------------------------------------
 */
elseif ($rec == 'insert')
{
	/* 上传图片生成 */
	$name = date('Ymd');
	for ($i = 0; $i < 6; $i++)
	{
		$name .= chr(mt_rand(97, 122));
	}

	$upfile = $img->upload_image('show_img', "$name"); //上传的文件域
	$file = $images_dir . $upfile;
	$img->to_file = true;
	$img->make_thumb($upfile, 100, 100);

	$sql = "INSERT INTO " . $dou->table('show') . " (id, show_name, show_link, show_img, sort)" .
	" VALUES (NULL, '$_POST[show_name]', '$_POST[show_link]', '$file', '$_POST[sort]')";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['show_add'] . ": " . $_POST[show_name]);
	$dou->dou_msg($_LANG['show_add_succes'], 'show.php');
}

/**
 +----------------------------------------------------------
 * 幻灯编辑
 +----------------------------------------------------------
 */
elseif ($rec == 'edit')
{
	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table('show'), '*', '`id` = \'' . $id . '\'');
	$show = $dou->fetch_array($query);

	$smarty->assign('id', $id);
	$smarty->assign('show', $show);
	
  $smarty->display('show.htm');
}

elseif ($rec == 'update')
{
	/* 分析广告图片名称 */
	$basename = basename($_POST['show_img']);
	$file_name = substr($basename, 0, strrpos($basename, '.'));

	/* 上传图片生成 */
	if ($_FILES['show_img']['name'] != "")
	{
		$upfile = $img->upload_image('show_img', "$file_name"); //上传的文件域
		$file = $images_dir . $upfile;
		$img->to_file = true;
		$img->make_thumb($upfile, 100, 100);

		$up_file = ", image='$file'";
	}

	$sql = "update " . $dou->table('show') . " SET show_name='$_POST[show_name]'" . $up_file . " ,show_link = '$_POST[show_link]', sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['show_edit'] . ": " . $_POST[show_name]);

	$dou->dou_msg($_LANG['show_edit_succes'], 'show.php');
}

/**
 +----------------------------------------------------------
 * 幻灯删除
 +----------------------------------------------------------
 */
elseif ($rec == 'del')
{
	$id = trim($_REQUEST['id']);
	$show_name = $dou->get_one("SELECT show_name FROM " . $dou->table('show') . " WHERE id = '$id'");

	if ($_POST['confirm'])
	{
		/* 删除相应商品图片 */
		$show_img = $dou->get_one("SELECT show_img FROM " . $dou->table('show') . " WHERE id = '$id'");
		$file_name = basename($show_img);
		$image = explode(".", $file_name);
		$show_img_thumb = $images_dir . $thumb_dir . $image['0'] . "_thumb." . $image['1'];
		@unlink(ROOT_PATH . $show_img);
		@unlink(ROOT_PATH . $show_img_thumb);

		$dou->create_admin_log($_LANG['show_del'] . ": " . $show_name);
		$dou->delete($dou->table('show'), "id = $id", 'show.php');
	}
	else
	{
		$_LANG['del_check'] = preg_replace('/d%/Ums', $show_name, $_LANG['del_check']);
		$dou->dou_msg($_LANG['del_check'], 'show.php', '', '30', "show.php?rec=del&id=$id");
	}
}

/**
 +----------------------------------------------------------
 * 获取下级幻灯列表
 +----------------------------------------------------------
 */
function get_show_list()
{
	$sql = "SELECT * FROM " . $GLOBALS['dou']->table('show') . " ORDER BY sort ASC, id ASC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		$file_name = basename($row['show_img']);
		$image = explode(".", $file_name);
		$thumb = ROOT_URL . $GLOBALS['images_dir'] . $GLOBALS['thumb_dir'] . $image['0'] . "_thumb." . $image['1'];

		$show_list[] = array (
			"id" => $row['id'],
			"show_name" => $row['show_name'],
			"show_link" => $row['show_link'],
			"show_img" => $row['show_img'],
			"thumb" => $thumb,
			"sort" => $row['sort']
		);
	}

	return $show_list;
}


?>