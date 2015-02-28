<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Функция создания выпадающего списка - групп аватаров
* 
* @return
*/
function select_avatars(){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    $select = "\n<select id='cat' class='select2 chzn-search-hide' name='avatar'>\n";
    $avatars = opendir($userconf['directory_avatar']);
    while(($file = readdir($avatars))) if(is_dir($userconf['directory_avatar'].$file) AND $file!='.' AND $file!='..' AND $file!='admin' AND $file!='.svn') $select .= "<option value='{$file}'>$file</option>\n";
    closedir($avatars);
    return $select."</select>\n";
}
?>
