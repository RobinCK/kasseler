<?php
   if (!defined('KASSELERCMS')) die('Access is limited');
   /**
   * установить признак сесси при след обращении закрыть сессию
   */
   function set_this_session_close(){
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($_SESSION['cache_session_user']))  $_SESSION['close_session']=true;
   }
   /**
   * установить признак обновления сессии
   * 
   */
   function set_this_session_update(){
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($_SESSION['cache_session_user'])) $_SESSION['update_session']=true;
   }
   /**
   * применение изменений к сесиям пользователя
   * 
   * @param mixed $user_name
   * @param mixed $proc_modify
   */
   function user_sessions_modify($user_name,$proc_modify=''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($proc_modify)) $proc_modify='set_this_session_update';
      $current_session=session_id();
      $update=array('time'=>time());
      $args=func_num_args()>2?array_slice(func_get_args(),2):array();
      session_commit();
      try {
         $dbr = $main->db->sql_query("select * from ".SESSIONS." where upper(uname)=upper('{$user_name}')");
         if($main->db->sql_numrows($dbr)>0){
            while (($row=$main->db->sql_fetchrow($dbr))){
               session_id($row['sid']);
               session_start();
               call_user_func_array($proc_modify,$args);
               //$proc_modify($args);
               session_write_close();
               sql_update($update,SESSIONS," sid='{$row['sid']}'");
            }
         }
      } catch (Exception $e) { }
      // восстанавливаем свою сессию 
      session_id($current_session);
      session_start();
   }
   /**
   * Даем изменения по ВСЕМ пользователям (кроме текуще естественно)
   * 
   * @param mixed $proc_modify
   */
   function all_user_sessions_modify($proc_modify=''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($proc_modify)) $proc_modify='set_this_session_update';
      $current_session=session_id();
      $update=array('time'=>time());
      session_commit();
      try {
         $dbr = $main->db->sql_query("select * from ".SESSIONS." where sid<>'{$current_session}'");
         if($main->db->sql_numrows($dbr)>0){
            while (($row=$main->db->sql_fetchrow($dbr))){
               session_id($row['sid']);
               session_start();
               $proc_modify();
               session_write_close();
               sql_update($update,SESSIONS," sid='{$row['sid']}'");
            }
         }
      } catch (Exception $e) { }
      // восстанавливаем свою сессию 
      session_id($current_session);
      session_start();
   }
   /**
   * установить закрытие сесий для определенного пользователя (также пример для использвания user_sessions_modify)
   * 
   * @param string $user_name - имя пользователя
   */
   function user_sessions_kill_all($user_name){
      global $main;
      user_sessions_modify($user_name,'set_this_session_close');
   }

   function sess_setcookies($value, $name, $time=0){
      global $config;
      $time = ($time==0) ? time() + 60*60*24*intval($config['time_of_life_session']) : $time;
      $time = ($time==1) ? 0 : $time;
      setcookie($name, $value, $time, "/");
   }

   /**
   * принудительный logut сесии
   * 
   */
   function session_logut(){
      global $config;
      $admin_cookie=isset($_SESSION['admin']);
      //session_unset();
      $clear_session=array('cache_session_user','user','lastAction','save_authorise','admin','supervision');
      foreach ($clear_session as $key => $value) {if(isset($_SESSION[$value])) unset($_SESSION[$value]);}
      session_commit();
      if(isset($_COOKIE[$config['admin_cookies']])) sess_setcookies("",$config['admin_cookies'], 1);
      $clear_cookie=array('update_session','online',$config['admin_cookies'],$config['user_cookies']);
      foreach ($clear_cookie as $key => $value) {if(isset($_COOKIE[$value])) unset($_COOKIE[$value]);}
      sess_setcookies("", $config['user_cookies'], 1);
      sess_setcookies("", "update_session", 1);
      sess_setcookies("", "online", 1);
      //header("Location: ".$_SERVER['REQUEST_URI']);
   }
?>
