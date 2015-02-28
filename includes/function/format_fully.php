<?php
if(!defined('FUNC_FILE')) die('Access is limited');

function format_fully($date, $format=null){
global $main, $userinfo, $config;
    $user_gmt=empty($userinfo['user_gmt'])?0:intval($userinfo['user_gmt']);
    $GMT=$user_gmt-intval($config['GMT_correct']);
    $date=strtotime($date)+intval($GMT)*(60*60);
    $format = $format!=null ? $format : $main->config['date_format'];
    $_date = date($format, $date);
    $_time = date('H:i:s', $date);
    if($_date==date($format)) return $main->lang['today'].' '.$_time;
    elseif($_date==date($format)-1) return $main->lang['yesterday'].' '.$_time;
    else return $_date." {$main->lang['time_in']} ".$_time;
}
?>
