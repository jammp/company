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
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}

	/**
	 +----------------------------------------------------------
	 * 获取导航菜单
	 * $parent_id 默认获取一级导航
	 * $current_module 当前页面模型名称
	 * $current_id 当前页面分类ID
	 +----------------------------------------------------------
	 */
	function get_nav($data, $type = 'middle', $parent_id = '0', $current_module = '', $current_id = '')
	{
		$nav = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id && $value['type'] == $type)
			{
				$value['url'] = ($value['module'] == 'nav') ? $value['guide'] : $this->rewrite_url($value['module'], $value['guide']);

				$value['cur'] = $this->dou_current($value['module'], $value['guide'], $current_module, $current_id);

				foreach ($data as $child)
				{
					if ($child['parent_id'] == $value['id'])
					{
						$value['child'] = $this->get_nav($data, $type, $value['id']);
						break;
					}
				}
				$nav[] = $value;
			}
		}
		return $nav;
	}

	/**
	 +----------------------------------------------------------
	 * 获取文章分类
	 +----------------------------------------------------------
	 */
	function get_article_category($data, $current_id = '', $parent_id = '0', $level = '0', $mark = '-')
	{
		static $article_category = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id)
			{
				$value['url'] = $this->rewrite_url('article_category', $value['cat_id']);
				$value['mark'] = str_repeat($mark, $level);
				$value['cur'] = $value['cat_id'] == $current_id ? true : false;
				$article_category[] = $value;
				$this->get_article_category($data, $current_id, $value['cat_id'], $level +1);
			}
		}
		return $article_category;
	}

	/**
	 +----------------------------------------------------------
	 * 获取文章列表
	 +----------------------------------------------------------
	 */
	function get_recommend_article($cat_id = '', $num = '')
	{
		$where = $cat_id ? " WHERE cat_id = $cat_id " : '';
		$limit = $num ? ' LIMIT '. $num : '';

		$sql = "SELECT id, title, add_time FROM " . $this->table('article') . $where . "ORDER BY home_sort DESC, id DESC" . $limit;
		$query = $this->query($sql);
		while ($row = $this->fetch_array($query))
		{
			$url = $this->rewrite_url('article', $row['id']);
			$add_time_short = date("m-d", $row['add_time']);

			$article_list[] = array (
				"id" => $row['id'],
				"title" => $row['title'],
				"add_time_short" => $add_time_short,
				"url" => $url
			);
		}

		return $article_list;
	}

	/**
	 +----------------------------------------------------------
	 * 获取商品分类
	 +----------------------------------------------------------
	 */
	function get_product_category($data, $current_id = '', $parent_id = '0', $level = '0', $mark = '-')
	{
		static $product_category = array ();
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id)
			{
				$value['url'] = $this->rewrite_url('product_category', $value['cat_id']);
				$value['mark'] = str_repeat($mark, $level);
				$value['cur'] = $value['cat_id'] == $current_id ? true : false;
				$product_category[] = $value;
				$this->get_product_category($data, $current_id, $value['cat_id'], $level +1);
			}
		}
		return $product_category;
	}

	/**
	 +----------------------------------------------------------
	 * 获取商品列表
	 +----------------------------------------------------------
	 */
	function get_recommend_product($cat_id = '', $num = '')
	{
		$where = $cat_id ? " WHERE cat_id = $cat_id " : '';
		$limit = $num ? ' LIMIT '. $num : '';

		$sql = "SELECT id, product_name, price, product_image FROM " . $this->table('product') . $where . "ORDER BY home_sort DESC, id DESC" . $limit;
		$query = $this->query($sql);
		while ($row = $this->fetch_array($query))
		{
			$url = $this->rewrite_url('product', $row['id']);
			$image = explode(".", $row['product_image']);
			$thumb = ROOT_URL . $image[0] . "_thumb." . $image[1];
			$price = $row['price'] > 0 ? $this->price_format($row['price']) : $GLOBALS['_LANG']['price_discuss'];

			$product_list[] = array (
				"id" => $row['id'],
				"name" => $row['product_name'],
				"price" => $price,
				"thumb" => $thumb,
				"url" => $url
			);
		}

		return $product_list;
	}

	/**
	 +----------------------------------------------------------
	 * 获取指定分类单页面列表
	 +----------------------------------------------------------
	 */
	function get_page_list($parent_id = '', $current_id = '')
	{
		if ($parent_id)
		{
			$where = "where parent_id = $parent_id ";
		}

		$sql = "SELECT * FROM " . $this->table('page') . $where . " ORDER BY id ASC";
		$query = $this->query($sql);
		while ($row = $this->fetch_array($query))
		{
			$url = $this->rewrite_url('page', $row['id']);
			$cur = $this->dou_current('page', $row['id'], 'page', $current_id);

			$page_list[] = array (
				"id" => $row['id'],
				"parent_id" => $row['parent_id'],
				"page_name" => $row['page_name'],
				"cur" => $cur,
				"url" => $url
			);
		}

		return $page_list;
	}

	/**
	 +----------------------------------------------------------
	 * 分页
	 +----------------------------------------------------------
	 */
	function pager($table, $page_size = '10', $page, $cat_id = '', $keyword = '')
	{
		$rewrite = intval($GLOBALS['_CFG']['rewrite']);


		if ($cat_id)
		{
			/* 当前分类下子分类ID */
			$child_id = $this->dou_child_id($this->fetch_array_all($table . '_category'), $cat_id);
			
			$where = " WHERE cat_id IN (" . $cat_id . $child_id . ") ";
		}
		elseif ($keyword)
		{
			$where = " WHERE product_name LIKE '%$keyword%'";
		}
		elseif ($table == 'guestbook')
		{
			$where = " WHERE reply_id = '0'";
		}

		$sql = "SELECT * FROM " . $this->table($table) . $where;

		$record_count = mysql_num_rows($this->query($sql));

		if ($keyword)
		{
			$url = ROOT_URL;
			$search = $keyword ? '&s=' . $keyword : '';
			$get_request = '?p=';
		}
		else
		{
		 $url = $this->rewrite_url($table . '_category', $cat_id);
			$get_request = $rewrite ? '/o' : ($cat_id ? '&page=' : '?page=');
		}

		$page_count = ceil($record_count / $page_size);
		$previous = $url . $get_request . ($page > 1 ? $page -1 : 0) . $search;
		$next = $url . $get_request . ($page_count > $page ? $page +1 : 0) . $search;
		$last = $url . $get_request . $page_count . $search;

		$pager = array (
			"record_count" => $record_count,
			"page_size" => $page_size,
			"page" => $page,
			"page_count" => $page_count,
			"previous" => $previous,
			"next" => $next,
			"first" => $keyword ? ROOT_URL . '?s=' . $keyword : $url,
			"last" => $last
		);

		$start = ($page -1) * $page_size;
		$limit = " LIMIT $start, $page_size";

		$GLOBALS['smarty']->assign('pager', $pager);

		return $limit;
	}

	/**
	 +----------------------------------------------------------
	 * 高亮当前菜单
	 +----------------------------------------------------------
	 */
	function dou_current($module, $id, $current_module, $current_id = '')
	{
		if ($id == $current_id && $module == $current_module)
		{
			return true;
		}
		elseif (!$id && $module == $current_module)
		{
			return true;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 当前位置
	 +----------------------------------------------------------
	 */
	function ur_here($module, $cat_id = '', $title = '')
	{
		//如果是单页面，则只执行这一句
		if ($module == 'onepage')
		{
			return $title;
			exit ();
		}

		//模块名称
		$main = "<a href=" . $this->rewrite_url($module) . ">" . $GLOBALS['_LANG'][$module] . "</a>";

		//如果存在分类
		if ($cat_id)
		{
			$cat_name = $this->get_one("SELECT cat_name FROM " . $this->table($module) . " WHERE cat_id = '" . $cat_id . "'");

			//如果存在标题
			if ($title)
			{
				$category = "<b>></b><a href=" . $this->rewrite_url($module, $cat_id) . ">" . $cat_name . "</a>";
			}
			else
			{
				$category = "<b>></b>$cat_name";
			}
		}

		//如果存在标题
		if ($title)
		{
			$title = "<b>></b>" . $title;
		}

		$ur_here = $main . $category . $title;

		return $ur_here;
	}

	/**
	 +----------------------------------------------------------
	 * 标题
	 +----------------------------------------------------------
	 */
	function page_title($module, $cat_id = '', $title = '')
	{
		//如果是单页面，则只执行这一句
		if ($module == 'onepage')
		{
			return $title . " | " . $GLOBALS[_CFG]['site_name'];
			exit ();
		}

		//主栏目
		if ($GLOBALS['_LANG'][$module])
		{
			$main = $GLOBALS['_LANG'][$module] . " | ";
		}

		//如果存在分类
		if ($cat_id)
		{
			$cat_name = $this->get_one("SELECT cat_name FROM " . $this->table($module) . " WHERE cat_id = '" . $cat_id . "'");
			$category = $cat_name . " | ";
		}

		//如果存在标题
		if ($title)
		{
			$title = $title . " | ";
		}

		$page_title = $title . $category . $main . $GLOBALS[_CFG]['site_name'];

		return $page_title;
	}

	/**
	 +----------------------------------------------------------
	 * 清除html,换行，空格类
	 +----------------------------------------------------------
	 */
	function dou_substr($str, $length, $charset = "utf-8")
	{
		$str = trim($str); //清除字符串两边的空格
		$str = strip_tags($str, ""); //利用php自带的函数清除html格式
		$str = preg_replace("/\t/", "", $str); //使用正则表达式匹配需要替换的内容，如：空格，换行，并将替换为空。
		$str = preg_replace("/\r\n/", "", $str);
		$str = preg_replace("/\r/", "", $str);
		$str = preg_replace("/\n/", "", $str);
		$str = preg_replace("/ /", "", $str);
		$str = preg_replace("/&nbsp; /", "", $str); //匹配html中的空格
		$str = trim($str); //清除字符串两边的空格

		if (function_exists("mb_substr"))
		{
			$substr = mb_substr($str, 0, $length, $charset);
		}
		else
		{
			$c['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$c['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			preg_match_all($c[$charset], $str, $match);
			$substr = join("", array_slice($match[0], 0, $length));
		}

		return $substr;
	}

	/**
	 +----------------------------------------------------------
	 * 信息提示
	 +----------------------------------------------------------
	 */
	function dou_msg($text, $url = '', $time = '3')
	{
		if (!$text)
		{
			$text = $GLOBALS['_LANG']['dou_msg_success'];
		}

		/* 获取meta和title信息 */
		$GLOBALS['smarty']->assign('page_title', $GLOBALS['_CFG']['site_title']);
		$GLOBALS['smarty']->assign('keywords', $GLOBALS['_CFG']['site_keywords']);
		$GLOBALS['smarty']->assign('description', $GLOBALS['_CFG']['site_description']);
		
		/* 初始化导航栏 */
		$data = $this->fetch_array_all('nav', 'sort ASC');
		$GLOBALS['smarty']->assign('nav_top_list', $this->get_nav($data, 'top'));
		$GLOBALS['smarty']->assign('nav_middle_list', $this->get_nav($data));
		$GLOBALS['smarty']->assign('nav_bottom_list', $this->get_nav($data, 'bottom'));

  /* 初始化数据 */
		$GLOBALS['smarty']->assign('ur_here', $GLOBALS['_LANG']['dou_msg']);
		$GLOBALS['smarty']->assign('text', $text);
		$GLOBALS['smarty']->assign('url', $url);
		$GLOBALS['smarty']->assign('time', $time);

		//根据跳转时间生成跳转提示
		$cue = preg_replace('/d%/Ums', $time, $GLOBALS['_LANG']['dou_msg_cue']);
		$GLOBALS['smarty']->assign('cue', $cue);

		$GLOBALS['smarty']->display('dou_msg.dwt');
		exit ();
	}





}
?>