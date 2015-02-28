<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load, $tpl_create;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('add', 'add_group'),
    array('points', 'points_conf')
);

main::add2script('includes/javascript/color.js');

function main_groups(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;    
    $result = $main->db->sql_query("SELECT g.*, (SELECT COUNT(uid) FROM ".USERS." AS u WHERE u.user_group=g.id OR u.user_groups LIKE CONCAT('%,',g.id,',%')) AS count FROM ".GROUPS." AS g ORDER BY id");
    $count = $main->db->sql_numrows($result);
    if($count>0){        
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<table cellspacing='1' class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['name']}</th><th width='70'>{$main->lang['points_for_group']}</th><th width='90'>{$main->lang['user_in_group']}</th><th width='90'>{$main->lang['is_special_group']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($rows = $main->db->sql_fetchrow($result))){
            $rows['count'] = ($rows['count']!=0) ? "<a href='{$adminfile}?module=users&amp;do=search&amp;user=&amp;ip=&amp;mail=&amp;group={$rows['id']}&amp;regdate_d=00&amp;regdate_m=00&amp;regdate_y=0000&amp;country=&amp;lastvisit_d=00&amp;lastvisit_m=00&amp;lastvisit_y=0000&amp;type=-1' title='{$main->lang['show_user_in_group']}'>{$rows['count']}</a>" : $rows['count'];
            $op = (!in_array($rows['id'], array(1,2,3,4,5))) ? "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$rows['id']}", 'ajax_content')."</td></tr></table>" : "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}")."</td></tr></table>";
            echo "<tr class='{$row}'><td align='center'>{$i}</td><td".((!empty($rows['color']))?" style='color: #{$rows['color']}'":"").">{$rows['title']}</td><td align='center'>".(!empty($rows['points'])?$rows['points']:"-")."</td><td align='center'>{$rows['count']}</td><td align='center'>".(($rows['special']==1)?"<span style='color: red;'>{$main->lang['yes2']}</span>":"<span style='color: green;'>{$main->lang['no']}</span>")."</td><td align='center'>{$op}</td></tr>";
            $row = ($row=='row1') ? "row2" : "row1";
            $i++;
        }
        echo "</table>";
    } else info($main->lang['noinfo']);
}

function add_groups($msg=""){
global $main, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);    
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".in_area('description', 'textarea', 4)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['points_for_group']}:</td><td class='form_input'>".in_text("points", "input_text2", 0)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'><div id='fish'>{$main->lang['color_group']}:</div></td><td class='form_input'>".in_text("color", "", "#000000")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'>".in_sels('image', array_merge(array('clear'=>$main->lang['no']), scan_dir("includes/images/groups/", '/(.+?)\.(gif|png|jpg|jpeg)$/i', true)), 'select2 chzn-search-hide', '', " onchange=\"$$('preview_cat').src='includes/images/groups/'+this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['preview']}:</td><td class='form_input'><img id='preview_cat' src='includes/images/pixel.gif' alt='' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['is_special_group']}:</td><td class='form_input'>".in_chck('special', 'checkbox')."</td></tr>\n".        
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
    main::add2script("addEvent(window, 'load', function(){Colors.setup({objectId: 'fish',inputTextId: 'color',styleColor  : 'color', outImage: 'includes/images/rgb.gif',overImage: 'includes/images/on_rgb.gif'});});", false);    
}

function save_groups(){
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('name'), array('name_group_error'));
    if(empty($msg)){                       
        $g_arr = array(
            'title'        => $_POST['name'],
            'description'  => $_POST['description'],
            'special'      => (isset($_POST['special']) AND $_POST['special']==ENABLED) ? 1 : 0,
            'img'          => ($_POST['image']!='clear')?$_POST['image']:"",
            'color'        => str_replace('#', '', $_POST['color']),
            'points'       => !empty($_POST['points'])?$_POST['points']:0
        );
        ($_GET['do'] != 'save_edit') ? sql_insert($g_arr, GROUPS) : sql_update($g_arr, GROUPS, "id='{$_GET['id']}'");
        redirect(MODULE);
    } else ($_GET['do'] != 'save_edit') ? add_groups($msg) : edit_groups($msg);
}

function edit_groups($msg=""){
global $main, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);    
    $group = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".GROUPS." WHERE id='{$_GET['id']}'"));
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2", $group['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".in_area('description', 'textarea', 4, $group['description'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['points_for_group']}:</td><td class='form_input'>".in_text("points", "input_text2", $group['points'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'><div id='fish' style='color: #{$group['color']};'>{$main->lang['color_group']}:</div></td><td class='form_input'>".in_text("color", "", $group['color'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'>".in_sels('image', array_merge(array('clear'=>$main->lang['no']), scan_dir("includes/images/groups/", '/(.+?)\.(gif|png|jpg|jpeg)$/i', true)), 'select2', $group['img'], " onchange=\"$$('preview_cat').src='includes/images/groups/'+this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['preview']}:</td><td class='form_input'><img id='preview_cat' src='".(empty($group['img'])?"includes/images/pixel.gif":"includes/images/groups/{$group['img']}")."' alt='' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['is_special_group']}:</td><td class='form_input'>".in_chck('special', 'checkbox', ($group['special']==1)?ENABLED:"")."</td></tr>\n".        
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
    main::add2script("addEvent(window, 'load', function(){Colors.setup({objectId: 'fish',inputTextId: 'color',styleColor  : 'color', outImage: 'includes/images/rgb.gif',overImage: 'includes/images/on_rgb.gif'});});", false);    
}

function dels_groups(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".GROUPS." WHERE id='{$_GET['id']}'");    
    if (is_ajax()) main_groups(); else redirect(MODULE);
}

function points_conf(){
global $main, $adminfile, $points;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_points' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    (file_exists('modules/media/')?"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_media']}</b>:<br /><i>{$main->lang['p_add_media_d']}</i></td><td class='form_points'>".in_text('add_media', 'points', $points['add_media'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_comment_media']}</b>:<br /><i>{$main->lang['p_comment_media_d']}</i></td><td class='form_points'>".in_text('comment_media', 'points', $points['comment_media'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_media']}</b>:<br /><i>{$main->lang['p_rating_media_d']}</i></td><td class='form_points'>".in_text('rating_media', 'points', $points['rating_media'])."</td></tr>\n":"").    
    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_news']}</b>:<br /><i>{$main->lang['p_add_news_d']}</i></td><td class='form_points'>".in_text('add_news', 'points', $points['add_news'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_comment_news']}</b>:<br /><i>{$main->lang['p_comment_news_d']}</i></td><td class='form_points'>".in_text('comment_news', 'points', $points['comment_news'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_news']}</b>:<br /><i>{$main->lang['p_rating_news_d']}</i></td><td class='form_points'>".in_text('rating_news', 'points', $points['rating_news'])."</td></tr>\n".
    
    (file_exists('modules/pages/')?"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_pages']}</b>:<br /><i>{$main->lang['p_add_pages_d']}</i></td><td class='form_points'>".in_text('add_pages', 'points', $points['add_pages'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_comment_pages']}</b>:<br /><i>{$main->lang['p_comment_pages_d']}</i></td><td class='form_points'>".in_text('comment_pages', 'points', $points['comment_pages'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_pages']}</b>:<br /><i>{$main->lang['p_rating_pages_d']}</i></td><td class='form_points'>".in_text('rating_pages', 'points', $points['rating_pages'])."</td></tr>\n":"").
    
    (file_exists('modules/files/')?"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_files']}</b>:<br /><i>{$main->lang['p_add_files_d']}</i></td><td class='form_points'>".in_text('add_files', 'points', $points['add_files'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_comment_files']}</b>:<br /><i>{$main->lang['p_comment_files_d']}</i></td><td class='form_points'>".in_text('comment_files', 'points', $points['comment_files'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_files']}</b>:<br /><i>{$main->lang['p_rating_files_d']}</i></td><td class='form_points'>".in_text('rating_files', 'points', $points['rating_files'])."</td></tr>\n":"").
    
    (file_exists('modules/jokes/')?"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_jokes']}</b>:<br /><i>{$main->lang['p_add_jokes_d']}</i></td><td class='form_points'>".in_text('add_jokes', 'points', $points['add_jokes'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_jokes']}</b>:<br /><i>{$main->lang['p_rating_jokes_d']}</i></td><td class='form_points'>".in_text('rating_jokes', 'points', $points['rating_jokes'])."</td></tr>\n":"").    
    
    (file_exists('modules/faq/')?"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_add_faq']}</b>:<br /><i>{$main->lang['p_add_faq_d']}</i></td><td class='form_points'>".in_text('add_faq', 'points', $points['add_faq'])."</td></tr>\n":"").    
    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_rating_account']}</b>:<br /><i>{$main->lang['p_rating_account_d']}</i></td><td class='form_points'>".in_text('rating_account', 'points', $points['rating_account'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_voting']}</b>:<br /><i>{$main->lang['p_voting_d']}</i></td><td class='form_points'>".in_text('voting', 'points', $points['voting'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_comment_voting']}</b>:<br /><i>{$main->lang['p_comment_voting_d']}</i></td><td class='form_points'>".in_text('comment_voting', 'points', $points['comment_voting'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_contact']}</b>:<br /><i>{$main->lang['p_contact_d']}</i></td><td class='form_points'>".in_text('contact', 'points', $points['contact'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_recoment']}</b>:<br /><i>{$main->lang['p_recoment_d']}</i></td><td class='form_points'>".in_text('recoment', 'points', $points['recoment'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_forum_topic']}</b>:<br /><i>{$main->lang['p_forum_topic_d']}</i></td><td class='form_points'>".in_text('forum_topic', 'points', $points['forum_topic'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['p_forum_post']}</b>:<br /><i>{$main->lang['p_forum_post_d']}</i></td><td class='form_points'>".in_text('forum_post', 'points', $points['forum_post'])."</td></tr>\n".    
    
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_points(){
global $points, $adminfile, $main; 
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_points.php', '$points', $points);
    redirect("{$adminfile}?module={$_GET['module']}&do=points");
}
function switch_admin_groups(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "delete" : dels_groups(); break;
         case "add" : add_groups(); break;
         case "edit" : edit_groups(); break;
         case "save" : save_groups(); break;
         case "save_edit" : save_groups(); break;
         case "points" : points_conf(); break;
         case "save_points" : save_points(); break;
         default: main_groups(); break;
      }
   } elseif($break_load==false) main_groups();
}
switch_admin_groups();
?>