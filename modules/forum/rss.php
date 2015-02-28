<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");

   global $main,  $tpl_create;
   function rss_url_encode($matches){
      if(hook_check(__FUNCTION__)) return hook();
      return $matches[1].urldecode($matches[2]);
   }
   function rss_forum(){
      global $main, $forum, $config;
      if(hook_check(__FUNCTION__)) return hook();
      ob_clean();
      main::init_class('rss');
      main::required("modules/{$main->module}/accinfo.php"); 
      global $uid, $groups;
      if($forum['rss']==ENABLED){
         if($forum['rss_filter']==0){
            $main->db->sql_query("select p.post_id,t.topic_title,p.post_text,p.poster_name,p.post_time 
               from ".POSTS." p, ".TOPICS." t  where t.topic_id=p.topic_id order by p.post_id desc limit {$forum['rss_limit']}");
         } else {
            $access = forum_full_access_load_group($forum['rss_filter']);
            $main->db->sql_query("select p.post_id,t.topic_title,p.post_text,p.poster_name,p.post_time
               from ".POSTS." p, ".TOPICS." t where p.forum_id in (".implode(",", !empty($access['forum_read'])?$access['forum_read']:array(0)).") and t.topic_id=p.topic_id
               order by p.post_id desc limit {$forum['rss_limit']}");
         }
         if($main->db->sql_numrows()>0){
            $rss_writer = new rss_writer;
            while(list($post_id, $title, $begin, $author, $date) = $main->db->sql_fetchrow()){
               $content=parse_bb($forum['rss_filter']!=0?$begin:$main->lang['only_authorization']);
               $content = preg_replace('/([\'"]+)(engine\.php\?do=redirect&amp;url=)/i', '$1', $content);
               $content = preg_replace_callback('/([\'"]+)((aim|feed|file|ftp|gopher|http|https|irc|mailto|news|nntp|sftp|ssh|telnet)+[^\'"]*)/i','rss_url_encode',$content);
               $rss_writer->add_item($post_id, $title, gmdate("Y-m-d H:i:s", $date+intval($config['GMT_correct'])*60*60), '', $content, $author, $forum['rss_title']);
            }
            $rss_writer->write();
         } else info($main->lang['noinfo']);
      } else info($main->lang['rss_disabled']);
   }

?>
