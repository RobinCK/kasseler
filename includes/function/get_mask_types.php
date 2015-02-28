<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Функция генерации маски загружаемых файлов
* 
* @param string $string
* @param string $return
*/
function get_mask_types($string, $return=""){
    if(hook_check(__FUNCTION__)) return hook();
    $arr = explode(",", $string);
    $count = count($arr);
    for($i=0; $i<$count; $i++) $return .= ($i<$count-1) ? "*.{$arr[$i]};" : "*.{$arr[$i]}";
    return $return;
}
?>
