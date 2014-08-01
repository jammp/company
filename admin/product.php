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

$images_dir = 'images/product/'; //文件上传路径，结尾加斜杠
$thumb_dir = ''; //缩略图路径（必须在$images_dir下建立） 结尾加斜杠
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

$smarty->assign('rec', $_REQUEST['rec']);
$smarty->assign('cur', $cur = 'product');

/**
 +----------------------------------------------------------
 * 产品列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['product']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product_add'],
		'href' => 'product.php?rec=add'
	));

	/* 获得请求的分类 ID */
	$cat_id = trim($_POST['cat_id']);
	$keyword = trim($_POST['keyword']);

	$child_id = $dou->dou_child_id($dou->fetch_array_all('product_category'), $cat_id);

	if ($cat_id) $where .= " cat_id IN (" . $cat_id . $child_id . ") ";
	if ($cat_id && $keyword) $where .= 'AND';
	if ($keyword) $where .= " product_name LIKE '%$keyword%' ";

	$where = $where ? ' WHERE' . $where : '';

	/* 分页信息 */
	$page = trim($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
	$limit = $dou->pager(product, '15', $page, $cat_id);

	$sql = "SELECT id, product_name, cat_id, add_time FROM " . $dou->table('product') . $where . "ORDER BY id DESC" . $limit;
	$query = $dou->query($sql);

	while ($row = $dou->fetch_array($query))
	{
		$cat_name = $dou->get_one("SELECT cat_name FROM " . $dou->table('product_category') . " WHERE cat_id = '$row[cat_id]'");
		$add_time = date("Y-m-d", $row['add_time']);

		$product_list[] = array (
			"id" => $row['id'],
			"cat_id" => $row['cat_id'],
			"cat_name" => $cat_name,
			"product_name" => $row['product_name'],
			"add_time" => $add_time
		);
	}
	
	/* 首页显示商品数量限制框 */
	for ($i=1; $i<=$_CFG['home_display_product']; $i++)
	{
    $home_sort_bg .= "<li><em></em></li>";
  }

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));

	$smarty->assign('if_home_sort', $_SESSION['if_home_sort']);
	$smarty->assign('home_sort', get_home_sort());
	$smarty->assign('home_sort_bg', $home_sort_bg);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('keyword', $keyword);
	$smarty->assign('product_category', $product_category);
	$smarty->assign('product_list', $product_list);
	$smarty->display('product.htm');
}

/**
 +----------------------------------------------------------
 * 产品添加
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	$smarty->assign('ur_here', $_LANG['product_add']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product'],
		'href' => 'product.php'
	));

	/* 格式化自定义参数 */
	if ($_CFG['defined_product'])
	{
		$defined = explode(',', $_CFG['defined_product']);
		foreach ($defined as $row)
		{
			$defined_product .= $row . "：\n";
		}
		$product['defined'] = trim($defined_product);
		$product['defined_count'] = count(explode("\n", $product['defined'])) * 2;
	}

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));

	$smarty->assign('form_action', 'insert');
	$smarty->assign('product_category', $product_category);
	$smarty->assign('product', $product);
	$smarty->display('product.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	/* 判断是否有上传图片/上传图片生成 */
	if ($_FILES['product_image']['name'] != "")
	{
		$upfile = $img->upload_image('product_image', "$id"); //上传的文件域
		$file = $images_dir . $upfile;
		$img->to_file = true;
		$img->make_thumb($upfile, $_CFG['thumb_width'], $_CFG['thumb_height']);
	}
	$add_time = time();
	if (!$check->is_price($_POST['price']))
	{
		$_POST[price] = '0.00';
		$price_wrong  = true;
	}

	/* 格式化自定义参数 */
	$_POST['defined'] = str_replace("\n", ",", $_POST['defined']);
	
	$sql = "INSERT INTO " . $dou->table('product') . " (id, cat_id, product_name, price, defined, content, product_image ,keywords, add_time, description)" .
	" VALUES (NULL, '$_POST[cat_id]', '$_POST[product_name]', '$_POST[price]', '$_POST[defined]', '$_POST[content]', '$file', '$_POST[keywords]', '$add_time', '$_POST[description]')";
	$dou->query($sql);

	/* 判断是否有上传图片 */
	if ($_FILES['product_image']['name'] != "")
	{
		/* 格式化图片名称 */
		$good_id = mysql_insert_id();
		$no_ext = explode(".", $file);
		$file_thumb = $no_ext[0] . '_thumb' . '.' . $no_ext[1];
		$new_name = $images_dir . $good_id . '.' . $no_ext[1];
		$new_name_thumb = $images_dir . $good_id . '_thumb' . '.' . $no_ext[1];

		/* 图片存在的话，先删除图片 */
		@ unlink('../' . $new_name);
		@ unlink('../' . $new_name_thumb);

		/* 重命名图片名称 */
		rename('../' . $file, '../' . $new_name);
		rename('../' . $file_thumb, '../' . $new_name_thumb);
		$resql = "update " . $dou->table('product') . " SET product_image='$new_name' WHERE id='$good_id'";
	}
	$dou->query($resql);
	
	if ($price_wrong)
	{
		$dou->dou_msg($_LANG['price_wrong'], 'product.php?rec=edit&id=' . mysql_insert_id(), '', '5');
	}

	$dou->create_admin_log($_LANG['product_add'] . ": " . $_POST['product_name']);
	$dou->dou_msg($_LANG['product_add_succes'], 'product.php');

}

/**
 +----------------------------------------------------------
 * 产品编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['product_edit']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['product'],
		'href' => 'product.php'
	));

	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table(product), '*', '`id` = \'' . $id . '\'');
	$product = $dou->fetch_array($query);

	/* 格式化自定义参数 */
	if ($_CFG['defined_product'] || $product['defined'])
	{
		$defined = explode(',', $_CFG['defined_product']);
		foreach ($defined as $row)
		{
			$defined_product .= $row . "：\n";
		}
	
		if ($product['defined'])
		{
			$product['defined'] = str_replace(",", "\n", $product['defined']);
		}
		else
		{
			$product['defined'] = trim($defined_product);
		}
	
		$product['defined_count'] = count(explode("\n", $product['defined'])) * 2;
	}

	$product_category = $dou->get_product_category($dou->fetch_array_all('product_category', 'sort ASC'));

	$smarty->assign('form_action', 'update');
	$smarty->assign('product_category', $product_category);
	$smarty->assign('product', $product);
	$smarty->display('product.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	/* 分析商品图片名称 */
	$basename = basename($_POST['product_image']);
	$file_name = substr($basename, 0, strrpos($basename, '.'));

	/* 上传图片生成 */
	if ($_FILES['product_image']['name'] != "")
	{
		$upfile = $img->upload_image('product_image', $file_name); //上传的文件域
		$file = $images_dir . $upfile;
		$img->to_file = true;
		$img->make_thumb($upfile, $_CFG['thumb_width'], $_CFG['thumb_height']);

		$up_file = ", product_image='$file'";
	}

	/* 格式化自定义参数 */
	$_POST['defined'] = str_replace("\n", ",", $_POST['defined']);

	$sql = "update " . $dou->table('product') . " SET cat_id = '$_POST[cat_id]', product_name = '$_POST[product_name]', price = '$_POST[price]', defined = '$_POST[defined]' ,content = '$_POST[content]'" . $up_file . ", keywords = '$_POST[keywords]', description = '$_POST[description]' WHERE id = '$_POST[id]'";
	$dou->query($sql);
	
	if (!$check->is_price($_POST['price']))
	{
		$dou->dou_msg($_LANG['price_wrong'], 'product.php?rec=edit&id=' . $_POST['id'], '', '5');
	}

	$dou->create_admin_log($_LANG['product_edit'] . ": " . $_POST[product_name]);
	$dou->dou_msg($_LANG['product_edit_succes'], 'product.php');
}

/**
 +----------------------------------------------------------
 * 首页商品筛选
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'home_sort')
{
	$_SESSION['if_home_sort'] = true;
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

/**
 +----------------------------------------------------------
 * 首页商品筛选关闭
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'home_sort_close')
{
	$_SESSION['if_home_sort'] = false;
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

/**
 +----------------------------------------------------------
 * 设为首页显示商品
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'set_home_sort')
{
	$id = trim($_REQUEST['id']);
	$max_home_sort = $dou->get_one("SELECT home_sort FROM " . $dou->table('product') . " ORDER BY home_sort DESC");
	$new_home_sort = $max_home_sort + 1;
	$dou->query("UPDATE " . $dou->table('product') . " SET home_sort = '$new_home_sort' WHERE id = '$id'");
	
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

/**
 +----------------------------------------------------------
 * 取消首页显示商品
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del_home_sort')
{
	$id = trim($_REQUEST['id']);
	$dou->query("UPDATE " . $dou->table('product') . " SET home_sort = '' WHERE id = '$id'");
	
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

/**
 +----------------------------------------------------------
 * 产品删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$id = trim($_REQUEST['id']);
	$product_name = $dou->get_one("SELECT product_name FROM " . $dou->table('product') . " WHERE id = '$id'");
	
	if ($_POST['confirm'])
	{
		//删除相应商品图片
		$image = $dou->get_one("SELECT product_image FROM " . $dou->table('product') . " WHERE id = '$id'");
		del_image($image);

		$dou->create_admin_log($_LANG['product_del'] . ": " . $product_name);
		$dou->delete($dou->table(product), "id = $id", 'product.php');
	}
	else
	{
		$_LANG['del_check'] = preg_replace('/d%/Ums', $product_name, $_LANG['del_check']);
		$dou->dou_msg($_LANG['del_check'], 'product.php', '', '30', "product.php?rec=del&id=$id");
	}
}

/**
 +----------------------------------------------------------
 * 批量操作选择
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'action')
{
	if (is_array($_POST['checkbox']))
	{
	  $checkbox = $dou->create_sql_in($_POST['checkbox']);

			//批量删除产品
			if ($_POST['action'] == 'del_all')
			{
					del_all($checkbox);
			}
			//批量移动分类
			elseif ($_POST['action'] == 'category_move')
			{
				  if ($_POST['new_cat_id'])
						{
							  category_move($checkbox, $_POST['new_cat_id']);
						}
						else
						{
							  $dou->dou_msg($_LANG['category_select_empty']);
						}
			}
	}
	else
	{
	  $dou->dou_msg($_LANG['product_select_empty']);
	}
}

/**
 +----------------------------------------------------------
 * 批量移动分类
 +----------------------------------------------------------
 */
function category_move($checkbox, $new_cate_id)
{
	$GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('product') . " SET cat_id = '$new_cate_id' WHERE id " . $checkbox);

	$GLOBALS['dou']->create_admin_log($GLOBALS['_LANG']['category_move_batch'] . ": GOOD " . addslashes($checkbox));
	$GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['category_move_batch_succes']);
}

/**
 +----------------------------------------------------------
 * 批量产品删除
 +----------------------------------------------------------
 */
function del_all($checkbox)
{
	//删除相应商品图片
	$sql = "SELECT product_image FROM " . $GLOBALS['dou']->table('product') . " WHERE id " . $checkbox;
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		del_image($row['product_image']);
	}
	
	//删除商品
	$GLOBALS['dou']->query("DELETE FROM " . $GLOBALS['dou']->table('product') . " WHERE id " . $checkbox);
	
	$GLOBALS['dou']->create_admin_log($GLOBALS['_LANG']['product_del'] . ": GOOD " . addslashes($checkbox));
	$GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['del_succes']);
}

/**
 +----------------------------------------------------------
 * 删除商品图片
 +----------------------------------------------------------
 */
function del_image($image)
{
	$no_ext = explode(".", $image);
	$image_thumb = $no_ext[0] . '_thumb' . '.' . $no_ext[1];
	@ unlink(ROOT_PATH . $image);
	@ unlink(ROOT_PATH . $image_thumb);
}

/**
 +----------------------------------------------------------
 * 获取首页显示商品
 +----------------------------------------------------------
 */
function get_home_sort()
{
	$sql = "SELECT id, product_name, product_image FROM " . $GLOBALS['dou']->table('product') . " WHERE home_sort > 0 ORDER BY home_sort DESC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		$product_image = ROOT_URL . $row['product_image'];

		$home_sort[] = array (
			"id" => $row['id'],
			"product_name" => $row['product_name'],
			"product_image" => $product_image
		);
	}

	return $home_sort;
}


?>