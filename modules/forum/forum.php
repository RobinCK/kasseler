<?php
   /**
   * @author Igor Ognichenko, Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $main, $supervision, $forum, $template,$tpl_create, $forum_access;
   global $total_posts,$total_thems,$total_users, $curr_tree;

   list($total_posts,$total_thems,$total_users) = $main->db->sql_fetchrow($main->db->sql_query("select 
         (select count(p.post_id) from ".POSTS." p) as count_post,
         (select count(t.topic_id) from ".TOPICS." t) as count_topic,
         (select COUNT(u.uid)-1 from ".USERS." u) as count_user
         from ".SYSTEMDB));
   /**
   * форма логина при неаторизированом пользователе
   *          
   */
   function form_login(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return "<form action='index.php?module=account&amp;do=sign' method='post'><div align='center'>
      {$main->lang['login']}: <input type='text' name='user_name' value='' style='margin-right: 10px;' /> 
      {$main->lang['password']}: <input type='password' name='user_password' value='' />
      <input type='submit' value='{$main->lang['send']}' /></div>
      </form>";
   }
   /**
   * парсинг шаблона вывода информации
   * 
   */
   function forum_main_tpl_parse(){
      global $main, $template;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl('forum/show_forum', 'show_forum');
      if(is_user()) $template->template['show_forum'] = preg_replace('/<\!--begin\slogin-->(.+?)<\!--end\slogin-->/si', '', $template->template['show_forum']);
      else $template->set_tpl(array('USER_LOGINED' => form_login()), 'show_forum', array('start' => '{', 'end' => '}'));
      $template->get_subtpl(array(
            array('get_index' => 'show_forum', 'new_index' => 'SUB_CONTENT', 'selector' => ' forum subcategory'),
            array('get_index' => 'show_forum', 'new_index' => 'ROW_CONTENT', 'selector' => ' forum row'),
            array('get_index' => 'show_forum', 'new_index' => 'FORUM_CONTENT', 'selector' => ' cat_forum row'),
         ),array('start' => '{', 'end' => '}'));
   }
   /**
   * выборка из БД информации о дереве категорий форума
   * 
   * @param mixed $treelike
   */
   function forum_main_db_category($treelike = ''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $len=strlen($treelike);
      $maxlen=empty($len)?4:$len+2;
      $sublen=empty($len)?2:$len;
      $dbresult = $main->db->sql_query("
         SELECT c.cat_id,c.cat_title,c.tree,c.description,c.invisible,
         sum(f.forum_topics) as forum_topics,sum(f.forum_posts) as forum_posts,max(f.forum_last_post_id) as forum_last_post_id,
         count(f.forum_id) as count_forum,sum(f.forum_status) as forum_status,max(p.post_time) as post_time,max(p.post_id) as last_post_id,t.topic_last_post_id {FIELDS}
         FROM ".CAT_FORUM." AS c,".CAT_FORUM." AS cf  left join ".FORUMS." AS f on (f.cat_id=cf.cat_id)
         LEFT JOIN  ".TOPICS." AS t ON(t.forum_id=f.forum_id)
         LEFT JOIN  ".POSTS." AS p ON(p.post_id=t.topic_last_post_id) {TABLES}
         where c.tree like '{$treelike}%' and length(c.tree)<={$maxlen} and cf.tree like concat(c.tree,'%') and c.invisible='n' {WHERES}
         group by c.cat_id,c.cat_title,c.tree,c.description,c.invisible
         order by c.tree", __FUNCTION__);
      $ret=array();
      while (($row=$main->db->sql_fetchrow($dbresult))){
         $tree=$row['tree'];
         $row['cats']=array();
         $row['forums']=array();
         forum_open_access_tree($tree);
         if(check_access_forum(accView)) {
            if(strlen($tree)>$sublen){
               $tree=substr($tree,0,-2);
               $ret[$tree]['cats'][$row['tree']]=$row;
            } else $ret[$tree]=$row;
         }
      }
      return $ret;
   }
   /**
   * получаем информацию о доступных форумах
   * 
   * @param array $trees список tree доступных категорий
   */
   function forum_main_forums_db($cats){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return $main->db->sql_query("SELECT f.forum_status, f.forum_id, f.forum_name, f.cat_id, f.forum_posts, f.forum_topics, f.forum_desc, f.forum_last_post_id, f.forum_topics,
         p.post_id, p.topic_id, p.post_time, p.poster_id, p.poster_name,
         t.topic_id, t.topic_title, t.topic_replies, t.topic_time, t.topic_last_post_id,
         u.uid, u.user_id, u.user_name, u.user_avatar, u.user_email {FIELDS}
         FROM ".FORUMS." AS f LEFT JOIN
         ".POSTS." AS p ON(f.forum_last_post_id=p.post_id) LEFT JOIN
         ".TOPICS." AS t ON(p.topic_id=t.topic_id) LEFT JOIN
         ".USERS." AS u ON(p.poster_id=u.uid) {TABLES} where f.cat_id in (".implode(",",$cats).") {WHERES}
         ORDER BY f.cat_id, f.pos",__FUNCTION__);
   }
   /**
   * список пользователей on-line
   * 
   */
   function user_online_list(){
      global $main, $supervision;
      if(hook_check(__FUNCTION__)) return hook();
      $userlist = "";
      foreach(array_merge($supervision['admin'], $supervision['users'], $supervision['bots']) as $key => $value) $userlist .= (!empty($value['user_name'])) ? "<a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($value['user_id'], $value['uid'])))."' title='{$main->lang['user_profile']}' style='color: #{$value['color']}'>{$value['user_name']}</a>, " : "";
      $userlist = mb_substr($userlist, 0, mb_strlen($userlist)-2);
      return $userlist;
   }   

   /**
   * вывод информации по шаблону форуму
   * 
   */
   function forum_main_showtpl($content){
      global $main, $template,$forum, $total_posts, $total_thems, $total_users, $supervision, $curr_tree;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl('show_forum', 'show_forum');
      if(isset($_GET['do']) && $_GET['do']=='showsubforum'){ 
         load_forum_category();
         $new_title=gen_forum_breadcrumb(0,'',$curr_tree);
         $forum_bread_crumb = bcrumb::bread_crumb($new_title, true);
      } else {
         $new_title=gen_forum_breadcrumb(0,'','');
         $forum_bread_crumb = bcrumb::bread_crumb($new_title);
      }
      $template->set_tpl(array('bread_crumbs' => $forum_bread_crumb), 'index');
      $template->set_tpl(hook_set_tpl(array(
               'OPEN_TABLE'          => open(true),
               'CLOSE_TABLE'         => close(true),
               'FORUM_CONTENT'       => $content,

               'MENU_PROFILE'        => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
               'MENU_SEARCH'         => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
               'MENU_USERS'          => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
               'MENU_LOGOUT'         => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",

               'LOAD_TPL'            => $main->tpl,
               'LAST_VISIT_DATE'     => is_user()?$main->lang['forum_last_visit'].": ".format_date(isset($_SESSION['lastVisit'])?$_SESSION['lastVisit']:kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"):"",
               'CURRENT_TIME'        => format_date(kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"),
               'L_WHO_IS_ONLINE'     => $main->lang['who_is_online'],
               'L_NEW_POSTS'         => $main->lang['new_posts'],
               'L_NO_NEW_POSTS'      => $main->lang['no_new_posts'],
               'L_FORUM_LOCKED'      => $main->lang['forum_locked'],
               'TOTAL_POSTS'         => $main->lang['total_posts'].": ".$total_posts,
               'TOTAL_THEMS'         => $main->lang['total_thems'].": ".$total_thems,
               'TOTAL_USERS'         => $main->lang['total_users'].": ".$total_users,
               'TOTAL_USERS_ONLINE'  => $main->lang['total_users_online'].": ".(count(array_merge($supervision['admin'], $supervision['users'], $supervision['bots'], $supervision['guest']))),
               'LOGGED_IN_USER_LIST' => $main->lang['logged_in_user_list'].": ".user_online_list(),
               'L_INDEX'             => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
               'FORUM_BREAD_CRUMB'   => '',
               'L_SEARCH_NEW'        => is_user()?("<a href='".$main->url(array('module' => $main->module, 'do' => 'unanswered'))."' title='{$main->lang['find_not_read']}'>{$main->lang['find_not_read']}</a>"):"",
               'L_MARK_ALLREAD'      => is_user()?("<a href='".$main->url(array('module' => $main->module, 'do' => 'markallread'))."' title='{$main->lang['markallread']}'>{$main->lang['markallread']}</a>"):"",
               'L_SEARCH_SELF'       => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'egosearch', 'id' => kr_encodeurl($main->user['user_name'])))."' title='{$main->lang['search_self']}'>{$main->lang['search_self']}</a>":"",
               'L_SEARCH_UNANSWERED' => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'newposts'))."' title='{$main->lang['search_unanswered']}'>{$main->lang['search_unanswered']}</a>":"",
               'FORUM_MODER'         => "",
            ),__FUNCTION__), 'show_forum', array('start' => '{', 'end' => '}'));
      $template->tpl_create(false, 'show_forum');
   }

   function forum_main_category_tpl($row){
      global $main, $template, $curr_tree;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl('FORUM_CONTENT', 'FORUM_CONTENT');
      $forum_cat = array(
         'OPEN_TABLE'             => open(true),
         'CLOSE_TABLE'            => close(true),
         'ROW_CONTENT'            => forum_main_forums_content($row['forums']),
         'SUB_CONTENT'            => forum_main_subcat_content($row['cats']),
         'ROW_STYLE'              => "row2",
         'L_FORUM'                => $main->lang['forum'],
         'L_TOPICS'               => $main->lang['topics'],
         'L_POSTS'                => $main->lang['posts'],
         'L_LASTPOST'             => $main->lang['lastpost'],
         'catrow.CAT_DESC_ANCHOR' => "<a name=\"forum_cat_{$row['cat_id']}\" href=\"#\"></a>",
         'catrow.CAT_DESC'        => ($curr_tree!=$row['tree'])?$row['cat_title']:"",
      );
      $template->set_tpl(hook_set_tpl($forum_cat, __FUNCTION__), 'FORUM_CONTENT', array('start' => '{', 'end' => '}'));
      return $template->tpl_create(true, 'FORUM_CONTENT');
   }
   /**
   * вид записи форума по шаблону
   * 
   * @param array $row запись с информацией по форуму
   */
   function forum_main_forums_tpl($row){
      global $main, $template;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl('ROW_CONTENT', 'ROW_CONTENT');
      $floder = get_folder_forum($row['topic_id'], $row['topic_last_post_id'], $row['forum_status']);
      $topic_title = cut_text($row['topic_title'], 26);
      $forum_row = array(
         'catrow.forumrow.FORUM_NAME'           => "<a class='forumlink sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $row['forum_id']))."' title='{$row['forum_name']}'>{$row['forum_name']}</a>",
         'catrow.forumrow.FORUM_DESC'           => $row['forum_desc'],
         'catrow.forumrow.SUBFORUM_MODER'       => forum_list_moderators($row['tree'],$row['forum_id'],false,true),
         'catrow.forumrow.TOPICS'               => $row['forum_topics'],
         'catrow.forumrow.POSTS'                => $row['forum_posts'],
         'catrow.forumrow.LAST_POST_TITLE'      => !empty($row['poster_name']) ? "<img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/topic.png' align='left' alt='' /><a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $row['topic_id']))."' title='{$row['topic_title']}'>".$topic_title."</a>" : "<center class='noposts'>{$main->lang['noposts']}</center>",
         'catrow.forumrow.LAST_POST_TIME'       => !empty($row['poster_name']) ? user_format_date(gmdate("Y-m-d H:i:s", $row['post_time']), true) : "",
         'catrow.forumrow.LAST_POST_AUTHOR'     => forum_last_post_user($row, $row['poster_id'], $row['uid'], $row['user_id'], $row['poster_name']),
         'catrow.forumrow.LAST_POST_IMG'        => !empty($row['topic_title']) ? "<a href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $row['topic_id']))."'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_topic_latest.png' alt='' /></a>" : "",
         'catrow.forumrow.FORUM_FOLDER_IMG'     => $floder[0],
         'catrow.forumrow.L_FORUM_FOLDER_ALT'   => $floder[1],
         'LOAD_TPL'                             => $main->tpl
      );
      $template->set_tpl(hook_set_tpl($forum_row, __FUNCTION__), 'ROW_CONTENT', array('start' => '{', 'end' => '}'));
      return $template->tpl_create(true, 'ROW_CONTENT');
   }
   /**
   * вывод подкатегории по шаблону
   * 
   * @param array $row
   */
   function forum_main_subcat_tpl($row){
      global $main, $template;
      if(hook_check(__FUNCTION__)) return hook();
      $template->get_tpl('SUB_CONTENT', 'SUB_CONTENT');
      $href=$main->url(array('module' => $main->module, 'do' => 'showsubforum','id'=>$row['tree']));
      if(!isset($row['topic_title'])) $row['poster_name']=$row['poster_id']=$row['user_id']=$row['uid']=$row['user_name']=$row['topic_id']=$row['topic_title']='';
      $floder = get_folder_forum($row['topic_id'], $row['topic_last_post_id'],$row['forum_status']==$row['count_forum']?"1":"0");
      $topic_title = cut_text($row['topic_title'], 26);
      $sf_row = array(
         'subforum.title'                =>"<a href='{$href}'>{$row['cat_title']}</a>",
         'subforum.links'                =>'', //$row['lista'],
         'subforum.description'          =>$row['description'],
         'subforum.catforum_moder'       =>forum_list_moderators($row['tree'],0,false,true),
         'subforum.TOPICS'               =>$row['forum_topics'],
         'subforum.POSTS'                =>$row['forum_posts'],
         'subforum.FORUM_FOLDER_IMG'     => $floder[0],
         'subforum.L_FORUM_FOLDER_ALT'   => $floder[1],
         'subforum.LAST_POST_TITLE'      => !empty($row['poster_name']) ? "<img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/topic.png' align='left' alt='' /><a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $row['topic_id']))."' title='{$row['topic_title']}'>".$topic_title."</a>" : "<center class='noposts'>{$main->lang['noposts']}</center>",
         'subforum.LAST_POST_TIME'       => !empty($row['poster_name']) ? user_format_date(gmdate("Y-m-d H:i:s", $row['post_time']), true) : "",
         'subforum.LAST_POST_AUTHOR'     => forum_last_post_user($row, $row['poster_id'], $row['uid'], $row['user_id'], $row['poster_name']),
         'subforum.LAST_POST_IMG'        => !empty($row['topic_title']) ? "<a href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $row['topic_id']))."'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_topic_latest.png' alt='' /></a>" : ""
      );
      $template->set_tpl(hook_set_tpl($sf_row, __FUNCTION__), 'SUB_CONTENT', array('start' => '{', 'end' => '}'));
      return $template->tpl_create(true, 'SUB_CONTENT');
   }
   /**
   * возвращает контент списка форумов
   * 
   * @param array $flist список форумов
   */
   function forum_main_forums_content($flist){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $result='';
      if(!empty($flist)){
         foreach ($flist as $key => $value) {
            forum_open_access_forum($value['tree'],$value['forum_id']);
            if(check_access_forum(accView)) $result.=forum_main_forums_tpl($value);
         }
      }
      return $result;
   }

   function forum_main_subcat_content($info){
      if(hook_check(__FUNCTION__)) return hook();
      $result='';
      if(!empty($info)){
         foreach ($info as $key => $value) {
            forum_open_access_tree($value['tree']);
            if(check_access_forum(accView)) $result.=forum_main_subcat_tpl($value);
         }
      }
      return $result;
   }

   function forum_main_forum_content($info){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $return='';
      foreach ($info as $key => $value) {if(!empty($value['cats']) OR !empty($value['forums'])) $return.=forum_main_category_tpl($value);}
      return $return;
   }

   /**
   * список ID доступных категорий форума
   * 
   * @param mixed $arr
   */
   function forum_cats_list($arr){
      if(hook_check(__FUNCTION__)) return hook();
      $trees=array();
      foreach ($arr as $key => $value) {
         $trees[]=$value['cat_id'];
         if(isset($value['cats']) AND count($value['cats']>0)) {
            $r=forum_cats_list($value['cats']);
            $trees= array_merge($trees,$r);
         }
      }
      return $trees;
   }
   function forum_main_load_forums($db, &$arr){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $cid=array();
      foreach ($arr as $key => $value) {
         $cid[$value['cat_id']]=&$arr[$key];
         if(isset($value['cats']) AND count($value['cats']>0)) {
            foreach ($value['cats'] as $k => $v) {$cid[$v['cat_id']]=&$arr[$key]['cats'][$k];}
         }
      }
      $count=0;
      while (($row=$main->db->sql_fetchrow($db))){
         $row['tree']=$cid[$row['cat_id']]['tree'];
         $cid[$row['cat_id']]['forums'][]=$row;
         $count++;
      }
      return $count;
   }
   /**
   * основная функция показа форума
   * 
   */
   function forum_showmain($tree=''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      forum_main_tpl_parse();
      $r = forum_main_db_category($tree);
      $c = array();
      if(!empty($tree)){
         forum_open_access_tree($tree);
         $access=check_access_forum(accView);
      } else $access=true;
      if($access){
         open_forum_moder($tree);
         $cats = forum_cats_list($r);
         $posts_sub=array();
         $sub_cat_posts=array();
         if(!empty($cats)){
            foreach ($r as $key => $value) {
               if(!empty($value['cats'])){
                  foreach ($value['cats'] as $k => $v) {
                     if(!empty($v['last_post_id'])) {$posts_sub[]=$v['last_post_id']; 
                        $sub_cat_posts[$v['last_post_id']]=&$r[$key]['cats'][$k];
                     }
                  }
               }
            }
            if(!empty($posts_sub)){
               $posts=array();
               $main->db->sql_query("SELECT  p.post_id, p.topic_id, p.post_time, p.poster_id, p.poster_name, t.topic_title, u.uid, u.user_id, u.user_name, u.user_avatar, u.user_email 
                  FROM ".POSTS." AS p LEFT JOIN ".TOPICS." AS t ON(t.topic_id=p.topic_id) LEFT JOIN ".USERS." AS u ON(p.poster_id=u.uid) where p.post_id in (".implode(",",$posts_sub).")");
               while (($row=$main->db->sql_fetchrow())){
                  $sub = &$sub_cat_posts[$row['post_id']];
                  $sub= array_merge($sub, $row);
               }
            }
            $db = forum_main_forums_db($cats);
            $c = forum_main_load_forums($db, $r);
         } else $c =array();
      }
      $content=(empty($c) AND empty($r))?info($main->lang['noforumlist'], true):forum_main_forum_content($r);
      forum_main_showtpl($content);
   }
   $curr_tree=(isset($_GET['do']) AND $_GET['do']=='showsubforum')?$_GET['id']:"";
   forum_showmain($curr_tree);
?>
