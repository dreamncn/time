/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 50562
 Source Host           : 127.0.0.1:3306
 Source Schema         : abc_com

 Target Server Type    : MySQL
 Target Server Version : 50562
 File Encoding         : 65001

 Date: 03/07/2020 21:24:50
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for blog_comment
-- ----------------------------
DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE `blog_comment` (
                                `cid` int(11) NOT NULL AUTO_INCREMENT,
                                `gid` int(11) NOT NULL COMMENT '文章id',
                                `pid` int(11) NOT NULL DEFAULT '0' COMMENT '被评论的id',
                                `date` datetime NOT NULL COMMENT '时间',
                                `comment` text NOT NULL COMMENT '评论内容',
                                `qq` varchar(255) DEFAULT NULL COMMENT '评论者qq',
                                `yname` varchar(255) DEFAULT NULL,
                                `hide` tinyint(1) DEFAULT '0',
                                `browser` text NOT NULL COMMENT '浏览器',
                                `opsystem` text NOT NULL COMMENT '操作系统',
                                `admin` tinyint(1) DEFAULT '0',
                                `url` text,
                                PRIMARY KEY (`cid`),
                                KEY `blog_comment_blog_content_gid_fk` (`gid`),
                                CONSTRAINT `blog_comment_blog_content_gid_fk` FOREIGN KEY (`gid`) REFERENCES `blog_content` (`gid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='评论系统';

-- ----------------------------
-- Table structure for blog_content
-- ----------------------------
DROP TABLE IF EXISTS `blog_content`;
CREATE TABLE `blog_content` (
                                `gid` int(11) NOT NULL AUTO_INCREMENT,
                                `title` text NOT NULL,
                                `content` longtext NOT NULL,
                                `date` datetime NOT NULL,
                                `author` varchar(255) NOT NULL,
                                `tag` text COMMENT '标签',
                                `iscopy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否抄袭',
                                `copyurl` varchar(255) DEFAULT NULL COMMENT '抄袭地址',
                                `type` varchar(255) NOT NULL DEFAULT 'article' COMMENT '文章类型：page/article',
                                `views` int(11) NOT NULL DEFAULT '0' COMMENT '阅读量',
                                `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论量',
                                `top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页置顶',
                                `hide` int(11) NOT NULL DEFAULT '0' COMMENT '文章状态0 可视 1草稿 2隐藏',
                                `allowremark` tinyint(1) NOT NULL DEFAULT '1' COMMENT '允许评论',
                                `password` varchar(255) NOT NULL COMMENT '访问密码',
                                `alians` text COMMENT '别名',
                                `ismarkdown` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否使用markdown编辑',
                                `sid` int(11) DEFAULT '1' COMMENT '分类，只允许有一个',
                                `info` text COMMENT '截取一定长度的简介',
                                PRIMARY KEY (`gid`),
                                KEY `blog_content_blog_sort_sid_fk` (`sid`),
                                CONSTRAINT `blog_content_blog_sort_sid_fk` FOREIGN KEY (`sid`) REFERENCES `blog_sort` (`sid`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='自定义页面内容或者发表的内容';

-- ----------------------------
-- Table structure for blog_links
-- ----------------------------
DROP TABLE IF EXISTS `blog_links`;
CREATE TABLE `blog_links` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `sitename` varchar(255) NOT NULL,
                              `siteurl` varchar(255) NOT NULL,
                              `description` varchar(255) NOT NULL,
                              `hide` tinyint(1) NOT NULL DEFAULT '0',
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='友情链接';

-- ----------------------------
-- Table structure for blog_nav
-- ----------------------------
DROP TABLE IF EXISTS `blog_nav`;
CREATE TABLE `blog_nav` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nname` varchar(30) NOT NULL COMMENT '标签名',
                            `url` text,
                            `hide` tinyint(1) NOT NULL DEFAULT '0',
                            `stype` int(11) DEFAULT '0',
                            `pid` int(11) NOT NULL DEFAULT '0' COMMENT '在导航栏的排序',
                            `nexts` text COMMENT '是否含有下级子菜单',
                            `lastest` int(11) DEFAULT '0',
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of blog_nav
-- ----------------------------
BEGIN;
INSERT INTO `blog_nav` VALUES (1, '首页', '/', 0, 0, 0, '', 0);
INSERT INTO `blog_nav` VALUES (2, '归档', '/index/article/Archives', 0, 0, 0, '', 0);
INSERT INTO `blog_nav` VALUES (3, '链接', '/index/main/Urls', 0, 0, 0, '', 0);
COMMIT;

-- ----------------------------
-- Table structure for blog_options
-- ----------------------------
DROP TABLE IF EXISTS `blog_options`;
CREATE TABLE `blog_options` (
                                `op_id` int(11) NOT NULL AUTO_INCREMENT,
                                `op_name` varchar(255) NOT NULL,
                                `op_value` longtext NOT NULL,
                                PRIMARY KEY (`op_id`),
                                UNIQUE KEY `blog_options_op_name_uindex` (`op_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1495 DEFAULT CHARSET=utf8 COMMENT='博客的信息选项设置';

-- ----------------------------
-- Records of blog_options
-- ----------------------------
BEGIN;
INSERT INTO `blog_options` VALUES (1, 'theme', 'cn_dreamn_theme_even');
INSERT INTO `blog_options` VALUES (2, 'blog_name', 'Time');
INSERT INTO `blog_options` VALUES (3, 'info', '');
INSERT INTO `blog_options` VALUES (4, 'seo_description', '');
INSERT INTO `blog_options` VALUES (5, 'seo_key', '');
INSERT INTO `blog_options` VALUES (6, 'copyright', '');
INSERT INTO `blog_options` VALUES (7, 'icp', '');
INSERT INTO `blog_options` VALUES (8, 'footer', '');
INSERT INTO `blog_options` VALUES (9, 'captcha_is_open', '1');
INSERT INTO `blog_options` VALUES (10, 'need_captcha_passwd', '1');
INSERT INTO `blog_options` VALUES (11, 'need_captcha_login', '1');
INSERT INTO `blog_options` VALUES (12, 'need_captcha_comment', '1');
INSERT INTO `blog_options` VALUES (13, 'captcha_type', 'cn_dreamn_plugin_captcha_digital');
INSERT INTO `blog_options` VALUES (14, 'mail', '');
INSERT INTO `blog_options` VALUES (15, 'qq', '');
INSERT INTO `blog_options` VALUES (16, 'github', '');
INSERT INTO `blog_options` VALUES (17, 'author', '');
INSERT INTO `blog_options` VALUES (18, 'mail_notice_me', '1');
INSERT INTO `blog_options` VALUES (19, 'mail_notice_you', '1');
INSERT INTO `blog_options` VALUES (20, 'login_type', 'cn_dreamn_plugin_login_password');
INSERT INTO `blog_options` VALUES (21, 'start_time', '[year]');
INSERT INTO `blog_options` VALUES (22, 'picbed', 'cn_dreamn_plugin_picbed_local');
INSERT INTO `blog_options` VALUES (23, 'blog_open', 'true');
INSERT INTO `blog_options` VALUES (24, 'mail_smtp', '');
INSERT INTO `blog_options` VALUES (25, 'mail_port', '');
INSERT INTO `blog_options` VALUES (26, 'mail_send', '');
INSERT INTO `blog_options` VALUES (27, 'mail_pass', '');
COMMIT;

-- ----------------------------
-- Table structure for blog_plugin
-- ----------------------------
DROP TABLE IF EXISTS `blog_plugin`;
CREATE TABLE `blog_plugin` (
                               `int` int(11) NOT NULL AUTO_INCREMENT,
                               `index` varchar(255) NOT NULL,
                               `value` longtext,
                               PRIMARY KEY (`int`),
                               UNIQUE KEY `blog_plugin_index_uindex` (`index`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of blog_plugin
-- ----------------------------
BEGIN;
INSERT INTO `blog_plugin` VALUES (1, 'cn_dreamn_plugin_login_password_username', '[user]');
INSERT INTO `blog_plugin` VALUES (2, 'cn_dreamn_plugin_login_password_password', '[pass]');
INSERT INTO `blog_plugin` VALUES (3, 'cn_dreamn_plugin_login_password_logintime', '1593769011');
COMMIT;

-- ----------------------------
-- Table structure for blog_sidebar
-- ----------------------------
DROP TABLE IF EXISTS `blog_sidebar`;
CREATE TABLE `blog_sidebar` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `title` text NOT NULL,
                                `html` longtext,
                                `type` int(11) DEFAULT NULL COMMENT '类型，0为系统，1为自定义',
                                `sort` int(11) DEFAULT '0',
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COMMENT='侧栏';

-- ----------------------------
-- Records of blog_sidebar
-- ----------------------------
BEGIN;
INSERT INTO `blog_sidebar` VALUES (4, '标签云', 'tags', 1, 3);
INSERT INTO `blog_sidebar` VALUES (6, '一言', 'hitokoto', 1, 2);
INSERT INTO `blog_sidebar` VALUES (9, '个人信息', 'info', 1, 1);
INSERT INTO `blog_sidebar` VALUES (14, '日历', 'calendar', 1, 4);
COMMIT;

-- ----------------------------
-- Table structure for blog_sort
-- ----------------------------
DROP TABLE IF EXISTS `blog_sort`;
CREATE TABLE `blog_sort` (
                             `sid` int(11) NOT NULL AUTO_INCREMENT,
                             `sname` varchar(255) NOT NULL,
                             PRIMARY KEY (`sid`),
                             UNIQUE KEY `blog_sort_sname_uindex` (`sname`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='分类';

-- ----------------------------
-- Records of blog_sort
-- ----------------------------
BEGIN;
INSERT INTO `blog_sort` VALUES (1, '默认');
COMMIT;

-- ----------------------------
-- Table structure for blog_upload
-- ----------------------------
DROP TABLE IF EXISTS `blog_upload`;
CREATE TABLE `blog_upload` (
                               `id` int(11) NOT NULL AUTO_INCREMENT,
                               `file_path` text COMMENT '路径，有三种 1.网盘 2.静态资源 3.github',
                               `file_title` text COMMENT '文件名称，或者资源名称',
                               `file_bind_id` int(11) DEFAULT NULL COMMENT '绑定的id，一般为article_id',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=utf8mb4 COMMENT='文件上传管理';

-- ----------------------------
-- View structure for blog_comment_view
-- ----------------------------
DROP VIEW IF EXISTS `blog_comment_view`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `blog_comment_view` AS select `blog_comment`.`cid` AS `cid`,`blog_comment`.`gid` AS `gid`,`blog_content`.`title` AS `title`,`blog_comment`.`comment` AS `comment`,`blog_comment`.`date` AS `date`,`blog_comment`.`pid` AS `pid`,`blog_comment`.`qq` AS `qq`,`blog_comment`.`yname` AS `yname`,`blog_comment`.`hide` AS `hide`,`blog_comment`.`browser` AS `browser`,`blog_comment`.`opsystem` AS `opsystem`,`blog_comment`.`admin` AS `admin`,`blog_comment`.`url` AS `url` from (`blog_comment` join `blog_content` on((`blog_comment`.`gid` = `blog_content`.`gid`)));

-- ----------------------------
-- View structure for blog_sort_view
-- ----------------------------
DROP VIEW IF EXISTS `blog_sort_view`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `blog_sort_view` AS select `blog_sort`.`sname` AS `sname`,`blog_content`.`gid` AS `gid`,`blog_content`.`title` AS `title`,`blog_content`.`content` AS `content`,`blog_content`.`date` AS `date`,`blog_content`.`author` AS `author`,`blog_content`.`tag` AS `tag`,`blog_content`.`iscopy` AS `iscopy`,`blog_content`.`copyurl` AS `copyurl`,`blog_content`.`type` AS `type`,`blog_content`.`views` AS `views`,`blog_content`.`comments` AS `comments`,`blog_content`.`top` AS `top`,`blog_content`.`hide` AS `hide`,`blog_content`.`allowremark` AS `allowremark`,`blog_content`.`password` AS `password`,`blog_content`.`alians` AS `alians`,`blog_content`.`ismarkdown` AS `ismarkdown`,`blog_content`.`sid` AS `sid`,`blog_content`.`info` AS `info` from (`blog_content` join `blog_sort` on((`blog_content`.`sid` = `blog_sort`.`sid`)));

SET FOREIGN_KEY_CHECKS = 1;
