<?php
   /**
   * @author Igor Ognichenko, Dmitrey Browko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");  
   global $main, $forum, $tpl_create;

   //Проверка на пользовательскую сортировку
   if(isset($_GET['mod']) AND $_GET['mod']=='true'){
      unset($_GET['mod']);
      //Выполняем преобразование урла
      $url = array();
      foreach($_GET as $key => $value) $url[$key] = $value;
      redirect($main->url($url));
   }
   //Создаем правила ЧПУ
   $main->parse_rewrite(array('module', 'do', 'id', 'page', 'sort', 'type', 'time'));
   if(!is_numeric($_GET['id'])) kr_http_ereor_logs(404);

   /**
   * вывод списка тем на форуме
   * 
   * @param mixed $forums
   */
   function showforum_forum_tpl($forums, $content){
      global $main, $template, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      list($shows_users, $users_list) = showforum_usersview();
      list($pages, $pagenums) = showforum_pages($forums);
      $template->get_tpl('show_topic','show_topic');
      open_forum_moder($forums['tree'],$forums['forum_id']);
      $template->set_tpl(hook_set_tpl(array(
               'OPEN_TABLE'            => open(true),
               'CLOSE_TABLE'           => close(true),
               'LOAD_TPL'              => $main->tpl,
               'FORUM_BREAD_CRUMB'     => '',
               'FORUM_NAME'            => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $forums['forum_id']))."' title='{$forums['forum_name']}'>{$forums['forum_name']}</a>",
               'L_INDEX'               => "<a class='sys_link' href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",

               'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
               'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
               'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
               'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",

               'L_TOPICS'              => $main->lang['topic'],
               'L_REPLIES'             => $main->lang['replies'],
               'L_AUTHOR'              => $main->lang['poster'],
               'L_VIEWS'               => $main->lang['topic_viws'],
               'L_LASTPOST'            => $main->lang['topic_last'],    
               'L_NEW_POSTS'           => $main->lang['new_posts'],
               'L_NO_NEW_POSTS'        => $main->lang['no_new_posts'],
               'L_ANNOUNCEMENT'        => $main->lang['announcement'],
               'L_NEW_POSTS_HOT'       => $main->lang['new_posts_hot'],
               'L_NO_NEW_POSTS_HOT'    => $main->lang['no_new_posts_hot'],
               'L_STICKY'              => $main->lang['sticky'],
               'L_NEW_POSTS_LOCKED'    => $main->lang['new_posts_locked'],
               'L_NO_NEW_POSTS_LOCKED' => $main->lang['no_new_posts_locked'],   
               'L_MODERATOR'           => $main->lang['forum_moders'],   
               'L_MARK_TOPICS_READ'    => !empty($pagenums)?"<a class='mark_topic' href='".$main->url(array('module' => $main->module, 'do' => 'topicmark', 'id' => $forums['forum_id']))."' title='{$main->lang['mark_topic_read']}'>{$main->lang['mark_topic_read']}</a>":"",
               'POST_NEW_TOPIC'        => check_access_forum(accWrite)?"<a class='forum_button' href='".$main->url(array('module' => $main->module, 'do' => 'newtopic', 'id' => $forums['forum_id']))."' title='{$main->lang['newtopic']}'><span><img style='margin-right: 5px;' class='icon icon-topic icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['newtopic']}</a>":"",   
               'PAGINATION'            => !empty($pagenums)?$pagenums:"<a class='mark_topic' href='".$main->url(array('module' => $main->module, 'do' => 'topicmark', 'id' => $forums['forum_id']))."' title='{$main->lang['mark_topic_read']}'>{$main->lang['mark_topic_read']}</a>",
               'S_AUTH_LIST'           => forum_topic_access_list(),
               'MODERATORS'            => forum_list_moderators($forums['tree'], $forums['forum_id'],true),
               'SHOWS_FORM'            => preg_replace(array('/\{USER\}/i', '/\{GUEST\}/i', '/\{ALL\}/i'), array($shows_users['user'], $shows_users['guest'], $shows_users['guest']+$shows_users['user']), $main->lang['stat_shows_forum']),
               'QUICK_LINK'            => quick_link($forums['forum_id']),
               'QUICK_SORT'            => quick_sort(),
               'LOGINED_USER_LIST'     => !empty($users_list)?$users_list:"<i>{$main->lang['nologineduserlist']}</i>",
               'TOPIC_CONTENT'         => $content,
               'PAGE_NUMBER'           => isset($pages) ? preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array(isset($_GET['page'])?$_GET['page']:1, $pages), $main->lang['numberpage']) : "",
            ),__FUNCTION__), 'show_topic', array('start' => '{', 'end' => '}'));  
      return $template->tpl_create(true, 'show_topic');
   }
   /**
   * возврвщает запись-тему на форуме
   * @param array $row - массив-запись темы
   */
   function showforum_topic_tpl($row, $row_c){
      global $template, $main;
      if(hook_check(__FUNCTION__)) return hook();
      $floder = get_folder_topic($row['topic_id'], $row['topic_last_post_id'], $row['topic_status'], $row['topic_replies'], $row['topic_type']);
      $template->get_tpl('TOPIC_CONTENT','TOPIC_CONTENT');
      $topics_rows = array(            
         'topicrow.TOPIC_FOLDER_IMG'     => $floder[0],
         'topicrow.L_TOPIC_FOLDER_ALT'   => $floder[1],
         'topicrow.TOPIC_ICO'            => !empty($row['ico']) ? "<img src='".TEMPLATE_PATH."{$main->tpl}/forum/ico_topic/{$row['ico']}.gif' alt='' />" : "&nbsp;",
         'topicrow.TOPIC_TITLE'          => "<a class='sys_link' ".(check_access_forum(accRead)?"href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $row['topic_id']))."'":"")." title='{$row['topic_title']}'>{$row['topic_title']}</a>",
         'topicrow.TOPIC_DESC'           => $row['topic_desc'],
         'topicrow.GOTO_PAGE'            => '',
         'topicrow.ROW_CLASS'            => $row_c,
         'topicrow.REPLIES'              => $row['topic_replies'],
         'topicrow.TOPIC_AUTHOR'         => (!is_guest_name($row['user_name']) AND $row['topic_poster']!=-1) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['user_name']}</a>" : $row['user_name'],
         'topicrow.LAST_POST_TIME'       => user_format_date(gmdate("Y-m-d H:i:s", $row['post_time']), true),
         'topicrow.LAST_POST_AUTHOR'     => forum_last_post_user($row, $row['poster_id'], $row['poster_uid'], $row['poster_user_id'], $row['poster_user_name']),
         'topicrow.LAST_POST_IMG'        => !empty($row['topic_title']) ? "<a href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $row['topic_id']))."'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_topic_latest.png' alt='' /></a>" : "",
         'topicrow.VIEWS'                => $row['topic_views'],
         'LOAD_TPL'                      => $main->tpl
      );
      $template->set_tpl(hook_set_tpl($topics_rows,__FUNCTION__),'TOPIC_CONTENT', array('start' => '{', 'end' => '}'));
      return $template->tpl_create(true, 'TOPIC_CONTENT');
   }
   /**
   * формируем шаблоны для вывода информации
   * 
   */
   function showforum_parse_tpl(){
      global $template;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl("forum/show_topic", 'show_topic');
      $template->get_subtpl(array(
            array('get_index' => 'show_topic', 'new_index' => 'TOPIC_CONTENT', 'selector' => ' topic row')
         ),array('start' => '{', 'end' => '}'));
   }
   /**
   * кто смотрит форум
   * 
   */
   function showforum_usersview(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $shows_users = array('user' => 0, 'guest' => 0); $users_list = "";
      $result = $main->db->sql_query("SELECT s.uname, u.uid, u.user_id, u.user_name, g.id, g.title, g.color FROM ".SESSIONS." AS s LEFT JOIN ".USERS." AS u ON(s.uname=u.user_name) LEFT JOIN ".GROUPS." AS g ON(u.user_group=g.id) WHERE s.actives='y' AND s.url LIKE '%".($main->mod_rewrite?"showforum/{$_GET['id']}":"showforum&id={$_GET['id']}")."%'");
      while(($row = $main->db->sql_fetchrow($result))){
         if(!is_ip($row['uname'])) {
            $shows_users['user']++;
            $users_list .= "<a class='user_info' style='color: #{$row['color']}' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['user_name']}</a>, ";
         } else $shows_users['guest']++; 
      }
      $users_list = mb_substr($users_list, 0, mb_strlen($users_list)-2);
      return array($shows_users, $users_list);
   }
   function showforum_pages($forums){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(topic_id) FROM ".TOPICS." WHERE forum_id='".intval($_GET['id'])."' AND topic_first_post_id<>'0'"));
      $pages = ceil($count/$forum['topic_views_num']);
      $sort_arr = !isset($_GET['sort']) ? array() : array('sort' => $_GET['sort'], 'type' => isset($_GET['type'])?$_GET['type']:"Z-A", 'time' => isset($_GET['time'])?$_GET['time']:'all');
      $pagenums = pages_forum($count, $forum['topic_views_num'], array('module' => $main->module, 'do' => 'showforum', 'id' => $forums['forum_id']), $sort_arr);
      return array($pages, $pagenums);
   }
   /**
   * формирования списка тем
   * 
   */
   function showforum_topics_content(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $content="";
      //Создаем правила сортировки
      $time_url = array('all' => 0, 'srfd_lastvisit' => -1, 'today' => 1, 'five_days' => 5, 'seven_days' => 7, 'ten_days' => 10, 'fifteen_days' => 15, 'twenty_days' => 20, 'twenty_five_days' => 25, 'thirty_days' => 30, 'sixty_days' => 60, 'ninety_days' => 90);
      $sort_url = array('last_post' => 'p.post_time', 'title' => 't.topic_title', 'author' => 't.topic_poster_name', 'time' => 't.topic_time', 'replies' => 't.topic_replies', 'views' => 't.topic_views');
      $type_sort = (isset($_GET['type']) AND $_GET['type']=='A-Z') ? "ASC" : "DESC";
      $sort_str = (isset($_GET['sort']) AND isset($sort_url[$_GET['sort']])) ? ($sort_url[$_GET['sort']]) : 'p.post_time';
      $time_str = (isset($_GET['time']) AND isset($time_url[$_GET['time']]) AND $_GET['time']!='all' AND $_GET['time']!='lastvisit') ? " AND t.topic_time>='".(strtotime(kr_date('Y-m-d'))-(86400*$time_url[$_GET['time']]))."'" : ((isset($_GET['time']) AND $_GET['time']=='lastvisit')?" AND t.topic_time>='".(isset($_SESSION['lastVisit'])?strtotime($_SESSION['lastVisit']):kr_datecms("Y-m-d H:i:s"))."'":'');
      $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
      $offset = ($num-1) * $forum['topic_views_num'];
      $dbr = $main->db->sql_query("SELECT t.topic_status, t.topic_type, t.topic_id, t.topic_title, t.topic_poster, t.topic_replies, t.topic_views, t.topic_time, t.topic_first_post_id, t.topic_last_post_id, t.topic_desc, t.ico, 
         p.post_id, p.poster_name, p.poster_id, p.post_time,u.uid, u.user_id, u.user_name,uu.uid AS poster_uid, uu.user_id AS poster_user_id, uu.user_name AS poster_user_name, uu.user_avatar, uu.user_email {FIELDS}
         FROM ".TOPICS." AS t, ".POSTS." AS p, ".USERS." AS u, ".USERS." AS uu {TABLES}
         WHERE p.poster_id=uu.uid AND t.topic_last_post_id=p.post_id AND t.topic_poster=u.uid AND t.forum_id='".intval($_GET['id'])."' AND t.topic_first_post_id<>'0'{$time_str} {WHERES}
         ORDER BY t.topic_type DESC, {$sort_str} {$type_sort} 
         LIMIT {$offset}, {$forum['topic_views_num']}",__FUNCTION__);
      if($main->db->sql_numrows()>0){
         $row_c = 'rows2';
         while (($row=$main->db->sql_fetchrow($dbr))){
            $content.= showforum_topic_tpl($row, $row_c);
            $row_c = ($row_c=='rows2') ? "rows1" : 'rows2';
         }
      } else  $content.=info($main->lang['notopics'], true);
      return $content;
   }
   /**
   * основная функция формирования форума
   * 
   */
   function showforum_main(){
      global $main, $forum, $template;
      if(hook_check(__FUNCTION__)) return hook();
      $forums = forum_forum_info(intval($_GET['id']));
      if($forums===FALSE) {info("<b>{$main->lang['nosearch_topic']}</b>"); return false;}
      add_meta_value($forums['forum_name']);
      forum_open_access_forum($forums['tree'], $forums['forum_id']);
      load_forum_category();
      $new_title=gen_forum_breadcrumb(0, '', $forums['tree']);
      $template->set_tpl(array('bread_crumbs' => bcrumb::bread_crumb($new_title)), 'index');
      if(check_access_forum(accView)){
         showforum_parse_tpl();
         echo showforum_forum_tpl($forums, showforum_topics_content());
      } else {
         showforum_parse_tpl();
         echo showforum_forum_tpl($forums, info("<b>{$main->lang['not_view_this_forum']}</b>", true));
      }
   }
?>