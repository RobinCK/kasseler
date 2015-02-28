<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");  
   global $main, $forum, $smiles, $ip, $cache_ignore, $type_select,$show_first_post, $template;

   global $topic_info, $topic, $post_info, $id;
   //Проверяем наличие ID
   $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id'])?intval($_POST['id']):0);
   if(empty($id)) kr_http_ereor_logs(403);
   //Подключаем модуль прикрепления файлов
   main::init_function('attache', 'time2string');
   if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
   global $super_user, $topic_active, $uploaddir;
   //Инициализируем шаблон
   $template->get_tpl('forum/posting', 'posting');
   $uploaddir = $forum['directory'].USER_FOLDER."/";
   main::required("modules/{$main->module}/accinfo.php"); 
   /**
   * диалог редактирования голосования на форуме
   * 
   */
   function get_voting(){
      global $topic,$main;
      $ret="";
      if(in_array($_GET['do'], array('newtopic','topicedit'))){
         if(check_access_forum(accVoting)) {
            main::required("modules/{$main->module}/voting.php");
            if(isset($topic)){
               if(!empty($topic['vote_id'])){
                  $ret=forum_edit_voting($topic['vote_id']);
               } else $ret=forum_init_voting();
            } else $ret=forum_init_voting();
         };
      }
      return $ret;
   }
   /**
   * Удаление информации из шаблона
   * 
   * @param boolean $thistopic - редактирование темы?
   * @param boolean $thisfirst_post - это первый пост?
   * @param boolean $remove_admin - удалаять ли админ шаблон
   */
   function posting_remove_template($thistopic, $thisfirst_post, $remove_admin){
      global $main, $template;
      if(hook_check(__FUNCTION__)) return hook();
      //Если не администратор или модератор -  удаляем администраторские функции шаблона
      if($remove_admin) $template->remove_block('onlyadmin','posting');
      if(!$thistopic) $template->remove_block('onlytopic','posting');
      //Удаляем с шаблона функции закрепления post, если єто не первый post
      if(!$thisfirst_post) $template->remove_block('onlyfirstpost','posting');
   }

   /**
   * получение значениея user_timeout пользователя
   * 
   */
   function get_user_timeout(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      //Включаем игнорирование кэша
      $cache_ignore = true;
      list($timeout) = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_timeout FROM ".USERS." WHERE uid='{$main->user['uid']}'"));
      //Выключаем игнорирование кэша
      $cache_ignore = false;
      return $timeout;
   }
   /**
   * 
   * 
   * @param mixed $topic_id
   * @param mixed $post_id
   */
   function send_mail_subscribe($topic_id,$post_id){
      global $main;
      if(!isset($_SESSION['forum_subs'])) $_SESSION['forum_subs']=array();
      $_SESSION['forum_subs'][$topic_id]=$post_id;
      //main::required("modules/{$main->module}/mailposting.php");
      //$url=$main->url(array('module' => $main->module, 'do' => 'mail_subs', 'id' => $topic_id));
      //exec_php($url,array('PHPSESSID'=>$_SESSION['id']));
   }
   /**
   * содержимое "Иконки сообщения"
   * 
   */
   function posting_icontable(){
      global $main, $topic, $post_info;
      if(hook_check(__FUNCTION__)) return hook();
      $select_icon = (!isset($topic['ico']) AND !isset($post_info)) ? 'noico' : empty($topic['ico'])?(empty($post_info['ico'])?'noico':$post_info['ico']):$topic['ico'];
      $ico = "<div class='post_options' id='icotable'><table><tr>\n";
      for ($i=1; $i<=14; $i++) {
         $ico .= "<td>".in_radio('ico', "icon{$i}", "<img src='".TEMPLATE_PATH."{$main->tpl}/forum/ico_topic/icon{$i}.gif' alt='icon-{$i}' />", "icon-{$i}", ($select_icon=="icon{$i}")?true:false)."</td>\n";
         $ico .= ($i==7) ? "</tr><tr>" : "";
      }
      $ico .= "</tr><tr><td colspan='7'>".in_radio('ico', '', $main->lang['no_ico_posting'], "icon-no", ($select_icon=='noico')?true:false)."</td></tr></table>";
      $ico .= "</div>";
      return $ico;
   }
   /**
   * обьявление "Иконки сообщения"
   * 
   */
   function posting_icontable_title(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return "<a href='#' onclick=\"\$('#icotable').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['topic_ico']}</a>";
   }
   /**
   * заполнение переменной $topic содержащей дополнительную информацию
   * 
   * @param mixed $mode
   */
   function posting_get_topic($mode){
      global $main, $forum, $id, $topic, $topic_active, $super_user, $uploaddi, $template;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($topic)){
         switch($mode){
            case 0://sendnewtopic and newtopic
               $result = $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.forum_status,a.tree {FIELDS} FROM ".FORUMS." AS f ,".CAT_FORUM." AS a {TABLES} WHERE f.forum_id='{$id}' and a.cat_id=f.cat_id {WHERES}", __FUNCTION__);
               break;
            case 1://topicedit &  sendtopicedit
               $result = $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.forum_status, f.acc_write, t.topic_id, t.topic_first_post_fix, t.vote_id, t.topic_title, t.topic_desc, t.topic_type, t.ico, a.tree {FIELDS} FROM ".FORUMS." AS f, ".TOPICS." AS t,".CAT_FORUM." AS a {TABLES} WHERE t.forum_id=f.forum_id AND t.topic_id='{$id}' and a.cat_id=f.cat_id {WHERES}", __FUNCTION__);
               break;
            case 2: // sendnewpost & newpost
               $result = $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.forum_status, f.acc_post, f.acc_edit, t.topic_id, t.forum_id, t.topic_title, t.topic_status, t.topic_first_post_fix, t.topic_first_post_id,a.tree {FIELDS} FROM ".FORUMS." AS f, ".TOPICS." AS t, ".CAT_FORUM." AS a {TABLES} WHERE t.forum_id=f.forum_id AND t.topic_id='{$id}' and a.cat_id=f.cat_id {WHERES}", __FUNCTION__);
               break;
            case 3://postedit & sendpostedit & qutoepost
               $result = $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.forum_status, f.acc_post, f.acc_edit, t.topic_id, t.forum_id, t.topic_title, t.topic_status, t.topic_first_post_fix, t.topic_first_post_id, p.post_id, p.topic_id, p.forum_id, p.poster_id,a.tree {FIELDS} FROM ".FORUMS." AS f, ".TOPICS." AS t, ".POSTS." AS p, ".CAT_FORUM." AS a {TABLES} WHERE t.topic_id=p.topic_id AND p.forum_id=f.forum_id AND p.post_id='{$id}' and a.cat_id=f.cat_id {WHERES}", __FUNCTION__);
               break;
         }
         if($main->db->sql_numrows($result)>0){
            $topic = $main->db->sql_fetchrow($result);
            //Определение каталога для загрузки
            $uploaddir = (isset($topic['post_id']) AND file_exists($forum['directory'].$topic['post_id']."/")) ? $forum['directory'].$topic['post_id']."/" : $forum['directory'].USER_FOLDER."/";
            $_SESSION['uploaddir'] = $uploaddir;
            forum_open_access_forum($topic['tree'],$topic['forum_id']);
            $super_user = check_access_forum(accModerator);
            $topic_active = $topic['forum_status']!=1 AND (isset($topic['topic_status']) AND $topic['topic_status']!=1);
            return true;
         } else {
            $topic_active = false;
            $super_user = is_admin();
            $new_title=gen_forum_breadcrumb(0, '', '');
            $template->set_tpl(array('bread_crumbs' => bcrumb::bread_crumb($new_title)), 'index');
            warning($main->lang['nosearch_topic']);
            return false;
         }
      }
   }
   /**
   * Создаем список возможных тем
   * 
   */
   function posting_type_theme(){
      global $main, $topic;
      if(hook_check(__FUNCTION__)) return hook();
      $posting_type = "";
      $_arr_types = array('normal_topic', 'advertisement_topic', 'important_topic');
      $key_sel = (!isset($topic['topic_type'])) ? 0 : $topic['topic_type'];
      foreach($_arr_types as $key => $value) $posting_type .= "<span class='type_topic'>".in_radio('type', $key, $main->lang[$value], 'type'.$key, (($key==$key_sel)?true:false))."</span>";
      return $posting_type;
   }
   /**
   * Панель прикрепления файлов
   * 
   */
   function posting_attach($directory){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      return "<a href='#' onclick=\"\$('#attache').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['attach']}</a>".
      "<div id='attache' class='box post_options'>".in_hide("uploaddir", $directory, true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($directory)."</div>".SWFUpload("index.php?module={$main->module}&amp;do=upload", $forum['attaching_files_type'], $forum['attaching_files_size'], $forum['file_upload_limit'])."</div>";
   }

   /**
   * Шаблон создания топика
   * 
   * @param boolean $itsnew - это новый топик?
   * @param string $msg - сообщения с предупреждениями
   */
   function posting_tpl_topic($itsnew, $msg=''){
      global $main, $topic, $forum, $template;
      if(hook_check(__FUNCTION__)) return hook();
      main::add_css2head(".options_show, options_show_ac:hover{height: 22px !important;}");
      $template->set_tpl(hook_set_tpl(array(
               'OPEN_TABLE'            => open(true),
               'CLOSE_TABLE'           => close(true),
               'LOAD_TPL'              => $main->tpl,

               'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
               'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
               'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
               'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",

               'posting.TOPIC'         => "",
               'posting.MSG'           => !empty($msg)?warning($msg, true):"",
               'posting.ACTION'        => ($itsnew) ? $main->url(array('module' => $main->module, 'do' => 'sendnewtopic', 'id' => $_GET['id'])) : $main->url(array('module' => $main->module, 'do' => 'sendtopicedit', 'id' => $_GET['id'])),
               'posting.CASE_ICO'      => posting_icontable(),
               'posting.CASE_TYPE'     => posting_type_theme(),
               'posting.TITLE'         => in_text('title', 'input_text2', isset($topic['topic_title']) ? $topic['topic_title'] : ""),
               'posting.DESC'          => in_text('desc', 'input_text2', isset($topic['topic_desc']) ? $topic['topic_desc'] : ""),
               'posting.FIXED'         => in_chck('fixed', '', isset($topic['topic_first_post_fix']) ? ($topic['topic_first_post_fix']=='y'?1:" ") : " "),
               'posting.EDITOR'        => ($itsnew) ? "<div class='editorbox'>".editor('message', '160px', '100%')."</div>" : "",
               'posting.SMILES'        => ($itsnew) ? forum_smilebox():"",
               'posting.SUBMIT'        => send_button(),
               'posting.ATTACH'        => check_access_forum(accUpload)?posting_attach($forum['directory'].USER_FOLDER."/"):"",

               'FORUM_NAME'            => "<a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$topic['forum_name']}'>{$topic['forum_name']}</a>",
               'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$main->lang['forum_index']} {$main->config['home_title']}'>{$forum['forum_title']}</a>",
               'L_POSTING_TITLE'       => $main->lang['topic_name'],
               'L_POSTING_DESC'        => $main->lang['topic_desc'],
               'L_POSTING_FIXED'       => $main->lang['topic_fixed'],
               'L_POSTING_TYPE'        => $main->lang['topic_type'],
               'L_POSTING_ICO'         => posting_icontable_title(),
               'L_POSTING_VOTING'      => get_voting(),
            ),__FUNCTION__), 'posting',  array('start' => '{', 'end' => '}'));
      //Выводим шаблон
      $template->tpl_create(false, 'posting');
   }
   /**
   * диалог создания новой темы
   * 
   * @param mixed $msg
   */
   function posting_newtopic($msg = ""){
      global $main, $topic, $id, $super_user;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(0)){
         if(topic_access_forum(accWrite, 'no_acc_new_topic')){
            add_meta_value($main->lang['new_topic']);
            posting_remove_template(true, true, !$super_user);
            posting_tpl_topic(true, $msg);
         }
      }
   }
   /**
   * анализ возможности сохранения новой темы
   * 
   */
   function posting_sendnewtopic(){
      global $main, $topic, $id;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(0)){
         if(topic_access_forum(accWrite, '')){
            filter_arr(array('title', 'desc', 'type', 'ico'), POST, TAGS);
            $msg = error_empty(array('title'), array('topic_title_empty'));
            $timeout = get_user_timeout();
            $msg .= ($timeout>time() AND !is_support()) ? str_replace('{TIME}', time2string($timeout-time()), $main->lang['timeoutpost']) : "";
            if(empty($msg)){
               posting_savetopic();
            } else posting_newtopic($msg);
         }
      }
   }
   /**
   * сохранение темы форума
   * 
   */
   function posting_savetopic(){
      global $main, $topic, $forum, $ip, $id;
      if(hook_check(__FUNCTION__)) return hook();
      $_topic = array(
         'forum_id'          => intval($topic['forum_id']),
         'topic_title'       => mb_substr($_POST['title'], 0, 149),
         'topic_desc'        => kr_filter($_POST['desc'], TAGS),
         'topic_poster'      => $main->user['uid'],
         'topic_time'        => kr_time(),
         'topic_poster_name' => $main->user['user_name'],
         'ico'               => kr_filter($_POST['ico'], TAGS),
         'topic_views'       => '0',
         'topic_type'        => empty($_POST['type'])?"0":intval($_POST['type']),
         'topic_first_post_fix'=>empty($_POST['fixed'])?"n":($_POST['fixed']=='on'?'y':'n')
      );
      main::required("modules/{$main->module}/voting.php");
      $idv=forum_save_voting();
      if(is_numeric($idv)) $_topic['vote_id']=$idv;
      //Создаем новую тему
      sql_insert($_topic, TOPICS);
      //Узнаем сгенерированный ID темы
      list($topic_id) = $main->db->sql_fetchrow($main->db->sql_query("SELECT topic_id FROM ".TOPICS." WHERE topic_poster='{$main->user['uid']}' ORDER BY topic_id DESC LIMIT 1"));
      if(!is_guest()&&$main->user['user_forum_mail']=='1') sql_insert(array('uid'=>$main->user['uid'],'topic_id'=>$topic_id,'sending'=>'n'),FORUM_SUBSCRIBE);
      //Создаем первое сообщение темы
      sql_insert(array(
            'topic_id'      => $topic_id,
            'forum_id'      => intval($topic['forum_id']),
            'poster_id'     => $main->user['uid'],
            'post_time'     => kr_time(),
            'poster_ip'     => $ip,
            'post_subject'  => mb_substr($_POST['title'], 0, 149),
            'post_text'     => bb($_POST['message']),
            'poster_name'   => $main->user['user_name'],
            'ico'           => kr_filter($_POST['ico'], TAGS)
         ), POSTS);    
      add_points($main->points['forum_topic']);
      //Узнаем сгенерированный ID сообщения
      list($post_id) = $main->db->sql_fetchrow($main->db->sql_query("SELECT post_id FROM ".POSTS." WHERE topic_id='{$topic_id}' AND poster_id='{$main->user['uid']}' ORDER BY post_id DESC LIMIT 1"));                    
      //Обновляем информацию о теме
      fix_topic_info($topic_id);
      //Обновляем информацию о форуме
      fix_forum_info($id);
      //Добавляем +1 к количеству оставленных сообщений
      update_posts($main->user['uid'], "+");
      //Проверяем наличие прикрепленных файлов
      if(rename_attach($forum['directory'].USER_FOLDER."/", $forum['directory'].$post_id."/")){
         $_POST['message'] = str_replace(USER_FOLDER, $post_id, $_POST['message']);
         sql_update(array('post_text' => bb($_POST['message'])), POSTS, "post_id='{$post_id}'");
      }
      redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic_id)));
   }
   /**
   * обработка возможности созранения сообщения
   * 
   */
   function posting_sendnewpost(){
      global $main, $forum, $topic, $template, $id, $super_user, $topic_active, $uploaddir;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(2)){
         if(topic_access_forum(accPost, 'no_acc_new_post')){
            posting_remove_template(false, false, false);
            $msg = "";
            $msg = error_empty(array('message'), array('tp_message_empty'));
            $timeout = get_user_timeout();//Проверяем timeout
            $msg .= ($timeout>time() AND !is_support()) ? str_replace('{TIME}', time2string($timeout-time()), $main->lang['timeoutpost']) : "";
            posting_savepost(true, $msg);
         }
      }
   }
   /**
   * Сохранить сообщение
   * 
   * @param boolean $itsnew Єто новое сообщение?
   * @param mixed $msg предупреждения
   */
   function posting_savepost($itsnew, $msg = ""){
      global $main, $forum, $topic, $uploaddir, $ip;
      if(hook_check(__FUNCTION__)) return hook();
      //Выполняем фильтрацию полученных данных
      filter_arr(array('title', 'ico'), POST, TAGS);
      $_POST['message'] = kr_filter($_POST['message'], HTML);
      if(empty($msg)){
         if($itsnew){
            //Создаем новое сообщение
            main::init_function('editor_smile_conv');
            $dbr = sql_insert(array(
                  'topic_id'      => intval($topic['topic_id']),
                  'forum_id'      => intval($topic['forum_id']),
                  'poster_id'     => $main->user['uid'],
                  'post_time'     => kr_time(),
                  'poster_ip'     => $ip,
                  'post_subject'  => !empty($_POST['title'])?mb_substr($_POST['title'], 0, 149):addslashes(mb_substr("Re: {$topic['topic_title']}", 0, 149)),
                  'post_text'     => editor_smile_conv($_POST['message']),
                  'poster_name'   => $main->user['user_name'],
                  'ico'           => kr_filter($_POST['ico'], TAGS)
               ), POSTS);
            if($dbr){   
               $new_post_id = $main->db->sql_nextid();
               add_change_read($topic['topic_id'], $new_post_id);
               add_points($main->points['forum_post']);
               //Обновляем информацию о теме
               fix_topic_info($topic['topic_id']);
               //Обновляем информацию о форуме 
               fix_forum_info($topic['forum_id']);
               //Добавляем +1 к количеству оставленных сообщений
               update_posts($main->user['uid'], "+");
               send_mail_subscribe(intval($topic['topic_id']),$new_post_id);
               //Проверяем наличие прикрепленных файлов
               if(rename_attach($uploaddir, $forum['directory'].$new_post_id."/")){
                  $_POST['message'] = str_replace(USER_FOLDER, $new_post_id, $_POST['message']);
                  sql_update(array('post_text' => bb($_POST['message'])), POSTS, "post_id='{$new_post_id}'");
               }
            }
            /*[X]*/
            //Если добавление поста ajax запросом
            if(is_ajax()){
               //Проверка нужно ли делать редирект на последнюю страницу 
               if($_POST['count_post']<$forum['post_views_num'] AND $_POST['page']==$_POST['pages']){
                  //Подставляем нужные значения в GET
                  $_GET = array('module' => $main->module, 'do' => 'showtopic', 'id' => intval($_GET['id']));
                  //Если существует более 1 страницы добавляем их тоже в GET
                  if($_POST['page']!=1) $_GET = array_merge($_GET, array('page' => $_POST['page']));
                  main::required("modules/{$main->module}/showtopic.php");
                  echo showtopic_posting();
               } else echo "<script type='text/javascript'>location.href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $topic['topic_id']))."'.replaceAll('&amp;', '&');</script>";
               kr_exit();
            } else redirect($main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $topic['topic_id'])));
         } else {
            //Проверяем наличие прикрепленных файлов
            if(rename_attach($uploaddir, $forum['directory'].intval($_GET['id'])."/")) $_POST['message'] = str_replace(USER_FOLDER, intval($_GET['id']), $_POST['message']);
            //Сохранение отредактированого поста.
            sql_update(array(
                  'post_text'      => bb($_POST['message']),
                  'post_subject'   => kr_filter($_POST['title'], TAGS),
                  'ico'            => magic_quotes($_POST['ico']),
                  'post_edit_time' => kr_time(),
                  'post_edit_user' => $main->user['user_name']
               ), POSTS, "post_id='".intval($_GET['id'])."'");
            if(!empty($_POST['hide_fixed'])) sql_update(array('topic_first_post_fix'=>((isset($_POST['fixed']) AND $_POST['fixed']=='on')?'y':'n')), TOPICS, "topic_id='".$topic['topic_id']."'");
            if(isset($_POST['page_post'])){
               $main->db->sql_query("SELECT t.topic_first_post_fix,count(p.post_id) as post_count FROM ".TOPICS." AS t, ".POSTS." AS p WHERE t.topic_id='".intval($topic['topic_id'])."' and p.topic_id=t.topic_id and post_id<=".intval($_GET['id'])." group by t.topic_first_post_fix");
               if($main->db->sql_numrows()>0){
                  $row = $main->db->sql_fetchrow();
                  $pages = ceil(($row['post_count'])/$forum['post_views_num']);
                  $entry=($row['topic_first_post_fix']=='n')? $row['post_count']:$row['post_count']+1;
               } else $entry=1;
               $redirect =  array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id'], 'page' => $_POST['page_post']."#entry{$entry}");
            } else $redirect =array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id']);
            redirect($main->url($redirect));
         }
      } else {
         if(is_ajax()){echo "<script type='text/javascript'>alert('".strip_tags($msg)."');</script>"; kr_exit();}
         else warning($msg);
      }
   }
   /**
   * Вывод редактора нового сообщения
   * @param string $msg - предепреждения
   * 
   */
   function posting_showeditpost($itsnew, $msg = ""){
      global $main, $template, $forum, $topic, $post_info, $uploaddir;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($post_info['post_id'])) $uploaddir = $forum['directory'].$post_info['post_id']."/";
      $template->set_tpl(hook_set_tpl(array(
               'OPEN_TABLE'            => open(true),
               'CLOSE_TABLE'           => close(true),
               'LOAD_TPL'              => $main->tpl,

               'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
               'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
               'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
               'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",

               'posting.TOPIC'         => "<b><a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $topic['topic_id']))."' title='{$topic['topic_title']}'>{$topic['topic_title']}</a></b>",
               'posting.MSG'           => !empty($msg)?warning($msg, true):"",
               'posting.ACTION'        => ($itsnew?$main->url(array('module' => $main->module, 'do' => 'sendnewpost', 'id' => $topic['topic_id'])):$main->url(array('module' => $main->module, 'do' => 'sendpostedit', 'id' => $_GET['id']))),
               'posting.CASE_ICO'      => posting_icontable(),
               'posting.TITLE'         => in_text('title', 'input_text2', !isset($post_info)?"Re: {$topic['topic_title']}":$post_info['post_subject']),
               'posting.FIXED'         => in_chck('fixed', '',array_key_exists('topic_first_post_fix',$topic)?($topic['topic_first_post_fix']=='y'?1:""):""),
               'posting.EDITOR'        => "<div class='editorbox'>".editor('message', '160px', '100%', isset($post_info)?bb($post_info['post_text'], DECODE):"")."</div>",
               'posting.SMILES'        => forum_smilebox(),
               'posting.SUBMIT'        => (isset($_GET['page'])?in_hide('page_post', $_GET['page']):"").send_button(),
               'posting.ATTACH'        => check_access_forum(accUpload)?posting_attach($uploaddir):"",

               'FORUM_NAME'            => "<a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $topic['forum_id']))."' title='{$topic['forum_name']}'>{$topic['forum_name']}</a>",
               'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$main->lang['forum_index']} {$main->config['home_title']}'>{$forum['forum_title']}</a>",
               'L_POSTING_TITLE'       => $main->lang['post_name'],
               'L_POSTING_FIXED'       => $main->lang['topic_fixed'],
               'L_POSTING_ICO'         => posting_icontable_title(),
               'L_POSTING_VOTING'      => get_voting(),
            ),__FUNCTION__), 'posting', array('start' => '{', 'end' => '}'));

      //Выводим шаблон 
      $template->tpl_create(false, 'posting');
   }
   /**
   * создание нового сообщения
   * 
   */
   function posting_newpost(){
      global $main, $topic, $id, $template, $post_info;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(2)){
         if(topic_access_forum(accPost, 'no_acc_new_post')){
            posting_remove_template(false, false, true);
            posting_showeditpost(true);
         }
      }
   }
   /**
   * ответить с цитированием
   * 
   */
   function posting_quotepost(){
      global $main, $topic, $id, $template, $post_info;
      if(hook_check(__FUNCTION__)) return hook();
      add_meta_value($main->lang['new_post']);
      if(posting_get_topic(3)){
         $post_info = forum_post_info(intval($_GET['id']));
         if(topic_access_forum(accPost, 'no_acc_new_post')){
            posting_remove_template(false, false, true);
            $post_info['post_subject'] = "Re: {$post_info['post_subject']}";
            $post_info['post_text'] = "[cite={$post_info['poster_name']}, ".format_date(date("Y-m-d H:i:s", $post_info['post_time']), "{$main->config['date_format']} H:i:s")."]{$post_info['post_text']}[/cite]";
            posting_showeditpost(true);
            main::required("modules/{$main->module}/showtopic.php");
            showtopic_quotepost_listposts($topic['topic_id']);
         }
      }
   }

   /**
   * сохранение после qutoepost
   * 
   */
   function posting_sendpostedit(){
      global $main, $topic, $id, $template, $post_info;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(3)){
         if($topic['poster_id']==$main->user['uid'] OR topic_access_forum(array(accPost,accEdit), 'no_acc_new_post')){
            $msg = "";
            $msg = error_empty(array('message'), array('tp_message_empty'));
            $timeout = get_user_timeout();//Проверяем timeout
            $msg .= ($timeout>time() AND !is_support()) ? str_replace('{TIME}', time2string($timeout-time()), $main->lang['timeoutpost']) : "";
            add_meta_value($main->lang['edit_post']);
            posting_savepost(!isset($_POST['page_post']), $msg);
         } 
      }
   }
   /**
   * Редактирование сообщения
   * 
   */
   function posting_postedit(){
      global $main, $topic, $id, $template, $post_info, $topic_active;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(3)){
         add_meta_value($main->lang['edit_post']);
         $post_info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".POSTS." WHERE post_id='".intval($_GET['id'])."'"));
         if($topic_active AND ($post_info['poster_id']==$main->user['uid'] OR topic_access_forum(accEdit))){
            posting_remove_template(false, $topic['topic_first_post_id']==$post_info['post_id'], true);
            posting_showeditpost(false, '');
         }
      }
   }
   /**
   * редактирование темы
   * 
   */
   function posting_topicedit($msg = ""){
      global $main, $topic, $id, $template, $super_user;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(1)){
         add_meta_value($main->lang['edit_topic']);
         if(topic_access_forum(array(accEdit,accWrite, accModerator))){
            posting_remove_template(true, true, !$super_user);
            posting_tpl_topic(false, $msg);
         }
      }
   }
   /**
   * сохранение темы после редактирования
   * 
   */
   function posting_sendtopicedit(){
      global $main, $topic, $id, $template, $super_user;
      if(hook_check(__FUNCTION__)) return hook();
      if(posting_get_topic(1)){
         if(topic_access_forum(array(accWrite,accEdit, accModerator))){
            filter_arr(array('title', 'desc', 'type', 'ico'), POST, TAGS);
            $msg = error_empty(array('title'), array('topic_title_empty'));
            $timeout = get_user_timeout();
            $msg .= ($timeout>time() AND !is_support()) ? str_replace('{TIME}', time2string($timeout-time()), $main->lang['timeoutpost']) : "";
            if(empty($msg)){
               $_topic = array(
                  'topic_title'       => mb_substr($_POST['title'], 0, 149),
                  'topic_desc'        => kr_filter($_POST['desc'], TAGS),
                  'ico'               => kr_filter($_POST['ico'], TAGS),
                  'topic_type'        => empty($_POST['type'])?"0":intval($_POST['type']),
                  'topic_first_post_fix'=>empty($_POST['fixed'])?"n":($_POST['fixed']=='on'?'y':'n')
               );
               //Сохранение отредактированого топика 
               main::required("modules/{$main->module}/voting.php");
               if(isset($topic['vote_id'])&&(!empty($topic['vote_id']))){
                  //$main->db->sql_query("delete from ".VOTING." where id={$topic['vote_id']}");
                  //$_topic['vote_id']="NULL";
                  $_POST['vt_id'] = $_topic['vote_id'];
               }
               $idv=forum_save_voting();
               if(is_numeric($idv)) $_topic['vote_id']=$idv;
               sql_update($_topic, TOPICS, "topic_id='".intval($_GET['id'])."'");
               redirect($main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $id)));
            } else posting_topicedit($msg);
         }
      }
   }
?>
