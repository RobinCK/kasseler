<?php
if (!defined('FUNC_FILE')) die('Access is limited');
   function get_tmcefile_config($size_editor=0){
      if(hook_check(__FUNCTION__)) return hook();
      switch ($size_editor){
         case 0:$fsname='tinymce_small.js';break;
         case 1:$fsname='tinymce_medium.js';break;
         case 2:$fsname='tinymce_big.js';break;
         default:$fsname="";break;
      }
      return $fsname;
   }
?>
