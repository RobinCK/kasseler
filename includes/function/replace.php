<?php
if (!defined('FUNC_FILE')) die('Access is limited');

function set_mylang($val){
global $language, $mylang;  
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($mylang[$val]) AND isset($mylang[$val][$language])) return $mylang[$val][$language];
    else return "";
}

function parse_mylang($txt){
    if(hook_check(__FUNCTION__)) return hook();
    return preg_replace_callback('/mylang\[(.+?)\]/is', function($matches) { return set_mylang($matches[1]);}, $txt);
}

function replace_content($html){
    if(hook_check(__FUNCTION__)) return hook();
    $in = array();
    $out = array();
    return parse_mylang(preg_replace($in, $out, $html));
}

function replace_link($html){
    if(hook_check(__FUNCTION__)) return hook();
    return $html;
}
?>