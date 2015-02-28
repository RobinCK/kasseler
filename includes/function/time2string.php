<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция преобразования секунд в дни, часы, минуты
* 
* @param int $sal
* @param string $return
* @return string
*/
function time2string($sal, $return=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $days = floor($sal/86400); $sal -= $days*86400;
    $h = floor($sal/3600); $sal -= $h*3600;
    $m = floor($sal/60); $sal -= $m*60;
    $txt = array(
        'day' => (mb_substr($days, mb_strlen($days)-1, 1)==1) ? $lang['u_day'] : ((mb_substr($days, mb_strlen($days)-1, 1)>1 AND (mb_substr($days, mb_strlen($days)-1, 1)<5)) ? $lang['u_day2'] : $lang['u_day3']),
        'h'   => (mb_substr($h, mb_strlen($h)-1, 1)==1) ? $lang['u_h'] : ((mb_substr($h, mb_strlen($h)-1, 1)>1 AND (mb_substr($h, mb_strlen($h)-1, 1)<5)) ? $lang['u_h2'] : $lang['u_h3']),
        'm'   => (mb_substr($m, mb_strlen($m)-1, 1)==1) ? $lang['u_m'] : ((mb_substr($m, mb_strlen($m)-1, 1)>1 AND (mb_substr($m, mb_strlen($m)-1, 1)<5)) ? $lang['u_m2'] : $lang['u_m3']),
        's'   => (mb_substr($sal, mb_strlen($sal)-1, 1)==1) ? $lang['u_s'] : ((mb_substr($sal, mb_strlen($sal)-1, 1)>1 AND (mb_substr($sal, mb_strlen($sal)-1, 1)<5)) ? $lang['u_s2'] : $lang['u_s3']),
    );
    $return .= ($days!=0) ? "{$days} {$txt['day']}" : "";
    $return .= ($h!=0) ? " {$h} {$txt['h']}" : "";
    $return .= ($m!=0) ? " {$m} {$txt['m']}" : "";
    $return .= ($sal!=0) ? " {$sal} {$txt['s']}" : "";
    return $return;      
}
?>
