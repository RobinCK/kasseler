<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!"); 

global $main, $break_load;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

function main_comments(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $result = $main->db->sql_query("SELECT * FROM ".COMMENTS." ORDER BY cid DESC LIMIT {$offset}, 30");
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        $tr = 'row1';
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['comment']}</th><th width='100'>IP</th><th width='90'>{$main->lang['user']}</th><th width='120'>{$main->lang['date']}</th><th width='80'>{$main->lang['module']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($row = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$row['cid']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['cid']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}'><td align='center'>{$i}</td><td>".cut_text(strip_tags($row['comment']), 4)."</td><td align='center'>{$row['ip']}</td><td align='center'><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urldecode($row['name'])))."'>{$row['name']}</a></td><td align='center'>".user_format_date($row['date'])."</td><td align='center'><a href='".$main->url(array('module' => $row['modul']))."'>".(!empty($main->lang[$row['modul']])?$main->lang[$row['modul']]:$row['modul'])."</a></td><td align='center'>{$op}</td></tr>";
            $tr = ($tr=='row1') ? 'row2' : 'row1'; $i++;
        }
        echo "</table>";
        if ($rows_c==30 OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".COMMENTS.""));
            //Если количество больше чем количество на страницу
            if($numrows>30){
                //Открываем стилевую таблицу
                open();                
                //создаем страницы
                pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
                //Закрываем стилевую таблицу
                close();
            }
        }        
    } else info($main->lang['noinfo']);
}

function edit_admin_comment($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".COMMENTS." WHERE cid='{$_GET['id']}'");
    if(!empty($msg)) warning($msg);
    if($main->db->sql_numrows($result)>0){
        $info = $main->db->sql_fetchrow($result);
        echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_comment&amp;id={$_GET['id']}'>\n".
        "<table class='form' align='center' id='form_{$main->module}'>\n".
        "<tr class='row_tr'><td class='form_text'>IP:</td><td class='form_input'>".in_text('ip', 'input_text2', $info['ip'])."</td></tr>\n".        
        "<tr class='row_tr'><td class='form_text'>{$main->lang['user']}:<span class='star'>*</span></td><td class='form_input'>".in_text('name', 'input_text2', $info['name'])."</td></tr>\n".        
        "<tr class='row_tr'><td class='form_text'>{$main->lang['message']}:<span class='star'>*</span></td><td class='form_input'>".editor('comment', 12, '500px', bb($info['comment'], DECODE))."</td></tr>\n".        
        "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
        "</table>\n</form>\n";
    } else redirect(MODULE);
}

function save_edit_comments(){
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('comment', 'name'), array('error_message', 'name_err'));  
    if(empty($msg)){
        sql_update(array(
            'comment'  => bb($_POST['comment']),
            'name'     => $_POST['name'],
            'ip'       => $_POST['ip']
        ), COMMENTS, "cid='{$_GET['id']}'");
        redirect(MODULE);
    } else edit_admin_comment($msg);
}

function delete_admin_comment(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".COMMENTS." WHERE cid='{$_GET['id']}'");        
    if (is_ajax()) main_comments(); else redirect(MODULE);
}
function switch_admin_comments(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "delete": delete_admin_comment(); break;
         case "edit": edit_admin_comment(); break;
         case "save_comment": save_edit_comments(); break;
         default: main_comments(); break;
      }
   } elseif($break_load==false) main_comments();
}
switch_admin_comments();
?>
