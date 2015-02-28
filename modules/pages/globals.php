<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

function global_add_pages($msg=""){
global $main, $pages, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    main::init_function('attache');
    main::init_class('afields');
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    $_SESSION['uploaddir'] = $pages['directory'].USER_FOLDER."/";
    $af = new afields('');
    open();
    $disabled = (!is_support() AND is_user()) ? true : false;
    echo "<form id='autocomplete' method='post' action='".((!defined("ADMIN_FILE")) ? $main->url(array('module' => $main->module, 'do' => 'save')) : "{$adminfile}?module={$main->module}&amp;do=save")."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2", $main->user['user_name'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2")."</td></tr>\n".
    (defined("ADMIN_FILE")?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_description']}</td><td class='form_input '>".in_text("meta_desc", "input_text2", "")."</td></tr>":"").
    (defined("ADMIN_FILE")?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_key']}</td><td class='form_input '>".in_text("meta_key", "input_text2", "")."</td></tr>":"").
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(array(''), $main->module, $pages['multiple_cat']==ENABLED?true:false)."</td></tr>\n".
    (is_support() ? "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n" : "").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['title_window']}</td><td class='form_input '>".in_text("title_win", "input_text2", $af->val('title'))."</td></tr>":"").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_description']}</td><td class='form_input '>".in_text("meta_desc", "input_text2", $af->val('meta_description'))."</td></tr>":'').
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_key']}</td><td class='form_input '>".in_text("meta_key", "input_text2", $af->val('meta_key'))."</td></tr>":"").
    "<tr class='row_tr'><td class='form_text'>{$main->lang['text']}:<span class='star'>*</span></td><td class='form_input'>".editor("main_text", 10, "97%")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['content_text']}:</td><td class='form_input'>".editor("content_text", 10, "97%")."</td></tr>\n";
    echo $pages['tags_status']==ENABLED ? "<tr class='row_tr'><td class='form_text'>{$main->lang['tags_add']}:</td><td class='form_input'>".in_tag()."</td></tr>":'';
    echo is_support() ? "<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date(gmdate("Y-m-d H:i:s"), "Y-m-d H:i:s"))."</td></tr>\n" : "";
    if($pages['comments_publishing']==ENABLED OR is_support()) echo "<tr><td class='form_text'>{$main->lang['active_comments']}</td><td class='form_input '>".in_chck("comments", "input_checkbox")."</td></tr>";
    if(is_support()) echo "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>";
    echo (!defined("ADMIN_FILE")?captcha():"").
    "<tr><td>".(($pages['attaching']==ENABLED OR is_support())?in_hide('attache_page', "index.php?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />":'&nbsp;')."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>";
    close();
}

function attache_page_pages(){
global $main, $pages, $adminfile;
    main::init_function('attache');
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $_SESSION['uploaddir'], true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($_SESSION['uploaddir'])."</div>".SWFUpload("index.php?module={$main->module}&amp;do=upload", $pages['attaching_files_type'], $pages['attaching_files_size'], $pages['file_upload_limit'])."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    kr_exit();
}

function global_save_pages(){
global $main, $pages;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('tags');
    main::init_class("afields");
    
    $filter = array('name', 'title', 'key_link');
    if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $_POST['cid'][$kay] = kr_filter($_POST['cid'][$kay], TAGS);
    $filter += (isset($_POST['comments'])) ? array('comments') : array();
    filter_arr($filter, POST, TAGS);
    $msg = error_empty(array('name', 'title', 'main_text'), array('author_err', 'title_err', 'text_err')).check_captcha();
    $pages_id = (!isset($_POST['key_link']) OR empty($_POST['key_link'])) ? cyr2lat($_POST['title']) : $_POST['key_link'];
    if(empty($msg)){
        $result = $main->db->sql_query("SELECT pages_id, title FROM ".PAGES." WHERE pages_id='{$pages_id}' OR title='{$_POST['title']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
    }
    if(empty($msg)){
        $cid = ",";
        $af = new afields();
        $af->load_from_post(array("title_win"=>'title',"meta_desc"=>'meta_description',"meta_key"=>'meta_key'));

        if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $cid .= $value.",";
        $nex_id = $main->db->sql_nextid(sql_insert(array(
            'pages_id'      => $pages_id,
            'title'         => $_POST['title'],
            'author'        => $_POST['name'],
            'date'          => kr_dateuser2db(),
            'cid'           => $cid,
            'status'        => ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $pages['moderation_publications']!=ENABLED) ? 1 : 0,
            'show_comment'  => (isset($_POST['comments']) AND $_POST['comments']==ENABLED) ? 1 : 0,
            'tags'          => normalize_tags(),  
            'show_group'    => '',
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",
            'begin'         => bb(kr_filter(empty($_POST['content_text']) ? cut_text(preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text'])) : preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text']))),
            'content'       => bb(kr_filter(empty($_POST['content_text']) ? $_POST['main_text'] : $_POST['content_text'])),
            'afields'       => $af->sql()
        ), PAGES));
        set_calendar_date($nex_id, $main->module, kr_dateuser2db("Y-m-d"), ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $pages['moderation_publications']!=ENABLED) ? 1 : 0);
        //Добавляем теги в БД
        set_tags_sql($nex_id, $main->module);
        //Добавляем пункты пользователю
        add_points($main->points['add_pages']);
        ////Прикрепление файлов 
        if(rename_attach($pages['directory'].USER_FOLDER."/", $pages['directory'].$nex_id."/")){
            $_POST['main_text'] = str_replace(USER_FOLDER, $nex_id, $_POST['main_text']);
            $_POST['content_text'] = str_replace(USER_FOLDER, $nex_id, $_POST['content_text']);
            sql_update(array(
                'begin'         => bb(kr_filter(empty($_POST['content_text']) ? cut_text(preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text'])) : preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text']))),
                'content'       => bb(kr_filter(empty($_POST['content_text']) ? $_POST['main_text'] : $_POST['content_text']))
            ), PAGES, "id='{$nex_id}'");
        }
        redirect(MODULE);
    } else global_add_pages($msg);
}

function global_upload_attach_pages(){
global $pages;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    upload_attach($pages);
}
?>