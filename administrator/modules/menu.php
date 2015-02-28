<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
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
    array('add', 'add_row_menu'),
    array("save_config", "save"),
    array("back_config", "cancel")
);

function main_menu(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".MENU." ORDER BY pos");
    $count = $main->db->sql_numrows($result);
    if($count>0){
        echo "<table class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['title']}</th><th width='70'>{$main->lang['position']}</th><th width='150'>{$main->lang['class']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        $i = 1;
        $row = "row1";
        while(($rows = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$rows['id']}", 'ajax_content')."</td></tr></table>";
            $up_down = ($count>1) ? up_down_analizy($rows['pos'], $count, $rows['id'], 'ajax_content') : "";
            echo "<tr class='{$row}'><td align='center'>{$i}</td><td>{$rows['title']}</td><td align='center'>{$up_down}</td><td align='center'>".(empty($rows['class'])?"-":$rows['class'])."</td><td align='center'>{$op}</td></tr>";
            $i++;
            $row = ($row=='row1') ? "row2" : "row1";
        }
        echo "</table>";
    } else info($main->lang['noinfo']);
}

function moves_menu(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=="up") $next = $_GET['pos']-1; else $next = $_GET['pos']+1;    
    list($id_tmp) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id FROM ".MENU." WHERE pos='{$next}'"));
    $main->db->sql_query("UPDATE ".MENU." SET pos='{$_GET['pos']}' WHERE id='{$id_tmp}'");
    $main->db->sql_query("UPDATE ".MENU." SET pos='{$next}' WHERE id='{$_GET['id']}'");
    if (is_ajax()) main_menu(); else redirect(MODULE);
}

function dels_menu(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".MENU." WHERE id='{$_GET['id']}'");
    $result = $main->db->sql_query("SELECT id FROM ".MENU." ORDER BY pos");
    $i = 1;
    while(list($id) = $main->db->sql_fetchrow($result)) {
        $main->db->sql_query("UPDATE ".MENU." SET pos='{$i}' WHERE id='{$id}'");
        $i++;
    }    
    if (is_ajax()) main_menu(); else redirect(MODULE);
}

function add_menu($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['module']}:</td><td class='form_input'>".select_modules("", " onchange=\"$$('url').value = '".$main->url(array('module' => ''))."'.replace('{$main->config['file_rewrite']}', '')+this.value+((".(($main->mod_rewrite)?"true":"false").")?'{$main->config['file_rewrite']}':'')\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['url']}:<span class='star'>*</span></td><td class='form_input'>".in_text("url", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['class']}:</td><td class='form_input'>".in_text("class", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups(array())."</td></tr>\n".      
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_menu(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'url'), array('errortitle', 'errorurl'));
    if(empty($msg)){       
        $row = $main->db->sql_fetchrow($main->db->sql_query("SELECT MAX(pos) as pos FROM ".MENU.""));
        $group = "";
        if(isset($_POST['groups']) AND is_array($_POST['groups']) AND count($_POST['groups'])>0) foreach ($_POST['groups'] as $value) $group .= $value.",";
        sql_insert(array(
            'title'    => $_POST['title'],
            'url'      => $_POST['url'],
            'groups'   => $group,
            'class'    => $_POST['class'],
            'pos'      => $row['pos']+1
        ), MENU);
        redirect(MODULE);
    } else add_menu($msg);
}

function edit_menu($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $row = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".MENU." WHERE id='{$_GET['id']}'"));
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $row['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['module']}:</td><td class='form_input'>".select_modules("", " onchange=\"$$('url').value = '".$main->url(array('module' => ''))."'.replace('{$main->config['file_rewrite']}', '')+this.value+((".(($main->mod_rewrite)?"true":"false").")?'{$main->config['file_rewrite']}':'')\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['url']}:<span class='star'>*</span></td><td class='form_input'>".in_text("url", "input_text2", $row['url'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['class']}:</td><td class='form_input'>".in_text("class", "input_text2", $row['class'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups(explode(',', $row['groups']))."</td></tr>\n".      
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_edit_menu(){
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'url'), array('errortitle', 'errorurl'));
    if(empty($msg)){       
        $group = "";
        if(isset($_POST['groups']) AND is_array($_POST['groups']) AND count($_POST['groups'])>0) foreach ($_POST['groups'] as $value) $group .= $value.",";
        sql_update(array(
            'title'    => $_POST['title'],
            'url'      => $_POST['url'],
            'groups'    => $group,
            'class'    => $_POST['class']
        ), MENU, "id='{$_GET['id']}'");
        redirect(MODULE);
    } else edit_menu($msg);
}

function saves_config_menu(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $config = "<?php\n/**********************************************/\n/* Kasseler CMS: Content Management System    */\n/**********************************************/\n/*                                            */\n/* Copyright (c)2007-2010 by Igor Ognichenko  */\n/* http://www.kasseler-cms.net/               */\n/*                                            */\n/**********************************************/\n/*                                            */\n/* FileName: config_menu.php                */\n/* Description: Configure menu              */\n/*                                            */\n/**********************************************/\nif (!defined('FUNC_FILE')) die('Access is limited');\n\n$"."menu_config = array(\n";
    $result = $main->db->sql_query("SELECT * FROM ".MENU." ORDER BY pos");
    if($main->db->sql_numrows($result)>0){
        while(($row = $main->db->sql_fetchrow($result))){
            $config .= "\tarray('id' => '{$row['id']}', 'title' => '".addslashes($row['title'])."', 'url' => '{$row['url']}', 'groups' => '{$row['groups']}', 'class' => '{$row['class']}', 'pos' => '{$row['pos']}'),\n";
        }
        file_write("includes/config/config_menu.php", mb_substr($config, 0, mb_strlen($config)-2)."\n);\n?".">");
    } else file_write("includes/config/config_menu.php", $config."\n);\n?".">");
    if(!is_ajax()) redirect(MODULE); else main_menu();
}

function back_config_menu(){
global $menu_config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".MENU);
    foreach($menu_config as $arr){
        sql_insert(array(
            'id'        => $arr['id'],
            'title'     => $arr['title'],
            'url'       => $arr['url'],
            'groups'    => $arr['groups'],
            'class'     => $arr['class'],
            'pos'       => $arr['pos']
        ), MENU);
    }
    if(!is_ajax()) redirect(MODULE); else main_menu();
}
function switch_admin_menu(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "move" : moves_menu(); break;
         case "add" : add_menu(); break;
         case "save" : save_menu(); break;
         case "edit" : edit_menu(); break;
         case "save_edit" : save_edit_menu(); break;
         case "delete" : dels_menu(); break;
         case "save_config" : saves_config_menu(); break;
         case "back_config" : back_config_menu(); break;
         default: main_menu(); break;
      }
   } elseif($break_load==false) main_menu();
}
switch_admin_menu();
?>