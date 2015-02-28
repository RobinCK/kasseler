<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");


   /**
   * Корректное изменение счетчиков forum ~ topics & posts
   * 
   * @param mixed $forum_id
   */
   function fix_forum_info($forum_id = 0){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $tbl = FORUMS;
      $main->db->sql_query("
         UPDATE ".FORUMS." SET 
         forum_topics=(select count(ft.topic_id) from ".TOPICS." ft where ft.forum_id={$tbl}.forum_id), 
         forum_posts=(select count(fp.post_id) from ".POSTS." fp where fp.forum_id={$tbl}.forum_id), 
         forum_last_post_id=ifnull((select fp.post_id from ".POSTS." fp where fp.forum_id={$tbl}.forum_id order by fp.post_id desc limit 1),0)
         ".($forum_id==0?"":" WHERE {$tbl}.forum_id={$forum_id}"));
   }
   /**
   * Корректное изменение счетчиков topics ~ posts
   * 
   * @param mixed $topic_id
   */
   function fix_topic_info($topic_id = 0){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $tbl = TOPICS;
      $main->db->sql_query("UPDATE ".TOPICS." SET 
         topic_first_post_id=(select min(p.post_id) from ".POSTS." p where p.topic_id={$tbl}.topic_id),
         topic_last_post_id=(select max(p.post_id) from ".POSTS." p where p.topic_id={$tbl}.topic_id),
         topic_replies=(select count(p.post_id) from ".POSTS." p where p.topic_id={$tbl}.topic_id)".
         ($topic_id==0?"":" where {$tbl}.topic_id={$topic_id}"));
   }
?>
