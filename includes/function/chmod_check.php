<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция проверки chmod
* 
* @param int $chmod
* @param string $file
* @return bool
*/
function chmod_check($chmod, $file){
    if(hook_check(__FUNCTION__)) return hook();
    if(decoct(0777 & fileperms($file))>=$chmod) return true; else return false;
}
?>
