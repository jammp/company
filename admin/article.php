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
$smarty->assign('cur', $cur = 'article');

/**
 +----------------------------------------------------------
 * 文章列表
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['article']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['article_add'],
		'href' => 'article.php?rec=add'
	));

	/* 获得请求的分类 ID */
	if ($_POST['cat_id'])
	{
		header("Location: article.php?id=" . $_POST['cat_id'] . "\n");
	}
	$cat_id = trim($_REQUEST['id']);

	$child_id = $dou->dou_child_id($dou->fetch_array_all('article_category'), $cat_id);

	if ($cat_id)
	{
		$where = " WHERE cat_id IN (" . $cat_id . $child_id . ") ";
	}

	/* 分页信息 */
	$page = trim($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
	$limit = $dou->pager(article, '15', $page, $cat_id);

	$sql = "SELECT id, title, cat_id, add_time FROM " . $dou->table('article') . $where . "ORDER BY id DESC" . $limit;
	$query = $dou->query($sql);

	while ($row = $dou->fetch_array($query))
	{
		$cat_name = $dou->get_one("SELECT cat_name FROM " . $dou->table('article_category') . " WHERE cat_id = '$row[cat_id]'");
		$add_time = date("Y-m-d", $row['add_time']);

		$article_list[] = array (
			"id" => $row['id'],
			"cat_id" => $row['cat_id'],
			"cat_name" => $cat_name,
			"title" => $row['title'],
			"add_time" => $add_time
		);
	}
	
	/* 首页显示文章数量限制框 */
	for ($i=1; $i<=$_CFG['home_display_product']; $i++)
	{
    $home_sort_bg .= "<li><em></em></li>";
  }

	$article_category = $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'));

	$smarty->assign('if_home_sort', $_SESSION['if_home_sort']);
	$smarty->assign('home_sort', get_home_sort());
	$smarty->assign('home_sort_bg', $home_sort_bg);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('article_category', $article_category);
	$smarty->assign('article_list', $article_list);
	$smarty->display('article.htm');
}

/**
 +----------------------------------------------------------
 * 文章添加
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'add')
{
	$smarty->assign('ur_here', $_LANG['article_add']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['article'],
		'href' => 'article.php'
	));

	/* 格式化自定义参数 */
	if ($_CFG['defined_article'])
	{
		$defined = explode(',', $_CFG['defined_article']);
		foreach ($defined as $row)
		{
			$defined_article .= $row . "：\n";
		}
		$article['defined'] = trim($defined_article);
		$article['defined_count'] = count(explode("\n", $article['defined'])) * 2;
	}

	$article_category = $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'));

	$smarty->assign('form_action', 'insert');
	$smarty->assign('article_category', $article_category);
	$smarty->assign('article', $article);
	$smarty->display('article.htm');
}

elseif ($_REQUEST['rec'] == 'insert')
{
	$add_time = time();

	/* 格式化自定义参数 */
	$_POST['defined'] = str_replace("\n", ",", $_POST['defined']);

	$sql = "INSERT INTO " . $dou->table('article') . " (id, cat_id, title, defined, content ,keywords, add_time, description)" .
	" VALUES (NULL, '$_POST[cat_id]', '$_POST[title]', '$_POST[defined]', '$_POST[content]', '$_POST[keywords]', '$add_time', '$_POST[description]')";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['article_add'] . ": " . $_POST[title]);

	$dou->dou_msg($_LANG['article_add_succes'], 'article.php');
}

/**
 +----------------------------------------------------------
 * 文章编辑
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'edit')
{
	$smarty->assign('ur_here', $_LANG['article_edit']);
	$smarty->assign('action_link', array (
		'text' => $_LANG['article'],
		'href' => 'article.php'
	));

	$id = trim($_REQUEST['id']);
	$query = $dou->select($dou->table(article), '*', '`id` = \'' . $id . '\'');
	$article = $dou->fetch_array($query);

	/* 格式化自定义参数 */
	if ($_CFG['defined_article'] || $article['defined'])
	{
		$defined = explode(',', $_CFG['defined_article']);
		foreach ($defined as $row)
		{
			$defined_article .= $row . "：\n";
		}
	
		if ($article['defined'])
		{
			$article['defined'] = str_replace(",", "\n", $article['defined']);
		}
		else
		{
			$article['defined'] = trim($defined_article);
		}
	
		$article['defined_count'] = count(explode("\n", $article['defined'])) * 2;
	}

	$article_category = $dou->get_article_category($dou->fetch_array_all('article_category', 'sort ASC'));

	$smarty->assign('form_action', 'update');
	$smarty->assign('article_category', $article_category);
	$smarty->assign('article', $article);
	$smarty->display('article.htm');
}

elseif ($_REQUEST['rec'] == 'update')
{
	/* 格式化自定义参数 */
	$_POST['defined'] = str_replace("\n", ",", $_POST['defined']);
	
	$sql = "UPDATE " . $dou->table('article') . " SET cat_id = '$_POST[cat_id]', title = '$_POST[title]', defined = '$_POST[defined]' ,content = '$_POST[content]', keywords = '$_POST[keywords]', description = '$_POST[description]' WHERE id = '$_POST[id]'";
	$dou->query($sql);

	$dou->create_admin_log($_LANG['article_edit'] . ": " . $_POST[title]);

	$dou->dou_msg($_LANG['article_edit_succes'], 'article.php');
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
	$max_home_sort = $dou->get_one("SELECT home_sort FROM " . $dou->table('article') . " ORDER BY home_sort DESC");
	$new_home_sort = $max_home_sort + 1;
	$dou->query("UPDATE " . $dou->table('article') . " SET home_sort = '$new_home_sort' WHERE id = '$id'");
	
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
	$dou->query("UPDATE " . $dou->table('article') . " SET home_sort = '' WHERE id = '$id'");
	
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

/**
 +----------------------------------------------------------
 * 文章删除
 +----------------------------------------------------------
 */
elseif ($_REQUEST['rec'] == 'del')
{
	$id = trim($_REQUEST['id']);

	$title = $dou->get_one("SELECT title FROM " . $dou->table('article') . " WHERE id = '$id'");
	
	if ($_POST['confirm'])
	{
		$dou->create_admin_log($_LANG['article_del'] . ": " . $title);
		$dou->delete($dou->table(article), "id = $id", 'article.php');
	}
	else
	{
		$_LANG['del_check'] = preg_replace('/d%/Ums', $title, $_LANG['del_check']);
		$dou->dou_msg($_LANG['del_check'], 'article.php', '', '30', "article.php?rec=del&id=$id");
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

			//批量产品删除
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
	  $dou->dou_msg($_LANG['article_select_empty']);
	}
}

/**
 +----------------------------------------------------------
 * 批量移动分类
 +----------------------------------------------------------
 */
function category_move($checkbox, $new_cate_id)
{
	$GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('article') . " SET cat_id = '$new_cate_id' WHERE id " . $checkbox);
	
	$GLOBALS['dou']->create_admin_log($GLOBALS['_LANG']['category_move_batch'] . ": GOOD " . addslashes($checkbox));
	$GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['category_move_batch_succes']);
}

/**
 +----------------------------------------------------------
 * 批量文章删除
 +----------------------------------------------------------
 */
function del_all($checkbox)
{
		//删除文章
		$GLOBALS['dou']->query("DELETE FROM " . $GLOBALS['dou']->table('article') . " WHERE id " . $checkbox);
		
		$GLOBALS['dou']->create_admin_log($GLOBALS['_LANG']['article_del'] . ": ARTICLE " . addslashes($checkbox));
	 $GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['del_succes']);
}

/**
 +----------------------------------------------------------
 * 获取首页显示文章
 +----------------------------------------------------------
 */
function get_home_sort()
{
	$sql = "SELECT id, title FROM " . $GLOBALS['dou']->table('article') . " WHERE home_sort > 0 ORDER BY home_sort DESC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		$home_sort[] = array (
			"id" => $row['id'],
			"title" => $row['title']
		);
	}

	return $home_sort;
}


?>