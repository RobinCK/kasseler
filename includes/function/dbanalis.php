<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $main;
   global $adb_tables, $adb_fields;
   function db_tables($refresh_force=false){
      global $main, $adb_tables, $database;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($adb_tables) OR $refresh_force){
         $result = $main->db->sql_query("SHOW TABLES FROM `{$database['name']}`");
         while(list($name) = $main->db->sql_fetchrow($result)) $adb_tables[$name] = $name;
      }
      return $adb_tables;
   }
   function db_fields($table, $refresh_force=false){
      global $main, $adb_tables, $adb_fields;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($adb_fields[$table]) OR $refresh_force){
         $main->db->sql_query("SHOW COLUMNS FROM {$table}");
         $adb_fields[$table]=array();
         while (($row=$main->db->sql_fetchrow())){$adb_fields[$table][$row[0]]=$row[1];}
      }
      return $adb_fields[$table];
   }
?>
