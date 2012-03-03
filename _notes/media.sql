/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50141
Source Host           : localhost:3306
Source Database       : chrisd

Target Server Type    : MYSQL
Target Server Version : 50141
File Encoding         : 65001

Date: 2012-02-22 01:33:03
*/

SET FOREIGN_KEY_CHECKS=0;
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
