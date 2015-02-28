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

$navi = array(
    array('', 'home'),
    array('add', 'add_news'),
    array('config', 'config')
);

if(isset($_GET['module'])) main::required("modules/{$main->module}/globals.php");

function admin_main_news(){
global $main, $adminfile,$count_in_page;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "date";
    $filter=(isset($_GET['filter']) AND !empty($_GET['filter']) AND intval($_GET['filter'])!=0) ? intval($_GET['filter']) : "";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "DESC";
    $result = $main->db->sql_query("SELECT n.id, n.news_id, n.title, n.author, n.date, n.cid, n.status, n.language, u.uid, u.user_id, u.user_name FROM ".NEWS." AS n LEFT JOIN ".USERS." AS u ON(n.author=u.user_name) ".(($filter!="")?"where n.cid like '%,{$filter},%' ":"")." ORDER BY n.{$sort} {$sorttype} LIMIT {$offset}, 30");
    $rows = $main->db->sql_numrows($result);
    if($rows>0){
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("date", $main->lang['asdate']), array("status", $main->lang['asstatus']), array("title", $main->lang['astitle'])),filter_array())."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$main->module}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table cellspacing='1' class='table' width='100%'>\n<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['title']}</th><th width='80'>{$main->lang['author']}</th><th width='50'>{$main->lang['language']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";
        while(list($id, $news_id, $title, $author, $date, $cid, $status, $language, $uid, $user_id, $user_name) = $main->db->sql_fetchrow($result)){
            $aut = (!is_guest_name($author) AND !empty($user_name)) ? "<a class='author' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id, $uid)))."' title='{$main->lang['user_profile']}'>{$author}</a>" : $author;
            $lang = (isset($main->lang[$language])) ? $main->lang[$language] : (empty($language) ? $main->lang['no'] : $language);
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$id}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$id}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}".($status==2?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$id}' /></td><td class='col' align='center'>{$i}</td><td><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($news_id, $id)))."' title='{$title}'>{$title}</a></td><td align='center' class='col'>{$aut}</td><td align='center'>{$lang}</td><td class='col' align='center' id='onoff_{$id}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$id}', 'onoff_{$id}')\">".(($status==0) ? $main->lang['moderation'] : (($status==1) ? $main->lang['on'] : $main->lang['off']))."</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table><table width='100%'><tr><td>".get_function_checked()."</td></tr></table></form>";
        if($rows==30 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".NEWS." "));
            pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
        }
    } else info($main->lang['noinfo']);
}

function admin_edit_news($msg=""){
global $main, $news, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);

    main::add2script("includes/javascript/kr_calendar.js");
    main::add2link("includes/css/kr_calendar.css");
    $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".NEWS." WHERE id={$_GET['id']}"));
    global_add_news("",$result);
}

function admin_del_record_news($value){
global $main, $news;
    if(hook_check(__FUNCTION__)) return hook();
    delete_points($main->points['add_news'], $value, 'news');
    $main->db->sql_query("DELETE FROM ".NEWS." WHERE id='{$value}'");
    $main->db->sql_query("DELETE FROM ".COMMENTS." WHERE modul='{$main->module}' AND parentid='{$value}'");
    $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$news['directory']}{$value}/%'");
    $main->db->sql_query("DELETE FROM ".FAVORITE." WHERE modul='{$main->module}' AND post='{$value}'");
	$main->db->sql_query("DELETE FROM ".TAG." WHERE modul='{$main->module}' AND post='{$value}'");
    $main->db->sql_query("DELETE FROM ".CALENDAR." WHERE module='{$main->module}' AND id='{$value}'");
    if(file_exists($news['directory'].$value)) remove_dir($news['directory'].$value);
}

function admin_config_news(){
global $news, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $news['directory'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $news['attaching_files_type'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_width']}</b>:<br /><i>{$main->lang['miniature_image_width_d']}</i></td><td class='form_input2'>".in_text('miniature_image_width', 'input_text2', $news['miniature_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_height']}</b>:<br /><i>{$main->lang['miniature_image_height_d']}</i></td><td class='form_input2'>".in_text('miniature_image_height', 'input_text2', $news['miniature_image_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $news['max_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $news['max_image_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $news['attaching_files_size'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['file_upload_limit']}</b>:<br /><i>{$main->lang['file_upload_limit_d']}</i></td><td class='form_input2'>".in_text('file_upload_limit', 'input_text2', $news['file_upload_limit'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_in_page']}</b>:<br /><i>{$main->lang['publications_in_page_d']}</i></td><td class='form_input2'>".in_text('publications_in_page', 'input_text2', $news['publications_in_page'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sort_type_publications']}</b>:<br /><i>{$main->lang['sort_type_publications_d']}</i></td><td class='form_input2'>".in_sels('sort_type_publications', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $news['sort_type_publications'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sort_publications']}</b>:<br /><i>{$main->lang['sort_publications_d']}</i></td><td class='form_input2'>".in_sels('sort_publications', array('date'=>$main->lang['asdate'], 'title'=>$main->lang['astitle']), 'select chzn-search-hide', $news['sort_publications'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['comments_sort']}</b>:<br /><i>{$main->lang['comments_sort_d']}</i></td><td class='form_input2'>".in_sels('comments_sort', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $news['comments_sort'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['cat_cols']}</b>:<br /><i>{$main->lang['cat_cols_d']}</i></td><td class='form_input2'>".in_text('cat_cols', 'input_text2', $news['cat_cols'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_title']}</b>:<br /><i>{$main->lang['rss_title_d']}</i></td><td class='form_input2'>".in_text('rss_title', 'input_text2', $news['rss_title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_limit']}</b>:<br /><i>{$main->lang['rss_limit_d']}</i></td><td class='form_input2'>".in_text('rss_limit', 'input_text2', $news['rss_limit'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_rss']}</b>:<br /><i>{$main->lang['conf_rss_d']}</i></td><td class='form_input2'>".in_chck('rss', 'input_checkbox', $news['rss'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['clasic_cat']}</b>:<br /><i>{$main->lang['clasic_cat_d']}</i></td><td class='form_input2'>".in_chck('clasic_cat', 'input_checkbox', $news['clasic_cat'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching']}</b>:<br /><i>{$main->lang['attaching_d']}</i></td><td class='form_input2'>".in_chck('attaching', 'input_checkbox', $news['attaching'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['list_publications']}</b>:<br /><i>{$main->lang['list_publications_d']}</i></td><td class='form_input2'>".in_chck('list_publications', 'input_checkbox', $news['list_publications'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_categories']}</b>:<br /><i>{$main->lang['conf_categories_d']}</i></td><td class='form_input2'>".in_chck('categories', 'input_checkbox', $news['categories'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['categories_ico']}</b>:<br /><i>{$main->lang['categories_ico_d']}</i></td><td class='form_input2'>".in_chck('categories_ico', 'input_checkbox', $news['categories_ico'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['categories_desc']}</b>:<br /><i>{$main->lang['categories_desc_d']}</i></td><td class='form_input2'>".in_chck('categories_desc', 'input_checkbox', $news['categories_desc'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['multiple_cat']}</b>:<br /><i>{$main->lang['multiple_cat_d']}</i></td><td class='form_input2'>".in_chck('multiple_cat', 'input_checkbox', $news['multiple_cat'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_d']}</i></td><td class='form_input2'>".in_chck('ratings', 'input_checkbox', $news['ratings'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_comments']}</b>:<br /><i>{$main->lang['conf_comments_d']}</i></td><td class='form_input2'>".in_chck('comments', 'input_checkbox', $news['comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['guests_comments']}</b>:<br /><i>{$main->lang['guests_comments_d']}</i></td><td class='form_input2'>".in_chck('guests_comments', 'input_checkbox', $news['guests_comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['similar_publications']}</b>:<br /><i>{$main->lang['similar_publications_d']}</i></td><td class='form_input2'>".in_chck('similar_publications', 'input_checkbox', $news['similar_publications'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['moderation_publications']}</b>:<br /><i>{$main->lang['moderation_publications_d']}</i></td><td class='form_input2'>".in_chck('moderation_publications', 'input_checkbox', $news['moderation_publications'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['comments_publishing']}</b>:<br /><i>{$main->lang['comments_publishing_d']}</i></td><td class='form_input2'>".in_chck('comments_publishing', 'input_checkbox', $news['comments_publishing'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_users']}</b>:<br /><i>{$main->lang['publications_users_d']}</i></td><td class='form_input2'>".in_chck('publications_users', 'input_checkbox', $news['publications_users'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_guest']}</b>:<br /><i>{$main->lang['publications_guest_d']}</i></td><td class='form_input2'>".in_chck('publications_guest', 'input_checkbox', $news['publications_guest'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['favorite_status']}</b>:<br /><i>{$main->lang['favorite_status_d']}</i></td><td class='form_input2'>".in_chck('favorite_status', 'input_checkbox', $news['favorite_status'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['favorite_page_status']}</b>:<br /><i>{$main->lang['favorite_page_status_d']}</i></td><td class='form_input2'>".in_chck('favorite_page', 'input_checkbox', $news['favorite_page'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tags_page_status']}</b>:<br /><i>{$main->lang['tags_page_status_d']}</i></td><td class='form_input2'>".in_chck('tags_page_status', 'input_checkbox', $news['tags_page_status'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tags_status']}</b>:<br /><i>{$main->lang['tags_status_d']}</i></td><td class='form_input2'>".in_chck('tags_status', 'input_checkbox', $news['tags_status'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_page_break']}</b>:<br /><i>{$main->lang['conf_page_break_d']}</i></td><td class='form_input2'>".in_chck('page_break', 'input_checkbox', $news['page_break'])."</td></tr>\n".    
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function admin_saves_news(){
global $news, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_news.php', '$news', $news);
    update_rss_config();
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function admin_on_off_news(){
global $main;
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".NEWS." WHERE id='{$_GET['id']}'"));    
    if($info['status']==1){
        $main->db->sql_query("UPDATE ".NEWS." SET status='2' WHERE id='{$_GET['id']}'");
        set_calendar_date($info['id'], $main->module, $info['date'], 0);
        echo $main->lang['off']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".NEWS." SET status='1' WHERE id='{$_GET['id']}'");
        set_calendar_date($info['id'], $main->module, $info['date'], 1);
        echo $main->lang['on']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function admin_change_op_news(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".NEWS." WHERE id='{$value}'"));
                sql_update(array('status' => (($status!=1) ? 1 : 2)), NEWS, "id='{$value}'");
            }
        } else {
            foreach($_POST['sels'] as $value) {
                admin_del_record_news($value);
            }
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else admin_main_news();
}

function admin_dels_news(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    admin_del_record_news($_GET['id']);
    if(is_ajax()) admin_main_news(); else redirect(MODULE);
}

global $database,$check_revision;
if(intval($database['revision'])>=$check_revision){
   if(isset($_GET['do']) AND $break_load==false){
       switch($_GET['do']){
           case "add": global_add_news(); break;
           case "save": global_save_news(); break;
           case "edit": admin_edit_news(); break;        
           case "config": admin_config_news(); break;
           case "save_conf": admin_saves_news(); break;
           case "delete": admin_dels_news(); break;
           case "on_off": admin_on_off_news(); break;
           case "change_op": admin_change_op_news(); break;
           default: admin_main_news(); break;
       }
   } elseif($break_load==false) admin_main_news();
} else echo warning(str_replace('{REVISION}',$check_revision,$main->lang['garant_revision']), true)
?>