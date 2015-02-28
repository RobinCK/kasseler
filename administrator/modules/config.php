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
    array('', 'total'),
    array('messages', 'confmail'),
    array('patterns', 'patterns_text'),
    array('smiles', 'smiles'),
    array('editor', 'editor_conf'),
    array('editor_tiny_mce','TinyMCE'),
    array('further', 'further'),
    array('security', 'security')
);

function main_config(){
global $main, $adminfile, $version_sys, $license_sys; 
    if(hook_check(__FUNCTION__)) return hook();
    $dir = opendir('modules/');
    while(($file = readdir($dir))) if(is_dir('modules/'.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $sel[$file] = isset($main->lang[$file]) ? $main->lang[$file]:$file;
    closedir($dir);
    $modules = in_sels('module', $sel, 'select', explode(',', $main->config['default_module']), '', true, 10);
    main::add2script("$.krReady(function(){jQuery('.textareaA').autoResize();})", false);
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save' method='post'>".in_hide('cms_version', $version_sys)."<table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['home_title']}</b>:<br /><i>{$main->lang['home_title_d']}</i></td><td class='form_input2'>".in_text('home_title', 'input_text2', $main->config['home_title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['http_home_url']}</b>:<br /><i>{$main->lang['http_home_url_d']}</i></td><td class='form_input2'>".in_text('http_home_url', 'input_text2', $main->config['http_home_url'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sitelogo']}</b>:<br /><i>{$main->lang['sitelogo_d']}</i></td><td class='form_input2'>".in_text('sitelogo', 'input_text2', $main->config['sitelogo'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['charset']}</b>:<br /><i>{$main->lang['charset_d']}</i></td><td class='form_input2'>".in_text('charset', 'input_text2', $main->config['charset'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['description']}</b>:<br /><i>{$main->lang['description_d']}</i></td><td class='form_input2'>".in_area('description', 'textarea textareaA', 1, $main->config['description'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['keywords']}</b>:<br /><i>{$main->lang['keywords_d']}</i></td><td class='form_input2'>".in_area('keywords', 'textarea textareaA', 1, $main->config['keywords'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['user_cookies']}</b>:<br /><i>{$main->lang['admin_cookies_d']}</i></td><td class='form_input2'>".in_text('user_cookies', 'input_text2', $main->config['user_cookies'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['admin_cookies']}</b>:<br /><i>{$main->lang['user_cookies_d']}</i></td><td class='form_input2'>".in_text('admin_cookies', 'input_text2', $main->config['admin_cookies'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['GMT_correct']}</b>:<br /><i>{$main->lang['GMT_correct_d']}</i></td><td class='form_input2'>".in_text('GMT_correct', 'input_text2', $main->config['GMT_correct'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rewrite']}</b>:<br /><i>{$main->lang['rewrite_d']}</i><br />".($license_sys=='FREE'?$main->lang['isnotsupported']:'')."</td><td class='form_input2'>".in_chck('rewrite', 'input_checkbox', $main->config['rewrite'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['separator_rewrite']}</b>:<br /><i>{$main->lang['separator_rewrite_d']}</i><br />".($license_sys=='FREE'?$main->lang['isnotsupported']:'')."</td><td class='form_input2'>".in_text('separator_rewrite', 'input_text2', $main->config['separator_rewrite'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['file_rewrite']}</b>:<br /><i>{$main->lang['file_rewrite_d']}</i><br />".($license_sys=='FREE'?$main->lang['isnotsupported']:'')."</td><td class='form_input2'>".in_text('file_rewrite', 'input_text2', $main->config['file_rewrite'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['separator']}</b>:<br /><i>{$main->lang['separator_d']}</i></td><td class='form_input2'>".in_text('separator', 'input_text2', $main->config['separator'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['multilanguage']}</b>:<br /><i>{$main->lang['multilanguage_d']}</i></td><td class='form_input2'>".in_chck('multilanguage', 'input_checkbox', $main->config['multilanguage'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['gz']}</b>:<br /><i>{$main->lang['gz_d']}</i></td><td class='form_input2'>".in_chck('gz', 'input_checkbox', $main->config['gz'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['gzlevel']}</b>:<br /><i>{$main->lang['gzlevel_d']}</i></td><td class='form_input2'>".in_text('gzlevel', 'input_text2', $main->config['gzlevel'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['default_module']}</b>:<br /><i>{$main->lang['default_module_d']}</i></td><td class='form_input2'>{$modules}</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['adm_language']}</b>:<br /><i>{$main->lang['language_d']}</i></td><td class='form_input2'>".get_lang_file($main->config['language'], false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['template']}</b>:<br /><i>{$main->lang['template_d']}</i></td><td class='form_input2'>".select_template($main->config['template'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['case_template']}</b>:<br /><i>{$main->lang['case_template_d']}</i></td><td class='form_input2'>".in_chck('case_template', 'input_checkbox', $main->config['case_template'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['disable_site']}</b>:<br /><i>{$main->lang['disable_site_d']}</i></td><td class='form_input2'>".in_chck('disable_site', 'input_checkbox', $main->config['disable_site'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['disable_description']}</b>:<br /><i>{$main->lang['disable_description_d']}</i></td><td class='form_input2'>".in_area('disable_description', 'textarea', 8, stripslashes($main->config['disable_description']))."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function saves_config(){
global $config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['module'])) {
        $_POST['default_module'] = implode(',', $_POST['module']);
        if(preg_match('/(.*?)forum(.*?)/i', $_POST['default_module'])) $_POST['default_module'] = 'forum';
    }
    main::init_function('sources');
    if(!isset($config['ratings'])) $config['ratings'] = ENABLED;
    if(!isset($config['method_br'])) $config['method_br']='0';
    if(!isset($config['module_br'])) $config['module_br']='';
    if(!isset($config['captcha_free']) OR (isset($_POST['set_captcha_free']) AND empty($_POST['captcha_free']))) $config['captcha_free']='';
    if(!isset($config['xhtmleditor_g']) OR (isset($config['xhtmleditor_g']) AND empty($_POST['xhtmleditor_g']) AND isset($_POST['set_xhtmleditor']))) $config['xhtmleditor_g']='';
    save_config('config.php', '$config', $config);
    redirect(BACK);
}

function smiles(){
global $smiles, $lang, $adminfile, $main;    
    if(hook_check(__FUNCTION__)) return hook();
    echo "<table cellspacing='1' class='table' width='100%'><tr><th>{$lang['smile_code']}</th><th width='100'>{$lang['smile']}</th><th width='70'>{$lang['functions']}</th><th>{$lang['smile_code']}</th><th width='100'>{$lang['smile']}</th><th width='70'>{$lang['functions']}</th></tr>";
    $row = "row1";
    for($i=0;$i<count($smiles);$i+=2){
        if(isset($smiles[$i]) AND isset($smiles[$i+1])){
            $sm = htmlspecialchars($smiles[$i][0], ENT_QUOTES);
            $sm1 = htmlspecialchars($smiles[$i+1][0], ENT_QUOTES);
            $op1 = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit_smiles&amp;id={$i}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete_smiles&amp;id={$i}", 'ajax_content')."</td></tr></table>";
            $op2 = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit_smiles&amp;id=".($i+1)."").delete_button("{$adminfile}?module={$main->module}&amp;do=delete_smiles&amp;id=".($i+1), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}'>".
            "<td align='center'>{$sm}</td><td align='center'><img src='{$smiles[$i][1]}' alt='{$sm}' title='{$sm}' /></td><td align='center'>{$op1}</td>".
            "<td align='center'>{$sm1}</td><td align='center'><img src='{$smiles[$i+1][1]}' alt='{$sm1}' title='{$sm1}' /></td><td align='center'>{$op2}</td>".
            "</tr>";
        } else {
            $sm = htmlspecialchars($smiles[$i][0], ENT_QUOTES);
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit_smiles&amp;id={$i}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete_smiles&amp;id={$i}", 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}'>".
            "<td align='center'>{$sm}</td><td align='center'><img src='{$smiles[$i][1]}' alt='{$sm}' title='{$sm}' /></td><td align='center'>{$op}</td>".
            "<td colspan='3'>&nbsp;</td>".
            "</tr>";
        }
        $row = ($row=="row1") ? "row2" : "row1";
    }
    echo "</table>";
    echo "<br /><form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_smiles' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['image']}</b>:<br /></td><td class='form_input2 pixel'></td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['text_smile']}</b>:<br /></td><td class='form_input2'>".in_text('smile_text', 'input_text2', "", false, "onkeyup=\"$$('update_smile').alt=this.value; $$('update_smile').title=this.value;\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['url_smile']}</b>:<br /></td><td class='form_input2'>".in_text('smile_url', 'input_text2', "", false, "onkeyup=\"$$('update_smile').src=this.value;\"")."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function edit_smiles(){
global $smiles, $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $sm = htmlspecialchars($smiles[$_GET['id']][0], ENT_QUOTES);
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_smiles&amp;id={$_GET['id']}' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['image']}</b>:<br /></td><td class='form_input2'><img id='update_smile' src='{$smiles[$_GET['id']][1]}' alt='{$sm}' title='{$sm}' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['text_smile']}</b>:<br /></td><td class='form_input2'>".in_text('smile_text', 'input_text2', $sm, false, "onkeyup=\"$$('update_smile').alt=this.value; $$('update_smile').title=this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['url_smile']}</b>:<br /></td><td class='form_input2'>".in_text('smile_url', 'input_text2', $smiles[$_GET['id']][1], false, "onkeyup=\"$$('update_smile').src=this.value\"")."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_smiles(){
global $smiles, $adminfile, $main, $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_GET['id'])) $smiles[$_GET['id']] = array($_POST['smile_text'], $_POST['smile_url']);
    else $smiles[] = array($_POST['smile_text'], $_POST['smile_url']); 
    $file = fopen("includes/config/config_smiles.php", "w");
    fputs ($file, $copyright_file.arr2str($smiles, 'smiles')."\n?".">");
    fclose ($file);
    redirect("{$adminfile}?module={$main->module}&do=smiles");
}

function delete_smiles(){
global $smiles, $adminfile, $main, $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    unset($smiles[$_GET['id']]);
    $tmp = array();
    foreach($smiles as $value) $tmp[] = $value;
    $smiles = $tmp;
    $file = fopen("includes/config/config_smiles.php", "w");
    fputs ($file, $copyright_file.arr2str($smiles, 'smiles')."\n?".">");
    fclose ($file);
    redirect("{$adminfile}?module={$main->module}&do=smiles");
}

function further(){
global $main, $adminfile, $config, $license_sys;
    if(hook_check(__FUNCTION__)) return hook();    
    main::init_function('modules');
    $modlist=list_modules();
    $val_module_br=isset($config['module_br'])?explode(',',$config['module_br']):array('');
    $type_br=array(0=>$main->lang['method_br1'],1=>$main->lang['method_br2']);
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['time_of_life_session']}</b>:<br /><i>{$main->lang['time_of_life_session_d']}</i></td><td class='form_input2'>".in_text('time_of_life_session', 'input_text2', $config['time_of_life_session'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['time_online']}</b>:<br /><i>{$main->lang['time_online_d']}</i></td><td class='form_input2'>".in_text('time_online', 'input_text2', $config['time_online'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['interval_session_update']}</b>:<br /><i>{$main->lang['interval_session_update_d']}</i></td><td class='form_input2'>".in_text('interval_session_update', 'input_text2', $config['interval_session_update'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['date_format']}</b>:<br /><i>{$main->lang['date_format_d']}</i></td><td class='form_input2'>".in_text('date_format', 'input_text2', $config['date_format'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['classes_links']}</b>:<br /><i>{$main->lang['classes_links_d']}</i><br />".($license_sys=='FREE'?$main->lang['isnotsupported']:'')."</td><td class='form_input2'>".in_text('classes_links', 'input_text2', $config['classes_links'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['whois_servoce']}</b>:<br /><i>{$main->lang['whois_servoce_d']}</i></td><td class='form_input2'>".in_text('whois', 'input_text2', $config['whois'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ajaxload']}</b>:<br /><i>{$main->lang['ajaxload_d']}</i><br />".($license_sys=='FREE'?$main->lang['isnotsupported']:'')."</td><td class='form_input2'>".in_chck('ajaxload', 'input_checkbox', $config['ajaxload'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['geoip']}</b>:<br /><i>{$main->lang['geoip_d']}</i></td><td class='form_input2'>".in_chck('geoip', 'input_checkbox', $config['geoip'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['user_agent_full']}</b>:<br /><i>{$main->lang['user_agent_full_d']}</i></td><td class='form_input2'>".in_chck('user_agent_full', 'input_checkbox', $config['user_agent_full'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['download_resume']}</b>:<br /><i>{$main->lang['download_resume_d']}</i></td><td class='form_input2'>".in_chck('download_resume', 'input_checkbox', $config['download_resume'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['download_speed']}</b>:<br /><i>{$main->lang['download_speed_d']}</i></td><td class='form_input2'>".in_text('download_speed', 'input_text2', $config['download_speed'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['jquery_conf']}</b>:<br /><i>{$main->lang['jquery_conf_d']}</i></td><td class='form_input2'>".in_sels('jquery', array('local' => $main->lang['loadlocal'], 'google' => 'Google', 'yandex' => 'Yandex', 'jquery' => 'jQuery'), 'select2 chzn-search-hide', $config['jquery'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['pdaversion']}</b>:<br /><i>{$main->lang['pdaversion_d']}</i></td><td class='form_input2'>".in_chck('pdaversion', 'input_checkbox', $config['pdaversion'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_d']}</i></td><td class='form_input2'>".in_chck('ratings', 'input_checkbox', !isset($config['ratings'])?ENABLED:$config['ratings'])."</td></tr>\n".
    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['method_br']}</b>:<br /><i>{$main->lang['method_br_d']}</i></td><td class='form_input2'>".in_sels('method_br', $type_br, 'select2 chzn-search-hide', isset($config['method_br'])?$config['method_br']:0)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['module_br']}</b>:<br /><i>{$main->lang['module_br_d']}</i></td><td class='form_input2'>".in_sels('module_br', $modlist, 'select2', $val_module_br,'',true,10)."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function messages(){
global $main, $adminfile, $config;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    /////
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['site_name_for_mail']}</b>:<br /><i>{$main->lang['site_name_for_mail_d']}</i></td><td class='form_input2'>".in_text('site_name_for_mail', 'input_text2', $config['site_name_for_mail'])."</td></tr>\n".
    /////
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['admin_mail']}</b>:<br /><i>{$main->lang['admin_mail_d']}</i></td><td class='form_input2'>".in_text('admin_mail', 'input_text2', $config['admin_mail'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sends_mail']}</b>:<br /><i>{$main->lang['sends_mail_d']}</i></td><td class='form_input2'>".in_text('sends_mail', 'input_text2', $config['sends_mail'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['contact_mail']}</b>:<br /><i>{$main->lang['contact_mail_d']}</i></td><td class='form_input2'>".in_text('contact_mail', 'input_text2', $config['contact_mail'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['charset_mail']}</b>:<br /><i>{$main->lang['charset_mail_d']}</i></td><td class='form_input2'>".in_text('charset_mail', 'input_text2', $config['charset_mail'])."</td></tr>\n".
    /////
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['template_mail']}</b>:<br /><i>{$main->lang['template_mail_d']}</i></td><td class='form_input2'>".in_text('template_mail', 'input_text2', $config['template_mail'])."</td></tr>\n".
    /////
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['smtp_host']}</b>:<br /><i>{$main->lang['smtp_host_d']}</i></td><td class='form_input2'>".in_text('smtp_host', 'input_text2', $config['smtp_host'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['smtp_port']}</b>:<br /><i>{$main->lang['smtp_port_d']}</i></td><td class='form_input2'>".in_text('smtp_port', 'input_text2', $config['smtp_port'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['smtp_user']}</b>:<br /><i>{$main->lang['smtp_user_d']}</i></td><td class='form_input2'>".in_text('smtp_user', 'input_text2', $config['smtp_user'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['smtp_password']}</b>:<br /><i>{$main->lang['smtp_password_d']}</i></td><td class='form_input2'>".in_pass('smtp_password', 'input_text2', $config['smtp_password'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>SMTP SSL:</b>:<br /><i>{$main->lang['smtp_ssl_d']}</i></td><td class='form_input2'>".in_chck('smtp_ssl', 'input_checkbox', $config['smtp_ssl'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['send_mail']}</b>:<br /><i>{$main->lang['send_mail_d']}</i></td><td class='form_input2'>".in_chck('send_mail', 'input_checkbox', $config['send_mail'])."</td></tr>\n".

    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['bcc_send']}</b>:<br /><i>{$main->lang['bcc_send_d']}</i></td><td class='form_input2'>".in_chck('bcc_send', 'input_checkbox', $config['bcc_send'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mail_is_html']}</b>:<br /><i>{$main->lang['mail_is_html_d']}</i></td><td class='form_input2'>".in_sels('type_emeils', array('text/plain' => 'text/plain', 'text/html' => 'text/html'), 'select2 chzn-search-hide', $config['type_emeils'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['type_email_send']}</b>:<br /><i>{$main->lang['type_email_send_d']}</i></td><td class='form_input2'>".in_sels('type_email_send', array('mail' => 'mail()', 'smtp' => 'smtp', 'qmail' => 'qmail', 'sendmail' => 'sendmail'), 'select2 chzn-search-hide', $config['type_email_send'])."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function editor_conf(){
global $main, $adminfile, $config;
    if(hook_check(__FUNCTION__)) return hook();
    main::add2script("$.krReady(function(){jQuery('.textareaA').autoResize();})", false);
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save' method='post'><table align='center' class='form' id='form_{$main->module}'>".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mark']}</b>:<br /><i>{$main->lang['mark_d']}</i></td><td class='form_input2'>".in_sels('mark', array('0' => $main->lang['off2'], '1' => $main->lang['mark1'], '2' => $main->lang['mark2'], '3' => $main->lang['mark3'], '4' => $main->lang['mark4'], '5' => $main->lang['mark5']), 'select2 chzn-search-hide', $config['mark'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mark_img']}</b>:<br /><i>{$main->lang['mark_img_d']}</i></td><td class='form_input2'>".in_text('mark_img', 'input_text2', $config['mark_img'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['htmlTags']}</b>:<br /><i>{$main->lang['htmlTags_d']}</i></td><td class='form_input2'>".in_area('htmlTags', 'textarea textareaA', 1, $config['htmlTags'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['xhtml_editor']}</b>:<br /><i>{$main->lang['xhtml_editor_d']}</i></td><td class='form_input2'>".get_groups(explode(',', isset($config['xhtmleditor_g'])?$config['xhtmleditor_g']:''),'xhtmleditor_g')."</td></tr>\n".in_hide("set_xhtmleditor",true).
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function patterns_text(){
global $main, $adminfile, $patterns;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_patterns' method='post'><table align='center' class='form' id='form_{$main->module}'>".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_small']}</b>:</td><td class='form_input2'>".in_area('message_registration', 'textarea', 6, $patterns['message_registration'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['pat_activation_user']}</b>:<br /><i>{$main->lang['activation_user_d']}</i></td><td class='form_input2'>".in_area('activation_user', 'textarea', 6, $patterns['activation_user'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['post_report_pat']}</b>:<br /><i>{$main->lang['post_report_pat_d']}</i></td><td class='form_input2'>".in_area('post_report', 'textarea', 6, $patterns['post_report'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['new_pm_pat']}</b>:<br /><i>{$main->lang['new_pm_pat_d']}</i></td><td class='form_input2'>".in_area('new_pm', 'textarea', 6, $patterns['new_pm'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['new_pm_pat2']}</b>:<br /><i>{$main->lang['new_pm_pat_d2']}</i></td><td class='form_input2'>".in_area('your_new_pm_message', 'textarea', 12, $patterns['your_new_pm_message'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['new_password_pat']}</b>:<br /><i>{$main->lang['new_password_pat_d']}</i></td><td class='form_input2'>".in_area('new_password', 'textarea', 6, $patterns['new_password'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['save_newpassword_pat']}</b>:<br /><i>{$main->lang['save_newpassword_pat_d']}</i></td><td class='form_input2'>".in_area('save_newpassword', 'textarea', 6, $patterns['save_newpassword'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['new_forum_message']}</b>:<br /><i>{$main->lang['new_forum_message_d']}</i></td><td class='form_input2'>".in_area('new_post_forum', 'textarea', 6, isset($patterns['new_post_forum'])?$patterns['new_post_forum']:"")."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_patterns(){
global $adminfile, $patterns, $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    $patterns['new_post_forum']=$_POST['new_post_forum'];
    save_config('config_patterns.php', '$patterns', $patterns, true);
    redirect("{$adminfile}?module={$_GET['module']}&do=patterns");
}

function security_config(){
   global $main,$adminfile,$config;
   if(hook_check(__FUNCTION__)) return hook();
    main::init_function('modules');
    $modlist=list_modules();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['variables']}</b>:<br /><i>{$main->lang['variables_d']}</i></td><td class='form_input2'>".in_sels('variables', array('0' => $main->lang['off2'], '2' => $main->lang['onlyadmin'], '1' => $main->lang['allusers']), 'select2 chzn-search-hide', $config['variables'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['query']}</b>:<br /><i>{$main->lang['query_d']}</i></td><td class='form_input2'>".in_sels('query', array('0' => $main->lang['off2'], '2' => $main->lang['onlyadmin'], '1' => $main->lang['allusers']), 'select2 chzn-search-hide', $config['query'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mode_debugging_php']}</b>:<br /><i>{$main->lang['mode_debugging_php_d']}</i></td><td class='form_input2'>".in_chck('mode_debugging_php', 'input_checkbox', $config['mode_debugging_php'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mode_debugging_sql']}</b>:<br /><i>{$main->lang['mode_debugging_sql_d']}</i></td><td class='form_input2'>".in_chck('mode_debugging_sql', 'input_checkbox', $config['mode_debugging_sql'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['mode_debugging_http']}</b>:<br /><i>{$main->lang['mode_debugging_http_d']}</i></td><td class='form_input2'>".in_chck('mode_debugging_http', 'input_checkbox', $config['mode_debugging_http'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['log_debugging_php']}</b>:<br /><i>{$main->lang['log_debugging_php_d']}</i></td><td class='form_input2'>".in_chck('log_debugging_php', 'input_checkbox', $config['log_debugging_php'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['log_debugging_sql']}</b>:<br /><i>{$main->lang['log_debugging_sql_d']}</i></td><td class='form_input2'>".in_chck('log_debugging_sql', 'input_checkbox', $config['log_debugging_sql'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['log_debugging_http']}</b>:<br /><i>{$main->lang['log_debugging_http_d']}</i></td><td class='form_input2'>".in_chck('log_debugging_http', 'input_checkbox', $config['log_debugging_http'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['log_error_user_logined']}</b>:<br /><i>{$main->lang['log_error_user_logined_d']}</i></td><td class='form_input2'>".in_chck('log_error_user_logined', 'input_checkbox', $config['log_error_user_logined'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['captcha_free']}</b>:<br /><i>{$main->lang['captcha_free_d']}</i></td><td class='form_input2'>".get_groups(explode(',', isset($config['captcha_free'])?$config['captcha_free']:''),'captcha_free')."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table>".in_hide("set_captcha_free",true)."</form>";
}
function switch_admin_config(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])AND $break_load==false){
      switch($_GET['do']){
         case "save_patterns": save_patterns(); break;
         case "patterns": patterns_text(); break;
         case "editor": editor_conf(); break;
         case "messages": messages(); break;
         case "further": further(); break;
         case "save_smiles": save_smiles(); break;
         case "edit_smiles": edit_smiles(); break;
         case "delete_smiles": delete_smiles(); break;
         case "smiles": smiles(); break;
         case "save": saves_config(); break;
         case "editor_tiny_mce":
            main::init_function('tinycme_adm');
            editor_tiny_mce();break;
         case "save_tiny_mce":
            main::init_function('tinycme_adm');
            save_tiny_mce();break;
         case "security":security_config();break;   
         default: main_config(); break;
      }
   } elseif($break_load==false) main_config();
}
switch_admin_config();
?>