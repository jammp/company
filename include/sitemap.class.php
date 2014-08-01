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

class SiteMap
{
	var $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">";
	var $footer = "\t</urlset>";
	var $output;
	
	/**
	 +----------------------------------------------------------
	 * 构造函数
	 +----------------------------------------------------------
	 */
	function SiteMap($domain, $today = '')
	{
		$this->domain = $domain;
		$this->today = $today;
	}

	/**
	 +----------------------------------------------------------
	 * 构造站点地图
	 +----------------------------------------------------------
	 */
	function build_sitemap()
	{
			$output = $this->header . "\n\n";
			$output .= $this->read_item();
			$output .= $this->footer;
			return $output;
	}

	/**
	 +----------------------------------------------------------
	 * 增加一个新的子项
	 +----------------------------------------------------------
	 */
	function read_item()
	{
		$item = $this->array_item();
		
		$arr = "\t\t<url>\n";
		$arr .= "\t\t\t<loc>$this->domain</loc>\n";
		$arr .= "\t\t\t<lastmod>$this->today</lastmod>\n";
		$arr .= "\t\t\t<changefreq>hourly</changefreq>\n";
		$arr .= "\t\t\t<priority>0.9</priority>\n";
		$arr .= "\t\t</url>\n\n";
		
		foreach ($item as $row)
		{
			$arr .= "\t\t<url>\n";
			$arr .= "\t\t\t<loc>$row[url]</loc>\n";
			$arr .= "\t\t\t<lastmod>$row[date]</lastmod>\n";
			$arr .= "\t\t\t<changefreq>$row[changefreq]</changefreq>\n";
			$arr .= "\t\t\t<priority>0.9</priority>\n";
			$arr .= "\t\t</url>\n\n";
		}
		
		return $arr;
	}

	/**
	 +----------------------------------------------------------
	 * 增加一个新的子项
	 +----------------------------------------------------------
	 */
	function array_item()
	{
		/* 单页面列表 */
		$page_list = $GLOBALS['dou']->get_page_list();
		foreach ($page_list as $row)
		{
			$page_array[] = array (
				"date" => $this->today,
				"changefreq" => 'weekly',
				"url" => $row['url']
			);
		}
		
		$item_array = $page_array;
		
		/* 产品分类列表 */
		$product_category = $GLOBALS['dou']->get_product_category($GLOBALS['dou']->fetch_array_all('product_category', 'sort ASC'));
		$product_category_array[] = array (
			"date" => $this->today,
			"changefreq" => 'hourly',
			"url" => $GLOBALS['dou']->rewrite_url('product_category')
		);
		foreach ($product_category as $row)
		{
			$product_category_array[] = array (
				"date" => $this->today,
				"changefreq" => 'hourly',
				"url" => $row['url']
			);
		}

		$item_array = array_merge($item_array, $product_category_array);
		
		/* 产品列表 */
		$product_list = $GLOBALS['dou']->get_recommend_product();
		foreach ($product_list as $row)
		{
			$product_list_array[] = array (
				"date" => $row[add_time],
				"changefreq" => 'weekly',
				"url" => $row['url']
			);
		}

		$item_array = array_merge($item_array,$product_list_array);
		
		/* 文章分类列表 */
		$article_category = $GLOBALS['dou']->get_article_category($GLOBALS['dou']->fetch_array_all('article_category', 'sort ASC'));
		$article_category_array[] = array (
			"date" => $this->today,
			"changefreq" => 'hourly',
			"url" => $GLOBALS['dou']->rewrite_url('article_category')
		);
		foreach ($article_category as $row)
		{
			$article_category_array[] = array (
				"date" => $this->today,
				"changefreq" => 'hourly',
				"url" => $row['url']
			);
		}

		$item_array = array_merge($item_array,$article_category_array);
		
		/* 文章列表 */
		$article_list = $GLOBALS['dou']->get_recommend_article();
		foreach ($article_list as $row)
		{
			$article_list_array[] = array (
				"date" => $row[add_time],
				"changefreq" => 'weekly',
				"url" => $row['url']
			);
		}
	
		$item_array = array_merge($item_array,$article_list_array);
		
		return $item_array;
	}


}
?>