<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('add', 'add_cat'),
    array('addcubcat', 'add_cubcat')
);

function main_categories(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sql = "SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level, c.title AS parent FROM ".CAT." AS t LEFT JOIN ".CAT." AS c ON(SUBSTR(t.tree,1,LENGTH(t.tree)-2)=c.tree) WHERE t.module<>'albom' ORDER BY t.tree LIMIT {$offset}, 30";
    $result = $main->db->sql_query($sql);
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        echo "<table cellspacing='1' class='table' width='100%'>
        <tr><th width='15'>#</th><th>{$main->lang['title']}</th><th width='80'>{$main->lang['module']}</th><th width='130'>{$main->lang['main_cat']}</th><th width='80'>{$main->lang['subcat']}</th><th width='60'>{$main->lang['level']}</th><th width='90'>{$main->lang['image']}</th><th width='60'>{$main->lang['functions']}</th></tr>";    
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        while(($rows = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$_GET['module']}&amp;do=edit&amp;id={$rows['cid']}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=delete&amp;id={$rows['tree']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$row}'><td class='col' align='center'>{$i}</td><td>{$rows['title']}</td><td align='center'>".(isset($main->lang[$rows['module']])?$main->lang[$rows['module']]:$rows['module'])."</td><td align='center'>".(!empty($rows['parent'])?$rows['parent']:"-")."</td><td align='center'>".((!empty($rows['parent']))?"<span style='color:red'>{$main->lang['yes2']}</span>":"<span style='color:green'>{$main->lang['no']}</span>")."</td><td align='center'>".(($rows['level']>0)?$rows['level']:"-")."</td><td align='center'>".((!empty($rows['image']) AND $rows['image']!='no.png') ? "<span style='color:green'>{$main->lang['yes']}</span>" : "<span style='color:red'>{$main->lang['no']}</span>")."</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
        echo "</table>";
        if ($rows_c==30 OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".CAT." where module<>'albom'"));
            //Если количество больше чем количество на страницу
            if($numrows>30){
                //Открываем стилевую таблицу
                open();
                //создаем страницы
                pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else info($main->lang['noinfo']);
}

function delete_cat(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('treedb');
    $treedb = new treedb(CAT);
    $treedb->delete($_GET['id']);
    if(is_ajax()) main_categories(); else redirect(MODULE);
}

function edit_categories($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);    
    $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT *, substr(tree,1,length(tree)-2) as parent FROM ".CAT." WHERE cid={$_GET['id']}"));
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".in_hide('this_tree', $result['tree']).in_hide('start_tree', mb_substr($result['tree'], 0, mb_strlen($result['tree'])-2)).
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $result['title'], false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", $result['cat_id'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:<span class='star'>*</span></td><td class='form_input'><select id='tree' name='tree' class='select2'><option value=''>{$main->lang['case_module']}</option></select></td></tr>\n".        
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 8, $result['description'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'>".in_sels('image', array('no.png'=>'no.png')+scan_dir("includes/images/cat/", '/(.+?)\.(gif|png|jpg|jpeg)$/i', true), 'select2 chzn-search-hide', $result['image'], " onchange=\"$$('preview_cat').src='includes/images/cat/'+this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['preview']}:</td><td class='form_input'><img id='preview_cat' src='includes/images/cat/{$result['image']}' alt='' /></td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";    
    echo "<script type='text/javascript'>addEvent(window, 'load', function(){select_add_options('tree', '{$adminfile}?module={$_GET['module']}&amp;do=load_cat', '{$result['module']}', '".mb_substr($result['tree'], 0, mb_strlen($result['tree'])-2)."')});</script>";
}

function save_edit_categories(){
global $main; 
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title'), array('cat_title_err'));
    if($_POST['tree']!=$_POST['start_tree']){
        if ($_POST['this_tree'] != mb_substr($_POST['tree'], 0, mb_strlen($_POST['this_tree']))) {
            main::init_class('treedb');
            $treedb = new treedb(CAT);
            $treedb->move($_POST['this_tree'], $_POST['tree']);
        } else $msg = $main->lang['novalidcatparametr'];
    }
    if(empty($msg)){       
        sql_update(array(
            'title'       => $_POST['title'],
            'cat_id'      => (!empty($_POST['key_link'])) ? $_POST['key_link'] : cyr2lat($_POST['title']),
            'description' => $_POST['description'],
            'image'       => $_POST['image']
        ), CAT, "cid='{$_GET['id']}'");
        redirect(MODULE);
    } else edit_categories($msg);
}

function add_categories($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", "")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['module']}:<span class='star'>*</span></td><td class='form_input'>".select_modules()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 8)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'>".in_sels('image', array('no.png'=>'no.png')+scan_dir("includes/images/cat/", '/(.+?)\.(gif|png|jpg|jpeg)$/i', true), 'select2 chzn-search-hide', "", " onchange=\"$$('preview_cat').src='includes/images/cat/'+this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['preview']}:</td><td class='form_input'><img id='preview_cat' src='includes/images/cat/no.png' alt='' /></td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_categories(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'module'), array('cat_title_err', 'module_err'));
    if(empty($msg)){
        main::init_class('treedb');
        $cat_id = (!empty($_POST['key_link'])) ? $_POST['key_link'] : cyr2lat($_POST['title']);
        $treedb = new treedb(CAT);
        $treedb->append('', "INSERT INTO ".CAT." (cat_id, title, module, description, image, tree) VALUES ('{$cat_id}', '{$_POST['title']}', '{$_POST['module']}', '{$_POST['description']}', '{$_POST['image']}', '{IDTREE}')");
        redirect(MODULE);
    } else add_categories($msg);
}

function addcubcat($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_addcubcat'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", "", false, " onkeyup=\"rewrite_key('key_link', this.value);\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:</td><td class='form_input'>".in_text("key_link", "input_text2", "")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['module']}:<span class='star'>*</span></td><td class='form_input'>".select_modules("", " onchange=\"select_add_options('tree', '{$adminfile}?module={$_GET['module']}&amp;do=load_cat', this.value)\"", array('' => $main->lang['case_module']))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['category']}:<span class='star'>*</span></td><td class='form_input'><select id='tree' name='tree' class='select2'><option value=''>{$main->lang['case_module']}</option></select></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['description2']}:</td><td class='form_input'>".in_area("description", "textarea", 8)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['image']}:</td><td class='form_input'>".in_sels('image', array('no.png'=>'no.png')+scan_dir("includes/images/cat/", '/(.+?)\.(gif|png|jpg|jpeg)$/i', true), 'select2 chzn-search-hide', "", " onchange=\"$$('preview_cat').src='includes/images/cat/'+this.value\"")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['preview']}:</td><td class='form_input'><img id='preview_cat' src='includes/images/cat/no.png' alt='' /></td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_addcubcat(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('title', 'module', 'tree'), array('cat_title_err', 'module_err', 'cat_case_error'));
    if(empty($msg)){
        main::init_class('treedb');
        $cat_id = (!empty($_POST['key_link'])) ? $_POST['key_link'] : cyr2lat($_POST['title']);
        $treedb = new treedb(CAT);
        $treedb->append($_POST['tree'], "INSERT INTO ".CAT." (cat_id, title, module, description, image, tree) VALUES ('{$cat_id}', '{$_POST['title']}', '{$_POST['module']}', '{$_POST['description']}', '{$_POST['image']}', '{IDTREE}')");
        redirect(MODULE);
    } else addcubcat($msg);
}

function load_cat(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('pre_html');
    
    $result = $main->db->sql_query("SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level FROM ".CAT." AS t WHERE module='{$_POST['value']}' ORDER BY t.tree");
    if($main->db->sql_numrows($result)>0){
        echo "sels[sels.length] = ['', '{$main->lang['case_cat']}']; ";
        while(($row = $main->db->sql_fetchrow($result))){
            echo "sels[sels.length] = ['{$row['tree']}', '".pre_html($row['level']-1).$row['title']."']; ";
        }
    }
}
function switch_admin_categories(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "load_cat": load_cat(); break;
         case "addcubcat": addcubcat(); break;
         case "save_addcubcat": save_addcubcat(); break;
         case "add": add_categories(); break;
         case "save": save_categories(); break;
         case "edit": edit_categories(); break;
         case "save_edit": save_edit_categories(); break;
         case "delete": delete_cat(); break;
         default: main_categories(); break;
      }
   } elseif($break_load==false) main_categories();
}
switch_admin_categories();
?>