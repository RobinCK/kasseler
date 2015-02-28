<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   /**
   * Отправка локальных сообщений пользователю
   * 
   * @param string $user_from - Имя отправителя
   * @param string $user_to - Имя получателя
   * @param string $subj - тема сообщения
   * @param string $text_message - текст сообщения
   */
   function send_message($user_from, $user_to, $subj, $text_message){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $subj = preg_replace('/<[^>]+>/i', '', $subj);
      $user_sel = $main->db->sql_query("SELECT uid, user_name, user_email, user_pm_send FROM ".USERS." WHERE user_name='{$user_to}'");
      $user = $main->db->sql_fetchrow($user_sel);
      send_mail($user['user_email'], $main->lang['user'], $main->config['sends_mail'], $main->config['home_title']." System", $subj, 
         $text_message, array(), array(), array());
   }
?>
