<?php
if(!defined('ADMIN_FILE')) die("Hacking attempt!");

global $main, $adminfile, $session, $template, $security; 

if(!isset($_SESSION['admin'])){    
    if($security['captcha']==ENABLED) {
        define('ADMIN_FILE_C', true);
    }   
    if(isset($_POST['login'])){
        //Поиск пользователя
        $result = $main->db->sql_query("SELECT user_name, user_password, user_level FROM ".USERS." WHERE user_name='".addslashes($_POST['login'])."'");            
        if($main->db->sql_numrows($result)>0){                
            $info = $main->db->sql_fetchrow($result);
            //Проверка пароля и права администрирования
            $msg = check_captcha();
            if($info['user_password']==pass_crypt($_POST['password']) AND $info['user_level']>0 AND empty($msg)) {
                //Создание сессии администратора
                $_SESSION['admin'] = $main->user['user_name'];
                setcookies($info['user_name'].",".$info['user_password'], $main->config['admin_cookies']);
                if(!is_user()){
                    if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
                    if(isset($_SESSION['user'])) unset($_SESSION['user']);
                    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
                    setcookies("", $main->config['user_cookies'], 1);
                    setcookies("", "update_session", 1);
                    setcookies("", "online", 1);
                    //Если не создана сессия пользователя то создаем ее
                    setcookies($info['user_name'].",".$info['user_password'], $main->config['user_cookies']);
                    //Обновляем информацию о пользователе
                    $main->db->sql_query("UPDATE ".USERS." SET user_last_os='".kr_filter($main->agent['os'], TAGS)."', user_last_browser='".kr_filter($main->agent['browser'], TAGS)."', user_last_ip='{$main->ip}', user_last_visit=NOW() WHERE user_name='{$info['user_name']}'");
                    if($main->db->sql_numrows($main->db->sql_query("SELECT uname FROM ".SESSIONS." WHERE uname='{$info['user_name']}'"))==0) $main->db->sql_query("UPDATE ".SESSIONS." SET uname='{$info['user_name']}', actives='y' WHERE sid='".session_id()."'");
                    //Регистрируем сессию
                    $_SESSION['user'] = $main->user['user_name'];
                    $session->register($info['user_name']);
                }
                redirect($adminfile);
            }
        }    
    } 
    if(!isset($_SESSION['admin']) AND !defined("INSTALLCMS")){
        //Добавляем заголовок на страницу
        add_meta_value($main->lang['admin_login']);
        //Заменяем переменные шаблона
        $form = "<form action='{$adminfile}' method='post'>
        <table width='100%' class='form'><tr class='row_tr'><td width='80' align='center'><img src='".TEMPLATE_PATH."{$main->tpl}/images/mini_logo.png' alt='Kasseler CMS' /></td><td><table width='100%'>
        <tr><td class='form_text' style='width: 50px;'>{$main->lang['login']}: </td><td class='form_input'><input class='input_login' type='text' name='login' style='width: 100%;' value='' /></td></tr>
        <tr><td class='form_text' style='width: 50px;'>{$main->lang['password']}: </td><td class='form_input'><input class='input_login' type='password' name='password' style='width: 100%;' value='' /></td></tr>".($security['captcha']==ENABLED?captcha():'').
        "</table></td></tr><tr><td colspan='2' align='center'><input type='submit' value='{$main->lang['account_login']}' /></td></tr></table>
        <input type='hidden' name='logined' value='true' /></form>";
        $template->set_tpl(array(
            'login_title'  => $main->lang['admin_login'],
            'login_title'  => $main->lang['admin_login'],
            'content'      => $form,
        ), 'index');
    }
}
?>