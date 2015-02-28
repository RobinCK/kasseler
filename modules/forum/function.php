<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS') && !defined("ADMIN_FILE")) die("Hacking attempt!");
   global $forum_user_online;
   /**
   * проверяем есть ли новые сообщения в топике
   * 
   * @param mixed $topic_id
   * @param mixed $last_post_id
   */
   function check_topic_forum($topic_id, $last_post_id){
      if(hook_check(__FUNCTION__)) return hook();
      $return = true;
      if(isset($_SESSION['forum_read'])){
         $r=$_SESSION['forum_read'];
         if(!empty($topic_id)){
            if(isset($r['info'][0]) AND $r['info'][0]>=$last_post_id) $return=false;
            if(isset($r['info'][$topic_id]) AND $r['info'][$topic_id]>=$last_post_id) $return=false;
         } else $return=false;
      }
      return $return;
   }

   /**
   * возвращаем информацию о состояние топика
   * 
   * @param mixed $id
   * @param mixed $last_post_id
   * @param mixed $status
   * @param mixed $topic_replies
   * @param mixed $topic_type
   */
   function get_folder_forum($topic_id, $last_post_id, $status){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $img = array(
         'forum_folder_big'          => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_big.png",
         'forum_folder_new_big'      => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_new_big.png",
         'forum_folder_locked_big'   => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_locked_big.png",
      );
      if(hook_check(__FUNCTION__)) return hook();
      if($status=="1") return array($img['forum_folder_locked_big'], $main->lang['forum_folder_locked']);
      if(check_topic_forum($topic_id, $last_post_id)) return array($img['forum_folder_new_big'], $main->lang['forum_folder_new']);
      else return array($img['forum_folder_big'], $main->lang['forum_folder']);
   }
   /**
   * возвращаем информацию о состояние топика
   * 
   * @param mixed $id
   * @param mixed $last_post_id
   * @param mixed $status
   * @param mixed $topic_replies
   * @param mixed $topic_type
   */
   function get_folder_topic($topic_id, $last_post_id, $status, $topic_replies, $topic_type){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $img = array(
         'folder_locked_big'         => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_locked_big.png",
         'folder_locked_big_new'     => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_locked_big_new.png",
         'folder_new_big'            => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_new_big.png",
         'folder_big'                => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_big.png",
         'folder_announce'           => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_announce.png",
         'folder_sticky'             => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_sticky.png",
         'folder_new_hot'            => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_new_hot.png",
         'folder_hot'                => TEMPLATE_PATH."{$main->tpl}/forum/images/folder_hot.png",
      );
      if($topic_type=="2") return array($img['folder_sticky'], $main->lang['sticky']);
      elseif($topic_type=="1") return array($img['folder_announce'], $main->lang['announcement']);
      elseif($status=="1" AND check_topic_forum($topic_id, $last_post_id)) return array($img['folder_locked_big_new'], $main->lang['new_posts_locked']);    
      elseif($status=="1" AND !check_topic_forum($topic_id, $last_post_id)) return array($img['folder_locked_big'], $main->lang['forum_locked']);    
      elseif($topic_replies>$forum['post_views_num'] AND check_topic_forum($topic_id, $last_post_id)) return array($img['folder_new_hot'], $main->lang['new_posts_hot']);    
      elseif($topic_replies>$forum['post_views_num'] AND !check_topic_forum($topic_id, $last_post_id)) return array($img['folder_hot'], $main->lang['no_new_posts_hot']);
      elseif(check_topic_forum($topic_id, $last_post_id)) return array($img['folder_new_big'], $main->lang['new_posts']);
      else return array($img['folder_big'], $main->lang['no_new_posts']);
   }

   function update_posts($id, $type){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      if($id!='-1') $main->db->sql_query("UPDATE ".USERS." SET user_posts=user_posts{$type}1, user_timeout='".(time()+$forum['timeout'])."' WHERE uid='{$id}'");
   }
   function load_forum_category(){
      global $category_bread_crumb,$main;
      if(hook_check(__FUNCTION__)) return hook();
      if(count($category_bread_crumb)==0){
         $main->db->sql_query("select `cat_id`, `tree`, `cat_title` from ".CAT_FORUM." order by `tree`");
         while ($row=$main->db->sql_fetchrow()){
            $category_bread_crumb[$row['tree']]['caption']=$row['cat_title'];
            $category_bread_crumb[$row['tree']]['id']=$row['cat_id'];
         }
      }
   }
   function gen_forum_breadcrumb($forum_id,$forum_title,$forum_tree){
      global $category_bread_crumb,$main;
      if(hook_check(__FUNCTION__)) return hook();
      if($forum_title!="") $arr=array(array('caption'=>$forum_title,'href'=>$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $forum_id))));
      else $arr=array();
      $tree=$forum_tree;
      while ($tree!=""){
         if(isset($category_bread_crumb[$tree])){
            $href=$main->url(array('module' => $main->module, 'do' => 'showsubforum','id'=>$tree));
            $caption=$category_bread_crumb[$tree]['caption'];
            $arr[]=array('caption'=>$caption,'href'=>$href);
         }
         $tree=substr($tree,0,-2);
      }
      $arr[]=array('caption'=>$main->lang['forum'],'href'=>$main->url(array('module' => $main->module)));
      $arr[]=array('caption'=>$main->lang['home'],'href'=>$main->url(array()));
      return array_reverse($arr);
   }
   /**
   * сформировать box со смайлами
   * 
   */
   function forum_smilebox(){
      global $main,$smiles;
      if(hook_check(__FUNCTION__)) return hook();
      $i=0; $y=1;
      $smilesbox = "<div class='smilesbox' id='smilesbox'><table align='center'>";
      foreach($smiles as $key=>$arr){
         if($y==21) break;
         $imgt = "<td align='center' width='39' height='39'><img onclick=\"bbeditor.insert(' ".magic_quotes($arr[0])." ', '', 'message'); \" style='cursor: pointer;' src='{$arr[1]}' alt='".htmlspecialchars($arr[0], ENT_QUOTES)."' title='".htmlspecialchars($arr[0], ENT_QUOTES)."' /></td>";
         if($i==0) $smilesbox .= "<tr>{$imgt}";
         elseif($i==3) {$smilesbox .= "{$imgt}</tr>"; $i=-1;}
         else $smilesbox .= $imgt;
         $i++; $y++;
      }
      $smilesbox.="</table></div>";
      return $smilesbox;
   }

   /**
   * блок возможностей управления темой
   * 
   * @param mixed $forum_id
   */
   function forum_topic_access_list(){
      global $main, $acc;
      if(hook_check(__FUNCTION__)) return hook();
      $super_user=check_access_forum(accModerator);
      $autch = (check_access_forum(accView) OR $super_user) ? $main->lang['acc_view']."<br />" : $main->lang['acc_view2']."<br />";
      $autch .= (check_access_forum(accWrite) OR $super_user) ? $main->lang['acc_write']."<br />" : $main->lang['acc_write2']."<br />";
      $autch .= (check_access_forum(accPost) OR $super_user) ? $main->lang['acc_post']."<br />" : $main->lang['acc_post2']."<br />";
      $autch .= (check_access_forum(accEdit) OR $super_user) ? $main->lang['acc_edit']."<br />" : $main->lang['acc_edit2']."<br />";
      $autch .= (check_access_forum(accDelete) OR $super_user) ? $main->lang['acc_delete']."<br />" : $main->lang['acc_delete2']."<br />";
      $autch .= ($super_user) ? $main->lang['acc_moderation']."<br />" : $main->lang['acc_moderation2']."<br />";        
      $autch .= (check_access_forum(accVoting) OR $super_user) ? $main->lang['acc_voting']."<br />" : $main->lang['acc_voting2']."<br />";
      return $autch;
   }

   /**
   * проверка на доступы к форуму
   * 
   * @param mixed $access
   */
   function topic_access_forum($access, $not_acc_lang='no_access'){
      global $main, $super_user, $topic_active, $template, $topic, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      if($topic_active or $super_user){
         if(check_access_forum($access) OR $super_user) return true;
         else {
            if(is_ajax()) echo "<script type='text/javascript'>alert('".strip_tags($main->lang[$not_acc_lang])."');</script>";
            else {
               add_meta_value($main->lang['new_post']);
               $template->template['posting'] = preg_replace('/<\!--content-->(.+?)<\!--content-->/si', '{CONTENT}', $template->template['posting']);
               $warn = $main->lang[$not_acc_lang];
               $template->set_tpl(hook_set_tpl(array(
                        'OPEN_TABLE'            => open(true),
                        'CLOSE_TABLE'           => close(true),
                        'LOAD_TPL'              => $main->tpl,
                        'CONTENT'               => info("<b>{$warn}</b>", true),
                        'posting.MSG'           => '',
                        'posting.TOPIC'         => '',
                        'FORUM_NAME'            => "<a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$topic['forum_name']}'>{$topic['forum_name']}</a>",
                        'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
                        'L_POSTING_VOTING'      => get_voting(),
                     ),'check_access_forum'), 'posting', array('start' => '{', 'end' => '}'));  
               //Выводим шаблон
               $template->tpl_create(false, 'posting');
            }
            return false;
         }
      } else {
         if(is_ajax()) echo "<script type='text/javascript'>alert('".strip_tags($main->lang['error_topic_closed'])."');</script>";
         else warning($main->lang['error_topic_closed']);
         return false;
      }
   }
   /**
   * список пользователей on-line
   * 
   */
   function user_online(){
      global $supervision, $forum_user_online, $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(!isset($forum_user_online)){
         $online = array();
         foreach(array_merge($supervision['admin'], $supervision['users']) as $key=>$value) $online[] = $value['user_name'];
         $forum_user_online = $online;
      }
      return $forum_user_online;
   }
   /**
   * информация по теме
   * 
   * @param mixed $topic_id
   */
   function forum_topic_info($topic_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return $main->db->sql_fetchrow($main->db->sql_query("SELECT t.*, f.forum_name, c.tree FROM ".TOPICS." AS t, ".FORUMS." AS f, ".CAT_FORUM." AS c WHERE t.topic_id='{$topic_id}' and f.forum_id=t.forum_id and c.cat_id=f.cat_id"));
   }
   /**
   * информация по сообщению
   * 
   * @param mixed $post_id
   */
   function forum_post_info($post_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return $main->db->sql_fetchrow($main->db->sql_query("SELECT p.*, c.tree FROM ".POSTS." AS p, ".FORUMS." AS f, ".CAT_FORUM." AS c WHERE p.post_id='{$post_id}' and f.forum_id=p.forum_id and c.cat_id=f.cat_id"));
   }
   /**
   * информация по форуму
   * 
   * @param mixed $forum_id
   */
   function forum_forum_info($forum_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return $main->db->sql_fetchrow($main->db->sql_query("SELECT f.*, c.tree FROM ".FORUMS." AS f, ".CAT_FORUM." AS c WHERE f.forum_id='{$forum_id}' and c.cat_id=f.cat_id"));
   }

   function add_change_read($topic_id, $value){
      $change=isset($_SESSION['forum_read']['info'][$topic_id])?$_SESSION['forum_read']['info'][$topic_id]<$value:true;
      if($change){
         $_SESSION['forum_read']['info'][$topic_id]=$value;
         $_SESSION['forum_read']['change'][$topic_id]=$value;
         $_SESSION['forum_read']['time']=time();
      }
   }
   /**
   * Выставляем сигнал на прочитаный пост
   * 
   * @param mixed $id
   * @param mixed $value
   * @param mixed $forum_id
   */
   function forum_modify_read($topic_id,$value,$forum_id) {
      if(!isset($_SESSION['forum_read']['info'][$topic_id])||$_SESSION['forum_read']['info'][$topic_id]<$value) {
         add_change_read($topic_id,$value);
      }
   }
   /**
   * инициализируем данные о прочитанных постах
   * 
   */
   function forum_load_read_info(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_SESSION['forum_read']) AND count($_SESSION['forum_read']['change'])>0) forum_save_read_info();
      $_SESSION['forum_read']=array('info'=>array(0=>0),'change'=>array());
      $_SESSION['forum_read']['user']=$main->user['uid'];
      $_SESSION['forum_read']['time']=time();
      $main->db->sql_query("select * from ".FORUM_READ." where uid={$main->user['uid']}");
      if($main->db->sql_numrows()!=0){
         $row=$main->db->sql_fetchrow();
         preg_match_all('/(?is)([a-z0-9]*)=([0-9]*)/', $row['read_info'], $reg, PREG_PATTERN_ORDER);
         for ($i = 0; $i < count($reg[0]); $i++) {
            $_SESSION['forum_read']['info'][$reg[1][$i]]=$reg[2][$i];
         }
      } else {
         if(isset($_SESSION['lastVisit'])){
            $last_visit=strtotime($_SESSION['lastVisit']);
            $main->db->sql_query("select max(post_id) from ".POSTS." where post_time<={$last_visit}");
            if($main->db->sql_numrows()>0)list($max_post)=$main->db->sql_fetchrow();
            else $max_post=0;
            add_change_read(0,$max_post);
         }
         if(isset($_SESSION['topic_list'])){
            $topics=array();
            preg_match_all('/(?is)([a-z0-9]*)=([0-9]*)/', $_SESSION['topic_list'], $reg, PREG_PATTERN_ORDER); 
            for ($i = 0; $i < count($reg[0]); $i++) $topics[]=$reg[1][$i];
            if(count($topics)>0){
               $main->db->sql_query("select topic_id,topic_last_post_id from ".TOPICS." where topic_id in (".implode(",",$topics).")");
               if($main->db->sql_numrows()>0){
                  while (($row=$main->db->sql_fetchrow())){
                     add_change_read($row['topic_id'],$row['topic_last_post_id']);
                  }
               }
            }
            forum_save_read_info(false);
         }
      }
   }
   /**
   * Сохраняем информацию о прочтении из сессии в базу
   * 
   */
   function forum_save_read_info($load_db=true){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_SESSION['forum_read'])){
         $change=$_SESSION['forum_read']['change'];
         if(is_user() AND count($change)>0){
            if($load_db){
               $info=array();
               $main->db->sql_query("select * from ".FORUM_READ." where uid={$main->user['uid']}");
               $exists=$main->db->sql_numrows()!=0;
               if($exists){
                  $row=$main->db->sql_fetchrow();
                  preg_match_all('/(?is)([a-z0-9]*)=([0-9]*)/', $row['read_info'], $reg, PREG_PATTERN_ORDER);
                  for ($i = 0; $i < count($reg[0]); $i++) {
                     $info[$reg[1][$i]]=$reg[2][$i];
                  }
               }
               foreach ($change as $key => $value){if(!isset($info[$key])||$value>$info[$key]) $info[$key]=$value;}
            } else $info=$_SESSION['forum_read']['info'];
            $data="";
            foreach ($info as $key => $value) $data.="{$key}={$value};";
            $main->db->sql_query("REPLACE INTO ".FORUM_READ." SET uid={$main->user['uid']},read_info='{$data}'");
            $_SESSION['forum_read']['change']=array();
         }
      }
   }
   function forum_last_read($topic_id){
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_SESSION['forum_read'])){
         if(isset($_SESSION['forum_read']['change'][$topic_id])) return $_SESSION['forum_read']['change'][$topic_id];
         if(isset($_SESSION['forum_read']['info'][$topic_id])) return $_SESSION['forum_read']['info'][$topic_id];
      }
      return 0;
   }

   function forum_last_post_user($row, $poster_id, $uid, $user_id, $poster_name){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return !empty($poster_name)?($main->lang['ot']." ".(($poster_id!=-1&&$uid!="") ? "<a class='user_info' style='vertical-align: top;' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id,$uid)))."' title='{$main->lang['user_profile']}'>".get_avatar($row,'micro')."<span style='vertical-align: top; margin-left: 4px;'>{$poster_name}</span></a>" : $poster_name)):"";
   }

   function fix_topic_move($topic_id, $new_forum_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("UPDATE ".POSTS." SET forum_id = {$new_forum_id} where topic_id={$topic_id}");
   }

   function forum_topic_move($topic_id, $prev_forum_id, $new_forum_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("UPDATE ".TOPICS." set forum_id={$new_forum_id} where topic_id={$topic_id}");
      fix_topic_move($topic_id, $new_forum_id);
      fix_forum_info($prev_forum_id);
      fix_forum_info($new_forum_id);
   }
   function forum_select_forums(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.cat_id, c.tree, c.cat_title FROM ".FORUMS." AS f, ".CAT_FORUM." AS c where c.cat_id=f.cat_id AND c.invisible='n' ORDER BY c.tree, f.forum_id");
      $selg=$direct=$gtitle=array();
      while (($row=$main->db->sql_fetchrow())){
         $tree=$row['tree'];
         if(isset($direct[$tree])){
            $direct[$tree][$row['forum_id']]=$row['forum_name'];
         } else {
            $selg[$tree]=array($row['forum_id']=>$row['forum_name']);
            $direct[$tree]=&$selg[$tree];
            $gtitle[$tree]=$row['cat_title'];
         }
      }
      return array($selg, $gtitle);
   }
?>
