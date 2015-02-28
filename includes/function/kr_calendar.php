<?php
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   function in_calendar($name, $class="", $value=""){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($class)) $class = 'input_text_d';
      $value = empty($value)?date("d.m.Y"):$value;
      $id=conv_name_to_id($name);
      return in_text($name, $class, $value, false," size='11' style='vertical-align: middle;' ")."<img id='{$id}_calendar' class='input_calendar' alt='{$main->lang['calendar']}' title='{$main->lang['calendar']}' src='includes/images/date.png' style='cursor: pointer;vertical-align: middle;'/>";
   }

   function in_calendar_time($name, $class="", $value=""){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $value_time='00:00:00';
      if(!empty($value)){
         $time= strtotime($value);
         $value_data = date('d.m.Y',$time);
         $value_time = date('H:i:s',$time);
      } else $value_data=$value;
      if(empty($class)) $class = 'input_text_d';
      return in_calendar($name, $class, $value_data).in_text("{$name}_time", "input_text2", $value_time,''," style='margin-left:5px; vertical-align: middle; width: 5em;'");
   }
?>
