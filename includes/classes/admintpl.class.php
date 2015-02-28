<?php
/**
* Файл построения шаблона админ панели
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/admintpl.php
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die('Access is limited');

global $default_modules_admin, $main;

main::required('includes/config/admin_panel.php');
main::init_function('admin', 'scan_dir');


class admin_tpl extends tpl_create {
    function title_insert(){
    global $page_title, $config;
        return (empty($page_title)) ? $config['home_title'] : $page_title;
    }

    function menu_create($navi){
    global $lang, $adminfile;
        if(empty($navi) OR count($navi)==0 OR !isset($_GET['module'])) return "";
        $menu = "";
        $title = $lang['home'];
        if(isset($navi) AND is_array($navi)){
            foreach($navi as $arr){
                if(isset($_GET['do']) AND $_GET['do']==$arr[0]) $title = isset($lang[$arr[1]]) ? $lang[$arr[1]] : $arr[1];
                $menu .= !empty($arr[0]) ? " | <a href='{$adminfile}?module={$_GET['module']}&amp;do={$arr[0]}' title='".(isset($lang[$arr[1]]) ? $lang[$arr[1]] : $arr[1])."'>".(isset($lang[$arr[1]]) ? $lang[$arr[1]] : $arr[1])."</a> " : "<a href='{$adminfile}?module={$_GET['module']}' title='".(isset($lang[$arr[1]]) ? $lang[$arr[1]] : $arr[1])."'>".(isset($lang[$arr[1]]) ? $lang[$arr[1]] : $arr[1])."</a>";
            }
        }
        return array($menu, $title);
    }

    function admin_content(){
    global $main, $msg, $link, $serverinfo, $navi, $title, $modules_links, $parametr_design, $default_modules_admin, $template;
    
        main::init_function('get_php_content', 'module_exists');
        
        $parametr_design = true;
        if(is_home()){
            $default_modules_admin = empty($default_modules_admin) ? array('management') : $default_modules_admin;
            $a = $m = array();
            foreach(scan_dir("administrator/modules/", '/(.+?)\.php$/i') as $value) $a[] = preg_replace('/(.+?)\.php$/i', '\\1', $value);
            if(($handle = opendir("modules/"))){ 
                while (false !== ($file = readdir($handle))) if (is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $m[] = $file;
            }
            $content = ""; $error_load = false;
            $module = $main->module;
            foreach($default_modules_admin as $val){
                ob_start();
                $_GET['module'] = $val; $main->module = $val;
                if(!empty($a) AND in_array($val, $a) AND file_exists("administrator/modules/{$val}.php")) main::required("administrator/modules/{$val}.php");
                elseif(!empty($m) AND in_array($val, $m) AND file_exists("modules/{$val}/admin/index.php")) main::required("modules/{$val}/admin/index.php");
                else $error_load = true;
                $_GET['module'] = $module; $main->module = $module;
                $res = $this->menu_create($navi);
                $menu = (isset($res[0])) ? $res[0] : "";
                $modules_c = ob_get_contents(); ob_get_clean();
                $template->get_tpl('admin_content','admin_content');
                $row = array(
                   'title' => isset($main->lang[$val])?$main->lang[$val]:$val, 
                   'content' => ($error_load==false)?$modules_c:warning($main->lang['error_load_amin_module'], true),
                   'link' => $menu, 
                );
                $template->set_tpl(hook_set_tpl($row,__FUNCTION__),'admin_content',array('start' => '$', 'end' => ''));
                $content .= $template->tpl_create(true,'admin_content');
            } 
            if(is_home()) unset($_GET['module']); else $_GET['module']=$module;
            if(!is_ajax()) return (!empty($msg) ? warning($msg, true) : "").$content;
            //else echo (!empty($msg) ? warning($msg, true) : "").$management_c;
        } else {
            if(admin_module_exists('administrator/modules/', $main->module, '.php') OR user_module_exists('modules/', $main->module, '/admin/index.php')){
                $loaded = file_exists("administrator/modules/{$main->module}.php") ? "administrator/modules/{$main->module}.php" : "modules/{$main->module}/admin/index.php";
                $content = get_php_content($loaded, 'global $navi, $title;');
                if(!$parametr_design OR !$this->design) page($content, $this->title_insert());
                $res = $this->menu_create($navi);
                $menu = (isset($res[0])) ? $res[0] : "";
                $title = (isset($res[1])) ? $res[1] : "";
                if(in_array($main->module, array("serverinfo"))) $serverinfo = array('content'=>$content, 'title'=>(isset($title)?$title:$main->lang['home']));
                if(file_exists("administrator/links/{$main->module}.php") OR file_exists("modules/{$main->module}/admin/link.php")){
                    $link = array();
                    $loaded = file_exists("administrator/links/{$main->module}.php") ? "administrator/links/{$main->module}.php" : "modules/{$main->module}/admin/link.php";
                    require_once $loaded;
                    $modules_links[$main->module] = $link;
                    $path = isset($link['icon_patch']) ? $link['icon_patch'] : 'includes/images/admin/';
                    $link = array(
                        'name'   =>  isset($main->lang[$link['name']]) ? $main->lang[$link['name']] : $link['name'],
                        'ico'    =>  (isset($link['ico']) AND file_exists("{$path}{$link['ico']}")) ? "{$path}{$link['ico']}" : "includes/images/admin/ico.png",
                    );
                    if(!empty($title)) set_meta_value((($title!=$main->lang['home'])?array($title):array())+array($link['name']));
                    elseif(isset($_GET['module'])) set_meta_value(array($link['name']));
                    $template->get_tpl('modules_content','modules_content');
                    $row = array(
                       'load_tpl' => $main->tpl, 
                       'module' => $link['name'],
                       'main_module' => !empty($title)?$link['name']:$main->lang['home'], 
                       'link' => $menu, 
                       'title' => !empty($title)?"/ ".$title:"", 
                       'ico' => $link['ico'], 
                       'content' => "<div id='ajax_content'>{$content}</div>");
                    $template->set_tpl(hook_set_tpl($row,__FUNCTION__),'modules_content',array('start' => '$', 'end' => ''));
                    $file_tpl = $template->tpl_create(true,'modules_content');
                    if(!is_ajax()) return (!empty($msg) ? warning($msg, true) : "").$file_tpl;
                    else echo (!empty($msg) ? warning($msg, true) : "").$content;
                } else {
                    if(isset($_GET['module'])){
                        $title = (isset($main->lang[$_GET['module']])) ? $main->lang[$_GET['module']] : $_GET['module'];
                        add_meta_value($title);
                    } else $title = "";
                    $template->get_tpl('admin_content','admin_content');
                    $row = array(
                       'title' => $title, 
                       'content' => $content,
                       'link' => $menu, 
                    );
                    $template->set_tpl(hook_set_tpl($row,__FUNCTION__),'admin_content',array('start' => '$', 'end' => ''));
                    $file_tpl = $template->tpl_create(true,'admin_content');
                    if(!is_ajax()) return (!empty($msg) ? warning($msg, true) : "").$file_tpl;
                    else echo (!empty($msg) ? warning($msg, true) : "").$content;
                }
            }
        }
        return true;
    }
    
    function check_root_menu($admin_menu){
        $tmp = "";
        if(isset($_GET['module'])){
            foreach($admin_menu as $key=>$arr){
                $tmp = $key;
                foreach($arr['submenu'] as $a){
                    $parsed = parse_url($a['link']);
                    if(isset($parsed['query'])){
                        $par = "";
                        parse_str($parsed['query'], $par);
                        if($_GET['module']==$par['module']) return $tmp;
                    }
                }
            }
        } else return 'home';
        return 'home';
    }
    
    function admin_menu(){
    global $admin_menu, $lang;
        $menu = "";
        main::required("administrator/menu.php");
        $root_menu = $this->check_root_menu($admin_menu);
        $mod  = (isset($_GET['module']) AND isset($admin_menu[$_GET['module']])) ? $_GET['module'] : $root_menu;
        foreach($admin_menu as $key=>$arr){
            $title = (isset($lang[$key])) ? $lang[$key] : $key;
            $ac = ($mod == $key) ? " class='ac'" : "";
            $menu .= "<a".(empty($arr['link'])?" onclick='return false;'":"")." href='".(!empty($arr['link'])?$arr['link']:"#")."'{$ac} id='{$key}'><b>{$title}</b></a> ";
        }
        $subemu = "";
        $mod = (isset($_GET['module']) AND isset($admin_menu[$_GET['module']])) ? $_GET['module'] : $root_menu;
        if(isset($admin_menu[$mod])){
             foreach($admin_menu[$mod]['submenu'] as $key=>$arr){
                 $title = (isset($lang[$arr['title']])) ? $lang[$arr['title']] : $arr['title'];
                 $subemu .= "<a href='{$arr['link']}'>{$title}</a>";
             }
        } 
        $js_menu = "\n<script type='text/javascript'>var menu ={";
        foreach($admin_menu as $key=>$arr){
            $js_menu .= "'{$key}':[";
            foreach($admin_menu[$key]['submenu'] as $sm){
                $title = (isset($lang[$sm['title']])) ? $lang[$sm['title']] : $sm['title'];
                $js_menu .= "['{$title}', '{$sm['link']}'],";
            }
            $js_menu = mb_substr($js_menu, 0, mb_strlen($js_menu)-1);
            $js_menu .= "],";
        }
        $js_menu = mb_substr($js_menu, 0, mb_strlen($js_menu)-1)."}</script>";
        return array($menu, $subemu, $js_menu);
    }
    
    function modules_block(){
    global $main, $adminfile, $modules_links, $template;
        $content = "<span id='mod_con'></span>";
        $modules = array();
        if(($handle = opendir("modules/"))){
            while (false !== ($file = readdir($handle))) if (is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $modules[] = $file;
            closedir($handle);
        }
        sort($modules);
        for($i=0;$i<count($modules);$i++){
            $link = array();
            if(!isset($modules_links[$modules[$i]]) AND file_exists("modules/{$modules[$i]}/admin/link.php")) require_once "modules/{$modules[$i]}/admin/link.php";
            else $link = $modules_links[$modules[$i]];
            $link['name'] = (isset($main->lang[$link['name']])) ? $main->lang[$link['name']] : $link['name'];
            $path = isset($link['icon_patch']) ? $link['icon_patch'] : 'includes/images/admin/' ;
            $link['ico'] = (isset($link['ico']) AND file_exists("{$path}{$link['ico']}")) ? "{$path}{$link['ico']}" : "includes/images/admin/ico.png";
            if(isset($link['desc'])) $link['desc'] = (isset($main->lang[$link['desc']])) ? "<i>".$main->lang[$link['desc']]."</i>" : "<i>".$link['desc']."</i>";
            else $link['desc'] = '';
            $ac = (isset($_GET['module']) AND $_GET['module']==$modules[$i]) ? " class='ac'" : "";
            $content .= "<a id='mod_{$modules[$i]}' href='{$adminfile}?module={$modules[$i]}' title='{$link['name']}'{$ac}><span><img src='{$link['ico']}' alt='' /><b>{$link['name']}</b><br />{$link['desc']}</span></a>";
        }          
        $content .= "<script type='text/javascript'>addEvent(window, 'load', function(){ScrollTo('".(isset($_GET['module']) ? "mod_".$_GET['module'] : '')."')});</script>";
        $template->get_tpl('block-modules','block-modules');
        $row = array('title' => $main->lang['modules'], 'content' => $content);
        $template->set_tpl(hook_set_tpl($row,__FUNCTION__),'block-modules',array('start' => '$', 'end' => ''));
        return $template->tpl_create(true,'block-modules');
    }
    
    function block_info(){
    global $adminfile, $main, $title, $serverinfo;
        $key = (!isset($_POST['key']))?0:$_POST['key'];
        $mods = array("systeminfo", "serverinfo");
        $mods_count = count($mods);
        if(!isset($serverinfo['content'])){
            ob_start();
            main::required("administrator/modules/{$mods[$key]}.php");
            $content = ob_get_contents(); ob_get_clean();
        } else {
            $content = $serverinfo['content'];
            $title = $serverinfo['title'];
        }
        $rows1 = ($key!=0) ? "<a href='#' onclick=\"loadinfo('{$adminfile}', ".($key-1)."); return false;\" class='leftarrow' title='<'><img src='".TEMPLATE_PATH."{$main->tpl}/images/spacer.png' alt='<' /></a>" : "";
        $rows2 = ($key!=$mods_count-1) ? "<a href='#' onclick=\"loadinfo('{$adminfile}', ".($key+1)."); return false;\" class='rightarrow' title='>'><img src='".TEMPLATE_PATH."{$main->tpl}/images/spacer.png' alt='>' /></a>" : "";
        if(!is_ajax()){
            $serverinfo = array('content'=>$content, 'title'=>$title);
            return str_replace(array('$rows', '$title', '$content'), array($rows1.$rows2, $title, $content), file_get_contents(TEMPLATE_PATH."{$main->tpl}/block-info.tpl"));
        } else echo str_replace(array('$rows', '$title', '$content'), array($rows1.$rows2, $title, $content), file_get_contents(TEMPLATE_PATH."{$main->tpl}/block-info.tpl"));
        return true;
    }
    
    function create_admin_blocks(){
    global $load_tpl, $lang;
        $blocks = "";
        if(($handle = opendir("administrator/blocks/"))){
            while(false !== ($file = readdir($handle))){
                if(preg_match('/(.+?)\.php$/is', $file)){
                    ob_start();   
                    main::required("administrator/blocks/{$file}");
                    $content = ob_get_contents(); ob_end_clean();
                    $title = preg_replace('/(.+?)\.php$/is', '\\1', $file);
                    $blocks .= str_replace(array('$content', '$title', '$load_tpl'), array($content, (isset($lang[$title])?$lang[$title]:$title), $load_tpl), file_get_contents(TEMPLATE_PATH."admin/block.tpl"));
                }
            }
            closedir($handle);
        }
        return $blocks;
    }
    
    function tpl_creates(){
    global $template, $parametr_design, $main, $adminfile, $version_sys, $parametr_design, $revision;
        if(!is_ajax()) main::required("includes/nocache.php");
        else return false;
        main::add2script("\nKR_AJAX.this_module = '{$main->module}';\nvar ajaxload = false;\n", false);
        main::add2script("set_checked_callback('ajax_session_update', ".($main->config['interval_session_update']/1.25*1000).");", false);
        //Попытка авторизации администратора
        main::required("administrator/login.php");
        $ver_info_loading = "<img src=\\'".TEMPLATE_PATH."{$main->tpl}/images/arrow.gif\\' alt=\\'>\\' style=\\'margin-right: 4px;\\' />{$main->lang['loading']} | <b>{$main->lang['your_version']}: {$version_sys} r{$revision}</b>";
        $ver_info = "<a onclick=\"chk_ver('{$version_sys}', '{$ver_info_loading}'); return false;\" href='#' title='{$main->lang['version_check']}'><img src='".TEMPLATE_PATH."{$main->tpl}/images/arrow.gif' alt='>' style='margin-right: 4px;' />{$main->lang['version_check']}</a> | <b>{$main->lang['your_version']}: {$version_sys} r{$revision}</b>";
        if(isset($_SESSION['admin']) AND !defined("INSTALLCMS")){
            $mod = $this->admin_content();
            parent::tpl_creates();
            $menu = $this->admin_menu();
            $template->set_tpl(array(
                'ver_info'           => $ver_info,
                'blocks'             => $this->create_admin_blocks(),
                'main_menu'          => $menu[0],
                'sub_menu'           => $menu[1].$menu[2]."\n<script type='text/javascript' src='includes/javascript/admenu.js'></script>",
                'adminfile'          => $adminfile,
                'help'               => "<a href='http://www.kasseler-cms.net/' title='{$main->lang['help']}'><img src='".TEMPLATE_PATH."{$main->tpl}/images/menuhelp.gif' alt='+' style='margin-right: 4px;' />{$main->lang['help']}</a>",
                'block_modules'      => $this->modules_block(),
                'content'            => $mod,
            ));
            if(!is_ajax()) $template->set_tpl(array('block_info' => "<div id='blockinfo_cont'>".$this->block_info()."</div>"));
        } else parent::tpl_creates();
        return true;
    }
}

function get_function_checked(){
global $lang;
    return "<br /><div style='margin-right: 0px;' align='right'>
        <select id='op' name='op' class='chzn-search-hide' style='width: 208px;'>
            <option value='status'>{$lang['sataus_f']}</option>
            <option value='delete'>{$lang['delete_f']}</option>
        </select> 
        <input type='submit' value='{$lang['send']}' onclick='send_form('send_ajax_form', 'ajax_content');' /></div>";
}
/**
* Возвращает список категорий модуля для функции sort_as
* 
* @param string $module
*/
function filter_array($module=""){
 global $main;
 if ($module=="") $module=$main->module;
 $ra=array(array(0,""));
 $main->db->sql_query("select * from ".CAT." where module='{$module}' ORDER BY BINARY(UPPER(title))");
 while ($row=$main->db->sql_fetchrow()) $ra[]=array($row['cid'],$row['title']);
 return $ra;
}
/**
* Формирует диалог сортировки а админ-модулях
* 
* @param array $arr_sorted
* @param array $arr_filter
* @return string
*/
function sort_as($arr_sorted,$arr_filter=array()){
global $adminfile, $main;
    if(!isset($_GET['module'])) return "";
    $is_filter=count($arr_filter)!=0;
    $page = (isset($_GET['page'])) ? "&amp;page=".$_GET['page']."" : "";
    $do = (isset($_GET['do']) AND $_GET['do']!='search') ? "&amp;do=".$_GET['do']."" : "";
    if ($is_filter){
       $tmp= "<div style='float:left;margin-top: 2px;'>{$main->lang['filteras']}: <select id='filteras' class='chzn-search-hide' style='min-width:100px'>";
       for ($i=0; $i<count($arr_filter); $i++){
           $selected = (isset($_GET['filter']) AND $_GET['filter']==$arr_filter[$i][0]) ? " selected='selected'" : "";
           $tmp .= "<option value='".$arr_filter[$i][0]."'{$selected}>".$arr_filter[$i][1]."</option>";
       }
       $tmp .= "</select></div>";
    } else $tmp="";
    $tmp .= "{$main->lang['sortas']}: <select id='sortas' class='chzn-search-hide'>";
    for ($i=0; $i<count($arr_sorted); $i++){
        $selected = (isset($_GET['sort']) AND $_GET['sort']==$arr_sorted[$i][0]) ? " selected='selected'" : "";
        $tmp .= "<option value='".$arr_sorted[$i][0]."'{$selected}>".$arr_sorted[$i][1]."</option>";
    }
    $tmp .= "</select>";
    $sel2 = (isset($_GET['sorttype']) AND $_GET['sorttype']=="ASC") ? " selected='selected'" : "";
    $tmp .= "<select id='sortastype' class='chzn-search-hide' style='margin-left: 5px; margin-right:5px;'><option value='DESC'>{$main->lang['methoddown']}</option><option value='ASC'{$sel2}>{$main->lang['methodup']}</option></select><input type='submit' value='{$main->lang['ok']}' onclick=\"location.href='{$adminfile}?module=".$_GET['module']."{$do}{$page}&amp;sort='+document.getElementById('sortas').value+'&amp;sorttype='+document.getElementById('sortastype').value".($is_filter?"+'&amp;filter='+document.getElementById('filteras').value":"")."; return false;\" />";
return $tmp;
}

?>