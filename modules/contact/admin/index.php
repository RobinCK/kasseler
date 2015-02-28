<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $main, $navi, $break_load;
$break_load=false;
if(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('form_add', 'add_row_form'),
    array('config', 'config'),
);

function admin_main_contact(){
global $main, $adminfile, $contact_form;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<table cellspacing='1' class='table' width='100%'>".
    "<tr><th width='15'>#</th><th>{$main->lang['title']}</th><th width='120'>{$main->lang['name']}</th><th width='120'>{$main->lang['type_form_row']}</th><th width='70'>{$main->lang['position']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
    $i = 1; $row = "row1";
    $count = count($contact_form);
    foreach($contact_form as $arr){
        $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit_form&amp;id=".($i-1)).delete_button("{$adminfile}?module={$main->module}&amp;do=delete_form_row&amp;id=".($i-1), 'ajax_content')."</td></tr></table>";
        $up_down = ($count>1) ? up_down_analizy($i, $count, $i-1, 'ajax_content') : "";
        echo "<tr class='{$row}'><td align='center' class='col'>{$i}</td><td>{$arr['title']}</td><td class='col'>".($arr['type']!='file'?$arr['name']:'&nbsp;')."</td><td align='center'>"._type_form_name_set($arr['type'])."</td><td class='col' align='center'>{$up_down}</td><td align='center'>{$op}</td></tr>";
        $row = ($row=="row1") ? "row2" : "row1"; 
        $i++;
    }
    echo "</table>";
}

function admin_moves_contact(){
global $main, $contact_form, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=="up") $next = $_GET['id']-1; else $next = $_GET['id']+1;
    $_arr = $contact_form[$_GET['id']];
    $contact_form[$_GET['id']] = $contact_form[$next];
    $contact_form[$next] = $_arr;
    admin_save_config_file_contact($contact_form);
    if(is_ajax()) admin_main_contact(); else redirect("{$adminfile}?module={$main->module}");
}

function admin_save_config_file_contact($config){
global $copyright_file;
    $file = fopen("includes/config/config_contact_form.php", "w");
    fputs ($file, $copyright_file.arr2str($config, 'contact_form')."\n?".">");
    fclose ($file);
}

function admin_dels_form_row_contact(){
global $main, $contact_form, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    unset($contact_form[$_GET['id']]);
    $_contact_form = $contact_form;
    unset($contact_form);
    $contact_form = array();
    foreach($_contact_form as $value) $contact_form[] = $value;
    admin_save_config_file_contact($contact_form);
    if(is_ajax()) admin_main_contact(); else redirect("{$adminfile}?module={$main->module}");
}

function admin_form_add_contact($msg=""){
global $main, $adminfile, $lang;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=save_elm_form' method='post'>".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:</td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:</td><td class='form_input'>".in_text("name", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['class']}:</td><td class='form_input'>".in_text("class", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['type_form_row']}:</td><td class='form_input'>".in_sels("type", array('text' => $lang['text_form'], 'textarea' => $lang['textarea_form'], 'checkbox' => $lang['checkbox_form'], 'radio' => $lang['radio_form'], 'select' => $lang['select_form'], 'file' => $lang['file_form']), "select chzn-search-hide", '', " onchange=\"select_form_elm(this);\"")."</td></tr>\n".    
    "<tr class='row_tr' id='def_val'><td class='form_text'>{$main->lang['default_value']}:</td><td class='form_input'>".in_text("default", 'input_text2', '')."</td></tr>\n".
    "<tr class='row_tr' id='def_val_ck' style='display:none;'><td class='form_text'>{$main->lang['default_value']}:</td><td class='form_input'>".in_radio("default_ck", 'yes', $main->lang['activate'], 'ac')." ".in_radio("default_ck", 'no', $main->lang['deactivate'], 'deac', true)."</td></tr>\n".
    "<tr class='row_tr' id='case_val' style='display:none;'><td class='form_text'>{$main->lang['case_value']}:</td><td class='form_input'>".in_case_val('select', 'input_text')."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['must']}:</td><td class='form_input'>".in_chck("must", "checkbox")."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>".
    "</form>";
}

function admin_save_elm_form_contact(){
global $main, $adminfile, $lang, $contact_form;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($_POST['title']) OR empty($_POST['name']) AND $_POST['type']!='file') $msg = $main->lang['allerror'];
    if($_POST['type']=='text' OR $_POST['type']=='textarea'){
        $default = $_POST['default'];
        $option = false;
    } elseif($_POST['type']=='checkbox'){
        $default = $_POST['default_ck']=='no' ? false : true;
        $option = false;
    } elseif($_POST['type']=='select'){
        $default = (isset($_POST['selected']) AND !empty($_POST['selected'])) ? $_POST['selected'] : 0;
        $option = array();
        if(is_array($_POST['select']) AND count($_POST['select'])>0){
            foreach($_POST['select'] as $key => $value) if($value!='') $option[] = $value;
            if(count($option)==0) $msg = $main->lang['allerror'];
        } else $msg = $main->lang['allerror'];
    } elseif($_POST['type']=='radio'){
        $default = (isset($_POST['selected']) AND !empty($_POST['selected'])) ? $_POST['selected'] : 0;
        $option = array();
        if(is_array($_POST['select']) AND count($_POST['select'])>0){
            foreach($_POST['select'] as $key => $value) {
                if($value!='') {
                    $option[] = array(
                        'title' => $value,
                        'value' => "val1".$key
                    );
                }
            }
            if(count($option)==0) $msg = $main->lang['allerror'];
        } else $msg = $main->lang['allerror'];
    } 
    if(empty($msg)){
        $_arr = array (
            'title'   => $_POST['title'],
            'name'    => isset($_POST['name'])?$_POST['name']:'fileloader[]',
            'type'    => $_POST['type'],
            'class'   => $_POST['class'],
            'default' => isset($default)?$default:'',
            'option'  => isset($option)?$option:'',
            'must'    => (isset($_POST['must']) AND $_POST['must']=='on') ? true : false,
        );                
        if(!isset($_GET['id'])) $contact_form[] = $_arr;
        else $contact_form[$_GET['id']] = $_arr;
        admin_save_config_file_contact($contact_form);
        redirect("{$adminfile}?module={$main->module}");
    } else form_add($msg); 
}

function admin_edit_form_contact(){
global $main, $adminfile, $lang, $contact_form;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $d = $contact_form[$_GET['id']]['default'];
    if($contact_form[$_GET['id']]['type']=='select') {
        $o = $contact_form[$_GET['id']]['option'];
    } elseif($contact_form[$_GET['id']]['type']=='radio') {
        $o = array();
        foreach($contact_form[$_GET['id']]['option'] as $value) $o[] = $value['title'];
    }
    $m = $contact_form[$_GET['id']]['must'];
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=save_elm_form&amp;id={$_GET['id']}' method='post'>".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:</td><td class='form_input'>".in_text("title", "input_text2", $contact_form[$_GET['id']]['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:</td><td class='form_input'>".in_text("name", "input_text2", $contact_form[$_GET['id']]['type']!='file'?$contact_form[$_GET['id']]['name']:'')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['class']}:</td><td class='form_input'>".in_text("class", "input_text2", $contact_form[$_GET['id']]['class'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['type_form_row']}:</td><td class='form_input'>".in_sels("type", array('text' => $lang['text_form'], 'textarea' => $lang['textarea_form'], 'checkbox' => $lang['checkbox_form'], 'radio' => $lang['radio_form'], 'select' => $lang['select_form'], 'file' => $lang['file_form']), "select chzn-search-hide", $contact_form[$_GET['id']]['type'], " onchange=\"select_form_elm(this);\"")."</td></tr>\n".    
    "<tr class='row_tr' id='def_val'".(($contact_form[$_GET['id']]['type']=='text' OR $contact_form[$_GET['id']]['type']=='textarea')?'':" style='display:none;'")."><td class='form_text'>{$main->lang['default_value']}:</td><td class='form_input'>".in_text("default", 'input_text2', is_string($d)?$d:'')."</td></tr>\n".
    "<tr class='row_tr' id='def_val_ck'".($contact_form[$_GET['id']]['type']=='checkbox'?'':" style='display:none;'")."><td class='form_text'>{$main->lang['default_value']}:</td><td class='form_input'>".in_radio("default_ck", 'yes', $main->lang['activate'], 'ac', (is_bool($d) AND $d==true)?true:false)." ".in_radio("default_ck", 'no', $main->lang['deactivate'], 'deac', (is_bool($d) AND $d==true)?false:true)."</td></tr>\n".
    "<tr class='row_tr' id='case_val'".(($contact_form[$_GET['id']]['type']=='select' OR $contact_form[$_GET['id']]['type']=='radio')?'':" style='display:none;'")."><td class='form_text'>{$main->lang['case_value']}:</td><td class='form_input'>".in_case_val('select', 'input_text', (isset($o) AND is_array($o))?$o:array(), $d)."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['must']}:</td><td class='form_input'>".in_chck("must", "checkbox", is_bool($m)?$m:false)."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>\n".
    "<script type='text/javascript'>select_form_elm(\$\$('type'));</script>";
}

function admin_config_contact(){
global $contact, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $contact['attaching_files_type'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $contact['max_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $contact['max_image_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $contact['attaching_files_size'])."</td></tr>\n".
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function admin_saves_contact(){
global $contact, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_contact.php', '$contact', $contact);
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case "move": admin_moves_contact(); break;
        case "delete_form_row": admin_dels_form_row_contact(); break;
        case "form_add": admin_form_add_contact(); break;
        case "save_elm_form": admin_save_elm_form_contact(); break;
        case "edit_form": admin_edit_form_contact(); break;
        case "config": admin_config_contact(); break;
        case "save_conf": admin_saves_contact(); break;
        default: admin_main_contact(); break;
    }
} elseif($break_load==false) admin_main_contact();
?>