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

$navi = array(
    array('', 'home'),
    array("config", "config")
);

function main_pm(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $result = $main->db->sql_query("SELECT * FROM ".PM_TEXT." AS t, ".PM." AS p WHERE t.tid=p.tid AND p.type=((SELECT COUNT(*) FROM ".PM." WHERE tid=t.tid)-1) ORDER BY t.tid DESC LIMIT {$offset}, 30");
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        $tr = 'row1'; 
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['subj']}</th><th width='100'>{$main->lang['sender']}</th><th width='100'>{$main->lang['recipient']}</th><th width='120'>{$main->lang['date']}</th><th width='100'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($row = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$row['tid']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['tid']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}'><td align='center'>{$i}</td><td>{$row['subj']}</td><td align='center'><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urldecode($row['user'])))."'>{$row['user']}</a></td><td align='center'><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urldecode($row['user_from'])))."'>{$row['user_from']}</a></td><td align='center'>".user_format_date($row['date'])."</td><td align='center'>".($row['pm_read']=='1'?"<span style='color: green;'>{$main->lang['pm_read']}</span>":"<span style='color: red;'>{$main->lang['pm_no_read']}</span>")."</td><td align='center'>{$op}</td></tr>";
            $tr = ($tr=='row1') ? "row2" : "row1"; $i++;
        }
        echo "</table>";
        if ($rows_c==30 OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(t.tid) FROM ".PM_TEXT." AS t, ".PM." AS p WHERE t.tid=p.tid AND p.type=((SELECT COUNT(*) FROM ".PM." WHERE tid=t.tid)-1)"));
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

function edit_pm($msg=""){
global $main, $adminfile, $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".PM_TEXT." AS t LEFT JOIN ".PM." AS p ON(t.tid=p.tid) WHERE t.tid='{$_GET['id']}' AND p.type='1'"));
    if(!empty($msg)) warning($msg);
    $_SESSION['uploaddir'] = file_exists($userconf['directory'].$_GET['id']."/") ? $userconf['directory'].$_GET['id']."/" : $userconf['directory'].USER_FOLDER."/";
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
        "<table class='form' align='center' id='form_{$main->module}'>\n".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['sender']}:</td><td class='form_input'><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urlencode($info['user'])))."'>{$info['user']}</a></td></tr>\n".        
        "<tr class='row_tr'><td class='form_text'>{$main->lang['recipient']}:</td><td class='form_input'><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urlencode($info['user_from'])))."'>{$info['user_from']}</a></td></tr>\n".        
        "<tr class='row_tr'><td class='form_text'>{$main->lang['subj']}:<span class='star'>*</span></td><td class='form_input'>".in_text('subj', 'input_text2', $info['subj'])."</td></tr>\n".        
        "<tr class='row_tr'><td class='form_text'>{$main->lang['message']}:<span class='star'>*</span></td><td class='form_input'>".editor('message', 12, '500px', bb($info['text'], DECODE))."</td></tr>\n".        
        "<tr><td>".in_hide('attache_page', "{$adminfile}?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />"."</td><td align='right'>".send_button()."</td></tr>".
        "</table>\n</form>\n";
}

function attache_page_pm(){
global $main, $userconf, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $_SESSION['uploaddir'], true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($_SESSION['uploaddir'])."</div>".SWFUpload("index.php?module=account&amp;do=pm&op=upload", $userconf['attaching_files_type'], $userconf['attaching_files_size'], $userconf['file_upload_limit'])."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    exit;
}

function save_edit_pm(){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('message', 'subj'), array('error_message', 'subj_err'));  
    if(empty($msg)){
        if(rename_attach($userconf['directory'].USER_FOLDER."/", $userconf['directory'].$_GET['id']."/")) $_POST['message'] = str_replace(USER_FOLDER, $_GET['id'], $_POST['message']);
        sql_update(array('text' => bb($_POST['message'])), PM_TEXT, "tid='{$_GET['id']}'");
        sql_update(array('subj' => $_POST['subj']), PM, "tid='{$_GET['id']}'");
        redirect(MODULE);
    } else edit_pm($msg);
}

function config_pm(){
global $userconf, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $userconf['directory'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $userconf['attaching_files_type'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_width']}</b>:<br /><i>{$main->lang['miniature_image_width_d']}</i></td><td class='form_input2'>".in_text('miniature_image_width', 'input_text2', $userconf['miniature_image_width'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_height']}</b>:<br /><i>{$main->lang['miniature_image_height_d']}</i></td><td class='form_input2'>".in_text('miniature_image_height', 'input_text2', $userconf['miniature_image_height'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $userconf['max_image_width'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $userconf['max_image_height'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $userconf['attaching_files_size'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching']}</b>:<br /><i>{$main->lang['attachingpm_d']}</i></td><td class='form_input2'>".in_chck('attaching', 'checkbox', $userconf['attaching'])."</td></tr>\n".        
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_config_pm(){
global $userconf, $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_user.php', '$userconf', $userconf);
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function delete_admin_pm(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".PM_TEXT." WHERE tid='{$_GET['id']}'");
    $main->db->sql_query("DELETE FROM ".PM." WHERE tid='{$_GET['id']}'");
    if(is_ajax()) main_pm(); else redirect(MODULE);
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case "edit": edit_pm(); break;
        case "save_edit": save_edit_pm(); break;
        case "config": config_pm(); break;
        case "delete": delete_admin_pm(); break;
        case "save_conf": save_config_pm(); break;
        case "attache_page": attache_page_pm(); break;
        default: main_pm(); break;
    }
} elseif($break_load==false) main_pm();
?>