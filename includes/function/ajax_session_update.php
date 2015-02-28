<?php
if(!defined('FUNC_FILE')) die('Access is limited');

function ajax_session_update(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $ret = array('status'=>true);
    return json_encode($ret);
}
?>
