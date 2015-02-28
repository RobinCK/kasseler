<?php
   /**
   * Модуль информации по пользователю (forum mode)
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2011 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $nextpage, $main;
   function ui_topic(){
      if(hook_check(__FUNCTION__)) return hook();
   }
   // подключаем контроль доступа
   main::required("modules/{$main->module}/accinfo.php"); 

   function acc_forum_list(){
      if(hook_check(__FUNCTION__)) return hook();
      $acclist=(isset($_SESSION['forum_access']) AND count($_SESSION['forum_access']['forum_read']))>0?$_SESSION['forum_access']['forum_read']:array(0);
      return $acclist;
   }
   function forum_ui_breadcrumb($lang_text){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $bread=array();   
      $bread[]=array('caption'=>$main->lang['home'],'href'=>$main->url(array()));
      $bread[]=array('caption'=>$main->lang['forum'],'href'=>$main->url(array('module' => $main->module)));
      $bread[]=array('caption'=>$main->lang[$lang_text],'href'=>$main->url(array('module' => $main->module, 'do' => 'userinfo', 'op' => $_GET['op'], 'user' => $_GET['user'])));
      main::add_template_tag('$bread_crumbs', bcrumb::bread_crumb($bread));
   }
   function forum_ui_pages($where){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      list($count)=$main->db->sql_fetchrow($main->db->sql_query("select count(p.post_id) from ".POSTS." as p where p.forum_id in (".implode(',',acc_forum_list()).") and {$where} "));
      $pages = ceil($count/$forum['post_views_num']);
      if($count>$forum['post_views_num'] OR isset($_GET['page'])) $pagenums = pages_forum($count, $forum['post_views_num'], array('module' => $main->module, 'do' => 'userinfo', 'op' => $_GET['op'], 'user' => $_GET['user']));
      else $pagenums = '';
      return array($pages, $pagenums);
   }
   /**
   * Все посты пользователя
   * 
   */
   function ui_post(){
      global $main, $template, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $user=addslashes($_GET['user']);
      $template->get_tpl('forum/show_post', 'show_post');
      $template->get_subtpl(array(
            array('get_index' => 'show_post', 'new_index' => 'POST_CONTENT', 'selector' => ' post row')
         ),array('start' => '{', 'end' => '}'));
      $template->remove_variable(array('{TOPIC_SUBSCRIBE}','{MODERATORS}', '{TOPIC_TITLE}'),'show_post');
      add_meta_value($main->lang['user_list_post']);
      main::required('modules/forum/showtopic.php');
      global $topic;
      list($pages, $pagenums)=forum_ui_pages("p.poster_name='{$user}' ");
      $result = showtopic_init_db(false,4);
      $topic=array('topic_id' =>0, 'topic_first_post_fix'=>'n', 'forum_status' => 0, 'topic_status'=>0, 'topic_first_post_id'=>0,
         'forum_id'=>0,'forum_name'=>$main->lang['user_list_post'],'tree'=>'','sending'=>'', 'topic_title'=>$main->lang['user_list_post']);
      forum_ui_breadcrumb('user_list_post');
      showtopic_show_tpl_topic(array(
            'PAGINATION'            => $pagenums,
            'POST_CONTENT'          => forum_showtopic_gen_posts($result),
            'PAGE_NUMBER'           => "<span id='page_number'>".preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array(isset($_GET['page'])?$_GET['page']:1, $pages), $main->lang['numberpage'])."</span>",
            'FORUM_NAME'            => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'userinfo', 'op' => 'post', 'user' => $_GET['user']))."' title='{$main->lang['user_list_post']}'>{$main->lang['user_list_post']}</a>",
         ));
      if(is_ajax()) kr_exit();
   }
   /**
   * Все посты пользователя за которые сказали спасибо
   * 
   */
   function ui_gratitude(){
      global $main, $modules, $template, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $user=addslashes($_GET['user']);
      $template->get_tpl('forum/show_post', 'show_post');
      $template->get_subtpl(array(
            array('get_index' => 'show_post', 'new_index' => 'POST_CONTENT', 'selector' => ' post row')
         ),array('start' => '{', 'end' => '}'));
      $template->remove_variable(array('{TOPIC_SUBSCRIBE}','{MODERATORS}', '{TOPIC_TITLE}'),'show_post');
      add_meta_value($main->lang['user_list_gratitude']);
      main::required('modules/forum/showtopic.php');
      global $topic;
      list($pages, $pagenums)=forum_ui_pages("p.poster_name='{$user}' and (not p.post_tnx is null)");
      $result = showtopic_init_db(false,3);
      $topic=array('topic_id' =>0, 'topic_first_post_fix'=>'n', 'forum_status' => 0, 'topic_status'=>0, 'topic_first_post_id'=>0,
         'forum_id'=>0,'forum_name'=>$main->lang['user_list_post'],'tree'=>'','sending'=>'', 'topic_title'=>$main->lang['user_list_post']);
      forum_ui_breadcrumb('user_list_gratitude');
      showtopic_show_tpl_topic(array(
            'PAGINATION'            => $pagenums,
            'POST_CONTENT'          => forum_showtopic_gen_posts($result),
            'PAGE_NUMBER'           => "<span id='page_number'>".preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array(isset($_GET['page'])?$_GET['page']:1, $pages), $main->lang['numberpage'])."</span>",
            'FORUM_NAME'            => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'userinfo', 'op' => $_GET['op'], 'user' => $_GET['user']))."' title='{$main->lang['user_list_gratitude']}'>{$main->lang['user_list_gratitude']}</a>",
         ));
      if(is_ajax()) kr_exit();
   }
   function uinfo_hook_set_tpl($keys, $namespace = ''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if($namespace=='forum_showtopic_gen_posts'){
         $keys['postrow.POSTER_NAME'] = preg_replace('/<a[^>]*>/si', '<a>', $keys['postrow.POSTER_NAME']);
      }
      return hook_set_tpl($keys, $namespace);
   }
   if($_GET['do']=='userinfo'){
      hook_register('hook_set_tpl','uinfo_hook_set_tpl',false);
      $main->parse_rewrite(array('module', 'do', 'op','user','page'));
      $nextpage=array('module'=>$main->module, 'do'=>$_GET['do'], 'op'=>$_GET['op'],'user'=>$_GET['user']);
      if(isset($_GET['op'])){
         switch($_GET['op']){
            case "topic": ui_topic(); break;
            case "post":ui_post();break;
            case "gratitude": ui_gratitude(); break;
         }
      } else kr_http_ereor_logs("404");
   } else kr_http_ereor_logs("404");
?>
