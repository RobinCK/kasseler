<?php
if(!defined('FUNC_FILE')) die('Access is limited');
function admin_module_exists($path, $name, $ext=''){
    if(hook_check(__FUNCTION__)) return hook();
    return file_exists("{$path}{$name}{$ext}");
}

function user_module_exists($path, $name, $file=''){
    if(hook_check(__FUNCTION__)) return hook();
    return file_exists("{$path}{$name}{$file}");
}
?>
