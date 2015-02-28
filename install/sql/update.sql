-- {fix 728}
ALTER TABLE {PREFIX}_forum_forums
  CHANGE COLUMN acc_view acc_view VARCHAR(255) DEFAULT NULL;

ALTER TABLE {PREFIX}_forum_forums
  CHANGE COLUMN acc_write acc_write VARCHAR(255) DEFAULT NULL;


ALTER TABLE {PREFIX}_forum_forums
  CHANGE COLUMN acc_post acc_post VARCHAR(255) DEFAULT NULL;


ALTER TABLE {PREFIX}_forum_forums
  CHANGE COLUMN acc_edit acc_edit VARCHAR(255) DEFAULT NULL;


ALTER TABLE {PREFIX}_forum_forums
  CHANGE COLUMN acc_delete acc_delete VARCHAR(255) DEFAULT NULL;

UPDATE {PREFIX}_forum_forums
SET
  acc_view = (
  CASE acc_view
  WHEN 0 THEN "4,5,2,1,"
  WHEN 1 THEN "5,2,1,"
  WHEN 2 THEN "2,1,"
  WHEN 3 THEN "1,"
  ELSE "1,"
  END)
WHERE
  not acc_view like '%,';

UPDATE {PREFIX}_forum_forums
SET
  acc_write = (
  CASE acc_write
  WHEN 0 THEN "4,5,2,1,"
  WHEN 1 THEN "5,2,1,"
  WHEN 2 THEN "2,1,"
  WHEN 3 THEN "1,"
  ELSE "1,"
  END)
WHERE
  not acc_write like '%,';

UPDATE {PREFIX}_forum_forums
SET
  acc_post = (
  CASE acc_post
  WHEN 0 THEN "4,5,2,1,"
  WHEN 1 THEN "5,2,1,"
  WHEN 2 THEN "2,1,"
  WHEN 3 THEN "1,"
  ELSE "1,"
  END)
WHERE
  not acc_post like '%,';

UPDATE {PREFIX}_forum_forums
SET
  acc_delete = (
  CASE acc_delete
  WHEN 0 THEN "4,5,2,1,"
  WHEN 1 THEN "5,2,1,"
  WHEN 2 THEN "2,1,"
  WHEN 3 THEN "1,"
  ELSE "1,"
  END)
WHERE
  not acc_delete like '%,';

UPDATE {PREFIX}_forum_forums
SET
  acc_edit = (
  CASE acc_edit
  WHEN 0 THEN "4,5,2,1,"
  WHEN 1 THEN "5,2,1,"
  WHEN 2 THEN "2,1,"
  WHEN 3 THEN "1,"
  ELSE "1,"
  END)
WHERE
  not acc_edit like '%,';

--{fix end}

-- {fix 730}
UPDATE {PREFIX}_forum_forums
SET
  acc_view = "4,5,2,1,"
WHERE
  acc_view='0,';

UPDATE {PREFIX}_forum_forums
SET
  acc_write = "4,5,2,1,"
WHERE
  acc_write='0,';

UPDATE {PREFIX}_forum_forums
SET
  acc_post = "4,5,2,1,"
WHERE
  acc_post='0,';

UPDATE {PREFIX}_forum_forums
SET
  acc_delete = "4,5,2,1,"
WHERE
  acc_delete='0,';

UPDATE {PREFIX}_forum_forums
SET
  acc_edit = "4,5,2,1,"
WHERE
  acc_edit='0,';
--{fix end}

-- {fix 736}
ALTER TABLE {PREFIX}_forum_topics
  ADD COLUMN topic_first_post_fix CHAR(1) DEFAULT 'n';
--{fix end}

-- {fix 737}
ALTER TABLE {PREFIX}_message
  ADD COLUMN tpl VARCHAR(255) DEFAULT NULL;
--{fix end}
-- {fix 745}

--{fix end}

-- {fix 772}
ALTER TABLE {PREFIX}_news
  ADD COLUMN fix_news VARCHAR(1) DEFAULT 'n';
--{fix end}
-- {fix 779}
ALTER TABLE {PREFIX}_forum_categories
  ADD COLUMN tree VARCHAR(200) NOT NULL DEFAULT '00',
  ADD COLUMN description VARCHAR(255) DEFAULT NULL,
  ADD COLUMN invisible CHAR(1) NOT NULL DEFAULT 'n';

update {PREFIX}_forum_categories set tree=concat('0',CAST(cat_sort/10 AS UNSIGNED));

CREATE TABLE {PREFIX}_forum_acc (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ugid INT(11) NOT NULL DEFAULT 0 COMMENT 'user id or group id',
  thisuser CHAR(1) DEFAULT 'u' COMMENT 'this user? u- user, g-group',
  typeacc CHAR(1) NOT NULL DEFAULT 'c' COMMENT 'c- category,f-forum',
  idv INT(11) NOT NULL DEFAULT 0 COMMENT 'id (category or forum)',
  acc_view INT(11) DEFAULT 0,
  acc_read INT(11) DEFAULT 0,
  acc_write INT(11) DEFAULT 0,
  acc_post INT(11) DEFAULT 0,
  acc_edit INT(11) DEFAULT 0,
  acc_delete INT(11) DEFAULT 0,
  acc_upload INT(11) DEFAULT 0,
  acc_download INT(11) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE INDEX UK_kasseler_forum_acc (thisuser, ugid, typeacc, idv)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

insert into {PREFIX}_forum_acc (ugid,thisuser,typeacc,idv,acc_view,acc_read,acc_write,acc_post,acc_edit,acc_delete,acc_upload,acc_download)
select kg.id as ugid,'g' as thisuser,'c' as typeacc,fc.cat_id as idv,
  1 as  acc_view,1 as acc_read,0 as acc_write,0 as acc_post,0 as acc_edit,0 as acc_delete,0 as acc_upload,0 as acc_download
  from {PREFIX}_groups kg,{PREFIX}_forum_categories fc where fc.tree like '__' and kg.id in (3,4,5);

insert into {PREFIX}_forum_acc (ugid,thisuser,typeacc,idv,acc_view,acc_read,acc_write,acc_post,acc_edit,acc_delete,acc_upload,acc_download)
select kg.id as ugid,'g' as thisuser,'c' as typeacc,fc.cat_id as idv,
  1 as  acc_view,1 as acc_read,1 as acc_write,1 as acc_post,1 as acc_edit,1 as acc_delete,1 as acc_upload,1 as acc_download
  from {PREFIX}_groups kg,{PREFIX}_forum_categories fc where fc.tree like '__' and kg.id in (1,2);

insert into {PREFIX}_forum_acc (ugid,thisuser,typeacc,idv,acc_view,acc_read,acc_write,acc_post,acc_edit,acc_delete,acc_upload,acc_download)
select ka.uid as ugid,'u' as thisuser,'f' as typeacc,ka.id as idv,
  1 as  acc_view,1 as acc_read,1 as acc_write,1 as acc_post,1 as acc_edit,1 as acc_delete,1 as acc_upload,1 as acc_download
  from {PREFIX}_acc ka ;

--{fix end}
-- {fix 799}

CREATE TABLE IF NOT EXISTS {PREFIX}_system(
  namep VARCHAR(50) NOT NULL,
  valuep VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (namep)
)
ENGINE = MYISAM
AVG_ROW_LENGTH = 454
CHARACTER SET {CHARSET}
ROW_FORMAT = FIXED;

--{fix end}
-- {fix 810}
CREATE TABLE `{PREFIX}_forum_subscription` (
  uid INT(11) NOT NULL,
  topic_id INT(11) NOT NULL,
  sending CHAR(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (uid, topic_id)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};

ALTER TABLE {PREFIX}_users
  ADD COLUMN user_forum_mail INT(2) NOT NULL DEFAULT 0;

--{fix end}
-- {fix 831}
ALTER TABLE {PREFIX}_forum_posts
  ADD INDEX posts_poster_name (poster_name);
--{fix end}

-- {fix 833}
ALTER TABLE {PREFIX}_pm
  ADD INDEX tid (tid);

ALTER TABLE {PREFIX}_favorite
  ADD INDEX module_user_post (modul, users, post)
--{fix end}

-- {fix 853}
ALTER TABLE {PREFIX}_forum_topics
  ADD COLUMN vote_id INT(11) DEFAULT NULL;

ALTER TABLE {PREFIX}_voting
  ADD COLUMN module VARCHAR(255) DEFAULT NULL,
  ADD COLUMN multisel INT(1) NOT NULL DEFAULT 0,
  ADD COLUMN count_vote INT(11) NOT NULL DEFAULT 0;
--{fix end}

-- {fix 856}
ALTER TABLE {PREFIX}_forum_acc
  ADD COLUMN acc_voting INT(11) NOT NULL DEFAULT 0;
--{fix end}

-- {fix 898}
ALTER TABLE {PREFIX}_blocks
  ADD COLUMN blocktpl VARCHAR(255) DEFAULT NULL;
--{fix end}

-- {fix 900}
ALTER TABLE {PREFIX}_forum_acc
  ADD COLUMN acc_moderator INT(11) NOT NULL DEFAULT 0;
--{fix end}

-- {fix 908}

--{fix end}

-- {fix 921}
ALTER TABLE {PREFIX}_news
  ADD COLUMN afields LONGTEXT DEFAULT NULL,
  ADD COLUMN vgroups VARCHAR(255) DEFAULT NULL;

ALTER TABLE {PREFIX}_pages
  ADD COLUMN afields LONGTEXT DEFAULT NULL;

ALTER TABLE {PREFIX}_shop
  ADD COLUMN afields LONGTEXT DEFAULT NULL;

ALTER TABLE {PREFIX}_static
  ADD COLUMN afields LONGTEXT DEFAULT NULL;

--{fix end}

-- {fix 997}
ALTER TABLE {PREFIX}_voting
  ADD COLUMN agroups VARCHAR(255) DEFAULT NULL;
--{fix end}

-- {fix 998}
ALTER TABLE {PREFIX}_voting
  ADD COLUMN max_multi INT(11) DEFAULT 0,
  ADD COLUMN date_final DATE DEFAULT NULL;
--{fix end}

-- {fix 1019}
CREATE TABLE {PREFIX}_rating(
  id INT(11) NOT NULL AUTO_INCREMENT,
  idm INT(11) DEFAULT NULL,
  module VARCHAR(50) DEFAULT NULL,
  r_up INT(11) NOT NULL DEFAULT 0,
  r_down INT(11) NOT NULL DEFAULT 0,
  users TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX IX_{PREFIX}_rating (module, idm)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};
--{fix end}


-- {fix 1040}
ALTER TABLE {PREFIX}_users
        CHANGE COLUMN user_adm_modules user_adm_modules TEXT DEFAULT NULL;
--{fix end}

-- {fix 1062}
ALTER TABLE {PREFIX}_sessions
  DROP INDEX uname;

ALTER TABLE {PREFIX}_sessions
  ADD UNIQUE INDEX sid (sid);

ALTER TABLE {PREFIX}_sessions
  ADD COLUMN actives CHAR(1) NOT NULL DEFAULT 'y';
--{fix end}

-- {fix 1067}
DROP TABLE IF EXISTS {PREFIX}_content;
--{fix end}

-- {fix 1096}
ALTER TABLE {PREFIX}_users
  ADD COLUMN user_filter_ip TEXT DEFAULT NULL,
  ADD COLUMN user_filter_active INT(1) NOT NULL DEFAULT 0,
  ADD COLUMN user_filter_country INT(1) NOT NULL DEFAULT 1,
  ADD COLUMN user_filter_session INT(1) NOT NULL DEFAULT 0,
  ADD COLUMN user_first_name VARCHAR(60) DEFAULT NULL COMMENT 'name',
  ADD COLUMN user_last_name VARCHAR(60) DEFAULT NULL COMMENT 'familly';
--{fix end}

-- {fix 1151}
CREATE TABLE {PREFIX}_forum_read(
  uid INT(11) NOT NULL,
  read_info TEXT DEFAULT NULL,
  time_change TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (uid)
)
ENGINE = MYISAM
CHARACTER SET {CHARSET};
--{fix end}

-- {fix 1157}
update {PREFIX}_forum_posts p, {PREFIX}_forum_topics t
  set p.forum_id=t.forum_id
  where p.topic_id=t.topic_id;
--{fix end}

-- {fix 1167}
ALTER TABLE {PREFIX}_blocks
  CHANGE COLUMN view view VARCHAR(255) NOT NULL DEFAULT '';
--{fix end}

-- {fix 1188}
UPDATE {PREFIX}_users SET user_new_pm_count=0;
UPDATE {PREFIX}_pm SET pm_read=1;
--{fix end}

-- {fix 1226}
ALTER TABLE {PREFIX}_news
  DROP COLUMN show_group;
--{fix end}

-- {fix 1251}
ALTER TABLE {PREFIX}_albom
  CHANGE COLUMN cid cid INT(11) NOT NULL DEFAULT 0;
--{fix end}
