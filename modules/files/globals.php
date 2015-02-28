<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

function global_add_files($msg=""){
global $main, $files, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    //Если присутствует ошибка, выводим ее
    if(!empty($msg)) warning($msg);
    //Подключаем модуль прикрепления файлов
    main::init_function('attache');
    main::init_class('afields');
    //Если администрация, подключаем календарь
    if(is_support()){
        main::add2script("includes/javascript/kr_calendar.js");
        main::add2link("includes/css/kr_calendar.css");
    }
    $_SESSION['uploaddir'] = $files['directory'].USER_FOLDER."/";
    //Открываем стилевую таблицу
    $af = new afields('');
    open();
    //Определяем нужна ли блокировка авто-заполняемых полей
    $disabled = (!is_support() AND is_user()) ? true : false;
    //Создаем форму добавления публикации
    echo "<form id='autocomplete' enctype='multipart/form-data' method='post' action='".((!defined("ADMIN_FILE")) ? $main->url(array('module' => $main->module, 'do' => 'save')) : "{$adminfile}?module={$main->module}&amp;do=save")."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text", $main->user['user_name'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_mail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("mail", "input_text", $main->user['user_email'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input'>".get_cat(array(''), $main->module, $files['multiple_cat']==ENABLED?true:false)."</td></tr>\n".
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n" : "").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['title_window']}</td><td class='form_input '>".in_text("title_win", "input_text2", $af->val('title'))."</td></tr>":"").
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_description']}</td><td class='form_input '>".in_text("meta_desc", "input_text2", $af->val('meta_description'))."</td></tr>":'').
    (is_support()?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_key']}</td><td class='form_input '>".in_text("meta_key", "input_text2", $af->val('meta_key'))."</td></tr>":"").
    "<tr class='row_tr'><td class='form_text'>{$main->lang['text']}:<span class='star'>*</span></td><td class='form_input'>".editor("descript", 10, "97%")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['content_text']}:</td><td class='form_input'>".editor("content", 10, "97%")."</td></tr>\n";    
    echo $files['tags_status']==ENABLED ? "<tr class='row_tr'><td class='form_text'>{$main->lang['tags_add']}:</td><td class='form_input'>".in_tag()."</td></tr>":'';
    echo "<tr class='row_tr'><td class='form_text'>{$main->lang['homepage']}:</td><td class='form_input'>".in_text('homepage', 'input_text', 'http://')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['filelink']}:</td><td class='form_input'>".in_text('link', 'input_text', 'http://')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['file_version']}:</td><td class='form_input'>".in_text('version', 'input_text')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['uploadfile']}:</td><td class='form_input'><input type='file' name='userfile' size='44' /></td></tr>\n";
    echo "<tr class='row_tr'><td class='form_text'>{$main->lang['filesize']}:</td><td class='form_input'>".in_text('size', 'input_text2')."</td></tr>\n";
    echo is_support() ? "<tr class='row_tr'><td class='form_text'>{$main->lang['pub_date']}:</td><td class='form_input'>".get_date_case(format_date(gmdate("Y-m-d H:i:s"), "Y-m-d H:i:s"))."</td></tr>\n" : "";
    if($files['comments_publishing']==ENABLED OR is_support()) echo "<tr><td class='form_text'>{$main->lang['active_comments']}</td><td class='form_input '>".in_chck("comments", "input_checkbox")."</td></tr>";
    if(is_support()) echo "<tr class='row_tr'><td class='form_text'>{$main->lang['active']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>";
    echo (!defined("ADMIN_FILE")?captcha():"").
    "<tr><td>".(($files['attaching']==ENABLED OR is_support())?in_hide('attache_page', "index.php?module={$main->module}&amp;do=attache_page")."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />":'&nbsp;')."</td><td align='right'>".send_button()."</td></tr>".
    "</table>\n</form>";
    close();    
}

function attache_page_files(){
global $main, $files, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
        'time' => time(),
        'content' => in_hide("uploaddir", $_SESSION['uploaddir'], true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($_SESSION['uploaddir'])."</div>".SWFUpload("index.php?module={$main->module}&amp;do=upload", $files['attaching_files_type'], $files['attaching_files_size'], $files['file_upload_limit'])."</div>",
        'lang'  => array(
            'title' => $main->lang['attach']
        )
    ))."</script>";
    kr_exit();
}

function global_save_files(){
global $main, $files;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('tags', 'get_fsize');
    main::init_class("afields");
    //Создаем список фильтрируемых элементов POST
    $filter = array('name', 'title', 'key_link');
    $filter += (isset($_POST['comments'])) ? array('comments') : array();
    filter_arr($filter, POST, TAGS);
    //Выполняем фильтрацию категорий
    if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $_POST['cid'][$kay] = kr_filter($_POST['cid'][$kay], TAGS);        
    //Проверяем заполнение полей
    $msg = error_empty(array('name', 'title', 'descript'), array('author_err', 'title_err', 'text_err')).check_captcha().check_mail($_POST['mail']);
    //Создаем идентификатор ЧПУ
    $files_id = (!isset($_POST['key_link']) OR empty($_POST['key_link'])) ? cyr2lat($_POST['title']) : $_POST['key_link'];
    $load_file = false;
    //Проверка на дубликат публикации
    if(empty($msg)){
        $result = $main->db->sql_query("SELECT files_id, title FROM ".FILES." WHERE files_id='{$files_id}' OR title='{$_POST['title']}'");
        $msg .= ($main->db->sql_numrows($result)>0) ? $main->lang['dublicate_pub'] : "";
        //Если нет ошибок пытаемся загрузить файл
        if(empty($msg)){
            //Проверяем нужно ли загружать файл
            if(isset($_FILES["userfile"]) AND !empty($_FILES["userfile"]['name'])){
                //Подключаем модуль загрузки файлов
                main::init_class('uploader');
                //Создаем новое имя файла
                $new_name = get_name_file(cyr2lat($_FILES["userfile"]['name'],true));
                //Создаем массив параметров для загрузки файлов
                $attach = new upload(array(
                    'dir'    => $files['directory'].USER_FOLDER."/",
                    'file'   => $_FILES["userfile"],
                    'size'   => $files['attaching_files_size'],
                    'type'   => explode(",", $files['attaching_files_type']),
                    'name'   => $new_name
                ));
                if($attach->error) $msg = $attach->get_error_msg();
                elseif(!empty($_FILES["userfile"]['name'])) $load_file = true;
            }
        }
    }
    //Если нет ошибок делаем инсерт
    if(empty($msg)){
         $cid = ","; 
         $af = new afields();
         $af->load_from_post(array("title_win"=>'title',"meta_desc"=>'meta_description',"meta_key"=>'meta_key'));
         //Создаем списки тегов
         if(isset($_POST['cid']) AND is_array($_POST['cid']) AND count($_POST['cid'])>0) foreach ($_POST['cid'] as $kay=>$value) $cid .= $value.",";
         $nex_id = $main->db->sql_nextid(sql_insert(array(
             'files_id'      => $files_id,
             'title'         => $_POST['title'],
             'author'        => $_POST['name'],
             'date'          => isset($_POST['year']) ? date("Y-m-d H:i:s",strtotime("{$_POST['year']}-{$_POST['month']}-{$_POST['day']}".(isset($_POST['H'])?" {$_POST['H']}:{$_POST['i']}:{$_POST['s']}":" ".kr_date_user("H:i:s")))) : kr_datecms("Y-m-d H:i:s"),
             'cid'           => $cid,
             'status'        => ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $files['moderation_publications']!=ENABLED) ? 1 : 0,
             'show_comment'  => (isset($_POST['comments']) AND $_POST['comments']==ENABLED) ? 1 : 0,
             'tags'          => normalize_tags(),
             'show_group'    => '',
             'email'         => $_POST['mail'],
             'url'           => (!empty($_POST['link']) AND $_POST['link']!='http://' AND $load_file==false)?$_POST['link']:($load_file?$attach->file:""),
             'filesize'      => (!isset($_POST['size'])?(!empty($_POST['link']) AND $_POST['link']!='http://' AND $load_file==false)?get_fsize($_POST['link']):($load_file?filesize($files['directory'].USER_FOLDER."/".$attach->file):"0"):$_POST['size']),
             'version'       => mb_substr($_POST['version'], 0, 14),
             'homepage'      => mb_substr($_POST['homepage'], 0, 99),
             'language'      => isset($_POST['language']) ? $_POST['language'] : "",
             'description'   => bb(kr_filter($_POST['descript'])),
             'content'       => bb(kr_filter(empty($_POST['content']) ? $_POST['descript'] : $_POST['content'])),
             'afields'       => $af->sql(),
         ), FILES));
         set_calendar_date($nex_id, $main->module, kr_dateuser2db("Y-m-d"), ((is_support() AND isset($_POST['active']) AND $_POST['active']==ENABLED) OR $files['moderation_publications']!=ENABLED) ? 1 : 0);
         //Добавляем теги в БД
         set_tags_sql($nex_id, $main->module);
         //Добавляем пункты пользователю
         add_points($main->points['add_files']);
         //Прикрепляем файл
         if(rename_attach($files['directory'].USER_FOLDER."/", $files['directory'].$nex_id."/")){ 
             //Обновляем текст
             sql_update(array(
                'description'  => bb(kr_filter(str_replace(USER_FOLDER, $nex_id, $_POST['descript']))),
                'content'      => bb(kr_filter(str_replace(USER_FOLDER, $nex_id, empty($_POST['content']) ? $_POST['descript'] : $_POST['content'])))
             ), FILES, "id='{$nex_id}'");
         } elseif($load_file==true AND file_exists($files['directory'].USER_FOLDER."/".$attach->file)) rename($files['directory'].USER_FOLDER."/", $files['directory'].$nex_id."/"); 
         redirect(MODULE);
    } else global_add_files($msg);
}

function global_upload_attach_files(){
global $files;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    upload_attach($files);
}
?>