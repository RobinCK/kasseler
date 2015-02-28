<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");
global $main, $tpl_create, $template;
main::add2link('includes/css/output_xhtml.css');

if(isset($_GET['do']) AND $_GET['do']=='show' AND isset($_GET['id'])){
    $where = ($main->rewrite_id) ? "static_id='{$_GET['id']}'" : "id='{$_GET['id']}'";
    $result = $main->db->sql_query("SELECT * FROM ".STATIC_PAGE." WHERE {$where}");
} elseif(!isset($_GET['do']) OR !isset($_GET['id'])) $result = $main->db->sql_query("SELECT * FROM ".STATIC_PAGE." ORDER BY id=RAND()");
if(isset($result)){
    if($main->db->sql_numrows($result)>0){
        $row = $main->db->sql_fetchrow($result);
        publisher($row['id'], array(
            'title'     => $row['title'],
            'content'   => $row['content']
        ));
        main::init_class('afields');
        $af=new afields($row['afields']);
        add_meta_value($row['title'],$af);
        if($row['template']!='index.tpl' AND file_exists(TEMPLATE_PATH."{$main->tpl}/{$row['template']}")) $template->get_tpl(TEMPLATE_PATH."{$main->tpl}/{$row['template']}");
    } else info($main->lang['noinfo']);
} else info($main->lang['noinfo']);
?>