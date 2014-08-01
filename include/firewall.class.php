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

class Firewall
{
	/**
	 +----------------------------------------------------------
	 * 豆壳防火墙
	 +----------------------------------------------------------
	 */
	function dou_firewall()
	{
		//交互数据转义操作
		$this->dou_magic_quotes();
	}

	/**
	 +----------------------------------------------------------
	 * 交互数据转义操作
	 +----------------------------------------------------------
	 */
	function dou_magic_quotes()
	{
		if (!@ get_magic_quotes_gpc())
		{
			$_GET = $_GET ? $this->addslashes_deep($_GET) : '';
			$_POST = $_POST ? $this->addslashes_deep($_POST) : '';
			$_COOKIE = $this->addslashes_deep($_COOKIE);
			$_REQUEST = $this->addslashes_deep($_REQUEST);
		}
	}

	/**
	 +----------------------------------------------------------
	 * 递归方式的对变量中的特殊字符进行转义
	 +----------------------------------------------------------
	 */
	function addslashes_deep($value)
	{
		if (empty ($value))
		{
			return $value;
		}

		if (is_array($value))
		{
			foreach ((array) $value as $k => $v)
			{
				unset ($value[$k]);
				$k = addslashes($k);
				if (is_array($v))
				{
					$value[$k] = $this->addslashes_deep($v);
				}
				else
				{
					$value[$k] = addslashes($v);
				}
			}
		}
		else
		{
			$value = addslashes($value);
		}

		return $value;
	}

	/**
	 +----------------------------------------------------------
	 * 递归方式的对变量中的特殊字符去除转义
	 +----------------------------------------------------------
	 */
	function stripslashes_deep($value)
	{
		if (empty ($value))
		{
			return $value;
		}

		if (is_array($value))
		{
			foreach ((array) $value as $k => $v)
			{
				unset ($value[$k]);
				$k = stripslashes($k);
				if (is_array($v))
				{
					$value[$k] = $this->stripslashes_deep($v);
				}
				else
				{
					$value[$k] = stripslashes($v);
				}
			}
		}
		else
		{
			$value = stripslashes($value);
		}
		return $value;
	}

	/**
	 +----------------------------------------------------------
	 * html安全过滤器
	 +----------------------------------------------------------
	 */
	function dou_filter($value)
	{
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = htmlspecialchars($v,ENT_NOQUOTES);
			}
		}
		else
		{
			//参数ENT_NOQUOTES代表不转义任何引号，避免与addslashes冲突
			$value = htmlspecialchars($value,ENT_NOQUOTES);
		}
		
		return $value;
	}

	/**
	 +----------------------------------------------------------
	 * 设置令牌
	 +----------------------------------------------------------
	 */
	function set_token()
	{
		$token = md5(uniqid(rand(), true));
		$n = rand(1, 24);
		return $_SESSION['token'] = substr($token, $n, 8);;
	}

	/**
	 +----------------------------------------------------------
	 * 验证令牌
	 +----------------------------------------------------------
	 */
	function check_token($token)
	{
		if (isset($_SESSION['token']) && $token == $_SESSION['token'])
		{
			unset($_SESSION['token']);
			return true;
		}
		else
		{
			unset($_SESSION['token']);
		}
	}






}
?>