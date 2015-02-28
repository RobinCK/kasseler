<?php
/**
* Файл создания резервной копии БД
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource backup.php
* @version 2.0
*/
define('KASSELERCMS', true);
define('E__DATABASECONF___', true);
define('E__DATABASE__', true);
define('E__CORE__', true);

if(!file_exists("uploads/tmpfiles/runer.locked")) exit;

require_once "includes/function/init.php";
main::required("includes/nocache.php");
main::required("includes/classes/backup.class.php");

$max_backups = 3;
$backup = new backuper;
$backup->dir = 'uploads/backup/';
$backup->prefix = 'auto_';
$backup->backup();
$backup_dir = opendir("uploads/backup");
$backups_array = array();
while(($file = readdir($backup_dir))){
    if (preg_match('/auto_([0-9\-_]+).([a-z.]*)/s', $file)){
    	$match = "";
        preg_match('/auto_([0-9\-_]+).([a-z.]*)/s', $file, $match);
        $backups_array[] = $match[1].".".$match[2];
    }
}
closedir($backup_dir);

$count = count($backups_array);
if($count>$max_backups){
    sort($backups_array);
    $b = 1;
    foreach ($backups_array as $name=>$value){
        if($b<$count-($max_backups-1)) unlink("uploads/backup/auto_{$value}");
        $b++;
    }
}
?>