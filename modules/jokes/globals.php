<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

function global_add_jokes($msg=""){
global $main, $jokes, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    main::init_function('attache');
    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    open();
    $disabled = (!is_support() AND is_user()) ? true : false;
    echo "<form method='post' action='".((!defined("ADMIN_FILE")) ? $main->url(array('module' => $main->module, 'do' => 'save')) : "{$adminfile}?module={$main->module}&amp;do=save")."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2", $main->user['user_name'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(array(''), $main->module, $jokes['multiple_cat']==ENABLED?true:false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['joke']}:<span class='star'>*</span></td><td class='form_input'>".editor("joke", 10, "97%")."</td></tr>\n".    
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date(gmdate("Y-m-d H:i:s"), "Y-m-d H:i:s"))."</td></tr>\n":"").
    "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>".
    (!defined("ADMIN_FILE")?captcha():"")."<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";    
    close();
}

function global_save_jokes(){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    filter_arr(array('name', 'title', 'joke'), POST, TAGS);
    if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $_POST['cid'][$kay] = kr_filter($_POST['cid'][$kay], TAGS);        
    $msg = error_empty(array('name', 'title', 'joke'), array('author_err', 'title_err', 'text_err')).check_captcha();
    if(empty($msg)){
        $result = $main->db->sql_query("SELECT title FROM ".JOKES." WHERE title='{$_POST['title']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
    }
    if(empty($msg)){
        $cid = ",";
        if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $cid .= $value.",";
        $nex_id = $main->db->sql_nextid(sql_insert(array(
            'title'         => $_POST['title'],
            'author'        => $_POST['name'],
            'date'          => kr_dateuser2db(),
            'cid'           => $cid,
            'status'        => ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $jokes['moderation_publications']!=ENABLED) ? 1 : 0,
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",
            'joke'          => bb($_POST['joke'])
        ), JOKES));
        set_calendar_date($nex_id, $main->module, kr_dateuser2db("Y-m-d"), ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $jokes['moderation_publications']!=ENABLED) ? 1 : 0);
        add_points($main->points['add_jokes']);        
        redirect(MODULE);
    } else global_add_jokes($msg);
}
?>