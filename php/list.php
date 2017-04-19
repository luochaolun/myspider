<?php
require_once('config.ini.php');
require_once('DB.php');
require_once('Page.php');

$flid = @intval($_GET['id']);

$db = new DB();

$sql = "SELECT name FROM t_art_class WHERE id='$flid'";
$row = $db->get_row($sql);

$sql = "SELECT COUNT(id) FROM t_article WHERE flid='$flid'";
$rowCount = $db->get_one($sql);

$numsPerPage = 25; //每页记录数
$denyGetArr = array();
$p = new Page($rowCount, $numsPerPage, 2, $denyGetArr);				//总记录数，每页记录数
$totalPages = $p->pagecount;						//总页数
$thisPage = $p->currentpage();						//当前页
$startRecord =($p->currentpage()-1)*$numsPerPage;	//起始记录，即limit 起始记录
$strPage = $p->show_page();							//得到分页信息

$sql = "SELECT id,title FROM t_article WHERE flid='$flid' ORDER BY id ASC LIMIT ".$startRecord.",".$numsPerPage;
$results = $db->get_all($sql);
$db->close();
?>
<!DOCTYPE html> 
<html>
<head>
<title><?php echo $row['name'];?></title>
<meta http-equiv="Cache-Control" content="no-transform" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<style type="text/css">
html,body,ul,li{ margin: 6px 3px; padding: 0px;}
li {list-style: none; line-height: 28px;}
li a{text-decoration: none;}
.first {text-align: center; font-weight: bold; font-size: 200%;}
.cen { text-align: center; }
</style>
</head>
<body>
<ul>
<li class="first"><?php echo $row['name'];?></li>
<li class="cen"><?php echo $strPage;?></li>
<?php foreach($results as $k){?><li><a href="detail.php?id=<?php echo $k['id'];?>"><?php echo $k['title'];?></a></li><?php }?>
<li class="cen"><?php echo $strPage;?></li>
</ul>
</body>
</html>