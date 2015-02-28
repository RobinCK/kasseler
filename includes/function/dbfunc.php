<?php
   /**
   * Дополнительные функции для работы с БД
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource includes/classes/bbcode.class.php
   * @version 2.0
   */
   if (!defined("FUNC_FILE")) die("Access is limited");
   function user_change_sql_namespace($query = "", $namespace = ''){
      if(hook_check(__FUNCTION__)) return hook();
      return $query;
   }
?>
