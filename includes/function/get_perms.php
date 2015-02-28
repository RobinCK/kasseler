<?php
if (!defined('FUNC_FILE')) die('Access is limited');
/**
* Функция возвращает атрибуты файла
* 
* @param string $filepath
* @return string
*/
function get_perms($filepath) {
    if(hook_check(__FUNCTION__)) return hook();
    $mode = @fileperms($filepath);
    if (($mode & 0xC000) === 0xC000) {$type = 's';}
    elseif (($mode & 0x4000) === 0x4000) {$type = 'd';}
    elseif (($mode & 0xA000) === 0xA000) {$type = 'l';}
    elseif (($mode & 0x8000) === 0x8000) {$type = '-';} 
    elseif (($mode & 0x6000) === 0x6000) {$type = 'b';}
    elseif (($mode & 0x2000) === 0x2000) {$type = 'c';}
    elseif (($mode & 0x1000) === 0x1000) {$type = 'p';}
    else {$type = '?';}

    $owner['read'] = ($mode & 00400) ? 'r' : '-'; 
    $owner['write'] = ($mode & 00200) ? 'w' : '-'; 
    $owner['execute'] = ($mode & 00100) ? 'x' : '-'; 
    $group['read'] = ($mode & 00040) ? 'r' : '-'; 
    $group['write'] = ($mode & 00020) ? 'w' : '-'; 
    $group['execute'] = ($mode & 00010) ? 'x' : '-'; 
    $world['read'] = ($mode & 00004) ? 'r' : '-'; 
    $world['write'] = ($mode & 00002) ? 'w' : '-'; 
    $world['execute'] = ($mode & 00001) ? 'x' : '-'; 

    if( $mode & 0x800 ) {$owner['execute'] = ($owner['execute']=='x') ? 's' : 'S';}
    if( $mode & 0x400 ) {$group['execute'] = ($group['execute']=='x') ? 's' : 'S';}
    if( $mode & 0x200 ) {$world['execute'] = ($world['execute']=='x') ? 't' : 'T';}
 
    return $type.$owner['read'].$owner['write'].$owner['execute'].$group['read'].$group['write'].$group['execute'].$world['read'].$world['write'].$world['execute'];
}
?>
