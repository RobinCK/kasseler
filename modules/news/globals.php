<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");
global $check_revision,$count_in_page,$news, $main;
$check_revision=772;
$count_in_page=$news['publications_in_page'];

function global_add_news($msg="", $editv=array()){
global $main, $news, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    //Если присутствует ошибка, выводим ее
    if(!empty($msg)) warning($msg);
    //Подключаем модуль прикрепления файлов
    main::init_function('editor_value');
    main::init_class('afields');
    editval_set($editv);
    $af=new afields(editval('afields'));
    //Если администрация, подключаем календарь
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    //Удаляем сессию загрузки файлов
    if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
    $groups = array();
    $result = $main->db->sql_query("SELECT id, title FROM ".GROUPS." ORDER BY title");
    while(($row = $main->db->sql_fetchrow($result))) $groups[$row['id']] = $row['title'];
    //Открываем стилевую таблицу
    open();
    //Определяем нужна ли блокировка авто-заполняемых полей
    $disabled = (!is_support() AND is_user()) ? true : false;
    //Создаем форму добавления публикации 
    if(!defined("ADMIN_FILE")){
       $faarr = array('module' => $main->module, 'do' => 'save');
       if(!empty($editv['id'])) $faarr['id'] = editval('id');
       $form_action=$main->url($faarr);
    } else {
       $form_action= "{$adminfile}?module={$main->module}&amp;do=save";
       if(!empty($editv['id'])) $form_action.="&amp;id=".editval('id')."";
    }
    $attach_array = array('module' => $main->module, 'do' => 'attache_page');
    if(!empty($editv['id'])) $attach_array['id'] = $editv['id'];
    echo "<form id='autocomplete' method='post' action='".$form_action."'>".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text2", $main->user['user_name'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", editval('title'), false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(editval_array('cid'), $main->module, $news['multiple_cat']==ENABLED?true:false)."</td></tr>\n".
    ////
    "<tr class='row_tr'><td colspan='2' style='padding: 0;'><a href='#' onclick=\"$('#form_{$main->module}_seo').slideToggle(); $(this).toggleClass('options_show_ac'); $(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'></span>{$main->lang['optimize']}</a></td></tr>".
    "</table><div id='form_{$main->module}_seo' class='post_options'><table class='form' align='center'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2",editval('news_id'))."</td></tr>\n".
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file(editval('language'))."</td></tr>\n":"").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['title_window']}</td><td class='form_input '>".in_text("title_win", "input_text2", $af->val('title'))."</td></tr>":"").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_description']}</td><td class='form_input '>".in_text("meta_desc", "input_text2", $af->val('meta_description'))."</td></tr>":'').
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_key']}</td><td class='form_input '>".in_text("meta_key", "input_text2", $af->val('meta_key'))."</td></tr>":"").
    "</table></div><table class='form' align='center' id='form_{$main->module}1'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['text']}:<span class='star'>*</span></td><td class='form_input'>".editor("main_text", 10, "99%",editval_editor("begin"),0,"")."</td></tr>\n".
    /////////////////
    "<tr class='row_tr'><td colspan='2' style='padding: 0;'><a href='#' onclick=\"$('#form_{$main->module}_cntent').slideToggle(); $(this).toggleClass('options_show_ac'); $(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'></span>{$main->lang['content_text']}</a></td></tr>".
    "</table><div id='form_{$main->module}_cntent' class='post_options'><table class='form' align='center'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['content_text']}:</td><td class='form_input'>".editor("content_text", 10,"99%",editval_editor("content"),0,"")."</td></tr>\n".
    "</table></div><table class='form' align='center' id='form_{$main->module}2'>".
    /////////////////
    "<tr class='row_tr'><td colspan='2' style='padding: 0;'><a href='#' onclick=\"$('#form_{$main->module}_options').slideToggle(); $(this).toggleClass('options_show_ac'); $(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'></span>{$main->lang['options']}</a></td></tr>".    
    "</table><div id='form_{$main->module}_options' class='post_options'><table class='form' align='center'>".
    ($news['tags_status']==ENABLED ? "<tr class='row_tr'><td class='form_text'>{$main->lang['tags_add']}:</td><td class='form_input'>".in_tag(editval('tags',array()))."</td></tr>":'').    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['view_only_for']}</td><td class='form_input '>".in_sels('view_groups', $groups, 'select chzn-select',editval_array('vgroups'),"",true)."</td></tr>".
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date(editval('date',gmdate("Y-m-d H:i:s")), "Y-m-d H:i:s"))."</td></tr>\n":'').
    (is_support() ? "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox",editval('status',ENABLED))."</td></tr>" : '').
    (($news['comments_publishing']==ENABLED OR is_support())?"<tr class='row_tr'><td class='form_text'>{$main->lang['active_comments']}</td><td class='form_input '>".in_chck("comments", "input_checkbox",editval('show_comment'))."</td></tr>":'').
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['fix_news']}</td><td class='form_input '>".in_chck("fixed", "input_checkbox",editval('fix_news','n')=='y'?'on':'off')."</td></tr>":'').
    "</table></div><table class='form' align='center' id='form_{$main->module}3'>".
    captcha().
    "<tr class='buttonsrow'><td>".(($news['attaching']==ENABLED OR is_support())?in_hide('attache_page', $main->url($attach_array))."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />":'')."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>";
    close();
}

function attache_page_news(){
global $main, $news;
    main::init_function('attache');
    if(is_admin()&&!empty($_GET['id'])){
        $uploaddir = $news['directory'].intval($_GET['id'])."/";
        if(!file_exists($uploaddir)) mkdir($uploaddir);
    } else $uploaddir = $news['directory'].USER_FOLDER."/";
    $_SESSION['uploaddir'] = $uploaddir;
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $uploaddir, true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($uploaddir)."</div>".SWFUpload("index.php?module={$main->module}&amp;do=upload", $news['attaching_files_type'], $news['attaching_files_size'], $news['file_upload_limit'])."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    kr_exit();
}

function  global_array_save_news(){
   global $main, $news;
   if(hook_check(__FUNCTION__)) return hook();
   $news_id = (!isset($_POST['key_link']) OR empty($_POST['key_link'])) ? cyr2lat($_POST['title']) : $_POST['key_link'];
   main::init_class("afields");
   $af=new afields();
   if(isset($_GET['id'])) $af->load_from_db(NEWS,"id=".intval($_GET['id']));
   $af->load_from_post(array("title_win"=>'title',"meta_desc"=>'meta_description',"meta_key"=>'meta_key'));
   $cid = post_array_ids('cid');
   $modify=array(
      'news_id'       => $news_id,
      'title'         => $_POST['title'],
      'date'          => kr_dateuser2db("Y-m-d H:i:s"),
      'cid'           => $cid,
      'status'        => ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $news['moderation_publications']!=ENABLED) ? 1 : 0,
      'show_comment'  => (isset($_POST['comments']) AND $_POST['comments']==ENABLED) ? 1 : 0,
      'tags'          => normalize_tags(),  //Создаем списки тегов
      'language'      => isset($_POST['language']) ? $_POST['language'] : "",
      'begin'         => bb(kr_filter(empty($_POST['content_text']) ? cut_text(preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text'])) : preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text']))),
      'content'       => bb(kr_filter(empty($_POST['content_text']) ? $_POST['main_text'] : $_POST['content_text'])),
      'fix_news'      => (isset($_POST['fixed']) and $_POST['fixed']==ENABLED)?'y':'n',
      'afields'       => $af->sql(),
      'vgroups'       => post_array_ids('view_groups'));
   return $modify;
}

function global_save_news(){
global $main, $news,$count_in_page;
    if(hook_check(__FUNCTION__)) return hook();    
    main::init_function(array('tags','post_array_ids'));
    $filter = array('name', 'title', 'key_link');
    if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $_POST['cid'][$kay] = kr_filter($_POST['cid'][$kay], TAGS);
    $filter += (isset($_POST['comments'])) ? array('comments') : array();
    filter_arr($filter, POST, TAGS);
    $msg = error_empty(array('name', 'title', 'main_text'), array('author_err', 'title_err', 'text_err')).check_captcha();
    $news_id = (!isset($_POST['key_link']) OR empty($_POST['key_link'])) ? cyr2lat($_POST['title']) : $_POST['key_link'];
    if(empty($msg)&&!isset($_GET['id'])){
        $result = $main->db->sql_query("SELECT news_id, title FROM ".NEWS." WHERE news_id='{$news_id}' OR title='{$_POST['title']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
    }
    if(isset($_POST['fixed']) and $_POST['fixed']==ENABLED){
       $result = $main->db->sql_query("SELECT count(id) as cnt FROM ".NEWS." WHERE fix_news='y'");
       list($count)=$main->db->sql_fetchrow($result);
       if($count>=($count_in_page-2)) $msg.=$main->lang['many_fixed_err'];
    }
    if(empty($msg)){
       $modify = global_array_save_news();
       $id=isset($_GET['id'])?intval($_GET['id']):0;        
       if(!empty($id)&&!defined('ADMIN_FILE')) {redirect(BACK); kr_exit();}
       if($id==0) {
          $modify['author']  = $_POST['name'];
          $nex_id = $main->db->sql_nextid(sql_insert($modify, NEWS));
          //Добавляем пункты пользователю
          add_points($main->points['add_news']);
       }
       else {
          sql_update($modify,NEWS," id={$id}");
          $nex_id=$id;
          //Удаляем и создаем заново теги
          $main->db->sql_query("DELETE FROM ".TAG." WHERE modul='{$main->module}' AND post='{$id}'");
       }        
        if(isset($_POST['year'])) set_calendar_date($nex_id, $main->module, kr_dateuser2db("Y-m-d"), (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0);
        //Добавляем теги в БД
        set_tags_sql($nex_id, $main->module);
        
        ////Прикрепление файлов 
        if(!file_exists($news['directory'].$nex_id."/")){
           if(rename_attach($news['directory'].USER_FOLDER."/", $news['directory'].$nex_id."/")){ 
              $_POST['main_text'] = str_replace(USER_FOLDER, $nex_id, $_POST['main_text']);
              $_POST['content_text'] = str_replace(USER_FOLDER, $nex_id, $_POST['content_text']);
              sql_update(array(
                 'begin'         => bb(kr_filter(empty($_POST['content_text']) ? cut_text(preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text'])) : preg_replace('/\[PAGE_BREAK\]/is', '', $_POST['main_text']))),
                 'content'       => bb(kr_filter(empty($_POST['content_text']) ? $_POST['main_text'] : $_POST['content_text']))
                 ), NEWS, "id='{$nex_id}'");
           }
        }
        redirect(MODULE);
    } else {
       $modify = global_array_save_news();
       if(defined('ADMIN_FILE') && isset($_GET['id'])){
          $main->db->sql_query("select * from ".NEWS." where id=".intval($_GET['id']));
          if($main->db->sql_numrows()>0){
             $row = $main->db->sql_fetchrow();
             $modify = array_merge($row, $modify);
          }
       }
       global_add_news($msg, $modify);
    }
}

function global_upload_attach_news(){
global $news;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    upload_attach($news);
}
?>