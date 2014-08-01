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
define('EXIT_INIT', true);

require(dirname(__FILE__) . '/include/init.php');
require(ROOT_PATH . 'include/captcha.class.php');

/* 开启SESSION */
session_start();

//实例化验证码
$captcha = new Captcha(70, 25);

//清除之前出现的多余输入
@ob_end_clean();

$captcha->create_captcha();

?>