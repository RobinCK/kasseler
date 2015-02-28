<?php
if (!defined('FUNC_FILE')) die('Access is limited');
/**
* Проверка IP адреса на валидность
* 
* @param string $ip
* @return bool
*/
function validip($ip) {
global $reserved_ips;
    if(hook_check(__FUNCTION__)) return hook();
    if (!empty($ip) AND $ip == long2ip(ip2long($ip))){
        foreach ($reserved_ips as $r) if ((ip2long($ip) >= $r[0]) AND (ip2long($ip) <= $r[1])) return false;
        return true;
    } else return false;
}
?>
