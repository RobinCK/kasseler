<?php
   /**
   * Файл получение групп доступа к модулям и блокам
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource includes/function/module_access.php
   * @version 2.0
   */
   if (!defined("KASSELERCMS") AND !defined("ADMIN_FILE")) die("Access is limited");
   /**
   * возвращает доступ к модулям независимо от нового или старого метода хранения
   * 
   * @param mixed $row
   */
   function modulelist_encode_prev($row){
      if(hook_check(__FUNCTION__)) return hook();
      $rg="1";
      if(empty($row['groups'])){
         if(isset($row['view']))
         switch(intval($row['view'])){
            case 0: $rg="1"; break;
            case 1: $rg="0"; break;
            case 2: $rg="4"; break;
            case 3: $rg="5"; break;
            case 4: $rg="1"; break;
         }
      } else $rg=$row['groups'];
      $groups = explode(',', $rg);
      foreach ($groups as $key => $value){if($value=="") unset($groups[$key]); else $groups[$key]=intval($value);}
      return $groups;
   }

   /**
   * возвращает доступ к блокам независимо от нового или старого метода хранения
   * 
   * @param mixed $view
   */
   function block_encode_prev_acc($view){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $pos = strpos($view,',');
      if($pos === FALSE){
         $rg="";
         switch(intval($view)){
            case 0: $rg="1"; break;
            case 1: $rg="0"; break;
            case 2: $rg="4"; break;
            case 3: $rg="5"; break;
            case 4: $rg="1"; break;
         }
         return $rg==""?array(1):array(intval($rg));
      } else {
         if(!empty($view)){
            if($view[0]==',') $view=substr($view,1);
            $groups =  explode(',', $view);
            foreach ($groups as $key => $value){if($value=="") unset($groups[$key]); else $groups[$key]=intval($value);}
            return $groups;
         } else return array(1);
      }
   }
?>
