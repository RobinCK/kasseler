<?php
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* Определение размера удаленного файла
* 
* @param string $path
* @return int
*/
function get_fsize($path){
    if(hook_check(__FUNCTION__)) return hook();
    if(!file_exists($path)) return 0;
    $fp = fopen($path, "r");
    $inf = stream_get_meta_data($fp);
    fclose($fp);
    foreach($inf["wrapper_data"] as $v){
        if(stristr($v, "content-length")){
            $v = explode(":",$v);
            return trim($v[1]);
        }
    }
    return 0;
}
?>
