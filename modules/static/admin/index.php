<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
$break_load=false;
if(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('add', 'add')
);

function admin_main_static(){
global $main, $adminfile;
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;    
    $result = $main->db->sql_query("SELECT * FROM ".STATIC_PAGE." ORDER BY id DESC LIMIT {$offset}, 30");    
    $rows = $main->db->sql_numrows($result);
    $tr = 'row1'; $i = 1;
    if($rows>0){
        echo "<table cellspacing='1' class='table' width='100%'>\n<tr><th width='15'>#</th><th>{$main->lang['title']}</th><th width='200'>{$main->lang['link']}</th><th width='70'>{$main->lang['functions']}</th></tr>\n";        
        while($row = $main->db->sql_fetchrow($result)){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$_GET['module']}&amp;do=edit&amp;id={$row['id']}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=delete&amp;id={$row['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}'><td align='center'><td><a href='".$main->url(array('module' => $main->module, 'do' => 'show', 'id' => case_id($row['static_id'], $row['id'])))."'>{$row['title']}</a></td><td>".in_text('lint'.$row['id'], 'input_text', $main->url(array('module' => $main->module, 'do' => 'show', 'id' => case_id($row['static_id'], $row['id']))),true)."</td><td align='center'>{$op}</td></tr>\n";
            $tr = ($tr=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table>";
        if ($rows==30 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".STATIC_PAGE." "));
            pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
        }
    } else info($main->lang['noinfo']);
}

function admin_add_static($msg=''){
global $main, $adminfile, $tpl_create,$config;
    main::init_class('afields');
    main::init_function(array('language'));
    main::add2script("includes/javascript/editors/tiny_mce/tiny_mce.js");
    main::add2script("includes/javascript/fn_tinymce.js");
    if(isset($_GET['id']) AND !isset($_POST['title'])){
        $result = $main->db->sql_query("SELECT * FROM ".STATIC_PAGE." WHERE id='{$_GET['id']}'");
        if($main->db->sql_numrows($result)>0){
            $info = $main->db->sql_fetchrow($result);
            $_POST = array(
                'title' => $info['title'], 
                'key_link' => $info['static_id'], 
                'editor' => $info['content'], 
                'template' => $info['template'], 
                'afields' => $info['afields'], 
            );
        }
        
    }
    if(!empty($msg)) warning($msg);
    $af=new afields(isset($_POST['afields'])?$_POST['afields']:"");
    $tplv=!defined("ADMIN_FILE")?TEMPLATE_PATH."{$main->tpl}/css/":TEMPLATE_PATH."{$config['template']}/css/";
    echo "<form id='autocomplete' method='post' action='{$adminfile}?module={$main->module}&amp;do=save".(isset($_GET['id'])?"&amp;id={$_GET['id']}":'')."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2")."</td></tr>\n".
    (defined("ADMIN_FILE")?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_description']}</td><td class='form_input '>".in_text("meta_desc", "input_text2", $af->val('meta_description'))."</td></tr>":"").
    (defined("ADMIN_FILE")?"<tr class='row_tr'><td class='form_text'>{$main->lang['meta_key']}</td><td class='form_input '>".in_text("meta_key", "input_text2", $af->val('meta_key'))."</td></tr>":"").
    "<tr class='row_tr'><td colspan='2'>
        <span class='form_text'>HTML:<span class='star'>*</span></span><br />
        ".in_area('editor', '', 10)."
        <script type='text/javascript'>
            //<![CDATA[
            var option={language : '".small_language()."',cssp:'{$tplv}',
            plugins : 'autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,visualblocks',
            theme_advanced_buttons1 : 'save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,forecolor,backcolor',
            theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,insertdate,inserttime,preview,|,code',
            theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
            theme_advanced_buttons4 : 'ks_cite,|,insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,visualblocks',
            content_css : '{$tplv}engine.css,{$tplv}blocks.css,{$tplv}base.css,{$tplv}ie.css,{$tplv}main.css,{$tplv}tools.css,includes/css/tiny_mce.css,includes/css/output_xhtml.css'
            }
            init_tiny_mce(option);
            //]]>
            </script>
    </td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['template']}:</td><td class='form_input'>".in_text("template", "input_text2", !isset($_POST['template'])?'index.tpl':'')."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table><input type='hidden' name='op' value='".($_GET['do']=='add'?'insert':'update')."' /></form>";
}

function admin_save_static(){
global $main;
    $msg = error_empty(array('title', 'editor'), array('title_err', 'text_err'));
    $static_id = (!isset($_POST['key_link']) OR empty($_POST['key_link'])) ? cyr2lat($_POST['title']) : $_POST['key_link'];
    if(empty($msg)){
        main::init_class('afields');
        $af=new afields("");
        if(isset($_GET['id'])) $af->load_from_db(STATIC_PAGE,"id={$_GET['id']}");
        $af->load_from_post(array("meta_desc"=>'meta_description',"meta_key"=>'meta_key'));
        if($_POST['op']=='insert') sql_insert(array(
                'static_id' => $static_id,
                'title' => $_POST['title'],
                'content' => $_POST['editor'],
                'template' => file_exists(TEMPLATE_PATH."{$main->tpl}/{$_POST['template']}")?$_POST['template']:'index.tpl',
                'afields' => $af->sql(),
            ), STATIC_PAGE);
        else sql_update(array(
                'static_id' => $static_id,
                'title' => $_POST['title'],
                'content' => $_POST['editor'],
                'template' => file_exists(TEMPLATE_PATH."{$main->config['template']}/{$_POST['template']}")?$_POST['template']:'index.tpl',
                'afields' => $af->sql(),
            ), STATIC_PAGE, "id={$_GET['id']}");
        redirect(MODULE);
    } else admin_add_static($msg);
}

function admin_delete_static(){
global $main;
    $main->db->sql_query("DELETE FROM ".STATIC_PAGE." WHERE id='{$_GET['id']}'");
    if(is_ajax()) admin_main_static(); else redirect(MODULE);
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case 'add': admin_add_static(); break;
        case 'save': admin_save_static(); break;
        case 'edit': admin_add_static(); break;
        case 'edit_save': admin_save_static(); break;
        case 'delete': admin_delete_static(); break;
        default: admin_main_static(); break;
    }
} elseif($break_load==false) admin_main_static();
?>
