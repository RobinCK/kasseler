<?php
/**
* Файл дополнительных функций
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/tags.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция нормализации массива тегов
* 
* @param array $tags_arr
* @return array
*/
function normalize_tags(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['tags']) AND is_array($_POST['tags'])){
        return implode(',', $_POST['tags']);
    } else return '';
}

/**
* Функция заполнения таблицы тегов
* 
* @param array $tags
* @param int      $post
* @param string $post_id
* @param string $modul
*/
function set_tags_sql($post, $modul){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['tags']) AND is_array($_POST['tags'])) foreach ($_POST['tags'] as $key => $value) sql_insert(array( 'tag'=>$value, 'post'=>$post, 'modul'=>$modul), TAG);
}
?>