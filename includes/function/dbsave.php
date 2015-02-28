<?php
   /**
   * Фнкция экранирования символов для вставки в БД , но без экранирования "
   * 
   * @param mixed $source
   */
   function  dbslashes($source){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return str_replace('\"','"',addslashes($source));
   }
?>
