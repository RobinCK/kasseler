<?php
if (!defined('FUNC_FILE')) die('Access is limited');

function get_domain($url){
    if(hook_check(__FUNCTION__)) return hook();
    $nowww = preg_replace('/www\./i','',$url);
    $domain = parse_url($nowww);
    return !empty($domain["host"]) ? $domain["host"] : $domain["path"];
}
?>
