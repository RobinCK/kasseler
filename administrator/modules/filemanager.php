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

main::init_function('attache');

function main_filemanager($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_perms', 'get_chmod', 'get_ico_image');
    
    clearstatcache();
    $dir = (isset($_POST['dir']) AND !empty($_POST['dir'])) ? $_POST['dir'] : './';
    $dir = ($dir=='./' AND isset($_SESSION['dir']) AND !empty($_SESSION['dir']) AND !isset($_POST['dir'])) ? $_SESSION['dir'] : $dir;
    $is_writable = is_writable($dir);
    $_SESSION['dir'] = $_SESSION['uploaddir'] = $dir;
    $list_upload = "";
    echo (!is_ajax()) ? "<script type='text/javascript'>
    <!--
    function open_dir(dir){showuploadereffect();haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}', animation:false}, {'dir':dir}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}    
    function delete_file(file){if(confirm('{$main->lang['realdelete']}')) {showuploadereffect(); haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=delete', animation:false}, {'file':file}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});} else return false;}           
    function copy_file(file){haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=copy', animation:false}, {'file':file}, {});}    
    function rename_file(dir, file){var dialog = prompt(window.js_lang['new_name'], file); if (dialog && dialog!=file){haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=rename', animation:false}, {'dir':dir, 'file':file, 'new_name':dialog}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}}            
    function paste_file(dir){showuploadereffect(); haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=paste', animation:false}, {'dir':dir}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}    
    function create_file(dir){var dialog = prompt('{$main->lang['input_filename']}', ''); if (dialog){showuploadereffect(); haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=newfile', animation:false}, {'dir':dir, 'filename':dialog}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}}    
    function create_folder(dir){var dialog = prompt('{$main->lang['input_fildername']}', ''); if (dialog){showuploadereffect(); haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}&do=newfolder', animation:false}, {'dir':dir, 'foldername':dialog}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}}
    update_upload = function(dir){document.getElementById('upload_progress').innerHTML = ''; document.getElementById('attache').style.display='none'; showuploadereffect(); haja({elm:'filemanager_id', action:'{$adminfile}?module={$main->module}', animation:false}, {}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}
    // -->
    </script><div id='filemanager_id'>" : "";
    if(!empty($msg)) warning($msg);
    if(file_exists($dir)){
        //Открываем каталог
        $files_arr = array('file'=>array(), 'dir'=>array());
        $row = "up_row1"; $i = 1; $list_upload = "";
        //Проверяем, является ли каталог корневым 
        if($dir!='./'){
            $dir_arr = explode("/", $dir);
            $backdir = "";
            for($y=0;$y<count($dir_arr)-2;$y++) $backdir .= $dir_arr[$y]."/";
            $list_upload .= "<tr class='up_back'><td class='pointer' colspan='3' onclick=\"open_dir('{$backdir}')\" align='left'><img src='includes/images/swfupload/folder_up.png' title='{$main->lang['up']}' alt='{$main->lang['up']}' /> ..</td></tr>\n";
        }   
        //Читаем каталог
        $attache_dir = opendir($dir);
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
        foreach($files_arr['dir'] as $file){
            $buttons = "<td><img class='pointer' onclick=\"rename_file('{$dir}', '{$file}');\" src='includes/images/swfupload/drive_rename.png' alt='{$main->lang['rename']}' title='{$main->lang['rename']}' /></td>".            
            "<td><img class='pointer' onclick=\"delete_file('{$dir}{$file}')\" src='includes/images/swfupload/folder_delete.png' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></td>";
            if($file!='.' AND $file!='..') $list_upload .= "<tr class='{$row}'><td align='left' class='pointer' onclick=\"open_dir('{$dir}{$file}/');\"><img src='includes/images/swfupload/dir.png' alt='' align='left' />&nbsp;&nbsp;{$file}</td><td align='center'>".date('Y-m-d H:i:s', filemtime($dir.$file))."</td><td align='center'>-</td><td align='center'><a href='{$adminfile}?module={$main->module}&amp;do=change_chmod&amp;file={$dir}{$file}' title='{$main->lang['change_chmod']}'>".get_chmod($dir.$file, 1)."</a></td><td align='center'>".get_perms($dir.$file)."</td><td align='right'><table class='up_butons' cellpadding='0' cellspacing='0'><tr>{$buttons}</tr></table></td></tr>\n";
            $row = ($row=="up_row1") ? "up_row2" : "up_row1";
        }
        //Выводим файлы
        foreach($files_arr['file'] as $file) {
            $path = $dir.$file;
            $buttons = "<td><img class='pointer' onclick=\"rename_file('{$dir}', '{$file}');\" src='includes/images/swfupload/drive_rename.png' alt='{$main->lang['rename']}' title='{$main->lang['rename']}' /></td>".
            "<td><img class='pointer' onclick=\"copy_file('{$dir}{$file}');\" src='includes/images/swfupload/copy.png' alt='{$main->lang['copy']}' title='{$main->lang['copy']}' /></td>".
            "<td><img class='pointer' onclick=\"location.href='{$adminfile}?module={$main->module}&amp;do=download&amp;file={$dir}{$file}'\" src='includes/images/swfupload/download.png' alt='{$main->lang['download']}' title='{$main->lang['download']}' /></td>".
            "<td><img class='pointer' onclick=\"location.href='{$adminfile}?module={$main->module}&amp;do=edit_file&amp;file={$dir}{$file}'\" src='includes/images/swfupload/edit.png' alt='{$main->lang['edit']}' title='{$main->lang['edit']}' /></td>".
            "<td><img class='pointer' onclick=\"delete_file('{$dir}{$file}')\" src='includes/images/swfupload/remove.png' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></td>";            
            if($file!='.' AND $file!='..') $list_upload .= "<tr class='{$row}'><td align='left' class='pointer' onclick=\"location.href='{$dir}{$file}'\">".get_ico_image(get_type_file(mb_strtolower($file)), $dir, $file) ."&nbsp;&nbsp;{$file}</td><td align='center'>".date('Y-m-d H:i:s', filemtime($dir.$file))."</td><td align='center'>".get_size(filesize($dir.$file))."</td><td align='center'><a href='{$adminfile}?module={$main->module}&amp;do=change_chmod&amp;file={$dir}{$file}' title='{$main->lang['change_chmod']}'>".get_chmod($dir.$file, 1)."</a></td><td align='center'>".get_perms($dir.$file)."</td><td><table align='right' class='up_butons' cellpadding='0' cellspacing='0'><tr>{$buttons}</tr></table></td></tr>\n";
            $row = ($row=="up_row1") ? "up_row2" : "up_row1";
        }        
        $path_arr = explode('/', str_replace('./', '', $dir));
        if(empty($path_arr[0])) unset($path_arr[0]);
        $_dir = $link_path = "";
        foreach($path_arr as $val){
            $_dir .= "{$val}/";
            $link_path .= (!empty($val)) ? "<a href='#' onclick=\"open_dir('{$_dir}'); return false;\">{$val}</a> / " : "";
        }
        $link_path = "<a href='#' onclick=\"open_dir(''); return false;\">/</a>  ".$link_path;
        $uprd = in_hide('uploaddir', $dir)."<table width='100%' cellpadding='2' cellspacing='0' class='up_read_dir'><tr>
        <td align='left'><span class='dir_string'>{$main->lang['dir']}</span>: {$link_path} </td>
        <td align='right'>".($is_writable?$main->lang['is_writable']:$main->lang['is_not_writable']).($is_writable?"&nbsp;&nbsp;&nbsp;<img class='pointer' onclick=\"paste_file('{$dir}');\" src='includes/images/swfupload/paste.png' alt='{$main->lang['paste']}' title='{$main->lang['paste']}' /> <img class='pointer' onclick=\"create_file('{$dir}');\" src='includes/images/swfupload/file_add.png' alt='{$main->lang['newfile']}' title='{$main->lang['newfile']}' /> <img class='pointer' onclick=\"create_folder('{$dir}');\" src='includes/images/swfupload/folder_add.png' alt='{$main->lang['newfolder']}' title='{$main->lang['newfolder']}' /> <img class='pointer' onclick=\"$('#attache').toggle(); return false;\" src='includes/images/swfupload/upload.png' alt='{$main->lang['upload_file']}' title='{$main->lang['upload_file']}' />":"")."</td></tr></table><hr />";
        $list_upload = $uprd."<div style='padding: 5px;'><div class='flash' id='upload_progress'></div><div id='attache' style='display: none;'>".SWFUpload("{$adminfile}?module={$main->module}&amp;do=upload", '*', 104856600, 1000)."</div><table width='100%' cellpadding='3' cellspacing='0' id='up_table' class='up_table'><tr><th>{$main->lang['name']}</th><th width='120'>{$main->lang['mod_date']}</th><th width='100'>{$main->lang['file_size']}</th><th width='40'>CHMOD</th><th width='100'>PERMS</th><th width='50'>{$main->lang['functions']}</th></tr>\n{$list_upload}";
        $list_upload .= ($i!=1) ? "</table><br />\n" : "<tr><td></td></tr></table></div>\n";
        echo $list_upload;
    }    
    echo !is_ajax() ? "</div>" : "";
}

function change_chmod_filemanager(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('get_chmod');
    ?>
    <script type="text/javascript">
        function truefalse(ft){return (ft==true?1:0)}
        function eslafeurt(ee){return (ee=1?true:false)}
        function per(){a=(4*truefalse(document.b.b9.checked)+2*truefalse(document.b.b8.checked)+truefalse(document.b.b7.checked))+""+(4*truefalse(document.b.b6.checked)+2*truefalse(document.b.b5.checked)+truefalse(document.b.b4.checked))+""+(4*truefalse(document.b.b3.checked)+2*truefalse(document.b.b2.checked)+truefalse(document.b.b1.checked)); document.b.num.value=a;}
        function rep(){
            var preg=/^[0-7]{0,3}$/
            if (!preg.test(document.b.num.value)){
                <?php echo "alert('{$main->lang['enter_octal_number']}');"; ?>
                return 0;
            }
            a=parseInt(document.b.num.value,8); ab=a%2;
            document.b.b1.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b2.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b3.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b4.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b5.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b6.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b7.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b8.checked=ab; a=(a-ab)/2; ab=a%2;
            document.b.b9.checked=ab;
        }
    </script>
    <?php
    $chmod = get_chmod($_GET['file'], 1);
    echo (is_dir($_GET['file'])) ? "{$main->lang['dir']}: <a href='{$adminfile}?module={$main->module}'>".str_replace('/', ' / ', $_GET['file'])."</a><hr />" : "{$main->lang['file']}: <a href='{$adminfile}?module={$main->module}'>".str_replace('/', ' / ', $_GET['file'])."</a><hr />";
    echo "<form name='b' id='b' action='{$adminfile}?module={$main->module}&amp;do=save_chmod&amp;file={$_GET['file']}' method='post'>
    <table align='center'><tr><td>
    <fieldset>
        <legend>{$main->lang['changing_access_rights']}</legend>
    <fieldset>
    <legend>{$main->lang['owner']}</legend>
      <input type='checkbox' id='b9' onclick='per()' /> {$main->lang['reading']}<br />
      <input type='checkbox' id='b8' onclick='per()' /> {$main->lang['record']}<br />
      <input type='checkbox' id='b7' onclick='per()' /> {$main->lang['execution']}
    </fieldset>
    <fieldset>
    <legend>{$main->lang['group']}</legend>
      <input type='checkbox' id='b6' onclick='per()' /> {$main->lang['reading']}<br />
      <input type='checkbox' id='b5' onclick='per()' /> {$main->lang['record']}<br />
      <input type='checkbox' id='b4' onclick='per()' /> {$main->lang['execution']} 
    </fieldset>
    <fieldset>
    <legend>{$main->lang['others']}</legend>
      <input type='checkbox' id='b3' onclick='per()' /> {$main->lang['reading']}<br />
      <input type='checkbox' id='b2' onclick='per()' /> {$main->lang['record']}<br />
      <input type='checkbox' id='b1' onclick='per()' /> {$main->lang['execution']}
    </fieldset><br />
    <input size='3' maxlength='3' id='num' name='num' onkeyup='rep()'  onkeypress='rep()' value='{$chmod}' style='margin-left:3px;_margin-left:0px;' /> {$main->lang['numeric_chmod']}
    </fieldset>
    <br /><br /><center>".send_button()."</center>
    </td></tr></table>
    </form>
    <script type='text/javascript'>rep();</script>";
}

function delete_filemanager(){
    if(hook_check(__FUNCTION__)) return hook();
    if(is_dir($_POST['file'])) remove_dir($_POST['file']);
    else unlink($_POST['file']);
    main_filemanager();
}

function edit_file_filemanager(){
global $main, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    $_exp = explode('.', basename($_GET['file']));
    $ext = end($_exp);
    main::add2link('includes/javascript/codemirror/lib/codemirror.css');
    main::add2link('includes/javascript/codemirror/lib/util/simple-hint.css');
    main::add2link('includes/javascript/codemirror/theme/neat.css');
    main::add2script('includes/javascript/codemirror/lib/codemirror.js');
    main::add2script('includes/javascript/codemirror/lib/util/simple-hint.js');
    main::add2script('includes/javascript/codemirror/lib/util/javascript-hint.js');
    if(file_exists("includes/javascript/codemirror/mode/{$ext}")) main::add2script('includes/javascript/codemirror/mode/'.$ext.'/'.$ext.'.js');
    if($ext=='tpl' OR $ext=='html' OR $ext=='htm') {
        main::add2script('includes/javascript/codemirror/mode/xml/xml.js');
        main::add2script('includes/javascript/codemirror/mode/html/html.js');
        main::add2script('includes/javascript/codemirror/mode/css/css.js');
        main::add2script('includes/javascript/codemirror/mode/js/js.js');        
    }
    if($ext=='php') {
        main::add2script('includes/javascript/codemirror/mode/js/js.js');
        main::add2script('includes/javascript/codemirror/mode/xml/xml.js');
        main::add2script('includes/javascript/codemirror/mode/css/css.js');
        main::add2script('includes/javascript/codemirror/mode/clike/clike.js');
    }
    if(file_exists($_GET['file']) AND is_file($_GET['file'])){
        echo "{$main->lang['file']}: <a href='{$adminfile}?module={$main->module}'>".str_replace('/', ' / ', $_GET['file'])."</a><hr /><form action='{$adminfile}?module={$main->module}&amp;do=save_edit_file&amp;file={$_GET['file']}' method='post'><center>".
        in_area('content', 'filemanager_area', 25, file_get_contents($_GET['file'])).
        "<br /><br />".send_button().
        "</center></form>".
        '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("content"), {
                lineNumbers: true,
                theme: "neat",
                onCursorActivity: function() {
                    editor.setLineClass(hlLine, null);
                    hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
                },
                extraKeys: {
                    "F11": function() {
                        var scroller = editor.getScrollerElement();
                        if (scroller.className.search(/\bCodeMirror-fullscreen\b/) === -1) {
                            scroller.className += " CodeMirror-fullscreen";
                            scroller.style.height = "100%";
                            scroller.style.width = "100%";
                            scroller.style.position = "absolute";
                            editor.refresh();
                            $("body").css({"overflow-y": "hidden"});
                        } else {
                            scroller.className = scroller.className.replace(" CodeMirror-fullscreen", "");
                            scroller.style.height = "";
                            scroller.style.width = "";
                            scroller.style.position = "";
                            editor.refresh();
                            $("body").css({"overflow-y": ""});
                        }
                    },
                    "Esc": function() {
                         var scroller = editor.getScrollerElement();
                         if (scroller.className.search(/\bCodeMirror-fullscreen\b/) !== -1) {
                            scroller.className = scroller.className.replace(" CodeMirror-fullscreen", "");
                            scroller.style.height = "";
                            scroller.style.width = "";
                            scroller.style.position = "";
                            editor.refresh();
                            $("body").css({"overflow-y": ""});
                         }
                    },
                    "Ctrl-S": function(cm) {
                        haja({action: "'.$adminfile.'?module='.$main->module.'&do=save_edit_file&file='.$_GET['file'].'"}, {"content":editor.getValue()}, {});
                    },
                    "Ctrl-Space": function(cm) {CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);}
                }
            });
            var hlLine = editor.setLineClass(0, "activeline");
      </script>';
      
    
    } else redirect(MODULE);
}

function save_file_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists($_GET['file'])){
        if(is_writable($_GET['file'])){
            file_put_contents($_GET['file'], stripslashes($_POST['content']));
            if(!is_ajax()) redirect(MODULE);            
        } else {
            if(!is_ajax()) main_filemanager($main->lang['file_is_not_writable']);
            else echo "<script>alert('{$main->lang['file_is_not_writable']}')</script>";
        }
    } else {
        if(!is_ajax()) main_filemanager($main->lang['file_is_notfound']);
        else echo "<script>alert('{$main->lang['file_is_notfound']}')</script>";
    }
}

function save_chmod_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('get_chmod');
    if(file_exists($_GET['file'])){
        if(is_writable($_GET['file'])){
            if(!chmod($_GET['file'], '0'.$_POST['num'])) main_filemanager($main->lang['error_chmod_set']);
            if(get_chmod($_GET['file'])==$_POST['num']) redirect(MODULE);
            else main_filemanager($main->lang['error_chmod_func']);
        } else main_filemanager($main->lang['file_is_not_writable']);
    } else main_filemanager($main->lang['file_is_notfound']);
}

function download_filemanager(){
global $tpl_create, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $tpl_create->design = false;
    if(file_exists($_GET['file']) AND is_file($_GET['file'])){
        main::init_class('download');
        $download = new file_download($_GET['file'], 1, 102400);
        $download->download();
    } else redirect(MODULE);
    kr_exit();
}

function copy_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $file = basename($_POST['file']);
    $_SESSION['copy_filemanager'] = array($file, file_get_contents($_POST['file']));
    echo "<script type='text/javascript'>alert('{$main->lang['file_is_copied']}')</script>";
}

function paste_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_SESSION['copy_filemanager']) AND !empty($_SESSION['copy_filemanager'][0]) AND !empty($_POST['dir'])){
        $dir = ($_POST['dir'][0]=='/') ? mb_substr($_POST['dir'], 1, mb_strlen($_POST['dir'])) : $_POST['dir'];
        $dir .= ($dir[mb_strlen($dir)-1]!='/') ? '/' : '';
        if(file_exists($dir.$_SESSION['copy_filemanager'][0])){
            $i = 1;
            $basename = get_name_file($_SESSION['copy_filemanager'][0]);
            $ext = ($_SESSION['copy_filemanager'][0][0]!='.') ? '.'.get_type_file($_SESSION['copy_filemanager'][0]) : "";
            while(true){
                $filename = $basename." (copy {$i})".$ext;
                if(!file_exists($dir.$filename)){
                    file_write($dir.$filename, $_SESSION['copy_filemanager'][1]);
                    break;
                }
            }
        } else file_write($dir.$_SESSION['copy_filemanager'][0], $_SESSION['copy_filemanager'][1]);
        main_filemanager();
    } else echo "<script type='text/javascript'>alert('{$main->lang['error_copy']}')</script>"; 
}

function rename_filemanager(){
global $main;   
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($_POST['dir']) AND $_POST['dir'][0]=='/') $_POST['dir'] = mb_substr($_POST['dir'], 1, mb_strlen($_POST['dir']));    
    $new = trim($_POST['new_name']);    
    if(!file_exists($_POST['dir'].$_POST['file']) OR $new==$_POST['file']) return false;
    if($new == '') {echo "<script type='text/javascript'>alert('{$main->lang['new_file_name_empty']}')</script>"; return false;}    
    if(!file_exists($_POST['dir'].$new)) rename($_POST['dir'].$_POST['file'], $_POST['dir'].$new);
    else echo "<script type='text/javascript'>alert('{$main->lang['norenamefile']}')</script>";        
    main_filemanager();
    return true;
}

function newfile_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $dir = ($_POST['dir'][0]=='/') ? mb_substr($_POST['dir'], 1, mb_strlen($_POST['dir'])) : $_POST['dir'];
    $dir .= ($dir[mb_strlen($dir)-1]!='/') ? '/' : '';
    if(!file_exists($dir.$_POST['filename'])){
        file_write($dir.$_POST['filename'], '');
        main_filemanager();
    } else echo "<script type='text/javascript'>alert('{$main->lang['file_exists']}');</script>";
}

function newfolder_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $dir = ($_POST['dir'][0]=='/') ? mb_substr($_POST['dir'], 1, mb_strlen($_POST['dir'])) : $_POST['dir'];
    $dir .= ($dir[mb_strlen($dir)-1]!='/') ? '/' : '';
    if(!file_exists($dir.$_POST['foldername'])){
        mkdir($dir.$_POST['foldername'], 755);
        main_filemanager();
    } else echo "<script type='text/javascript'>alert('{$main->lang['folder_exists']}');</script>";
}

function upload_filemanager(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    //Проверяем сессию загружаемого файла
    if(session_id()!=$_POST['PHPSESSID']) return false;
    //Подключаем модуль загрузки файлов
    main::init_class('uploader');
    //Определяем каталог для загрузки файлов
    $uploaddir = $_SESSION['uploaddir'];
    if(isset($_FILES["Filedata"])){
        //Генерируем новое имя файла
        $new_name = get_name_file($_FILES["Filedata"]['name']);
        //Создаем массив параметров для загрузки файлов
        $atrib = array(
            'dir'   => $uploaddir,
            'file'  => $_FILES["Filedata"],
            'size'  => 104856600*1024,
            'type'  => array(get_type_file($_FILES["Filedata"]['name'])),
            'name'  => $new_name
        );          
        //Загружаем файл
        $attach = new upload($atrib);
        if($attach->error) header("HTTP/1.1 50{$attach->error_number} File Upload Error"); //Ошибка загрузки файла
    } else header("HTTP/1.1 500 File Upload Error");
    return true;
}
function switch_admin_filemanager(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "delete": delete_filemanager(); break;
         case "edit_file": edit_file_filemanager(); break;
         case "save_edit_file": save_file_filemanager(); break;
         case "download": download_filemanager(); break;
         case "copy": copy_filemanager(); break;
         case "paste": paste_filemanager(); break;
         case "newfile": newfile_filemanager(); break;
         case "newfolder": newfolder_filemanager(); break;
         case "rename": rename_filemanager(); break;
         case "change_chmod": change_chmod_filemanager(); break;
         case "save_chmod": save_chmod_filemanager(); break;
         case "upload": upload_filemanager(); break;
         default: main_filemanager(); break;
      }
   } elseif($break_load==false) main_filemanager();
}
switch_admin_filemanager();
?>