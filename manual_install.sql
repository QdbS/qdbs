# phpMyAdmin SQL Dump
# version 2.5.2
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Mar 23, 2004 at 03:30 PM
# Server version: 4.0.15
# PHP Version: 4.2.3
# 
# Database : `qdbs`
# 

# --------------------------------------------------------

#
# Table structure for table `admins`
#
# Creation: Sep 21, 2003 at 08:25 PM
# Last update: Nov 27, 2003 at 10:59 PM
#

CREATE TABLE `admins` (
  `username` varchar(16) NOT NULL default '',
  `password` text NOT NULL,
  `level` char(1) NOT NULL default '1',
  `ip` text NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `queue`
#
# Creation: Sep 12, 2003 at 10:56 PM
# Last update: Jan 05, 2004 at 04:25 PM
#

CREATE TABLE `queue` (
  `id` int(11) NOT NULL auto_increment,
  `quote` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `quotes`
#
# Creation: Sep 12, 2003 at 10:56 PM
# Last update: Mar 13, 2004 at 03:59 AM
#

CREATE TABLE `quotes` (
  `id` int(11) NOT NULL auto_increment,
  `quote` longtext NOT NULL,
  `rating` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `settings`
#
# Creation: Sep 21, 2003 at 12:15 AM
# Last update: Sep 21, 2003 at 03:55 PM
#

CREATE TABLE `settings` (
  `template` text NOT NULL,
  `heading` varchar(80) NOT NULL default '',
  `qlimit` int(11) NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `style` text NOT NULL
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `votes`
#
# Creation: Sep 13, 2003 at 11:08 PM
# Last update: Mar 13, 2004 at 03:59 AM
#

CREATE TABLE `votes` (
  `id` int(11) NOT NULL default '0',
  `ip` text NOT NULL
) TYPE=MyISAM;
