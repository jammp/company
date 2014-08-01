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

class Common extends DbMysql
{
	/**
	 +----------------------------------------------------------
	 * 重写 URL 地址
	 +----------------------------------------------------------
	 */
	function rewrite_url($module, $id, $rec = '')
	{
		$is_category = strpos($module, 'category');

		if (intval($GLOBALS['_CFG']['rewrite']))
		{
			$filename = $module != 'page' ? '/' . $id : '';
			$item = (!$is_category && $id > 0) ? $filename . '.html' : '';
			$url = $this->get_unique($module, $id) . $item . ($rec ? '/' . $rec : '');
		}
		else
		{
			$rec = ($rec ? '?rec=' . $rec : '');
			$id = $id > 0 ? '?id=' . $id : '';
			$url = $module . '.php' . $id . $rec;
		}

		return ROOT_URL . $url;
	}

	/**
	 +----------------------------------------------------------
	 * 获取别名
	 +----------------------------------------------------------
	 */
	function get_unique($module, $id)
	{
		$filed = $module == 'page' ? id : cat_id;
		$table_module = $module;

		//非单页面和分类模型下获取分类ID
		if (!strpos($module, 'category') && $module != 'page')
		{
			$id = $this->get_one("SELECT cat_id FROM " . $this->table($module) . " WHERE id = " . $id);
			$table_module = $module . '_category';
		}

		$unique_id = $this->get_one("SELECT unique_id FROM " . $this->table($table_module) . " WHERE " . $filed . " = " . $id);

		//把分类页和列表的别名统一
		$module = preg_replace("/\_category/", '', $module);

		//伪静态时使用的完整别名
		if ($module == 'page')
		{
			$unique = $unique_id;
		}
		elseif ($module == 'article')
		{
			$unique = $unique_id ? '/' . $unique_id : $unique_id;
			$unique = 'news' . $unique;
		}
		else
		{
			$unique = $unique_id ? '/' . $unique_id : $unique_id;
			$unique = $module . $unique;
		}

		return $unique;
	}

	/**
	 +----------------------------------------------------------
	 * 格式化商品价格
	 +----------------------------------------------------------
	 */
	function price_format($price = '')
	{
		$price = number_format($price, $GLOBALS['_CFG']['price_decimal']);
		$price_format = preg_replace('/d%/Ums', $price, $GLOBALS['_LANG']['price_format']);

		return $price_format;
	}

	/**
	 +----------------------------------------------------------
	 * 获取当前分类下所有子分类
	 +----------------------------------------------------------
	 */
	function dou_child_id($data, $parent_id = '0')
	{
		static $child;
		foreach ($data as $value)
		{
			if ($value['parent_id'] == $parent_id)
			{
				$child .= ',' . $value['cat_id'];
				$this->dou_child_id($data, $value['cat_id'], $level +1);
			}
		}
		return $child;
	}

	/**
	 +----------------------------------------------------------
	 * 获取真实IP地址
	 +----------------------------------------------------------
	 */
	function get_ip()
	{
		static $ip;
		if (isset ($_SERVER))
		{
			if (isset ($_SERVER["HTTP_X_FORWARDED_FOR"]))
			{
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}
			else
				if (isset ($_SERVER["HTTP_CLIENT_IP"]))
				{
					$ip = $_SERVER["HTTP_CLIENT_IP"];
				}
				else
				{
					$ip = $_SERVER["REMOTE_ADDR"];
				}
		}
		else
		{
			if (getenv("HTTP_X_FORWARDED_FOR"))
			{
				$ip = getenv("HTTP_X_FORWARDED_FOR");
			}
			else
				if (getenv("HTTP_CLIENT_IP"))
				{
					$ip = getenv("HTTP_CLIENT_IP");
				}
				else
				{
					$ip = getenv("REMOTE_ADDR");
				}
		}
		
		if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',$ip))
		{
				return $ip;
		}
		else
		{
				return '127.0.0.1';
		}
	}





}
?>