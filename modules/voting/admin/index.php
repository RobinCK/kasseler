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

main::required("modules/{$main->module}/global.php");

$navi = array(
    array('', 'home'),
    array('add', 'addvoting'),
    array('config', 'config')
);

function admin_main_voting(){
global $main, $adminfile;    
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "id";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "DESC";
    $result = $main->db->sql_query("SELECT * FROM ".VOTING." ORDER BY {$sort} {$sorttype} LIMIT {$offset}, 30");
    $rows = $main->db->sql_numrows($result);
    if($rows>0){        
        $tr = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("status", $main->lang['asstatus']), array("title", $main->lang['astitle'])))."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$main->module}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table cellspacing='1' class='table' width='100%'>\n<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['title']}</th><th width='50'>{$main->lang['language']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";        
        while(($row = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$row['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            $lang = (isset($main->lang[$row['language']])) ? $main->lang[$row['language']] : (empty($row['language']) ? $main->lang['no'] : $row['language']);
            echo "<tr class='{$tr}".($row['status']==2?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$row['id']}' /></td><td class='col' align='center'>{$i}</td><td>{$row['title']}</td><td align='center'>{$lang}</td><td class='col' align='center' id='onoff_{$row['id']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$row['id']}', 'onoff_{$row['id']}')\">".(($row['status']==1) ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>\n";
            $tr = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table><table width='100%'><tr><td>".get_function_checked()."</td></tr></table></form>";  
    } else info($main->lang['noinfo']);
}

function admin_on_off_voting(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".VOTING." WHERE id='{$_GET['id']}'"));
    if($status==1){
        $main->db->sql_query("UPDATE ".VOTING." SET status='2' WHERE id='{$_GET['id']}'");
        echo $main->lang['off']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".VOTING." SET status='1' WHERE id='{$_GET['id']}'");
        echo $main->lang['on']."<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function admin_change_op_voting(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".VOTING." WHERE id='{$value}'"));
                sql_update(array('status' => (($status!=1) ? 1 : 2)), VOTING, "id='{$value}'");
            }
        } else foreach($_POST['sels'] as $value) $main->db->sql_query("DELETE FROM ".VOTING." WHERE id='{$value}'");
    }
    if(!is_ajax()) redirect(MODULE);
    else admin_main_voting();
}

function admin_dels_voting(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".VOTING." WHERE id='{$_GET['id']}'");    
    if(!is_ajax()) redirect(MODULE);
    else admin_main_voting();
}


function admin_add_voting($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo global_add_voting("{$adminfile}?module={$main->module}&amp;do=save",$msg);
}

function admin_save_voting(){
global $main, $adminfile, $pull;
    if(hook_check(__FUNCTION__)) return hook();
    $ret=global_save_voting(isset($_GET['id'])?intval($_GET['id']):0);
    if(is_numeric($ret)){
       redirect(MODULE);
    } else admin_add_voting($ret);
}

function admin_edit_voting($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo global_edit_voting(intval($_GET['id']),"{$adminfile}?module={$main->module}&amp;do=save&amp;id={$_GET['id']}",$msg);
}

function admin_config_vote(){
global $pull, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['comments_sort']}</b>:<br /><i>{$main->lang['comments_sort_d']}</i></td><td class='form_input2'>".in_sels('comments_sort', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $pull['comments_sort'])."</td></tr>\n".            
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['guests_comments']}</b>:<br /><i>{$main->lang['guests_comments_d']}</i></td><td class='form_input2'>".in_chck('guests_comments', 'input_checkbox', $pull['guests_comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_comments']}</b>:<br /><i>{$main->lang['conf_comments_dv']}</i></td><td class='form_input2'>".in_chck('comments', 'input_checkbox', $pull['comments'])."</td></tr>\n".    
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function admin_saves_vote(){
global $pull, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_voting.php', '$pull', $pull);
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}   

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){        
        case "on_off": admin_on_off_voting(); break;
        case "delete": admin_dels_voting(); break;
        case "change_op": admin_change_op_voting(); break;        
        case "add": admin_add_voting(); break;
        case "save": admin_save_voting(); break;
        case "edit": admin_edit_voting(); break;
        case "config": admin_config_vote(); break;
        case "save_conf": admin_saves_vote(); break;
        default: admin_main_voting(); break;
    }
} elseif($break_load==false) admin_main_voting();
?>