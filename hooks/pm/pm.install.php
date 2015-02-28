<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!"); 

global $main, $database, $hooks, $userconf;

$main->db->sql_query("
CREATE TABLE IF NOT EXISTS `{$database['prefix']}_pm` (
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
CHARACTER SET {$database['charset']};
");

$main->db->sql_query("
CREATE TABLE IF NOT EXISTS `{$database['prefix']}_pm_text` (
  tid INT(11) NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  PRIMARY KEY (tid)
)
ENGINE = MYISAM
CHARACTER SET {$database['charset']};
");

$result = $main->db->sql_query("SHOW COLUMNS FROM ".USERS);
$cols = array();
while($row = $main->db->sql_fetchrow($result)) $cols[] = $row['Field'];
if(!in_array('user_new_pm_window', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users ADD COLUMN user_new_pm_window INT(1) DEFAULT 1;"); //Показ окна уведомления
if(!in_array('user_new_pm_count', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users ADD COLUMN user_new_pm_count INT(2) DEFAULT 0;"); //Количество сообщений
if(!in_array('user_pm_send', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users ADD COLUMN user_pm_send INT(1) DEFAULT 1;"); // Отправка мыла

main::init_function('sources');

$_conf = array(
    'directory'                  => 'uploads/pm/',
    'attaching_files_type'       => 'zip,rar,tar,gz,jpeg,jpg,gif,png',
    'miniature_image_width'      => '300',
    'miniature_image_height'     => '500',
    'max_image_width'            => '1280',
    'max_image_height'           => '2048',
    'attaching_files_size'       => '1024',
    'file_upload_limit'          => '10',
    'attaching'                  => 'on',
);

$userconf = array_merge($userconf, $_conf);
$_POST = $_conf;

save_config('config_user.php', '$userconf', $userconf);

if(isset($_GET['id'])){
    $hooks[$_GET['id']]['install'] = true;
    save_hook_config($hooks);
}

?>