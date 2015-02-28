<?php
/**
* Класс создания сесий
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/session.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

class session{
    /**
    * ID сесии
    * 
    * @var string
    */
    var $id;
    
    /**
    * Имя пользователя
    * 
    * @var string
    */
    var $login;
    
    /**
    * Время последнего действия на сайте
    * 
    * @var string
    */
    var $lastAction;
    
    /**
    * Время на протяжении которого пользователь считается online
    * 
    * @var int
    */
    var $maxInactiveTime;

    /**
    * Функция создания сессии (конструктор)
    * 
    * @param string $time
    * @return session
    */
    function session($time=300){
       global $config, $db, $ip, $proxy, $agentinfo,$main;
        if(isset($_FILES["Filedata"])){
           if(isset($_POST['PHPSESSID'])){
             $sid=substr($_POST['PHPSESSID'],0,100);
             $db->sql_query("select * from ".SESSIONS." where sid like '{$sid}'");
             if($db->sql_numrows()==0) return false;
           } else  return false;
        }
        //Игнорируем создание сессии для процесса установки системы
        if(defined("INSTALLCMS")&&!defined("UPDATECMS")) return false;
        $this->maxInactiveTime = $time;
        //Определяем IP пользователя
        if(!$this->is_session()){
            if($config['user_agent_full']!=ENABLED) $agentinfo = $this->user_agent();
            else $agentinfo = $this->user_agent_full();
            //Если найден cookies пользователя
            if(isset($_COOKIE[$config['user_cookies']])){
                $cookinfo=explode(",", $_COOKIE[$config['user_cookies']]);
                list($uid, $user_name, $user_password, $user_last_visit, $user_level, $user_baned, $user_baned_time) = $db->sql_fetchrow($db->sql_query("SELECT uid, user_name, user_password, user_last_visit, user_level, user_baned, user_baned_time FROM ".USERS." WHERE user_name='".kr_filter($cookinfo[0], TAGS)."'"));                
                //Выполняем проверку на блокировку пользователя
                if($user_baned=="1" AND $user_baned_time>time()){
                    $this->register("Guest");
                    return false;
                }
                //Выполняем проверку данных в cookies
                if(isset($cookinfo[1]) AND $user_password==$cookinfo[1]){
                    $_SESSION['lastVisit'] = (!isset($_SESSION['lastVisit'])) ? $user_last_visit : $_SESSION['lastVisit'];
                    //Регистрируем сессию пользователя
                    $this->register($cookinfo[0]);
                    //Определяем страну пользователя
                    if($config['geoip']==ENABLED){
                        if(!function_exists('geoip_country_code_by_name')) main::init_function('geoip');
                        $gi = geoip_open("includes/GeoIP.dat", GEOIP_STANDARD);
                        $country = geoip_country_name_by_addr($gi, $ip);
                        geoip_close($gi);
                    }
                    $country = (!isset($country) OR empty($country)) ? "default" : $country;
                    //Обновляем данные о пользователе
                    $agentinfo['os'] = (empty($agentinfo['os'])) ? "Windows" : $agentinfo['os'];
                    $agentinfo['browser'] = (empty($agentinfo['browser'])) ? "Opera" : $agentinfo['browser'];
                    $db->sql_query("UPDATE ".USERS." SET user_country='{$country}', user_last_os='".kr_filter($agentinfo['os'], TAGS)."', user_last_browser='".kr_filter($agentinfo['browser'], TAGS)."', user_last_ip='{$ip}', user_last_proxy='{$proxy}', user_last_visit='".kr_datecms("Y-m-d H:i:s")."' WHERE user_name='".kr_filter($user_name, TAGS)."'");
                    if($user_level>0){
                        //Если пользователь является админом или модератором 
                        if(!isset($_SESSION['admin'])){
                            //Если сессия администратора не создана
                            if(isset($_COOKIE[$config['admin_cookies']]) AND $config['logined_admin']==0){
                                //Проверяем данные в cookies
                                $cookinfo=explode(",", $_COOKIE[$config['admin_cookies']]);
                                if ($cookinfo[1]==$user_password){
                                    $_SESSION['admin'] = $cookinfo[0];
                                    $db->sql_query("UPDATE ".SESSIONS." SET actives='y', country='{$country}', is_admin='{$user_level}', time='".time()."' WHERE uname='{$user_name}' and sid='".session_id()."'");
                                } else {
                                    //Сookies не прошел проверку, удаляем его чтобы не мешал
                                    setcookies("", $config['admin_cookies'], 1);
                                }
                            } else setcookies("", $config['admin_cookies'], 1);
                        }
                    } else{
                        //Если сессия была создана а пользователь не является администратором, удаляем сессию
                        if(isset($_SESSION['admin'])) unset($_SESSION['admin']);
                    }
                } else {
                    //Сookies не прошел проверку
                    // Удаляем его чтобы не мешал
                    setcookies("", $config['user_cookies'], 1);
                    $_SESSION['lastVisit'] = 0;
                    //Регистрируем сессию гостя
                    $this->register("Guest");
                }
            } else $this->register("Guest");
        }
        if(empty($_SESSION['form_checked'])){
           $_SESSION['form_checked'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
           setcookies($_SESSION['form_checked'], "nIhDgOTW6j", 1);
        }
        //Получаем информацию о пользователе текущей сессии
        $this->userinfo();
        //Обновляем сессию пользователя
        $this->online();
        //Создаем массив наблюдения за пользователями
        $this->supervision();
        return true;
    }
    
    /**
    * Функция наблюдения за пользователями
    * 
    * @return void
    */
    function supervision(){
    global $main, $db, $supervision;
        if(!isset($_SESSION['supervision']) OR !is_array($_SESSION['supervision']) OR count($_SESSION['supervision'])<4 OR !isset($_COOKIE['update_session'])){
            //Делаем выборку сессий
            $result = $db->sql_query("SELECT s.sid, s.uname, s.is_admin, s.ip, s.time, s.module, s.url, s.user_agent, s.country, u.uid, u.user_id, u.user_name, u.user_group, g.id, g.title, g.color FROM ".SESSIONS." AS s LEFT JOIN ".USERS." AS u ON(s.uname=u.user_name) LEFT JOIN ".GROUPS." AS g ON(u.user_group=g.id) WHERE s.actives='y' GROUP BY s.uname");
            $online = array('admin' => array(), 'users' => array(), 'bots' => array(), 'guest' => array());
            //Сортируем всех пользователей по их типу
            while(($row = $db->sql_fetchrow($result))){
                $row['url'] = str_replace("&", "&amp;", $row['url']);
                if(($row['is_admin']==1 OR $row['is_admin']==2) AND !is_ip($row['uname'])) $online['admin'][] = $row;
                elseif(!is_ip($row['uname']) AND !is_bot($row['uname'])) $online['users'][] = $row;
                elseif(is_bot($row['uname'])) $online['bots'][] = $row;
                elseif(is_ip($row['uname'])) $online['guest'][] = $row;
            }
            $_SESSION['supervision'] = $supervision = $online;
            if (!isset($_COOKIE['update_session']) AND is_user() AND $main->config['interval_session_update']>"0") setcookies(time() + $main->config['interval_session_update'], "update_session", time() + $main->config['interval_session_update']);
        } else $supervision = $_SESSION['supervision'];
    }
    
    /**
    * Функция обновления сессии пользователя 
    * 
    * @return void
    */
    function online(){
    global $db, $ip, $module_name, $config, $userinfo, $list_bots;
        $user_agent = kr_filter(get_user_agent(), TAGS);
        //Отключаем вывод ошибок SQL
        $db->report_error = false;
        $user = (!is_guest()) ? $_SESSION['user'] : $ip;
        $robots = $robot_ag = "";   
        //Проверяем, является ли пользователь поисковой системой
        foreach ($list_bots as $bot_name=>$agent) if(preg_match("/$agent/i", $user_agent)){$robot_ag = $agent; $robots = $bot_name; break;}
        if(!empty($robots)){
            $user = $robots;
            $this->register($user);
            $userinfo = array('uid' => '0', 'user_id' => $robot_ag, 'user_folder'  => 'bots', 'user_name'  => $robots, 'user_email' => '', 'user_level' => 0, 'user_group' => 3, 'user_groups' => '', 'user_avatar' => 'guest.png', 'user_country' => 'default', 'group_title' => 'SearchBot');
        }
        //Проверяем, нужно ли делать обновление сессии
        if(!isset($_COOKIE['update_session'])){
            //$country = (!isset($userinfo['user_country']) OR empty($userinfo['user_country'])) ? "default" : $userinfo['user_country'];
            if($config['geoip']==ENABLED){
                if(!function_exists('geoip_country_code_by_name')) main::init_function('geoip');
                $gi = geoip_open("includes/GeoIP.dat", GEOIP_STANDARD);
                $country = geoip_country_name_by_addr($gi, $ip);
                geoip_close($gi);
            }
            $country = (!isset($country) OR empty($country)) ? "default" : $country;
            $userinfo['user_country'] = $country;
            //Создаем или обновляем сессию
            if(!empty($robots)){
               if($db->sql_fetchrow($db->sql_query("SELECT * FROM ".SESSIONS." WHERE upper(uname)=upper('{$robots}')"))>0) $db->sql_query("UPDATE ".SESSIONS." SET actives='y', country='{$country}', is_admin='{$userinfo['user_level']}', uname='{$robots}', ip='{$ip}', time='".time()."', module='{$module_name}', url='".get_env('REQUEST_URI')."' WHERE uname='{$robots}' ");
               else $db->sql_query("INSERT INTO ".SESSIONS." (country, is_admin, ip, sid, uname, time, module, url, user_agent, actives) VALUES ('{$country}', '{$userinfo['user_level']}', '{$ip}', '".session_id()."', '{$robots}', '".time()."', '{$module_name}', '".get_env('REQUEST_URI')."', '{$user_agent}','y')");
            } else {
               if($db->sql_fetchrow($db->sql_query("SELECT * FROM ".SESSIONS." WHERE sid='".session_id()."'"))>0) $db->sql_query("UPDATE ".SESSIONS." SET actives='y', country='{$country}', is_admin='{$userinfo['user_level']}', uname='{$user}', ip='{$ip}', time='".time()."', module='{$module_name}', url='".get_env('REQUEST_URI')."' WHERE sid='".session_id()."'");
               else $db->sql_query("INSERT INTO ".SESSIONS." (country, is_admin, ip, sid, uname, time, module, url, user_agent, actives) VALUES ('{$country}', '{$userinfo['user_level']}', '{$ip}', '".session_id()."', '{$user}', '".time()."', '{$module_name}', '".get_env('REQUEST_URI')."', '{$user_agent}','y')");
            }
        }
        //переводим в нерабочее состояние все неактуальные сессии
        if(!isset($_COOKIE['online'])){ 
            if(!isset($_FILES["Filedata"])) $db->sql_query("UPDATE ".SESSIONS." SET actives='n' WHERE time < '".(time() - $config['time_online'])."'");
            $time_life_cookie=time() - ($config['time_of_life_session']*86400);
            $time_life_server_session = time() - intval(ini_get('session.gc_maxlifetime'));
            $time_life_session=time() - $config['time_online'];
            $db->sql_query("DELETE FROM ".SESSIONS." WHERE (time < '".$time_life_server_session."') OR (uname=ip AND time < '".$time_life_session."')");
            setcookies(time() + $config['time_online'], "online", time() + $config['time_online']);
        }
        if(!isset($_COOKIE['update_session']) AND is_user() AND $config['interval_session_update']>"0") setcookies(time() + $config['interval_session_update'], "update_session", time() + $config['interval_session_update']);
        //Включаем вывод ошибок SQL
        $db->report_error = true;
    }
    
    /**
    * Функция устанавливает параметры пользователя текущей сессии
    * 
    * @return bool
    */
    function userinfo(){
    global $userinfo, $userconf, $config;
        if(is_guest()) {
            //Если сессия создана для гостя добавляем необходимую информацию о пользователе
            $userinfo = array('uid' => '-1', 'user_id' => 'guest', 'user_folder'  => 'guest', 'user_name'  => $userconf['guest_name'], 'user_email' => '', 'user_level' => 0, 'user_group' => 4, 'user_groups' => '', 'user_avatar' => 'guest.png', 'user_country' => 'default', 'group_title' => 'Guest');
            return true;
        }
        if(!is_bot()){
            if(isset($_SESSION['cache_session_user'])){
                if($_SESSION['cache_session_user']['user_name']!=$_SESSION['user']) $_SESSION['cache_session_user'] = $this->get_userinfo_var($_SESSION['user']);
            } else $_SESSION['cache_session_user'] = $this->get_userinfo_var($_SESSION['user']);
            $userinfo = $_SESSION['cache_session_user'];
            main::init_function('check_filter_ip');
            if($userinfo['user_filter_session']==1){ 
               if(!check_current_login($userinfo['user_filter_ip'])) {
                  setcookies("", $config['user_cookies'], 1);setcookies("", "update_session", 1);setcookies("", "online", 1);
                  session_unset(); die_work_block_ip();
               } 
            }
            return true;
        }
    }
    
    /**
    * Функция выборки данных пользователя текущей сессии
    * 
    * @param string $name
    * @return array
    */
    function get_userinfo_var($name){
    global $db;        
        $u = $db->sql_query("SELECT * FROM ".USERS." WHERE user_name='{$name}'");
        $userinfo = $db->sql_fetchrow($u);
        main::init_function('check_filter_ip');
        if($userinfo['user_filter_active']==1){unset($_SESSION['ipfilter']);check_current_login($userinfo['user_filter_ip']);}
        $userinfo['user_folder'] = $userinfo['user_id'];
        $g = $db->sql_query("SELECT * FROM ".GROUPS." WHERE id='{$userinfo['user_group']}'");
        $group = $db->sql_fetchrow($g);        
        if(!empty($group)) {
            $group['img'] = (!empty($group['img'])) ? "<img src='includes/images/groups/{$group['img']}' alt='{$group['title']}' />" : "";
            foreach($group as $key=>$value) $userinfo['group_'.$key] = $value;
        }
    return $userinfo;
    }
    
    /**
    * Функция обновляет данные о браузере, ОС для пользователя текущей сессии
    * 
    * @return void
    */
    function user_agent(){
        $agent = kr_filter(get_user_agent(), TAGS);
        if(!$agent) return $useragent = array('browser' => "undefined", 'os' => "undefined");
        //Определяем ОС  пользователя
        if(mb_strpos($agent, "Win") !== false) {
            if(mb_strpos($agent, "NT 6.1") !== false) $useragent['os'] = "Windows 7";
            elseif(mb_strpos($agent, "NT 6.0") !== false) $useragent['os'] = "Windows Vista";
            elseif(mb_strpos($agent, "NT 5.1") !== false OR mb_strpos($agent, "XP")) $useragent['os'] = "Windows XP";
            elseif(mb_strpos($agent, "NT 5.2") !== false) $useragent['os'] = "Windows Server 2003";
            elseif(mb_strpos($agent, "NT 5.0") !== false) $useragent['os'] = 'Windows 2000';
            elseif(mb_strpos($agent, "NT 4.0") !== false OR mb_strpos($agent, "3.5") !== false) $useragent['os'] = "Windows NT";
            elseif(mb_strpos($agent, "Me") !== false) $useragent['os'] = "Windows Me";
            elseif(mb_strpos($agent, "98") !== false) $useragent['os'] = "Windows 98";
            elseif(mb_strpos($agent, "95") !== false) $useragent['os'] = "Windows 95";
        }
        elseif(mb_strpos($agent, "Linux") !== false OR mb_strpos($agent, "Lynx")!== false OR mb_strpos($agent, "Unix")!== false) $useragent['os'] = "Linux";
        elseif(mb_strpos($agent, "Macintosh") !== false OR mb_strpos($agent, "PowerPC")) $useragent['os'] = "Macintosh";
        elseif(mb_strpos($agent, "FreeBSD") !== false) $useragent['os'] = "FreeBSD";
        else $useragent['os'] = "undefined";    

        //Определяем браузер пользователя
        if(mb_strpos($agent, "Maxthon") !== false) $useragent['browser'] = "Maxthon";
        elseif(mb_strpos($agent, "MSIE") !== false) $useragent['browser'] = "Internet Explorer";
        elseif(mb_strpos($agent, "Firefox") !== false) $useragent['browser'] = "Firefox";
        elseif(mb_strpos($agent, "Opera") !== false) $useragent['browser'] = "Opera";
        elseif(mb_strpos($agent, "Netscape") !== false) $useragent['browser'] = "Netscape";
        elseif(mb_strpos($agent, "Safari") !== false AND mb_strpos($agent, "Chrome") === false) $useragent['browser'] = "Safari";
        elseif(mb_strpos($agent, "Chrome") !== false) $useragent['browser'] = "Chrome";
        else $useragent['browser'] =  "undefined";
        return $useragent;
    } 
    
    function user_agent_full(){
    global $list_browser, $list_os;
        main::required('includes/config/user_agents.php');
        $agent = kr_filter(get_user_agent(), TAGS);
        $useragent = array('browser' => "undefined", 'os' => "undefined");
        if(!$agent) return $useragent;
        //Определяем браузер пользователя
        $agent = mb_strtolower($agent);    
        foreach ($list_browser as $key => $value) {
            if(mb_strpos($agent, $key) !== false)  {
                if(is_array($value)) { 
                	$mathes = "";
                    if(preg_match($value[1], $agent, $mathes)) {
                        $useragent['browser'] = $value[0].' '.(isset($mathes[1])?$mathes[1]:'');
                        break;
                    }
                } else {
                    $useragent['browser'] = $value;
                    break;
                }  
            }   
        }
        //Определяем ОС  пользователя
        $agent = str_replace(' ', '', $agent);
        foreach ($list_os as $key => $value) {
            if(mb_strpos($agent, $key) !== false)  {
                if(is_array($value)) {
                    foreach ($value as $mask  => $os) {
                        if(mb_strpos($agent, $mask) !== false) {
                            $useragent['os'] = $os; 
                            break;
                        }
                    }
                } else {
                    $useragent['os'] = $value; 
                    break;
                }  
            }   
        }
        return $useragent;
    }    

    /**
    * Функция регистрирует сессию пользователя
    * 
    * @param mixed $login
    * @return void
    */
    function register($login){
    global $userconf;
        //Устанавливаем идентификатор сессии
        $this->id = session_id();
        //Устанавливаем имя пользователя, для которого открыта сессия
        $this->login = ($login!="Guest") ? $login : $userconf['guest_name'];
        //Устанавливаем время последней активности пользователя  
        $this->lastAction = time();
        //Устанавливаем идентификатор сессии в $_SESSION
        $_SESSION['id']    = $this->get_session_id();
        //Устанавливаем имя пользователя, для которого открыта сессия в $_SESSION
        $_SESSION['user'] = $this->get_user();
        //Устанавливаем время последней активности пользователя в $_SESSION
        $_SESSION['lastAction']    = time();
    }

    /**
    * Функция проверяет, открыта ли сессия пользователя 
    * 
    * @return bool
    */
    function is_session(){
        if(!isset($_SESSION['id']) OR !isset($_SESSION['user'])){return false;}
        if(!$this->is_inativo($_SESSION['lastAction'])){return false;}
        return true;
    }

    /**
    * Функция проверяет, истекло ли время сессии
    * 
    * @param string $lastAction
    * @return bool
    */
    function is_inativo($lastAction){
        if((time() - $lastAction) >= $this->maxInactiveTime){
            return false;
        } else {
            $_SESSION['lastAction'] = time();
            return true;
        }
    }
    
    /**
    * Функция удаления сессии
    * 
    * @return void
    */
    function kill_session_cache(){
        //Удаляем данные о пользователе
        if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
        //Удаляем сессию с именем пользователя
        if(isset($_SESSION['user'])) unset($_SESSION['user']);
        //Удаляем идентификатор сессии
        if(isset($_SESSION['id'])) unset($_SESSION['id']);
        //Удаляем сессию последнего действия пользователя
        if(isset($_SESSION['lastAction'])) unset($_SESSION['lastAction']);        
        if(isset($_SESSION['lastVisit'])) unset($_SESSION['lastVisit']);
        if(isset($_SESSION['uploaddir'])) unset($_SESSION['uploaddir']);
        //Удаляем cookies обновления сессии
        setcookies("", 'update_session', 1);        
        //Удаляем cookies обновления данных о пользователе
        setcookies("", 'online', 1);
    }

    /**
    * Функция разрушает все данные, зарегистрированные в сессии.
    * 
    * @return void
    */
    function destroy_session(){session_destroy();}
    
    /**
    * Функция возвращает ID сессии
    * 
    * @return string
    */
    function get_session_id(){return $this->id;}
    
    /**
    * Функция возвращает логин пользователя
    * 
    * @return string
    */
    function get_user(){return $this->login;}
    
    /**
    * Функция возвращает время на протяжении которого пользователь считается online
    * 
    * @return string
    */
    function get_max_seesion_time(){return $this->maxInactiveTime;}
    
    /**
    * Функция возвращает время последнего действия пользователя на сайте.
    * 
    * @return string
    */
    function get_last_action(){return $this->lastAction;}
}
?>
