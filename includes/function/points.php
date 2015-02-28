<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2013 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   * @tutorial удаление points у пользователя
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $change_point;
   function global_delete_points_session(){
      global $main, $change_point;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
      $main->user['user_points'] -= $change_point;
   }
   function global_delete_points($points, $idrecord, $module=''){
      global $main, $change_point;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($module)) $module = $main->module;
      $whereuser="";
      if($module=='forum'){
         $main->db->sql_query("select poster_id FROM ".POSTS." WHERE post_id='{$idrecord}'");
         if($main->db->sql_numrows()>0){
            list($uid)=$main->db->sql_fetchrow();
            $whereuser=" uid='{$uid}'";
         }
      } else {
         $TABLE="";
         switch ($module) {
            case 'news':$TABLE=NEWS; break;
            case 'files':$TABLE=FILES;break;
            case 'jokes':$TABLE=JOKES;break;
            case 'media':$TABLE=MEDIA;break;
            case 'pages':$TABLE=PAGES;break;
         }
         if(!empty($TABLE)){
            $main->db->sql_query("select author FROM ".$TABLE." WHERE id='{$idrecord}'");
            if($main->db->sql_numrows()>0){
               list($author)=$main->db->sql_fetchrow();
               $whereuser=" upper(user_name)=upper('{$author}')";
            }
         }
      }
      if(!empty($whereuser)){
         $main->db->sql_query("select uid, user_group, user_points, user_name from ".USERS." WHERE {$whereuser}");
         if($main->db->sql_numrows()>0){
            list($uid, $user_group, $user_points, $user_name) = $main->db->sql_fetchrow();
            main::init_function('session_tools');
            $change_point = $points;
            user_sessions_modify($user_name,'global_delete_points_session');
            $user_points-= $points;
            $main->db->sql_query("UPDATE ".USERS." SET user_points=user_points-{$points} WHERE uid={$uid}");
            list($special) = $main->db->sql_fetchrow($main->db->sql_query("SELECT special FROM ".GROUPS." WHERE id={$user_group}"));
            if($special!="1"){
               list($gid, $points) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id, points FROM ".GROUPS." WHERE id<'{$user_group}' AND special=0 ORDER BY id DESC LIMIT 1"));
               if($user_points < $points AND !empty($gid)) $main->db->sql_query("UPDATE ".USERS." SET user_group='{$gid}' WHERE uid='{$uid}'");
            }
         }
      }
   }
?>
