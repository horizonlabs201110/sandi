-- phpMyAdmin SQL Dump
-- version 4.0.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 09, 2013 at 09:12 AM
-- Server version: 5.5.15
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sandi`
--
CREATE DATABASE IF NOT EXISTS `sandi` DEFAULT CHARACTER SET gbk COLLATE gbk_chinese_ci;
USE `sandi`;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL DEFAULT '',
  `name` char(32) NOT NULL DEFAULT '',
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- Table structure for table `t_category`
--

CREATE TABLE IF NOT EXISTS `t_category` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `category_name` varchar(50) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) unsigned NOT NULL COMMENT '父分类ID',
  `visibility` tinyint(1) NOT NULL DEFAULT '1' COMMENT '首页是否显示 1显示 0 不显示',
  `descript` varchar(255) DEFAULT NULL COMMENT '描述',
  `title` varchar(255) DEFAULT NULL COMMENT '标题 title',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模型分类表' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `t_category`
--

INSERT INTO `t_category` (`category_id`, `category_name`, `parent_id`, `visibility`, `descript`, `title`) VALUES
(1, '工艺', 0, 1, '工艺品', '工艺品'),
(2, '美术', 0, 1, '美术品', '美术品'),
(3, '0', 0, 1, 'ee', 'ee');

-- --------------------------------------------------------

--
-- Table structure for table `t_grant`
--

CREATE TABLE IF NOT EXISTS `t_grant` (
  `grand_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) unsigned NOT NULL,
  `grant_bitmap` tinyint(1) NOT NULL COMMENT '1 - 1- 1 - 1(downlaod-print-purchase-browse)',
  PRIMARY KEY (`grand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk AUTO_INCREMENT=38 ;


-- --------------------------------------------------------

--
-- Table structure for table `t_model`
--

CREATE TABLE IF NOT EXISTS `t_model` (
  `model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `designer_id` int(10) unsigned NOT NULL,
  `owner_id` int(11) NOT NULL,
  `profile` longtext COMMENT 'model描述信息， 用Xml方式存储',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`model_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk AUTO_INCREMENT=119 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_category_mapping`
--

CREATE TABLE IF NOT EXISTS `t_model_category_mapping` (
  `model_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `model_id` (`model_id`),
  KEY `categor_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;


-- --------------------------------------------------------

--
-- Table structure for table `t_model_download`
--

CREATE TABLE IF NOT EXISTS `t_model_download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `download_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_flag`
--

CREATE TABLE IF NOT EXISTS `t_model_flag` (
  `flag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_name` varchar(32) NOT NULL,
  PRIMARY KEY (`flag_id`),
  KEY `flag_name` (`flag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_flag_mapping`
--

CREATE TABLE IF NOT EXISTS `t_model_flag_mapping` (
  `model_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  KEY `model_id` (`model_id`),
  KEY `flag_id` (`flag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;


-- --------------------------------------------------------

--
-- Table structure for table `t_model_log`
--

CREATE TABLE IF NOT EXISTS `t_model_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `model_id` int(11) NOT NULL,
  `action` tinyint(4) NOT NULL COMMENT 'download4, print2, view1',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_log_rating`
--

CREATE TABLE IF NOT EXISTS `t_model_log_rating` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `marking` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_tag`
--

CREATE TABLE IF NOT EXISTS `t_model_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(32) NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_model_tag_mapping`
--

CREATE TABLE IF NOT EXISTS `t_model_tag_mapping` (
  `model_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  KEY `model_id` (`model_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- Table structure for table `t_offer`
--

CREATE TABLE IF NOT EXISTS `t_offer` (
  `offer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `contents` longtext NOT NULL COMMENT 'XML format',
  `grant_bitmap` tinyint(1) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`offer_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk AUTO_INCREMENT=39 ;


-- --------------------------------------------------------

--
-- Table structure for table `t_purchase`
--

CREATE TABLE IF NOT EXISTS `t_purchase` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `offer_id` int(11) NOT NULL,
  `purchase_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `offer_id` (`offer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_session`
--

CREATE TABLE IF NOT EXISTS `t_session` (
  `session_id` int(11) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `session_data` longtext NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- --------------------------------------------------------

--
-- Table structure for table `t_user`
--

CREATE TABLE IF NOT EXISTS `t_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL COMMENT '别名',
  `password` varchar(32) NOT NULL,
  `user_account` varchar(64) NOT NULL COMMENT '用email地址作帐号',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT NULL,
  `avatar` blob COMMENT '头像',
  `profile` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `UserAccount` (`user_account`),
  KEY `alias` (`alias`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk COMMENT='用户表' AUTO_INCREMENT=41 ;


-- --------------------------------------------------------

--
-- Table structure for table `t_user_detail`
--

CREATE TABLE IF NOT EXISTS `t_user_detail` (
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `true_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `telephone` varchar(50) DEFAULT NULL COMMENT '联系电话',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机',
  `area` varchar(255) DEFAULT NULL COMMENT '地区',
  `contact_addr` varchar(250) DEFAULT NULL COMMENT '联系地址',
  `qq` varchar(15) DEFAULT NULL COMMENT 'QQ',
  `msn` varchar(250) DEFAULT NULL COMMENT 'MSN',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `zip` varchar(10) DEFAULT NULL COMMENT '邮政编码',
  `register_time` datetime NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户信息表';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
