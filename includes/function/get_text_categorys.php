<?php
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* возвращает категории в удобно читаемом виде
* 
* @param array $catlist
* @param string $values
*/
function get_text_categorys($catlist,$values){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $varr=explode(',', $values);$outtext="";
    foreach ($varr as $key => $value) $outtext.=$value!=""?(array_key_exists($value,$catlist)?",".$catlist[$value]:""):"";
    return $outtext==""?"<i>{$main->lang['nocat']}</i>":substr($outtext,1);
}
?>
