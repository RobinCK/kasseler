<?php
if(!defined('FUNC_FILE')) die('Access is limited');

function add_meta_value($value, $afields=null){
global $keywords, $description, $page_title, $module_name, $modules, $config;
    if(hook_check(__FUNCTION__)) return hook();
    if(!defined('ADMIN_FILE')){
        if(isset($afields)&&isset($afields->title)&&!empty($afields->title)) $page_title = $afields->title;
        else {
            $page_title = (empty($page_title) ? "{$modules[$module_name]['title']} @ {$config['home_title']}" : $page_title);
            $page_title = $value." {$config['separator']} ".$page_title;
        }
        $keywords = isset($afields)&&isset($afields->meta_key)?$afields->meta_key:(empty($keywords) ? $modules[$module_name]['title'] : $keywords);
        $keywords = $value.", ".$keywords.", ";
        $description = isset($afields)&&isset($afields->meta_description)?$afields->meta_description:(empty($description) ? $modules[$module_name]['title'] : $description);
        $description = $value.", ".$description.", ";
    } else {
        $page_title = empty($page_title) ? "{$value} @ {$config['home_title']}" : $value." {$config['separator']} ".$page_title;
        $keywords = empty($keywords) ? $value : $value.", ".$keywords.", ";;
        $description = empty($description) ? $value : $value.", ".$description;
    }
}

?>