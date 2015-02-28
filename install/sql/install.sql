CREATE TABLE `{PREFIX}_acc` (
  uid INT(11) NOT NULL DEFAULT 0,
  id INT(11) NOT NULL DEFAULT 0
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};


CREATE TABLE `{PREFIX}_albom` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) DEFAULT NULL,
  cid INT(1) NOT NULL DEFAULT 0,
  description TEXT DEFAULT NULL,
  image VARCHAR(100) DEFAULT NULL,
  `time` VARCHAR(25) DEFAULT NULL,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_attach` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  module VARCHAR(50) DEFAULT NULL,
  path VARCHAR(255) DEFAULT NULL,
  file VARCHAR(100) DEFAULT NULL,
  downloads INT(11) NOT NULL DEFAULT 0,
  user_id INT(11) NOT NULL DEFAULT 0,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_audio` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) DEFAULT NULL,
  name VARCHAR(100) DEFAULT NULL,
  performers VARCHAR(255) DEFAULT NULL,
  author VARCHAR(50) DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  cid VARCHAR(255) DEFAULT NULL,
  file VARCHAR(150) DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  show_comment INT(1) NOT NULL DEFAULT 1,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  tags VARCHAR(100) NOT NULL DEFAULT '',
  downloads INT(11) NOT NULL DEFAULT 0,
  playing INT(11) NOT NULL DEFAULT 0,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  filesize INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_audio_authors` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  author VARCHAR(255) DEFAULT NULL,
  biography TEXT DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_blocks` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(50) NOT NULL,
  position CHAR(1) NOT NULL DEFAULT 'l',
  view INT(1) NOT NULL DEFAULT 1,
  active INT(1) NOT NULL DEFAULT 1,
  blockfile VARCHAR(255) NOT NULL DEFAULT '',
  modules TEXT DEFAULT NULL,
  weight INT(11) NOT NULL DEFAULT 0,
  content TEXT DEFAULT NULL,
  language VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX title (title)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_calendar` (
  cid INT(11) NOT NULL AUTO_INCREMENT,
  id INT(11) NOT NULL DEFAULT 0,
  module VARCHAR(50) NOT NULL DEFAULT '',
  `date` DATE NOT NULL DEFAULT '0000-00-00',
  status INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (cid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_categories` (
  cid INT(11) NOT NULL AUTO_INCREMENT,
  cat_id VARCHAR(50) DEFAULT NULL,
  title VARCHAR(100) NOT NULL DEFAULT '',
  module VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT DEFAULT NULL,
  image VARCHAR(255) NOT NULL DEFAULT '',
  tree VARCHAR(60) NOT NULL,
  PRIMARY KEY (cid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_comment` (
  cid INT(11) NOT NULL AUTO_INCREMENT,
  modul VARCHAR(60) NOT NULL DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  name VARCHAR(60) NOT NULL DEFAULT '',
  ip VARCHAR(16) NOT NULL DEFAULT '0.0.0.0',
  `comment` TEXT NOT NULL,
  parentid INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (cid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_faq` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  status INT(1) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_favorite` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  post INT(11) NOT NULL,
  users VARCHAR(50) NOT NULL DEFAULT '',
  modul VARCHAR(50) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  INDEX module_user_post (modul, users, post)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_files` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  files_id VARCHAR(100) DEFAULT NULL,
  title VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  content TEXT DEFAULT NULL,
  author VARCHAR(25) DEFAULT NULL,
  email VARCHAR(50) DEFAULT NULL,
  url VARCHAR(255) NOT NULL DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  filesize VARCHAR(15) NOT NULL DEFAULT '',
  version VARCHAR(15) NOT NULL DEFAULT '',
  homepage VARCHAR(100) NOT NULL DEFAULT '',
  hits INT(11) NOT NULL DEFAULT 0,
  view INT(11) DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  language VARCHAR(25) DEFAULT '',
  show_comment INT(1) NOT NULL DEFAULT 1,
  show_group VARCHAR(255) DEFAULT '',
  status INT(1) NOT NULL DEFAULT 0,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  tags VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_acc` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ugid INT(11) NOT NULL DEFAULT 0,
  thisuser CHAR(1) DEFAULT 'u',
  typeacc CHAR(1) NOT NULL DEFAULT 'c',
  idv INT(11) NOT NULL DEFAULT 0,
  acc_view INT(11) DEFAULT 0,
  acc_read INT(11) DEFAULT 0,
  acc_write INT(11) DEFAULT 0,
  acc_post INT(11) DEFAULT 0,
  acc_edit INT(11) DEFAULT 0,
  acc_delete INT(11) DEFAULT 0,
  acc_upload INT(11) DEFAULT 0,
  acc_download INT(11) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE INDEX UK_{PREFIX}_forum_acc (thisuser, ugid, typeacc, idv)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_categories` (
  cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  cat_title VARCHAR(100) DEFAULT NULL,
  cat_sort INT(8) NOT NULL DEFAULT 0,
  tree VARCHAR(200) NOT NULL DEFAULT '00',
  description VARCHAR(255) DEFAULT NULL,
  invisible CHAR(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (cat_id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_forums` (
  forum_id INT(5) NOT NULL AUTO_INCREMENT,
  cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  forum_name VARCHAR(150) DEFAULT NULL,
  forum_desc TEXT DEFAULT NULL,
  forum_status TINYINT(4) NOT NULL DEFAULT 0,
  forum_posts MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  forum_topics MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  forum_last_post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  acc_view VARCHAR(255) DEFAULT NULL,
  acc_write VARCHAR(255) DEFAULT NULL,
  acc_post VARCHAR(255) DEFAULT NULL,
  acc_edit VARCHAR(255) DEFAULT NULL,
  acc_delete VARCHAR(255) DEFAULT NULL,
  pos INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (forum_id),
  INDEX cat_id (cat_id),
  INDEX forum_last_post_id (forum_last_post_id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_posts` (
  post_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  topic_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  forum_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  poster_id MEDIUMINT(8) NOT NULL DEFAULT 0,
  post_time INT(11) NOT NULL DEFAULT 0,
  poster_ip VARCHAR(15) DEFAULT NULL,
  post_edit_time INT(11) DEFAULT NULL,
  post_subject VARCHAR(150) DEFAULT NULL,
  post_text TEXT DEFAULT NULL,
  poster_name VARCHAR(50) DEFAULT NULL,
  ico VARCHAR(50) DEFAULT NULL,
  post_tnx TEXT DEFAULT NULL,
  post_edit_user VARCHAR(50) NOT NULL DEFAULT '',
  PRIMARY KEY (post_id),
  INDEX forum_id (forum_id),
  INDEX post_time (post_time),
  INDEX poster_id (poster_id),
  INDEX topic_id (topic_id),
  INDEX posts_poster_name (poster_name)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_reports` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  post_id INT(11) DEFAULT 0,
  user_id INT(11) DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_search` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(32) DEFAULT NULL,
  topic_id INT(11) NOT NULL DEFAULT 0,
  `time` VARCHAR(16) DEFAULT NULL,
  keywords VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_search_keys` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(32) DEFAULT NULL,
  query TEXT DEFAULT NULL,
  `ignore` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_topics` (
  topic_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  forum_id SMALLINT(8) UNSIGNED NOT NULL DEFAULT 0,
  topic_title VARCHAR(60) NOT NULL DEFAULT '',
  topic_desc VARCHAR(255) NOT NULL DEFAULT '',
  topic_poster MEDIUMINT(8) NOT NULL DEFAULT 0,
  topic_time INT(11) NOT NULL DEFAULT 0,
  topic_views MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  topic_replies MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  topic_status TINYINT(3) NOT NULL DEFAULT 0,
  topic_type INT(1) DEFAULT 0,
  topic_first_post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  topic_last_post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  topic_poster_name VARCHAR(50) NOT NULL DEFAULT '',
  ico VARCHAR(20) DEFAULT NULL,
  topic_first_post_fix CHAR(1) DEFAULT 'n',
  PRIMARY KEY (topic_id),
  INDEX forum_id (forum_id),
  INDEX topic_status (topic_status),
  INDEX topic_type (topic_type)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_groups` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL DEFAULT '',
  description TEXT DEFAULT NULL,
  special INT(1) NOT NULL DEFAULT 0,
  color VARCHAR(7) NOT NULL DEFAULT '',
  points INT(11) NOT NULL DEFAULT 0,
  img VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_internet_radio` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(20) NOT NULL DEFAULT '',
  radio_id VARCHAR(50) NOT NULL,
  description TEXT DEFAULT NULL,
  country VARCHAR(100) DEFAULT NULL,
  stream VARCHAR(255) NOT NULL DEFAULT '',
  img VARCHAR(255) NOT NULL DEFAULT '',
  show_comment INT(1) NOT NULL DEFAULT 1,
  `comment` INT(11) NOT NULL DEFAULT 0,
  status INT(1) NOT NULL DEFAULT 0,
  language VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_jokes` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  author VARCHAR(25) DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  title VARCHAR(100) DEFAULT '',
  cid VARCHAR(255) NOT NULL DEFAULT '',
  joke TEXT DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_media` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  media_id VARCHAR(100) DEFAULT NULL,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  title VARCHAR(100) NOT NULL DEFAULT '',
  subtitle VARCHAR(100) DEFAULT NULL,
  `year` VARCHAR(10) DEFAULT NULL,
  director VARCHAR(100) DEFAULT NULL,
  roles TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  createdby VARCHAR(100) DEFAULT NULL,
  duration VARCHAR(100) DEFAULT NULL,
  format VARCHAR(20) DEFAULT NULL,
  quality VARCHAR(20) DEFAULT NULL,
  size VARCHAR(20) DEFAULT NULL,
  lang VARCHAR(20) DEFAULT NULL,
  author VARCHAR(25) DEFAULT NULL,
  placed VARCHAR(100) DEFAULT NULL,
  links TEXT DEFAULT NULL,
  img VARCHAR(100) DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  view INT(11) NOT NULL DEFAULT 0,
  status INT(1) NOT NULL DEFAULT 0,
  show_comment INT(1) NOT NULL DEFAULT 1,
  note TEXT DEFAULT NULL,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT NULL,
  show_group INT(1) NOT NULL DEFAULT 1,
  tags VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  INDEX cid (cid),
  INDEX title (title)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_menu` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(50) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  groups VARCHAR(100) DEFAULT NULL,
  class VARCHAR(15) DEFAULT NULL,
  pos INT(3) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_message` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(50) DEFAULT NULL,
  content TEXT DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  groups VARCHAR(255) NOT NULL DEFAULT '',
  pos INT(3) NOT NULL DEFAULT 0,
  tpl VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_modules` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(50) DEFAULT NULL,
  module VARCHAR(50) NOT NULL,
  active INT(1) NOT NULL DEFAULT 0,
  view INT(1) NOT NULL DEFAULT 0,
  blocks INT(1) NOT NULL DEFAULT 0,
  groups VARCHAR(255) DEFAULT NULL,
  pos INT(3) NOT NULL DEFAULT 0,
  sitemap VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_news` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  news_id VARCHAR(255) DEFAULT NULL,
  title VARCHAR(100) DEFAULT NULL,
  `begin` TEXT DEFAULT NULL,
  content TEXT DEFAULT NULL,
  author VARCHAR(25) DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  view INT(11) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  language VARCHAR(25) DEFAULT '',
  show_comment INT(1) NOT NULL DEFAULT 1,
  show_group VARCHAR(255) NOT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  tags VARCHAR(100) NOT NULL DEFAULT '',
  fix_news VARCHAR(1) DEFAULT 'n',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_pages` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  pages_id VARCHAR(255) DEFAULT NULL,
  title VARCHAR(100) DEFAULT NULL,
  `begin` TEXT DEFAULT NULL,
  content TEXT DEFAULT NULL,
  author VARCHAR(25) DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  view INT(11) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  status INT(1) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT '',
  show_comment INT(1) NOT NULL DEFAULT 1,
  show_group VARCHAR(255) NOT NULL,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  tags VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_pm` (
  mid INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  tid INT(11) NOT NULL DEFAULT 0,
  subj VARCHAR(255) NOT NULL DEFAULT '',
  user VARCHAR(50) NOT NULL DEFAULT '',
  user_from VARCHAR(50) NOT NULL DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  pm_read INT(1) NOT NULL DEFAULT 0,
  status INT(1) NOT NULL DEFAULT 0,
  type INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (mid),
  INDEX tid (tid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_pm_text` (
  tid INT(11) NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  PRIMARY KEY (tid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_robot` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) DEFAULT '',
  visit DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  country VARCHAR(50) NOT NULL DEFAULT 'default',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_search` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(32) DEFAULT NULL,
  title VARCHAR(150) DEFAULT NULL,
  author VARCHAR(50) DEFAULT NULL,
  content TEXT DEFAULT NULL,
  `date` DATETIME NOT NULL,
  module VARCHAR(25) DEFAULT NULL,
  subid INT(11) NOT NULL DEFAULT 0,
  rewrite_id VARCHAR(50) DEFAULT NULL,
  `time` VARCHAR(16) DEFAULT NULL,
  keywords VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_search_keys` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(32) DEFAULT NULL,
  query TEXT DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_sessions` (
  sid VARCHAR(100) NOT NULL DEFAULT '',
  uname VARCHAR(25) DEFAULT NULL,
  is_admin INT(1) NOT NULL DEFAULT 0,
  ip VARCHAR(15) DEFAULT NULL,
  `time` VARCHAR(14) NOT NULL DEFAULT '0',
  module VARCHAR(20) DEFAULT NULL,
  url VARCHAR(255) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  country VARCHAR(50) NOT NULL DEFAULT 'default',
  PRIMARY KEY (sid),
  UNIQUE INDEX uname (uname)
)
ENGINE = MYISAM
AVG_ROW_LENGTH = 216
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_shop` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  shop_id VARCHAR(100) DEFAULT NULL,
  title VARCHAR(100) DEFAULT NULL,
  author VARCHAR(25) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  content TEXT DEFAULT NULL,
  pay FLOAT(11, 2) NOT NULL DEFAULT 0.00,
  img VARCHAR(255) DEFAULT NULL,
  cid VARCHAR(255) NOT NULL DEFAULT '',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  clients INT(11) NOT NULL DEFAULT 0,
  view INT(11) NOT NULL DEFAULT 0,
  `comment` INT(11) NOT NULL DEFAULT 0,
  status INT(1) NOT NULL DEFAULT 0,
  show_comment INT(1) NOT NULL DEFAULT 1,
  language VARCHAR(25) DEFAULT '',
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  advanced TEXT DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_shop_clients` (
  sid INT(11) NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL DEFAULT '0000-00-00',
  form TEXT NOT NULL,
  pay FLOAT(11, 2) NOT NULL DEFAULT 0.00,
  `comment` TEXT DEFAULT NULL,
  unique_id VARCHAR(30) DEFAULT NULL,
  status INT(1) DEFAULT 0,
  user INT(11) DEFAULT -1,
  PRIMARY KEY (sid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_static` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  static_id VARCHAR(255) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  content LONGTEXT DEFAULT NULL,
  template VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_system` (
  namep VARCHAR(50) NOT NULL,
  valuep VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (namep)
)
ENGINE = MYISAM
AVG_ROW_LENGTH = 454
CHARACTER SET {CHARSET}
ROW_FORMAT = FIXED;

CREATE TABLE `{PREFIX}_tags` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  post INT(11) NOT NULL,
  tag VARCHAR(100) NOT NULL DEFAULT '',
  modul VARCHAR(50) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_topsites` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) DEFAULT NULL,
  link VARCHAR(50) DEFAULT NULL,
  mail VARCHAR(255) NOT NULL DEFAULT '',
  img VARCHAR(50) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  `date` DATE NOT NULL DEFAULT '0000-00-00',
  hits_out INT(11) NOT NULL DEFAULT 0,
  hits_in INT(11) NOT NULL DEFAULT 0,
  hosts LONGTEXT DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT '',
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_users` (
  uid INT(11) NOT NULL AUTO_INCREMENT,
  user_id VARCHAR(50) NOT NULL DEFAULT '',
  user_name VARCHAR(25) NOT NULL DEFAULT '',
  user_email VARCHAR(255) DEFAULT NULL,
  user_website VARCHAR(255) NOT NULL DEFAULT 'http://',
  user_avatar VARCHAR(100) NOT NULL DEFAULT 'default.png',
  user_regdate DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  user_country VARCHAR(50) NOT NULL DEFAULT 'default',
  user_level INT(1) NOT NULL DEFAULT 0,
  user_icq VARCHAR(15) DEFAULT NULL,
  user_aim VARCHAR(18) DEFAULT NULL,
  user_yim VARCHAR(25) DEFAULT NULL,
  user_msnm VARCHAR(25) DEFAULT NULL,
  user_password VARCHAR(40) NOT NULL,
  user_group INT(11) NOT NULL DEFAULT 0,
  user_groups VARCHAR(255) DEFAULT NULL,
  user_last_ip VARCHAR(15) NOT NULL DEFAULT '0.0.0.0',
  user_last_proxy VARCHAR(15) NOT NULL DEFAULT 'N/A',
  user_last_visit DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  user_last_os VARCHAR(100) NOT NULL DEFAULT 'N/A',
  user_last_browser VARCHAR(100) NOT NULL DEFAULT 'N/A',
  user_birthday DATE NOT NULL DEFAULT '0000-00-00',
  user_gender INT(1) NOT NULL DEFAULT 0,
  user_language VARCHAR(50) DEFAULT NULL,
  user_template VARCHAR(50) DEFAULT NULL,
  user_locality VARCHAR(100) DEFAULT NULL,
  user_signature TEXT DEFAULT NULL,
  user_interests VARCHAR(100) DEFAULT NULL,
  user_occupation VARCHAR(100) DEFAULT NULL,
  user_viewemail INT(1) NOT NULL DEFAULT 0,
  user_comments INT(5) NOT NULL DEFAULT 0,
  user_points INT(11) NOT NULL DEFAULT 0,
  user_posts INT(11) NOT NULL DEFAULT 0,
  user_timeout INT(11) NOT NULL DEFAULT 0,
  user_tnx INT(11) NOT NULL DEFAULT 0,
  user_baned INT(1) NOT NULL DEFAULT 0,
  user_baned_time INT(11) NOT NULL DEFAULT 0,
  user_baned_reason VARCHAR(100) DEFAULT NULL,
  user_activation INT(1) NOT NULL DEFAULT 0,
  user_activation_code VARCHAR(25) DEFAULT NULL,
  user_moderation INT(1) NOT NULL DEFAULT 1,
  user_password_update INT(11) NOT NULL DEFAULT 0,
  user_gmt INT(2) NOT NULL DEFAULT 0,
  rating FLOAT(5, 2) NOT NULL DEFAULT 0.00,
  voted INT(11) NOT NULL DEFAULT 0,
  user_pm_send INT(1) NOT NULL DEFAULT 1,
  user_adm_modules TINYTEXT DEFAULT NULL,
  user_new_pm_count INT(2) NOT NULL DEFAULT 0,
  user_new_pm_window INT(1) NOT NULL DEFAULT 1,
  user_skype VARCHAR(25) NOT NULL DEFAULT '',
  user_gtalk VARCHAR(50) NOT NULL DEFAULT '',
  user_forum_mail INT(2) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (uid),
  UNIQUE INDEX uname (user_name)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_voting` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL DEFAULT '',
  vote_case TEXT NOT NULL,
  vote_ip TEXT DEFAULT NULL,
  vote_users TEXT DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  result TEXT DEFAULT NULL,
  `comment` INT(11) NOT NULL DEFAULT 0,
  status INT(1) NOT NULL DEFAULT 0,
  language VARCHAR(25) DEFAULT NULL,
  show_comment INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

CREATE TABLE `{PREFIX}_forum_subscription` (
  uid INT(11) NOT NULL,
  topic_id INT(11) NOT NULL,
  sending CHAR(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (uid, topic_id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

INSERT INTO `{PREFIX}_blocks` VALUES 
  (1, 'Навигация', 'l', 1, 1, 'block-modules.php', '', 1, '', ''),
  (2, 'Администрация', 'l', 4, 1, 'block-monitoring.php', '', 2, '', ''),
  (3, 'Календарь', 'r', 1, 1, 'block-calendar.php', '', 2, '', ''),
  (4, 'Меню пользователя', 'r', 1, 1, 'block-user_menu.php', '', 1, '', ''),
  (5, 'Опрос', 'r', 1, 1, 'block-last_voting.php', '', 3, '', '');

INSERT INTO `{PREFIX}_groups` VALUES 
  (1, 'Администраторы', 'Администраторы сайта', 1, 'dd0000', 0, 'admin.png'),
  (2, 'Модераторы', 'Модераторы сайта', 1, '2ba94f', 0, 'moderators.png'),
  (3, 'Поисковые системы', 'Поисковые системы (боты)', 1, '000000', 0, ''),
  (4, 'Гости', 'Гости сайта', 1, '660000', 0, 'guestspng.png'),
  (5, 'Пользователи', 'Пользователи сайта', 0, '0066cc', 5, 'users.png');

INSERT INTO `{PREFIX}_modules` VALUES 
  (1, 'Профиль', 'account', 1, 1, 1, '', 1, ''),
  (2, 'Форум', 'forum', 1, 1, 3, '', 2, ''),
  (3, 'Новости', 'news', 1, 1, 0, '', 3, ''),
  (4, 'Статьи', 'pages', 1, 1, 0, '', 4, ''),
  (5, 'Файлы', 'files', 1, 1, 0, '', 5, ''),
  (6, 'Media', 'media', 1, 1, 0, '', 6, ''),
  (7, 'Анекдоты', 'jokes', 1, 1, 0, '', 7, ''),
  (8, 'Магазин', 'shop', 1, 1, 0, '', 9, ''),
  (9, 'Поиск', 'search', 1, 1, 0, '', 10, ''),
  (10, 'FAQ', 'faq', 1, 1, 0, '', 11, ''),
  (11, 'Опросы', 'voting', 1, 1, 0, '', 12, ''),
  (12, 'Аудио', 'audio', 1, 1, 0, '', 13, ''),
  (18, 'Альбом', 'albom', 1, 1, 3, '', 14, ''),
  (13, 'Интернет радио', 'radio', 1, 1, 0, '', 15, ''),
  (14, 'Топ сайтов', 'top_site', 1, 1, 1, '', 16, ''),
  (15, 'Топ пользователей', 'top_users', 1, 1, 1, '', 17, ''),
  (16, 'Рекомендовать', 'recommend', 1, 1, 0, '', 18, ''),
  (17, 'Обратная связь', 'contact', 1, 1, 0, '', 19, ''),
  (19, 'Статические страницы', 'static', 1, 1, 0, '', 8, '');


INSERT INTO `{PREFIX}_news` VALUES 
  (1, 'centroarts', 'Основной партнер в создании уникального стиля', '<div align=''center''><img src=''uploads/images/centroarts.jpg'' border=''0'' alt=''uploads/centroarts.png'' title=''uploads/centroarts.png'' /></div><br />\r\nСтудия Centroarts - основной партнер в создании уникального стиля нашего сайта. Рекомендуем Вам Centroarts для долгосрочных партнерских отношений с целью создания уникальных шаблонов, проектирования сайтов, отрисовки уникальных иконок. Все, что нужно для создания уникального стиля Вам поможет разработать студия Centroarts.', '<div align=''center''><img src=''uploads/images/centroarts.jpg'' border=''0'' alt=''uploads/centroarts.png'' title=''uploads/centroarts.png'' /></div><br />\r\nСтудия Centroarts - основной партнер в создании уникального стиля нашего сайта. Рекомендуем Вам Centroarts для долгосрочных партнерских отношений с целью создания уникальных шаблонов, проектирования сайтов, отрисовки уникальных иконок. Все, что нужно для создания уникального стиля Вам поможет разработать студия Centroarts.<br />\r\n<br />\r\nПосетить сайт', '{USER}', '{DATETIME}', 6, 0, '', '', 0, '', 1, 0.00, 0, '', 'n'),
  (2, 'netlevel', 'NetLevel - надёжный и качественный хостинг', '<div align=''center''><img src=''uploads/images/netlevel_logo.png'' align=''middle'' alt=''NetLevel'' title=''NetLevel'' /></div><br />\r\nNetLevel.ru является техническим партнёром системы управления сайтами Kasseler CMS. При создании сайта, одним из самых важных моментов является обеспечение его стабильной, быстрой и безопасной работы в сети Интернет. Основными отличительными чертами NetLevel является:', '<div align=''center''><img src=''uploads/images/netlevel_logo.png'' align=''middle'' alt=''NetLevel'' title=''NetLevel'' /></div><br />\r\nNetLevel.ru является техническим партнёром системы управления сайтами Kasseler CMS. При создании сайта, одним из самых важных моментов является обеспечение его стабильной, быстрой и безопасной работы в сети Интернет. Основными отличительными чертами NetLevel является:<br />\r\n<br />\r\n  * Полная совместимость с Kasseler CMS и бесплтаня установка<br />\r\n  * Скидки и специальные акции связанные с Kasseler CMS<br />\r\n  * Высокая стабильность, скорость и безопасность<br />\r\n  * Техническая поддержка 24/7/365<br />\r\n  * Широкий спектр услуг<br />\r\n<br />\r\n<br />\r\nВ продолжении подробная информация о услугах и ссылки.<br />\r\n<br />\r\n<b>Услуги</b>:<br />\r\n<br />\r\n<i><b>1. Виртуальный хостинг и домены</b></i><br />\r\nУслуга предусматривает размещение сайта, также возможна регистрация домена в одной из поддерживаемых нами зон. Мы предоставляем услуги виртуального хостинга на мощных серверах в лучших датацентрах мира с использованием быстрого вебсервера nginx, панели управления CPanel и поддержкой всех современных технологий используемых в CMS-системах.<br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Подробнее о виртуальном хостинге</a><br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Подробнее о регистрации доменов</a><br />\r\n<br />\r\n<br />\r\n<i><b>2. Выделенные и виртуальные серверы</b></i><br />\r\nВиртуальные (VPS) и выделенные серверы - идеальное решение для размещения сайта, которое предусматривает выделение гарантированных ресурсов и базовое администрирование. Таким образом Вы можете разместить большое количество сайтов, создавать аккаунты для своих клиентов или друзей и иметь полный root-доступ к своему серверу для установки любого ПО и изменения любых параметров ОС. <br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Подробнее о виртуальных серверах (VPS/VDS)</a><br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Подробнее о выделенных серверах</a><br />\r\n<br />\r\n<i><b>3. Администрирование и мониторинг</b></i><br />\r\nВыполняются любые операции связанные с мониторингом, установкой дополнительного ПО, решением проблем. Доступно постоянное и разовое администрирование. Разовое администрирование включает одноразовое выполнение технических работ с сервером и предоставление отчёта. Например - установка и конфигурирование программной системы защиты от DDoS атак, повышение скорости работы сервера, анализ и увеличение уровня безопасности и т.п. Постоянное (периодическое) администрирование предусматривает выполнение работ по графику а также мониторинг состояния сервера и решение проблем в случае необходимости. Например - периодическое обновление компонентов ОС и установка важных дополнений и патчей безопасности, мониторинг состояния служб и т.п. Ознакомьтесь подробнее с каждым вариантом администрирования и ценами нажав соответствующую ссылку ниже.<br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Постоянное администрирование и мониторинг</a><br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Разовое администрирование</a><br />\r\n<br />\r\nБолее подробно ознакомиться с предоставляемыми нами услугами можно на нашем сайте, там же можно связаться с нами и задать все интересующие Вас вопросы.<br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fbilling.netlevel.ru%2Faff.php%3Faff%3D038'' target=''_blank'' title=''Ссылка открывается в новом окне''>Хостинг</a>', '{USER}', '{DATETIME}', 0, 0, '', '', 0, '', 1, 0.00, 0, '', 'n'),
  (3, 'REG.RU', 'Официальный партнер регистрации доменов REG.RU', '<div align=''center''><a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fwww.reg.ru%2Fnewdomain%2Findex%3Frid%3D82987'' target=''_blank'' title=''Ссылка открывается в новом окне''><img src=''/uploads/images/regru.gif'' align=''middle'' alt=''REG.RU'' title=''REG.RU'' /></a></div><br />\r\nПреимущества <a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fwww.reg.ru%2Fnewdomain%2Findex%3Frid%3D82987'' target=''_blank'' title=''Ссылка открывается в новом окне''>REG.RU</a><br />\r\n- Предоставляет комплекс услуг: регистрацию доменов + хостинг;<br />\r\n- Служба поддержки 24;<br />\r\n- Выгодные цены;<br />\r\n- Есть видео-уроки для начинающих;', '<div align=''center''><a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fwww.reg.ru%2Fnewdomain%2Findex%3Frid%3D82987'' target=''_blank'' title=''Ссылка открывается в новом окне''><img src=''/uploads/images/regru.gif'' align=''middle'' alt=''REG.RU'' title=''REG.RU'' /></a></div><br />\r\nПреимущества <a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fwww.reg.ru%2Fnewdomain%2Findex%3Frid%3D82987'' target=''_blank'' title=''Ссылка открывается в новом окне''>REG.RU</a><br />\r\n- Предоставляет комплекс услуг: регистрацию доменов + хостинг;<br />\r\n- Служба поддержки 24;<br />\r\n- Выгодные цены;<br />\r\n- Есть видео-уроки для начинающих;<br />\r\n<br />\r\nПри помощи REG.RU Вы можете оплачивать услуги более 20 способами оплаты с помощью платежных систем:<br />\r\n<br />\r\n- Платежный оператор Webmoney<br />\r\n- Яндекс.Деньги<br />\r\n- Яндекс.Деньги<br />\r\n- ICQ Money<br />\r\n- Delta Key RUR<br />\r\n- MasterCard<br />\r\n- Visa<br />\r\n- Терминалы оплаты<br />\r\n- Банковские переводы<br />\r\n- Оплата SMS<br />\r\n- и другие<br />\r\n<br />\r\n<a href=''engine.php?do=redirect&amp;url=http%3A%2F%2Fwww.reg.ru%2Fnewdomain%2Findex%3Frid%3D82987'' target=''_blank'' title=''Ссылка открывается в новом окне''>Посетить сайт регистратора</a>', '{USER}', '{DATETIME}', 0, 0, '|', '', 0, '', 1, 0.00, 0, '', 'n');

INSERT INTO `{PREFIX}_system` VALUES 
  ('dbrevision', '833');

INSERT INTO `{PREFIX}_users` VALUES 
  (-1, 'guest', 'Guest', '', '', 'default.png', '0000-00-00 00:00:00', 'default', 0, '', '', '', '', '', 5, '', '0', '', '0000-00-00 00:00:00', '', '', '0000-00-00', 0, '', '', '', '', '', '', 0, -1, 0, 0, 0, 0, 0, 0, '', 0, 'MBzx97cQMjKQ47tJgil9PBQDr', 1, 0, 0, 0.00, 0, 1, NULL, 0, 1, '', '', 0);


INSERT INTO `{PREFIX}_voting` VALUES 
  (1, 'Как вы оцениваете наш движок?', 'Лучший|Неплохой движок|Устраивает|Есть и получше|Ужасный|Самый ужасный|А все равно|', '', '', '{DATETIME}', '0', 0, 1, '', 0);