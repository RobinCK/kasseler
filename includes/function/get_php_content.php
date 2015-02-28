<?php
if(!defined('FUNC_FILE')) die('Access is limited');

function get_php_content($file, $eval=""){
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists($file)){
        if(!empty($eval)) eval($eval);
        ob_start();
        main::required($file);
        $content = ob_get_contents(); ob_end_clean();
        return $content;
    } else return "";
}
?>
