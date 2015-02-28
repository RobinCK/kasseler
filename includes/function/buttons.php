<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   function button_d($caption,$onclick,$addon=''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($onclick)) $onclick=str_replace("'",'"',$onclick);
      return "<a class='d_button' ".(empty($addon)?"":$addon)."onclick='{$onclick};'><b>{$main->lang[$caption]}</b></a>";
   }
?>
