<?php
require_once('config.ini.php');
require_once('DB.php');

$id = @intval($_GET['id']);

$db = new DB();

$sql = "SELECT id,flid,title,cont FROM t_article WHERE id='$id'";
$row = $db->get_row($sql);
$flid = intval($row['flid']);

$sql = "SELECT name FROM t_art_class WHERE id='$flid'";
$art = $db->get_row($sql);

//前一章
$sql = "SELECT id,title FROM t_article WHERE flid='$flid' AND id<".$id." ORDER BY id DESC LIMIT 1";
$pervrow = $db->get_row($sql);
//print_r($pervrow);

//后一章
$sql = "SELECT id,title FROM t_article WHERE flid='$flid' AND id>".$id." ORDER BY id ASC LIMIT 1";
$nextrow = $db->get_row($sql);
//print_r($nextrow);

$db->close();
?>
<!DOCTYPE html> 
<html>
<head>
<title><?php echo $row['title'];?>_<?php echo $art['name'];?></title>
<meta http-equiv="Cache-Control" content="no-transform" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<style type="text/css">
html,body,ul,li{ margin: 6px 3px; padding: 0px;}
li { list-style: none; line-height: 28px; }
.s31 { flex:33.3%; display: inline; text-align: center; }
.first { text-align: center; font-weight: bold; font-size: 200%; }
</style>
</head>
<body>
<div class="first"><?php echo $row['title'];?></div>
<ul style="display: flex;">
<li class="s31"><a href="<?php if($pervrow){echo "?id=".$pervrow['id'];}else{echo "javascript:void(0);";}?>" class="prev">前一章</a></li>
<li class="s31"><a href="/xs/list.php?id=<?php echo $flid;?>">返回目录</a></li>
<li class="s31"><a href="<?php if($nextrow){echo "?id=".$nextrow['id'];}else{echo "javascript:void(0);";}?>" class="next">下一章</a></li>
</ul>
<ul>
<li>　　<?php echo str_replace("\n", "<br>　　", $row['cont']);?></li>
</ul>
<ul style="display: flex;">
<li class="s31"><a href="<?php if($pervrow){echo "?id=".$pervrow['id'];}else{echo "javascript:void(0);";}?>" class="prev">前一章</a></li>
<li class="s31"><a href="/xs/list.php?id=<?php echo $flid;?>">返回目录</a></li>
<li class="s31"><a href="<?php if($nextrow){echo "?id=".$nextrow['id'];}else{echo "javascript:void(0);";}?>" class="next">下一章</a></li>
</ul>
</body>
</html>