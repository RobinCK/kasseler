<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('ADMIN_FILE')) die("Hacking attempt!"); 

global $main, $database, $hooks, $userconf;

$main->db->sql_query("DROP TABLE IF EXISTS `{$database['prefix']}_pm`;");
$main->db->sql_query("DROP TABLE IF EXISTS `{$database['prefix']}_pm_text`;");


$result = $main->db->sql_query("SHOW COLUMNS FROM ".USERS);
$cols = array();
while($row = $main->db->sql_fetchrow($result)) $cols[] = $row['Field'];
if(in_array('user_new_pm_window', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users DROP COLUMN user_new_pm_window;"); //Показ окна уведомления
if(in_array('user_new_pm_count', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users DROP COLUMN user_new_pm_count; "); //Количество сообщений
if(in_array('user_pm_send', $cols)) $main->db->sql_query("ALTER TABLE {$database['prefix']}_users DROP COLUMN user_pm_send;"); // Отправка мыла

$conf_remove = array('directory', 'attaching_files_type', 'miniature_image_width', 'miniature_image_height', 'max_image_width', 'max_image_height', 'attaching_files_size', 'file_upload_limit', 'attaching');
$_nuc = array();
foreach($userconf as $k => $v){
    if(!in_array($k, $conf_remove)) $_nuc[$k] = $v;
}
$userconf = $_nuc;
main::init_function('sources');
save_config('config_user.php', '$userconf', $userconf);

if(isset($_GET['id'])){
    $hooks[$_GET['id']]['install'] = false;
    save_hook_config($hooks);
}

?>