<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Функция возвращает текущий chmod файла
* 
* @param string $file
* @return string
*/
function get_chmod($file, $substr=0){
    if(hook_check(__FUNCTION__)) return hook();
    $chmod = mb_substr(sprintf('%o', fileperms($file)), -4);
    return mb_substr($chmod, $substr, mb_strlen($chmod));
}
?>
