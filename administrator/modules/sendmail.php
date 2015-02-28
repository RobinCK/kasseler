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

function main_sendmail($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    main::init_function('attache');
    $_SESSION['uploaddir'] = "uploads/sendmail/";
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=send'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text' style='text-align:left;'><b>{$main->lang['title']}</b>:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text' style='text-align:left;'><b>{$main->lang['message']}</b>:<span class='star'>*</span><br /><div><i>{$main->lang['desc_send_meil']}</i></div></td><td class='form_input'>".in_area("message", 'textarea', '8')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text' style='text-align:left;'><b>{$main->lang['groups_sends']}</b>:<span class='star'>*</span><br /><div><i>{$main->lang['desc_case_group']}</i></div></td><td class='form_input'>".get_groups(array(0), '', true, $main->lang['all'])."</td></tr>\n".
    "<tr><td>".in_hide('attache_page', "{$adminfile}?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />"."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>\n";
}

function attache_page_sendmail(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $_SESSION['uploaddir'], true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($_SESSION['uploaddir'])."</div>".SWFUpload("{$adminfile}?module={$main->module}&amp;do=upload", "*.*", 102400, 100)."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    kr_exit();
}

function send(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    //Проверяем поля
    $msg = error_empty(array('title', 'message'), array('title_mess_err', 'message_err'));
    if(empty($msg)){
        $att = array();
        //Проверяем наличие прикрепленных файлов
        if(dir_file_count("uploads/sendmail/")>0){
            //Добавляем файлы к сообщению
            if(($tmpHandle = opendir("uploads/sendmail/"))){
                while(($tmpFile = readdir($tmpHandle))){
                    if(is_file("uploads/sendmail/".$tmpFile)) $att[] = "uploads/sendmail/{$tmpFile}";
                }
                closedir($tmpHandle);
            }
        } 
        //Делаем выборку пользователей
        if(isset($_POST['groups']) AND count($_POST['groups'])>0 AND $_POST['groups'][0]!=0) $result = $main->db->sql_query("SELECT user_name, user_email, user_group FROM ".USERS." WHERE user_group IN(".implode(",", $_POST['groups']).")");
        else $result = $main->db->sql_query("SELECT user_name, user_email, user_group FROM ".USERS." WHERE user_name<>'Guest'");
        //Проверяем метод отправки сообщений
        if($main->config['bcc_send']!=ENABLED){
            //Перебираем выбранных пользователей
            while(($row=$main->db->sql_fetchrow($result))){
                //Тело сообщения
                $message = preg_replace(
                    array('/\{USER\}/i', '/\{SITE\}/i', '/\{EMAIL\}/i'),
                    array($row['user_name'], "<a href='{$main->config['http_home_url']}'>{$main->config['site_name_for_mail']}</a>", "<a href='mailto:{$row['user_email']}'>{$row['user_email']}</a>"),
                    $_POST['message']
                );
                send_mail($row['user_email'], $row['user_name'], $main->config['sends_mail'], 'noreply', $_POST['title'], $message, array(), $att);
            }
        } else {
            //Инициализируем нужные нам переменные
            $i = 0;
            //Скрытая копия получателей
            $bbc = array();
            //Header сообщения
            //Перебираем выбранных пользователей
            while(($row = $main->db->sql_fetchrow($result))){
                //Тело сообщения
                $message = preg_replace(
                    array('/\{USER\}/i', '/\{SITE\}/i', '/\{EMAIL\}/i'),
                    array('', "<a href='{$main->config['http_home_url']}'>{$main->config['site_name_for_mail']}</a>", ''),
                    $_POST['message']
                );
                if($i==500){
                    //Если количество пользователей достигло 500 выполняем отправку
                    send_mail($main->config['admin_mail'], 'Admin', $main->config['sends_mail'], 'noreply', $_POST['title'], $message, array(), $att, $bbc);
                    $bbc = array();
                    $i = 0;
                } else $bbc[] = array('mail' => $row['user_email'], 'name' => $row['user_name']);
                $i++;
            }        
            //Отправляем всех которые не достигли 500
            if($i>=1 AND !empty($bbc)) send_mail($main->config['admin_mail'], 'Admin', $main->config['sends_mail'], 'noreply', $_POST['title'], $message, array(), $att, $bbc);
        }
        //Удаляем файлы
        if(file_exists("uploads/sendmail/")) remove_dir("uploads/sendmail/");
        redirect(MODULE);
    } else main_sendmail($msg);
}

function upload_mail(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('uploader');
    if(isset($_FILES["Filedata"])){
        if(!file_exists("uploads/sendmail/")) mkdir("uploads/sendmail/", 0777);
        $new_name = get_name_file(cyr2lat($_FILES["Filedata"]['name'],true));
        $atrib = array(
            'dir'   => "uploads/sendmail/",
            'file'  => $_FILES["Filedata"],
            'size'  => 102400,
            'type'  => array(get_type_file($_FILES["Filedata"]['name'])),
            'name'  => $new_name
        );
        $attach = new upload($atrib); 
        if($attach->error) header("HTTP/1.1 50{$attach->error_number} File Upload Error");
    } else header("HTTP/1.1 500 File Upload Error");
    return true;
}
function switch_admin_sendmail(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "send": send(); break;
         case "upload": upload_mail(); break;
         case "attache_page": attache_page_sendmail(); break;
         default: main_sendmail(); break;
      }
   } elseif($break_load==false) main_sendmail();
}
switch_admin_sendmail();
?>