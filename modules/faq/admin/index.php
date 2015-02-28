<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
$break_load=false;
if(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('add', 'addfaq'),
    array('config', 'config')
);
function admin_main_faq(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_array_cat', 'get_text_categorys');
    
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "id";
    $filter=(isset($_GET['filter']) AND !empty($_GET['filter']) AND intval($_GET['filter'])!=0) ? intval($_GET['filter']) : "";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "DESC";
    $result = $main->db->sql_query("SELECT * FROM ".FAQ." ".(($filter!="")?"where cid like '%,{$filter},%' ":"")." ORDER BY {$sort} {$sorttype} LIMIT {$offset}, 30");
    $rows = $main->db->sql_numrows($result);
    if($rows>0){
        $cat=get_array_cat($main->module);
        $tr = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("status", $main->lang['asstatus']), array("question", $main->lang['astitle'])),filter_array())."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$main->module}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table cellspacing='1' class='table' width='100%'>\n<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['question']}</th><th width='120'>{$main->lang['category']}</th><th width='50'>{$main->lang['language']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";        
        while(($row = $main->db->sql_fetchrow($result))){
            $catname=get_text_categorys($cat,$row['cid']);
            $lang = (isset($main->lang[$row['language']])) ? $main->lang[$row['language']] : (empty($row['language']) ? $main->lang['no'] : $row['language']);
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$row['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}".($row['status']==2?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$row['id']}' /></td><td class='col' align='center'>{$i}</td><td>{$row['question']}</td><td align='center'>{$catname}</td><td align='center'>{$lang}</td><td class='col' align='center' id='onoff_{$row['id']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$row['id']}', 'onoff_{$row['id']}')\">".(($row['status']==1) ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>\n";
            $tr = ($tr=="row1") ? "row2" : "row1";
            $i++;
        }        
        echo "</table><table width='100%'><tr><td>".get_function_checked()."</td></tr></table></form>";  
         if($rows==30 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FAQ." "));
            pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
        }
    } else info($main->lang['noinfo']);
}

function admin_config_faq(){
global $faq, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $faq['directory'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $faq['attaching_files_type'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_width']}</b>:<br /><i>{$main->lang['miniature_image_width_d']}</i></td><td class='form_input2'>".in_text('miniature_image_width', 'input_text2', $faq['miniature_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_height']}</b>:<br /><i>{$main->lang['miniature_image_height_d']}</i></td><td class='form_input2'>".in_text('miniature_image_height', 'input_text2', $faq['miniature_image_height'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $faq['max_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $faq['max_image_height'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $faq['attaching_files_size'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['file_upload_limit']}</b>:<br /><i>{$main->lang['file_upload_limit_d']}</i></td><td class='form_input2'>".in_text('file_upload_limit', 'input_text2', $faq['file_upload_limit'])."</td></tr>\n".    
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>";
}

function admin_saves_faq(){
global $faq, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_faq.php', '$faq', $faq);
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function admin_on_off_faq(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".FAQ." WHERE id='{$_GET['id']}'"));
    if($status==1){
        $main->db->sql_query("UPDATE ".FAQ." SET status='2' WHERE id='{$_GET['id']}'");
        echo $main->lang['off']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".FAQ." SET status='1' WHERE id='{$_GET['id']}'");
        echo $main->lang['on']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function admin_change_op_faq(){
global $main, $faq;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".FAQ." WHERE id='{$value}'"));
                sql_update(array('status' => (($status!=1) ? 1 : 2)), FAQ, "id='{$value}'");
            }
        } else {
            foreach($_POST['sels'] as $value) {
                $main->db->sql_query("DELETE FROM ".FAQ." WHERE id='{$value}'");
                $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$faq['directory']}{$value}/%'");
                if(file_exists($faq['directory'].$value)) remove_dir($faq['directory'].$value);
            }
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else admin_main_faq();
}

function admin_dels_faq(){
global $main, $faq;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".FAQ." WHERE id='{$_GET['id']}'");
    $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '{$faq['directory']}{$_GET['id']}/%'");
    if(file_exists($faq['directory'].$_GET['id'])) remove_dir($faq['directory'].$_GET['id']);
    if(!is_ajax()) redirect(MODULE);
    else admin_main_faq();
}

function admin_add_faq($msg=""){
global $main, $faq, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    main::init_function('attache');
    $_SESSION['uploaddir'] = $faq['directory'].USER_FOLDER."/";            
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['question']}:<span class='star'>*</span></td><td class='form_input'>".in_text("question", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(array(''), $main->module, false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['answer']}:<span class='star'>*</span></td><td class='form_input'>".editor("answer", 10, "97%")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['active_faq']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>".
    "<tr><td>".in_hide('attache_page', "{$adminfile}?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />"."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>\n";
}

function attache_page_faq(){
global $main, $faq, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $_SESSION['uploaddir'], true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($_SESSION['uploaddir'])."</div>".SWFUpload("index.php?module={$main->module}&amp;do=upload", $faq['attaching_files_type'], $faq['attaching_files_size'], $faq['file_upload_limit'])."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    kr_exit();
}

function admin_save_question(){
global $main, $faq;
    if(hook_check(__FUNCTION__)) return hook();
    filter_arr(array('question'), POST, TAGS);
    $msg = error_empty(array('question', 'answer'), array('question_err', 'answer_err'));    
    if(empty($msg)){
        $nex_id = $main->db->sql_nextid(sql_insert(array(
            'question'      => $_POST['question'],
            'answer'        => bb(kr_filter($_POST['answer'])),
            'cid'           => isset($_POST['cid']) ? ",".$_POST['cid'][0]."," : "",
            'status'        => (isset($_POST['active']) AND $_POST['active']=='on') ? 1 : 0,            
            'language'      => $_POST['language']            
        ), FAQ));
        add_points($main->points['add_jokes']);
        if(rename_attach($faq['directory'].USER_FOLDER."/", $faq['directory'].$nex_id."/")){ 
            $_POST['answer'] = str_replace(USER_FOLDER, $nex_id, $_POST['answer']);
            sql_update(array('answer'  => bb(kr_filter($_POST['answer']))), FAQ, "id='{$nex_id}'");
        }        
        redirect(MODULE);
    } else admin_add_faq($msg);
}

function admin_edit_faq($msg=""){
global $main, $faq, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".FAQ." WHERE id='{$_GET['id']}'"));
    main::init_function('attache');
    $_SESSION['uploaddir'] = file_exists($faq['directory'].$_GET['id']."/") ? $faq['directory'].$_GET['id']."/" : $faq['directory'].USER_FOLDER."/";
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['question']}:<span class='star'>*</span></td><td class='form_input'>".in_text("question", "input_text2", $info['question'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(explode(',', $info['cid']), $main->module, false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file($info['language'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['answer']}:<span class='star'>*</span></td><td class='form_input'>".editor("answer", 10, "97%", bb($info['answer'], DECODE))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['active_faq']}</td><td class='form_input '>".in_chck("active", "input_checkbox", $info['status']==1?ENABLED:'')."</td></tr>".
    "<tr><td>".in_hide('attache_page', "{$adminfile}?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />"."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>\n";
}

function admin_save_edit_faq(){
global $main, $faq;
    if(hook_check(__FUNCTION__)) return hook();
    filter_arr(array('question'), POST, TAGS);
    $msg = error_empty(array('question', 'answer'), array('question_err', 'answer_err'));    
    if(empty($msg)){
        sql_update(array(
            'question'      => $_POST['question'],
            'answer'        => bb(kr_filter($_POST['answer'])),
            'cid'           => isset($_POST['cid']) ? ",".$_POST['cid'][0]."," : "",
            'status'        => (isset($_POST['active']) AND $_POST['active']=='on') ? 1 : 0,            
            'language'      => $_POST['language']            
        ), FAQ, "id='{$_GET['id']}'");
        if(rename_attach($faq['directory'].USER_FOLDER."/", $faq['directory'].$_GET['id']."/")){ 
            $_POST['answer'] = str_replace(USER_FOLDER, $_GET['id'], $_POST['answer']);
            sql_update(array('answer'  => bb(kr_filter($_POST['answer']))), FAQ, "id='{$_GET['id']}'");
        }
        redirect(MODULE);
    } else admin_add_faq($msg);
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){        
        case "on_off": admin_on_off_faq(); break;
        case "delete": admin_dels_faq(); break;
        case "change_op": admin_change_op_faq(); break;
        case "config": admin_config_faq(); break;
        case "save_conf": admin_saves_faq(); break;
        case "add": admin_add_faq(); break;
        case "save": admin_save_question(); break;
        case "edit": admin_edit_faq(); break;
        case "save_edit": admin_save_edit_faq(); break;
        case "attache_page": attache_page_faq(); break;
        default: admin_main_faq(); break;
    }
} elseif($break_load==false) admin_main_faq();
?>