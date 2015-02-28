<?php
   /**
   * Центральный блок последних сообщений форума
   * 
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource blocks/block-center_forum.php
   * @version 2.0
   */
   if (!defined('BLOCK_FILE')) {
      Header("Location: ../index.php");
      exit;
   }

   $limit_view_topics = 10;

   global $main;
   main::init_language("forum");
   main::required("modules/forum/accinfo.php"); 
   forum_full_access_load();
   $acclist=(isset($_SESSION['forum_access']) AND isset($_SESSION['forum_access']['forum_read']))?$_SESSION['forum_access']['forum_read']:array();

   $result = $main->db->sql_query("SELECT t.topic_id, t.topic_title, t.topic_poster_name, t.topic_views, t.topic_replies, t.forum_id, fc.tree, p.poster_name, ut.user_id as topic_user_id, ut.uid as topic_uid, up.user_id as post_user_id, up.uid as post_uid
      FROM ".CAT_FORUM." fc, ".FORUMS." ff,".TOPICS." AS t LEFT JOIN ".POSTS." AS p ON (t.topic_last_post_id=p.post_id) LEFT JOIN ".USERS." AS ut ON(t.topic_poster=ut.uid) LEFT JOIN ".USERS." AS up ON(p.poster_id=up.uid)
      WHERE ".(!empty($acclist)?("ff.forum_id in (".implode(',',$acclist).") and "):"")." t.forum_id=ff.forum_id AND fc.cat_id=ff.cat_id
      ORDER BY p.post_id DESC 
      LIMIT ".($limit_view_topics*10)."");
   if($main->db->sql_numrows($result)>0){
      $row = 'row1';
      echo "<table class='block_table table_tr' width='100%' cellpadding='3' cellspacing='1'><thead><tr>\n<th>{$main->lang['topic_title']}</th>\n<th width='100' class='colsth'>{$main->lang['author']}</th>\n<th width='20'>{$main->lang['topic_views']}</th>\n<th width='20' class='colsth'>{$main->lang['topic_replies']}</th>\n<th width='100'>{$main->lang['poster_name']}</th></tr></thead><tbody>\n";
      $i=0;
      while(($topic = $main->db->sql_fetchrow($result))){
         forum_open_access_forum($topic['tree'], $topic['forum_id']);
         if(check_access_forum(accView)){
            $y=10;
            while($y>1){
               $topic_title = cut_text($topic['topic_title'], $y);
               if(mb_strlen($topic_title)>60) $y--;
               else break;
            }
            echo "<tr class='{$row}'>\n".
            "<td><a class='sys_link' href='".$main->url(array('module' => 'forum', 'do' => 'lastpost', 'id' => $topic['topic_id']))."' title='{$topic['topic_title']}'>{$topic_title}</a></td>\n".
            "<td class='cols'><a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($topic['topic_user_id'], $topic['topic_uid'])))."' title='{$main->lang['user_profile']}'>{$topic['topic_poster_name']}</a></td>\n".
            "<td align='center'>{$topic['topic_views']}</td>\n".
            "<td class='cols'>{$topic['topic_replies']}</td>\n".
            "<td align='center'><a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($topic['post_user_id'], $topic['post_uid'])))."' title='{$main->lang['user_profile']}'>{$topic['poster_name']}</a></td>\n".
            "</tr>";
            $row = ($row=='row1') ? 'row2' : 'row1';
            $i++;
         }
         if($i>=$limit_view_topics) break;
      }
      echo "</tbody></table>";
   }
?>