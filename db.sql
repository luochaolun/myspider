CREATE TABLE `t_art_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '书名',
  `muluurl` varchar(200) NOT NULL DEFAULT '' COMMENT '目录url',
  `cjflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否采集过',
  PRIMARY KEY (`id`),
  KEY `cjflag` (`cjflag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小说分类' AUTO_INCREMENT=4;

INSERT INTO `t_art_class` (`id`, `name`, `muluurl`, `cjflag`) VALUES
(1, '女人有毒', 'http://m.qqkanshu.com/nvrenyoudu/', 0),
(2, '抗日之特战兵王', 'http://m.qqkanshu.com/kangrizhitezhanbingwang/', 0),
(3, '太古剑神', 'http://m.qqkanshu.com/taigujianshen/', 0);

CREATE TABLE `t_article` (
  `id` int(11) NOT NULL,
  `flid` int(11) NOT NULL DEFAULT 0,
  `filename` varchar(200) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `cont` Text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `flid` (`flid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小说';