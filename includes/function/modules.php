<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   function list_modules(){
      global $main; 
      if(hook_check(__FUNCTION__)) return hook();
      $dir = opendir('modules/');
      while(($file = readdir($dir))) if(is_dir('modules/'.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $sel[$file] = isset($main->lang[$file]) ? $main->lang[$file]:$file;
         closedir($dir);
      return $sel;
   }
?>
