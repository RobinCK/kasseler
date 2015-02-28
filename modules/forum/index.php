<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");

   global $main,  $tpl_create;
   if(!is_ajax()) main::add2link("".TEMPLATE_PATH."{$main->tpl}/forum/style.css");

   if(!is_ajax()) main::add2script("modules/{$main->module}/script.js");

   main::required("modules/{$main->module}/function.php");
   main::required("modules/{$main->module}/templates.php");
   // подключаем контроль доступа
   main::required("modules/{$main->module}/accinfo.php"); 
   forum_full_access_load();
   if(empty($_SESSION['forum_read'])||empty($_SESSION['forum_read']['user'])||(isset($_SESSION['forum_read']['user']) AND $_SESSION['forum_read']['user']!=$main->user['uid']))  forum_load_read_info();
   main::init_function('forumtools');
   global $muid,$groups;

   global $show_first_post;
   $show_first_post=true;
   function user_tnx(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(!isset($_POST['post_id'])) return false;
      $tnxs = $main->db->sql_fetchrow($main->db->sql_query("SELECT poster_id, poster_name, post_tnx FROM ".POSTS." WHERE post_id='".intval($_POST['post_id'])."'"));
      $tnx = ($tnxs['post_tnx']=="") ? "" : "<br /><br /><fieldset class='post_tnx' style='margin-left: 30px;' title='{$main->lang['tnxplay']}'><legend class='post_tnx'>{$main->lang['tnxplay']}</legend>{$tnxs['post_tnx']}</fieldset>";
      if($main->user['uid']=='-1'){
         echo "{$tnx}<script language='javascript'>alert('{$main->lang['onlyusertnx']}');</script>";
      } else {        
         if (mb_strpos($tnxs['post_tnx'], ">{$main->user['user_name']}<")!==false){
            echo "{$tnx}<script language='javascript'>alert('{$main->lang['onlyonetnxpost']}')</script>";
         } else {
            if($tnxs['poster_id']==$main->user['uid']){
               echo "{$tnx}<script language='javascript'>alert('{$main->lang['tnxonlyotheruser']}')</script>";
            } else {
               $main->db->sql_query("UPDATE ".POSTS." SET post_tnx='{$tnxs['post_tnx']} <a href=\"index.php?module=account&amp;do=user&amp;id=".case_id($main->user['user_id'], $main->user['uid'])."\">{$main->user['user_name']}</a>' WHERE post_id='".intval($_POST['post_id'])."'");
               $main->db->sql_query("UPDATE ".USERS." SET user_tnx=user_tnx+1 WHERE uid='{$tnxs['poster_id']}'");
               echo "<br /><br /><fieldset class='post_tnx' style='margin-left: 30px;' title='{$main->lang['tnxplay']}'><legend class='post'>{$main->lang['tnxplay']}</legend>{$tnxs['post_tnx']} <a href=\"index.php?module=account&amp;do=user&amp;id=".case_id($main->user['user_id'], $main->user['uid'])."\">{$main->user['user_name']}</a></fieldset>";
            }
         }
      }
   }

   function report(){
      global $main, $patterns;  
      if(hook_check(__FUNCTION__)) return hook();
      if(!is_user()){echo json_encode(array('type' => 'error', 'text' => $main->lang['reposrtsendonlyuser'])); kr_exit();}
      $row = $main->db->sql_fetchrow($main->db->sql_query("SELECT p.post_id, p.topic_id, p.forum_id, p.post_subject, t.topic_id, t.topic_title FROM ".POSTS." AS p LEFT JOIN ".TOPICS." AS t ON(t.topic_id=p.topic_id) WHERE p.post_id='".intval($_POST['post_id'])."'"));
      list($ck) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".REPORTS." WHERE post_id='{$row['post_id']}'"));
      if($ck>0) {echo json_encode(array('type' => 'error', 'text' => $main->lang['report_is_checked'])); kr_exit();}
      $result = $main->db->sql_query("SELECT a.uid, a.id, u.uid, u.user_name, u.user_email FROM ".ACC." AS a LEFT JOIN ".USERS." u ON(a.uid=u.uid) WHERE a.id='{$row['forum_id']}'");
      $message = preg_replace(
         array(
            '/\{SITE\}/is',
            '/\{TOPIC\}/is',
            '/\{USER\}/is',
            '/\{POST\}/is'
         ), 
         array(
            "<a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['home_title']}</a>",            
            "<a href='http://".get_host_name()."/index.php?module=forum&amp;do=showtopic&amp;id={$row['topic_id']}'>{$row['topic_title']}</a>",            
            "<a href='http://".get_host_name()."/index.php?module=account&amp;do=user&amp;id=".case_id($main->user['user_id'], $main->user['uid'])."'>{$main->user['user_name']}</a>",
            "<a href='http://".get_host_name()."/index.php?module=forum&amp;do=showpost&amp;id={$row['post_id']}'>{$row['post_subject']}</a>"
         ), 
         $patterns['post_report']
      );
      sql_insert(array('post_id' => $row['post_id'], 'user_id' => $main->user['uid']), REPORTS);
      if($main->db->sql_numrows($result)>0){
         while(($rows = $main->db->sql_fetchrow($result))){
            send_mail($rows['user_email'], $rows['user_name'], $main->config['admin_mail'], $main->user['user_name'], "Report", $message);
         }
      } else send_mail($main->config['admin_mail'], "Administrator", $main->user['user_email'], $main->user['user_name'], "Report", $message);
      ob_clean();
      echo json_encode(array('type' => 'error', 'text' => $main->lang['reposrtsend']));
      kr_exit();
   }

   function global_upload_attach(){
      global $forum;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('attache');    
      upload_attach($forum);    
   }

   function lastpost(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_GET['id'])){
         $topic_id=intval($_GET['id']);
         $last_read=isset($_SESSION['forum_read'])?(isset($_SESSION['forum_read']['info'][$topic_id])?$_SESSION['forum_read']['info'][$topic_id]:$_SESSION['forum_read']['info'][0]):0;
         $result = $main->db->sql_query("SELECT t.topic_id,t.topic_first_post_fix,
            (SELECT count(p.post_id) FROM ".POSTS." AS p WHERE p.topic_id=t.topic_id and p.post_id<={$last_read}) as count_read,\n
            (SELECT count(p.post_id) FROM ".POSTS." AS p WHERE p.topic_id=t.topic_id and p.post_id>{$last_read}) as count_new\n
            FROM ".TOPICS." AS t  WHERE t.topic_id='".intval($_GET['id'])."'");
         if($main->db->sql_numrows($result)>0){
            $row = $main->db->sql_fetchrow($result);
            $count=empty($row['count_read'])?$row['count_new']:(empty($row['count_new'])?$row['count_read']:$row['count_read']+1);
            $pages = ceil(($count)/$forum['post_views_num']);
            $entry=(($row['topic_first_post_fix']=='n') OR $pages==1)?$count:$count+1;
            if(!is_ajax()) {
               header ('HTTP/1.1 301 Moved Permanently'); 
               if($pages>1) redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $_GET['id'], 'page' => $pages))."#entry".($entry));
               else redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $_GET['id']))."#entry".($entry));
            } else {
               $_GET = array(
                  'module' => $main->module,
                  'do' => 'showtopic',
                  'id' => $_GET['id'],
                  'page' => $pages,
               );
               main::required("modules/{$main->module}/showtopic.php");
               showtopic_main();
            }
         } else kr_http_ereor_logs(404);
      } else redirect(MODULE);
   }

   function closetopic(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      list($forum_id, $topic_status, $tree) = $main->db->sql_fetchrow($main->db->sql_query("SELECT t.forum_id, t.topic_status, c.tree FROM ".TOPICS." AS t, ".FORUMS." AS f, ".CAT_FORUM." AS c WHERE t.topic_id='".intval($_GET['id'])."' and f.forum_id=t.forum_id and c.cat_id=c.cat_id"));
      forum_open_access_forum($tree, $forum_id);
      if(check_access_forum(accModerator) AND !empty($forum_id)){
         sql_update(array('topic_status' => ($topic_status==0)?'1':'0'), TOPICS, "topic_id='".intval($_GET['id'])."'");
         if(!is_ajax()) redirect(BACK);
      } else redirect(MODULE);
   }

   function deletepost(){
      global $main, $forum, $type_select,$muid;
      if(hook_check(__FUNCTION__)) return hook();
      //Проверяем, существует ли пост
      $post = forum_post_info($_GET['id']);
      if($post===FALSE) redirect(MODULE);
      //Узнаем есть ли право на удаление
      $ht = ob_get_contents(); ob_get_clean();
      forum_open_access_forum($post['tree'], $post['forum_id']);
      if(check_access_forum(accDelete)){
         //Удаляем пост
         delete_points($main->points['forum_topic'],$_GET['id'],$main->module);
         $main->db->sql_query("DELETE FROM ".POSTS." WHERE post_id='{$post['post_id']}'");
         //Удаляем прикрепленные
         if(file_exists($forum['directory'].$post['post_id'])){
            $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$forum['directory']}{$post['post_id']}/%'");
            remove_dir($forum['directory'].$post['post_id']);
         }
         //Отнимаем у пользователя 1 пост
         update_posts($post['poster_id'], "-");
         fix_topic_info($post['topic_id']);
         $topic=forum_topic_info($post['topic_id']);
         if($topic['topic_replies']==0){
            //Если мы удалили последнее сообщение темы, то удаляем и саму тему 
            $main->db->sql_query("DELETE FROM ".TOPICS." WHERE topic_id='{$post['topic_id']}'");
            fix_forum_info($post['forum_id']);
            $href_redirect=$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $post['forum_id']));
            if(is_ajax()) {
               echo json_encode(array(
                     'status'        => 'redirect',
                     'data'          => str_replace('&amp;', '&', $href_redirect),
                     'page'          => '','count_in_page' => '','id_post'       => '','num'           => ''));
               kr_exit();
            } else redirect($href_redirect);
         }
         //Обновляем информацию о форуме 
         fix_forum_info($post['forum_id']);
         //Получаем количество страниц в теме
         $page = ceil($topic['topic_replies']/$forum['post_views_num']);
         if(!is_ajax()) redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $post['topic_id'], 'page' => (($_GET['page']>$page AND $_GET['page']!=1) ? $page : $_GET['page']))));
         else {
            $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
            $post_in_page=$forum['post_views_num'];
            $offset = ($num-1) * $post_in_page;
            $next_id=0;
            $n=0;
            $main->db->sql_query("select max(f.post_id) as mx_post_id, count(f.post_id) as count_post_id from (select post_id from ".POSTS." where topic_id={$post['topic_id']} order by post_id limit {$offset},{$post_in_page} ) AS f");
            if($main->db->sql_numrows()>0){
               $row=$main->db->sql_fetchrow();
               if(is_numeric($row['mx_post_id'])) $next_id= intval($row['mx_post_id']);
            }
            $page = isset($_GET['page'])?intval($_GET['page']):1;
            $json = array(
               'count_in_page' => $forum['post_views_num'],
               'page'          => $page,
               'id_post'       => intval($_GET['id']),
               'num'           => '',
            );
            list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".POSTS." WHERE topic_id='".intval($post['topic_id'])."'"));
            $count_pages = ceil($count / $forum['post_views_num']);
            if ($next_id!=0){
               $main->db->sql_query("select max(post_id) as mx,count(post_id) as cn from ".POSTS." where topic_id={$post['topic_id']}");
               $row=$main->db->sql_fetchrow();
               $last_post=$row['mx'];
               $count_post=$row['cn'];
               $_GET = array_merge($_GET, array('id' => $post['post_id'],'topic'=>$post['topic_id'],  'last_post'=>$next_id, 'count_post'=>$count_post));
               main::required("modules/{$main->module}/showtopic.php");
               $content = showtopic_deletepost();
               //cal pages
               $pagenums = "";
               if($count>$forum['post_views_num']) $pagenums = pages_forum($count, $forum['post_views_num'], array('module' => $main->module, 'do' => 'showtopic', 'id' => $post['topic_id']));
               ///
               $json = array_merge($json, array(
                     'status'        => 'content',
                     'data'          => $content,
                     'num'           => $pagenums,
                  ));
            } else {
               $main->db->sql_query("SELECT p.post_id FROM ".POSTS." AS p WHERE p.topic_id = '{$post['topic_id']}' LIMIT {$offset}, {$post_in_page} ");
               if ($main->db->sql_numrows()==0) $json = array_merge($json, array(
                        'status'        => 'redirect',
                        'data'          => str_replace('&amp;', '&', $main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $post['topic_id'], 'page' => $page-1))),
                     ));
               else {$json = array_merge($json, array(
                        'status'        => 'ok',
                        'data'          => '',
                     ));}
            }
            $json['page_number'] = preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array($page, $count_pages), $main->lang['numberpage']);
            $json['count_pages'] = $count_pages;
            echo json_encode($json);
            kr_exit();
         }
      } else kr_http_ereor_logs(403);
   }

   function topicdel(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      //Делаем выборку удаляемого топика
      $topic = forum_topic_info(intval($_GET['id']));
      if($topic===FALSE) redirect(MODULE);
      //Проверяем право удаления топика
      forum_open_access_forum($topic['tree'],$topic['forum_id']);
      if(check_access_forum(accModerator)){
         if(!empty($forum['trashforum'])){
            if($topic['forum_id']!=$forum['trashforum']){
               forum_topic_move($topic['topic_id'], $topic['forum_id'], $forum['trashforum']);
               redirect($main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id'])));
               kr_exit();
            }
         } 
         //Удаляем топик
         $main->db->sql_query("DELETE FROM ".TOPICS." WHERE topic_id='{$topic['topic_id']}'");
         //Делаем выборку удаляемых сообщений
         $posts_sql = $main->db->sql_query("SELECT post_id, poster_id FROM ".POSTS." WHERE topic_id='{$topic['topic_id']}'");
         //Перебираем удаляемые сообщения
         while(list($post_id, $poster_id) = $main->db->sql_fetchrow($posts_sql)){
            //Отнимаем у пользователя 1 пост
            update_posts($poster_id, "-");
            //Удаляем прикрепленные файлы 
            if(file_exists($forum['directory'].$post_id)){
               $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$forum['directory']}{$post_id}/%'");
               remove_dir($forum['directory'].$post_id);
            }
         }
         //Удаляем все сообщения удаляемого топика
         $main->db->sql_query("DELETE FROM ".POSTS." WHERE topic_id='{$topic['topic_id']}'"); 
         //Находим ID последнего сообщения форума
         list($forum_last_posts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT post_id FROM ".POSTS." WHERE forum_id='{$topic['forum_id']}' ORDER BY post_id DESC LIMIT 1"));
         //Обновляем информацию о форуме
         fix_forum_info($topic['forum_id']);
         redirect($main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id'])));
      } else redirect(MODULE);
   }

   function topicsplit($msg=""){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      //Делаем выборку топика
      $topic = forum_topic_info(intval($_GET['id']));
      //Проверяем права
      forum_open_access_forum($topic['tree'], $topic['forum_id']);
      if(check_access_forum(accModerator)){
         //Создаем массив форумов
         $result = $main->db->sql_query("SELECT forum_id, forum_name, cat_id FROM ".FORUMS." ORDER BY cat_id, forum_id");
         $forum_name = "";
         while(($rows_forum = $main->db->sql_fetchrow($result))){
            $forums_arr[$rows_forum['forum_id']] = $rows_forum['forum_name'];
            if($rows_forum['forum_id']==$topic['forum_id']) $forum_name = $rows_forum['forum_name'];
         }
         //Делаем выборку сообщений
         $result = $main->db->sql_query("SELECT post_id, poster_name, post_text, post_time, post_subject FROM ".POSTS." WHERE topic_id='{$topic['topic_id']}' ORDER BY post_time");
         //Объявляем нужные переменные
         $row = "postrow1"; $posts = "";
         //Перебираем результат
         while(list($post_id, $poster_name, $post_text, $post_time, $post_subject) = $main->db->sql_fetchrow($result)){
            $posts .= "<tr class='{$row}'><td valign='top' align='center' width='150'><b>{$poster_name}</b></td><td valign='top'>date: ".gmdate("d.m.Y H:i:s", $post_time)." {$main->lang['title']}: <a href='".$main->url(array('module' => $main->module, 'do' => 'showpost', 'id' => $post_id))."' title='{$main->lang['showonepost']}'>{$post_subject}</a><hr />".parse_bb($post_text)."</td><td align='center' ><input type='checkbox' name='posts[]' value='{$post_id}' /></td></tr>\n";
            $row = ($row=="postrow1") ? "postrow2" : "postrow1";
         }
         if(!empty($msg)) warning($msg);
         echo "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'><b>{$forum['forum_title']}</b></a> &raquo; <a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$forum_name}'><b>{$forum_name}</b></a> &raquo; <a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id']))."' title='{$topic['topic_title']}'><b>{$topic['topic_title']}</b></a>";
         open();
         $typesp=array(0=>$main->lang['topic_split_new'],1=>$main->lang['topic_split_append']);
         //Создаем форму
         echo "<form action='".$main->url(array('module' => $main->module, 'do' => 'send_topicsplit', 'id' => $topic['topic_id']))."' method='post'>".
         "<table width='700' cellspacing='1' cellpadding='3'>".
         "<tr><td>{$main->lang['case_new_forum']}:</td><td>".in_sels('forum_id', $forums_arr, 'select2', $topic['forum_id']," onchange='change_forum(this);'")."</td></tr>".
         "<tr><td width='180'>{$main->lang['topic_split_type']}:</td><td>".in_sels('type_split',$typesp, 'input_text',0," onchange='change_type(this);' ")."</td></tr>".
         "<tr id='newtop'><td width='180'>{$main->lang['newtopictitle']}:</td><td>".in_text('title', 'input_text')."</td></tr>".
         "<tr id='addtop' style='display:none'><td width='180'>{$main->lang['newtopictitle']}:</td><td>".in_sels('addto',array(), 'input_text')."</td></tr>".
         "</table><hr />".        
         in_hide('last_forum_id', $topic['forum_id']).
         "<table width='100%' class='cattable' cellspacing='1' cellpadding='3'>".
         "<tr><th>{$main->lang['author']}</th><th>{$main->lang['message']}</th><th>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th></tr>".$posts.
         "</table>".        
         "<div align='center'><br />".send_button()."</div>".
         "</form><br />";
         close();
         $href=$main->urljs(array('module' => $main->module, 'do' => 'get_theme'));
      ?>
      <script type="text/javascript">
         //<![CDATA[
         <?php echo "href_theme='{$href}';\n";    ?>
         function change_type(obj){
            if(obj.value==1){$('#newtop').hide();$('#addtop').show();} else {$('#newtop').show();$('#addtop').hide();}
         }
         function change_forum(obj){
            var id='addto';
            empty_chosen_select(id);
            $.post(href_theme,{ajax:true,forum_id:obj.value},function(data){
                  empty_chosen_select(id);
                  var n=0;
                  for(var i in data){
                     if($.isNumeric(i)) {$$(id).options[n] = new Option(data[i],i);n++;}
                  }
                  $('#' + id).trigger("liszt:updated");
               },'json');
         }
         $(document).ready(function(){
               change_forum($$('forum_id'));
         });

         //]]>
      </script>
      <?php

      } else redirect(MODULE);
   }

   function send_topicsplit(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      //Получаем инфу о топике
      $topic = forum_topic_info(intval($_GET['id']));
      //Проверяем права
      forum_open_access_forum($topic['tree'], $topic['forum_id']);
      if(check_access_forum(accModerator)){
         //Формируем ошибки
         if($_POST['type_split']==0) $msg = (empty($_POST['title'])) ? $main->lang['error_new_topic_title'] : "";
         else $msg = (empty($_POST['addto'])) ? $main->lang['topic_empty_appendto'] : "";
         $msg .= (!isset($_POST['posts']) OR count($_POST['posts'])==0) ? $main->lang['no_cese_split_post'] : "";
         $msg .= (isset($_POST['posts']) AND count($_POST['posts'])==$topic['topic_replies']+1) ? $main->lang['notcaseallpost'] : "";
         //Если нет ошибок… продолжаем
         if(empty($msg)){
            //Получаем ID и имя пользователя для создания новой темы
            list($poster_id, $poster_name) = $main->db->sql_fetchrow($main->db->sql_query("SELECT poster_id, poster_name FROM ".POSTS." WHERE post_id='{$_POST['posts'][0]}'"));
            if($_POST['type_split']==0){
               //Создаем новую тему
               $topic_id = $main->db->sql_nextid(sql_insert(array(
                        'forum_id'            => $_POST['forum_id'],
                        'topic_title'         => $_POST['title'],
                        'topic_poster'        => $poster_id,
                        'topic_time'          => time(),
                        'topic_poster_name'   => $poster_name,
                        'topic_first_post_id' => $_POST['posts'][0], //Временно устанавливаем ID первого поста
                        'topic_last_post_id'  => $_POST['posts'][count($_POST['posts'])-1], //Временно устанавливаем ID последнего поста
                        'topic_replies'       => count($_POST['posts'])-1
                     ), TOPICS));
            } else {
               $topic_id=intval($_POST['addto']);
               $main->db->sql_query("select * from ".TOPICS." where topic_id={$topic_id}");
               if($main->db->sql_numrows()==0) {$msg.=$main->lang['nosearch_topic'];topicsplit($msg); return true;}
            }
            //Обновляем данные перенесенных сообщений
            $main->db->sql_query("UPDATE ".POSTS." SET forum_id='{$_POST['forum_id']}', topic_id='{$topic_id}' WHERE post_id IN (".implode(',', $_POST['posts']).")");
            //Выполняем обновление первого и последнего постав новой темы
            fix_topic_info($topic_id);
            //Обновляем старую тему
            fix_topic_info(intval($_GET['id']));
            //Обновляем данные форума
            if($_POST['last_forum_id']!=$_POST['forum_id']) {
               //Если был выбран другой форум
               fix_forum_info($_POST['last_forum_id']);
               fix_forum_info($_POST['forum_id']);
            } else fix_forum_info($_POST['last_forum_id']);
            redirect($main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $_POST['forum_id'])));
         } else topicsplit($msg);        
      } else redirect(MODULE);
   }

   function topicmove(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      //Получаем данные темы
      $topic = forum_topic_info(intval($_GET['id']));
      if($topic===FALSE) redirect(MODULE);
      //Проверяем право на перемещение
      forum_open_access_forum($topic['tree'], $topic['forum_id']);
      if(check_access_forum(accModerator)){
         //Делаем выборку всех форумов
         list($selg, $gtitle) = forum_select_forums();
         //Создаем форму
         echo "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'><b>{$forum['forum_title']}</b></a> &raquo; <a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$topic['forum_name']}'><b>{$topic['forum_name']}</b></a> &raquo; <a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id']))."' title='{$topic['topic_title']}'><b>{$topic['topic_title']}</b></a>";
         //Открываем стилевую таблицу
         open();
         echo "<form action='".$main->url(array('module' => $main->module, 'do' => 'send_topicmove', 'id' => $topic['topic_id']))."' method='post'>".
         in_hide('set_forum_id', $topic['forum_id'])."<table align='center' width='100%' class='form'>".
         "<tr class='row_tr'><td class='form_text'>Case forum</td><td class='form_input'>".in_sels_group('forum_id', $selg, $gtitle, 'form_sel', $topic['forum_id'])."</td></tr>".
         "</table>".
         "<div align='center'><br />".send_button()."</div>".
         "</form>";
         //Закрываем стилевую таблицу
         close();
      } else redirect(MODULE);
   }

   function send_topicmove(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      //Получаем информацию о топике
      $topic = forum_topic_info(intval($_GET['id']));
      if($topic===FALSE) redirect(MODULE);
      list($forum_id, $topic_replies, $tree) = array($topic['forum_id'], $topic['topic_replies'], $topic['tree']);
      //Проверяем право на перемещение
      forum_open_access_forum($tree, $forum_id);
      if(check_access_forum(accModerator)){
         //Проверяем, был ли изменен форум
         if($_POST['set_forum_id']!=$_POST['forum_id']){
            forum_topic_move(intval($_GET['id']), $_POST['set_forum_id'], intval($_POST['forum_id']));
         }
         redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $_GET['id'])));
      } else redirect(MODULE);
   }

   function topicmark(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $result = $main->db->sql_query("SELECT topic_id, topic_last_post_id FROM ".TOPICS." WHERE forum_id='".intval($_GET['id'])."'");
      while(list($topic_id, $last_post_id) = $main->db->sql_fetchrow($result)){add_change_read($topic_id,$last_post_id);}
      forum_save_read_info();
      redirect($main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => intval($_GET['id']))));
   }

   function unanswered(){    
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $lastVisit = (isset($_SESSION['lastVisit']) AND !empty($_SESSION['lastVisit'])) ? strtotime($_SESSION['lastVisit'] ) : time();
      $search_key = crc32_integer($lastVisit);
      forum_save_read_info();
      $main->db->sql_query("DELETE FROM ".FORUM_SEARCH." WHERE `key` = '{$search_key}'");
      $main->db->sql_query("DELETE FROM ".FORUM_KEYS." WHERE `key` = '{$search_key}'");
      $whf=isset($_SESSION['forum_read']['info'][0])?" and t.topic_last_post_id>{$_SESSION['forum_read']['info'][0]} ":"";
      $main->db->sql_query("insert into ".FORUM_SEARCH." (`key`,topic_id, `time`,keywords)
         select '{$search_key}',t.topic_id,'".time()."','' from ".TOPICS." t, ".FORUM_READ." r
         where r.uid={$main->user['uid']} {$whf} AND NOT r.read_info LIKE concat('%', t.topic_id, '=', t.topic_last_post_id, ';%')");
      if($main->db->sql_affectedrows()>0)  sql_insert(array('key' => $search_key, 'query' => '-', 'ignore' => ''), FORUM_KEYS);
      redirect($main->url(array('module' => $main->module, 'do' => 'search', 'id' => $search_key)));
   }

   function newposts(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $search_key = crc32_integer('newposts');
      $main->db->sql_query("DELETE FROM ".FORUM_SEARCH." WHERE `key` = '{$search_key}'");
      $main->db->sql_query("DELETE FROM ".FORUM_KEYS." WHERE `key` = '{$search_key}'");
      $result = $main->db->sql_query("SELECT t.topic_id FROM ".TOPICS." AS t WHERE t.topic_replies='0'");
      if($main->db->sql_numrows($result)>0){
         $insert = "INSERT INTO `".FORUM_SEARCH."` VALUES \n";
         while(($row = $main->db->sql_fetchrow($result))) $insert .= "(NULL, '{$search_key}', '{$row['topic_id']}', '".time()."', ''),\n";
         $insert = mb_substr($insert, 0 , mb_strlen($insert)-2).";";        
         $main->db->sql_query($insert);        
         sql_insert(array('key' => $search_key, 'query' => '-', 'ignore' => ''), FORUM_KEYS);
      }
      redirect($main->url(array('module' => $main->module, 'do' => 'search', 'id' => $search_key)));
   }

   function egosearch(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $search_key = crc32_integer($main->user['user_name']);
      $main->db->sql_query("DELETE FROM ".FORUM_SEARCH." WHERE `key` = '{$search_key}'");
      $main->db->sql_query("DELETE FROM ".FORUM_KEYS." WHERE `key` = '{$search_key}'");
      $result = $main->db->sql_query("SELECT t.topic_id FROM ".TOPICS." AS t WHERE UPPER(t.topic_poster_name)='".mb_strtoupper($main->user['user_name'])."'");
      if($main->db->sql_numrows($result)>0){
         $insert = "INSERT INTO `".FORUM_SEARCH."` VALUES \n";
         while(($row = $main->db->sql_fetchrow($result))) $insert .= "(NULL, '{$search_key}', '{$row['topic_id']}', '".time()."', ''),\n";
         $insert = mb_substr($insert, 0 , mb_strlen($insert)-2).";";
         $main->db->sql_query($insert);        
         sql_insert(array('key' => $search_key, 'query' => '-', 'ignore' => ''), FORUM_KEYS);
      }
      redirect($main->url(array('module' => $main->module, 'do' => 'search', 'id' => $search_key)));
   }
   function topic_subscribe(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(!is_guest()){
         sql_insert(array('uid'=>$main->user['uid'],'topic_id'=>intval($_GET['id'])),FORUM_SUBSCRIBE);
      }
      if(is_ajax()) {
         $json=array('caption'=>$main->lang['topic_remove_subs'],
            'href'=>$main->url(array('module' => $main->module, 'do' => 'unsubscribe', 'id' => intval($_GET['id']))));
         echo json_encode($json);
         kr_exit();
      } else  redirect($main->ref);
   }
   function topic_unsubscribe(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if(!is_guest()){
         $main->db->sql_query("delete from ".FORUM_SUBSCRIBE." where uid={$main->user['uid']} and topic_id=".intval($_GET['id']));
      }
      if(is_ajax()) {
         $json=array('caption'=>$main->lang['topic_subs'],
            'href'=>$main->url(array('module' => $main->module, 'do' => 'subscribe', 'id' => intval($_GET['id']))));
         echo json_encode($json);
         kr_exit();
      } else redirect($main->ref);
   }
   if(!is_ajax()){
   ?>
   <script type="text/javascript">
      //<![CDATA[
      function forum_empty_editor(){
         $$('message').value = '';
         if(window['init_tiny_mce']!=undefined){for(i=0;i<tinyMCE.editors.length;i++){tinyMCE.editors[i].setContent('');}}
      }
      //]]>
   </script>
   <?php
   }
   /**
   * Отметить все форумы как прочитанные
   * 
   */
   function  forum_markallread(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("select max(post_id) from ".POSTS);
      if($main->db->sql_numrows()>0){
         list($max_post)=$main->db->sql_fetchrow();
      } else $max_post=0;
      $_SESSION['forum_read']['info']=array(0=>$max_post);
      $_SESSION['forum_read']['change']=array(0=>$max_post);
      forum_save_read_info(false);
      redirect($main->url(array('module' => $main->module)));
   }
   /**
   * AJAX запрос на список тем по выбранному форуму
   * 
   */
   function forum_get_theme(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $return=array();
      if(is_ajax()){
         if(isset($_POST['forum_id'])){
            $forum_id=intval($_POST['forum_id']);
            $main->db->sql_query("select topic_id,topic_title from ".TOPICS." where forum_id={$forum_id}");
            if($main->db->sql_numrows()>0){
               while ((list($topic_id,$topic_title)=$main->db->sql_fetchrow())){
                  $return[$topic_id]=$topic_title;
               }
            }
         }
      }
      echo json_encode($return);
      exit;
   }

   function switch_module_forum(){
      global $database,$main;
      if(hook_check(__FUNCTION__)) return hook();
      if(intval($database['revision'])>=810){
         //if(isset($_SESSION['forum_read']['time']) AND (time()-$_SESSION['forum_read']['time'])>3600) forum_save_read_info();
         if(isset($_SESSION['forum_read'])) forum_save_read_info();
         if(isset($_GET['do'])){
            switch($_GET['do']){
               case "unanswered": unanswered(); break;
               case "newposts": newposts(); break;
               case "egosearch": egosearch(); break;

               case "showforum": main::required("modules/{$main->module}/showforum.php"); showforum_main(); break;        
               case "showtopic": main::required("modules/{$main->module}/showtopic.php"); showtopic_main(); break;
               case "showtopica": main::required("modules/{$main->module}/showtopic.php"); showtopic_quotepost_listposts(intval($_GET['id'])); break;
               case "showpost": main::required("modules/{$main->module}/showtopic.php"); showtopic_showpost(); break;
               case "more": main::required("modules/{$main->module}/showtopic.php"); showtopic_showpost(); break;


               case "newpost": main::required("modules/{$main->module}/posting.php"); posting_newpost(); break;
               case "qutoepost": main::required("modules/{$main->module}/posting.php"); posting_quotepost(); break;
               case "sendnewpost": main::required("modules/{$main->module}/posting.php"); posting_sendnewpost(); break;
               case "postedit": main::required("modules/{$main->module}/posting.php"); posting_postedit(); break;
               case "sendpostedit": main::required("modules/{$main->module}/posting.php"); posting_sendpostedit(); break;

               case "newtopic": main::required("modules/{$main->module}/posting.php"); posting_newtopic(); break;
               case "sendnewtopic": main::required("modules/{$main->module}/posting.php"); posting_sendnewtopic(); break;
               case "topicedit": main::required("modules/{$main->module}/posting.php"); posting_topicedit(); break;
               case "sendtopicedit": main::required("modules/{$main->module}/posting.php"); posting_sendtopicedit(); break;

               case "deletepost": deletepost(); break;
               case "topicdel": topicdel(); break;          

               case "showip": main::required("modules/{$main->module}/showip.php"); break;
               case "user_tnx": user_tnx(); break;
               case "report": report(); break;

               case "closetopic": closetopic(); break;
               case "topicsplit": topicsplit(); break;
               case "get_theme":forum_get_theme();break;
               case "topicmove": topicmove(); break;
               case "send_topicmove": send_topicmove(); break;
               case "send_topicsplit": send_topicsplit(); break;
               case "topicmark": topicmark(); break;

               case "lastpost": lastpost(); break;
               case "search": main::required("modules/{$main->module}/search.php"); break;  
               case "upload": global_upload_attach(); break;
               case "userinfo":main::required("modules/{$main->module}/userinfo.php"); break;
               case "subscribe":topic_subscribe();break;
               case "unsubscribe":topic_unsubscribe();break;
               case "markallread": forum_markallread(); break;
               case "mail_subs":main::required("modules/{$main->module}/mailposting.php");
                  send_mail_subscrib();break;
               case "rss":main::required("modules/{$main->module}/rss.php");
                  rss_forum();break;   
               case "new_voting":
               case "delvoting":
               case "set_votes":
               main::required("modules/{$main->module}/voting.php");
               switch($_GET['do']){
                  case "new_voting":if(is_ajax()) echo forum_new_voting();break;
                  case "delvoting":if(is_ajax()) echo forum_remove_voting();break;
                  case "set_votes":echo forum_set_votes();break;
                     kr_exit();
               }
               break;
               default: main::required("modules/{$main->module}/forum.php"); break;
            }
         } else main::required("modules/{$main->module}/forum.php");
      } else echo warning(str_replace('{REVISION}',779,$main->lang['garant_revision']), true);
   }
   switch_module_forum();
?>