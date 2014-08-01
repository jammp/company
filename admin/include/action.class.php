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

class Action extends Common
{
	/**
	 +----------------------------------------------------------
	 * 获取网站信息
	 +----------------------------------------------------------
	 */
	function get_config()
	{
		$query = $this->select_all('config');
		while ($row = $this->fetch_array($query))
		{
			$config[$row[name]] = $row[value];
		}
		return $config;
	}

	/**
	 +----------------------------------------------------------
	 * 用户权限判断
	 +----------------------------------------------------------
	 */
	function admin_check($user_id, $shell, $action_list = ALL)
	{
		if ($row = $this->admin_state($user_id, $shell))
		{
			$this->admin_ontime(10800);
			return $row;
		}
		else
		{
			header("Location: " . ROOT_URL . ADMIN_PATH . "/login.php\n");
			exit;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 用户状态
	 +----------------------------------------------------------
	 */
	function admin_state($user_id, $shell)
	{
		$query = $this->select($this->table(admin), '*', '`user_id` = \'' . $user_id . '\'');
		$user = $this->fetch_array($query);

		//如果$user则开始比对$shell值
		$check_shell = is_array($user) ? $shell == md5($user['user_name'] . $user['password'] . DOU_SHELL) : FALSE;

		//如果比对$shell吻合，则返回会员信息，否则返回空
		return $check_shell ? $user : NULL;
	}

	/**
	 +----------------------------------------------------------
	 * 登录超时默认为3小时(10800秒)
	 +----------------------------------------------------------
	 */
	function admin_ontime($timeout = '10800')
	{
		$ontime = $_SESSION[ontime];
		$cur_time = time();
		if ($cur_time - $ontime > $timeout)
		{
			session_destroy();
		}
		else
		{
			$_SESSION[ontime] = time();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 获取单页面列表
	 +----------------------------------------------------------
	 */
	function get_page_list($data, $parent_id = '0', $level = '0', $current_id = '', $mark = '-')
	{
		static $page_list = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id && $value['id'] != $current_id)
			{
				$value['mark'] = str_repeat($mark, $level);
				$value['level'] = $level;
				$page_list[] = $value;
				$this->get_page_list($data, $value['id'], $level +1, $current_id);
			}
		}
		return $page_list;
	}

	/**
	 +----------------------------------------------------------
	 * 获取导航菜单
	 +----------------------------------------------------------
	 */
	function get_nav($data, $type = 'middle', $parent_id = '0', $level = '0', $current_id = '', $mark = '-')
	{
		static $nav = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id && $value['type'] == $type && $value['id'] != $current_id)
			{
				if ($value['module'] != 'nav')
				{
					$value['guide'] = $this->rewrite_url($value['module'], $value['guide']);
				}

				$value['mark'] = str_repeat($mark, $level);
				$nav[] = $value;
				$this->get_nav($data, $type, $value['id'], $level +1, $current_id);
			}
		}
		return $nav;
	}

	/**
	 +----------------------------------------------------------
	 * 获取商品分类
	 +----------------------------------------------------------
	 */
	function get_product_category($data, $parent_id = '0', $level = '0', $current_id = '', $mark = '-')
	{
		static $product_category = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id && $value['cat_id'] != $current_id)
			{
				$value['mark'] = str_repeat($mark, $level);
				$product_category[] = $value;
				$this->get_product_category($data, $value['cat_id'], $level +1, $current_id);
			}
		}
		return $product_category;
	}

	/**
	 +----------------------------------------------------------
	 * 获取文章分类
	 +----------------------------------------------------------
	 */
	function get_article_category($data, $parent_id = '0', $level = '0', $current_id = '', $mark = '-')
	{
		static $article_category = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id && $value['cat_id'] != $current_id)
			{
				$value['mark'] = str_repeat($mark, $level);
				$article_category[] = $value;
				$this->get_article_category($data, $value['cat_id'], $level +1, $current_id);
			}
		}
		return $article_category;
	}

	/**
	 +----------------------------------------------------------
	 * 获取文章列表
	 +----------------------------------------------------------
	 */
	function get_article_list($cat_id = '')
	{
		if ($cat_id)
		{
			$where = "and a.cat_id = $cat_id ";
		}

		$sql = "SELECT a.id, a.title, a.cat_id, a.add_time, c.cat_name FROM " . $this->table('article') . " AS a, " . $this->table('article_category') . " AS c WHERE a.cat_id = c.cat_id " . $where . "ORDER BY id ASC";
		$query = $this->query($sql);
		while ($row = $this->fetch_array($query))
		{
			$add_time = date("Y-m-d", $row['add_time']);

			$article_list[] = array (
				"id" => $row['id'],
				"cat_id" => $row['cat_id'],
				"cat_name" => $row['cat_name'],
				"title" => $row['title'],
				"add_time" => $add_time
			);
		}

		return $article_list;
	}

	/**
	 +----------------------------------------------------------
	 * 获取管理员日志
	 +----------------------------------------------------------
	 */
	function create_admin_log($action)
	{
		$create_time = time();
		$ip = $this->get_ip();

		$sql = "INSERT INTO " . $this->table('admin_log') . " (id, create_time, user_id, action ,ip)" .
		" VALUES (NULL, '$create_time', '$_SESSION[user_id]', '$action', '$ip')";
		$this->query($sql);
	}

	/**
	 +----------------------------------------------------------
	 * 获取管理员日志
	 +----------------------------------------------------------
	 */
	function get_admin_log($user_id = '', $num = '')
	{
		if ($user_id)
		{
			$where = " WHERE user_id = " . $user_id;
		}
		if ($num)
		{
			$limit = " LIMIT $num";
		}

		$sql = "SELECT * FROM " . $this->table('admin_log') . $where . " ORDER BY id DESC" . $limit;
		$query = $this->query($sql);
		while ($row = $this->fetch_array($query))
		{
			$create_time = date("Y-m-d", $row[create_time]);
			$user_name = $this->get_one("SELECT user_name FROM " . $this->table('admin') . " WHERE user_id = " . $row['user_id']);

			$log_list[] = array (
				"id" => $row['id'],
				"create_time" => $create_time,
				"user_name" => $user_name,
				"action" => $row['action'],
				"ip" => $row['ip']
			);
		}

		return $log_list;
	}

	/**
	 +----------------------------------------------------------
	 * 分页
	 +----------------------------------------------------------
	 */
	function pager($table, $page_size = '10', $page, $cat_id = '')
	{
		if ($cat_id)
		{
			/* 当前分类下子分类ID */
			$child_id = $this->dou_child_id($this->fetch_array_all($table . '_category'), $cat_id);
			
			$where = " WHERE cat_id IN (" . $cat_id . $child_id . ") ";
		}
		elseif ($table == 'guestbook')
		{
			$where = " WHERE reply_id = '0'";
		}

		$sql = "SELECT * FROM " . $this->table($table) . $where;

		$record_count = mysql_num_rows($this->query($sql));

		if ($table == 'admin_log')
		{
			$url = "manager.php?rec=manager_log";
			$get_request = "&page=";
		}
		else
		{
			$url = $GLOBALS['cur'] . ".php";
			$get_request = $cat_id ? "?id=$cat_id&page=" : "?page=";
		}

		$page_count = ceil($record_count / $page_size);
		$previous = $url . $get_request . ($page > 1 ? $page -1 : 0);
		$next = $url . $get_request . ($page_count > $page ? $page +1 : 0);
		$last = $url . $get_request . $page_count;

		$pager = array (
			"record_count" => $record_count,
			"page_size" => $page_size,
			"page" => $page,
			"page_count" => $page_count,
			"previous" => $previous,
			"next" => $next,
			"first" => $url,
			"last" => $last,

		);

		$start = ($page -1) * $page_size;
		$limit = " LIMIT $start, $page_size";

		$GLOBALS['smarty']->assign('pager', $pager);

		return $limit;
	}

	/**
	 +----------------------------------------------------------
	 * 获取当前目录子文件夹
	 +----------------------------------------------------------
	 */
	function get_subdirs($dir)
	{
		$subdirs = array ();
		if (!$dh = opendir($dir))
			return $subdirs;
		$i = 0;
		while ($f = readdir($dh))
		{
			if ($f == '.' || $f == '..')
				continue;
			//如果只要子目录名, path = $f;
			//$path = $dir.'/'.$f;
			$path = $f;
			$subdirs[$i] = $path;
			$i++;
		}
		return $subdirs;
	}

	/**
	 +----------------------------------------------------------
	 * 清除缓存及已编译模板
	 +----------------------------------------------------------
	 */
	function dou_clear_cache($dir)
	{
		$dir = realpath($dir);
		if (!$dir || !@ is_dir($dir))
			return 0;
		$handle = @ opendir($dir);
		if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR)
			$dir .= DIRECTORY_SEPARATOR;
		while ($file = @ readdir($handle))
		{
			if ($file != '.' && $file != '..')
			{
				if (@ is_dir($dir . $file) && !is_link($dir . $file))
					$this->dou_clear_cache($dir . $file);
				else
					@ unlink($dir . $file);
			}
		}
		closedir($handle);
	}

	/**
	 +----------------------------------------------------------
	 * 给URL自动上http://
	 +----------------------------------------------------------
	 */
	function auto_http($url)
	{
		if (strpos($url, 'http://') === false && strpos($url, 'https://') === false)
		{
			$url = 'http://' . trim($url);
		}
		else
		{
			$url = trim($url);
		}
		return $url;
	}

	/**
	 +----------------------------------------------------------
	 * 版本升级提示
	 +----------------------------------------------------------
	 */
	function dou_api()
	{
		global $_CFG;
		global $sys_info;

		$apiget = "ver=$_CFG[douphp_version]&lang=$_CFG[language]&php_ver=$sys_info[php_ver]&mysql_ver=$sys_info[mysql_ver]&os=$sys_info[os]&web_server=$sys_info[web_server]&charset=$sys_info[charset]&template=$_CFG[site_theme]&url=" . ROOT_URL;
		return "http://api.douco.com/update.php" . '?' . $apiget;
	}

	/**
	 +----------------------------------------------------------
	 * 创建IN查询如"IN('1','2')";
	 +----------------------------------------------------------
	 */
	function create_sql_in($arr)
	{
		foreach ($arr AS $list)
		{
			$sql_in .= $sql_in ? ",'$list'" : "'$list'";
		}
		return "IN ($sql_in)";
	}

	/**
	 +----------------------------------------------------------
	 * 后台通用信息提示
	 +----------------------------------------------------------
	 */
	function dou_msg($text, $url = '', $out = '', $time = '3', $check = '')
	{
		if (!$text)
		{
			$text = $GLOBALS['_LANG']['dou_msg_success'];
		}

		$GLOBALS['smarty']->assign('ur_here', $GLOBALS['_LANG']['dou_msg']);
		$GLOBALS['smarty']->assign('text', $text);
		$GLOBALS['smarty']->assign('url', $url);
		$GLOBALS['smarty']->assign('out', $out);
		$GLOBALS['smarty']->assign('time', $time);
		$GLOBALS['smarty']->assign('check', $check);

		//根据跳转时间生成跳转提示
		$cue = preg_replace('/d%/Ums', $time, $GLOBALS['_LANG']['dou_msg_cue']);
		$GLOBALS['smarty']->assign('cue', $cue);

		$GLOBALS['smarty']->display('dou_msg.htm');
		exit ();
	}

}
?>