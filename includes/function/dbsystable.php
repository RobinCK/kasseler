<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   @define("DBREVISION","dbrevision");
   global $main;
   function check_create_system_table(){
      global $main,$database;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("SHOW TABLES from `{$database['name']}` LIKE '%_system'");
      if($main->db->sql_numrows()==0) {
         $main->db->sql_query("
         CREATE TABLE IF NOT EXISTS ".SYSTEMDB."(
         namep VARCHAR(50) NOT NULL,
         valuep VARCHAR(100) DEFAULT NULL,
         PRIMARY KEY (namep)
         )
         ENGINE = MYISAM
         AVG_ROW_LENGTH = 454
         CHARACTER SET utf8
         COLLATE utf8_bin
         ROW_FORMAT = FIXED;
         ");
       $main->db->sql_query("REPLACE INTO  ".SYSTEMDB." VALUES ('".DBREVISION."', '{$database['revision']}')");  
      };
   }
   /**
   * Получить версию Kassleer DB
   * 
   */
   function get_db_revision($check_table=false){
      global $main,$database;
      if($check_table){
         $main->db->sql_query("SHOW TABLES from `{$database['name']}` LIKE '%_system'");
         if($main->db->sql_numrows()==0) return isset($database['revision'])?$database['revision']:793;
      }
      $main->db->sql_query("select valuep from ".SYSTEMDB." where namep='".DBREVISION."'");
      if($main->db->sql_numrows()!=0){
         list($valuep)=$main->db->sql_fetchrow();
         return intval($valuep);
      } else return 0;
   }
   /**
   * Задать версию Kassleer DB
   * 
   * @param int $new_revision
   * @return int
   */
   function set_db_revision($new_revision,$check_table=false){
      global $main,$database;
      if($check_table){
         $main->db->sql_query("SHOW TABLES from `{$database['name']}` LIKE '%_system'");
         if($main->db->sql_numrows()==0) return false;
      }
      $main->db->sql_query("REPLACE INTO  ".SYSTEMDB." VALUES ('".DBREVISION."', '{$new_revision}')");
   }
?>
