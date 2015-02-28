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
    array('add', 'addjokes'),
    array('config', 'config')
);

main::required("modules/{$main->module}/globals.php");

function admin_main_jokes(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "date";
    $filter=(isset($_GET['filter']) AND !empty($_GET['filter']) AND intval($_GET['filter'])!=0) ? intval($_GET['filter']) : "";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "DESC";
    $result = $main->db->sql_query("SELECT j.id, j.title, j.author, j.date, j.cid, j.status, j.language, u.uid, u.user_id, u.user_name FROM ".JOKES." AS j LEFT JOIN ".USERS." AS u ON(j.author=u.user_name) ".(($filter!="")?"where j.cid like '%,{$filter},%' ":"")." ORDER BY j.{$sort} {$sorttype} LIMIT {$offset}, 30");
    $rows = $main->db->sql_numrows($result);
    if($rows>0){
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("date", $main->lang['asdate']), array("status", $main->lang['asstatus']), array("title", $main->lang['astitle'])),filter_array())."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$main->module}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table cellspacing='1' class='table' width='100%'>\n<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['title']}</th><th width='80'>{$main->lang['author']}</th><th width='50'>{$main->lang['language']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";
        while(list($id, $title, $author, $date, $cid, $status, $language, $uid, $user_id, $user_name) = $main->db->sql_fetchrow($result)){
            $aut = (!is_guest_name($author) AND !empty($user_name)) ? "<a class='author' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id, $uid)))."' title='{$main->lang['user_profile']}'>{$author}</a>" : $author;
            $lang = (isset($main->lang[$language])) ? $main->lang[$language] : (empty($language) ? $main->lang['no'] : $language);
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$id}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$id}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}".($status==2?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$id}' /></td><td class='col' align='center'>{$i}</td><td>{$title}</td><td align='center' class='col'>{$aut}</td><td align='center'>{$lang}</td><td class='col' align='center' id='onoff_{$id}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$id}', 'onoff_{$id}')\">".(($status==0) ? $main->lang['moderation'] : (($status==1) ? $main->lang['on'] : $main->lang['off']))."</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }                    
        echo "</table><table width='100%'><tr><td>".get_function_checked()."</td></tr></table></form>";  
        if($rows==30 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".JOKES." "));
            pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
        }       
    } else info($main->lang['noinfo']);      
}
            
function admin_edit_jokes($msg=""){
global $main, $jokes, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
    main::init_function('attache');
    main::add2script("includes/javascript/kr_calendar.js");
    main::add2link("includes/css/kr_calendar.css");    
    $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".JOKES." WHERE id={$_GET['id']}"));    
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2", $result['author'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $result['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(explode(",", $result['cid']), $main->module)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file($result['language'])."</td></tr>\n".                                  
    "<tr class='row_tr'><td class='form_text'>{$main->lang['joke']}:<span class='star'>*</span></td><td class='form_input'>".editor("joke", 12, "500px", bb($result['joke'], DECODE))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['rating']}:</td><td class='form_input'>".in_text("rating", "input_text2", $result['rating'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date($result['date'], "Y-m-d H:i:s"))."</td></tr>\n".            
    "<tr><td class='form_text'>{$main->lang['active']}:</td><td class='form_input '>".in_chck("active", "input_checkbox", (($result['status']==1)?ENABLED:""))."</td></tr>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['delete']}?:</td><td class='form_input '>".in_chck("delete", "input_checkbox", "")."</td></tr>".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function admin_del_record_jokes($value){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".JOKES." WHERE id='{$value}'");
    $main->db->sql_query("DELETE FROM ".FAVORITE." WHERE modul='{$main->module}' AND post='{$value}'");
    $main->db->sql_query("DELETE FROM ".CALENDAR." WHERE module='{$main->module}' AND id='{$value}'");
}

function admin_save_edit_jokes(){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['delete']) AND $_POST['delete']==ENABLED){
        admin_del_record_jokes($_GET['id']);
        redirect(MODULE);
    }
    filter_arr(array('name', 'title', 'joke'), POST, TAGS);
    $msg = error_empty(array('name', 'title', 'joke'), array('author_err', 'title_err', 'text_err'));
    if(empty($msg)){
        $result = $main->db->sql_query("SELECT title FROM ".JOKES." WHERE title='{$_POST['title']}' AND id<>{$_GET['id']}");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
    }
    if(empty($msg)){
         $cid = ",";         
         if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $value) $cid .= $value.",";         
         sql_update(array(
            'title'         => $_POST['title'],
            'joke'          => bb($_POST['joke']),            
            'author'        => $_POST['name'],
            'date'          => kr_dateuser2db(),
            'cid'           => $cid,
            'status'        => (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,
            'rating'        => $_POST['rating'],
            'language'      => $_POST['language']
        ), JOKES, "id={$_GET['id']}");
        set_calendar_date($_GET['id'], $main->module, kr_dateuser2db("Y-m-d"), (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0);
        redirect(MODULE);
    } else admin_edit_jokes($msg);
}

function admin_config_jokes(){
global $jokes, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_in_page']}</b>:<br /><i>{$main->lang['publications_in_page_d']}</i></td><td class='form_input2'>".in_text('publications_in_page', 'input_text2', $jokes['publications_in_page'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sort_type_publications']}</b>:<br /><i>{$main->lang['sort_type_publications_d']}</i></td><td class='form_input2'>".in_sels('sort_type_publications', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $jokes['sort_type_publications'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sort_publications']}</b>:<br /><i>{$main->lang['sort_publications_d']}</i></td><td class='form_input2'>".in_sels('sort_publications', array('date'=>$main->lang['asdate'], 'title'=>$main->lang['astitle']), 'select chzn-search-hide', $jokes['sort_publications'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['cat_cols']}</b>:<br /><i>{$main->lang['cat_cols_d']}</i></td><td class='form_input2'>".in_text('cat_cols', 'input_text2', $jokes['cat_cols'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_title']}</b>:<br /><i>{$main->lang['rss_title_d']}</i></td><td class='form_input2'>".in_text('rss_title', 'input_text2', $jokes['rss_title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rss_limit']}</b>:<br /><i>{$main->lang['rss_limit_d']}</i></td><td class='form_input2'>".in_text('rss_limit', 'input_text2', $jokes['rss_limit'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_rss']}</b>:<br /><i>{$main->lang['conf_rss_d']}</i></td><td class='form_input2'>".in_chck('rss', 'input_checkbox', $jokes['rss'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['clasic_cat']}</b>:<br /><i>{$main->lang['clasic_cat_d']}</i></td><td class='form_input2'>".in_chck('clasic_cat', 'input_checkbox', $jokes['clasic_cat'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_categories']}</b>:<br /><i>{$main->lang['conf_categories_d']}</i></td><td class='form_input2'>".in_chck('categories', 'input_checkbox', $jokes['categories'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['categories_ico']}</b>:<br /><i>{$main->lang['categories_ico_d']}</i></td><td class='form_input2'>".in_chck('categories_ico', 'input_checkbox', $jokes['categories_ico'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['categories_desc']}</b>:<br /><i>{$main->lang['categories_desc_d']}</i></td><td class='form_input2'>".in_chck('categories_desc', 'input_checkbox', $jokes['categories_desc'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['multiple_cat']}</b>:<br /><i>{$main->lang['multiple_cat_d']}</i></td><td class='form_input2'>".in_chck('multiple_cat', 'input_checkbox', $jokes['multiple_cat'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_d']}</i></td><td class='form_input2'>".in_chck('ratings', 'input_checkbox', $jokes['ratings'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['moderation_publications']}</b>:<br /><i>{$main->lang['moderation_publications_d']}</i></td><td class='form_input2'>".in_chck('moderation_publications', 'input_checkbox', $jokes['moderation_publications'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_users']}</b>:<br /><i>{$main->lang['publications_users_d']}</i></td><td class='form_input2'>".in_chck('publications_users', 'input_checkbox', $jokes['publications_users'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_guest']}</b>:<br /><i>{$main->lang['publications_guest_d']}</i></td><td class='form_input2'>".in_chck('publications_guest', 'input_checkbox', $jokes['publications_guest'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['favorite_status']}</b>:<br /><i>{$main->lang['favorite_status_d']}</i></td><td class='form_input2'>".in_chck('favorite_status', 'input_checkbox', $jokes['favorite_status'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['favorite_page_status']}</b>:<br /><i>{$main->lang['favorite_page_status_d']}</i></td><td class='form_input2'>".in_chck('favorite_page', 'input_checkbox', $jokes['favorite_page'])."</td></tr>\n".
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function admin_saves_jokes(){
global $jokes, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_jokes.php', '$jokes', $jokes);
    update_rss_config();
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function admin_on_off_jokes(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".JOKES." WHERE id='{$_GET['id']}'"));
    if($info['status']==1){
        $main->db->sql_query("UPDATE ".JOKES." SET status='2' WHERE id='{$_GET['id']}'");
        set_calendar_date($info['id'], $main->module, $info['date'], 0);
        echo $main->lang['off']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".JOKES." SET status='1' WHERE id='{$_GET['id']}'");
        set_calendar_date($info['id'], $main->module, $info['date'], 1);
        echo $main->lang['on']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function admin_change_op_jokes(){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".JOKES." WHERE id='{$value}'"));
                sql_update(array('status' => (($status!=1) ? 1 : 2)), JOKES, "id='{$value}'");
            }
        } else foreach($_POST['sels'] as $value) {
            admin_del_record_jokes($value);
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else admin_main_jokes();
}

function admin_dels_jokes(){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    delete_points($main->points['add_jokes'], $_GET['id'],'jokes');
    admin_del_record_jokes($_GET['id']);
    if(is_ajax()) admin_main_jokes(); else redirect(MODULE);
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case "add": global_add_jokes(); break;
        case "save": global_save_jokes(); break;
        case "edit": admin_edit_jokes(); break;        
        case "save_edit": admin_save_edit_jokes(); break;
        case "config": admin_config_jokes(); break;
        case "save_conf": admin_saves_jokes(); break;
        case "delete": admin_dels_jokes(); break;
        case "on_off": admin_on_off_jokes(); break;
        case "change_op": admin_change_op_jokes(); break;
        default: admin_main_jokes(); break;
    }
} elseif($break_load==false) admin_main_jokes();
?>