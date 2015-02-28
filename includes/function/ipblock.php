<?php
/**
* Файл блокировки IP адресов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/ipblock.php
* @version 2.0
*/
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция возвращает требуемый элемент из супер глобальных массивов $_SERVER, $_ENV
* 
* @param string $var_name
* @param string $return
* @return string
*/
function get_env($var_name, $return='') {
    if(isset($_SERVER[$var_name])) return $_SERVER[$var_name];
    elseif (isset($_ENV[$var_name])) return $_ENV[$var_name];
    elseif (getenv($var_name)) return getenv($var_name);
    elseif(function_exists('apache_getenv') && apache_getenv($var_name, true)) return apache_getenv($var_name, true);
    return ($return!=''?$return:'');
}

/**
* Функция определяет IP адрес посетителя и его proxy сервер – если он есть
* 
* @return void
*/
function get_ip(){
global $proxy, $ip;
    $ip="0.0.0.0";
    $proxy=$dproxy="0.0.0.0";
    $localnet=array(array (167772160,184549375), //'10.0.0.0','10.255.255.255'
    array(-1408237568,-1407188993),//'172.16.0.0','172.31.255.255'
    array(-1062731776,-1062666241));//'192.168.0.0','192.168.255.255'
    if(get_env('HTTP_X_FORWARDED_FOR')) $proxy = get_env("REMOTE_ADDR");
    if(get_env('HTTP_X_FORWARDED_FOR')) $ip = get_env('HTTP_X_FORWARDED_FOR');
    elseif(get_env('HTTP_CLIENT_IP')) $ip = get_env('HTTP_CLIENT_IP');
    else $ip = get_env('REMOTE_ADDR');
    if($proxy!=$dproxy){$ipd=ip2long($ip);foreach ($localnet as $key => $value) {if($ipd>=$value[0] AND $ipd<=$value[1]) $ip=$proxy;}}
    if(strpos($ip, ',')!==false){
        $_ip = explode(',', $ip);
        $ip = trim($_ip[1]);
        $proxy = trim($_ip[0]);
    }
}

global $security, $ip, $config;
$ip_arg = 0; $k = -1;
get_ip();
if(count($security['look_ip'])>0 AND !empty($ip)){
    foreach($security['look_ip'] as $key => $val){
        if($val[0]==$ip){$k=$key; $ip_arg = 4; break;} 
        else {
            $ip_arg = 0;
            $arr_arg1 = explode(".", $val[0]);
            $arr_arg2 = explode(".", $ip);
            if(count($arr_arg1)==4 AND count($arr_arg2)==4){
                $ip_arg += ($arr_arg1[0]==$arr_arg2[0] OR $arr_arg1[0]=="*") ? 1 : 0;
                $ip_arg += ($arr_arg1[1]==$arr_arg2[1] OR $arr_arg1[1]=="*") ? 1 : 0;
                $ip_arg += ($arr_arg1[2]==$arr_arg2[2] OR $arr_arg1[2]=="*") ? 1 : 0;
                $ip_arg += ($arr_arg1[3]==$arr_arg2[3] OR $arr_arg1[3]=="*") ? 1 : 0;
                if ($ip_arg==4) {
                    $k=$key;
                    break;
                }
            }
        }
    }
}
//die("s=".$ip_arg);
if(empty($ip)) $ip_arg = 4;
if($ip_arg==4){
    $d = $k>=0 ? (!empty($security['look_ip'][$k][1]) ? "<b>Reason:</b> ".$security['look_ip'][$k][1] : '') : '';
    die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <title>'.$config['home_title'].'</title>
    <meta http-equiv="content-type" content="text/html; charset='.$config['charset'].'" />
    <meta name="copyright" content="Copyright (c) Kasseler CMS 2.0.0" />    
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    </head>
    <body>
    <br /><br /><br /><br /><center><h1>Your IP address is blocked :[</h1>'.$d.'<br /><b>Contact E-mail</b>: <a href="mailto:'.$config['contact_mail'].'">'.$config['contact_mail'].'</a></center>
    </body></html>');
}
?>
