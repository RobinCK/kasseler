<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

function global_add_topsite($msg=""){
global $main, $topsite, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    open();
    echo "<form enctype='multipart/form-data' method='post' action='".((!defined("ADMIN_FILE")) ? $main->url(array('module' => $main->module, 'do' => 'save')) : "{$adminfile}?module={$main->module}&amp;do=save")."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_amail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("email", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".editor("descript", 10, "97%")."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_url']}:<span class='star'>*</span></td><td class='form_input'>".in_text("link", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['site_preview']}:<br /><i>{$main->lang['width']}: {$topsite['privew_width']}; {$main->lang['height']}: {$topsite['privew_height']};</i></td><td class='form_input'><input type='file' name='file_cover' size='44' /></td></tr>\n".
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date(gmdate("Y-m-d"), "Y-m-d"))."</td></tr>\n":"").
    "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>".
    (!defined("ADMIN_FILE")?captcha():"")."<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";    
    close();
}

function global_save_topsite(){
global $main, $topsite, $navi;
    if(hook_check(__FUNCTION__)) return hook();
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
        $result = $main->db->sql_query("SELECT title FROM ".TOPSITES." WHERE title='{$_POST['title']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
        $img = "";
        if(empty($msg)){
            if(isset($_FILES["file_cover"]) AND !empty($_FILES["file_cover"]['name'])){
                main::init_class('uploader');
                $attach = new upload(array(
                    'dir'    => $topsite['directory'],
                    'file'   => $_FILES["file_cover"],
                    'size'   => $topsite['privew_size'],
                    'type'   => explode(",", $topsite['privew_type']),
                    'name'   => $site,
                    'width'  => $topsite['privew_width'], 
                    'height' => $topsite['privew_height']
                ));
                if(!$attach->error) $img = $attach->file;
                else $msg = $attach->get_error_msg();
            }
        }
    }       
    if(empty($msg)){
        sql_insert(array(
            'title'         => $_POST['title'],
            'link'          => "http://".$site,
            'img'           => $img,
            'mail'          => $_POST['email'],
            'date'          => kr_dateuser2db(),
            'status'        => (is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,                        
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",            
            'description'   => bb($_POST['descript'])
        ), TOPSITES);
        if(!defined("ADMIN_FILE")){
            echo $navi;
            info($main->lang['info_add_site']);
            open();            
            echo "<h3>{$main->lang['link']}</h3><center>".in_area('link_site', 'textarea', 3, "<a href='{$main->config['http_home_url']}ref={$site}' title='{$main->config['description']}'>{$main->config['home_title']}</a>")."</center><br /><h3>{$main->lang['baner']}</h3><center>".in_area('baner_site', 'textarea', 3, "<a href='{$main->config['http_home_url']}?ref={$site}' title='{$main->config['description']}'><img src='{$topsite['baner_url']}' alt='{$main->config['home_title']}' title='{$main->config['description']}' /></a>")."</center><br />";
            close();
        } else redirect(MODULE);
    } else global_add_topsite($msg);
}
?>