/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50141
Source Host           : localhost:3306
Source Database       : chrisd

Target Server Type    : MYSQL
Target Server Version : 50141
File Encoding         : 65001

Date: 2012-03-01 01:21:32
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `admin_accts`
-- ----------------------------
DROP TABLE IF EXISTS `admin_accts`;
CREATE TABLE `admin_accts` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phash` varchar(255) DEFAULT NULL,
  `pcle` varchar(255) DEFAULT NULL,
  `level` int(2) DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `firstname` varchar(20) DEFAULT NULL,
  `lastname` varchar(20) DEFAULT NULL,
  `twitter_link` varchar(255) DEFAULT NULL,
  `google_plus_link` varchar(255) DEFAULT NULL,
  `facebook_link` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `hint` varchar(255) DEFAULT NULL,
  `activated` tinyint(1) DEFAULT '1',
  `blocked` tinyint(1) DEFAULT '0',
  `blocked_time` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of admin_accts
-- ----------------------------
INSERT INTO `admin_accts` VALUES ('1', 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'd494d9a4a7a3ab98:da41cf2102dd5da553224f0a81331cc2', '19fd04f48ebdd17648fa47327e41b8dba2b23d77', '0', 'cdonalds01@gmail.com', 'Chris', 'Donalds', '', '', '', null, null, null, '', '1', '0', null);

-- ----------------------------
-- Table structure for `data_events`
-- ----------------------------
DROP TABLE IF EXISTS `data_events`;
CREATE TABLE `data_events` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `sectionid` int(3) DEFAULT '0',
  `code` varchar(255) DEFAULT NULL,
  `itemtitle` varchar(255) DEFAULT NULL,
  `eventtype` varchar(50) DEFAULT '',
  `start_date` date DEFAULT '0000-00-00',
  `start_time` time DEFAULT '00:00:00',
  `description` text,
  `otheraddress` varchar(255) DEFAULT NULL,
  `draft` tinyint(1) DEFAULT '0',
  `published` tinyint(1) DEFAULT '1',
  `date_published` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of data_events
-- ----------------------------
INSERT INTO `data_events` VALUES ('1', '0', '', 'New Event', 'event', '2011-04-20', '00:00:00', '<p>\r\n	some text</p>', '', '0', '1', '2011-12-24 13:31:59', '2011-12-24 13:31:59');
INSERT INTO `data_events` VALUES ('2', '0', '', 'test', 'event', '2012-07-26', '16:00:00', '<p>\r\n	test event</p>', 'new location', '0', '1', '2011-12-23 20:34:03', '2011-12-23 20:34:03');
INSERT INTO `data_events` VALUES ('3', '0', '', 'a', '', '2011-07-31', '16:00:00', '<p>\r\n	e</p>', 'f', '0', '1', '2011-07-31 03:14:44', null);

-- ----------------------------
-- Table structure for `data_photos`
-- ----------------------------
DROP TABLE IF EXISTS `data_photos`;
CREATE TABLE `data_photos` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `cat_id` int(3) DEFAULT '0',
  `code` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `gallery_def` tinyint(1) DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `rank` int(5) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `date_updated` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `date_published` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of data_photos
-- ----------------------------
INSERT INTO `data_photos` VALUES ('1', '1', 'test123', 'Test123', null, '1', 'images/data_photos/4r.jpg', 'thumbs/data_photos/thm_4r.jpg', '2', '1', '2012-02-15 02:24:57', '2011-12-29 03:52:18');
INSERT INTO `data_photos` VALUES ('2', '1', 'new2', 'New2', null, '0', 'images/data_photos/image001.jpg', 'thumbs/data_photos/thm_image001.jpg', '1', '1', '2012-02-19 17:11:24', '2012-01-03 21:41:56');
INSERT INTO `data_photos` VALUES ('4', '1', 'test_3', 'Test 3', null, '0', 'images/data_photos/009.jpg', 'thumbs/data_photos/thm_009.jpg', '3', '1', '2012-02-19 17:11:41', '2012-01-03 22:07:18');
INSERT INTO `data_photos` VALUES ('5', '1', 'test_3_1', 'Test 3', null, '0', 'images/data_photos/JE902630C.JPG', 'thumbs/data_photos/thm_JE902630C.JPG', '4', '1', '2012-02-19 17:36:05', '2012-01-03 22:08:37');

-- ----------------------------
-- Table structure for `data_photos_cat`
-- ----------------------------
DROP TABLE IF EXISTS `data_photos_cat`;
CREATE TABLE `data_photos_cat` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `sectionid` int(3) DEFAULT '0',
  `cat_id` int(3) DEFAULT '0',
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `rank` int(3) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `date_published` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of data_photos_cat
-- ----------------------------
INSERT INTO `data_photos_cat` VALUES ('1', '0', '0', 'new1', 'New1', '', '0', '1', '2012-01-03 21:11:34', '2012-02-15 02:22:50');

-- ----------------------------
-- Table structure for `data_projects`
-- ----------------------------
DROP TABLE IF EXISTS `data_projects`;
CREATE TABLE `data_projects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) DEFAULT '0',
  `lineage` varchar(254) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `rank` int(6) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `date_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_published` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of data_projects
-- ----------------------------
INSERT INTO `data_projects` VALUES ('1', '2', '0,0', 'first', 'First', 'dsadsadsadsa', '1', '1', '2012-02-18 17:34:32', '0000-00-00 00:00:00');
INSERT INTO `data_projects` VALUES ('2', '0', '0', 'main', 'Main', 'ddsadadsa', '0', '1', '2012-02-16 02:44:16', '0000-00-00 00:00:00');
INSERT INTO `data_projects` VALUES ('3', '2', '0,2', 'second', 'Second', 'fdgrwefwfewfew', '0', '1', '2012-02-18 17:58:55', '0000-00-00 00:00:00');
INSERT INTO `data_projects` VALUES ('4', '3', '0,2,0', 'second1', 'Second1', 'dadadqwdwqdw', '0', '1', '2012-02-18 17:58:55', '0000-00-00 00:00:00');
INSERT INTO `data_projects` VALUES ('5', '2', '0,1', 'third', 'Third', '324f23f32', '0', '1', '2012-02-18 17:34:34', '0000-00-00 00:00:00');
INSERT INTO `data_projects` VALUES ('6', '4', '0,2,0,0', 'second1_1', 'Second1.1', 'fdsfasfdafda', '0', '1', '2012-02-18 17:58:55', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for `data_whatsnew`
-- ----------------------------
DROP TABLE IF EXISTS `data_whatsnew`;
CREATE TABLE `data_whatsnew` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `sectionid` int(3) DEFAULT '0',
  `code` varchar(255) DEFAULT NULL,
  `itemtitle` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT '0000-00-00',
  `start_time` time DEFAULT '00:00:00',
  `shortdescr` varchar(255) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `draft` tinyint(1) DEFAULT '0',
  `published` tinyint(1) DEFAULT '1',
  `date_published` timestamp NULL DEFAULT NULL,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of data_whatsnew
-- ----------------------------
INSERT INTO `data_whatsnew` VALUES ('1', '0', 'new-event', 'New Event', '2011-04-20', '00:00:00', '\r\n	som text', '<p>\r\n	som text</p>', '', '', '0', '1', null, '2011-12-30 00:02:50');
INSERT INTO `data_whatsnew` VALUES ('2', '0', 'test', 'test', '2012-07-26', '16:00:00', null, '<p>\r\n	test event</p>', null, null, '0', '1', '2011-06-25 08:52:49', '2011-12-14 03:09:59');
INSERT INTO `data_whatsnew` VALUES ('4', '0', 'test1', 'test1', '2011-07-31', '00:00:00', '\r\n	fdsfdsfdsfs', '<p>\r\n	fdsfdsfdsfs</p>', '', '', '0', '1', '2011-07-31 21:00:21', '2011-12-14 03:10:05');
INSERT INTO `data_whatsnew` VALUES ('14', '0', 'test5', 'test5', '2011-08-01', '00:00:00', '\r\n	test test', '<p>\r\n	test test</p>', '', '', '0', '1', '2011-08-01 02:13:11', null);

-- ----------------------------
-- Table structure for `editor_userpages`
-- ----------------------------
DROP TABLE IF EXISTS `editor_userpages`;
CREATE TABLE `editor_userpages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pageid` int(11) DEFAULT '0',
  `content` text,
  `link` varchar(255) DEFAULT NULL,
  `objwidth` int(4) DEFAULT '305',
  `objheight` int(4) DEFAULT '205',
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=181 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of editor_userpages
-- ----------------------------
INSERT INTO `editor_userpages` VALUES ('96', '8', '<p>\r\n	<b>We are sorry.</b></p>\r\n<p>\r\n	The page you are requesting cannot be found.</p>\r\n<p>\r\n	Please either refresh your browser by pressing F5 or clear your browser cache.</p>\r\n<p>\r\n	If you still cannot access this page, please contact us.</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('94', '7', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('93', '6', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('63', '10', '<p>\r\n	test</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('168', '11', '<p>\r\n	test</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('64', '12', '<p>\r\n	test4</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('85', '13', '<p>\r\n	test</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('167', '2', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('156', '3', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('92', '5', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('95', '9', '', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('97', '14', '<p>\r\n	test</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('146', '15', '<p>\r\n	test new page</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('103', '16', '<p>\r\n	new page contents</p>', null, '305', '205', null, null);
INSERT INTO `editor_userpages` VALUES ('180', '1', '<p>\r\n	Welcome To The Template Site</p>\r\n<p>\r\n	{testmacro name1=value1}</p>\r\n<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sapien nibh, egestas et mattis non, dictum luctus nulla. Nulla rhoncus iaculis ullamcorper. Duis ornare imperdiet volutpat. Nulla facilisi. Phasellus sollicitudin diam a lorem accumsan mattis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam et arcu urna, et faucibus quam. Morbi consequat convallis lacus, et rhoncus orci laoreet nec. Suspendisse porttitor accumsan metus et bibendum. Aliquam gravida, nunc id sodales feugiat, est eros adipiscing mauris, non luctus nunc metus sit amet orci. Morbi vitae dolor elit. Mauris cursus rhoncus viverra. Suspendisse auctor egestas ipsum vel tempor. Sed dolor enim, vehicula ac pharetra eu, aliquam sit amet augue. Sed et ornare mauris. Fusce tincidunt tempor ipsum vel egestas. Proin volutpat viverra mollis. Nam et feugiat ipsum. Proin imperdiet, mauris a iaculis eleifend, turpis diam dapibus metus, sit amet hendrerit nunc est vitae lorem.</p>\r\n<p>\r\n	Fusce ut eros non libero porta euismod et ac velit. Morbi bibendum feugiat varius. Suspendisse placerat, dui ut scelerisque pharetra, velit turpis accumsan turpis, a bibendum eros magna quis tellus. Aliquam ultricies iaculis molestie. Donec at felis ultricies nisl feugiat viverra eu non lacus. Donec ac sem pretium metus lacinia tempor. Curabitur ut metus sed leo placerat ultricies. Proin volutpat scelerisque enim, vel venenatis lorem luctus non. Etiam convallis nulla in urna imperdiet tempus. Etiam consequat mattis cursus. Vestibulum at sapien erat, id euismod ante. Etiam eu augue mauris, et imperdiet lacus. Fusce at orci nibh, id dapibus ligula. Vestibulum est ante, malesuada ac mollis quis, sollicitudin ac sapien. Maecenas sit amet tortor in sem commodo pharetra in et massa. Suspendisse lobortis luctus nibh ut hendrerit. Mauris vulputate imperdiet blandit. Aliquam aliquam velit non arcu malesuada a varius nisl semper.</p>\r\n<p>\r\n	Donec volutpat turpis pulvinar metus sodales luctus. Nunc porttitor metus ut nisl scelerisque condimentum. Sed commodo tempor nulla a gravida. Ut suscipit bibendum semper. In hac habitasse platea dictumst. Morbi id egestas mauris. Nunc vel felis nisi, eget pulvinar sem. Maecenas ultricies commodo lorem nec posuere. Aliquam sit amet odio diam, nec sodales augue. Aliquam ut est augue, id vulputate justo. Mauris lorem nisl, sollicitudin vehicula eleifend vitae, aliquam quis lorem. Curabitur blandit pulvinar lorem, nec mollis lorem varius non. Ut ut metus sit amet enim adipiscing convallis quis vel tellus. Phasellus imperdiet interdum molestie</p>\r\n<h2>\r\n	Sub Heading Text</h2>\r\n<p>\r\n	Donec volutpat turpis pulvinar metus sodales luctus. Nunc porttitor metus ut nisl scelerisque condimentum. Sed commodo tempor nulla a gravida. Ut suscipit bibendum semper. In hac habitasse platea dictumst. Morbi id egestas mauris. Nunc vel felis nisi, eget pulvinar sem. Maecenas ultricies commodo lorem nec posuere. Aliquam sit amet odio diam, nec sodales augue. Aliquam ut est augue, id vulputate justo. Mauris lorem nisl, sollicitudin vehicula eleifend vitae, aliquam quis lorem. Curabitur blandit pulvinar lorem, nec mollis lorem varius non. Ut ut metus sit amet enim adipiscing convallis quis vel tellus. Phasellus imperdiet interdum molestie</p>\r\n<p>\r\n	Donec volutpat turpis pulvinar metus sodales luctus. Nunc porttitor metus ut nisl scelerisque condimentum. Sed commodo tempor nulla a gravida. Ut suscipit bibendum semper. In hac habitasse platea dictumst. Morbi id egestas mauris. Nunc vel felis nisi, eget pulvinar sem. Maecenas ultricies commodo lorem nec posuere. Aliquam sit amet odio diam, nec sodales augue. Aliquam ut est augue, id vulputate justo. Mauris lorem nisl, sollicitudin vehicula eleifend vitae, aliquam quis lorem. Curabitur blandit pulvinar lorem, nec mollis lorem varius non. Ut ut metus sit amet enim adipiscing convallis quis vel tellus. Phasellus imperdiet interdum molestie</p>', null, '305', '205', null, null);

-- ----------------------------
-- Table structure for `editor_userpages_images`
-- ----------------------------
DROP TABLE IF EXISTS `editor_userpages_images`;
CREATE TABLE `editor_userpages_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `temp_id` varchar(255) DEFAULT NULL,
  `page_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `rank` int(11) DEFAULT '1',
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=355 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of editor_userpages_images
-- ----------------------------

-- ----------------------------
-- Table structure for `media`
-- ----------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rec_id` int(10) NOT NULL DEFAULT '0',
  `table` varchar(255) NOT NULL,
  `doc_type` enum('video','web','doc','audio','pdf','image') NOT NULL,
  `has_thumbnail` tinyint(1) DEFAULT '0',
  `filepath` varchar(255) NOT NULL,
  `copy_of_id` int(10) DEFAULT NULL,
  `registered_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of media
-- ----------------------------

-- ----------------------------
-- Table structure for `navtweet`
-- ----------------------------
DROP TABLE IF EXISTS `navtweet`;
CREATE TABLE `navtweet` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `callback` varchar(255) DEFAULT NULL,
  `return_url` varchar(255) DEFAULT NULL,
  `twitter_section` varchar(255) DEFAULT NULL,
  `twitter_op` varchar(255) DEFAULT NULL,
  `twitter_content` text,
  `twitter_link` varchar(255) DEFAULT NULL,
  `twitter_linkname` varchar(255) DEFAULT NULL,
  `oauth_token` varchar(255) DEFAULT NULL,
  `oauth_token_secret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of navtweet
-- ----------------------------
INSERT INTO `navtweet` VALUES ('53', null, 'http://localhost/chrisd/admin/whatsnew/list-whatsnew.php', 'status', 'update', '\r\n	fdsfdsfdsfs', 'http://localhost/chrisd/', 'News Article', null, null);

-- ----------------------------
-- Table structure for `page_types`
-- ----------------------------
DROP TABLE IF EXISTS `page_types`;
CREATE TABLE `page_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of page_types
-- ----------------------------
INSERT INTO `page_types` VALUES ('1', 'editor', 'Page is constructed with a WYSIWYG editor', '0');
INSERT INTO `page_types` VALUES ('2', 'data', 'Page provides access to database-driven content', '1');
INSERT INTO `page_types` VALUES ('3', 'form', 'Page includes a simple form', '1');

-- ----------------------------
-- Table structure for `pages`
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sectionid` int(11) NOT NULL DEFAULT '0',
  `ppage_id` int(11) DEFAULT '0',
  `lineage` varchar(254) DEFAULT NULL,
  `pagename` varchar(50) NOT NULL,
  `pagealias` varchar(50) DEFAULT NULL,
  `pagetitle` varchar(50) NOT NULL,
  `metatitle` varchar(255) DEFAULT NULL,
  `metakeywords` varchar(512) DEFAULT NULL,
  `metadescr` varchar(255) NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT 'english',
  `pagetypeid` int(11) NOT NULL DEFAULT '1',
  `locked` tinyint(1) NOT NULL DEFAULT '1',
  `searchable` tinyint(1) NOT NULL DEFAULT '1',
  `displayed` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `subpagesallowed` tinyint(1) DEFAULT '0',
  `rank` int(6) DEFAULT '0',
  `draft` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_published` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES ('1', '0', '0', '0', 'home-page', 'index', 'Home Page', 'Home Page', '', 'Welcome To The Template SiteLorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sapien nibh, egestas et mattis non, dictum luctus nulla. Nulla rhoncus iaculis ullamcorper. Duis ornare imperdiet volutpat. Nulla facilisi. Phasellus sollici', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-01-26 09:12:56', '2012-02-25 23:31:57', '2012-02-11 03:14:50');
INSERT INTO `pages` VALUES ('2', '0', '0', '1', 'moreinfo/about-us', '', 'About Us', '', null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-01-26 09:13:05', '2012-02-16 04:19:38', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('3', '0', '0', '2', 'events', '', 'Events', null, null, '', 'english', '2', '0', '1', '1', '0', '0', '0', '0', '1', '2010-01-26 09:14:24', '2012-01-29 23:15:01', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('4', '0', '0', '3', 'galleries', '', 'Galleries', null, null, '', 'english', '2', '0', '1', '1', '0', '0', '0', '0', '1', '2010-01-26 09:14:42', '2012-01-29 21:34:07', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('5', '0', '0', '4', 'requestinfo', '', 'Request More Info', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-02-15 15:30:48', '2012-01-29 21:34:07', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('6', '0', '0', '5', 'contactus', '', 'Contact Us', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-04-07 16:05:47', '2012-01-29 21:34:07', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('7', '0', '0', '6', 'whatsnew', '', 'Whats New', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-04-07 16:06:08', '2012-01-29 21:34:07', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('8', '0', '0', '7', '404', '', 'Page Not Found', null, null, '', 'english', '1', '0', '0', '0', '1', '0', '0', '0', '1', '2010-06-11 10:46:19', '2012-02-16 04:06:37', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('9', '0', '0', '8', 'sitemap', '', 'Sitemap', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-11-23 10:37:57', '2012-01-29 21:34:07', '2011-12-30 00:55:52');
INSERT INTO `pages` VALUES ('10', '0', '11', '0,0,0', 'test2', '', 'test2', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '1', '0', '1', '2010-12-06 15:12:28', '2012-01-29 21:34:07', '2011-12-30 00:55:51');
INSERT INTO `pages` VALUES ('11', '0', '1', '0,0', 'test', '', 'test', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '2010-12-06 15:29:40', '2012-02-16 04:19:47', '2012-02-04 11:54:19');
INSERT INTO `pages` VALUES ('16', '0', '1', '0,1', 'some-page', '', 'Some page', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '0000-00-00 00:00:00', '2012-01-29 21:34:07', '2011-12-30 00:55:51');
INSERT INTO `pages` VALUES ('15', '0', '2', '1,0', 'new-page', '', 'new page', null, null, '', 'english', '1', '0', '1', '1', '0', '0', '0', '0', '1', '0000-00-00 00:00:00', '2012-01-29 22:05:40', '2011-12-30 00:55:52');

-- ----------------------------
-- Table structure for `plugins`
-- ----------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `ver` varchar(10) DEFAULT '1.0',
  `sysver` varchar(10) DEFAULT '3.00',
  `author` varchar(255) DEFAULT NULL,
  `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `revised` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `descr` varchar(255) DEFAULT NULL,
  `license` varchar(50) DEFAULT 'free',
  `website` varchar(255) DEFAULT NULL,
  `usedin` varchar(5) DEFAULT 'both',
  `folder` varchar(255) DEFAULT NULL,
  `depends` varchar(255) DEFAULT NULL,
  `incl` varchar(50) DEFAULT NULL,
  `initfile` varchar(255) DEFAULT NULL,
  `headerfunc` varchar(255) DEFAULT NULL,
  `settingsfunc` varchar(255) DEFAULT NULL,
  `nodisable` tinyint(1) DEFAULT '0',
  `nodelete` tinyint(1) DEFAULT '0',
  `builtin` tinyint(1) DEFAULT '0',
  `inline_settings` varchar(255) DEFAULT NULL,
  `custom_settings` text,
  `is_framework` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `errors` text,
  `error_code` int(3) DEFAULT '0',
  `updflag` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of plugins
-- ----------------------------
INSERT INTO `plugins` VALUES ('1', 'CKEditor', '3.5', '1.5', 'Frederico Knabben', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'CMS HTML editor', 'free', 'http://www.ckeditor.com', 'both', null, 'jquery', 'ckeditor', 'jquery.min.js', '', '', '1', '1', '1', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('2', 'Auto-Complete', '1.0', '3.0', 'Chris Donalds (orig: JQuery team, JÃ¶rn Zaefferer)', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Adds JQuery autocomplete functionality to select menus', 'free', 'http://www.navigatormultimedia.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/autocomplete/', 'jquery', 'autocomplete', 'autocomplete.core.php', 'autocomplete_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('3', 'CD-Cal', '2.0', '2.6', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Drop-in events calendar with popup link support.', 'free', 'http://www.navigatormultimedia.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/cdcal/', 'jquery', 'cdcal', 'cdcal.core.php', 'cdcal_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('4', 'Cipher', '1.5', '2.6', 'Chris Donalds (orig: Peter Hanneman SecureCube)', '2011-04-14 00:00:00', '2011-04-15 00:00:00', '2-way encryption tool using 320-bit private SHA-1/256-bit-public MD5 encryption', 'free', 'http://www.navigatormultimedia.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/cipher/', '', 'cipher', 'cipherlib.php', 'ciherlib_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('5', 'CRON-Lib', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Manages scheduled tasks (simulated CRON) for site.', 'free', 'http://www.navigatormultimedia.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/cron/', null, 'cron', 'cron.core.php', 'cron_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('33', 'Administer', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Foundry admin access bar', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/administer/', 'jquery', 'administer', 'administer.core.php', 'administer_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('7', 'LiveClock', '1.0', '3.0', 'Chris Donalds', '2011-06-05 00:00:00', '2011-06-05 00:00:00', 'Analog/digital clock widget with customizable faces', 'free', 'http://www.navigatormultimedia.com', 'front', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/liveclock/', '', 'liveclock', 'liveclock.core.php', 'liveclock_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('8', 'My Plugin', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'First plugin', 'free', null, 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/myplugin/', 'jquery', 'myplugin', 'myplugin.core.php', 'myplugin_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('9', 'My Plugin2', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'First plugin', 'free', 'http://www.google.ca', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/myplugin2/', 'myplugin', 'myplugin2', 'myplugin.core.php', 'myplugin2_headerprep', 'myplugin2_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('10', 'NavTweet', '2.0', '2.6', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Twitter posting utility.  If ShrinkURL is enabled, links will be minified.', 'free', 'http://www.navigatormultimedia.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/navtweet/', 'shrinkurl', 'twitter', 'navtweet.core.php', 'navtweet_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('23', 'Googlemap', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Google Map API integrator', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/googlemap/', 'jquery', 'googlemap', 'googlemap.core.php', 'googlemap_headerprep', 'googlemap_settings', '0', '0', '0', null, 'ABQIAAAA5izKpp6wQ-18IrM13GTxohT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQojbWh7ZsqRPOv14SD0h5Cu7TCDQ', '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('12', 'jQuery UI', '1.8.18', '2', 'various', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'User Interface module for jQuery.  Includes UI Core, several interactions, widgets, and effects.', 'free', 'http://www.jqueryui.com', 'both', null, 'jquery', 'jqueryui', 'jquery-ui.min.js', '', '', '1', '1', '1', 'ver', null, '1', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('13', 'jQuery', '1.7.1', '2', 'John Resig', '2010-11-11 00:00:00', '0000-00-00 00:00:00', 'jQuery core framework.', 'free', 'http://www.jquery.com', 'both', null, '', 'jquery', 'jquery.min.js', '', '', '1', '1', '1', 'ver', null, '1', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('24', 'Mootools', '1.3.2', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Mootools Framework.  ', 'free', 'http://www.mootools.net', 'both', null, '', 'mootools', 'mootools-yui-compressed.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('25', 'Prototype', '1.7.0.0', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Prototype Framework.  Warning: Prototype and JQuery $-use are incompatible.  See further notes in _config/frameworks.ini', 'free', 'http://www.prototypejs.org', 'both', null, '', 'prototype', 'prototype.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('26', 'Chrome Frame', '1.0.2', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Chrome Frame Framework.  ', 'free', 'https://code.google.com/chrome/chromeframe/', 'both', null, '', 'chrome-frame', 'CFInstall.min.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('27', 'Dojo', '1.6.1', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Dojo Framework.  ', 'free', 'http://dojotoolkit.org/', 'both', null, '', 'dojo', 'dojo/dojo.xd.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('28', 'Ext Core', '3.1.0', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Ext Core Framework.  ', 'free', 'http://www.sencha.com/products/extjs/', 'both', null, '', 'ext-core', 'ext-core.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('29', 'Script.aculo.us', '1.9.0', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Script.aculo.us Framework.  ', 'free', 'http://script.aculo.us/', 'both', null, 'prototype', 'scriptaculous', 'scriptaculous.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('30', 'SWFObject', '2.2', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'SWFObject Framework.  ', 'free', 'http://code.google.com/p/swfobject/', 'both', null, '', 'swfobject', 'swfobject.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('31', 'Yahoo! User Interface Library (YUI)', '3.3.0', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'Yahoo! User Interface Library (YUI) Framework.  ', 'free', 'http://developer.yahoo.com/yui/', 'both', null, '', 'yui', 'build/yui/yui-min.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('32', 'WebFont Loader', '1.0.22', '3.00', null, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'WebFont Loader Framework.  ', 'free', 'http://code.google.com/apis/webfonts/docs/webfont_loader.html', 'both', null, '', 'webfont', 'webfont.js', null, null, '0', '1', '0', null, null, '1', '0', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('70', 'jQuery Mobile', '1.0', '3.0', 'The jQuery Project', '2011-12-01 00:00:00', '2011-12-01 00:00:00', 'A unified user interface system that works seamlessly across all popular mobile device platforms, built on the rock-solid jQuery and jQuery UI foundation.', 'free', 'http://www.jquerymobile.com', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/jquerymobile/', 'jquery', 'jquerymobile', 'jquerymobile.core.php', 'jquerymobile_headerprep', 'jquerymobile_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('34', 'Browser Detector', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Detects various information about the user agent (browser)', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/browserdetector/', '', 'browserdetector', 'detect.class.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '-1');
INSERT INTO `plugins` VALUES ('35', 'Captcha', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Captcha code handler', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/captcha/', '', 'captcha', 'CaptchaSecurityImages.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('36', 'DynSearch', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Dynamic site search engine tool', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/dynsearch/', '', 'dynsearch', 'dynsearch.core.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('37', 'Exporter', '1.0', '2.6', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'CSV export preparation tool', 'free', 'http://www.navigatormultimedia.com', 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/exporters/', '', 'exportcsv', 'exportcsv.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('38', 'File Uploader', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'File upload, and thumbnail generator utility', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/fileuploader/', 'jquery', 'fileuploader', 'fileuploader.core.php', 'fileuploader_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('39', 'Imageset Marker', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Adds the imageset marker feature and button to CKEditor', 'free', null, 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/imagesetmarker/', 'ckeditor', 'imagesetmarker', 'imageset.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('40', 'Image Editor', '1.0', '3.0', 'Chris Donalds, parts based on the Jcrop plugin by Kelly Hallman @khallman@gmail.com', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Image editor (cropping, scaling, rotation, flipping) utility.  Works in conjunction with and requires FileUploader', 'free', null, 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/imgedit/', 'fileuploader', 'imgedit', 'imgedit.core.php', 'imgedit_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('41', 'Jquery Validator', '1.7', '3.0', 'JÃ¶rn Zaefferer', '2009-06-17 00:00:00', '2009-06-17 00:00:00', 'Adds jQuery-powered form field validation to forms', 'free', 'http://docs.jquery.com/Plugins/Validation', 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/jquery.validator/', 'jquery', 'validator', 'validator.core.php', 'validator_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', 'The INITFILE was not found in the jquery.validator/ folder.', '8192', '-1');
INSERT INTO `plugins` VALUES ('42', 'JTime', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Adds jQuery time/date controls', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/jtime/', 'jquery', 'jtime', 'jtime.core.php', 'jtime_headerprep', null, '0', '0', '0', null, null, '0', '1', '0', 'The INITFILE was not found in the jtime/ folder.', '8192', '-1');
INSERT INTO `plugins` VALUES ('43', 'Lightbox for Foundry', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'The Foundry version of the popular Lightbox addon.  * Foundry plugin compatibility not part of original authored code.', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/lightbox/', 'jquery', 'lightbox', 'lightbox.core.php', 'lightbox_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', 'The INITFILE was not found in the lightbox/ folder.', '8192', '-1');
INSERT INTO `plugins` VALUES ('44', 'Foundry Login', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Adds login and authentication facilities to Foundry websites', 'free', null, 'front', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/login/', '', 'login', 'login.php', 'myplugin_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('45', 'FormtoEmail Pro (Foundry version)', '1.0', '3.0', 'Chris Donalds. (c) FormToEmail.com 2006 - 2009', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Form2Email Pro adaptation for Foundry', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/mail/', 'jquery', 'form2email', 'formtoemail_head.php', 'form2email_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('46', 'My Plugin', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'First plugin', 'free', null, 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/paypal/', 'jquery', 'myplugin', 'myplugin.core.php', 'myplugin_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', 'The name &#34;My Plugin&#34; in paypal/plugin.info has been registered to another plugin.<br/>The INITFILE was not found in the paypal/ folder.', '8193', '-2');
INSERT INTO `plugins` VALUES ('47', 'ShrinkURL', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Minifies URLs to the host site, similar to TinyURL.', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/shrinkurl/', 'jquery', 'shrinkurl', 'shrinkurl.core.php', 'myplugin_headerprep', 'shrinkurl_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('48', 'Sitemap Generator', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Generates Google-compatible sitemap XML and content inclusion file.  Processes files and/or database records', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/sitemapgen/', '', 'sitemap', 'xmlsitemapgen.php', 'myplugin_headerprep', 'sitemapgen_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('49', 'SMS', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'SMS submission tool', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/sms/', '', 'sms', 'xmlsender.php', 'myplugin_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('50', 'TubePress Pro for Foundry', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'The Foundry version of the popular TubePress Pro tool.', 'free', null, 'front', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/tubepress_pro/', '', 'tubepresspro', 'tubepress.php', '', '', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('51', 'Upload Progressbar', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'Progressbar addon', 'free', null, 'admin', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/uploadprogressbar/', '', 'uploadprogressbar', 'uploadprogressbar.php', 'uploadprogressbar_headerprep', 'myplugin_settings', '0', '0', '0', null, null, '0', '1', '0', 'The INITFILE was not found in the uploadprogressbar/ folder.', '8192', '-1');
INSERT INTO `plugins` VALUES ('52', 'WeatherNet', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'WeatherNet adds a weather widget to webpages via feeds from The Weather Network or Environment Canada.', 'free', null, 'front', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/weather/', '', 'weather', 'weather.core.php', '', '', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('53', 'ePaypal', '1.0', '3.0', 'Chris Donalds', '2011-04-14 00:00:00', '2011-04-15 00:00:00', 'PayPal ecommerce Direct Payment API', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/epaypal/', null, 'paypal', 'paypal.core.php', null, 'paypal_settings', '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');
INSERT INTO `plugins` VALUES ('69', 'Mobile Device Detector', '1.0', '3.0', 'Anthony Hand', '2011-06-04 00:00:00', '2011-06-04 00:00:00', 'Detects various information about the user agent (browser)', 'free', null, 'both', 'C:/Storage Backup/Web Files/chrisd/admin/inc/_plugins/mobiledetector/', '', 'mobidetector', 'detect.class.php', null, null, '0', '0', '0', null, null, '0', '1', '0', '', '0', '49029');

-- ----------------------------
-- Table structure for `register`
-- ----------------------------
DROP TABLE IF EXISTS `register`;
CREATE TABLE `register` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `db_table` varchar(50) DEFAULT NULL,
  `db_child_table` varchar(50) DEFAULT NULL,
  `db_parent_table` varchar(50) DEFAULT NULL,
  `function` varchar(255) DEFAULT NULL,
  `parameters` text,
  `trigger` varchar(50) DEFAULT NULL,
  `priority` int(2) DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=621 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of register
-- ----------------------------
INSERT INTO `register` VALUES ('1', 'db', '/chrisd/admin/pages/list-pages.php', 'pages', 'editor-userpages', null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('2', 'db', '/chrisd/admin/events/list-events.php', 'data_events', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('3', 'db', '/chrisd/admin/photos/list-photos_cat.php', 'data_photos_cat', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('5', 'db', '/chrisd/admin/pages/edit-userpage.php', 'pages', 'editor_userpages', null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('10', 'db', '/chrisd/admin/photos/edit-photos_cat.php', 'data_photos_cat', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('11', 'db', '/chrisd/admin/photos/edit-photos.php', 'data_photos', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('49', 'db', '/chrisd/admin/photos/list-photos.php', 'data_photos', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('122', 'showlist', '/chrisd/admin/pages/list-pages.php', null, null, null, null, '{\"query\":\"SELECT p.*, pp.pagetitle as parentpage    FROM pages p    LEFT JOIN pages pp    ON p.ppage_id = pp.id WHERE p.pagetypeid > 0 ORDER BY lineage ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"pagetitle\":\"Page\",\"parentpage\":\"Parent\",\"metatitle\":\"Meta Title\",\"metakeywords\":\"Keywords\",\"metadescr\":\"Meta Description\",\"pagealias\":\"Homepage?\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"pagetitle\":\"attr:indent; padstr:\'&nbsp\\\\;&nbsp\\\\;&nbsp\\\\;&nbsp\\\\;\'; checkfield:lineage; checkval:,\",\"metatitle\":\"attr:boolean; trueval:Completed; falseval:Incomplete\",\"metakeywords\":\"attr:boolean; trueval:Completed; falseval:Incomplete\",\"metadescr\":\"attr:boolean; trueval:Completed; falseval:Incomplete\",\"pagealias\":\"attr:advexpr; compareusing:=; compareval:index; trueval:Yes; falseval:\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"pagetitle\":\"15%\",\"parentpage\":\"15%\",\"metatitle\":\"10%\",\"metakeywords\":\"10%\",\"metadescr\":\"10%\",\"pagealias\":\"10%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":{\"1\":[\"edit:: Page\",\"editmeta\",\"publish\",\"unpublish\",\"clone\",\"delete\",\"undelete\"],\"2\":[\"edit:: Page\",\"editmeta\",\"publish\",\"unpublish\",\"delete\",\"undelete\"],\"3\":[\"edit:: Page\",\"editmeta\",\"publish\",\"unpublish\",\"delete\",\"undelete\"]},\"buttontagfield\":\"pagename\",\"buttoncondindex\":\"pagetypeid\",\"altparams\":{\"edit\":\"test_id\"},\"altgroups\":{\"delete\":\"test\"},\"addqueries\":{\"publish\":\"pub_id=1\",\"unpublish\":\"unpub_id=1\"},\"titlefld\":\"pagetitle\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('123', 'showlist', '/chrisd/admin/photos/list-photos_cat.php', null, null, null, null, '{\"query\":\"SELECT pg.*, COUNT(p.id) AS numpics, (SELECT thumb FROM data_photos WHERE gallery_def = 1 AND cat_id = pg.id) AS thumb    FROM data_photos_cat AS pg    LEFT JOIN data_photos AS p    ON pg.id = p.cat_id GROUP BY pg.name ORDER BY pg.name ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"name\":\"Gallery\",\"numpics\":\"# Images\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"name\":\"attr:image\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"name\":\"30%\",\"numpics\":\"10%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"view::Images\",\"add::Image\",\"publish\",\"unpublish\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"name\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('126', 'showlist', '/chrisd/admin/events/list-events.php', null, null, null, null, '{\"query\":\"SELECT *    FROM data_events as d WHERE eventtype = \'event\' ORDER BY itemtitle ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"itemtitle\":\"Title\",\"start_date\":\"Start Date\",\"description\":\"Description\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"description\":\"attr:hover\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"itemtitle\":\"30%\",\"start_date\":\"15%\",\"description\":\"15%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"clone::title\",\"publish\",\"unpublish\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"itemtitle\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('127', 'showlist', '/chrisd/admin/whatsnew/list-whatsnew.php', null, null, null, null, '{\"query\":\"SELECT w.*    FROM data_whatsnew w ORDER BY start_date DESC LIMIT 0, 100\",\"cols\":{\"start_date\":\"Date\",\"itemtitle\":\"Title\",\"description\":\"Description\",\"actions\":\"\"},\"colattr\":{\"description\":\"attr:hover\"},\"colsize\":{\"start_date\":\"15%\",\"itemtitle\":\"15%\",\"description\":\"30%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"clone\",\"delete\",\"undelete\",\"publish\",\"unpublish\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"itemtitle\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('149', 'db', '/chrisd/admin/events/add-events.php', 'data_events', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('150', 'showlist', '/chrisd/admin/photos/list-photos.php?cat_id=1', null, null, null, null, '{\"query\":\"SELECT p.*, IFNULL(pg.name, \'[Unattached]\') as gallery    FROM data_photos AS p    LEFT JOIN data_photos_cat AS pg    ON p.cat_id = pg.id WHERE p.cat_id = 1 ORDER BY gallery, rank ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"gallery\":\"Gallery\",\"title\":\"Image\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"title\":\"attr:image\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"gallery\":\"15%\",\"title\":\"20%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"publish\",\"unpublish\",\"default::Gallery Image\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"title\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('128', 'showlist', '/chrisd/admin/photos/list-photos.php?page=1&cat_id=1', null, null, null, null, '{\"query\":\"SELECT p.*, IFNULL(pg.name, \'[Unattached]\') as gallery    FROM data_photos AS p    LEFT JOIN data_galleries AS pg    ON p.cat_id = pg.id WHERE p.cat_id = 1 ORDER BY gallery, rank ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"gallery\":\"Gallery\",\"title\":\"Image\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"title\":\"attr:image\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"gallery\":\"15%\",\"title\":\"20%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"button_array\":[\"edit\",\"publish\",\"unpublish\",\"default::Gallery Image\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"title\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('151', 'db', '/chrisd/admin/events/edit-events.php', 'data_events', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('152', 'db', '/chrisd/admin/pages/edit-pages.php', 'pages', 'editor_userpages', null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('154', 'db', '/chrisd/admin/photos/add-photos.php', 'data_photos', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('155', 'db', '/chrisd/admin/pages/add-pages.php', 'pages', 'editor_userpages', null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('156', 'db', '/chrisd/admin/photos/add-photos_cat.php', 'data_galleries', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('157', 'db', '/chrisd/admin/pages/edit-meta.php', 'pages', 'editor_userpages', null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('158', 'showlist', '/chrisd/admin/photos/list-photos.php', null, null, null, null, '{\"query\":\"SELECT p.*, IFNULL(pg.name, \'[Unattached]\') as gallery    FROM data_photos AS p    LEFT JOIN data_galleries AS pg    ON p.cat_id = pg.id WHERE p.cat_id = ORDER BY gallery, rank ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"gallery\":\"Gallery\",\"title\":\"Image\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"title\":\"attr:image\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"gallery\":\"15%\",\"title\":\"20%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"button_array\":[\"edit\",\"publish\",\"unpublish\",\"default::Gallery Image\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"title\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('159', 'showlist', '/chrisd/admin/photos/list-photos.php?cat_id=0', null, null, null, null, '{\"query\":\"SELECT p.*, IFNULL(pg.name, \'[Unattached]\') as gallery    FROM data_photos AS p    LEFT JOIN data_galleries AS pg    ON p.cat_id = pg.id WHERE p.cat_id = 0 ORDER BY gallery, rank ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"gallery\":\"Gallery\",\"title\":\"Image\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"title\":\"attr:image\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"gallery\":\"15%\",\"title\":\"20%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"publish\",\"unpublish\",\"default::Gallery Image\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"title\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('176', 'db', '/chrisd/admin/projects/list-projects.php', 'data_projects', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('160', 'db', '/chrisd/admin/whatsnew/add-whatsnew.php', 'data2_whatsnew', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('161', 'dataalias', '/events/@startyear@/@startmonth@/@startday@', 'events', null, null, null, '{\"sd\":\"$1-$2-$3\"}', null, '0', '1');
INSERT INTO `register` VALUES ('162', 'dataalias', '/photos/@categories@/@code@', 'photos', null, null, null, '{\"mc\":\"$1\",\"c\":\"$2\"}', null, '0', '1');
INSERT INTO `register` VALUES ('163', 'dataalias', '/gallery/@categories@', 'photos_cat', null, null, null, '{\"mc\":\"$1\"}', null, '0', '1');
INSERT INTO `register` VALUES ('169', 'dataalias', '/project/@categories@', 'projects', null, null, null, '{\"mc\":\"$1\"}', null, '0', '1');
INSERT INTO `register` VALUES ('177', 'showlist', '/chrisd/admin/projects/list-projects.php', null, null, null, null, '{\"query\":\"SELECT p.*, pp.name as parent    FROM data_projects p    LEFT JOIN data_projects pp    ON p.cat_id = pp.id ORDER BY lineage ASC LIMIT 0, 100\",\"cols\":{\"_chk\":\"\",\"name\":\"Project\",\"parent\":\"Parent\",\"published\":\"Status\",\"actions\":\"\"},\"colattr\":{\"name\":\"attr:indent; padstr:\'&nbsp\\\\;&nbsp\\\\;&nbsp\\\\;&nbsp\\\\;\'; checkfield:lineage; checkval:,\",\"published\":\"attr:boolean; trueval:Published; falseval:Draft\"},\"colsize\":{\"name\":\"15%\",\"parent\":\"15%\",\"published\":\"8%\",\"actions\":\"\"},\"totalcols\":null,\"buttons\":[\"edit\",\"publish\",\"unpublish\",\"delete\",\"undelete\"],\"buttontagfield\":\"\",\"buttoncondindex\":\"\",\"altparams\":null,\"altgroups\":null,\"addqueries\":null,\"titlefld\":\"name\",\"imagefld\":\"image\",\"thumbfld\":\"thumb\"}', null, '0', '1');
INSERT INTO `register` VALUES ('178', 'db', '/chrisd/admin/projects/add-projects.php', 'data_projects', null, null, null, null, null, '0', '1');
INSERT INTO `register` VALUES ('179', 'db', '/chrisd/admin/projects/edit-projects.php', 'data_projects', null, null, null, null, null, '0', '1');

-- ----------------------------
-- Table structure for `sections`
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `alias` varchar(50) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `lat` float(20,10) DEFAULT NULL,
  `lon` float(20,10) DEFAULT NULL,
  `weatherurl` varchar(255) DEFAULT NULL,
  `citycode` varchar(10) DEFAULT NULL,
  `area` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sections
-- ----------------------------

-- ----------------------------
-- Table structure for `session_login`
-- ----------------------------
DROP TABLE IF EXISTS `session_login`;
CREATE TABLE `session_login` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ip_hash` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT NULL,
  `section` varchar(255) DEFAULT NULL,
  `logged_in_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logged_in` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=858 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of session_login
-- ----------------------------
INSERT INTO `session_login` VALUES ('857', 'f528764d624db129b32c21fbca0cb8d6', '1', 'admin', 'admin', '2012-03-01 00:55:09', '1');

-- ----------------------------
-- Table structure for `settings`
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` text,
  `type` varchar(3) DEFAULT 'str',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES ('DEF_METAKEYWORDS', 'Default Keywords', 'str');
INSERT INTO `settings` VALUES ('DEF_METADESCRIPTION', 'Default Description', 'str');
INSERT INTO `settings` VALUES ('DEF_METATITLE', 'Default Title', 'str');
INSERT INTO `settings` VALUES ('BUSINESS', 'Sample Site', 'str');
INSERT INTO `settings` VALUES ('SITE_NAME', 'Working Sample', 'str');
INSERT INTO `settings` VALUES ('OWNER_EMAIL', 'chrisd@navigatormm.com', 'str');
INSERT INTO `settings` VALUES ('ADMIN_EMAIL', 'chrisd@navigatormm.com', 'str');
INSERT INTO `settings` VALUES ('BUS_ADDRESS', '201-260 Harvey Ave,\r\nKelowna, BC', 'str');
INSERT INTO `settings` VALUES ('BUS_PHONE', '250-862-9868', 'str');
INSERT INTO `settings` VALUES ('EMAIL_CONFIRM', '', 'str');
INSERT INTO `settings` VALUES ('IMG_LOGIN_LOGO', '', 'str');
INSERT INTO `settings` VALUES ('IMG_MAX_WIDTH', '800', 'int');
INSERT INTO `settings` VALUES ('IMG_MAX_UPLOAD_SIZE', '6000', 'str');
INSERT INTO `settings` VALUES ('MAX_IFRAME_IMGS', '6', 'int');
INSERT INTO `settings` VALUES ('THM_MAX_WIDTH', '150', 'int');
INSERT INTO `settings` VALUES ('THM_MAX_HEIGHT', '150', 'int');
INSERT INTO `settings` VALUES ('ACTION_ICONS', '0', 'int');
INSERT INTO `settings` VALUES ('THM_MAX_UPLOAD_SIZE', '50', 'str');
INSERT INTO `settings` VALUES ('ORG_THM_MAX_WIDTH', '100', 'int');
INSERT INTO `settings` VALUES ('ORG_THM_MAX_HEIGHT', '100', 'int');
INSERT INTO `settings` VALUES ('NAV_LOGIN_PWD', '25cb9e48311db30734cbe3c658ddcd2d', 'str');
INSERT INTO `settings` VALUES ('CKE_CSS_COLORS', 'FFFFFF,666666,000000', 'upd');
INSERT INTO `settings` VALUES ('THEME', 'default', 'str');
INSERT INTO `settings` VALUES ('BUS_FAX', '250-862-9860', 'str');
INSERT INTO `settings` VALUES ('EMAIL_NOTIFY', null, 'str');
INSERT INTO `settings` VALUES ('IMG_MAX_HEIGHT', '600', 'int');
INSERT INTO `settings` VALUES ('THEMES_ENABLED', '1', 'int');
INSERT INTO `settings` VALUES ('IMG_UPLOAD_FOLDER', 'images/', 'str');
INSERT INTO `settings` VALUES ('THM_UPLOAD_FOLDER', 'thumbs/', 'str');
INSERT INTO `settings` VALUES ('TIMEZONE', 'America/Vancouver', 'str');
INSERT INTO `settings` VALUES ('THM_MED_MAX_WIDTH', '200', 'str');
INSERT INTO `settings` VALUES ('THM_MED_MAX_HEIGHT', '200', 'str');
INSERT INTO `settings` VALUES ('ALLOWANCES', '{\"view_pages_list\":[1,1,1,1,1,1],\"edit_page\":[1,1,1,1,0,0],\"add_page\":[1,1,1,0,0,0],\"delete_page\":[1,1,1,0,0,0],\"rename_page\":[1,1,1,0,0,0],\"view_page\":[1,1,1,1,1,1],\"publish_page\":[1,1,1,0,0,0],\"activate_page\":[1,1,1,0,0,0],\"clone_page\":[1,1,1,0,0,0],\"edit_page_meta\":[1,1,0,0,0,0],\"view_users\":[1,1,1,1,1,1],\"create_user\":[1,1,0,0,0,0],\"create_lower_user\":[1,1,1,1,0,0],\"edit_user\":[1,1,0,0,0,0],\"edit_profile\":[1,1,1,1,1,1],\"delete_user\":[1,1,0,0,0,0],\"delete_lower_user\":[1,1,1,1,0,0],\"activate_user\":[1,1,0,0,0,0],\"activate_lower_user\":[1,1,1,1,0,0],\"ban_user\":[1,1,0,0,0,0],\"ban_lower_user\":[1,1,1,1,0,0],\"view_themes\":[1,1,1,1,0,0],\"install_theme\":[1,1,0,0,0,0],\"edit_theme\":[1,1,1,0,0,0],\"delete_theme\":[1,1,0,0,0,0],\"activate_theme\":[1,1,0,0,0,0],\"view_menu_settings\":[1,1,0,0,0,0],\"edit_menu_settings\":[1,0,0,0,0,0],\"view_plugins\":[1,1,1,1,0,0],\"install_plugins\":[1,0,0,0,0,0],\"update_plugins\":[1,1,1,0,0,0],\"repair_plugins\":[1,0,0,0,0,0],\"delete_plugins\":[1,0,0,0,0,0],\"activate_plugins\":[1,1,0,0,0,0],\"activate_frameworks\":[1,1,0,0,0,0],\"view_media_settings\":[1,1,1,1,0,0],\"edit_media_settings\":[1,1,1,0,0,0],\"view_general_settings\":[1,1,1,1,0,0],\"edit_general_settings\":[1,1,1,0,0,0],\"manage_database\":[1,0,0,0,0,0],\"view_advanced_settings\":[1,0,0,0,0,0],\"edit_advanced_settings\":[1,0,0,0,0,0],\"upload_files\":[1,1,1,1,1,1],\"view_list\":[1,1,1,1,1,1],\"edit_record\":[1,1,1,1,0,0],\"add_record\":[1,1,1,0,0,0],\"delete_record\":[1,1,1,0,0,0],\"rename_record\":[1,1,1,0,0,0],\"view_record\":[1,1,1,1,1,1],\"publish_record\":[1,1,1,0,0,0],\"activate_record\":[1,1,1,0,0,0],\"clone_record\":[1,1,1,0,0,0],\"organize_records\":[1,1,1,1,0,0],\"export_records\":[1,1,1,0,0,0],\"send_emails\":[1,1,1,0,0,0],\"manage_urls\":[1,0,0,0,0,0],\"manage_robots\":[1,1,0,0,0,0],\"manage_debugger\":[1,0,0,0,0,0],\"install_website_theme\":[1,1,0,0,0,0],\"install_admin_theme\":[1,0,0,0,0,0],\"edit_website_theme\":[1,1,1,0,0,0],\"edit_admin_theme\":[1,0,0,0,0,0],\"delete_website_theme\":[1,1,0,0,0,0],\"delete_admin_theme\":[1,0,0,0,0,0],\"activate_website_theme\":[1,1,0,0,0,0],\"activate_admin_theme\":[1,0,0,0,0,0],\"manage_visibility\":[1,1,0,0,0,0],\"view_locked_pages\":[1,0,0,0,0,0],\"view_locked_menus\":[1,0,0,0,0,0],\"edit_website_menus\":[1,0,0,0,0,0],\"edit_admin_menus\":[1,0,0,0,0,0],\"manage_aliases\":[1,0,0,0,0,0]}', 'str');
INSERT INTO `settings` VALUES ('PHP_DATE_FORMAT', 'Y-m-d', 'str');
INSERT INTO `settings` VALUES ('ERROR_SENSITIVITY', '30719', 'str');
INSERT INTO `settings` VALUES ('SITEOFFLINE_MSG', 'This site is down for maintenance. Please check back again soon.', 'str');
INSERT INTO `settings` VALUES ('DB_TABLE_PREFIX', 'data_', 'str');
INSERT INTO `settings` VALUES ('SITEOFFLINE', '0', 'str');
INSERT INTO `settings` VALUES ('ALLOW_DEBUGGING', '1', 'str');
INSERT INTO `settings` VALUES ('ERROR_LOG_TYPE', '0', 'str');
INSERT INTO `settings` VALUES ('ERROR_LOG_TO_EMAIL', '', 'str');
INSERT INTO `settings` VALUES ('ERROR_LOG_TO_FILE', '', 'str');

-- ----------------------------
-- Table structure for `shrinkurl`
-- ----------------------------
DROP TABLE IF EXISTS `shrinkurl`;
CREATE TABLE `shrinkurl` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `surl` varchar(10) NOT NULL,
  `key` varchar(10) NOT NULL,
  `hits` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of shrinkurl
-- ----------------------------
INSERT INTO `shrinkurl` VALUES ('18', 'http://chrisd.nav/whatsnew/165', 'myxR5', '', '0');
INSERT INTO `shrinkurl` VALUES ('19', 'http://www.chrisd.nav/', 'myxRJ', '', '2');
INSERT INTO `shrinkurl` VALUES ('20', 'http://chrisd.nav/whatsnew/166', 'myxSH', '', '0');
INSERT INTO `shrinkurl` VALUES ('21', 'http://chrisd.nav/', 'mAk1z', '', '1');

-- ----------------------------
-- Table structure for `weather`
-- ----------------------------
DROP TABLE IF EXISTS `weather`;
CREATE TABLE `weather` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `word1` varchar(20) DEFAULT NULL,
  `word2` varchar(20) DEFAULT NULL,
  `word3` varchar(20) DEFAULT NULL,
  `word4` varchar(20) DEFAULT NULL,
  `img_num` smallint(3) DEFAULT NULL,
  `wordcount` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of weather
-- ----------------------------
INSERT INTO `weather` VALUES ('1', 'blizzard', null, null, null, '43', '1');
INSERT INTO `weather` VALUES ('2', 'clear', null, null, null, '32', '1');
INSERT INTO `weather` VALUES ('3', 'cloud', 'few', null, null, '30', '2');
INSERT INTO `weather` VALUES ('4', 'cloud', 'increas', null, null, '28', '2');
INSERT INTO `weather` VALUES ('5', 'cloud', 'mostly', null, null, '26', '2');
INSERT INTO `weather` VALUES ('6', 'cloud', 'partly', null, null, '28', '2');
INSERT INTO `weather` VALUES ('7', 'cloud', null, null, null, '26', '1');
INSERT INTO `weather` VALUES ('8', 'drizzle', null, null, null, '8', '1');
INSERT INTO `weather` VALUES ('9', 'flurries', 'chance', null, null, '13', '2');
INSERT INTO `weather` VALUES ('10', 'flurries', null, null, null, '16', '1');
INSERT INTO `weather` VALUES ('11', 'fog', null, null, null, '19', '1');
INSERT INTO `weather` VALUES ('12', 'hail', null, null, null, '25', '1');
INSERT INTO `weather` VALUES ('13', 'haz', null, null, null, '34', '1');
INSERT INTO `weather` VALUES ('14', 'overcast', null, null, null, '22', '1');
INSERT INTO `weather` VALUES ('15', 'rain', 'light', null, null, '9', '2');
INSERT INTO `weather` VALUES ('16', 'rain', null, null, null, '11', '1');
INSERT INTO `weather` VALUES ('17', 'rain', 'heavy', null, null, '12', '2');
INSERT INTO `weather` VALUES ('18', 'shower', 'few', null, null, '11', '2');
INSERT INTO `weather` VALUES ('19', 'shower', 'flurries', 'chance', null, '5', '3');
INSERT INTO `weather` VALUES ('20', 'shower', 'flurries', null, null, '5', '2');
INSERT INTO `weather` VALUES ('21', 'showers', 'chance', null, null, '39', '2');
INSERT INTO `weather` VALUES ('22', 'sleat', null, null, null, '7', '1');
INSERT INTO `weather` VALUES ('23', 'snow', 'heavy', null, null, '15', '2');
INSERT INTO `weather` VALUES ('24', 'sun', 'cloud', 'mix', null, '30', '3');
INSERT INTO `weather` VALUES ('25', 'sun', 'cloud', 'period', null, '30', '3');
INSERT INTO `weather` VALUES ('26', 'sun', 'cloud', null, null, '28', '2');
INSERT INTO `weather` VALUES ('27', 'sun', 'mainly', null, null, '34', '2');
INSERT INTO `weather` VALUES ('28', 'sun', 'showers', null, null, '39', '2');
INSERT INTO `weather` VALUES ('29', 'sun', null, null, null, '32', '1');
INSERT INTO `weather` VALUES ('30', 'thundershower', null, null, null, '0', '1');
INSERT INTO `weather` VALUES ('31', 'tornado', null, null, null, '2', '1');
INSERT INTO `weather` VALUES ('32', 'wind', 'chance', null, null, '23', '2');
INSERT INTO `weather` VALUES ('33', 'wind', 'rain', null, null, '2', '2');
INSERT INTO `weather` VALUES ('34', 'wind', null, null, null, '23', '1');
INSERT INTO `weather` VALUES ('35', 'wind', 'snow', null, null, '43', '2');
INSERT INTO `weather` VALUES ('36', 'heat', null, null, null, '36', '1');
INSERT INTO `weather` VALUES ('37', 'full moon', null, null, null, '31', '1');
