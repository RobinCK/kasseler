<?php
   /**
   * Дополнительные функции для работы с шаблонами
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource includes/classes/bbcode.class.php
   * @version 2.0
   */
   if (!defined("FUNC_FILE")) die("Access is limited");
   
   /**
   * меняет переменные из $content значениями из $variables($key=>$value)
   * 
   * @param array $variables
   * @param string $content
   */
   function tpl_replace($variables, $content){
   global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $keys=$values=array();
      foreach ($variables as $key => $value) {$keys[]='/'.$key.'/si';$values[]=$value;}
      return preg_replace($keys, $values, $content);
   }
   /**
   * меняет $keys и $values согласно значениям из $newkv
   * 
   * @param mixed $keys
   * @param mixed $values
   * @param mixed $newkv
   */
   function tpl_replace_keyvalue(&$keys,&$values,$newkv){
      if(hook_check(__FUNCTION__)) return hook();
      foreach ($newkv as $value) {
         if(($key = array_search("{{$value}}", $keys))!== FALSE){$values[$key]=$value;}
      }
   }
?>
