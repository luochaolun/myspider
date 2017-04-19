<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('PRC');
/******************************* 数据库设置 *******************************/
define('DB_HOST', 'localhost');		//数据库主机
define('DB_USER', 'testuser');		//数据库用户名
define('DB_PASS', 'testpwd');		//数据库密码
define('DB_NAME', 'mobiles');		//数据库名
define('DB_PORT', '3306');			//数据库端口
define('DB_CHARSET', 'utf8');		//数据库编码,不建议修改
?>
