<?php
if(!defined('FUNC_FILE')) die('Access is limited');

function get_sock_content($host, $get, $port=80, $timeout=30, $user_agent=' kasselerbot', $referer =''){
    if(hook_check(__FUNCTION__)) return hook();
    $content = "";
    $header = "GET /{$get} HTTP/1.1\r\n".
    "Host: {$host}\r\n".
    "User-Agent: Mozilla/5.0 (X11; U; Linux x86_64; en-GB; rv:1.8.0.4) Gecko/20060608 Ubuntu/dapper-security Epiphany/2.14{$user_agent}\r\n".
    "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5\r\n".
    "Accept-Language: ru-ru,ru;q=0.8\r\n".    
    "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7\r\n".
    "Keep-Alive: 300\r\n". 
    (!empty($referer)?"Referer: {$referer}\r\n":"").
    "Proxy-Connection: keep-alive\r\n\r\n\r\n";
    $errno = $errstr = "";
    $socket = fsockopen($host, $port, $errno, $errstr, $timeout);
    if($socket !== false){
        socket_set_timeout($socket, $timeout/10);
        fwrite($socket, $header);
        while (!feof($socket)) $content .= fgets($socket, 128);
    }
    fclose($socket);
    return $content;
}
?>
