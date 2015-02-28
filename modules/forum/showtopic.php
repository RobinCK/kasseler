<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");  
   global $main, $forum, $config, $userconf, $supervision, $tpl_create, $smiles, $type_select, $template;
   main::required("modules/{$main->module}/accinfo.php");
   main::required("modules/{$main->module}/voting.php");

   global $groups,$acc;
   global $_last_post, $voting, $topic, $inum, $this_page, $count_rows;
   /**
   * формируем переменные шаблона
   * 
   * @param mixed $row
   */
   function forum_showtopic_post_tpl($row, $row_c, $i, $inum){
      global $main, $forum, $topic, $voting, $this_page, $_last_post;
      if(hook_check(__FUNCTION__)) return hook();
      $fixed_post=$topic['topic_first_post_fix']=='y';
      $_last_post = $row['post_id'];
      $tnx = (!empty($row['post_tnx'])) ? "<br /><br /><fieldset class='post_tnx' style='margin-left: 30px;' title='{$main->lang['tnxplay']}'><legend class='post'>{$main->lang['tnxplay']}</legend>{$row['post_tnx']}</fieldset>" : "";
      $text_value=check_access_forum(accDownload)?$row['post_text']:preg_replace('/<!--start\x20attach-->.*<!--end\x20attach-->/si', '', $row['post_text']);
      $superuser = check_access_forum(accModerator);
      $topic_active = $topic['forum_status']==0 AND $topic['topic_status']==0;
      return array(            
         'OPEN_TABLE'              => open(true),
         'CLOSE_TABLE'             => close(true),
         'L_POST_SUBJECT'          => $main->lang['title_message'],
         'L_POSTED'                => $main->lang['postdate'],
         'BACK_TO_TOP'             => "<a style='padding:2px 3px 3px 9px;' class='forum_button forum_button2' href='#' onclick=\"\$('body,html').animate({scrollTop : 0},'slow'); return false;\"><span><img style='margin-right: 5px;' class='icon icon-up icon_relative' src='includes/images/pixel.gif' alt='' /></span></a>",
         'LOAD_TPL'                => $main->tpl,
         'FORUM_VOTE'              => ($i==1)?$voting:"",

         'postrow.PROFILE_IMG'     => ($row['poster_id']!='-1')?"<a class='user_info forum_button forum_button2' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'><span><img style='margin-right: 5px;' class='icon icon-profile icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['account']}</b></a>":"",
         ///////////////////////////////////////////////////////////////////////////////////////////////
         'postrow.QUOTE_IMG'       => (($topic_active AND check_access_forum(accPost)) OR $superuser)?"<a class='forum_button forum_button2' href='".$main->url(array('module' => $main->module, 'do' => 'qutoepost', 'id' => $row['post_id']))."'><span><img style='margin-right: 5px;' class='icon icon-quote icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['quote']}</b></a>":"",
         'postrow.EDIT_IMG'        => (($superuser OR ($row['poster_id']==$main->user['uid'] AND $topic_active) OR check_access_forum(accEdit)) AND $main->user['uid']!=-1)?"<a class='forum_button forum_button2' href='".$main->url(array('module' => $main->module, 'do' => 'postedit', 'id' => $row['post_id'], 'page' => $this_page))."' title='{$main->lang['edit']}'><span><img style='margin-right: 5px;' class='icon icon-edit icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['edit']}</b></a>":"",
         'postrow.DELETE_IMG'      => ($superuser OR check_access_forum(accDelete))?"<a class='forum_button forum_button2 postrow_delete id_post_{$row['post_id']}' href='".$main->url(array('module' => $main->module, 'do' => 'deletepost', 'id' => $row['post_id'], 'page' => isset($_GET['page'])?$_GET['page']:1))."' title='{$main->lang['delete']}' onclick=\"return bind_delete(this, '{$main->lang['realdelete']}')\" ><span><img style='margin-right: 5px;' class='icon icon-close icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['delete']}</b></a>":"",
         'postrow.IP_IMG'          => $superuser?"<a class='ipshow forum_button forum_button2 sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showip', 'id' => $row['post_id']))."' title='{$main->lang['showip']}'><span><img style='margin-right: 5px;' class='icon icon-ip icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>IP</b></a>":"",
         'postrow.REPORT'          => "<a class='forum_button forum_button2' href='#' onclick=\"return send_report('http://".get_host_name()."/index.php?module={$main->module}&amp;do=report', '{$row['post_id']}');\" title='{$main->lang['postreport']}'><span><img style='margin-right: 5px;' class='icon icon-report icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['freport']}</b></a>",
         'postrow.TNX'             => "<a class='forum_button forum_button2' href='#' onclick=\"haja({elm:'posttnx_{$row['post_id']}', action:'http://".get_host_name()."/index.php?module={$main->module}&amp;do=user_tnx'}, {'post_id':'{$row['post_id']}'}, {}); return false;\" title='{$main->lang['tnxthis']}'><span><img style='margin-right: 5px;' class='icon icon-tnx icon_relative' src='includes/images/pixel.gif' alt='' /></span><b>{$main->lang['tnx']}</b></a>",
         'postrow.POSTER_NAME'     => $row['user_id']!=""?"<a name='entry{$inum}'>&nbsp;</a>".get_flag($row['user_country'], "", " style='position: relative; top: 4px; left: -3px;'")."<a href='#' onclick=\"return name_user_set('{$row['user_name']}');\" ><b>{$row['poster_name']}</b></a>":"<b>{$row['poster_name']}</b>",
         ///////////////////////////////////////////////////////////////////////////////////////////////
         'postrow.COUNTRY'         => "",
         'postrow.RANK_IMAGE'      => "",
         'postrow.POSTER_AVATAR'   => get_avatar($row, 'normal'),
         'postrow.SMALL_AVATAR'    => get_avatar($row, 'small'),
         'postrow.MINI_AVATAR'     => get_avatar($row, 'mini'),
         'postrow.USER_NUMBER'     => ($row['poster_id']!='-1'&&$row['user_id']!="")?$main->lang['user_num'].": {$row['uid']}<br />":"",
         'postrow.USER_TNX'        => ($row['poster_id']!='-1'&&$row['user_id']!="")?$main->lang['user_tnx'].": {$row['user_tnx']} {$main->lang['counts']}<br />":"",
         'postrow.POSTER_JOINED'   => ($row['poster_id']!='-1'&&$row['user_id']!="")?$main->lang['reg_date'].": ".format_date($row['user_regdate'])."<br />":"",
         'postrow.POSTER_POSTS'    => ($row['poster_id']!='-1'&&$row['user_id']!="")?$main->lang['posts'].": {$row['user_posts']}<br />":"",
         'postrow.POSTER_AGE'      => ($row['user_birthday']!="0000-00-00"&&$row['user_id']!="")?$main->lang['age'].": ".get_age($row['user_birthday'])."<br />":"",
         'postrow.POSTER_GROUP'    => ($row['user_group']!=0)?$main->lang['group'].": <span style='color: #{$row['color']}'>".$row['title']."</span><br />":"",
         'postrow.POSTER_LOCALITY' => ($row['poster_id']!='-1')?$main->lang['locality'].": {$row['user_locality']}<br />":"",
         'postrow.POSTER_STATUS'   => ($row['poster_id']!='-1')?($main->lang['status'].": ".(in_array($row['user_name'], user_online())?"<span style='color: green;'>Online</span>":"<span style='color: red;'>Offline</span>")):"",

         'postrow.ROW_CLASS'       => (($row['post_id']==$topic['topic_first_post_id']&&$fixed_post)?"row0":$row_c),
         'postrow.POST_LAST_EDIT'  => !empty($row['post_edit_time'])?"<br /><span class='last_post_edit'>".str_replace(array('{DATE}', '{USER}'), array(format_date(date('Y-m-d H:i:s', $row['post_edit_time']), "d.m.Y H:i:s"), "<b>{$row['post_edit_user']}</b>"), $main->lang['last_post_edit'])."</span>":'',
         'postrow.SIGNATURE'       => !empty($row['user_signature'])?"<br />__________________<br />".parse_bb($row['user_signature']):"",
         'postrow.POST_NUMBER'     => "<a class='post_number' href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $row['topic_id'])+(isset($_GET['page'])?array('page' => $_GET['page']):array()))."#entry{$inum}'>#{$inum}</a>",
         'postrow.POST_DATE'       => format_date(date("Y-m-d H:i:s", $row['post_time']), "{$main->config['date_format']} H:i:s"),
         'postrow.POST_SUBJECT'    => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showpost', 'id' => $row['post_id']))."' title='{$main->lang['showonepost']}'>{$row['post_subject']}</a>",
         'postrow.MESSAGE'         => "<div id='posttext_{$row['post_id']}'>".parse_bb($text_value)."</div><div id='posttnx_{$row['post_id']}'>{$tnx}</div>",
      );
   }
   /**
   * сформировать выбранные сообщения
   * 
   * @param mixed $result - DB результат выборки
   * @param mixed $this_page - текущая страцица показа
   */
   function forum_showtopic_gen_posts($result){
      global $main, $template, $voting, $topic, $inum, $_last_post, $this_page, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $ret="";$i=1;$row_c = "row1";$_last_post = 0;
      $initi=isset($_POST['count_post'])?$_POST['count_post']+1:1;
      $inum=isset($_GET['page'])?($forum['post_views_num']*(intval($_GET['page'])-1)+$initi):$initi;
      $max_read=0;
      while (($row=$main->db->sql_fetchrow($result))){
         $template->get_tpl('POST_CONTENT','POST_CONTENT');
         if($max_read<$row['post_id']) $max_read=$row['post_id'];
         $row_post = forum_showtopic_post_tpl($row, $row_c, $i, $inum);
         $row_c = ($row_c=='row1') ? "row2" : 'row1';
         $template->set_tpl(hook_set_tpl($row_post,__FUNCTION__),'POST_CONTENT',array('start' => '{', 'end' => '}'));
         $ret .= $template->tpl_create(true,'POST_CONTENT');
         $i++; $inum++;
      }
      forum_modify_read($topic['topic_id'], $max_read, $topic['forum_id']);
      return $ret;
   }
   /**
   * кнопки администрирования сообщений в теме
   * 
   */
   function forum_topic_admin_button(){
      global $main,$topic;
      if(hook_check(__FUNCTION__)) return hook();
      return check_access_forum(accModerator) ? "
      <a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'topicedit', 'id' => intval($_GET['id'])))."' title='{$main->lang['adm_edit_topic']}'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_edit.png' alt='{$main->lang['adm_edit_topic']}' /></a>
      <a href='".$main->url(array('module' => $main->module, 'do' => 'closetopic', 'id' => intval($_GET['id'])))."' title='".($topic['topic_status']=='0'?$main->lang['adm_close_topic']:$main->lang['adm_open_topic'])."'>".($topic['topic_status']=='0'?"<img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_close.png' alt='{$main->lang['adm_close_topic']}' />":"<img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_open.png' alt='{$main->lang['adm_open_topic']}' />")."</a>    
      <a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'topicmove', 'id' => intval($_GET['id'])))."' title='{$main->lang['adm_move_topic']}'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_move.png' alt='{$main->lang['adm_move_topic']}' /></a>
      <a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'topicsplit', 'id' => intval($_GET['id'])))."' title='{$main->lang['adm_split_topic']}'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_split.png' alt='{$main->lang['adm_split_topic']}' /></a>        
      <a onclick=\"return dialog('{$main->lang['real_delete_topic']}');\" href='".$main->url(array('module' => $main->module, 'do' => 'topicdel', 'id' => intval($_GET['id'])))."' title='{$main->lang['adm_delete_topic']}'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_delete.png' alt='{$main->lang['adm_delete_topic']}' /></a>
      " : "";
   }
   /**
   * Инициализация информации о сообщениях
   * 
   * @param boolean $show_first_post - выводить первую запись ?
   * @param integer $num_where - код формирования условия выборки
   * @return resourse
   */
   function showtopic_init_db($show_first_post, $num_where){
      global $main, $forum, $count_rows, $this_page;
      if(hook_check(__FUNCTION__)) return hook();
      $offset = ($this_page-1) * $forum['post_views_num'];
      if($offset<0) kr_http_ereor_logs(404);
      $limit=!is_ajax();
      switch($num_where){
         case "0": $where = "WHERE p.topic_id='".intval($_GET['id'])."'"; break;
         case "1": $where = "WHERE p.topic_id='".intval($_GET['id'])."' AND p.post_id>'".intval($_POST['last_post'])."'"; break;//posting
         case "2": $where = "WHERE p.topic_id='".intval($_GET['topic'])."' AND p.post_id>'".intval($_GET['id'])."' AND p.post_id<='".intval($_GET['last_post'])."'"; break;//delete post
         case "3": $where = "WHERE p.poster_name='".addslashes($_GET['user'])."' and (not p.post_tnx is null) "; break;//user-info gratitude
         case "4": $where = "WHERE p.poster_name='".addslashes($_GET['user'])."' "; break;//user-info posts
         case "5": $where = "WHERE p.post_id='".intval($_GET['id'])."' "; break;//show post
         default: $where = "";
      }
      $fields="SELECT p.topic_id, p.post_id, p.poster_id, p.poster_name, p.post_time, p.post_subject, p.post_text, p.ico, p.post_edit_time, p.post_edit_user, p.post_tnx, 
      u.uid, u.user_id, u.user_name, u.user_regdate, u.user_avatar, u.user_group, u.user_signature, u.user_posts, u.user_locality, u.user_birthday, u.user_tnx, u.user_country, u.user_email, 
      g.id, g.title, g.color, g.img {FIELDS}\n";
      $sql=$show_first_post?"(".$fields." FROM ".TOPICS." as t, ".POSTS." AS p LEFT JOIN
      ".USERS." AS u ON(p.poster_id=u.uid) LEFT JOIN
      ".GROUPS." AS g ON(u.user_group=g.id) {TABLES}
      WHERE p.topic_id='".intval($_GET['id'])."' and t.topic_first_post_fix='y' and p.topic_id=p.topic_id and p.post_id=t.topic_first_post_id) \n union \n":"";
      $sql.=($show_first_post?"(":"").$fields." FROM ".POSTS." AS p LEFT JOIN
      ".USERS." AS u ON(p.poster_id=u.uid) LEFT JOIN
      ".GROUPS." AS g ON(u.user_group=g.id) {TABLES} {$where} {WHERES}
      ORDER BY p.post_id".($limit?" LIMIT {$offset}, {$forum['post_views_num']}":"")."".($show_first_post?")":"");
      $dbr = $main->db->sql_query($sql, __FUNCTION__);
      $count_rows = $main->db->sql_numrows($dbr);
      return $dbr;
   }
   /**
   * сформировать шаблон для вывода сообщений
   * 
   * @param mixed $showtopicmode
   */
   function showtopic_postrow($showtopicmode){
      global $main, $forum, $template, $supervision, $count_rows;
      if(hook_check(__FUNCTION__)) return hook();
      //if(!is_ajax()){
      $template->get_tpl('forum/show_post', 'show_post');
      $match = "";
      $template->get_subtpl(array(
            array('get_index' => 'show_post', 'new_index' => 'POST_CONTENT', 'selector' => ' post row')
         ),array('start' => '{', 'end' => '}'));
      if(!$showtopicmode) $template->template['show_post'] = preg_replace('/<\!--begin\sshowonepost-->(.+?)<\!--end\sshowonepost-->/si', '', $template->template['show_post']);
      $pagenums = "";
      if($count_rows==$forum['post_views_num'] OR isset($_GET['page'])){
         list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".POSTS." WHERE topic_id='".intval($_GET['id'])."'"));
         $pages = ceil($count/$forum['post_views_num']);
         $pagenums = pages_forum($count, $forum['post_views_num'], array('module' => $main->module, 'do' => 'showtopic', 'id' => intval($_GET['id'])));
      } else $pages = 1;
      /*} else {        
      preg_match('/<\!--begin\spost\srow-->(.+?)<\!--end\spost\srow-->/si', file_get_contents("".TEMPLATE_PATH."{$main->tpl}/forum/show_post.tpl"), $match);
      $template->cache['POST_CONTENT']=$match[1];
      $pages = $pagenums = 1;
      }*/
      return array($pages, $pagenums);
   }
   /**
   * основная функция показа темы
   * 
   * @param mixed $show_first_post
   */
   function showtopic_main($show_first_post=true){
      global $main, $this_page, $topic, $forum, $template, $_last_post, $count_rows;
      if(hook_check(__FUNCTION__)) return hook();
      $dbresult=showtopic_init_db($show_first_post, 0);
      $topic = $main->db->sql_fetchrow($main->db->sql_query("SELECT t.topic_id, t.vote_id, t.forum_id, t.topic_title, t.topic_views, t.topic_status, t.topic_first_post_id, t.topic_first_post_fix, f.forum_id, f.forum_name, f.forum_status, fs.uid, fs.sending,a.tree FROM ".FORUMS." AS f, ".CAT_FORUM." AS a,".TOPICS." AS t LEFT JOIN ".FORUM_SUBSCRIBE." AS fs ON(fs.uid={$main->user['uid']} and fs.topic_id=t.topic_id) WHERE t.topic_id='".intval($_GET['id'])."' AND t.forum_id=f.forum_id and a.cat_id=f.cat_id"));
      open_forum_moder($topic['tree'],$topic['forum_id']);
      if($count_rows>0){
         $_SESSION['topic_list'] = (!isset($_SESSION['topic_list'])) ? $_GET['id']."=".kr_time().",":$_SESSION['topic_list'].$_GET['id']."=".kr_time().",";
         $content = ""; 
         if(isset($topic['sending'])&&$topic['sending']=='y') sql_update(array('sending'=>'n'),FORUM_SUBSCRIBE," topic_id={$topic['topic_id']} and uid={$main->user['uid']}");
         set_meta_value(array($topic['topic_title'], $topic['forum_name'], $main->title));
         forum_open_access_forum($topic['tree'],$topic['forum_id']);
         list($pages, $pagenums)=showtopic_postrow(true);
         if(check_access_forum(accView,accRead)){
            $inum = !isset($_POST['num']) ? ((1*$this_page>1) ? ($forum['post_views_num']*($this_page-1))+1 : 1*$this_page) : $_POST['num'];
            if($this_page==1&&!empty($topic['vote_id'])){
               $_POST['voteblock']="true";
               $voting=forum_more_voting($topic['vote_id']);
            } else $voting="";
            $content .= forum_showtopic_gen_posts($dbresult);
            sql_update(array('topic_views' => $topic['topic_views']+1), TOPICS, "topic_id='".intval($_GET['id'])."'");
            //Если тема закрыта – удаляем быстрый ответ
            if($topic['topic_status']!='0' OR (!check_access_forum(accPost)) OR $topic['forum_status']!='0') $template->template['show_post'] = preg_replace('/<\!--begin\squick_reply-->(.+?)<\!--end\squick_reply-->/si', '', $template->template['show_post']);

            echo in_hide('last_post', $_last_post).
            in_hide('count_post', $count_rows).
            in_hide('pages', $pages).
            in_hide('page', $this_page).
            in_hide('num', $inum);
            if(!is_guest()){
               $subscribe="<a class='subscribe' onclick='return subscribe(this);' href='".$main->url(array('module' => $main->module, 'do' => ($topic['sending']!=""?'unsubscribe':'subscribe'), 'id' => intval($_GET['id'])))."'>".($topic['sending']!=""?$main->lang['topic_remove_subs']:$main->lang['topic_subs'])."</a>";
               $content.="<script type=\"text/javascript\">
               //<![CDATA[
               function subscribe(obj){
               haja({action:obj.href, animation:true,dataType:'json'}, {ajax:true}, {onendload:function(data){
               \$('.subscribe').text(data.caption);
               \$('.subscribe').attr('href',data.href.replaceAll('&amp;', '&'));
               }});
               return false;
               }
               //]]>
               </script>";
            } else $subscribe="";
            load_forum_category();
            $superuser = check_access_forum(accModerator);
            $topic_active = $topic['topic_status']=='0' AND $topic['forum_status']=='0';
            $topic_posting = $topic_active AND check_access_forum(accPost);
            showtopic_show_tpl_topic(array(
                  'PAGINATION'            => $pagenums,
                  'POST_CONTENT'          => $content,
                  'FORUM_VOTE'            => $voting,
                  'PAGE_NUMBER'           => "<span id='page_number'>".preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array(isset($_GET['page'])?$_GET['page']:1, $pages), $main->lang['numberpage'])."</span>",
                  'POST_NEW_TOPIC'        => ($superuser OR check_access_forum(accWrite))?"<a class='forum_button'  href='".$main->url(array('module' => $main->module, 'do' => 'newtopic', 'id' => $topic['forum_id']))."' title='{$main->lang['newtopic']}'><span><img style='margin-right: 5px;' class='icon icon-topic icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['newtopic']}</a>":"",   
                  'POST_REPLY_TOPIC'      => ($topic_active OR $superuser)?(($superuser OR check_access_forum(accPost))?"<a class='forum_button'  href='".$main->url(array('module' => $main->module, 'do' => 'newpost', 'id' => $topic['topic_id']))."' title='{$main->lang['newpost']}'><span><img style='margin-right: 5px;' class='icon icon-post icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['newpost']}</a>":""):"<a class='forum_button forum_button3'  href='javascript:void(0)' title='{$main->lang['topic_closed']}'><span><img style='margin-right: 5px;' class='icon icon-close icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['topic_closed']}</a>",
                  'S_AUTH_LIST'           => forum_topic_access_list(),
                  'SMILES_BOX'            => ($topic['topic_status']=='0' AND $topic['forum_status']=='0')?forum_smilebox():"",
                  'QUICKREPLY_ACTION'     => ($topic_posting)?$main->url(array('module' => $main->module, 'do' => 'sendnewpost', 'id' => $_GET['id'])):"",
                  'QUICKREPLY_BOX'        => ($topic_posting)?"<div class='editorbox'>".editor('message', '160px', '99%')."</div>":"",
                  'QUICKREPLY_BUTTON'     => ($topic_posting)?send_button(" onclick=\"sendpost('".$main->url(array('module' => $main->module, 'do' => 'sendnewpost', 'id' => $_GET['id']))."'); return false;\""):"",
                  'S_TOPIC_ADMIN'         => forum_topic_admin_button(),
               ));
         } else  meta_refresh(3, $main->url(array('module' => $main->module)), $main->lang['not_view_this_forum']);
      } else warning($main->lang['nosearch_topic']);
   }

   function showtopic_show_tpl_topic($appendkey = array()){
      global $main, $this_page, $topic, $forum, $template, $_last_post, $count_rows;
      if(hook_check(__FUNCTION__)) return hook();
      load_forum_category();
      $new_title=gen_forum_breadcrumb($topic['forum_id'],$topic['forum_name'], $topic['tree']);
      $template->set_tpl(array('bread_crumbs' => bcrumb::bread_crumb($new_title)), 'index');
      $newkey=array(
         'OPEN_TABLE'            => open(true),
         'CLOSE_TABLE'           => close(true),
         'LOAD_TPL'              => $main->tpl,
         'PAGINATION'            => '',
         'SMILES_BOX'            => "",
         'FORUM_NAME'            => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$topic['forum_name']}'>{$topic['forum_name']}</a>",
         'L_INDEX'               => "<a class='sys_link' href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
         'FORUM_BREAD_CRUMB'     => '',
         'TOPIC_SUBSCRIBE'       => !is_guest()?$subscribe="<a class='subscribe' onclick='return subscribe(this);' href='".$main->url(array('module' => $main->module, 'do' => ($topic['sending']!=""?'unsubscribe':'subscribe'), 'id' => intval($topic['topic_id'])))."'>".($topic['sending']!=""?$main->lang['topic_remove_subs']:$main->lang['topic_subs'])."</a>":"",
         'TOPIC_TITLE'           => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id']))."' title='{$topic['topic_title']}'>{$topic['topic_title']}</a>",
         'POST_NEW_TOPIC'        => "",
         'POST_REPLY_TOPIC'      => '',
         'QUICK_REPLY'           => '',
         'S_AUTH_LIST'           => '',

         'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
         'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
         'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
         'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",

         'QUICKREPLY_ACTION'     => '',
         'QUICKREPLY_BOX'        => "",
         'QUICKREPLY_BUTTON'     => "",
         'AUTHOR_POST'           => $main->lang['author_post'],
         'MESSAGE_POST'          => $main->lang['message_post'],
         'PAGE_NUMBER'           => '',
         'POST_CONTENT'          => '',
         'S_TOPIC_ADMIN'         => '',
         'FORUM_VOTE'            => '',
         'MODERATORS'            => forum_list_moderators($topic['tree'],$topic['forum_id'],true,true,false)
      );
      foreach ($appendkey as $key => $value) {$newkey[$key]=$value;}
      $template->set_tpl(hook_set_tpl($newkey,__FUNCTION__), 'show_post', array('start' => '{', 'end' => '}'));  
      $template->tpl_create(false, 'show_post');
   }
   /**
   * Обработка удаления сообщения
   * 
   */
   function showtopic_deletepost(){
      global $main, $topic, $this_page, $count_rows;
      if(hook_check(__FUNCTION__)) return hook();
      $content="";
      $dbresult=showtopic_init_db(false, 2);
      if($count_rows>0){
         $topic = $main->db->sql_fetchrow($main->db->sql_query("SELECT t.topic_id, t.vote_id, t.forum_id, t.topic_title, t.topic_views, t.topic_status, t.topic_first_post_id, t.topic_first_post_fix, f.forum_id, f.forum_name, f.forum_status, fs.uid, fs.sending, p.post_id, p.topic_id,a.tree{FIELDS} FROM ".FORUMS." AS f, ".CAT_FORUM." AS a, ".POSTS." AS p,".TOPICS." AS t{TABLES} LEFT JOIN ".FORUM_SUBSCRIBE." AS fs ON(fs.uid={$main->user['uid']} and fs.topic_id=t.topic_id) WHERE p.post_id='".intval($_GET['id'])."' AND t.topic_id=p.topic_id AND t.forum_id=f.forum_id and a.cat_id=f.cat_id{WHERES}",__FUNCTION__));
         set_meta_value(array($topic['topic_title'], $topic['forum_name'], $main->title));
         forum_open_access_forum($topic['tree'],$topic['forum_id']);
         list($pages, $pagenums)=showtopic_postrow(false);
         if(check_access_forum(array(accView,accRead))){
            $content .= forum_showtopic_gen_posts($dbresult);
         }
      }
      return $content;
   }
   /**
   * выдача форматированного сообщения при добавлении
   * 
   */
   function showtopic_posting(){
      global $main, $this_page, $forum, $topic, $_last_post, $count_rows, $forum_access;
      if(hook_check(__FUNCTION__)) return hook();
      $topic = $main->db->sql_fetchrow($main->db->sql_query("SELECT t.topic_id, t.vote_id, t.forum_id, t.topic_title, t.topic_views, t.topic_status, t.topic_first_post_id, t.topic_first_post_fix, f.forum_id, f.forum_name, f.forum_status, fs.uid, fs.sending,a.tree {FIELDS} FROM ".FORUMS." AS f, ".CAT_FORUM." AS a,".TOPICS." AS t LEFT JOIN ".FORUM_SUBSCRIBE." AS fs ON(fs.uid={$main->user['uid']} and fs.topic_id=t.topic_id) {TABLES} WHERE t.topic_id='".intval($_GET['id'])."' AND t.forum_id=f.forum_id and a.cat_id=f.cat_id {WHERES}",__FUNCTION__));
      open_forum_moder($topic['tree'],$topic['forum_id']);
      $dbresult=showtopic_init_db(false, 1);
      $content = "";
      if($count_rows+$_POST['count_post']>$forum['post_views_num']) $content .= "<script type='text/javascript'>location.href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $_GET['id']))."';</script>";
      if($count_rows>0){
         set_meta_value(array($main->lang['showonepost'], $topic['topic_title'], $topic['forum_name'], $main->title));
         list($pages, $pagenums)=showtopic_postrow(false);
         if(check_access_forum(array(accView,accRead))){
            $inum = !isset($_POST['num']) ? ((1*$this_page>1) ? ($forum['post_views_num']*($this_page-1))+1 : 1*$this_page) : $_POST['num'];
            $content .= forum_showtopic_gen_posts($dbresult);
            if(!empty($_SESSION['forum_subs'])) {
               $url=$main->url(array('module' => $main->module, 'do' => 'mail_subs', 'id' => intval($_GET['id'])));
               $content.="<link type='text/css' href='{$url}' rel='stylesheet'>";
            }
            $content .=
            "<script type='text/javascript'>
            $$('count_post').value = parseInt($$('count_post').value) + {$count_rows};
            $$('num').value = '{$inum}';
            $$('last_post').value = '{$_last_post}';
            forum_empty_editor();
            </script>";
         }
      }
      return $content;
   }

   /**
   * показать ождно сообщение
   * 
   */
   function showtopic_showpost(){
      global $main, $this_page, $forum, $topic, $_last_post, $count_rows;
      if(hook_check(__FUNCTION__)) return hook();
      $topic = $main->db->sql_fetchrow($main->db->sql_query("SELECT t.topic_id, t.vote_id, t.forum_id, t.topic_title, t.topic_views, t.topic_status, t.topic_first_post_id, t.topic_first_post_fix, f.forum_id, f.forum_name, f.forum_status, fs.uid, fs.sending, p.post_id, p.topic_id,a.tree{FIELDS} FROM ".FORUMS." AS f, ".CAT_FORUM." AS a, ".POSTS." AS p,".TOPICS." AS t{TABLES} LEFT JOIN ".FORUM_SUBSCRIBE." AS fs ON(fs.uid={$main->user['uid']} and fs.topic_id=t.topic_id) WHERE p.post_id='".intval($_GET['id'])."' AND t.topic_id=p.topic_id AND t.forum_id=f.forum_id and a.cat_id=f.cat_id{WHERES}",__FUNCTION__));
      open_forum_moder($topic['tree'],$topic['forum_id']);
      $dbresult=showtopic_init_db(false, 5);
      $content = ""; 
      if($count_rows>0){
         set_meta_value(array($main->lang['showonepost'], $topic['topic_title'], $topic['forum_name'], $main->title));
         forum_open_access_forum($topic['tree'],$topic['forum_id']);
         list($pages, $pagenums)=showtopic_postrow(false);
         if(check_access_forum(array(accView,accRead))){
            $inum = !isset($_POST['num']) ? ((1*$this_page>1) ? ($forum['post_views_num']*($this_page-1))+1 : 1*$this_page) : $_POST['num'];
            $content .= forum_showtopic_gen_posts($dbresult);
         }
         showtopic_show_tpl_topic(array(
               'POST_CONTENT'          => $content,
            ));
      } else warning($main->lang['nosearch_topic']);
   }
   /**
   * формируем список сообщений внизу, при цитирровании
   * 
   */
   function showtopic_quotepost_listposts($topic_id){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $forum['post_views_num']=5;
      $_GET['id'] = $topic_id;
      if(!is_ajax()) {
         open(); echo "<div id='topic_quote' style='visibility: hidden'>";
      } else hook_register('main::add2script','block_script',false);
      hook_register('hook_set_tpl','quotepost_hook_set_tpl', false);
      echo showtopic_main(false);
      if(!is_ajax()) {
         echo "</div>"; 
         close();
      ?>
      <script type="text/javascript">
         //<![CDATA[
         var strt='';
         $(document).ready(function(){
               var r=$('#topic_quote');
               function get_new(href){
                  $.post(href,{ajax:true},function(data){
                        r.html(data);
                        remove_control();
                        r.css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0},1000);
                  });
               }
               function remove_control(){
                  $('.forumnum').find('a[href]').each(function(){
                        this.href=this.href.replace('showtopic','showtopica');
                  }).on('click',function(){
                        var hhref=this.href;
                        r.animate({opacity: 0.0}, 500,function(){get_new(hhref);});
                        return false;
                  });
               }
               remove_control();
               r.css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0},1000);
         });
         //]]>
      </script>
      <?php
      }
   }
   /**
   * Удаление управляющих элементов
   * 
   * @param mixed $keys
   * @param mixed $namespace
   */
   function quotepost_hook_set_tpl($keys, $namespace = ''){
      if(hook_check(__FUNCTION__)) return hook();
      if($namespace=='forum_showtopic_gen_posts'){
         $removes=array('postrow.QUOTE_IMG','postrow.EDIT_IMG','postrow.DELETE_IMG','postrow.IP_IMG',
            'postrow.REPORT','postrow.TNX','postrow.POSTER_NAME','postrow.PROFILE_IMG');
         foreach ($removes as $value) $keys[$value]="";
      }
      if($namespace=='showtopic_show_tpl_topic'){
         $removes=array('QUICKREPLY_BUTTON','S_TOPIC_ADMIN','FORUM_VOTE','MODERATORS','MENU_PROFILE','MENU_SEARCH','MENU_USERS',
            'MENU_LOGOUT','S_AUTH_LIST','POST_NEW_TOPIC','POST_REPLY_TOPIC','TOPIC_SUBSCRIBE',
            'QUICKREPLY_ACTION','QUICKREPLY_BOX','QUICKREPLY_BUTTON','SMILES_BOX');
         foreach ($removes as $value) $keys[$value]="";
      }
      return $keys;
   }
   /**
   * Блокируем добавление скриптов
   * 
   * @param mixed $text
   * @param mixed $link
   */
   function block_script($text, $link=true){
      if(hook_check(__FUNCTION__)) return hook();
   }

   $this_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
?>