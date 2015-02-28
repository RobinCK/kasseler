<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция чтения каталога
* 
* @param string $patch
* @param string $regexp
* @return array
*/
function scan_dir($patch, $regexp, $associative = false){
    if(hook_check(__FUNCTION__)) return hook();
    $files = array();
    if(($handle = opendir($patch))){
        while(false !== ($file = readdir($handle))) if(!is_dir($patch.$file) AND preg_match($regexp, $file)) if($associative==false) $files[]=$file; else $files[$file]=$file;
        closedir($handle);
    }
    return $files;
}
?>
