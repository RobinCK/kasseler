<?php
if (!defined('KASSELERCMS')) die('Access is limited');
/**
* Функция конвертирования POST array параметра, при множественном выборе 
* 
* @param string $param_name
* @return string
*/
   function post_array_ids($param_name){
      if(isset($_POST[$param_name]) AND is_array($_POST[$param_name]) AND count($_POST[$param_name])>0){
         $arr=$_POST[$param_name];
         return ",".implode(",",$arr).",";
      } else return "";
   }
?>
