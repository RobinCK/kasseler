<?php
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция определения иконки файла по заданному типу
* 
* @param string $type
* @param string $dir
* @param string $file
* @return string
*/
function get_ico_image($type, $dir, $file){
global $ico_types;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($ico_types[$type])) return "<img src='includes/images/swfupload/{$ico_types[$type]}' alt='{$type}' align='left' />";
    return "<img src='includes/images/swfupload/file.png' alt='' align='left' />";
}
?>
