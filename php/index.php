<?php
require_once('config.ini.php');
require_once('DB.php');

$db = new DB();
$sql = "SELECT id,name FROM t_art_class ORDER BY id ASC";
$results = $db->get_all($sql);
$db->close();
?>
<!DOCTYPE html> 
<html>
<head>
<title>目录</title>
<meta http-equiv="Cache-Control" content="no-transform" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<style type="text/css">
html,body,ul,li{ margin: 6px 3px; padding: 0px;}
li {list-style: none; line-height: 28px;}
li a{text-decoration: none;}
.first{text-align: center; font-weight: bold; font-size: 200%;}
</style>
</head>
<body>
<ul>
<li class="first">目录</li>
<?php foreach($results as $k){?><li><a href="list.php?id=<?php echo $k['id'];?>"><?php echo $k['name'];?></a></li><?php }?>
</ul>
</body>
</html>