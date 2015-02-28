<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
$break_load=false;
if(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}
main::required("modules/{$main->module}/globals.php");
$navi = array(
    array('', 'home'),
    array('category', 'categoryes'),
    array('add', 'addphoto'),
    array('add_category', 'add_cat'),
    array('add_cubcat', 'add_cubcat'),
    array('config', 'config')
);

function admin_main_albom(){
global $main, $adminfile, $albom, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $result = $main->db->sql_query("SELECT a.id, a.title, a.image, a.cid, a.time, c.title AS cat_title, c.image AS cat_image{FIELDS} FROM ".ALBOM." AS a LEFT JOIN ".CAT." AS c ON(a.cid=c.cid){TABLES} ORDER BY time DESC LIMIT {$offset}, 30",__FUNCTION__);
    $rows = $main->db->sql_numrows($result);
    if($rows>0){
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<table cellspacing='1' class='table' width='100%'>\n<tr><th width='15'>#</th><th>{$main->lang['image']}</th><th>{$main->lang['title']}</th><th width='120'>{$main->lang['category']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";
        while($r = $main->db->sql_fetchrow($result)){
            $op = "<table align='center' cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$r['id']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$r['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}' id='foto{$r['id']}'><td class='col' align='center'>{$i}</td><td width='50' align='center'><a onclick=\"\$(this).colorbox();\" href='{$albom['directory']}{$r['time']}/temp-{$r['image']}'><img src='{$albom['directory']}{$r['time']}/macro-{$r['image']}' alt='{$r['title']}' /></a></td><td class='col'>{$r['title']}</td><td align='center'>".(!empty($r['cat_title'])?$r['cat_title']:"<i>{$main->lang['nocat']}</i>")."</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table>";
        //Вывод фотографий
        if ($rows==30 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".ALBOM." "));
            pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
        }
    } else info($main->lang['noinfo']);
}

function admin_config_albom(){
global $albom, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('admmodulecontrol');
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $albom['directory'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_width_photo']}</b>:<br /><i>{$main->lang['max_width_photo_d']}</i></td><td class='form_input2'>".in_text('max_width', 'input_text2', $albom['max_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_height_photo']}</b>:<br /><i>{$main->lang['max_height_photo_d']}</i></td><td class='form_input2'>".in_text('max_height', 'input_text2', $albom['max_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_size_photo']}</b>:<br /><i>{$main->lang['max_size_photo_d']}</i></td><td class='form_input2'>".in_text('max_size', 'input_text2', isset($albom['max_size'])?$albom['max_size']:"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_width_photo']}</b>:<br /><i>{$main->lang['miniature_width_photo_d']}</i></td><td class='form_input2'>".in_text('miniature_width', 'input_text2', $albom['miniature_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_height_photo']}</b>:<br /><i>{$main->lang['miniature_height_photo_d']}</i></td><td class='form_input2'>".in_text('miniature_height', 'input_text2', $albom['miniature_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['cols_photo']}</b>:<br /><i>{$main->lang['cols_photo_d']}</i></td><td class='form_input2'>".in_text('cols', 'input_text2', $albom['cols'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['rows_photo']}</b>:<br /><i>{$main->lang['rows_photo_d']}</i></td><td class='form_input2'>".in_text('rows', 'input_text2', $albom['rows'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sort_type_publications']}</b>:<br /><i>{$main->lang['sort_type_publications_d']}</i></td><td class='form_input2'>".in_sels('sort_type_publications', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', isset($albom['sort_type_publications'])?$albom['sort_type_publications']:"ASC")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['comments_sort']}</b>:<br /><i>{$main->lang['comments_sort_d']}</i></td><td class='form_input2'>".in_sels('comments_sort', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $albom['comments_sort'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_d']}</i></td><td class='form_input2'>".in_chck('rating', 'input_checkbox', $albom['rating'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_comments']}</b>:<br /><i>{$main->lang['conf_comments_d']}</i></td><td class='form_input2'>".in_chck('comments', 'input_checkbox', $albom['comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['guests_comments']}</b>:<br /><i>{$main->lang['guests_comments_d']}</i></td><td class='form_input2'>".in_chck('guests_comments', 'input_checkbox', $albom['guests_comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['show_name_photo']}</b>:<br /><i>{$main->lang['show_name_photo_d']}</i></td><td class='form_input2'>".in_chck('show_name_photo', 'input_checkbox', $albom['show_name_photo'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['show_name_cat']}</b>:<br /><i>{$main->lang['show_name_cat_d']}</i></td><td class='form_input2'>".in_chck('show_name_cat', 'input_checkbox', $albom['show_name_cat'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['show_descript_photo']}</b>:<br /><i>{$main->lang['show_descript_photo_d']}</i></td><td class='form_input2'>".in_chck('show_descript_photo', 'input_checkbox', $albom['show_descript_photo'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['show_descript_cat']}</b>:<br /><i>{$main->lang['show_descript_cat_d']}</i></td><td class='form_input2'>".in_chck('show_descript_cat', 'input_checkbox', $albom['show_descript_cat'])."</td></tr>\n".
    module_control_config().
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function admin_saves_albom(){
global $albom, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if (!isset($albom['max_size'])) $albom['max_size']="";// temporary fix new parametr
    main::init_function('sources');
    if(!isset($albom['sort_type_publications'])) $albom['sort_type_publications']='ASC';
    save_config('config_albom.php', '$albom', $albom);
    main::init_function('admmodulecontrol'); module_control_saveconfig();
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function admin_download_read_albom(){
global $main, $adminfile, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    $patch = $albom['directory'].$main->user['user_name'].'/';
    $files = array();
    $type_arr = explode(',', mb_strtoupper($albom['photo_type']));
    if(($handle = opendir($patch))){
        while(false !== ($file = readdir($handle))) {
            if(!is_dir($patch.$file)){
                $exp = mb_strtoupper(get_type_file($file));
                if(in_array($exp, $type_arr) AND !preg_match('/^orig\-|mini\-/i', $file)) $files[] = $file;
            }
        }
        closedir($handle);
    }
    echo "var file_listener = [];";
    for($i=0;$i<count($files);$i++) echo "file_listener[{$i}] = '{$files[$i]}';";
    kr_exit();
}

function admin_remove_dir_albom(){
global $main, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists("{$albom['directory']}".USER_FOLDER)){
        remove_dir("{$albom['directory']}".USER_FOLDER);
    }
    kr_exit();
}

function admin_list_category(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;    
    $sql = "SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level, c.title AS parent{FIELDS} FROM ".CAT." AS t LEFT JOIN ".CAT." AS c ON(SUBSTR(t.tree,1,LENGTH(t.tree)-2)=c.tree){TABLES} WHERE t.module='{$main->module}' {WHERES} ORDER BY t.tree LIMIT {$offset}, 30";
    $result = $main->db->sql_query($sql,__FUNCTION__);
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        echo "<table cellspacing='1' class='table' width='100%'>
        <tr><th width='15'>#</th><th>{$main->lang['title']}</th><th width='130'>{$main->lang['main_cat']}</th><th width='80'>{$main->lang['subcat']}</th><th width='60'>{$main->lang['level']}</th><th width='90'>{$main->lang['image']}</th><th width='60'>{$main->lang['functions']}</th></tr>";    
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        while(($rows = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$_GET['module']}&amp;do=edit_category&amp;id={$rows['cid']}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=delete_category&amp;id={$rows['cid']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}'><td class='col' align='center'>{$i}</td><td>{$rows['title']}</td><td align='center'>".(!empty($rows['parent'])?$rows['parent']:"-")."</td><td align='center'>".((!empty($rows['parent']))?"<span style='color:red'>{$main->lang['yes2']}</span>":"<span style='color:green'>{$main->lang['no']}</span>")."</td><td align='center'>".(($rows['level']>0)?$rows['level']:"-")."</td><td align='center'>".((!empty($rows['image']) AND $rows['image']!='no.png') ? "<span style='color:green'>{$main->lang['yes']}</span>" : "<span style='color:red'>{$main->lang['no']}</span>")."</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table>";
        if ($rows_c==30 OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".CAT." WHERE module='{$main->module}'"));
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

function admin_add_category_albom($msg=""){
global $main, $adminfile, $albom, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_mask_types','attache');
    
    if(!empty($msg)) warning($msg);
    main::add2script("modules/{$main->module}/script.js");
    main::add2script('includes/javascript/jquery/jquery.swfupload.js');
    main::add2script('includes/javascript/jquery/jquery.Jcrop.min.js');
    
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_category'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", "")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 3)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['load_image']}:<br /><i>".str_replace('{SIZE}', "{$albom['miniature_width']}x{$albom['miniature_height']}", $main->lang['minimum_size'])."</i></td>
    <td class='form_input' id='image_prev'>
         <div class='textwrapper'>".
            "<script type='text/javascript'>
            <!--
                setTimeout(function(){
                    KR_AJAX.swfupload({
                        upload_url: 'index.php?module={$main->module}&do=download_photo_cat',
                        post_params: {
                            'PHPSESSID' : '".session_id()."',
                            'uname':'{$main->user['user_name']}',
                            'secID':(jsSecretID?jsSecretID:'')
                        },
                        file_size_limit : ".($albom['max_size']==""?"1024000":$albom['max_size']).",
                        file_types : '".get_mask_types($albom['photo_type'])."',
                        file_types_description : '".($albom['photo_type']!='*'?str_replace(",", ";", mb_strtoupper($albom['photo_type'])):'All')."',
                        file_upload_limit : 100,
                        upload_complete_handler : function(file){
                            upload_ch('{$albom['directory']}', '".strtolower($main->user['user_name'])."', {$albom['miniature_width']}, {$albom['miniature_height']});
                        },
                        button_image_url : '".$main->config['http_home_url']."includes/images/pixel.gif'
                    });
                }, 700);
            // -->
            </script>".
            "<div class='crop_content' style='position:absolute; left: -10000px;'><img src='includes/images/pixel.gif' alt='' /></div>".
            '<div id="Buttons" style="text-align:left;"><span id="UploadPhotos"><input type="button" id="Progress" /><i id="fAddPhotos"></i><input type="button" id="AddPhotos" value="'.$main->lang['upload'].'" /></span></div>'.
         "</div>
    </td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>".in_hide('type_save', "cat")."\n</form>\n".
    in_hide('crop_img', "{$adminfile}?module={$main->module}&amp;do=crop_img");
}

function admin_add_cubcat_albom($msg=""){
global $main, $adminfile, $albom, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_mask_types');
    
    if(!empty($msg)) warning($msg);
    main::add2script("modules/{$main->module}/script.js");
    main::add2script("includes/javascript/jquery/jquery.swfupload.js");
    main::add2script('includes/javascript/jquery/jquery.Jcrop.min.js');
    
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_category'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", "")."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:<span class='star'>*</span></td><td class='form_input'>".get_cat('', $main->module, false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 3)."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['load_image']}:<br /><i>".str_replace('{SIZE}', "{$albom['miniature_width']}x{$albom['miniature_height']}", $main->lang['minimum_size'])."</i></td>
    <td class='form_input' id='image_prev'>
         <div class='textwrapper'>
            <script type='text/javascript'>
            <!--
                setTimeout(function(){
                    KR_AJAX.swfupload({
                        upload_url: 'index.php?module={$main->module}&do=download_photo_cat',
                        post_params: {
                            'PHPSESSID' : '".session_id()."',
                            'uname':'{$main->user['user_name']}',
                            'secID':(jsSecretID?jsSecretID:'')
                        },
                        file_size_limit : ".($albom['max_size']==""?"1024000":$albom['max_size']).",
                        file_types : '".get_mask_types($albom['photo_type'])."',
                        file_types_description : '".($albom['photo_type']!='*'?str_replace(",", ";", mb_strtoupper($albom['photo_type'])):'All')."',
                        file_upload_limit : 100,
                        upload_complete_handler : function(file){
                            upload_ch('{$albom['directory']}', '{$main->user['user_name']}', {$albom['miniature_width']}, {$albom['miniature_height']});
                        },
                        button_image_url : '".$main->config['http_home_url']."includes/images/pixel.gif'
                    });
                }, 700);
            // -->
            </script>".
            "<div class='crop_content' style='position:absolute; left: -10000px;'><img src='includes/images/pixel.gif' alt='' /></div>".
            '<div id="Buttons" style="text-align:left;"><span id="UploadPhotos"><input type="button" id="Progress" /><i id="fAddPhotos"></i><input type="button" id="AddPhotos" value="'.$main->lang['upload'].'" /></span></div>'.
         "</div>
    </td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n".in_hide('subcut', 'true').in_hide('type_save', "subcat")."</form>\n".
    in_hide('crop_img', "{$adminfile}?module={$main->module}&amp;do=crop_img");
}

function admin_edit_category_albom($msg=""){
global $main, $adminfile, $albom, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_mask_types');
    
    if(!empty($msg)) warning($msg);
    main::add2script("modules/{$main->module}/script.js");
    main::add2script('includes/javascript/jquery/jquery.swfupload.js');
    main::add2script('includes/javascript/jquery/jquery.Jcrop.min.js');
    
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".CAT." WHERE cid='{$_GET['id']}'"));
    
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_category&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $info['title'], false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", $info['cat_id'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 3, $info['description'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input' id='image_prev'>
        <img id='prewiew' style='border: 1px #dfe5ea solid;' src='{$albom['directory']}category/{$info['image']}?=".time()."' alt='' /><input type='hidden' id='admin_delete_image' name='admin_delete_image' value='false' /><br />
        <a href='#' onclick=\"document.getElementById('prewiew').style.display = 'none'; document.getElementById('admin_delete_image').value='true'; parentNode.innerHTML = '<i style=\'color: green\'>{$main->lang['image_deleted']}</i>'; return false;\">{$main->lang['delete']}</a>
    </td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['load_image']}:<br /><i>".str_replace('{SIZE}', "{$albom['miniature_width']}x{$albom['miniature_height']}", $main->lang['minimum_size'])."</i></td>
    <td class='form_input'>
         <div class='textwrapper'>
            <script type='text/javascript'>
            <!--
                setTimeout(function(){
                    KR_AJAX.swfupload({
                        upload_url: 'index.php?module={$main->module}&do=download_photo_cat',
                        post_params: {
                            'PHPSESSID' : '".session_id()."',
                            'uname':'{$main->user['user_name']}',
                            'secID':(jsSecretID?jsSecretID:'')
                        },
                        file_size_limit : ".($albom['max_size']==""?"1024000":$albom['max_size']).",
                        file_types : '".get_mask_types($albom['photo_type'])."',
                        file_types_description : '".($albom['photo_type']!='*'?str_replace(",", ";", mb_strtoupper($albom['photo_type'])):'All')."',
                        file_upload_limit : 100,
                        upload_complete_handler : function(file){
                            upload_ch('{$albom['directory']}', '{$main->user['user_name']}', {$albom['miniature_width']}, {$albom['miniature_height']});
                        },
                        button_image_url : '".$main->config['http_home_url']."includes/images/pixel.gif'
                    });
                }, 700);
            // -->
            </script>".
            "<div class='crop_content' style='position:absolute; left: -10000px;'><img src='includes/images/pixel.gif' alt='' /></div>".
            '<div id="Buttons" style="text-align:left;"><span id="UploadPhotos"><input type="button" id="Progress" /><i id="fAddPhotos"></i><input type="button" id="AddPhotos" value="'.$main->lang['upload'].'" /></span></div>'.
         "</div>
    </td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n".in_hide('subcut', 'true').in_hide('type_save', "save")."</form>\n".
    in_hide('crop_img', "{$adminfile}?module={$main->module}&amp;do=crop_img");
}

function admin_save_category_albom(){
    global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('treedb');
    $cat_id = (!empty($_POST['key_link'])) ? $_POST['key_link'] : cyr2lat($_POST['title']);
    $treedb = new treedb(CAT);
    $imgt = isset($_POST['image']) ? $_POST['image'] : '';
    $_POST['image'] = basename($imgt);
    if($_POST['type_save']=='cat' OR $_POST['type_save']=='edit') $msg = error_empty(array('title'), array('cat_title_err'));
    elseif($_POST['type_save']=='subcat') $msg = error_empty(array('title', 'cid'), array('cat_title_err', 'cat_case_error'));
    if(empty($msg)){
        if($_POST['type_save']=='cat') $cid = $treedb->append('', "INSERT INTO ".CAT." (cat_id, title, module, description, image, tree) VALUES ('{$cat_id}', '{$_POST['title']}', '{$main->module}', '{$_POST['description']}', '{$imgt}', '{IDTREE}')");
        elseif($_POST['type_save']=='subcat'){
            $info_tree = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".CAT." WHERE cid='".addslashes($_POST['cid'])."'"));
            $cid = $treedb->append($info_tree['tree'], "INSERT INTO ".CAT." (cat_id, title, module, description, image, tree) VALUES ('{$cat_id}', '{$_POST['title']}', '{$main->module}', '{$_POST['description']}', '{$imgt}', '{IDTREE}')");
        } elseif($_POST['type_save']=='save'){
            sql_update(array(
                'cat_id'        => $cat_id,
                'title'         => $_POST['title'],
                'description'   => $_POST['description'],
            ), CAT, "cid='{$_GET['id']}'");
        }
        if(!empty($_POST['image'])){
            if($_POST['type_save']!='save'){
                $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".CAT." WHERE tree='{$cid}'"));
                $filecid = $info['cid'];
            } else $filecid = $_GET['id'];
            $ext = get_type_file($_POST['image']);
            sql_update(array('image' => "{$filecid}.{$ext}"), CAT, "cid='{$filecid}'");
            rename($imgt, str_replace($_POST['image'], '', $imgt)."{$filecid}.".$ext);
        }
        redirect("{$adminfile}?module={$main->module}&do=category");
    } else {
        if($_POST['type_save']=='subcat') admin_add_cubcat_albom($msg);
        if($_POST['type_save']=='cat') admin_add_category_albom($msg);
        if($_POST['type_save']=='save') admin_edit_category_albom($msg);
    }
}

function admin_delete_image($image, $dir){
global $albom;
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists("{$dir}{$image}")) unlink("{$dir}{$image}");
    if(file_exists("{$dir}macro-{$image}")) unlink("{$dir}macro-{$image}");
    if(file_exists("{$dir}mini-{$image}")) unlink("{$dir}mini-{$image}");
    if(file_exists("{$dir}orig-{$image}")) unlink("{$dir}orig-{$image}");
    if(file_exists("{$dir}temp-{$image}")) unlink("{$dir}temp-{$image}");
}

function admin_delete_category_albom(){
global $main, $adminfile, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".CAT." WHERE cid='{$_GET['id']}'");
    main::init_class('treedb');
    $treedb = new treedb(CAT);
    if($main->db->sql_numrows($result)>0){        
        $info = $main->db->sql_fetchrow($result);
        if(!empty($info['image']) AND file_exists("{$albom['directory']}category/{$info['image']}")) unlink("{$albom['directory']}category/{$info['image']}");
        $search_images = $main->db->sql_query("SELECT * FROM ".ALBOM." WHERE cid='{$info['cid']}'");
        if($main->db->sql_numrows($search_images)>0) {
            while(($row = $main->db->sql_fetchrow($search_images))) {
                if(file_exists("{$albom['directory']}{$row['time']}")) {
                    admin_delete_image($row['image'], "{$albom['directory']}{$row['time']}/");
                    $main->db->sql_query("DELETE FROM ".ALBOM." WHERE id='{$row['id']}'");
                }
            }
        }
        $search_subcat = $main->db->sql_query("SELECT *,ROUND(LENGTH(t.tree)/2) AS level,SUBSTR(t.tree,1,LENGTH(t.tree)-2) AS parent FROM ".CAT." t WHERE t.tree LIKE (SELECT CONCAT(l.tree,'__%') FROM ".CAT." l WHERE l.cid={$info['cid']}) ORDER BY t.tree");
        if($main->db->sql_numrows($search_subcat)>0){
            while(($row = $main->db->sql_fetchrow($search_subcat))){
                if(!empty($info['image']) AND file_exists("{$albom['directory']}category/{$row['image']}")) unlink("{$albom['directory']}category/{$row['image']}");
                $search_images = $main->db->sql_query("SELECT * FROM ".ALBOM." WHERE cid='{$row['cid']}'");
                if($main->db->sql_numrows($search_images)>0) {
                    while(($row2 = $main->db->sql_fetchrow($search_images))) {
                        if(file_exists("{$albom['directory']}{$row2['time']}")) {
                            admin_delete_image($row2['image'], "{$albom['directory']}{$row2['time']}/");
                            $main->db->sql_query("DELETE FROM ".ALBOM." WHERE id='{$row2['id']}'");
                        }
                    }
                }
            }
        }
        $treedb->delete($info['tree']);
    }    
    if(is_ajax()) admin_list_category();
    else redirect("{$adminfile}?module={$main->module}&do=category");
}

function admin_delete_albom(){
global $main, $adminfile, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".ALBOM." WHERE id='{$_GET['id']}'");
    if($main->db->sql_numrows($result)>0){
        $info = $main->db->sql_fetchrow($result);
        $main->db->sql_query("DELETE FROM ".ALBOM." WHERE id='{$_GET['id']}'");
        admin_delete_image($info['image'], "{$albom['directory']}{$info['time']}/");
        if(dir_file_count("{$albom['directory']}{$info['time']}/")==0) remove_dir("{$albom['directory']}{$info['time']}/");
    }
    if(is_ajax()) admin_main_albom();
    else redirect("{$adminfile}?module={$main->module}");
}

function admin_edit_photo_albom($msg=""){
global $main, $adminfile, $albom;    
    if(hook_check(__FUNCTION__)) return hook();
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".ALBOM." WHERE id='{$_GET['id']}'"));
    if(!empty($msg)) warning($msg);
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['photo_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $info['title'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input' align='left'>".get_cat($info['cid'], $main->module)."</td></tr>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".in_area("description", "textarea", 3, $info['description'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'><img src='{$albom['directory']}{$info['time']}/mini-{$info['image']}' alt='{$info['title']}' /></td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>";
}

function admin_save_edit_photo_albom(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = empty($_POST['title']) ? $main->lang['err_photo_name'] : '';
    if(empty($msg)){
        sql_update(array(
            'title'         => $_POST['title'],
            'cid'           => $_POST['cid'],
            'description'   => $_POST['description'],
        ), ALBOM, "id='{$_GET['id']}'");
        redirect("{$adminfile}?module={$main->module}");
    } else admin_edit_photo_albom($msg);
}

function switch_admin_albom(){
   global $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "config": admin_config_albom(); break;
         case "add": global_add_albom(); break;
         case "edit": admin_edit_photo_albom(); break;
         case "save_edit": admin_save_edit_photo_albom(); break;
         case "save": global_save_photos_albom(); break;
         case "delete": admin_delete_albom(); break;
         case "save_conf": admin_saves_albom(); break;
         case "download_read": admin_download_read_albom(); break;
         case "remove_dir": admin_remove_dir_albom(); break;
         case "crop_img": global_crop_image_albom(); break;
         case "resize_image": global_resize_image_albom(); break;
         case "delete_photo": global_delete_photo_albom(); break;
         case "update_processed": global_update_processed_albom(); break;
         case "category": admin_list_category(); break;
         case "add_category": admin_add_category_albom(); break;
         case "save_category": admin_save_category_albom(); break;
         case "edit_category": admin_edit_category_albom(); break;
         case "delete_category": admin_delete_category_albom(); break;
         case "add_cubcat": admin_add_cubcat_albom(); break;
         case "save_cubcat": admin_save_category_albom(); break;
         default: admin_main_albom(); break;
      }
   } elseif($break_load==false) admin_main_albom();
}
switch_admin_albom();
?>
