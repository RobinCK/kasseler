<?php
/**
* Основной класс системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/main.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

class main {
    /**
    * Параметр ЧПУ
    * 
    * @var bool
    */
    public $mod_rewrite = false;
    
    /**
    * Массив ссылок
    * 
    * @var array
    */
    public $links = array();
    
    /**
    * Название текущего модуля
    * 
    * @var string
    */
    public $module;
    
    /**
    * Заголовок текущего модуля
    * 
    * @var string
    */
    public $title;
    
    /**
    * Название текущего шаблона
    * 
    * @var string
    */
    public $tpl;
    
    /**
    * Информация о пользователе
    * 
    * @var array
    */
    public $user;
    
    /**
    * Текущая локализация
    * 
    * @var string
    */
    public $language;
    
    /**
    * Объект базы данных
    * 
    * @var sql_db
    */
    public $db;
    
    /**
     * Языковый параментр
     * 
     * @var string
     */
    public $lang;
    
    /**
    * Массив изображений
    * 
    * @var array
    */
    public $img;
    
    /**
    * IP адрес пользователя
    * 
    * @var string
    */
    public $ip;
    
    /**
    * USER AGENT пользователя
    * 
    * @var string
    */
    public $agent;
    
    /**
    * Общая конфигурация системы
    * 
    * @var array
    */
    public $config;
    
    /**
    * REFERER пользователя
    * 
    * @var string
    */
    public $ref;
    
    /**
    * REQUEST_URI пользователя  
    * 
    * @var string
    */
    public $uri;
    
    /**
    * Доменное имя сайта
    * 
    * @var string
    */
    public $host;
    
    /**
    * Массив правил для добавления пунктов
    * 
    * @var array
    */
    public $points;
    
    /**
    * Флаг идентификатора
    * 
    * @var bool
    */
    public $rewrite_id = true;
    
    public $is_moile = false;
    
    /**
    * Массив хуков
    * 
    * @var array
    */    
    public $hooks = array();
    public $hooks_info = array();
    
    /**
    * Массив подключенных файлов
    * 
    * @var array
    */
    public $require_files = array();

    /**
    * Массив подключенных  файлов конфигурации
    * 
    * @var array
    */
    public $conf = array();     
    
    public $tpl_tag = array();
    
    public $css_head_link=array();
    public $css_head=array();
    public $js_head_link=array();
    public $js_head=array();
    public $script = array();
    public $link = array();
    
    /**
    * Конструктор класса
    * 
    * @return void
    */
    public function __construct(){
    global $config;
        if($config['rewrite']==ENABLED) $this->mod_rewrite = true;
    }
    
    public static function add_template_tag($tag, $content){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $main->tpl_tag[$tag] = $content; 
    }
    
    public static function add2script($text, $link=true, $onload=false){
    global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(!in_array(array($text, $link, $onload), $main->script)) {
            if(!is_ajax()) $main->script[] = array($text, $link, $onload);
            else {
                if($link==true) echo "<script type='text/javascript'>KR_AJAX.include.script('{$text}')</script>";
                else echo "<script type='text/javascript'><!--\n{$text}\n//--></script>";
            }
            return true;
        } else return false;
    }

    public static function add2link($url){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(!in_array($url, $main->link)) {
            if(!is_ajax()) $main->link[] = $url;
            else echo "<script type='text/javascript'>KR_AJAX.include.style('{$url}')</script>";
            return true;
        } else return false;
    }
    
    /**
    * Добавляет $css_text в style head страницы
    * 
    * @param mixed $css_text
    */
    public static function add_css2head($css_text){
    global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       $main->css_head[] = $css_text;
    }
    
    /**
    * Присоеденяет $file в head страницы
    * 
    * @param mixed $file
    */
    public static function add_cssfile2head($file){
    global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(!in_array($file, $main->css_head_link)) $main->css_head_link[] = $file;
    }
    
    public static function add_javascript2body($js_text){
    global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       $main->js_head[] = $js_text;
    }
    
    public static function add_javascript_file2body($file){
    global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(!in_array($file, $main->js_head_link)) $main->js_head_link[] = $file;
    }
    
    public static function add_js_function($functions){
       global $main;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(is_array($functions)){
          foreach ($functions as $key => $value) {
             $file="includes/javascript/function/{$value}.js";
             if(!in_array($file, $main->js_head_link)) $main->js_head_link[] = $file;
          }
       } else {
          $file="includes/javascript/function/{$functions}.js";
          if(!in_array($file, $main->js_head_link)) $main->js_head_link[] = $file;
       }
    }

    /**
    * Инициализация глобальных переменных
    * 
    * @return void
    */
    public function init(){
    global $module_name, $userinfo, $load_tpl, $db, $module_title, $language, $img, $ip, $agentinfo, $config, $points;
        $this->module = &$module_name;
        $this->tpl = &$load_tpl;
        $this->title = &$module_title;
        $this->user = &$userinfo;
        $this->db = &$db;
        $this->language = &$language;
        $this->img = &$img;
        $this->ip = &$ip;
        $this->agent = &$agentinfo;
        $this->config = &$config;
        $this->points = &$points;
        $referer=get_env('HTTP_REFERER');
        $this->ref = (isset($referer) ? $referer : "");
        $this->uri = get_env('REQUEST_URI');
        $this->host = get_host_name();
        $this->rewrite_id = (isset($_GET['mod_rewrite']) OR (is_home() AND $config['rewrite']==ENABLED) OR ($config['rewrite']==ENABLED AND defined("ADMIN_FILE"))) ? true : false;
    }
    
    /**
    * Функция подключения файлов
    * 
    * @param mixed $files
    * @param string $path
    * @param string $prefix
    * @param string $context
    * @param string $eval
    * @return void
    */
    public function require_file(&$files, $path='', $prefix='', $context='', $eval='', $ext='.php'){
    global $lang, $img;
        if(!is_array($files)) $files = array($files);
        foreach($files AS $f) {                                                                                                      //Перебираем параметры и пытаемся подключить
            if(!in_array($prefix.$f, $this->require_files) AND !empty($f)){                                                          //Проверяем подключен ли уже текущий файл
                //Проверяем наличие хука
                if(isset($this->conf['hooks']) AND isset($this->conf['hooks'][$path.$f]) AND $this->conf['hooks'][$path.$f]['type']=='file'){
                    if(file_exists("hooks/".$this->conf['hooks'][$path.$f]['file'])) {                                               //Проверяем наличие файла
                        require 'hooks/'.$this->conf['hooks'][$path.$f]['file'];                                                     //Подключаем файл
                        $this->require_files[] = 'hooks/'.$this->conf['hooks'][$path.$f]['file'];                                    //Заносим файл в массив для будущей проверки подключения
                        if(!empty($eval)) eval($eval);
                    //В случаи неудачи выводим ошибку
                    } else trigger_error("SYSTEM ERROR: include hook file 'hooks/{$context}{$f}{$ext}' not exists.", E_USER_WARNING);
                } else {
                    if(file_exists("{$path}{$context}{$f}{$ext}")) {                                                                   //Проверяем наличие файла
                        require "{$path}{$context}{$f}{$ext}";                                                                         //Подключаем файл
                        $this->require_files[] = $prefix.$f;                                                                         //Заносим файл в массив для будущей проверки подключения
                        if(!empty($eval)) eval($eval);
                    } else trigger_error("SYSTEM ERROR: include file '{$path}{$context}{$f}{$ext}' not exists.", E_USER_WARNING);      //В случаи неудачи выводим ошибку
                }
            }
        }
    }
    
    public static function required($file){
    global $main;
        $_vars = (!is_array($file)) ? func_get_args() : $file;
        $main->require_file($_vars, '', '', '', '', '');
    }
    
    /**
    * Функция подключения системного API
    * 
    * @param mixed $file
    * @return void
    */
    public static function init_function($file){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $vars = (!is_array($file)) ? func_get_args() : $file;
        $main->require_file($vars, 'includes/function/', 'function_');
    }
    
    /**
    * Функция подключения системнных классов
    * 
    * @param mixed $file
    * @return void
    */
    public static function init_class($file){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $_vars = (!is_array($file)) ? func_get_args() : $file;
        $vars = array();
        foreach($_vars as $f) $vars[] = $f.'.class';
        $main->require_file($vars, 'includes/classes/', 'class_');
    }
    
    /**
    * Функция подключения языковых файлов
    * 
    * @param string $file
    * @param string $path
    * @return void
    */
    public static function init_language($file, $path=null, $prefix=null){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $eval = 'global $main, $lang; if(empty($main->lang)) $main->lang = array(); $main->lang = array_merge($main->lang, $lang);$lang = $main->lang;';
        //$vars = (!is_array($file)) ? func_get_args() : $file;
        $main->require_file($file, $path!=null?$path:'includes/language/'.$main->language.'/', $prefix!=null?$prefix:'language_', '', $eval);
    }
    
    /**
    * Универсальная функция подключения файлов
    * 
    * @param mixed $file
    * @return void
    */
    public static function inited($file){
    global $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $_vars = (!is_array($file)) ? func_get_args() : $file;
        $require = array('class' => array(), 'function' => array(), 'language' => array());
        foreach($_vars as $f) {preg_match('/(.*?)\.(.*)/', $f, $prefix); $require[$prefix[1]][] = $prefix[2];}
        if(!empty($require['class'])) main::init_class($require['class']);
        if(!empty($require['language'])) main::init_language($require['language']);
        if(!empty($require['function'])) main::init_function($require['function']);
    }

    /**
    * Функция генерации ссылок
    * 
    * @param array $link
    * @param string $return
    * @param array $addon дополнительные параметры(jscript линки)
    * @return string
    */
    public function url($link, $type_link="", $return="",$addon=array()){
    global $config, $languages2code;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if($config['multilanguage']==ENABLED AND isset($_COOKIE['lang']) AND count($link)>0 AND isset($languages2code[$_COOKIE['lang']])) $link = array('lang' => $languages2code[$_COOKIE['lang']])+$link;
        if(count($link)==0) return 'http://'.get_host_name().'/';
        else {
            if($this->mod_rewrite){
                foreach ($link as $kay=>$value) $return .= $config['separator_rewrite'].$value;
                $return = mb_substr($return, 1, mb_strlen($return)).$config['file_rewrite'];
            } else {
                $is_java_link = (array_key_exists('jscript',$addon) OR is_ajax());
                foreach ($link as $kay=>$value) $return .= "{$kay}={$value}".($is_java_link?"&":"&amp;");
                $return = (empty($type_link)?"index.php?":"{$type_link}?").mb_substr($return, 0, ($is_java_link?mb_strlen($return)-1:mb_strlen($return)-5));
            }
        }
        $this->links[] = $return;
        return 'http://'.get_host_name().'/'.$return;
    }
    
    /**
    * Модификация $this->url для JScript ссылок
    */
    public function urljs($link, $type_link="", $return=""){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $mod = $this->mod_rewrite;
        $this->mod_rewrite = false;
        $url=$this->url($link, $type_link, $return);
        $this->mod_rewrite = $mod;
        return str_replace("&amp;","&",$url);
    }
    
    /**
    * Функция разбора конфигурации ЧПУ
    * 
    * @param array $mod
    * @return void
    */
    public function parse_rewrite($mod=array()){
    global $rewrite, $config, $code2languages;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        
        if(!$this->rewrite_id) return false; 
        else foreach($_GET as $kay=>$value) unset($kay);
        
        $rewrite = (empty($mod)) ? $rewrite : $mod;
        if(!empty($config['file_rewrite'])) $file_type = explode($config['file_rewrite'], mb_substr(get_env('REQUEST_URI'), 1, mb_strlen(get_env('REQUEST_URI'))));
        else $file_type = array(substr(get_env('REQUEST_URI'), 1, strlen(get_env('REQUEST_URI'))));
        $key_corect = 0;
        foreach($_GET as $key => $value) if($key!='mod_rewrite') unset($_GET[$key]);
        foreach (explode($config['separator_rewrite'], $file_type[0]) as $kay=>$value){
            if($config['multilanguage']==ENABLED AND isset($code2languages[$value])){
                if(!isset($_COOKIE['lang']) OR $_COOKIE['lang']!=$code2languages[$value]) setcookies($code2languages[$value], 'lang');
                $_COOKIE['lang'] = $code2languages[$value];
                $key_corect++;
                continue;
            }
            if(isset($rewrite[$kay-$key_corect])) $_GET[$rewrite[$kay-$key_corect]] = (mb_strpos($value, "%") === false) ? strip_tags($value) : strip_tags(urldecode($value));
        }
        return true;
    }
}
$main = new main();

?>
