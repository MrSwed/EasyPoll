###################################################################################
# SQL Commands to create poll manager tables
#
# Use the setup in the MODx Manager to create them!
# If you're reading this because the automatic setup failed, please run these
# commands on your MySQL DB. Use phpMyAdmin or similar for this.
#
# ATTENTION: If you do run these commands manually, make sure to RENAME all table
# names and add the modx table prefix to them (per default this is modx_)!
# Example: Replace all occurences of ep_poll with modx_ep_poll. The easiest thing
# to do is a search-replace. Replace "ep_" with "<your table prefix>ep_"
#
# Version 1.1
# by banal
###################################################################################

CREATE TABLE IF NOT EXISTS `ep_poll` (
  `idPoll` int(10) unsigned NOT NULL auto_increment,
  `Title` varchar(128) NOT NULL,
  `isActive` tinyint(1) unsigned NOT NULL default '0',
  `StartDate` datetime default NULL,
  `EndDate` datetime default NULL,
  PRIMARY KEY  (`idPoll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ep_choice` (
  `idChoice` int(10) unsigned NOT NULL auto_increment,
  `idPoll` int(10) unsigned NOT NULL,
  `Title` varchar(128) NOT NULL,
  `Sorting` tinyint(3) unsigned NOT NULL,
  `Votes` int(11) NOT NULL,
  PRIMARY KEY  (`idChoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ep_language` (
  `idLang` int(10) unsigned NOT NULL auto_increment,
  `LangShort` char(3) NOT NULL,
  `LangName` varchar(256) NOT NULL,
  PRIMARY KEY  (`idLang`),
  UNIQUE KEY `uniqLang` (`LangShort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ep_translation` (
  `idPoll` int(10) unsigned NOT NULL,
  `idChoice` int(10) unsigned NOT NULL default '0',
  `idLang` int(10) unsigned NOT NULL,
  `TextValue` varchar(2048) NOT NULL,
  PRIMARY KEY  USING BTREE (`idChoice`,`idLang`,`idPoll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ep_userip` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `idPoll` int(10) unsigned NOT NULL,
  `ipAddress` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;