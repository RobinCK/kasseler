<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi;
$navi = navi(array(), false, false);

function main_contact($msg=""){
global $main, $navi, $contact_form;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_home()) echo $navi;
    if(!empty($msg)) warning($msg);
    open();
    main::add2script("$.krReady(function(){jQuery('.form textarea').autoResize();})", false);
    echo "<form method='post' enctype='multipart/form-data' action='".$main->url(array('module' => $main->module, 'do' => 'send'))."'><table class='form' align='center' id='form_{$main->module}'>\n";
    foreach($contact_form as $row){
        $in = "";
        if(!empty($row['option'])) foreach($row['option'] as $key => $value)
        {
           if(is_array($value)) {
              foreach ($value as $k1 => $v1) $value[$k1]=stripcslashes($v1);
              $row['option'][$key]=$value;
           } else $row['option'][$key] = stripcslashes($value);
        }
        $def_value=is_numeric($row['default'])?intval($row['default']):(isset($main->user[$row['default']])?$main->user[$row['default']]:stripcslashes($row['default']));
        switch($row['type']){
            case "text": $in = in_text($row['name'], $row['class'], $def_value); break;
            case "textarea": $in = in_area($row['name'], $row['class'], 5, $def_value); break;
            case "select": $in = in_sels($row['name'], $row['option'], $row['class'], $def_value); break;
            case "checkbox": $in = in_chck($row['name'], $row['class'], stripcslashes($row['default'])); break;
            case "file": $in = "<input type='file' name='{$row['name']}' class='{$row['class']}' />"; break;
            case "radio": 
                $i = 0;
                if($row['default'] == false OR $row['default'] == "") $row['default'] = 0;
                foreach($row['option'] as $arr){
                    $in .= in_radio($row['name'], $arr['value'], $arr['title'], $arr['value']."_id", ($row['default']==$i)?true:false)."<br/> "; 
                    $i++;
                }
            break;
        }
        echo "<tr class='row_tr'><td class='form_text'>".stripcslashes(parse_mylang($row['title'])).":".($row['must']==true?"<span class='star'>*</span>":"")."</td><td class='form_input'>{$in}</td></tr>\n";
    }
    echo captcha()."<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n</table></form><br />\n";
    close();
    //Выполняем удаление файлов
    $files = array();
    if(!file_exists("uploads/contact/")) mkdir("uploads/contact/");
    if(($handle = opendir("uploads/contact/"))){
        while(false !== ($file = readdir($handle))) if($file!='.svn' AND $file!='.' AND $file!='..') $files[] = $file;
        closedir($handle);
    }
    foreach($files as $f) if(file_exists('uploads/contact/'.$f)) unlink('uploads/contact/'.$f);
}

function send_contact(){
global $main, $contact_form, $contact;
    if(hook_check(__FUNCTION__)) return hook();
    $filtered = array();
    foreach($contact_form as $row) if($row['type']!='file') $filtered[] = $row['name'];
    filter_arr($filtered, POST, TAGS);
    $msg = check_captcha();
    if(empty($msg)){
        //Создаем таблицу формы
        $br = $main->config['type_emeils']=='text/html' ? "<br />" : "\n";
        $content = "";
        foreach($contact_form as $row){
            if($row['type']=='file') continue;
            $value = "";
            if(isset($_POST[$row['name']]) OR (!isset($_POST[$row['name']]) AND $row['type']=='checkbox')){
                if($row['type']=='select') $value = stripcslashes($row['option'][intval($_POST[$row['name']])]);
                elseif($row['type']=='checkbox') $value = isset($_POST[$row['name']]) ? 'yes' : 'no';
                else $value = $_POST[$row['name']];
                if($row['must']==true  AND (!isset($_POST[$row['name']]) OR ($_POST[$row['name']]=="" AND $row['type']!='select'))) {
                    $msg = $main->lang['no_parametrs']." ".$row['name'];
                    break;
                }
                $content .= ($main->config['type_emeils']=='text/html'?"<b>".stripcslashes(parse_mylang($row['title']))."</b>":stripcslashes(parse_mylang($row['title']))).": ".stripcslashes($value)."{$br}";
            }
        }
        $content .= $main->config['type_emeils']=='text/html' ? "<hr />\n" : "\n";
        $user = $main->config['type_emeils']=='text/html' ? ($main->user['uid']!=-1 ? "<a href='http://".get_host_name()."/index.php?module=account&amp;do=user&amp;id={$main->user['user_id']}'>{$main->user['user_name']}</a>":$main->user['user_name']) : $main->user['user_name'];
        $ipp = $main->config['type_emeils']=='text/html' ? "<a href='http://www.kasseler-cms.net/index.php?module=whois&ip={$main->ip}'>{$main->ip}</a>" : $main->ip;
        $content .= ($main->config['type_emeils']=='text/html'?"<b>User name</b>":'User name').": {$user}{$br}";
        $content .= ($main->config['type_emeils']=='text/html'?"<b>IP</b>":'IP').": {$ipp}{$br}";
        $content .= ($main->config['type_emeils']=='text/html'?"<b>Date</b>":'Date').": ".kr_date("Y-m-d H:i:s")."{$br}";
        //Выполняем загрузку файлов
        $files_loaded = array();
        if(isset($_FILES["fileloader"]) AND !empty($_FILES["fileloader"])){
            //Подключаем Class загрузки файлов
            main::init_class('uploader');
            //Перестраиваем $_FILES
            $files_arr = array();
            foreach($_FILES['fileloader'] as $var_name=>$var_value){
                foreach($var_value as $var_name2=>$var_value2){
                    $files_arr[$var_name2][$var_name] = $var_value2;
                }
            }
            //Проверяем и сохраняем файлы
            foreach($files_arr as $key){
                if(empty($key['name'])) continue;
                $new_name = get_name_file(cyr2lat($key['name']));
                $exp = get_type_file(mb_strtolower($key['name']));
                $atrib = array(
                    'dir'   => "uploads/contact/",
                    'file'  => $key,
                    'size'  => $contact['attaching_files_size'],
                    'type'  => explode(',', $contact['attaching_files_type']),
                    'name'  => $new_name
                );
                if(preg_match('/jpg|jpeg|gif|png/is', $exp)) $atrib = $atrib + array('width' => $contact['max_image_width'], 'height' => $contact['max_image_height']);
                if(!isset($attach) OR !is_object($attach)) $attach = new upload($atrib);
                else $attach->upload($atrib);
                if(!$attach->error) $files_loaded[] = $attach->file;
                else $msg = $attach->get_error_msg();
            }
        }
        if(empty($msg)){
            add_points($main->points['contact']);
            $att = array();
            if(!empty($files_loaded)) {
                foreach($files_loaded as $f) {
                    if(is_file('uploads/contact/'.$f)) $att[] = 'uploads/contact/'.$f;
                }
            }
            send_mail($main->config['contact_mail'], 'Support', $main->config['sends_mail'], 'noreply', "{$main->title} - {$main->config['site_name_for_mail']}", $content, array(), $att);
            if(!empty($files_loaded)){
                //Выполняем удаление файлов
                $files = array();
                if(($handle = opendir("uploads/contact/"))){
                    while(false !== ($file = readdir($handle))) if($file!='.svn' AND $file!='.' AND $file!='..') $files[] = $file;
                    closedir($handle);
                }
                foreach($files as $f) if(file_exists('uploads/contact/'.$f)) unlink('uploads/contact/'.$f);
            }
            meta_refresh(3, $main->url(array('module' => $main->module)), $main->lang['contact_send']);
        } else main_contact($msg);
    } else main_contact($msg);
}
function switch_module_contact(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "send": send_contact(); break;
         default: kr_http_ereor_logs("404"); break;
      }
   } else main_contact();
}
switch_module_contact();
?>