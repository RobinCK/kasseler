<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2013 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   * @tutorial функции для работы с конфигами
   */
   if (!defined('FUNC_FILE')) die('Access is limited');

   function scan_init_dir($dirname){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $dir = opendir($dirname);
      while(($file = readdir($dir))){
         if(!preg_match('/\./', $file)){
            $path="{$dirname}/{$file}";
            if(is_dir($path)){
               $initm="{$path}/configure.init.php";
               if(file_exists($initm)) main::required($initm);
            }
         }
      }
      closedir($dir);
   }
   /**
   * Сканирует модули и запускает инициализацию
   * 
   */
   function scan_init_modules(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      scan_init_dir("modules");
   }
   /**
   * Сканирует плагины и запускает инициализацию
   * 
   */
   function scan_init_plagin(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      scan_init_dir("hooks");
   }
?>
