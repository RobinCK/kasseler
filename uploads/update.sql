ALTER TABLE kasseler_users ADD user_skype varchar(25) DEFAULT '' NOT NULL;
ALTER TABLE kasseler_users ADD user_gtalk varchar(50) DEFAULT '' NOT NULL;
ALTER TABLE kasseler_users MODIFY user_adm_modules text DEFAULT NULL;
ALTER TABLE kasseler_forum_posts ADD post_edit_user varchar(50) DEFAULT '' NOT NULL;
ALTER TABLE kasseler_users ADD user_new_pm_count int(2) DEFAULT '0' NOT NULL;
ALTER TABLE kasseler_users ADD user_new_pm_window int(1) DEFAULT '1' NOT NULL;

CREATE TABLE `kasseler_static` (
  `id` int(11) NOT NULL auto_increment,
  `static_id` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `content` longtext,
  `template` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `kasseler_albom` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) default NULL,
  `cid` int(1) NOT NULL default '0',
  `description` text,
  `image` varchar(100) default NULL,
  `time` varchar(25) default NULL,
  `rating` float(5,2) NOT NULL default '0.00',
  `voted` int(11) NOT NULL default '0',
  `comment` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `kasseler_forum_reports` (
  `id` int(11) NOT NULL auto_increment,
  `post_id` int(11) default '0',
  `user_id` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
