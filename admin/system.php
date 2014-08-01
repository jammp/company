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

/* rec操作项的初始化 */
if (empty ($_REQUEST['rec']))
{
	$_REQUEST['rec'] = 'default';
}
else
{
	$_REQUEST['rec'] = trim($_REQUEST['rec']);
}

$smarty->assign('cur', 'system');

/**
 +----------------------------------------------------------
 * 系统设置
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'default')
{
	$smarty->assign('ur_here', $_LANG['system']);

	$smarty->assign('cfg_list_main', get_cfg_list());
	$smarty->assign('cfg_list_display', get_cfg_list('display'));
	$smarty->assign('cfg_list_defined', get_cfg_list('defined'));
	$smarty->display('system.htm');
}

/**
 +----------------------------------------------------------
 * 系统设置
 +----------------------------------------------------------
 */
if ($_REQUEST['rec'] == 'edit')
{
	/* 上传图片生成 */
	if ($_FILES[site_logo][name] != "")
	{
		$logo_dir = ROOT_PATH . "theme/" . $_POST['site_theme'] . "/images/"; //logo上传路径,结尾加斜杠
		$logo = new Upload($logo_dir, ''); //实例化类文件
		$upfile = $logo->upload_image('site_logo', 'logo'); //上传的文件域
		$_POST['site_logo'] = $upfile;
	}

	foreach ($_POST AS $name => $value)
	{
		$sql = "update " . $dou->table('config') . " SET value = '$value' WHERE name = '$name'";
		$dou->query($sql);
	}

	$dou->create_admin_log($_LANG['system'] . ": " . $_LANG['edit_succes']);

	$dou->dou_msg($_LANG['edit_succes'], 'system.php');
}

/**
 +----------------------------------------------------------
 * 获取系统设置列表
 +----------------------------------------------------------
 */
function get_cfg_list($tab = 'main')
{
	$sql = "SELECT * FROM " . $GLOBALS['dou']->table('config') . " WHERE type != 'hidden' AND tab = '$tab' ORDER BY sort ASC";
	$query = $GLOBALS['dou']->query($sql);
	while ($row = $GLOBALS['dou']->fetch_array($query))
	{
		if ($row['box'])
		{
			$box = explode(",", $row['box']);
		}
		if ($row['name'] == 'site_logo')
		{
			$row['value'] = "theme/" . $_CFG['site_theme'] . "/images/" . $row['value'];
		}
		if ($row['name'] == 'site_theme')
		{
			$box = $GLOBALS['dou']->get_subdirs(ROOT_PATH . 'theme');
		}
		if ($row['name'] == 'language')
		{
			$box = $GLOBALS['dou']->get_subdirs(ROOT_PATH . 'languages');
		}
		
		$cue = $GLOBALS['_LANG'][$row['name'] . '_cue'];

		if ($row['name'] == 'rewrite')
		{
			//根据 Web 服务器信息 判断伪静态文件
			if (stristr($_SERVER['SERVER_SOFTWARE'], "Apache"))
			{
				$rewrite_file = ".htaccess";
			}
			elseif (stristr($_SERVER['SERVER_SOFTWARE'], "IIS"))
			{
				$iis_exp = explode("/", $_SERVER['SERVER_SOFTWARE']);
				$iis_ver = $iis_exp['1'];
				
				if ($iis_ver >= 7.0)
				{
					$rewrite_file = "web.config";
				}
				else
				{
					$rewrite_file = "httpd.ini";
				}
			}
			
			if (stristr($_SERVER['SERVER_SOFTWARE'], "nginx"))
			{
				$cue = $GLOBALS['_LANG'][$row['name'] . '_cue_nginx'];
			}
			elseif ($rewrite_file)
			{
				$cue = preg_replace('/d%/Ums', $rewrite_file, $cue);
			}
			else
			{
				$cue = $GLOBALS['_LANG'][$row['name'] . '_cue_other'];
			}
		}
		
		$cfg_list[] = array (
			"value" => $row['value'],
			"name" => $row['name'],
			"type" => $row['type'],
			"box" => $box,
			"lang" => $GLOBALS['_LANG'][$row['name']],
			"cue" => $cue
		);
	}

	return $cfg_list;
}



?>