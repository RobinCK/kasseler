<?php
/**
* Общие функции шаблонизатора
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/templates.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

class pub_list{
    var $row_tpl;
    var $tpl;
    var $content = "";
    var $replace_title = false;

    function pub_list($id = ''){
    global $load_tpl, $module_name;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(file_exists(TEMPLATE_PATH."{$load_tpl}/publisher_list-{$module_name}.tpl")) $file = TEMPLATE_PATH."{$load_tpl}/publisher_list-{$module_name}.tpl";
        else $file = TEMPLATE_PATH."{$load_tpl}/publisher_list.tpl";
        $content = file_get_contents($file);
        $this->row_tpl = preg_replace('/(.+?)<\!--BEGIN row-->(.+?)<\!--END row-->(.+?)$/si', "\\2", $content);
        $this->tpl = preg_replace('/<\!--BEGIN\srow-->(.+?)<\!--END\srow-->/si', '<!--content-->', $content);
    }

    function add_row($id, $pub){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();

        $this->content .= preg_replace_callback(
            '#\$pub\[([a-z_-]+)\]#is',
            function ($matches) use ($pub) {
                return $pub[$matches[1]];
            },
            $this->row_tpl
        );

        if(!$this->replace_title){

            $this->tpl = preg_replace_callback(
                '#\$pub\[([a-z_-]+)\]#is',
                function ($matches) use ($pub) {
                    return $pub[$matches[1]];
                },
                $this->tpl
            );

            $this->replace_title = true;
        }
    }

    function init($return=false){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $row = preg_replace('/<\!--content-->/si', $this->content, $this->tpl);
        if (!$return) {
            open();
            echo $row;
            close();
        } else return $row;
        return "";
    }
}

//Создает заголовок страницы а также keywords и description
function set_meta_value($array){
global $keywords, $description, $page_title, $config;
    if(hook_check(__FUNCTION__)) return hook();
    $page_title = "";
    $keywords = "";
    $description = "";
    $count = count($array);
    for ($i=0; $i<$count; $i++){
        $page_title .= ($i<$count-1) ? "{$array[$i]} {$config['separator']} " : "{$array[$i]}";
        $keywords .= $array[$i].", ";
        $description .= $array[$i].", ";
    }
    $page_title .= " @ {$config['home_title']}";
}

class tpl_create{
    var $design = true;
    var $rss = "";
    
    function title_insert(){
    global $page_title, $config, $modules, $module_name;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(isset($modules[$module_name]) AND !is_home()) return (empty($page_title)) ? "{$modules[$module_name]['title']} @ {$config['home_title']}" : $page_title;
        else return $config['home_title'];
    }
    
    //Функция генерации meta тегов
    function meta_insert(){
    global $config, $keywords, $description, $version_sys;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        return "<meta http-equiv='content-type' content='text/html; charset={$config['charset']}' />\n".
            "<meta name='author' content='".$config['home_title']."' />\n".
            "<meta name='copyright' content='Copyright (c) Kasseler CMS {$config['cms_version']}' />\n".
            "<meta name='resource-type' content='document' />\n".
            "<meta name='document-state' content='dynamic' />\n".
            "<meta name='distribution' content='global' />\n".
            "<meta name='robots' content='index, follow' />\n".
            "<meta name='revisit-after' content='1 days' />\n".
            "<meta name='rating' content='general' />\n".
            "<meta name='generator' content='Kasseler CMS {$version_sys}' />\n".
            "<meta name='description' content='{$description}{$config['description']}' />\n".
            "<meta name='keywords' content='{$keywords}{$config['keywords']}' />\n".
        "<base href='http://".get_host_name()."/' />";
    }

    function javascript_insert($text_only = false){
    global $load_tpl, $config, $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $js = array();
        if(!is_ajax()){
           if($config['jquery']!='noload') {
              switch ($config['jquery']){
                 case 'local': $js[] = array('includes/javascript/jquery/jquery.js', true); break;
                 case 'google': $js[] = array('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', true); break;
                 case 'yandex': $js[] = array('http://yandex.st/jquery/1.7.2/jquery.min.js', true); break;
                 case 'jquery': $js[] = array('http://code.jquery.com/jquery-1.7.2.min.js', true); break;
              }                
           }
           $js[] = array('includes/javascript/kr_ajax.js', true);
           $js[] = array('includes/javascript/function.js', true);
           if(defined("ADMIN_FILE")) $js[] = array('includes/javascript/adminJS.js', true);
        }
        
        $main->script = array_merge($js, $main->script);
        $script = "";
        if(!is_ajax()){
           $form_checked = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
           $script.="<script type='text/javascript'>var jsSecretID = '{$form_checked}';</script>\n";
        }
        $script_text='';$script_text_onload='';
        foreach($main->script as $l) {
            if(!empty($l[0]) AND $text_only==false){
                if($l[1]==true ) $script .= "<script type='text/javascript' src='{$l[0]}'></script>\n";
                else {if($l[2]) $script_text_onload .= "{$l[0]}\n"; else $script_text .= "{$l[0]}\n";}
            } else if(!empty($l[0]) AND $text_only==true AND $l[1]!=true) {if($l[2]) $script_text_onload .= "{$l[0]}\n"; else $script_text .= "{$l[0]}\n";};
        }
        if(!empty($script_text)) $script.="<script type='text/javascript'><!--\n{$script_text}\n//--></script>\n";
        if(!empty($script_text_onload)) $script.="<script type='text/javascript'><!--\n$(document).ready(function(){{$script_text_onload}});\n//--></script>\n";
        $js_source="";
        foreach($main->js_head_link as $l) if(!empty($l)) $js_source .= file_get_contents($l)."\n";
        foreach($main->js_head as $l) if(!empty($l)) $js_source .= $l."\n";
        if ($js_source!=""){$script.="<script type='text/javascript'><!--\n{$js_source}\n//--></script>\n";}
        return $script;
        
    }

    function link_insert($text_only = false){
    global $load_tpl, $userinfo, $links_text, $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(!is_ajax()) $main->link = array_merge(array(TEMPLATE_PATH."{$load_tpl}/style.css", 'includes/css/system.css'), $main->link);
        
        $link_browser = $link = '';
        if(mb_strpos(get_env('HTTP_USER_AGENT'), 'Chrome') !== false) $link_browser = (file_exists(TEMPLATE_PATH."{$load_tpl}/css/сhrome.css")) ? TEMPLATE_PATH."{$load_tpl}/css/сhrome.css" : '';
        elseif(preg_match('/Flock|Phoenix|Firebird|Firefox|Shiretoko|NetPositive/i', get_env('HTTP_USER_AGENT'))) $link_browser = (file_exists(TEMPLATE_PATH."{$load_tpl}/css/firefox.css")) ? TEMPLATE_PATH."{$load_tpl}/css/firefox.css" : '';
        elseif(mb_strpos(get_env('HTTP_USER_AGENT'), 'Opera') !== false) $link_browser = (file_exists(TEMPLATE_PATH."{$load_tpl}/css/opera.css")) ? TEMPLATE_PATH."{$load_tpl}/css/opera.css" : '';        
        elseif(mb_strpos(get_env('HTTP_USER_AGENT'), 'MSIE') !== false) $link_browser = (file_exists(TEMPLATE_PATH."{$load_tpl}/css/ie.css")) ? TEMPLATE_PATH."{$load_tpl}/css/ie.css" : '';        
        elseif(mb_strpos(get_env('HTTP_USER_AGENT'), 'Safari') !== false) $link_browser = (file_exists(TEMPLATE_PATH."{$load_tpl}/css/safari.css")) ? TEMPLATE_PATH."{$load_tpl}/css/safari.css" : '';        
        if(!empty($link_browser)) main::add2link($link_browser);
                
        if($text_only==false) foreach($main->link as $l) if(!empty($l)) $link .= !is_ajax() ? "<link rel='stylesheet' href='{$l}' type='text/css' />\n" : "<script type='text/javascript'>KR_AJAX.include.style('{$l}')</script>";
        
        $css_source="";
        foreach($main->css_head_link as $l) if(!empty($l)) $css_source .= file_get_contents($l)."\n";
        foreach($main->css_head as $l) if(!empty($l)) $css_source .= $l."\n";
        
        if($css_source!=""){$link.="<style type='text/css'>\n{$css_source}\n</style>\n";}
        $link = $links_text.$link;
        return !is_ajax() ? "<link rel='shortcut icon' href='http://".get_host_name()."/favicon.ico' type='image/x-icon' />\n".$link.$this->rss : $link;
    }

    //Функция вывода списка Глобальных переменных
    function variable_insert(){
    global $variable;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        return "<div class='variables-title' id='variables-title' onclick=\"\$('#variables').toggle('slow');\">Variables Inform</div>".
        "<div class='variables' id='variables' style='display: none;' align='left'>{$variable['GET']}{$variable['POST']}{$variable['SESSION']}{$variable['SESSIONID']}{$variable['COOKIE']}{$variable['FILES']}</div>";
    }

    //Функция вывода списка запросов к базе данных
    function query_insert(){
    global $db;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(defined("INSTALLCMS") OR defined("ADMIN_FILE")) return "";
        $query = '';
        foreach($db->time_query as $v) {
            list($total_tdb, $q) = $v;
            $query .= ($total_tdb > 0.01) ? "<div align='left' class='debbug_query'><font color='red' style='float: left;'><b>{$total_tdb}</b></font><pre>[".htmlspecialchars(trim($q), ENT_QUOTES)."]</pre></div>\n" : "<div align='left'><font color='green' style='float: left;'><b>{$total_tdb}</b></font><pre>[".htmlspecialchars(trim($q), ENT_QUOTES)."]</pre></div>\n";
        }
        return ($query!="") ? "<div onclick=\"\$('#query').toggle('slow');\" class='query-title' id='query-title'>Query Inform</div><div style='display: none;' class='query' id='query'>{$query}</div>" : "";
    }
    

    function tpl_creates(){
    global $template, $load_tpl, $load_tpl, $config, $add_style, $agentinfo, $lang, $main;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        //Проверяем события планировщика
        $this->check_planner();
        //if(file_exists(TEMPLATE_PATH."{$load_tpl}/css/".mb_strtolower($agentinfo['browser']).".css")) main::add2link(TEMPLATE_PATH."{$load_tpl}/css/".mb_strtolower($agentinfo['browser']).".css");
        main::add2script('includes/javascript/jquery/jquery.json.js');
        main::add2script('includes/javascript/jquery/jquery.tpl.js');
        main::add2script('includes/javascript/jquery/jquery.guiders.js');
        main::add2script('includes/javascript/jquery/jquery.event.drag.js');
        
        main::add2script('includes/javascript/jquery/lazyload/jquery.lazyload.js');
        
        main::add2script('includes/javascript/jquery/chosen/chosen.jquery.js');
        main::add2link('includes/javascript/jquery/chosen/chosen.css');
        $useri = &$main->user;
        $template->template = preg_replace_callback('#\$lang\[([a-z_-]+)\]#is', function($matches) use ($lang) { return array_value_set($matches[1], $lang);} , $template->template);
        $template->template = preg_replace_callback('#\$user\[([a-z_-]+)\]#is', function($matches) use ($useri) { return array_value_set($matches[1], $useri);} , $template->template);
        $template->template = preg_replace_callback('#\$module\[([a-z_-]+)\]#is', function($matches) { return link_inserted($matches[1]);} , $template->template);
        $script = $this->javascript_insert();
        $link = $this->link_insert();
        $template->set_tpl(array(
            'host'          => get_host_name(),
            'load_tpl'      => $load_tpl,  //Вставка названия текущего шаблона
            'title'         => $this->title_insert(), //Вставка заголовка страницы
            'meta'          => $this->meta_insert(), //Вставка мета тегов
            'script'        => mb_substr($script, 0, mb_strlen($script)-1), //Вставка javascript
            'link'          => mb_substr($link, 0, mb_strlen($link)-1).$add_style, //Вставка таблиц стилей
            'license'       => "Copyright &copy;2007-".date('Y')." by <a href='http://www.kasseler-cms.net/' target='_BLANK' title='Content Management System'>Kasseler CMS</a>. All rights reserved.",
            'var_info'      => (($config['variables']==1) OR ($config['variables']==2 AND is_admin())) ? $this->variable_insert() : "", //Вставка анализа переменных
            'query_info'    => (($config['query']==1) OR ($config['query']==2 AND is_admin())) ? $this->query_insert() : "", //Вставка запросов к базе данных
        ));
        //Подключение дополнительных пользовательских функций шаблона
        if(file_exists(TEMPLATE_PATH."{$load_tpl}/tpl.php")) main::required(TEMPLATE_PATH."{$load_tpl}/tpl.php");

        if(is_ajax()){ 
            echo $this->javascript_insert();
            echo $this->link_insert();
        }
    }
    
    function check_planner(){
       global $planner, $config;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(!isset($config['othe_cron']) OR $config['othe_cron']!='on'){
          foreach($planner as $id => $run){
             if(gmdate('Y-m-d', strtotime($run['nextrun']))<=gmdate('Y-m-d H:i')){
                if(gmdate('H:i', strtotime($run['nextrun']))<=gmdate('H:i') AND in_array(gmdate('w'), explode(',', $run['weekdays'])) AND $run['status']==ENABLED) {
                   main::add2script("addEvent(window, 'load', function(){haja({action:'index.php?ajaxed=runer', animation:false}, {}, {})});", false);
                   break;
                }
             }
          }
       }
    }
}

function link_inserted($p){
global $main;
    return $main->url(array('module' => $p));
}

function open_table($return=false){
global $template;
    if(hook_check(__FUNCTION__)) return hook();
    $template->get_tpl('open_table', 'open_table');
    if($return) return $template->tpl_create(true, 'open_table');
    else echo $template->tpl_create(true, 'open_table');
}

function close_table($return=false){
global $template;
    if(hook_check(__FUNCTION__)) return hook();
    $template->get_tpl('close_table', 'close_table');
    if($return) return $template->tpl_create(true, 'close_table');
    else echo $template->tpl_create(true, 'close_table');
}

function warning($string, $return=false){
global $module_name, $template;
    if(hook_check(__FUNCTION__)) return hook();
    $template->get_tpl('warning', 'warning');
    $template->set_tpl(array('content' => $string), 'warning');
    if($return) return $template->tpl_create(true, 'warning');
    else echo $template->tpl_create(true, 'warning');
}

function info($string, $return=false){
global $module_name, $template;
    if(hook_check(__FUNCTION__)) return hook();
    if(!defined("ENGINE")){
        $template->get_tpl('info', 'info');
        $template->set_tpl(array('content' => $string), 'info');
        if($return) return $template->tpl_create(true, 'info');
        else echo $template->tpl_create(true, 'info');
    }
}

function open($return=false){
    if(hook_check(__FUNCTION__)) return hook();
    return open_table($return);
}

function close($return=false){
    if(hook_check(__FUNCTION__)) return hook();
    return close_table($return);
}

function title($title_set, $return=false){
global $module_name;
    if(hook_check(__FUNCTION__)) return hook();
    $title = open(true);
    $title .= "<h1 id='style_{$module_name}' class='module_title'>{$title_set}</h1>";
    $title .= close(true);
    if($return) return $title;
    else echo $title;
}

function total_pages($numrows, $limit, $numpages){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<h4>".preg_replace(array("#{TOTAL}#", "#{PAGES}#", "#{COUNT}#"), array($numrows, $numpages, $limit), $lang['total_pages'])."</h4>";
}

function pages($numrows, $limit, $url_array, $total=false, $return=false, $last_url_array=array(), $parse_get=false, $table_align=''){
global $main, $lang, $adminfile, $links_text;
    if(hook_check(__FUNCTION__)) return hook();
    $rew = $main->mod_rewrite; $content = '';
    if(defined('ADMIN_FILE')) $main->mod_rewrite = false;
    if(isset($_GET['page'])) add_meta_value($lang['title_page'].intval($_GET['page']));
    $numpages = ceil($numrows / $limit); $pagenum = (isset($_GET['page']) AND !empty($_GET['page'])) ? intval($_GET['page']) : 1;
    if($pagenum<=$numpages){
        if ($numpages > 1) {
            $content = "<div class='basepub'><div class='basenumbers'><div class='binner'><div class='numbers'>";
            if($total) $content .= total_pages($numrows, $limit, $numpages);
            $content .= "<table".(!empty($table_align)?" align='{$table_align}' style='margin: 0;'":'')."><tr><td>";
            if(isset($pagenum) AND $pagenum>1) {
                $prev_link = $main->url(array_merge($url_array, array('page' => $pagenum-1), $last_url_array), defined('ADMIN_FILE')?$adminfile:"").($parse_get==true?parse_get(array('page', 'delete', 'change_op'), array_merge($url_array, $last_url_array)):"");
                $links_text .= "<link rel='prev' href='{$prev_link}' />\n";
                $content .= "<a class='sys_link' href='{$prev_link}'><b class='arrowleft'>&#171;</b></a>";
            }
            for ($i=1;$i<=$numpages;$i++) {
                if ($i == $pagenum) $content .= "<span class='noselect'><b>{$i}</b></span>\n";
                elseif((($i > ($pagenum - 5)) AND ($i < ($pagenum + 5))) OR ($i == $numpages) OR ($i == 1)) $content .= "<a class='sys_link' href='".$main->url(array_merge($url_array, array('page' => $i), $last_url_array), defined('ADMIN_FILE')?$adminfile:"").($parse_get==true?parse_get(array('page', 'delete', 'change_op'), array_merge($url_array, $last_url_array)):"")."'><b>{$i}</b></a>\n";
                if ($i<$numpages AND ($pagenum>6 AND $i==1) OR ($pagenum<$numpages-5 AND $i==$numpages-1)) $content .= "<span class='more'><b>...</b></span>\n";
            }
            if($pagenum<$numpages) {
                $next_link = $main->url(array_merge($url_array, array('page' => $pagenum+1), $last_url_array), defined('ADMIN_FILE')?$adminfile:"").($parse_get==true?parse_get(array('page', 'delete', 'change_op'), array_merge($url_array, $last_url_array)):"");
                $links_text .= "<link rel='next' href='{$next_link}' />\n";
                $content .= "<a class='sys_link' href='{$next_link}'><b class='arrowright'>&#187;</b></a>";
            }
            $content .= "</td></tr></table></div></div></div></div>";
        }
        if($return) return $content;
        else echo $content;
        $main->mod_rewrite = $rew;
    } else redirect(MODULE);
    return "";
}

function loads_tpl($file){
global $module_name, $load_tpl;
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists("{$file}-{$module_name}.tpl")) $tpl_file = "{$file}-{$module_name}.tpl";
    else $tpl_file = "{$file}.tpl";
    return str_replace('$load_tpl', $load_tpl, file_get_contents($tpl_file));
}
?>
