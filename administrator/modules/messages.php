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
    array('add', 'add_message'),
    array("save_config", "save"),
    array("back_config", "cancel")
);

function main_messages(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".MESSAGE." ORDER BY pos");
    $count = $main->db->sql_numrows($result);
    if($count>0){        
        $row = "row1";
        echo "<table class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['title']}</th><th width='70'>{$main->lang['position']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($rows = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$rows['id']}", 'ajax_content')."</td></tr></table>";
            $up_down = ($count>1) ? up_down_analizy($rows['pos'], $count, $rows['id'], 'ajax_content') : "";
            echo "<tr class='{$row}".(($rows['status']==0)?"_warn":"")."'><td align='center'>{$rows['pos']}</td><td>{$rows['title']}</td><td align='center'>{$up_down}</td><td class='col' align='center' id='onoff_{$rows['id']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$rows['id']}', 'onoff_{$rows['id']}')\">".($rows['status']==1 ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>";
            $row = ($row=='row1') ? "row2" : "row1";
        }
        echo "</table>";
    } else info($main->lang['noinfo']);
}

function on_off_messages(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT status FROM ".MESSAGE." WHERE id='{$_GET['id']}'"));
    if($active==1){
        $main->db->sql_query("UPDATE ".MESSAGE." SET status='0' WHERE id='{$_GET['id']}'");
        echo $main->lang['off'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".MESSAGE." SET status='1' WHERE id='{$_GET['id']}'");
        echo $main->lang['on'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function dels_messages(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".MESSAGE." WHERE id='{$_GET['id']}'");    
    $result = $main->db->sql_query("SELECT id FROM ".MESSAGE." ORDER BY pos");
    $i = 1;
    while(list($id) = $main->db->sql_fetchrow($result)) {
        $main->db->sql_query("UPDATE ".MESSAGE." SET pos='{$i}' WHERE id='{$id}'");
        $i++;
    }    
    if (is_ajax()) main_messages(); else redirect(MODULE);
}

function moves_messages(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=="up") $next = $_GET['pos']-1; else $next = $_GET['pos']+1;
    list($id_tmp) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id FROM ".MESSAGE." WHERE pos='{$next}'"));
    $main->db->sql_query("UPDATE ".MESSAGE." SET pos='{$_GET['pos']}' WHERE id='{$id_tmp}'");
    $main->db->sql_query("UPDATE ".MESSAGE." SET pos='{$next}' WHERE id='{$_GET['id']}'");
    if (is_ajax()) main_messages(); else redirect(MODULE);
}

function add_messages($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $tpls = array();
    foreach (glob(TEMPLATE_PATH."{$main->config['template']}/message*.tpl") as $filename) {
        $name = basename($filename);
        $tpls[$name] = $name;
    }
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['message']}:<span class='star'>*</span></td><td class='form_input'>".editor('message', 12, '500px')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups(array())."</td></tr>\n".      
    "<tr><td class='form_text'>{$main->lang['template']}:</td><td class='form_input '>".in_sels('tpl', $tpls, 'select2 chzn-search-hide', 'message.tpl')."</td></tr>".
    "<tr><td class='form_text'>{$main->lang['enabled']}:</td><td class='form_input '>".in_chck("active", "input_checkbox", 'on')."</td></tr>".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_messages(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'message'), array('errortitle', 'error_message'));
    if(empty($msg)){       
        $row = $main->db->sql_fetchrow($main->db->sql_query("SELECT MAX(pos) as pos FROM ".MESSAGE.""));
        $group = "";
        if(isset($_POST['groups']) AND is_array($_POST['groups']) AND count($_POST['groups'])>0) foreach ($_POST['groups'] as $value) $group .= $value.",";
        sql_insert(array(
            'title'    => $_POST['title'],
            'content'  => bb($_POST['message']),
            'status'   => (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,
            'groups'   => $group,
            'pos'      => $row['pos']+1,
            'tpl'      => $_POST['tpl'],
        ), MESSAGE);
        redirect(MODULE);
    } else add_messages($msg);
}

function edit_messages($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".MESSAGE." WHERE id='{$_GET['id']}'"));
    $tpls = array();
    foreach (glob(TEMPLATE_PATH."{$main->config['template']}/message*.tpl") as $filename) {
        $name = basename($filename);
        $tpls[$name] = $name;
    }
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $result['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['message']}:<span class='star'>*</span></td><td class='form_input'>".editor('message', 12, '500px', bb($result['content'], DECODE))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups(explode(',', $result['groups']))."</td></tr>\n".      
    "<tr><td class='form_text'>{$main->lang['template']}:</td><td class='form_input '>".in_sels('tpl', $tpls, 'select2', !empty($result['tpl'])?$result['tpl']:'message.tpl')."</td></tr>".
    "<tr><td class='form_text'>{$main->lang['enabled']}:</td><td class='form_input '>".in_chck("active", "input_checkbox", (($result['status']==1)?"on":""))."</td></tr>".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_edit_messages(){
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'message'), array('errortitle', 'error_message'));
    if(empty($msg)){       
        $group = "";
        if(isset($_POST['groups']) AND is_array($_POST['groups']) AND count($_POST['groups'])>0) foreach ($_POST['groups'] as $value) $group .= $value.",";
        sql_update(array(
            'title'    => $_POST['title'],
            'content'  => bb($_POST['message']),
            'status'   => (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,
            'groups'   => $group,
            'tpl'      => $_POST['tpl'],
        ), MESSAGE, "id='{$_GET['id']}'");
        redirect(MODULE);
    } else edit_messages($msg);
}

function saves_config_messages(){
global $main, $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    $config = $copyright_file."\$messages = array(\n";
    $result = $main->db->sql_query("SELECT * FROM ".MESSAGE." ORDER BY pos");
    $main->init_function('dbsave');
    if($main->db->sql_numrows($result)>0){
        while(($row = $main->db->sql_fetchrow($result))){
            $config .= "    array('id' => '{$row['id']}', 'title' => '".dbslashes($row['title'])."', 'content' => '".dbslashes($row['content'])."', 'groups' => '{$row['groups']}', 'status' => '{$row['status']}', 'pos' => '{$row['pos']}', 'tpl' => '{$row['tpl']}'),\n";
        }    
        file_write("includes/config/config_messages.php", mb_substr($config, 0, mb_strlen($config)-2)."\n);\n?".">");
    } else file_write("includes/config/config_messages.php", $config."\n);\n?".">");
    if(!is_ajax()) redirect(MODULE); else main_messages();
}

function back_config_messages(){
global $messages, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".MESSAGE);
    foreach($messages as $arr){
        sql_insert(array(
            'id'        => $arr['id'],
            'title'     => $arr['title'],
            'content'   => $arr['content'],
            'groups'    => $arr['groups'],
            'status'    => $arr['status'],
            'pos'       => $arr['pos']
        ), MESSAGE);
    }
    if(!is_ajax()) redirect(MODULE); else main_messages();
}
function switch_admin_messages(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "on_off" : on_off_messages(); break;
         case "delete" : dels_messages(); break;
         case "move" : moves_messages(); break;
         case "add" : add_messages(); break;
         case "save" : save_messages(); break;
         case "edit" : edit_messages(); break;
         case "save_edit" : save_edit_messages(); break;
         case "save_config" : saves_config_messages(); break;
         case "back_config" : back_config_messages(); break;
         default: main_messages(); break;
      }
   } elseif($break_load==false) main_messages();
}
switch_admin_messages();
?>