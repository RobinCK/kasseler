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
    array('add', 'add_site'),
    array('clear', 'clear_rating_site'),
    array('config', 'config')
);

main::required("modules/{$main->module}/globals.php");

function admin_main_top_site(){
global $main, $adminfile;
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "date";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "DESC";
    $result = $main->db->sql_query("SELECT * FROM ".TOPSITES." ORDER BY {$sort} {$sorttype} LIMIT {$offset}, 30");
    $rows = $main->db->sql_numrows($result);
    if($rows>0){
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("date", $main->lang['asdate']), array("status", $main->lang['asstatus']), array("title", $main->lang['astitle'])))."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$main->module}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table cellspacing='1' class='table' width='100%'>\n<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['site_name']}</th><th width='190'>{$main->lang['site_amail']}</th><th width='50'>{$main->lang['language']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";
        while($rows = $main->db->sql_fetchrow($result)){
            $lang = (isset($main->lang[$rows['language']])) ? $main->lang[$rows['language']] : (empty($rows['language']) ? $main->lang['no'] : $rows['language']);
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$rows['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}".($rows['status']==2?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$rows['id']}' /></td><td class='col' align='center'>{$i}</td><td><a href='engine.php?do=redirect&amp;url=".urlencode($rows['link'])."' title='{$rows['title']}'>{$rows['title']}</a></td><td align='center'><a href='mailto:{$rows['mail']}'>{$rows['mail']}</a></td><td align='center'>{$lang}</td><td class='col' align='center' id='onoff_{$rows['id']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$rows['id']}', 'onoff_{$rows['id']}')\">".(($rows['status']==0) ? $main->lang['moderation'] : (($rows['status']==1) ? $main->lang['on'] : $main->lang['off']))."</td><td align='center'>{$op}</td></tr>\n";
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

function admin_config_top_site(){
global $main, $topsite, $adminfile;
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $topsite['directory'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_url']}</b>:<br /><i>{$main->lang['privew_url_d']}</i></td><td class='form_input2'>".in_text('privew_url', 'input_text2', $topsite['privew_url'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['baner_url']}</b>:<br /><i>{$main->lang['baner_url_d']}</i></td><td class='form_input2'>".in_text('baner_url', 'input_text2', $topsite['baner_url'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('privew_type', 'input_text2', $topsite['privew_type'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_width']}</b>:<br /><i>{$main->lang['privew_width_d']}</i></td><td class='form_input2'>".in_text('privew_width', 'input_text2', $topsite['privew_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_height']}</b>:<br /><i>{$main->lang['privew_height']}</i></td><td class='form_input2'>".in_text('privew_height', 'input_text2', $topsite['privew_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_size']}</b>:<br /><i>{$main->lang['privew_size_d']}</i></td><td class='form_input2'>".in_text('privew_size', 'input_text2', $topsite['privew_size'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_in_page']}</b>:<br /><i>{$main->lang['publications_in_page_d']}</i></td><td class='form_input2'>".in_text('publications_in_page', 'input_text2', $topsite['publications_in_page'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_cols']}</b>:<br /><i>{$main->lang['publications_cols_d']}</i></td><td class='form_input2'>".in_text('publications_cols', 'input_text2', $topsite['publications_cols'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_d']}</i></td><td class='form_input2'>".in_chck('ratings', 'input_checkbox', $topsite['ratings'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_users']}</b>:<br /><i>{$main->lang['publications_users_d']}</i></td><td class='form_input2'>".in_chck('publications_users', 'input_checkbox', $topsite['publications_users'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['publications_guest']}</b>:<br /><i>{$main->lang['publications_guest_d']}</i></td><td class='form_input2'>".in_chck('publications_guest', 'input_checkbox', $topsite['publications_guest'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['privew_status']}</b>:<br /><i>{$main->lang['privew_status_d']}</i></td><td class='form_input2'>".in_chck('privew_status', 'input_checkbox', $topsite['privew_status'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['description_status']}</b>:<br /><i>{$main->lang['description_status_d']}</i></td><td class='form_input2'>".in_chck('description_status', 'input_checkbox', $topsite['description_status'])."</td></tr>\n".    
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";   
}

function admin_save_conf_top_site(){
global $topsite, $adminfile;
    main::init_function('sources');
    save_config('config_top_sites.php', '$topsite', $topsite);
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function admin_clear_top_site(){
global $main;
    $main->db->sql_query("UPDATE ".TOPSITES." SET hits_out='0', hits_in='0'");
    redirect(MODULE);
}

function admin_main_on_off_topsite(){
global $main;
    list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".TOPSITES." WHERE id='{$_GET['id']}'"));
    if($status==1){
        $main->db->sql_query("UPDATE ".TOPSITES." SET status='2' WHERE id='{$_GET['id']}'");
        echo $main->lang['off']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".TOPSITES." SET status='1' WHERE id='{$_GET['id']}'");
        echo $main->lang['on']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function admin_change_op_topsite(){
global $main, $topsite;
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".TOPSITES." WHERE id='{$value}'"));
                sql_update(array('status' => (($status!=1) ? 1 : 2)), TOPSITES, "id='{$value}'");
            }
        } else {
            foreach($_POST['sels'] as $value) {
                $img = $main->db->sql_fetchrow($main->db->sql_query("SELECT img FROM ".TOPSITES." WHERE id='{$value}'"));
                $main->db->sql_query("DELETE FROM ".TOPSITES." WHERE id='{$value}'");                
                if(file_exists($topsite['directory'].$img)) unlink($topsite['directory'].$img);
            }
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else admin_main_top_site();
}

function admin_dels_topsite(){
global $main, $topsite;
    $img = $main->db->sql_fetchrow($main->db->sql_query("SELECT img FROM ".TOPSITES." WHERE id='{$_GET['id']}'"));
    $main->db->sql_query("DELETE FROM ".TOPSITES." WHERE id='{$_GET['id']}'");    
    if(file_exists($topsite['directory'].$img)) unlink($topsite['directory'].$img);
    if(is_ajax()) admin_main_top_site(); else redirect(MODULE);
}

function admin_edit_topsite($msg=""){
global $main, $topsite, $adminfile, $tpl_create;
    if(!empty($msg)) warning($msg);
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".TOPSITES." WHERE id='{$_GET['id']}'"));
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $info['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_amail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("email", "input_text2", $info['mail'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file($info['language'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".editor("descript", 10, "97%", bb($info['description'], DECODE))."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_url']}:<span class='star'>*</span></td><td class='form_input'>".in_text("link", "input_text2", $info['link'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image_site']}:</td><td class='form_input'>".((file_exists($topsite['directory'].$info['img']) AND ! empty($info['img']))?"<center><img src='{$topsite['directory']}{$info['img']}' alt='{$info['title']}' /></center><br />":"")."".in_text("image", "input_text2", $info['img'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_preview']}:<br /><i>{$main->lang['width']}: {$topsite['privew_width']}; {$main->lang['height']}: {$topsite['privew_height']};</i></td><td class='form_input'><input type='file' name='file_cover' size='44' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date($info['date'], "Y-m-d"))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox", $info['status']==1?ENABLED:'')."</td></tr>".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";    
}

function admin_save_edit_topsite(){
global $main, $topsite, $adminfile;
    filter_arr(array('name', 'title', 'descript'), POST, TAGS);
    $msg = error_empty(array('email', 'title', 'link'), array('site_amail_err', 'title_site_err', 'link_err')).check_captcha();
    $site = "";
    $url = trim($_POST['link']);
    if(!empty($url)){
        $link = (mb_substr($url, 0, 7) != 'http://') ? "http://".$url : $url;
        $parse = parse_url($link);
        $ip = gethostbyname($parse['host']);
        if(is_ip($ip)) $site = $parse['host'];
        else $msg = $main->lang['error_site_domain'];
    } else $msg .= $main->lang['error_site_domain'];
    if(empty($msg)){
        $result = $main->db->sql_query("SELECT title FROM ".TOPSITES." WHERE title='{$_POST['title']}' AND id<>'{$_GET['id']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
        $img = $_POST['image'];
        if(empty($msg)){
            if(isset($_FILES["file_cover"]) AND !empty($_FILES["file_cover"]['name'])){
                main::init_class('uploader');
                $attach = new upload(array(
                    'dir'       => $topsite['directory'],
                    'file'      => $_FILES["file_cover"],
                    'size'      => $topsite['privew_size'],
                    'type'      => explode(",", $topsite['privew_type']),
                    'name'      => $site,
                    'width'     => $topsite['privew_width'], 
                    'height'    => $topsite['privew_height'],
                    'overwrite' => true
                ));
                if(!$attach->error) $img = $attach->file;
                else $msg = $attach->get_error_msg();
            }
        }
    }       
    if(empty($msg)){
        sql_update(array(
            'title'         => $_POST['title'],
            'link'          => "http://".$site,
            'img'           => $img,
            'mail'          => $_POST['email'],
            'date'          => kr_dateuser2db(),
            'status'        => (is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,                        
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",            
            'description'   => bb($_POST['descript'])
        ), TOPSITES, "id='{$_GET['id']}'");
        redirect(MODULE);
    } else admin_edit_topsite($msg);
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case "config": admin_config_top_site(); break;
        case "save_conf": admin_save_conf_top_site(); break;
        case "clear": admin_clear_top_site(); break;
        case "add": global_add_topsite(); break;
        case "save": global_save_topsite(); break;
        case "edit": admin_edit_topsite(); break;
        case "save_edit": admin_save_edit_topsite(); break;
        case "delete": admin_dels_topsite(); break;
        case "on_off": admin_main_on_off_topsite(); break;
        case "change_op": admin_change_op_topsite(); break;
        default: admin_main_top_site(); break;
    }
} elseif($break_load==false) admin_main_top_site();
?>