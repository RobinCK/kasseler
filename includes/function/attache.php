<?php
/**
* Файл функций Attach
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/attache.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция чтения каталога загрузки
* 
* @param string $dir
* @return string
*/
function update_list_files($dir, $options=true){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_ico_image');
    
    //Ограничиваем каталог
    $list_upload = '';
    if(mb_strpos($dir, 'uploads/') === false) $dir = 'uploads/';
    //Если нет каталога загрузки файлов, создаем его
    if(file_exists($dir)) {
        if(empty($_POST)&&preg_match("%(?i).*?".USER_FOLDER."/%", $dir)){
            remove_dir($dir);
            mkdir($dir, 0777);
        }
    } else if(!file_exists($dir)) mkdir($dir, 0777);
    //Проверяем наличие каталога
    if(file_exists($dir)){
        //Открываем каталог
        $attache_dir = opendir($dir);
        $files_arr = array('file'=>array(), 'dir'=>array());
        $row = "up_row1"; $i = 1; $list_upload = "";
        //Проверяем, является ли каталог корневым 
        if($dir!='uploads/' AND is_support()){
            $dir_arr = explode("/", $dir);
            $backdir = "";
            for($y=0;$y<count($dir_arr)-2;$y++) $backdir .= $dir_arr[$y]."/";
            $list_upload .= "<tr class='up_back'><td class='pointer' colspan='3' align='left' onclick=\"update_upload('{$backdir}');\"><img src='includes/images/swfupload/folder_up.png' title='{$main->lang['up']}' alt='{$main->lang['up']}' /> ..</td></tr>\n";
        }   
        //Читаем каталог
        while(($file = readdir($attache_dir))){
            if(is_dir($dir.$file)) $files_arr['dir'][] = $file;
            else $files_arr['file'][] = $file;
        }
        //Закрываем каталог
        closedir($attache_dir);
        //Сортируем файлы
        sort($files_arr['file']);
        //Сортируем каталоги
        sort($files_arr['dir']);
        //Выводим каталоги
        foreach($files_arr['dir'] as $file) {
            $buttons = "";
            $buttons .= "<td><img class='pointer' onclick=\"rename('{$dir}', '{$file}')\" src='includes/images/swfupload/rename.png' alt='{$main->lang['rename']}' title='{$main->lang['rename']}' /></td>";
            $buttons .= "<td><img class='pointer' onclick=\"delete_attach('index.php?ajaxed=delete_attach&amp;dir={$dir}&amp;file={$file}', '{$main->lang['realdelete']} \\'\\'{$file}\\'\\'.');\" src='includes/images/swfupload/delete.png' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></td>";
            if($file!='.' AND $file!='..') $list_upload .= "<tr class='{$row}'><td align='left' class='pointer' onclick=\"update_upload('{$dir}{$file}/');\"><img src='includes/images/swfupload/dir.png' alt='' align='left' />&nbsp;&nbsp;{$file}</td><td width='96' align='right'><table class='up_butons' cellpadding='0' cellspacing='0'><tr>{$buttons}</tr></table></td></tr>\n";
            $row = ($row=="up_row1") ? "up_row2" : "up_row1";
        }
        //Выводим файлы
        foreach($files_arr['file'] as $file) {
            $buttons = "";            
            if($options==true){
                if(preg_match('/mini\-(.*?).(jpeg|jpg|png|gif)$/is', $file)) continue;
                if(preg_match('/(.*?).(jpeg|jpg|png|gif)$/is', $file)) $buttons = (file_exists($dir."mini-".$file)) ? "<td><img onclick=\"window.insert_miniimg_file('{$dir}mini-{$file}');\" class='pointer' src='includes/images/swfupload/mini_image.png' alt='{$main->lang['paste_mini_image']}' title='{$main->lang['paste_mini_image']}' /></td><td><img onclick=\"window.insert_img_file('/{$dir}{$file}');\" class='pointer' src='includes/images/swfupload/image.png' alt='{$main->lang['paste_image']}' title='{$main->lang['paste_image']}' /></td>" : "<td><img onclick=\"window.insert_img_file('/{$dir}{$file}');\" class='pointer' src='includes/images/swfupload/image.png' alt='{$main->lang['paste_image']}' title='{$main->lang['paste_image']}' /></td>";
                $buttons .= "<td><img class='pointer' onclick=\"rename('{$dir}', '{$file}')\" src='includes/images/swfupload/rename.png' alt='{$main->lang['rename']}' title='{$main->lang['rename']}' /></td>";
                $buttons .= "<td><img class='pointer' onclick=\"alert('http://".get_host_name()."/{$dir}{$file}')\" src='includes/images/swfupload/info.png' alt='{$main->lang['info_file']}' title='{$main->lang['info_file']}' /></td>";
                $buttons .= "<td><img class='pointer' onclick=\"window.insert_attach_file('/{$dir}{$file}')\" src='includes/images/swfupload/add.png' alt='{$main->lang['paste_file']}' title='{$main->lang['paste_file']}' /></td>";
                $buttons .= "<td><img class='pointer' onclick=\"delete_attach('index.php?ajaxed=delete_attach&amp;dir={$dir}&amp;file={$file}', '{$main->lang['realdelete']} \\'\\'{$file}\\'\\'.');\" src='includes/images/swfupload/delete.png' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></td>";
            } else $buttons .= "<td><img class='pointer' onclick=\"rename('{$dir}', '{$file}')\" src='includes/images/swfupload/rename.png' alt='{$main->lang['rename']}' title='{$main->lang['rename']}' /></td><td><img class='pointer' onclick=\"delete_attach('index.php?ajaxed=delete_attach&amp;dir={$dir}&amp;file={$file}', '{$main->lang['realdelete']} \\'\\'{$file}\\'\\'.');\" src='includes/images/swfupload/delete.png' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></td>";
            if($file!='.' AND $file!='..') $list_upload .= "<tr class='{$row}'><td align='left' class='pointer' onclick=\"location.href='http://".get_host_name()."/{$dir}{$file}'\">".get_ico_image(get_type_file(mb_strtolower($file)), $dir, $file) ."&nbsp;&nbsp;{$file}</td><td width='96'><table align='right' class='up_butons' cellpadding='0' cellspacing='0'><tr>{$buttons}</tr></table></td></tr>\n";
            $row = ($row=="up_row1") ? "up_row2" : "up_row1";
        }        
        $uprd = in_hide('update_upload_options', $options?'true':'false')."<table width='100%' cellpadding='2' cellspacing='0' class='up_read_dir'><tr><td align='left'><span class='dir_string'>{$main->lang['dir']}</span>: {$dir}</td><td align='right' style='padding-right: 8px;'>".(is_support()?"<a href='#' class='create_dir' onclick='return create_dir();'><b>{$main->lang['create_dir']}</b></a>":"&nbsp;")."</td></tr></table>";
        if(!empty($list_upload)){
            $list_upload = "<div style='padding: 5px;'><table width='100%' cellpadding='3' cellspacing='0' id='up_table' class='up_table'>\n{$list_upload}";
            $list_upload .= ($i!=1) ? "</table><br />\n" : "<tr><td></td></tr></table></div>\n";
        }
    }
    return $uprd.$list_upload;
}

/**
* Функция конфигурации SWFUpload
* 
* @param string $upload_file
* @param string $types
* @param int $size
* @param int $limit
*/
function SWFUpload($upload_file, $types="*.*", $size=1024, $limit=10){
global $tpl_create, $main;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_mask_types');
    main::add2script("includes/javascript/jquery/jquery.swfupload.js", true);
    $s = "<script type='text/javascript'>
    <!--
    setTimeout(function(){
       KR_AJAX.swfupload({
            upload_url: '/".str_replace("amp;", "", $upload_file)."',
            post_params: {
                'PHPSESSID' : '".session_id()."',
                'uname':'{$main->user['user_name']}',
                'secID':(jsSecretID?jsSecretID:'')
           },
            file_size_limit : '{$size}',
            file_types : '".get_mask_types($types)."',
            file_types_description : '".($types!='*'?str_replace(",", ";", mb_strtoupper($types)):'All')."',
            file_upload_limit : {$limit},
            button_image_url : '".$main->config['http_home_url']."includes/images/pixel.gif'
        });
    }, 700);
    function removeFunc(funcName){
      window[ funcName ] = undefined;
      try {
         delete window[ funcName ];
      } catch(e){}
    }
    uploadpost=function(){
      swfu.addPostParam('uname','{$main->user['user_id']}');
      removeFunc('uploadpost');
    }
    //-->
    </script>";
    return '<div id="Buttons"><span id="UploadPhotos"><input type="button" id="Progress" /><i id="fAddPhotos"></i><input type="button" id="AddPhotos" value="'.$main->lang['upload'].'" /></span></div>'.$s;
}

/**
* Функция загрузки прикрепленных файлов
* 
* @param array $conf
* @return void
*/
function upload_attach($conf, $inputname = "Filedata"){
global $main, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($userinfo)) {
       if(!isset($_POST['uname'])) return false;
       $result = $main->db->sql_query("SELECT * FROM ".SESSIONS." WHERE uname='{$_POST['uname']}' AND actives='y'");
       if($main->db->sql_numrows($result)==0) return false;
       $sid = $main->db->sql_fetchrow($result);
       $user_info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".USERS." WHERE user_name='{$sid['uname']}'")); 
    } else $user_info = $userinfo;
    //Проверяем возможность прикрепления файлов
    if($conf['attaching']!=ENABLED AND !is_support()) return false;
    //Проверяем сессию загружаемого файла
    //if(session_id()!=$_POST['PHPSESSID']) return false;
    //Подключаем модуль загрузки файлов    
    main::init_class('uploader');
    //Определяем каталог для загрузки файлов
    $uploaddir = (isset($_SESSION['uploaddir'])) ? $_SESSION['uploaddir'] : $conf['directory']."filedata-".$user_info['user_id']."/";
    if(isset($_FILES[$inputname])){
        //Создаем каталог для загрузки файлов, если его нет
        if(!file_exists($uploaddir)) mkdir($uploaddir, 0777);
        //Генерируем новое имя файла
        $new_name = cyr2lat(get_name_file($_FILES[$inputname]['name']));
        //Создаем массив параметров для загрузки файлов
        $atrib = array(
            'dir'   => $uploaddir,
            'file'  => $_FILES[$inputname],
            'size'  => $conf['attaching_files_size'],
            'type'  => explode(",", $conf['attaching_files_type']),
            'name'  => $new_name
        );   
        //Определяем тип загружаемого файла
        $exp = get_type_file(mb_strtolower($_FILES[$inputname]['name']));
        //Если картинка добавляем к параметрам ограничения размера
        if(preg_match('/jpg|jpeg|gif|png/is', $exp)){
            $image_type = true;
            $atrib = $atrib + array('width' => $conf['max_image_width'], 'height' => $conf['max_image_height']);
        } else $image_type = false;
        //Загружаем файл
        $attach = new upload($atrib);
        if($image_type AND !$attach->error){
            //Если файл загружен, подключаем класс работы с графикой
            main::init_class('graphics');
            //Создаем объект класса
            $graphics = new graphics(array(
                'name'                  => $new_name.".".$exp,
                'new_name'              => $new_name,
                'width'                 => $conf['miniature_image_width'],
                'height'                => $conf['miniature_image_height'],
                'watermark'             => $main->config['mark_img'],
                'watermark_position'    => $main->config['mark'],
                'directory_image'       => $uploaddir,
                'directory_new_image'   => $uploaddir
            ));
            //Выполняем налаживание водяного знака
            $graphics->watermark();
            //Возвращаем информацию о изображении
            $size_image = getimagesize($uploaddir.$attach->file);
            //Выполняем преобразования изображения
            if($size_image[0]>$conf['miniature_image_width'] OR $size_image[1]>$conf['miniature_image_height']) $graphics->resize_image();
        }        
        if(!$attach->error) {
            $attach_file = $attach->file;
            //Сохраняем информацию о загрузки файла
            sql_insert(array(
                'module'  => $main->module,
                'path'    => $uploaddir,
                'file'    => $attach_file,
                'user_id' => $user_info['uid'],
                'date'    => kr_date("Y-m-d H:i:s")
            ), ATTACH);
        //Ошибка загрузки файла
        } else header("HTTP/1.1 50{$attach->error_number} File Upload Error");
        $attache_dir = opendir($uploaddir);
        $files = array();
        $i = 1; $list_upload = "";
        while(($file = readdir($attache_dir))){
           if(!is_dir($uploaddir.$file)){$files[] = $file;}
        }
        closedir($attache_dir);
        foreach ($files as $key => $value) {
           if(substr($value,0,5)=='mini-'){
              $fv = substr($value,6);
              $find = array_search($fv, $files);
              if($find!==false) unset($files[$key]);
           } else {
              $find = array_search('mini-'.$value, $files);
              if($find!==false) unset($files[$find]);
           }
        }
        sort($files);
        echo json_encode($files);
        kr_exit();
    } else header("HTTP/1.1 500 File Upload Error");
    return true;
}
?>