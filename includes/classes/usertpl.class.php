<?php
/**
* Файл функций Attach
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/usertpl.php
* @version 2.0
*/
if (!defined('KASSELERCMS')) die('Access is limited');


function load_module(){
global $modules, $module_name, $lang, $main;
    if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
    if(is_ajax() AND isset($_POST['load_module'])){
        if(isset($modules[$_POST['module']]) AND $modules[$_POST['module']]['blocks']!=$modules[$main->module]['blocks']){
            echo "window.location.href = '".get_env('REQUEST_URI')."';";
            kr_exit();
        }
    }
    if(!isset($modules[$module_name]) AND !is_home() AND !is_ajax()){
        //redirect("index.php"); //Убрать комментарий в том случаи если нужно все http ошибки переадресовывать на главную.
        kr_http_ereor_logs("404");

        //unset($_GET); $_GET = array();
        //$_GET['module'] = $module_name = "news";
        //return include_module($module_name);
    } elseif(is_support()) return include_module($module_name);
    else {
        if(isset($modules[$module_name]) AND $modules[$module_name]['active']!="0"){ 
            switch($modules[$module_name]['view']){
                case '1': return include_module($module_name);  break;
                case '2': if(is_guest()) return include_module($module_name); else return warning($lang['only_guest_module'], true); break;
                case '3': if(is_user()) return include_module($module_name); else return warning($lang['only_users_module'], true); break;
                case '4': if(is_support()) return include_module($module_name); else return warning($lang['only_admin_module'], true); break;                    
                default: return warning($lang['no_view_module'], true); break;
            }
        } elseif(isset($modules[$module_name])) return warning($lang['module_off'], true);
        else kr_http_ereor_logs("404");
    }
    return "";
}

/**
    * Функция загрузки модуля
    * 
    * @param string $module_name
    * @return string
    */
    function include_module($module_name){
    global $parametr_design, $main, $modules, $module_title;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $contents = ""; $_module = $main->module;
        $_arr_home_modules = is_home() ? explode(',', $main->config['default_module']) : array($main->module);
        if(count($_arr_home_modules)>1){
            $_arr = array();
            foreach($modules as $_k => $_c) if(in_array($_k, $_arr_home_modules) AND $_c['active']==1) $_arr[] = $_k;
            $_arr_home_modules = $_arr;
        }
        foreach($_arr_home_modules as $m){
            $main->module = $m;
            $module_title = (isset($modules[$main->module])) ? $modules[$main->module]['title'] : $main->module;
            if(file_exists("modules/{$m}/index.php")){
                if(isset($modules[$m]['groups']) AND check_user_group($modules[$m]['groups'])){
                    ob_start();
                    main::required("modules/{$m}/index.php");
                    $contents .= ob_get_contents(); 
                    if(ob_get_length()>0) ob_end_clean();
                } else $contents .= warning($main->lang['no_view_module'], true);
            } else $contents .= warning($main->lang['error_load_module'], true);
        }
        $main->module = $_module;
        return $contents;
    }

class user_tpl extends tpl_create{
        
    function create_home_message(){
    global $main, $template, $messages;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $mess = "";
        if(count($messages)>0 AND is_home() AND file_exists(TEMPLATE_PATH."{$main->tpl}/message.tpl")){
            foreach($messages as $m){
                if(check_user_group($m['groups']) AND $m['status']==1){
                    $tpl = str_replace('.tpl', '', ((isset($m['tpl']) AND $m['tpl']!='message.tpl' AND $m['tpl']!='' AND file_exists(TEMPLATE_PATH."{$main->tpl}/{$m['tpl']}")) ? $m['tpl'] : 'message.tpl'));
                    $template->get_tpl($tpl, $tpl);    
                    $template->set_tpl(array(
                        'title'            => $m['title'],
                        'content'          => parse_bb($m['content']),
                    ), $tpl);
                    $mess .= $template->tpl_create(true, $tpl);
                }
            }
        }
        return "<div id='home_message'>{$mess}</div>";
    }

    function create_blocks($position = array(), $return = false){
    global $block_config, $module_name, $modules, $lang, $template, $language, $main;
        $blocksmas = array('l' => '', 'r' => '', 'c' => '', 'd' => '', 'b' => '', 'f' => '');
        main::init_function('module_access');
        foreach ($block_config as $conf){
            $module = !is_home() ? $module_name : "home"; $continue = false; $block_module = 0;
            if(isset($modules[$module_name])) $block_module = $modules[$module_name]['blocks'];
            if(!empty($position) AND !in_array($conf['position'], $position)) $continue = true;
            if($continue==false){
                if((!empty($conf['language']) AND $conf['language']!=$language) OR $conf['active']==0) $continue = true;
                if($block_module=="3" AND ($conf['position']=='l' OR $conf['position']=='r')) $continue = true;
                elseif($block_module=="1" AND $conf['position']=='r') $continue = true;
                elseif($block_module=="2" AND $conf['position']=='l') $continue = true;
                if(!in_array($module, explode(',', $conf['modules'])) AND !empty($conf['modules'])) $continue = true;
                if(!check_user_group(implode(",",block_encode_prev_acc($conf['view'])))) $continue = true;
            }
            if($continue==true) {$blocksmas[$conf['position']] .= "<div id='blockid_{$conf['id']}'></div>\n"; continue;}
            if(empty($conf['content']) OR file_exists("blocks/{$conf['blockfile']}")){
                if(!empty($conf['blockfile']) AND file_exists("blocks/{$conf['blockfile']}")){
                    ob_start();
                    require("blocks/{$conf['blockfile']}");
                    $content = ob_get_contents(); ob_end_clean();
                }
                $content = isset($content) ? $content.$conf['content'] : $conf['content'];
                if(empty($content)) $content="<div id='blockid_{$conf['id']}'><center>{$lang['blocknocontent']}</center></div>";
            } else $content = "<div id='blockid_{$conf['id']}'><center>{$lang['blockproblem']}</center></div>";
            $blocksmas[$conf['position']] .= $this->tpl_blocks($conf['id'], $conf['title'], $content, $conf['position'], $conf['blockfile'], isset($conf['blocktpl'])?$conf['blocktpl']:"");
            $content = "";
        } 
        if($return==false){
            $l = trim(strip_tags($blocksmas['l'])); $r = trim(strip_tags($blocksmas['r']));
            if(!empty($l)){
                $template->template['index'] = preg_replace(array('/<\!--BEGIN\sblock-->(.+?)<\!--END\sblock-->/si', '/<\!--BEGIN\sleftblock-->(.+?)<\!--END\sleftblock-->/si'), array('', '\\1'), $template->template['index']);            
                $template->template['index'] = preg_replace("#<td id='leftcol' valign='top' style='display: none;'>#si", "<td id='leftcol' valign='top'>", $template->template['index']);
                $template->set_tpl(array('block_left' => $blocksmas['l']));
            } else $template->template['index'] = preg_replace('/<\!--BEGIN\sleftblock-->(.+?)<\!--END\sleftblock-->/si', '', $template->template['index']);
            if(!empty($r)){
                $template->template['index'] = preg_replace(array('/<\!--BEGIN\sblock-->(.+?)<\!--END\sblock-->/si', '/<\!--BEGIN\srightblock-->(.+?)<\!--END\srightblock-->/si'), array('', '\\1'), $template->template['index']);
                $template->template['index'] = preg_replace("#<td id='rightcol' valign='top' style='display: none;'>#si", "<td id='rightcol' valign='top'>", $template->template['index']);
                $template->set_tpl(array('block_right' => $blocksmas['r']));
            } else $template->template['index'] = preg_replace('/<\!--BEGIN\srightblock-->(.+?)<\!--END\srightblock-->/si', '', $template->template['index']);
            $template->set_tpl(array(
                'block_center'      => $blocksmas['c'],
                'block_down'        => $blocksmas['d'],
                'topbaner'          => !empty($blocksmas['b']) ? $blocksmas['b'] : "&nbsp;",
                'footbaner'         => !empty($blocksmas['f']) ? $blocksmas['f'] : "&nbsp;",
            ));
        } else return $blocksmas;
    }

    function tpl_blocks($id, $title, $content, $position, $blockfile, $blocktpl){
    global $module_name, $load_tpl, $template;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $btpls = array('l' => 'block-left', 'r' => 'block-right', 'c' => 'block-center', 'd' => 'block-down', 'b' => 'block-head', 'f' => 'block-footer');
        $block_tpl = isset($btpls[$position]) ? $btpls[$position] : 'block-fly';
        $blockfile = str_replace(".php", "", $blockfile);
        $blocktpl = str_replace(".tpl", "", $blocktpl);
        $block_tpl = (!empty($blocktpl)&&file_exists(TEMPLATE_PATH."{$load_tpl}/{$blocktpl}.tpl"))?$blocktpl:((file_exists(TEMPLATE_PATH."{$load_tpl}/{$blockfile}.tpl")) ? $blockfile : $block_tpl);
        $template->get_tpl($block_tpl, $block_tpl);    
        $template->set_tpl(array(
            'title'            => $title,
            'content'          => stripslashes($content),
        ), $block_tpl);
        return "<div id='blockid_{$id}'>".$template->tpl_create(true, $block_tpl)."</div>\n";
    }
    
    function generate_info_insert($generate, $time_sql_query, $sql_querys, $size_page, $gz_size_page){
    global $lang, $config, $generate_template;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $lang['generate'] = str_replace("{time}", $generate, $lang['generate']);
        $lang['time_sql_query'] = str_replace("{time}", $time_sql_query, $lang['time_sql_query']);
        $lang['sql_querys'] = str_replace("{query}", $sql_querys, $lang['sql_querys']);
        $lang['gz_level'] = str_replace("{encoding}", $config['gzlevel'], $lang['gz_level']);
        $lang['generate_template'] = str_replace("{time}", $generate_template, $lang['generate_template']);
        $info = "\n<!--{$lang['generate']}-->\n".
        "<!--{$lang['generate_template']}-->\n".
        "<!--{$lang['time_sql_query']}-->\n".
        "<!--{$lang['sql_querys']}-->\n";
        if($config['gz']==ENABLED){
            $lang['page_size'] = str_replace("{size_page}", $size_page, $lang['page_size']);
            $lang['page_size'] = str_replace("{gz_size_page}", $gz_size_page, $lang['page_size']);
            $info .= "<!--".$lang['gz_level']."-->\n".
            "<!--".$lang['page_size']."-->\n";
        }
        return $info;
    }
    
    function rss_link(){
    global $rss, $main;
        foreach($rss as $key=>$value){
            $conf = explode("|", $value);
            if($conf[1]==ENABLED) $this->rss .= "<link rel='alternate' type='application/rss+xml' href='".$main->url(array('module' => $key, 'do' => 'rss'))."' title='{$conf[0]}' />\n";
        }
    }
    
    function tpl_creates(){
    global $template, $load_tpl, $lang, $parametr_design, $config, $main, $modules, $patterns, $bread_crumb_array;;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(!is_ajax()){
            if($config['ajaxload']==ENABLED ) {
                main::add2script("\nKR_AJAX.this_module = '".(is_home()?'home':$main->module)."';\nvar ajaxload = true;\nvar classes = Array(".array_create($config['classes_links']).");\n", false);
                main::add2script('includes/javascript/loadmodule.js');
            } else {
               main::add2script("\nvar ajaxload = false;\n", false);
               main::add2script("set_checked_callback('ajax_session_update', ".($main->config['interval_session_update']/1.25*1000).");", false);
            }
        }
        $footer = ""; 
        if(!is_ajax()) $this->rss_link();
        $parametr_design = true;
        //Подключение модуля
        $mod = load_module();
        $tit = $this->title_insert();
        if(!$parametr_design OR !$this->design) page($mod, $tit);
        if(is_ajax()) {            
            die("<title>{$tit}</title>".$this->javascript_insert().$this->link_insert().$mod);            
        }
        $this->create_blocks();
        $bread_crumbs=bcrumb::bread_crumb($bread_crumb_array);
        parent::tpl_creates();
        ob_start();
        $footer = ob_get_contents(); ob_end_clean();
        //Подключение дополнительных пользовательских функций
        $template->set_tpl(array(
            'modules'      => "\n<!--BEGIN content-->\n<div id='ajax_content'>{$mod}</div>\n<!--END content-->\n",
            'footer'       => $footer,
            'user_name'    => $main->user['user_name'],
            'message'      => $this->create_home_message(),
            'bread_crumbs' => $bread_crumbs
        ));
    }
}


//Функция определения загружаемого стиля
function get_style(){
global $module_name, $load_tpl;
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists(TEMPLATE_PATH."{$load_tpl}/style-{$module_name}.css")) return TEMPLATE_PATH."{$load_tpl}/style_{$module_name}.css";
    elseif(file_exists(TEMPLATE_PATH."{$load_tpl}/style-{$module_name}-{$_GET['id']}.tpl") AND !isset($_GET['module'])) return TEMPLATE_PATH."{$load_tpl}/style-{$module_name}-{$_GET['id']}.tpl";
    elseif(file_exists(TEMPLATE_PATH."{$load_tpl}/style-home.tpl") AND !isset($_GET['module'])) return TEMPLATE_PATH."{$load_tpl}/style-home.tpl";
    else return TEMPLATE_PATH."{$load_tpl}/style.css";
}

function show_category($col=2){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_GET['do']) AND $_GET['do']=='category') $parent = (!$main->rewrite_id) ? "c.cid='{$_GET['id']}'" : "c.cat_id='{$_GET['id']}'";
    else $parent = "c.cid IS NULL";
    $result = $main->db->sql_query("SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level, c.cid AS parent_id, c.cat_id AS parent_cat FROM ".CAT." AS t LEFT JOIN ".CAT." AS c ON(SUBSTR(t.tree,1,LENGTH(t.tree)-2)=c.tree) WHERE t.module='{$main->module}' AND {$parent} ORDER BY t.title");
    if($main->db->sql_numrows($result)>0){
        $array_cat = array(); $i=0;   
        while(($row = $main->db->sql_fetchrow($result))) $array_cat[] = array('cid' => $row['cid'], 'cat_id' => $row['cat_id'], 'title'  => $row['title'], 'image'  => $row['image'], 'description' => $row['description']);        
        open();
        echo "<table class='catlist'>";
        while($i<count($array_cat)){
            echo "<tr><td>";
            for($y=1;$y<=$col;$y++){
                if(!isset($array_cat[$i+$y-1])) continue;
                echo "<div style='width: ".round(100/$col,2)."%;'><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($array_cat[$i+$y-1]['cat_id'], $array_cat[$i+$y-1]['cid'])))."' title='{$array_cat[$i+$y-1]['title']}'><span>".(($array_cat[$i+$y-1]['image']!='no.png') ? "<img src='includes/images/cat/{$array_cat[$i+$y-1]['image']}' alt='{$array_cat[$i+$y-1]['title']}' />" : "")."<b>{$array_cat[$i+$y-1]['title']}</b><br />".cut_char($array_cat[$i+$y-1]['description'])."</span></a></div>";
            }
            echo "</td></tr>";
            $i+=$col;
        }
        echo "</table>";
        close();
    }
}

function publisher($id, $vars, $return=false){
global $module_name, $template;
    if(hook_check(__FUNCTION__)) return hook();
    $template->get_tpl('publisher', 'publisher');
    preg_match_all('/(?i)\$pub\[([a-z_\-]+)\]/m', $template->template['publisher'], $regs, PREG_PATTERN_ORDER);
    for ($i = 0; $i < count($regs[0]); $i++) {if(!isset($vars[$regs[1][$i]])) $vars[$regs[1][$i]] ='';}
    $template->set_tpl($vars, 'publisher', array('start' => '$pub[', 'end' => ']'));
    $template->template['publisher'] = "<div id='{$module_name}_{$id}'>{$template->template['publisher']}</div>";
    if($return) return $template->tpl_create(true, 'publisher');
    else echo $template->tpl_create(true, 'publisher');
    return "";
}

function publisher_more($id, $vars, $return=false){
global $template, $module_name;
    if(hook_check(__FUNCTION__)) return hook();
    $template->get_tpl('more', 'more');
    preg_match_all('/(?i)\$pub\[([a-z_\-]+)\]/m', $template->template['more'], $regs, PREG_PATTERN_ORDER);
    for ($i = 0; $i < count($regs[0]); $i++) {if(!isset($vars[$regs[1][$i]])) $vars[$regs[1][$i]] ='';}
    $template->set_tpl($vars, 'more', array('start' => '$pub[', 'end' => ']'));
    if($return) return $template->tpl_create(true, 'more');
    else echo $template->tpl_create(true, 'more');
}

function publisher_print($id, $vars){
global $description, $keywords, $tpl_create, $main, $bread_crumb_array, $template;
    if(hook_check(__FUNCTION__)) return hook();
    if (file_exists(TEMPLATE_PATH."{$main->tpl}/print-{$main->module}-{$id}.tpl")) $file = TEMPLATE_PATH."{$main->tpl}/print-{$main->module}-{$id}.tpl";
    elseif (file_exists(TEMPLATE_PATH."{$main->tpl}/print-{$main->module}.tpl")) $file = TEMPLATE_PATH."{$main->tpl}/print-{$main->module}.tpl";
    else $file = TEMPLATE_PATH."{$main->tpl}/print.tpl";
    $meta = "<meta http-equiv='content-type' content='text/html; charset={$main->config['charset']}' />\n".
            "<meta name='author' content='{$main->config['home_title']}' />\n".
            "<meta name='copyright' content='Copyright (c) Kasseler CMS {$main->config['cms_version']}' />\n".
            "<meta name='resource-type' content='document' />\n".
            "<meta name='document-state' content='dynamic' />\n".
            "<meta name='distribution' content='global' />\n".
            "<meta name='robots' content='index, follow' />\n".
            "<meta name='revisit-after' content='1 days' />\n".
            "<meta name='rating' content='general' />\n".
            "<meta name='generator' content='Kasseler CMS {$main->config['cms_version']}' />\n".
            "<meta name='description' content='{$description}{$main->config['description']}' />\n".
            "<meta name='keywords' content='{$keywords}{$main->config['keywords']}' />\n".
            "<meta name='Cache-Control' content='no-cache' />\n".
            "<meta http-equiv='Expires' content='0' />\n".
            "<base href='http://".get_host_name()."/' />";
    $vars = array_merge($vars, array(
        'meta'       => $meta,
        'style'      => $tpl_create->link_insert(),
        'sitename'   => "{$main->lang['site']}: <a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['home_title']}</a>",
        'pub_author' => "{$main->lang['author']}: {$vars['author']}",
        'url'        => "{$main->lang['url']}: <a href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $_GET['id']))."'>".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $_GET['id']))."</a>",
        'logo'       => "<img src='{$main->config['sitelogo']}' alt='{$main->config['home_title']}' title='{$main->config['home_title']}' />",
        'page_title' => $tpl_create->title_insert(),
        'bread_crumbs'=> bcrumb::bread_crumb($bread_crumb_array)
    ));
    $template->template['print'] = file_get_contents($file);
    preg_match_all('/(?i)\$print\[([a-z_\-]+)\]/m', $template->template['print'], $regs, PREG_PATTERN_ORDER);
    for ($i = 0; $i < count($regs[0]); $i++) {if(!isset($vars[$regs[1][$i]])) $vars[$regs[1][$i]] ='';}
    $template->set_tpl($vars, 'print', array('start' => '$print[', 'end' => ']'));
    $content = $template->tpl_create(true, 'print');
    //$content = preg_replace('#\$print\[([a-z_-]+)\]#ise', "\$vars['\\1']", file_get_contents($file));
    echo $content;
    kr_exit();
}
function create_link($id, $links, $op){
global $title;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($links[$id])){
        $add_param=sizeof($links[$id])==4?$links[$id][3]:"";
        if(isset($_GET[$op]) AND $_GET[$op]==$links[$id][2]){
            $title = $links[$id][1];
            return "<a class='module_navi_sel' href='{$links[$id][0]}' title='{$links[$id][1]}' {$add_param}><b>{$links[$id][1]}</b></a>";
        } else return "<a class='module_navi' href='{$links[$id][0]}' title='{$links[$id][1]}' {$add_param}><b>{$links[$id][1]}</b></a>";
    } else return "";
}

function navi($links, $search=true, $link=true, $user_title='', $op='do'){
global $main, $title;
    if(hook_check(__FUNCTION__)) return hook();
    if (isset($_GET['id']) AND file_exists(TEMPLATE_PATH."{$main->tpl}/navi-{$main->module}-{$_GET['id']}.tpl")) $file = TEMPLATE_PATH."{$main->tpl}/navi-{$main->module}-{$_GET['id']}.tpl";
    elseif (file_exists(TEMPLATE_PATH."{$main->tpl}/navi-{$main->module}.tpl")) $file = TEMPLATE_PATH."{$main->tpl}/navi-{$main->module}.tpl";
    else $file = TEMPLATE_PATH."{$main->tpl}/navi.tpl";
    $content = file_get_contents($file);
    if(!$search) $content = preg_replace('/<\!--BEGIN\ssearch-->(.+?)<\!--END\ssearch-->/si', '', $content);
    if(!$link) $content = preg_replace('/<\!--BEGIN\slinks-->(.+?)<\!--END\slinks-->/si', '', $content);
    $content = preg_replace_callback('#\$link\[([0-9]+)\]#is', function($matches) use ($links, $op) { return create_link($matches[1], $links, $op); } , $content);
    if(!empty($user_title)) add_meta_value($user_title);
    if(!empty($title) AND $title!="&nbsp;") add_meta_value($title);
    $user_title = (!empty($user_title)) ? $user_title : $main->title;
    $title = (empty($title)) ? "&nbsp;" : $title;
    $nav = array('module' => $user_title, 'title' => $title, 'search' => searche_module());
    $content = preg_replace_callback('#\$nav\[([a-z_-]+)\]#is', function($matches) use ($nav) { return $nav[$matches[1]]; } , $content);
    return $content;
}

function searche_module(){
global $main;
    return "<form class='search_module_form' action='".$main->url(array('module' => 'search', 'do' => 'result', 'module' => $main->module))."' method='get'>".
    "<input class='module_search' type='text' value='' /> ".button_search_module().
    "</form>";
}

function show_comment($id, $vars, $cid=0, $class="", $return=false){
global $load_tpl, $module_name, $comment_file, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($comment_file)){
        if (file_exists(TEMPLATE_PATH."{$load_tpl}/comment-{$module_name}-{$id}.tpl")) $file = TEMPLATE_PATH."{$load_tpl}/comment-{$module_name}-{$id}.tpl";
        elseif (file_exists(TEMPLATE_PATH."{$load_tpl}/comment-{$module_name}.tpl")) $file = TEMPLATE_PATH."{$load_tpl}/comment-{$module_name}.tpl";
        else $file = TEMPLATE_PATH."{$load_tpl}/comment.tpl";
        $comment_file = $file;
    } else $file = $comment_file;

    $content = "<div id='comment_{$cid}' class='{$class}'>".preg_replace_callback(
        '#\$pub\[([a-z_-]+)\]#is',
        function ($matches) use ($vars) {
            return $vars[$matches[1]];
        },
        str_replace('$load_tpl', $load_tpl, file_get_contents($file))
    )."</div>";

    $content = preg_replace_callback(
        '#\$lang\[([a-z_-]+)\]#is',
        function ($matches) use (&$main) {
            return array_value_set($matches[1], $main->lang);
        },
        $content
    );

    if($return) return $content;
    else echo $content;
    return "";
}

function list_liter(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    open();
    echo "<div class='basepub'><div class='basenumbers'><div class='binner'><div class='listlit'><table><tr><td>";
    foreach(range('0', '9') as $letter) echo "<a class='list_link' href='".$main->url(array('module' => $main->module, 'do' => 'list', 'page' => 1, 'id' => $letter))."'><b>{$letter}</b></a>";
    echo "</td></tr></table><table><tr><td>";
    foreach(range('A', 'Z') as $letter) echo "<a class='list_link' href='".$main->url(array('module' => $main->module, 'do' => 'list', 'page' => 1, 'id' => $letter))."'><b>{$letter}</b></a>";
    echo "</td></tr></table><table><tr><td>";    
    foreach(array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я') as $letter) echo "<a class='list_link' href='".$main->url(array('module' => $main->module, 'do' => 'list', 'page' => 1, 'id' => kr_encodeurl($letter)))."'><b>{$letter}</b></a>";
    echo "</td></tr></table></div></div></div></div>";
    close();    
}
 
?>
