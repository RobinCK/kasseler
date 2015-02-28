<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main;
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$navi = navi($links);
function main_faq(){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_home()) echo $navi;
    if(!isset($_GET['do'])) {
        $result = $main->db->sql_query("SELECT * FROM ".CAT." WHERE module='{$main->module}'");
        //Проверяем наличие категорий для модуля
        $check_module = ($main->db->sql_numrows($result)>0)?true:false;
    } else $check_module = false;
    if($check_module==true AND !isset($_GET['do'])){
        //Открываем стилевую таблицу
        open();
        //Выводим список категорий
        while(($row = $main->db->sql_fetchrow($result))){
            echo "<div class='faq_div'><img src='includes/images/16x16/help.png' align='left' alt='' /><a href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($row['cat_id'], $row['cid'])))."' title='{$row['title']}'>{$row['title']}</a></div>";
        }
        //Открываем стилевую таблицу
        close();
    } else {
        if (isset($_GET['id'])) {
            $main->db->sql_query("select title from ".CAT." where module='{$main->module}' AND ".($main->rewrite_id?"cat_id='{$_GET['id']}'":"cid='".intval($_GET['id'])."'"));
            list($title)=$main->db->sql_fetchrow();
            bcrumb::add($title);
        }
        $where = "";
        if(isset($_GET['do']) AND $_GET['do']=='category'){
            $where = $main->rewrite_id ?sql_check_chpu_categorys("kf"):" AND cid LIKE BINARY('%,".$_GET['id'].",%')";
        }        
        $result = $main->db->sql_query("SELECT kf.* FROM ".FAQ." kf WHERE status='1' AND (language='{$main->language}' OR language=''){$where}");
        if($main->db->sql_numrows($result)>0){
            $_list_question = $content = "";
            $i = 1;
            while(($row = $main->db->sql_fetchrow($result))){
                $_list_question .= "<div class='faq_div2'><img src='includes/images/16x16/help.png' align='left' alt='' /><a href='".($main->url(array('module' => $main->module, 'do' => 'category', 'id' => $_GET['id'])))."#answer_{$i}'>{$row['question']}</a></div>";
                $content .= "<div class='faq_div2'><a href='#' name='answer_{$i}'>&nbsp;</a><h2><img src='includes/images/16x16/info.png' align='left' alt='' />{$row['question']}</h2></div><div class='faq_answer'>".parse_bb($row['answer'])."</div>";
                $i++;
            }
            open();
            echo $_list_question;
            close();
            open();
            echo $content;
            close();
        } else info($main->lang['noinfo']);
    }
    
}

function upload_attach_faq(){
global $faq;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    $faq['attaching'] = ENABLED;
    upload_attach($faq);
}
function switch_module_faq(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "category": main_faq(); break;
         case "upload": upload_attach_faq(); break;
         default: main_faq(); break;
      }
   } else main_faq();
}
switch_module_faq();
?>
