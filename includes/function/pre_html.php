<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция создания отступа для построения деревьев
* 
* @param int $int
* @param string $space
* @return string
*/
function pre_html($int, $space=""){
    if(hook_check(__FUNCTION__)) return hook();
    for($i=0;$i<$int;$i++) $space .= "  ";
    return ($int!=0) ? $space."› " : $space;
}
?>
