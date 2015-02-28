<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция проверки является ли заданное имя, именем поисковой системы
* 
* @param string $name
* @return bool
*/
function is_bot($name=""){
global $list_bots;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($name)) return isset($list_bots[$name]) ? true : false;
    else return (isset($_SESSION['user']) AND isset($list_bots[$_SESSION['user']])) ? true : false;
}

/**
* Проверяет поддержку gzip сжатия
* 
* @return bool
*/
function check_can_gzip(){
    if(hook_check(__FUNCTION__)) return hook();
    if(headers_sent() || connection_aborted() || !function_exists('ob_gzhandler') || ini_get('zlib.output_compression')) return 0;
    if(get_env('HTTP_ACCEPT_ENCODING')){
        $encoding=get_env('HTTP_ACCEPT_ENCODING');
        if(mb_strpos($encoding, 'x-gzip') !== false) return "x-gzip";
        if(mb_strpos($encoding, 'gzip') !== false) return "gzip";
    }
    return false;
}

/**
* Функция проверки является ли страница главной
* 
* @return bool
*/
function is_home(){
global $adminfile, $code2languages, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $uri=get_env('REQUEST_URI');
    if($uri=="/" OR $uri=="/index.php" OR $uri=="/index{$main->config['file_rewrite']}" OR $uri=="/".$adminfile OR !empty($code2languages[str_replace("/", "", $uri)])) return true; return false;
}

/**
* Функция проверки является ли пользователь администратором или модератором
* 
* @return bool
*/
function is_support($id = 0){
    if(hook_check(__FUNCTION__)) return hook();
    if(is_admin() OR is_moder($id)) return true; else return false;
}

/**
* Функция проверяет, является ли пользователь администратором
* 
* @return bool
*/
function is_admin(){
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_SESSION['admin']) AND is_user()) return true; else return false;
}

/**
* Функция проверяет, является ли пользователь модератором
* 
* @param int $forum_id
* @return bool
*/
function is_moder($id = 0){
global $userinfo, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($id!=0){
        if(!isset($_SESSION['forum'][$main->user['user_name']][$id])){
            $result = $main->db->sql_query("SELECT * FROM ".ACC." WHERE uid='{$userinfo['uid']}' AND id='{$id}'");
            if($main->db->sql_numrows($result)>0) $_SESSION['forum'][$main->user['user_name']][$id] = true;
            else $_SESSION['forum'][$main->user['user_name']][$id] = false;
        }
        return $_SESSION['forum'][$main->user['user_name']][$id];
    } elseif(isset($_SESSION['admin']) AND $userinfo['user_level']=="1" AND is_user()) return true; else return false;
}

/**
* Функция проверяет, является ли посетитель авторизированным пользователем
* 
* @return bool
*/
function is_user(){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_SESSION['user']) AND $_SESSION['user']!=$userconf['guest_name'] AND !is_bot()) return true; else return false;
}

/**
* Функция проверки является ли переданное имя, именем гостя
* 
* @param string $name
* @return bool
*/
function is_guest_name($name){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    if($name==$userconf['guest_name']) return true; else return false;
}

/**
* Функция проверки является ли посетитель гостем
* 
* @return bool
*/
function is_guest(){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_SESSION['user']) AND ($_SESSION['user']==$userconf['guest_name'] OR is_bot())) return true; else return false;
}

/**
* Функция проверки выполнения аякс. Выполняется ли скрипт  как аякс приложение
* 
* @return bool
*/
function is_ajax(){
    if(hook_check(__FUNCTION__)) return hook();
    return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') OR isset($_GET['ajaxed']) OR isset($_GET['ajax']) OR isset($_POST['ajax'])) ? true : false;
}

/**
* Функция определяет, является ли строка IP адресом
* 
* @param string $string
* @return bool
*/
function is_ip($string){
    if(hook_check(__FUNCTION__)) return hook();
    if(preg_match('/^([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/', $string)) return true;
    else return false;
}

/**
* Функция проверки состоит ли пользователь в списке переданных групп
* 
* @param string $groups
* @return bool
*/
function check_user_group($groups){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($groups)) return true;
    else $arr = explode(',', $groups);
    if(in_array('0',$arr)) return true;
    $user_groups = array($main->user['user_group'])+(!empty($main->user['user_groups'])?explode(',', $main->user['user_groups']):array());    
    $array = array_intersect($arr, $user_groups);
    $_array = $array;
    foreach($_array as $key => $value) if(empty($array[$key])) unset($array[$key]);
    if(empty($array)) return false;
    else return true;
}

?>