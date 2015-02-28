<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('ADMIN_FILE')) die("Hacking attempt!");

   global $navi, $main, $break_load;
   $break_load=false;
   if(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
      warning($main->lang['admin_error']);
      $break_load = true;
   }

   main::required( "modules/{$main->module}/function.php", 
      "modules/{$main->module}/templates.php", 
      "modules/{$main->module}/accinfo.php");
   main::init_language('forum');

   $navi = array(
      array('', 'home'),
      array('admin_access_rights', 'access_rights'),
      array('access_detail', 'forum_access_detail'),
      array('admin_sort_control', 'sort_control'),
      array('config', 'config'),
   );
   function admin_redirect_parent($ref=''){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if($ref!="") redirect($ref);
      elseif($main->ref!="") redirect($main->ref);
      else redirect(MODULE);
   }

   /**
   * deprecated 
   */

   function admin_new_admin_main_forum(){
      global $main, $adminfile,$load_tpl,$tpl_create;
      if(hook_check(__FUNCTION__)) return hook();
      main::add2link("includes/css/treeviewli.css");
      main::add2script("includes/javascript/jquery/treeviewli.js");
      main::add_css2head("
         .notable td {padding: 3px}
         .treeviewli ul {margin-top: 0 !important;}
      ");
      main::init_class('li_tree');
      $subtree=(isset($_GET['id']))?$_GET['id']:"";
      $open=loads_tpl(TEMPLATE_PATH."{$load_tpl}/open_table");
      $close=loads_tpl(TEMPLATE_PATH."{$load_tpl}/close_table");
      $result = $main->db->sql_query("SELECT * FROM ".CAT_FORUM." where tree like '{$subtree}%' order by tree");
      $litree=new li_tree('fcaregory','tree_c treeviewli');
      $litree->class_tree='mfcategory';
      $litree->prefix_tree='mf';
      $litree->show_id=true;
      $litree->load_db($result,'cat_title','cat_id','tree');
      $result = $main->db->sql_query("SELECT f.forum_id, f.forum_name, f.cat_id, f.forum_posts, f.forum_topics, f.forum_desc, f.forum_last_post_id, f.forum_topics, c.cat_id, c.cat_title, c.cat_sort, c.tree".
         ",(select count(p.forum_id) from ".FORUMS." AS p where cat_id = c.cat_id) as count_forums ".
         " FROM ".CAT_FORUM." AS c,".FORUMS." AS f where c.tree like '{$subtree}%' and f.cat_id=c.cat_id ORDER BY c.tree, pos");    
      $tree="";
      foreach ($litree->tree_direct as $key => $value) {
         $_cat_id=substr($value['id'],2);
         $tree_id=$value['tree'];
         $caption="<a href='{$adminfile}?module={$_GET['module']}&amp;do=subcategory&amp;id={$value['tree']}'>{$value['caption']}</a>";
         $aclass=count($litree->tree_direct[$key]['children'])>0?" libg_forum":"";
         $litree->tree_direct[$key]['mcaption']="<table width='100%' class='litable_forum{$aclass}' cellpadding='3' cellspacing='1'>\n".
         "<tr class='forum_cat'>\n".
         "<th >{$caption}</th>\n".
         "<th style='text-align: center;' width='100'><a class='forum_adm_link' href='{$adminfile}?module={$_GET['module']}&amp;do=admin_edit_cat&amp;id={$_cat_id}' title='{$main->lang['edit']}'>{$main->lang['edit']}</a></th>\n".
         "<th style='text-align: center;' width='100'><a onclick=\"return admin_delete_cat_forum('{$main->lang['realdelete']}');\" class='forum_adm_link' href='{$adminfile}?module={$_GET['module']}&amp;do=delete_cat&amp;id={$_cat_id}' title='{$main->lang['delete']}'>{$main->lang['delete']}</a></th>\n".
         "</tr>\n".
         "<tr><td colspan='3'>".$open."<table cellspacing='1' class='cl' width='100%'>";
      }
      if ($main->db->sql_numrows($result)>0){
         $_cat_id = 0; $_count = 1; $row_tr = 'row4';
         $content="";
         while(($row = $main->db->sql_fetchrow($result))){
            if($tree!=$row['tree']){
               if($tree!=""&&$content!="") {$car=&$litree->get_parent($tree);$car['mcaption'].=$content;}
               $content="";
               $tree=$row['tree'];
               $_count = 1;
            }
            $_cat_id = $row['cat_id'];
            $count_forums=$row['count_forums'];
            $dcl=(($_count!=1)&&($_count<$count_forums))?"two":"one";
            $top = ($_count!=1) ? up_button("{$adminfile}?module={$main->module}&amp;do=admin_move_forum&amp;id={$row['forum_id']}&amp;type=up&amp;", 'ajax_content') : "";
            $down = ($_count<$count_forums) ? down_button("{$adminfile}?module={$main->module}&amp;do=admin_move_forum&amp;id={$row['forum_id']}&amp;type=down", 'ajax_content') : "";
            $op_forum = edit_button("{$adminfile}?module={$_GET['module']}&amp;do=admin_edit_forum&amp;id={$row['forum_id']}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=admin_delete_forum&amp;id={$row['forum_id']}", 'ajax_content');
            $content.= "<tr class='{$row_tr}'>".
            "<td height='50' style='padding-left: 20px;' class='form_text2'><a href='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $row['forum_id']))."' title='{$row['forum_name']}' target='_BLANK'><b>{$row['forum_name']}</b></a><br />".(!empty($row['forum_desc'])?"<i>{$row['forum_desc']}</i>":"")."</td>".
            "<td width='35' align='center'>{$row['forum_topics']}</td>".
            "<td width='35' align='center'>{$row['forum_posts']}</td>".
            "<td width='45' align='center' class='{$dcl}'>{$top}{$down}</td>".
            "<td width='50' align='center'>{$op_forum}</td>".
            "</tr>\n";
            $row_tr = ($row_tr!='row4') ? 'row4' : 'row5';
            $_count++;
         }
         if($tree!=""&&$content!="") {$car=&$litree->get_parent($tree);$car['mcaption'].=$content;}
         foreach ($litree->tree_direct as $key => $value) {
            $_cat_id=$value['id'];
            $tree_id=$value['tree'];
            $litree->tree_direct[$key]['mcaption'].=
            "<tr><td colspan='5'><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_forum&amp;cid={$_cat_id}'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='forum' /></td><td width='200' align='right'><input class='button_forum2' type='submit' value='{$main->lang['new_forum']}' /></td></tr></table></form></td></tr>".
            "<tr><td colspan='5'><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_sub_category&amp;parent={$tree_id}'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='cat' /></td><td width='200' align='right'><input class='button_forum' type='submit' value='{$main->lang['new_sub_forum']}' /></td></tr></table></form></td></tr>".
            "</table>".$close."</td></tr></table>\n";
         }
         //$car['ul']="<li style='padding-bottom: 0;padding-left: 0;padding-top: 0;'>".$open."test".$close."</li>";
         $litree->echo_html();
         if($subtree=='')  echo "<table cellspacing='1' class='cl' width='100%'>".
            "<tr><td><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_category'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='cat' /></td><td width='200' align='right'><input class='button_forum' type='submit' value='{$main->lang['new_forum_cat']}' /></td></tr></table></form></td></tr>".
            "</table>";
      }  else {        
         foreach ($litree->tree_direct as $key => $value) {
            $_cat_id=$value['id'];
            $tree_id=$value['tree'];
            $litree->tree_direct[$key]['mcaption'].=
            "<tr><td colspan='5'><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_forum&amp;cid={$_cat_id}'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='forum' /></td><td width='180' align='right'><input class='button_forum2' type='submit' value='{$main->lang['new_forum']}' /></td></tr></table></form></td></tr>".
            "<tr><td colspan='5'><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_sub_category&amp;parent={$tree_id}'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='cat' /></td><td width='180' align='right'><input class='button_forum' type='submit' value='{$main->lang['new_sub_forum']}' /></td></tr></table></form></td></tr>".
            "</table>".$close."</td></tr></table>\n";
         }
         $litree->echo_html();
         echo "<br /><form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_new_category'><table cellpadding='0' cellspacing='0' width='100%' class='notable'><tr><td><input type='text' style='width: 100%;' name='cat' /></td><td width='180' align='right'><input class='button_forum2' type='submit' value='{$main->lang['new_forum_cat']}' /></td></tr></table></form>";
      }
   ?>
   <script type="text/javascript">
      //<![CDATA[
      $('#fcaregory').treeviewli({collapsed: false,selected:true,multiselect:false,togglediv:true});
      //]]>
   </script>
   <?php
   }

   function admin_new_category(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $title = trim($_POST['cat']);
      if(!empty($title)){ 
         main::init_class('treedb');
         $treed=new treedb(CAT_FORUM,2);
         $treed->append('',"INSERT INTO ".CAT_FORUM." (cat_title,tree) VALUES ('{$title}','{IDTREE}')");
         $idc=$main->db->sql_nextid();
         $main->db->sql_query("INSERT INTO ".FORUM_ACC." (ugid,thisuser,typeacc,idv,acc_view,acc_read,acc_write,acc_post,acc_edit,acc_delete,acc_upload,acc_download,acc_voting) VALUES
            (1,'g','c',{$idc},1,1,1,1,1,1,1,1,1),
            (2,'g','c',{$idc},1,1,1,1,1,1,1,1,1),
            (5,'g','c',{$idc},1,1,1,1,0,0,1,1,1),
            (4,'g','c',{$idc},1,1,0,0,0,0,0,0,0),
            (3,'g','c',{$idc},1,1,0,0,0,0,0,0,0)");
         admin_forum_change();
         redirect(MODULE);
      } else { 
         warning($main->lang['no_name_new_cat']);
      }
   }

   function admin_new_forum(){
      global $main; 
      if(hook_check(__FUNCTION__)) return hook();
      $title = trim($_POST['forum']);
      $id=$_GET['cid'];if(!is_numeric($id)) $id=substr($id,2);
      if(!empty($title)){
         $main->db->sql_query("INSERT INTO ".FORUMS." (forum_name, cat_id, pos) 
            select '{$title}', '{$id}', if(isnull(max(n.pos)),10,max(n.pos)+10) as npos from ".FORUMS." AS n where cat_id={$id}");
         admin_forum_change();   
         admin_redirect_parent();
      } else {
         warning($main->lang['no_name_admin_new_forum']);
      }
   }
   function admin_new_sub_category(){
      global $main; 
      if(hook_check(__FUNCTION__)) return hook();
      main::init_class('treedb');
      $title = trim($_POST['cat']);
      $parent = trim($_GET['parent']);
      if(!empty($title)&&!empty($parent)){ 
         $dbtree = new treedb(CAT_FORUM,2);
         $dbtree->append($parent,"INSERT INTO ".CAT_FORUM." (cat_title,tree) VALUES ('{$title}','{IDTREE}')");
         admin_forum_change();
         admin_redirect_parent();
      } else { 
         warning($main->lang['no_name_new_cat']);
      }
   }
   function admin_edit_forum($msg=""){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($msg)) warning($msg);
      $forum_info = $main->db->sql_fetchrow($main->db->sql_query("SELECT forum_id, cat_id, forum_name, forum_desc, forum_status FROM ".FORUMS." WHERE forum_id='{$_GET['id']}'"));
      $result = $main->db->sql_query("SELECT cat_id, cat_title FROM ".CAT_FORUM." ORDER BY cat_sort");
      $cat = array();
      $this_cat = 0;
      while(($row = $main->db->sql_fetchrow($result))){
         $cat[$row['cat_id']] = $row['cat_title'];
         $this_cat = ($row['cat_id']==$forum_info['cat_id']) ? $row['cat_id'] : $this_cat;
      }
      $sels = array('0' => $main->lang['show_guest'], '1' => $main->lang['show_user'], '2' => $main->lang['show_moder'], '3' => $main->lang['show_admin']);
      echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_save_edit_forum&amp;id={$_GET['id']}'>\n".
      in_hide('reference', htmlspecialchars($main->ref)).
      "<table width='100%' align='center' class='form' id='form_{$main->module}'>\n".
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['title_forum']}:</td><td class='form_input2'>".in_text('title', 'input_text2', $forum_info['forum_name'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['forum_desc']}:</td><td class='form_input2'>".in_area('desc', 'textarea', 3, $forum_info['forum_desc'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['enabled_forum']}:</td><td class='form_input2'>".in_chck('status', 'input_checkbox', ($forum_info['forum_status']==0)?ENABLED:'')."</td></tr>\n".
      "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
      "</table></form>";
   }
   function admin_save_group_str($post_param){
      if(hook_check(__FUNCTION__)) return hook();
      $group = "";
      if(isset($_POST[$post_param]) AND is_array($_POST[$post_param]) AND count($_POST[$post_param])>0) foreach($_POST[$post_param] as $value) $group .= $value.",";
      return $group;
   }
   function admin_save_edit_forum(){
      global $main;    
      if(hook_check(__FUNCTION__)) return hook();
      $msg = (empty($_POST['title'])) ? $main->lang['no_name_admin_new_forum'] : "";
      $status = (!isset($_POST['status']) OR $_POST['status']!="on") ? "1" : "0";
      if(empty($msg)){
         sql_update(array(
               'forum_name'   => $_POST['title'],
               'forum_desc'   => $_POST['desc'],
               'forum_status' => $status
            ), FORUMS, "forum_id='{$_GET['id']}'");
         admin_redirect_parent(isset($_POST['reference'])?$_POST['reference']:"");
      } else admin_edit_forum($msg);
   }

   function admin_delete_forum(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      //Выбираем все темы заданного форума
      $topics = $main->db->sql_query("SELECT topic_id FROM ".TOPICS." WHERE forum_id='{$_GET['id']}'");
      //Перебираем все записи
      while(list($topic_id) = $main->db->sql_fetchrow($topics)){
         //Выбираем все сообщения заданной темы
         $posts = $main->db->sql_query("SELECT post_id, poster_id FROM ".POSTS." WHERE topic_id='{$topic_id}'");
         while(list($post_id, $poster_id) = $main->db->sql_fetchrow($posts)){
            //Отнимаем у пользователя 1 пост
            update_posts($poster_id, "-");
            //Удаляем прикрепленные файлы 
            if(file_exists($forum['directory'].$post_id)){
               $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$forum['directory']}{$post_id}/%'");
               remove_dir($forum['directory'].$post_id);
            }
         }
         //Удаляем топик 
         $main->db->sql_query("DELETE FROM ".TOPICS." WHERE topic_id='{$topic_id}'");
         //Удаляем все сообщения топика
         $main->db->sql_query("DELETE FROM ".POSTS." WHERE topic_id='{$topic_id}'");
      }  
      //Удаляем форум  
      $main->db->sql_query("DELETE FROM ".FORUM_ACC." WHERE typeacc = 'f' and idv='{$_GET['id']}'");
      $main->db->sql_query("DELETE FROM ".FORUMS." WHERE forum_id='{$_GET['id']}'");
      if(!is_ajax()) admin_redirect_parent();
      else admin_new_admin_main_forum();
   }
   function admin_parse_rewrite($href,$mod=array()){
      global $main,$config;
      if(hook_check(__FUNCTION__)) return hook();
      $u = parse_url($href);
      $REQUEST_URI=$u['path'].(array_key_exists('query',$u)?"?".$u['query']:"");
      $_SERVER['REQUEST_URI']=$REQUEST_URI;
      if ($config['rewrite']==""){
         parse_str($u['query'], $s);
         $_GET=$s;
      } else {
         $main->parse_rewrite($mod);
      }
   }

   function admin_move_forum(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $down=$_GET['type']=="down";
      $main->db->sql_query("SELECT f1.forum_id as id1,f2.forum_id as id2,f1.pos as pos1,f2.pos as pos2 
         FROM ".FORUMS." f1,".FORUMS." f2 
         WHERE f1.forum_id=".intval($_GET['id'])." and f2.cat_id=f1.cat_id and f2.pos".($down?">":"<")."f1.pos order by f2.pos ".($down?"asc":"desc")." limit 1");
      if($main->db->sql_numrows()!=0){
         list($id1,$id2,$pos1,$pos2)=$main->db->sql_fetchrow();
         $sql="update ".FORUMS." set pos=(case forum_id when {$id1} then {$pos2}
         when {$id2} then {$pos1} end) where forum_id in ({$id1},{$id2})";
         $main->db->sql_query($sql);
      }
      if(!is_ajax()) admin_redirect_parent();
      else {admin_parse_rewrite($main->ref); admin_new_admin_main_forum();}
   }

   function admin_config_forum(){
      global $forum, $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('admmodulecontrol');
      $forums_arr=array(0=>'&nbsp; ');
      $result = $main->db->sql_query("SELECT forum_id, forum_name, cat_id FROM ".FORUMS." ORDER BY cat_id, forum_id");
      while(($rows_forum = $main->db->sql_fetchrow($result))){
         $forums_arr[$rows_forum['forum_id']] = $rows_forum['forum_name'];
      }
      $groups=array(0=>$main->lang['forum_rss_all']);
      $main->db->sql_query("select id,title from ".GROUPS);
      while ((list($gid,$gtitle)=$main->db->sql_fetchrow())){
         $groups[$gid]=$main->lang['forum_rss_only'].$gtitle;
      }
      echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['forum_title']}</b>:<br /><i>{$main->lang['forum_title_d']}</i></td><td class='form_input2'>".in_text('forum_title', 'input_text2', $forum['forum_title'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $forum['directory'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['topic_views_num']}</b>:<br /><i>{$main->lang['topic_views_num_d']}</i></td><td class='form_input2'>".in_text('topic_views_num', 'input_text2', $forum['topic_views_num'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['post_views_num']}</b>:<br /><i>{$main->lang['post_views_num']}</i></td><td class='form_input2'>".in_text('post_views_num', 'input_text2', $forum['post_views_num'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $forum['attaching_files_type'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_width']}</b>:<br /><i>{$main->lang['miniature_image_width_d']}</i></td><td class='form_input2'>".in_text('miniature_image_width', 'input_text2', $forum['miniature_image_width'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_height']}</b>:<br /><i>{$main->lang['miniature_image_height_d']}</i></td><td class='form_input2'>".in_text('miniature_image_height', 'input_text2', $forum['miniature_image_height'])."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $forum['max_image_width'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $forum['max_image_height'])."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $forum['attaching_files_size'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['file_upload_limit']}</b>:<br /><i>{$main->lang['file_upload_limit_d']}</i></td><td class='form_input2'>".in_text('file_upload_limit', 'input_text2', $forum['file_upload_limit'])."</td></tr>\n".                    
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching']}</b>:<br /><i>{$main->lang['attaching_d']}</i></td><td class='form_input2'>".in_chck('attaching', 'input_checkbox', $forum['attaching'])."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['forum_timeout']}</b>:<br /><i>{$main->lang['forum_timeout_d']}</i></td><td class='form_input2'>".in_text('timeout', 'input_text2', $forum['timeout'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['trash_forum']}</b>:<br /><i>{$main->lang['trash_forum_d']}</i></td><td class='form_input2'>".in_sels('trashforum',$forums_arr,'select chzn-default', isset($forum['trashforum'])?$forum['trashforum']:'-1')."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_title']}</b>:<br /><i>{$main->lang['rss_title_d']}</i></td><td class='form_input2'>".in_text('rss_title', 'input_text2', isset($forum['rss_title'])?$forum['rss_title']:"Sitename - Forum|")."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_limit']}</b>:<br /><i>{$main->lang['rss_limit_d']}</i></td><td class='form_input2'>".in_text('rss_limit', 'input_text2', isset($forum['rss_limit'])?$forum['rss_limit']:"")."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_rss']}</b>:<br /><i>{$main->lang['conf_rss_d']}</i></td><td class='form_input2'>".in_chck('rss', 'input_checkbox', isset($forum['rss'])?$forum['rss']:'off')."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['forum_rss_method']}</b>:<br /><i>{$main->lang['forum_rss_method_d']}</i></td><td class='form_input2'>".in_sels('rss_filter', $groups,'select', isset($forum['rss_filter'])?$forum['rss_filter']:'0')."</td></tr>\n".
      module_control_config().
      "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".    
      "</table></form>";
   }

   function admin_save_forum(){
      global $forum, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('sources');
      $forum['trashforum']=$_POST['trashforum']==0?"":$_POST['trashforum'];
      $forum['rss_title']=$_POST['rss_title'];
      $forum['rss_limit']=$_POST['rss_limit'];
      $forum['rss']=isset($_POST['rss'])?$_POST['rss']:'off';
      $forum['rss_filter']=$_POST['rss_filter'];
      save_config('config_forum.php', '$forum', $forum);
      update_rss_config();
      main::init_function('admmodulecontrol'); module_control_saveconfig();
      redirect("{$adminfile}?module={$_GET['module']}&do=config");
   }

   function admin_access_control(){
      global $main,$tpl_create,$adminfile,$lang_acc;
      if(hook_check(__FUNCTION__)) return hook();
      main::add2link("includes/css/treeviewli.css");
      main::add2script("includes/javascript/jquery/treeviewli.js");
      main::add2script("includes/javascript/jquery/jquery.disable.text.select.js");
      main::add_css2head("
         #src_acc input,#src_acc label,#src_acc a{vertical-align: middle;}
         #src_acc span{margin:4px;}
         #src_acc input{margin:2px 4px 4px 0;height:14px;}
         #src_acc input:focus {
         outline: none;
         -moz-outline-offset: -1px !important; -moz-outline: 1px solid #000 !important;
         }
         #src_acc label{margin:4px 4px 4px 1px;cursor:pointer}
         .exists > span{text-decoration:underline}
         .tree_cat{background: transparent url('includes/images/16x16/category.png') no-repeat 3px 1px;}
         .tree_forum{background: transparent url('includes/images/16x16/forum_16.png') no-repeat 3px 1px;}
         .treeviewli span {padding: 3px 10px 3px 23px !important;}
         #sd_object{display:none; position:absolute; -moz-box-shadow: 0 0 12px #555555; background-color: #FFFFFF;
         -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;padding:4px;overflow:auto;}
         #tbSelUser td{cursor:pointer;padding-left:5px}
         #tbSelUser tr.active td {background-color: #C0FFC0 !important}
         .active {background-color: #C0FFC0 !important}
         span.silver{margin:4px;background: url('".TEMPLATE_PATH."{$main->tpl}/images/checkbox_silver.gif') no-repeat scroll left center transparent;}
         span.allset{margin:4px;background: url('".TEMPLATE_PATH."{$main->tpl}/images/checkbox_allset.png') no-repeat scroll left center transparent;}
         span.silver input,span.allset input{ margin: 2px 4px 4px 0 !important;height:14px;
         filter:progid:DXImageTransform.Microsoft.Alpha(opacity=10); /* IE 5.5+*/
         -moz-opacity: 0.1; /* Mozilla 1.6 */
         -khtml-opacity: 0.1; /* Konqueror 3.1, Safari 1.1 */
         opacity: 0.1; /* CSS3 - Mozilla 1.7b +, Firefox 0.9 +, Safari 1.2+, Opera 9 */
         }
         span.silver label{color: silver}
         a.btn {background: url('".TEMPLATE_PATH."{$main->tpl}/images/bk_button2.png') repeat scroll 0 0 #666666;
         border: 1px solid #387CAC;cursor: pointer;
         color: #FFFFFF;padding: 1px 9px;margin: 4px;}
         #terminator td{border-bottom: 2px solid black !important;padding: 1px;} 
         .usel td{border-bottom: 2px dotted silver}
         #existsuser table {border:none}
         #existsuser table td {border:1px solid silver}
         .usersel{cursor:pointer}
         #dvas {border-bottom:1px solid; margin-bottom:1px;}
         #dvas div{color:green;cursor:pointer;width:32%;float:left;}
         #srcList tr{cursor:pointer}
         #srcList tr.select{background-color: #8080FF !important;color:white;}
         .type_sel{font-weight: bolder}
         #timerajax_src{cursor:pointer; background-color:#E0E0E0; -webkit-border-radius: 3px;-moz-border-radius: 3px; border-radius: 3px;line-height:35px;}
         #timerajax_src div{background: transparent url('includes/images/loading/timer.gif') top left no-repeat;
         padding-left:35px;font-size:1.1em; width: auto; margin: 0pt auto; display: table;
         margin-top:4px}
         #btnc {text-align:right; border-bottom: 2px solid; padding-bottom: 3px;}  
         #btnc span{}
         ");
      main::init_class('li_tree');
      $dbr=$main->db->sql_query("SELECT * FROM ".CAT_FORUM." order by tree");
      $litr=new li_tree('trall','tree_c treeviewli');
      $litr->class_tree='tree_cat';
      $litr->prefix_tree='mc';
      $litr->show_id=true;
      $litr->load_db($dbr,'cat_title','cat_id','tree');
      $litr->class_tree='tree_forum';
      $litr->prefix_tree='mf';
      $dbr=$main->db->sql_query("SELECT f.forum_id, f.forum_name, concat(c.tree,'00') as tree
         FROM ".FORUMS." AS f LEFT JOIN ".CAT_FORUM." AS c ON(f.cat_id=c.cat_id)
      ORDER BY c.tree, f.forum_name");
      $litr->load_db($dbr,'forum_name','forum_id','tree');
      echo "<div id='src_tree'>";
      $litr->echo_html();
      echo "<div style='display:none'>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=acc_info' id='ajax1'></a>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=admin_acc_save' id='ajax2'></a>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=admin_list_user' id='ajax3'></a>";
      echo "</div>";
      echo "</div>";
      echo "<div id='src_acc' style='display:none'>";
      open();
      echo "<div id='dvas'>";
      echo "<div id='agroups' style='text-align:left;'>{$main->lang['ad_groups']}</div>";
      echo "<div id='ausers' style='text-alignt:center;'>{$main->lang['ad_users']}</div>";
      echo "<div id='asetup' style='text-align:right;'>{$main->lang['exists_acc']}</div>";
      echo "<br clear='all' />";
      echo "</div>";
      echo "<div id='srcList'>";
      $main->db->sql_query("select distinct u.uid,u.user_name from ".USERS." u left join ".FORUM_ACC." fa on (fa.thisuser='u' and fa.ugid=u.uid)  where not (fa.id is null)");
      echo "<div id='existsuser' style='display:none;overflow:auto;max-height:300px'>";
      if($main->db->sql_numrows()!=0){
         echo "<table class='table' width='98%'>".
         "<tr><th width='100'></th><th></th></tr>";
         while ($row=$main->db->sql_fetchrow()){
            echo "<tr class='usel' id='u{$row['uid']}'><td>{$main->lang['user']}</td><td class='usersel'>".in_hide("userex[]","u{$row['uid']}")."{$row['user_name']}</td></tr>";
         }
         echo "</table>";
      }
      echo "</div>";

      echo "<div id='listgroups' style='display:none;overflow:auto;max-height:300px'>";
      echo "<table id='tbGroups' class='table' width='98%'>".
      "<tr><th width='100'></th><th></th></tr>";
      $main->db->sql_query("select * from ".GROUPS);
      while (($row=$main->db->sql_fetchrow())){
         echo "<tr id='g{$row['id']}' class='usel'><td>{$main->lang['group']}</td><td>{$row['title']}</td></tr>";
      }
      echo "</table>";
      echo "</div>";

      echo "<div id='listusers' style='display:none;overflow:auto;max-height:300px'>";
      echo "<table id='tbUsers' class='table' width='98%'>".
      "<tr><th width='100'></th><th></th></tr>";
      echo "<tr ><td>{$main->lang['user']}</td><td>".in_text("user_search","input","")."</td></tr>";
      echo "<tr id='terminator'><td colspan='2'></td></tr>";
      echo "</table>";
      echo "</div>";

      echo "</div>";
      echo "<div style='border-top:1px solid'><label for='objsetup' >{$main->lang['object_setup']}: </label> <span id='objsetup'>{$main->lang['select']}</span></div>";

      close();
      open();
      echo "<div id='btnc'>
      <span class='allset' style='float:left;margin: 0 4px !important;'><input type='checkbox' id='allcheck'/><label for='allcheck'>{$main->lang['all']}</label></span>
      <a class='btn'>{$main->lang['clear']}</a>
      </div>";
      echo "<div style='clear:left;'>";
      foreach ($lang_acc as $key => $value) {
         echo "<span><input type='checkbox' id='{$key}'/><label for='{$key}'>{$value}</label></span><br />";
      }
      echo "</div>";
      close();
      echo "<div id='timerajax_src' onclick='load_user_acc();' style='display:none;'><div>{$main->lang['timer_update']}</div>";
      echo "</div></div>";
      echo "<form id='block_form' style='clear: both;' action='{$adminfile}?module={$_GET['module']}&amp;do=admin_acc_save' method='post'>
      <table align='center' class='form' >".
      "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='image' onclick='commit_acc(true); return false;' src='".TEMPLATE_PATH."{$main->tpl}/images/done.png' alt='{$main->lang['send']}' /></td></tr>\n".    
      "</table></form>";

      $lang=array('user'=>$main->lang['user'],'users'=>$main->lang['users'],
         'group'=>$main->lang['group'],'groups'=>$main->lang['groups']);
   ?>
   <script type="text/javascript">
      //<![CDATA[
      //lang constant
      <?php echo "names=".json_encode($lang)."\n";    ?>
      Array.prototype.each = function(func) {
         var j=this.length;
         if(j>0) for (i in this) {
            func(this[i]);
            j--;if(j<=0) break;// block prototype function
         }
      }
      $('#agroups,#ausers,#asetup').on('mouseover',function(){$(this).css('text-decoration','underline');})
      .on('mouseout',function(){$(this).css('text-decoration','none');});
      function isFunction(obj){
         return Object.prototype.toString.call(obj) === "[object Function]";
      }
      function isset(obj){return obj!=undefined&&obj!="";}
      Array.prototype.eachKey = function(func) {
         for (i in this) {
            if(this.hasOwnProperty(i) && !isFunction(this[i]))  func(i);
         }
      }
      var objAcc=$('#src_acc');
      var objSelectAcc=objAcc.children('div:eq(1)');
      var sUser=[];
      var sCatForum='';
      var accInfo=[];
      var accDeleted=[];
      var modifyAccess=false;
      var tmrUChange=0;
      var divG=$('#listgroups');
      var divU=$('#listusers');
      var divUE=$('#existsuser');

      var dvLine=$('<div/>').attr('id','lined').css({'position':'absolute','border-bottom':'1px dashed silver',
            'height':'1px'}).appendTo(document.body);
      var jobList=[];
      function addJob(href,data,mfuncAfter,type){
         obj={href:href,data:data,funca:mfuncAfter,type:type};
         if (type!=undefined) obj.type=type;
         jobList.push(obj);
      }
      function execJob(){
         if (jobList.length!=0){
            obj=jobList.shift();
            while (jobList.length>0){
               obj2=jobList.shift();
               if(obj2.type!=obj.type){jobList.unshift(obj2);break;}
               else obj=obj2;
            }
            $.post(obj.href,obj.data,function(data){
                  setTimeout('execJob()',1000);
                  if (obj.funca!=undefined&&obj.funca!=null) obj.funca(obj,data);
               },'json');
         } else setTimeout('execJob()',1000);
      }
      function commit_acc(button){
         if(modifyAccess){
            var saved=true;
            if(button==undefined) saved=confirm('Editing information save?');
            if(saved) {
               var datas={ajax:true,save:new Array(),deleted:accDeleted};
               accInfo.eachKey(function(i){
                     var trees=accInfo[i];
                     var val={};
                     for (n in trees) {if(trees[n].modify) val[n]=trees[n];}
                     if(!$.isEmptyObject(val)){
                        val.user=i;
                        datas.save.push(val);
                     }
               });
               haja({action:$('#ajax2').attr('href'),animation:true,dataType:'json'},datas,{onendload:function(data){
                        accDeleted=[];load_acc(data);
               }});
            }
         }
      }
      function show_exists(){
         $('#src_tree').find('li').each(function(){
               var exists=sUser.length>0;
               var id=this.id;
               sUser.each(function(i){
                     var acc=accInfo[i];
                     if(acc==undefined||acc[id]==undefined) exists=false;
               });
               if(exists) $(this).addClass('exists');
               else $(this).removeClass('exists'); 
         })
      }
      function showChecks(){
         if(sUser!=''&&sCatForum!='') {center_checks(); objSelectAcc.show('normal');return true}
         else return false;
      }
      function show_acc(){
         var nCheck=nNotCheck=0;
         cnCheck=objSelectAcc.find(':checkbox').not('#allcheck').length;
         if(sUser.length>0&&sCatForum!=''){ 
            if(!objSelectAcc.is(':visible')) showChecks();
            var gexists=false;
            var gNot=false;
            sUser.each(function(i){if(accInfo[i]==undefined||accInfo[i][sCatForum]==undefined) gNot=true;});
            var cu=sUser.length;
            objSelectAcc.find(':checkbox').not('#allcheck').each(function(){
                  var cOk=cNot=0;
                  var id=this.id;
                  if(!gNot){
                     sUser.each(function(i){
                           acc=accInfo[i][sCatForum];
                           if(acc[id]!=undefined){
                              if(acc[id]) cOk++; else cNot++;
                           }
                     });
                     gexists=((cOk==cu)||(cNot==cu));
                  } else gexists=false;
                  if(gexists){
                     if(cOk==cu) nCheck++; else nNotCheck++;
                  }
                  this.checked=(cOk==cu)?true:false;
                  if(!gexists) $(this).parent().addClass('silver');
                  else $(this).parent().removeClass('silver');
            })
            if(gexists) $('#btnc').find('.btn').css('visibility','visible');
            else $('#btnc').find('.btn').css('visibility','hidden');
         } else {
            if(objSelectAcc.is(':visible')) objSelectAcc.hide();
         }
         if(nCheck==cnCheck||nNotCheck==cnCheck) $('#allcheck').parent().removeClass('allset').end().get(0).checked=(nCheck==cnCheck);
         else $('#allcheck').parent().addClass('allset').end().get(0).checked=false;
      }
      var alredyRunning=false;
      function center_checks(new_height,call_back){
         if(alredyRunning) return true;
         alredyRunning=true;
         var main_call_back=function(){
            alredyRunning=false;
            if(isset(call_back)) call_back();
         }
         obj=$('#trall').find('span.select');
         var pt=obj.bounds(true);
         var ps=$('#src_tree').bounds(true);
         var pa=objAcc.bounds(true);
         var height=0;
         if(typeof new_height =='function'){call_back=new_height;new_height=undefined;}
         if(new_height==undefined){
            if(sUser!=''&&sCatForum!=''){
               height=objAcc.bounds(true).height;
               if(!objSelectAcc.is(':visible')) height+=objSelectAcc.bounds(true).height;
            } else {
               var height=pa.height;
            }
         } else height=new_height;
         var top=(pt.top-ps.top)-(height-pt.height)/2;  
         var bottom=ps.height;
         if((top+height)>bottom) top=(bottom-height);
         if(top<0) top=0;
         dvLine.css({top:(pt.top+(pt.bottom-pt.top)/2)+'px',left:(pt.right+2)+'px',width:(pa.left-pt.right-4)+'px'});
         var ptop=pa.top-objAcc.parent().bounds().top;
         if(Math.abs(ptop-top)>20) $('#src_acc').animate({'top':top+'px'},'slow',main_call_back);
         else {$('#src_acc').css({'top':top+'px'});main_call_back();}
      }
      function load_acc(data){
         ok=(data['status']!=undefined&&data['status']=='ok');
         msg=ok?data['message']:"";
         if(ok) {delete(data['status']);delete(data['message']);}
         for(i in data) {
            if(accInfo[i]) delete(accInfo[i]);
            accInfo[i]=data[i];
         }
         if(msg!="") alert(msg);
         modifyAccess=false;
      }
      function setChecked(obj){
         $(obj).parent().removeClass('silver');
         sUser.each(function(i){
               if(accInfo[i]==undefined) accInfo[i]={};
               if(accInfo[i][sCatForum]==undefined) accInfo[i][sCatForum]={};
               accInfo[i][sCatForum][obj.id]=obj.checked;
               accInfo[i][sCatForum].modify=true;
         });
         show_exists();
         modifyAccess=true;
      }
      function load_user_acc(){
         if(tmrUpdate!=0) {clearTimeout(tmrUpdate);tmrUpdate=0;}
         var gu=[];
         sUser.each(function(i){if(accInfo[i]==undefined) gu.push(i);});
         $('#timerajax_src').hide();
         if(gu.length>0){
            haja({action:$('#ajax1').attr('href'),animation:true,dataType:'json'},{info:gu},{onendload:function(data){
                     load_acc(data);
                     show_acc();
                     show_exists();  
            }});
         } else {
            show_acc();
            show_exists();  
         }
      }
      var ctu=0;
      var tmrUpdate=0;
      var tmrClickUpdate=0;
      function update_time_load(){
         ctu--;
         $('#timerajax_src').find('b').text(ctu);
         if(ctu>0) tmrUpdate=setTimeout(update_time_load,1000);
         else {
            load_user_acc();
         }
      }
      function show_timer_load(tm){
         var bRun=false;
         sUser.each(function(i){
               if(!isset(accInfo[i])) bRun=true;
         });
         if(bRun&&sUser.length>0){
            ctu=tm;
            $('#timerajax_src').show();
            if(tmrUpdate!=0) clearTimeout(tmrUpdate);
            tmrUpdate=setTimeout(update_time_load,1000);
            $('#timerajax_src').find('b').text(ctu);
         }
      }
      function show_select_object(){
         obj=$('#dvas').find('div.type_sel');
         switch (obj.attr('id')){
            case "asetup":
               var oneSel=names['user'];var multiSel=names['users'];var div=divUE;
               break;
            case "agroups":
               var oneSel=names['group'];var multiSel=names['groups'];var div=divG;
               break;
            case "ausers":
               var oneSel=names['user'];var multiSel=names['users'];var div=divU;
               break;
         }
         var us=[];
         sUser=[];
         div.find('tr.select').each(function(i){
               $tr=$(this);
               us.push($tr.find('td:eq(1)').text());
               if(isset($tr.attr('id'))) sUser.push($tr.attr('id'));
               else sUser.push($tr.attr('name'));
         });
         var text=us.length>0?(us.length>1?multiSel+' - '+us.join(', '):oneSel+' '+us.join(', ')):"";
         $('#objsetup').text(text);
         show_exists(); show_acc();
         if(tmrClickUpdate!=0) clearTimeout(tmrClickUpdate);
         tmrClickUpdate=setTimeout(function(){show_timer_load(4)},500);
      }
      var tmrOOS=0;
      var lastClick=null;
      function out_object_select(){
         tmrOOS=0;
         $('#srcList').find('tr.active').removeClass('active');
      }
      $('#srcList').find('table').bind('mousemove',function(e){
            e=$.event.fix(e);
            if(tmrOOS!=0) {clearTimeout(tmrOOS);tmrOOS=0;}
            var tr=$(e.target).parents('tr:first');
            $(this).find('tr.active').not(tr).removeClass('active');
            if(!tr.hasClass('active')&&tr.hasClass('usel')) tr.addClass('active');
      }).bind('mouseout',function(e){
            if(tmrOOS==0) tmrOOS=setTimeout(out_object_select,200);
      }).bind('click',function(e){
            e=$.event.fix(e);
            var tr=$(e.target).parents('tr:first');
            if(tr.hasClass('usel')){
               if(e.ctrlKey){
                  $('#scroller').focus();
                  tr.toggleClass('select');
               } else if(e.shiftKey){
                  if(lastClick!=null){
                     var tbl=tr.parents('table:first').find('tr');
                     var n=tbl.index(tr);
                     var k=tbl.index(lastClick);
                     var m=Math.min(n,k);
                     var l=Math.max(n,k);
                     for(i=m;i<=l;i++) tbl.eq(i).addClass('select');
                  }
               } else {
                  $(this).find('tr.select').not(tr).removeClass('select');
                  tr.addClass('select');
               }
               lastClick=tr;
               show_select_object();
            }
      }).bind('dblclick',function(e){
            if(tmrClickUpdate!=0) clearTimeout(tmrClickUpdate);
            ctu=0;update_time_load();
      }).disableTextSelect();//.find('tr:has(td):odd').addClass('row2');
      $(document).ready(function(){
            objSelectAcc.hide();
            objSelectAcc.find('.btn').bind('click',function(){
                  var acc=accInfo[sUser][sCatForum];
                  if(acc.id) accDeleted.push(acc.id);
                  delete(accInfo[sUser][sCatForum]);
                  modifyAccess=true;
                  show_acc();
                  show_exists();
            })
            objAcc.show().css({width:'50%','float':'right',position:'relative'});
            $('#src_tree').css({width:'50%','float':'left',overflow:'auto','max-height':($('#leftcol').outerHeight(400)+'px')});
            $('#trall').treeviewli({collapsed: false,selected:true,multiselect:false,togglediv:true,
                  dragdrop:false,change:function(obj){
                     sCatForum=obj.id;
                     show_acc();
                     if(!showChecks()) center_checks();
            }});
            var grayed=false;
            objSelectAcc.find(':checkbox').bind('change',function(e){
                  if(this.id!='allcheck') setChecked(this);
                  else {
                     var checked=this.checked;
                     $(this).parent().removeClass('allset');
                     objSelectAcc.find(':checkbox').not(this).each(function(i){this.checked=checked;setChecked(this);})
                  }
            });
            var prev_user='';
            $('#user_search').bind('keyup',function(e){
                  var uname=$('#user_search').parent().prev().text();
                  var vSearch=$('#user_search').val();
                  if(isset(vSearch)){
                     if(vSearch!=prev_user){
                        prev_user=vSearch;
                        addJob($('#ajax3').attr('href'),{ajax:true,user:vSearch},function(obj,data){
                              var $r=$('#terminator');
                              $r.nextAll('tr').remove();
                              for(i in data) {
                                 if(i.substr(0,1)=='u'){
                                    $nr=$("<tr/>").attr('name',i).addClass('usel').append($('<td/>').text(uname)).append($('<td/>').text(data[i]));
                                    $r.after($nr);
                                    $r=$nr;
                                 }
                              }
                           },'search');
                     }
                  } else $('#terminator').nextAll('tr').remove();
            });
            $('#dvas').find('div').bind('click',function(e){
                  $('#dvas').find('div').not(this).removeClass('type_sel');
                  $(this).addClass('type_sel')
                  switch (this.id){
                     case "asetup":
                     divG.hide();divU.hide();
                     if(divUE.css('display')=='none') {
                        center_checks(objAcc.bounds().height+divUE.bounds().height,function(){divUE.show();}); 
                     } else divUE.hide();
                     break;
                     case "agroups":
                     divUE.hide();divU.hide();
                     if(divG.css('display')=='none') {
                        center_checks(objAcc.bounds().height+divG.bounds().height,function(){divG.show()}); 
                     } else divG.hide();
                     break;
                     case "ausers":
                     divUE.hide();divG.hide();
                     if(divU.css('display')=='none') {
                        center_checks(objAcc.bounds().height+divU.bounds().height,function(){divU.show();$('#user_search').focus();}); 
                     } else divU.hide();
                     break;
                  }
            })
            execJob();
      });
      //]]>
   </script>
   <?php
   }
   function admin_edit_cat(){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      $result = $main->db->sql_query("SELECT * FROM ".CAT_FORUM." ORDER BY cat_sort");
      $pos_case = array();
      $cat = array(); $sel_cat_post = 0; $_cat_id = 0;
      while(($row = $main->db->sql_fetchrow($result))){
         if($row['cat_id']!=$_GET['id']){$pos_case[] = $row; $_cat_id = $row['cat_id'];} 
         else {$cat = $row; $sel_cat_post = $_cat_id;}
      }
      $sel_arr = array(0 => $main->lang['first_cat']);
      foreach($pos_case as $val){
         $sel_arr[$val['cat_id']] = "{$main->lang['following_category']}: \"{$val['cat_title']}\"";
      }
      echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=admin_save_edit_cat&amp;id={$_GET['id']}'>\n".
      in_hide('sel_cat_post', $sel_cat_post).
      in_hide('reference',htmlspecialchars($main->ref)).
      "<table width='100%' align='center' class='form' id='form_{$main->module}'>\n".
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['title_cat']}:</td><td class='form_input2'>".in_text('title', 'input_text2', $cat['cat_title'])."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['descript']}:</td><td class='form_input2'>".in_text('description', 'input_text2', $cat['description'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'>{$main->lang['invisible']}:</td><td class='form_input2'>".in_chck('invisible', 'checkbox', $cat['invisible']=='y'?ENABLED:'')."</td></tr>\n".    
      "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
      "</table></form>";
   }

   function admin_save_edit_cat(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      sql_update(array(
            'cat_title' => $_POST['title'],
            'description' => $_POST['description'],
            'invisible' =>(isset($_POST['invisible'])&&$_POST['invisible']=='on')?'y':'n'
         ), CAT_FORUM, "cat_id='{$_GET['id']}'");
      admin_redirect_parent(isset($_POST['reference'])?$_POST['reference']:"");
   }

   function admin_delete_cat_forum(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_class('treedb');
      $main->db->sql_query("SELECT tree FROM ".CAT_FORUM." WHERE cat_id='{$_GET['id']}'");
      if($main->db->sql_numrows()!=0){
         list($tree) = $main->db->sql_fetchrow();
         //Выбираем все форумы категории
         $forums = $main->db->sql_query("SELECT forum_id FROM ".FORUMS." WHERE cat_id='{$_GET['id']}'");
         //Перебираем форумы
         while(list($forum_id) = $main->db->sql_fetchrow($forums)){
            //Выбираем все темы заданного форума
            $topics = $main->db->sql_query("SELECT topic_id FROM ".TOPICS." WHERE forum_id='{$forum_id}'");
            //Перебираем темы
            while(list($topic_id) = $main->db->sql_fetchrow($topics)){
               //Делаем выборку всех сообщений заданной темы
               $posts = $main->db->sql_query("SELECT post_id, poster_id FROM ".POSTS." WHERE topic_id='{$topic_id}'");
               //Перебираем сообщения
               while(list($post_id, $poster_id) = $main->db->sql_fetchrow($posts)){
                  //Отнимаем у пользователя 1 пост
                  update_posts($poster_id, "-");
                  //Удаляем прикрепленные
                  if(file_exists($forum['directory'].$post_id)){
                     $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$forum['directory']}{$post_id}/%'");
                     remove_dir($forum['directory'].$post_id);
                  }
               }
               //Удаляем топик 
               //$main->db->sql_query("DELETE FROM ".TOPICS." WHERE topic_id='{$topic_id}'");
               //Удаляем все сообщения топика
               //main->db->sql_query("DELETE FROM ".POSTS." WHERE topic_id='{$topic_id}'");
            }    
            //Удаляем форум  
            //$main->db->sql_query("DELETE FROM ".FORUMS." WHERE forum_id='{$forum_id}'");
         }
         //Удаляем категорию
         $main->db->sql_query("DELETE  FROM  ".POSTS."
            where forum_id in (select f.forum_id from ".CAT_FORUM." AS c,".FORUMS." AS f where c.tree like '{$tree}%' and f.cat_id=c.cat_id)");
         $main->db->sql_query("DELETE  FROM  ".TOPICS."
            where forum_id in (select f.forum_id from ".CAT_FORUM." AS c,".FORUMS." AS f where c.tree like '{$tree}%' and f.cat_id=c.cat_id)");
         $main->db->sql_query("DELETE FROM ".FORUMS."
            where cat_id in (select c.cat_id from ".CAT_FORUM." AS c where c.tree like '{$tree}%')");
         $main->db->sql_query("DELETE FROM ".FORUM_ACC." WHERE typeacc = 'c' and idv='{$_GET['id']}'");
         $dbtree=new treedb(CAT_FORUM,2);
         $dbtree->delete($tree);
         //$main->db->sql_query("DELETE FROM ".CAT_FORUM." WHERE cat_id='{$_GET['id']}'");
         //Обновляем позиции категорий
         $pos = 10;     
         $cats = $main->db->sql_query("SELECT cat_id FROM ".CAT_FORUM." ORDER BY cat_sort");
         while(list($cat_id) = $main->db->sql_fetchrow($cats)){
            $main->db->sql_query("UPDATE ".CAT_FORUM." SET cat_sort='{$pos}' WHERE cat_id='{$cat_id}'");
            $pos=$pos+10;
         }
         admin_redirect_parent();
      } else info($main->lang['noinfo']);
   }
   function admin_sort_control(){
      global $main,$tpl_create,$adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::add2link("includes/css/treeviewli.css");
      main::add2script("includes/javascript/jquery/treeviewli.js");
      main::add_css2head("
         .tree_cat{background: transparent url('includes/images/16x16/category.png') no-repeat 3px 1px;}
         .tree_forum{background: transparent url('includes/images/16x16/forum_16.png') no-repeat 3px 1px;}
         .treeviewli span {padding: 3px 10px 3px 23px !important;border: 1px solid transparent;}
         .treeviewli li { padding: 1px 0pt 2px 16px !important;border-top:2px dotted transparent;border-bottom:2px dotted transparent;}
         .treeviewli .node{ margin-top: -1px;}
      ");
      main::init_class('li_tree');
      $dbr=$main->db->sql_query("SELECT * FROM ".CAT_FORUM." order by tree"); //substr(tree,1,length(tree)-2),cat_title,tree
      $litr=new li_tree('trall','tree_c treeviewli');
      $litr->class_tree='tree_cat';
      $litr->prefix_tree='mc';
      $litr->show_id=true;
      $litr->load_db($dbr,'cat_title','cat_id','tree');
      $litr->class_tree='tree_forum';
      $litr->prefix_tree='mf';
      $dbr=$main->db->sql_query("SELECT f.forum_id, f.forum_name, concat(c.tree,'00') as tree
         FROM ".FORUMS." AS f LEFT JOIN ".CAT_FORUM." AS c ON(f.cat_id=c.cat_id)
      ORDER BY c.tree, f.pos, f.forum_name");
      $litr->load_db($dbr,'forum_name','forum_id','tree');
      echo "<form method='post' id='treeform' action='{$adminfile}?module={$main->module}&amp;do=save_sort'>";
      $litr->echo_html();
      echo "<table align='center' class='form' >".
      "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='image' onclick='sendAjax(); return false;' src='".TEMPLATE_PATH."{$main->tpl}/images/done.png' alt='{$main->lang['send']}' /></td></tr>\n".    
      "</table>";
      echo "</form>";
      $count_timeout=5;
      $text_wait=str_replace("{COUNT}",$count_timeout,$main->lang['wait_timer']);
   ?>
   <script type="text/javascript">
      //<![CDATA[
      <?php echo "waitsend={$count_timeout};\n textwait='{$text_wait}';\n";   ?>
      var noHook=false;
      function setHookClose(){
         if(!noHook){
            noHook=true;
            window.onbeforeunload = function(evt) {
               evt = evt || window.event;
               evt.returnValue = textwait;
            }
         }
      }
      var dataOut=[];
      var tmr=0;
      function sendAjax(){
         var mdata={};
         mdata.info=dataOut;
         dataOut=[]; window.onbeforeunload=null;noHook=false;
         mdata.ajax=true;
         haja({action:$('#treeform').attr('action'),animation:true,dataType:'json'},mdata,{onendload:function(data){
                  if(data.status=='ok') alert(data.okMessage);
                  else alert(data.errorMessage);
         }});
      }
      $('#trall').treeviewli({collapsed: false,selected:true,multiselect:false,togglediv:true,
            dragdrop:true,dragdest:'tree_cat',event:function(name,data){
               if(name=='drag start'){
                  var catf=$(data.src).hasClass('tree_cat');
                  if(catf){
                     $('#trall').find('.tree_forum').setTDMode();
                  } else {
                     $('#trall').find('.tree_forum').setTDMode();
                     $(data.src).parents('ul:first').children().children('.tree_forum').setTDMode({after:true,before:true,child:false});
                     $('#trall').find('.tree_cat').setTDMode({after:false,before:false,child:true});
                  }
               }
               if(name=='drag stop'){
                  if(tmr!=0) clearTimeout(tmr);
                  dataOut.push({mode:data.dragmode,src:$(data.src).parent().attr('id'),dest:$(data.dest).parent().attr('id')});
                  //setHookClose();
               }
      }});
      //]]>
   </script>
   <?php
   }
   function admin_fix_admin_sort_control($cat_id){
      global $main;
      $result = $main->db->sql_query("SELECT * FROM ".FORUMS." where cat_id={$cat_id} ORDER BY pos");
      $i = 10;
      while(($row = $main->db->sql_fetchrow($result))){
         $main->db->sql_query("UPDATE ".FORUMS." SET pos='{$i}' WHERE forum_id='{$row['forum_id']}'");
         $i+=10;
      }
   }
   /**
   * put your comment there...
   * 
   * @param int $source ID того кого перемещают
   * @param int $dest ID того куда перемещают
   * @param string $mode ('a' - after, 'b' - before)
   */
   function admin_change_forum_sort($source,$dest,$mode){
      global $main;
      $cat_id=array();
      $main->db->sql_query("select `forum_id`,`pos`,`cat_id` from ".FORUMS." where forum_id in ({$source},{$dest})");
      while (($row=$main->db->sql_fetchrow())){
         if($row['forum_id']==$source) $pos_s=$row['pos'];
         else $pos_d=$row['pos'];
         $cat_id[]=$row['cat_id'];
      }
      if($pos_d==$pos_s&&$cat_id[0]==$cat_id[1]){
         admin_fix_admin_sort_control($cat_id[0]);
         $main->db->sql_query("select `forum_id`,`pos`,`cat_id` from ".FORUMS." where forum_id in ({$source},{$dest})");
         while (($row=$main->db->sql_fetchrow())){
            if($row['forum_id']==$source) $pos_s=$row['pos'];
            else $pos_d=$row['pos'];
            $cat_id[]=$row['cat_id'];
         }
      }
      if($cat_id[0]==$cat_id[1]){
         if($mode=='b'){
            if($pos_s<$pos_d){
               $main->db->sql_query("update ".FORUMS." set `pos`=`pos`-10 where cat_id={$cat_id[0]} and pos>{$pos_s} and pos<{$pos_d}");
               $main->db->sql_query("update ".FORUMS." set `pos`={$pos_d}-10 where forum_id={$source}");
            }  else {
               $main->db->sql_query("update ".FORUMS." set `pos`=`pos`+10 where cat_id={$cat_id[0]} and pos>={$pos_d} and pos<{$pos_s}");
               $main->db->sql_query("update ".FORUMS." set `pos`={$pos_d} where forum_id={$source}");
            }
         } elseif($mode=='a'){
            if($pos_s<$pos_d){
               $main->db->sql_query("update ".FORUMS." set `pos`=`pos`-10 where cat_id={$cat_id[0]} and pos>{$pos_s} and pos<={$pos_d}");
               $main->db->sql_query("update ".FORUMS." set `pos`={$pos_d} where forum_id={$source}");
            }  else {
               $main->db->sql_query("update ".FORUMS." set `pos`=`pos`+10 where cat_id={$cat_id[0]} and pos>{$pos_d} and pos<{$pos_s}");
               $main->db->sql_query("update ".FORUMS." set `pos`={$pos_d}+10 where forum_id={$source}");
            }
         }
      }
   }
   function admin_save_admin_sort_control(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_class('treedb');
      $info=$_POST['info'];
      $tree= new treedb(CAT_FORUM,2);
      foreach ($info as $key => $value) {
         $ms=substr($value['src'],0,2);
         $md=substr($value['dest'],0,2);
         $ids=substr($value['src'],2);
         $idd=substr($value['dest'],2);
         if($ms=='mc'&&$md=='mc'){
            $main->db->sql_query("select cat_id,tree from ".CAT_FORUM." where cat_id in ({$idd},{$ids})");
            while (($row=$main->db->sql_fetchrow())){
               if($row['cat_id']==$ids) $tree_s=$row['tree'];
               else $tree_d=$row['tree'];
            }
         }
         switch ($value['mode']){
            case 'c':
               if($md=='mc'){
                  if($ms=='mf') {
                     $main->db->sql_query("select `pos`,`cat_id` from ".FORUMS." where forum_id={$ids}");
                     list($opos,$ocat_id)=$main->db->sql_fetchrow();
                     sql_update(array('cat_id'=>$idd,'pos'=>0),FORUMS," forum_id={$ids}");
                     $main->db->sql_query("UPDATE ".FORUMS." left join (select n.cat_id,max(n.pos) as npos from ".FORUMS." AS n group by cat_id) as nf USING (cat_id)
                        SET `pos`=(npos+10) WHERE forum_id={$ids}");
                     $main->db->sql_query("UPDATE ".FORUMS." SET `pos`=`pos`-10 WHERE `cat_id`={$ocat_id} and `pos`>{$opos}");
                  }  else $tree->move($tree_s,$tree_d);
               }
               break;
            case 'a':if($ms=='mc'&&$md=='mc') $tree->move($tree_s,$tree_d,'a');
               elseif($ms=='mf'&&$md=='mf') admin_change_forum_sort($ids,$idd,$value['mode']);
               break;
            case 'b':if($ms=='mc'&&$md=='mc') $tree->move($tree_s,$tree_d,'b'); 
               elseif($ms=='mf'&&$md=='mf') admin_change_forum_sort($ids,$idd,$value['mode']);
               break;
         }
         $json=array('status'=>'ok','okMessage'=>$main->lang['save_continue']);
         echo json_encode($json);
      }
      if(is_ajax()) kr_exit();
   }

   function admin_get_acc_script($where='',$is_saved=false){
      global $main,$listacc;
      if(hook_check(__FUNCTION__)) return hook();
      $ret=array();
      $cwhere="";
      if($where==""){
         if(isset($_POST['info'])){
            $info=$_POST['info'];
            foreach ($info as $value) {
               $mode=$value{0};
               $ugid=intval(substr($value,1));
               $cwhere.=" or (thisuser='{$mode}' and ugid={$ugid}) ";
            }
            $cwhere="(".substr($cwhere,4).")";
         }
      } else $cwhere=$where;
      if($cwhere!=""){
         $main->db->sql_query("select * from ".FORUM_ACC." where {$cwhere} order by thisuser,ugid");
         $tugid='';
         if($main->db->sql_numrows()){
            while (($row=$main->db->sql_fetchrow())){
               $cus=$row['thisuser'].$row['ugid'];
               if($tugid!=$cus){
                  $ret[$cus]=array();$tugid=$cus;
               }
               $key="m{$row['typeacc']}{$row['idv']}";
               $ret[$cus][$key]=array('id'=>$row['id']);
               foreach ($listacc as $value) {
                  $ret[$cus][$key][$value]=$row[$value]==1?true:false;
               }
            }
         }
      }
      if($is_saved){
         $ret['status']='ok';
         $ret['message']=$main->lang['save_continue'];
      }
      echo json_encode($ret);
      if(is_ajax()) kr_exit();
   }
   function admin_acc_save(){
      global $main,$listacc, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $sinfo=isset($_POST['save'])?$_POST['save']:array();
      if(isset($_POST['deleted'])){
         $deleted=$_POST['deleted'];
         if(count($deleted)>0) $main->db->sql_query("DELETE FROM ".FORUM_ACC." where id in (".implode(',',$deleted).")");
      }
      $where='';
      foreach ($sinfo as $value) {
         $user=$value['user'];
         $thisuser=$user{0};
         $ugid=intval(substr($user,1));
         $change=false;
         foreach ($value as $key =>$arr) {
            if($key=='user') continue;
            $typeacc=substr($key,0,2)=='mc'?'c':'f';
            $idv=intval(substr($key,2));
            $id=isset($arr['id'])?intval($arr['id']):0;
            if($id==0){
               $insert=array('ugid'=>$ugid,'thisuser'=>$thisuser,'typeacc'=>$typeacc,
                  'idv'=>$idv);
               foreach ($listacc as $val) {
                  $insert[$val]=isset($arr[$val])?(strtolower($arr[$val])=='true'?1:0):0;
               }
               sql_insert($insert,FORUM_ACC);
               $change=true;
            } else {
               if(isset($arr['modify'])&&$arr['modify']=='true'){
                  $update=array('ugid'=>$ugid,'thisuser'=>$thisuser,'typeacc'=>$typeacc,
                     'idv'=>$idv);
                  foreach ($listacc as $val) {
                     $update[$val]=isset($arr[$val])?(strtolower($arr[$val])=='true'?1:0):0;
                  }
                  sql_update($update,FORUM_ACC," id={$id}");
                  $change=true;
               }
            }    
         }
         if($change) $where.="or (thisuser='{$thisuser}' and ugid={$ugid}) ";
      }
      admin_forum_change();
      if(count($where)>0) {$where=substr($where,3);admin_get_acc_script($where,true);}
      if(is_ajax()) kr_exit();
   }

   function admin_list_user(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $user_find=addslashes($_POST['user']);
      $main->db->sql_query(" select * from ".USERS." where upper(user_name) like upper('%{$user_find}%')  limit 40");
      $users=array();
      while ($row=$main->db->sql_fetchrow()){$users["u{$row['uid']}"]=$row['user_name'];}
      echo json_encode($users);
      if(is_ajax()) kr_exit();
   }
   function admin_forum_change(){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $forum['change_acc']=isset($forum['change_acc'])?$forum['change_acc']+1:1;
      main::init_function('sources');
      save_config('config_forum.php', '$forum', $forum);
   }
   /**
   * Анализ доступа пользователя к форуму
   * 
   */
   function admin_access_detail(){
      global $main, $adminfile, $lang_acc;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_language('search');
      main::add2link("includes/css/treeviewli.css");
      main::add2script("includes/javascript/ajax_job.js");
      main::add2script("includes/javascript/jquery/treeviewli.js");
      main::add2script("includes/javascript/jquery/jquery.disable.text.select.js");
      main::add_css2head("
         .tree_cat{background: transparent url('includes/images/16x16/category.png') no-repeat 3px 1px;}
         .tree_forum{background: transparent url('includes/images/16x16/forum_16.png') no-repeat 3px 1px;}
         .treeviewli span {padding: 3px 10px 3px 23px !important;}
         #src_text label{margin:4px;}
      ");
      open();
      echo "<div style='text-align: center;'>";
      echo "{$main->lang['findauthor']} : ".in_text("user_search",'input_text','')."<br/>";
      echo "</div>";
      echo "<div id='lsuser' style='text-align: center; margin-top:4px;'>";
      echo "{$main->lang['inputusername']} : ".in_sels("user_found",array(),'select',''," onchange='change_user_select(this);' ")."";
      echo "</div>";
      echo "<div style='height:6px; border-bottom: 1px solid silver;'></div>";
      close();
      main::init_class('li_tree');
      $dbr=$main->db->sql_query("SELECT * FROM ".CAT_FORUM." order by tree");
      $litr=new li_tree('trall','tree_c treeviewli');
      $litr->class_tree='tree_cat';
      $litr->prefix_tree='mc';
      $litr->show_id=true;
      $litr->load_db($dbr,'cat_title','cat_id','tree');
      $litr->class_tree='tree_forum';
      $litr->prefix_tree='mf';
      $dbr=$main->db->sql_query("SELECT f.forum_id, f.forum_name, concat(c.tree,'00') as tree
         FROM ".FORUMS." AS f LEFT JOIN ".CAT_FORUM." AS c ON(f.cat_id=c.cat_id)
      ORDER BY c.tree, f.forum_name");
      $litr->load_db($dbr,'forum_name','forum_id','tree');
      echo "<div id='src_tree' style='margin-top:4px;'>";
      $litr->echo_html();
      echo "</div>";
      echo "<div id='src_text' style='margin-top:4px; width: 40%; float:right;'>";
      echo "<span></span><br>";
      echo "<div id='access_view' style='display:none; margin-top:10px;'>";
      echo "<div style='width:30%; height:50%;position: absolute;'></div>";
      foreach ($lang_acc as $key => $value) {
         echo "<span><input type='checkbox' id='{$key}'/><label for='{$key}'>{$value}</label></span><br />";
      }
      echo "</div>";
      echo "</div>";
      echo "<div style='display:none'>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=admin2' id='ajax1'></a>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=admin_acc_detail' id='ajax2'></a>";
      echo "<a href='{$adminfile}?module={$main->module}&amp;do=admin_list_user' id='ajax3'></a>";
      echo "</div>";
      $jslang=array('noinfo'=>$main->lang['noinfo'],'forum_user_select'=>$main->lang['forum_user_select']);
   ?>
   <script type="text/javascript">
      //<![CDATA[
      <?php echo "jslang=".json_encode($jslang).";\n";   ?>
      var prev_user='';
      var du=$('#lsuser');
      var idf='user_found';
      var idfj='#'+idf;
      var found_visible=false;
      du.css({opacity: 0.0});
      var load_user='';
      var user_data, span_text,user_please_select,hp,wp;
      function isset(obj){return obj!=undefined&&obj!="";}
      function change_user_select(obj){
         var usersel = $(idfj).val();
         $.post($('#ajax2').attr('href'),{ajax:true,user: usersel},function(data){
               load_user = usersel;
               user_data=data;
               change_tree();
            },'json')
      }
      if (!Array.prototype.indexOf){
         Array.prototype.indexOf = function(elt /*, from*/){
            var len = this.length;
            var from = Number(arguments[1]) || 0;
            from = (from < 0)? Math.ceil(from) : Math.floor(from);
            if (from < 0) from += len;
            for (; from < len; from++){
               if (from in this &&
                  this[from] === elt)
                  return from;
            }
            return -1;
         };
      }
      var sel_tree;
      function change_tree(obj){
         if(obj!==undefined) sel_tree=obj;
         else obj=sel_tree;
         if(load_user==''){
            $('#access_view').hide();
            span_text.html(jslang.forum_user_select);
         } else {
            var found=user_data[obj.id]!==undefined;
            var text = found?user_data[obj.id].text:jslang.noinfo;
            span_text.html(text);
            if(found) {
               var acc=user_data[obj.id]['acc'];
               for(i in acc) {$$(i).checked = acc[i]=='1'?true:false;}
               $('#access_view').show();
            } else $('#access_view').hide();
         }
         var ht = $('#src_text').height();
         var ho = $(obj).offset().top;
         var n=(ho - hp)-ht/2+4;
         var nt=(n<0)?0:(n>wp?wp-ht:n);
         $('#src_text').css('margin-top',nt+'px');
      }
      $(document).ready(function(){
            user_please_select = $('#ajax1').text();
            hp = $('#trall').offset().top;
            wp = $('#trall').height();
            span_text=$('#src_text').find('span:first');
            $('#src_tree').css({width:'50%','float':'left',overflow:'auto','max-height':($('#leftcol').outerHeight(400)+'px')});
            $('#trall').treeviewli({collapsed: false,selected:true,multiselect:false,togglediv:true,
                  dragdrop:false,change:function(obj){
                     change_tree(obj);
            }});
            $('#user_search').bind('keyup',function(e){
                  var uname=$('#user_search').parent().prev().text();
                  var vSearch=$('#user_search').val();
                  if(isset(vSearch) && vSearch.length>1){
                     if(vSearch!=prev_user){
                        prev_user=vSearch;
                        load_user='';
                        addJob($('#ajax3').attr('href'),{ajax:true,user:vSearch},function(obj,data){
                              var n=0;
                              empty_chosen_select(idf);
                              $$(idf).options[n] = new Option("", 0);
                              for(i in data) {if(i.substr(0,1)=='u'){n++;$$(idf).options[n] = new Option(data[i], i);}}
                              $(idfj).trigger("liszt:updated");
                              if(!found_visible) du.animate({opacity: 1.0}, 1000);
                              found_visible=true;
                           },'search');
                     }
                  } else {
                     found_visible=false;
                     du.animate({opacity: 0.0}, 500);
                     empty_chosen_select(idf);
                     $(idfj).trigger("liszt:updated");
                  };
            });
      });
      //]]>
   </script>
   <?php
   }
   function admin_forum_encode_debug(&$value, $cats, $forums, $groupl){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if($value[0]=='p'){
         $id = intval(substr($value['pm'],2));
         if(substr($value['pm'],0,2)=='mf') $value['text']="{$main->lang['forum_personal_setup']} :<br/><b>{$forums[$id]}</b>";
         else $value['text']="{$main->lang['forum_personal_setup']} :<br/><b>{$cats[$id]}</b>";
      } else {
         $value['text']="{$main->lang['forum_group_setup']} :<br/>";
         foreach ($value['grp'] as $k => $v) {
            $gn=$groupl[$k];
            $id = intval(substr($v,2));
            if(substr($v,0,2)=='mf') $value['text'].="<b>{$gn} => {$forums[$id]}</b><br/>";
            else $value['text'].="<b>  {$gn} => {$cats[$id]}</b><br/>";
         }
      }

   }
   function admin_acc_detail(){
      global $main, $forum_access, $detail_mode_acc;
      if(hook_check(__FUNCTION__)) return hook();
      $detail_mode_acc = true;
      $user=intval(substr($_POST['user'],1));
      $main->db->sql_query("select user_group, user_groups from ".USERS." where uid={$user}");
      if($main->db->sql_numrows()>0){
         list($ugroup, $ugroups)= $main->db->sql_fetchrow();
         $r=forum_full_access_load_user($user, $ugroup, $ugroups);
         $forum_access = forum_encode_session_access($r);
         $retac=array();
         $main->db->sql_query("select tree, cat_id, cat_title FROM ".CAT_FORUM." order by tree"); 
         $cats=array(); $treecs=array();
         while (($row=$main->db->sql_fetchrow())) {
            $cats[$row['cat_id']] = $row['cat_title'];
            $treec[$row['cat_id']] = $row['tree'];
         }

         $main->db->sql_query("select id, title FROM ".GROUPS); $groupl=array();
         while (($row=$main->db->sql_fetchrow())) $groupl[$row['id']] = $row['title'];

         $main->db->sql_query("select fc.tree, ff.forum_id, ff.forum_name FROM ".CAT_FORUM." fc, ".FORUMS." ff where ff.cat_id=fc.cat_id"); 
         $forums=array(); $treef =array();
         while (($row=$main->db->sql_fetchrow())) {
            $forums[$row['forum_id']] = $row['forum_name'];
            $treef[$row['forum_id']] = $row['tree'];
         }
         foreach ($r['debug']['fm'] as $key => $value) {
            if(!empty($value)){
               $id=intval(substr($key,2));
               $value['acc']=forum_open_access_forum($treef[$id], $id);
               admin_forum_encode_debug($value, $cats, $forums, $groupl);
               $retac[$key]=$value;
            }
         }
         foreach ($r['debug']['cat'] as $key => $value) {
            if(!empty($value)){
               $id=intval(substr($key,2));
               $value['acc']=forum_open_access_tree($treec[$id]);
               admin_forum_encode_debug($value, $cats, $forums, $groupl);
               $retac[$key]=$value;
            }
         }
         echo json_encode($retac);
      } else echo json_encode(array());
      exit;
   }

   global $database;
   if(intval($database['revision'])>=779){
      if(isset($_GET['do']) AND $break_load==false){
         switch($_GET['do']){
            case "admin_new_category": admin_new_category(); break;
            case "admin_new_forum": admin_new_forum(); break;
            case "admin_new_sub_category":admin_new_sub_category();break;
            case "admin_edit_forum": admin_edit_forum(); break;
            case "admin_save_edit_forum": admin_save_edit_forum(); break;
            case "admin_delete_forum": admin_delete_forum(); break;
            case "admin_move_forum": admin_move_forum(); break;
            case "config": admin_config_forum(); break;
            case "save_conf": admin_save_forum(); break;
            case "admin_access_rights": admin_access_control(); break;
            case "admin_save_access_rights": admin_save_access_rights(); break;
            case "admin_edit_cat": admin_edit_cat(); break;
            case "admin_save_edit_cat": admin_save_edit_cat(); break;
            case "delete_cat": admin_delete_cat_forum(); break;
            case "admin_main_forum":
            case "admin_new_admin_main_forum":admin_new_admin_main_forum();break;
            case "admin_sort_control":admin_sort_control();break;
            case "save_sort":admin_save_admin_sort_control();break;
            case "acc_info":admin_get_acc_script();break;
            case "admin_acc_save":admin_acc_save();break;
            case "admin_list_user":admin_list_user();break;
            case "access_detail":admin_access_detail(); break;
            case "admin_acc_detail":admin_acc_detail(); break;
            default: admin_new_admin_main_forum(); break;
         }
      } elseif($break_load==false) admin_new_admin_main_forum();
   } else echo warning(str_replace('{REVISION}',779,$main->lang['garant_revision']), true)

?>