<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   function small_language(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      switch($main->language){
         case "english": return "en"; break;
         case "german": return "de"; break;
         case "russian": return "ru"; break;
         case "ukraine": return "uk"; break;
      }
      return "en";
   }
?>
