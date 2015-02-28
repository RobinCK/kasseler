<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Возвращает массив значений категорий по модулю
* 
* @param string $module
*/
function get_array_cat($module){
 global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('pre_html');    
    $result = $main->db->sql_query("SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level FROM ".CAT." AS t WHERE module='{$module}' ORDER BY t.tree");
    while(($row = $main->db->sql_fetchrow($result))) $sel[$row['cid']] = pre_html($row['level']-1).$row['title'];
    return isset($sel)?$sel:array();
}
?>
