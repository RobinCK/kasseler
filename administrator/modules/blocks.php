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
main::init_function('module_access');
$navi = array(
    array('', 'home'),
    array('add', 'add_block'),
    array("save_config", "save"),
    array("back_config", "cancel")
);

function main_blocks(){
   global $main, $adminfile;
   if(hook_check(__FUNCTION__)) return hook();
   $result = $main->db->sql_query("SELECT * FROM ".GROUPS." ORDER BY id");
   $group = array(0 => array('text'=>$main->lang['alluser'],'color'=>'green'));
   while(($row = $main->db->sql_fetchrow($result))) $group[$row['id']] = array('text'=>$row['title'], 'color'=>"#".$row['color']);
   echo "<form id='send_ajax_form' action='{$adminfile}?module={$_GET['module']}&amp;do=change_op' method='post'><table cellspacing='1' class='table' width='100%'>".
   "<tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['title']}</th><th colspan='2'>{$main->lang['weight']}</th><th width='130'>{$main->lang['position']}</th><th width='50'>{$main->lang['status']}</th><th width='135'>{$main->lang['who_views']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
   $ret = blocks_create('b', "row1", 1, $group);
   $ret = blocks_create('c', $ret[0], $ret[1], $group);
   $ret = blocks_create('l', $ret[0], $ret[1], $group);
   $ret = blocks_create('r', $ret[0], $ret[1], $group);
   $ret = blocks_create('d', $ret[0], $ret[1], $group);
   $ret = blocks_create('f', $ret[0], $ret[1], $group);
   echo "</table>".get_function_checked()."</form>
   <script type='text/javascript'>
   <!--
   var timeout = setTimeout(function(){ var chekeds = document.getElementsByTagName('input'); for(i=0; i<chekeds.length; i++) if(chekeds[i].type=='checkbox' && chekeds[i].id!='checkbox_sel') chekeds[i].name = 'sels[]';}, 1000);
   // -->
   </script>";
}

function blocks_create($pos, $row, $i, $group){
    global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $ps = get_block_position($pos);
    $result = $main->db->sql_query("SELECT id, title, position, weight, active, view FROM ".BLOCKS." WHERE position='{$pos}' ORDER BY weight");
    if($main->db->sql_numrows($result)>0){
        list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(weight) FROM ".BLOCKS." WHERE position='{$pos}'"));
        while(list($id, $title, $position, $weight, $active, $view) = $main->db->sql_fetchrow($result)){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$_GET['module']}&amp;do=edit&amp;id={$id}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=delete&amp;pos={$position}&amp;id={$id}", 'ajax_content')."</td></tr></table>";
            $up_down = ($count>1) ? up_down_analizy($weight, $count, $id, 'ajax_content') : "";
            $grv = block_encode_prev_acc($view);
            if(count($grv)==1) $view_caption = "<span style='color: {$group[$grv[0]]['color']};'>{$group[$grv[0]]['text']}</span>";
            else {
               $titlev="";
               foreach ($grv as $key => $value) {$titlev.=",".$group[$value]['text'];}
               $titlev = substr($titlev,1);
               $view_caption = "<span style='color: green;' title='{$titlev}'> - </span>";
            }
            echo "<tr class='{$row}".(($active==0)?"_warn":"")."'><td align='center' width='15'>".in_chck("sels{$id}", "", "", "value='{$id}'")."</td><td align='center' class='col'>{$i}</td><td id='tit_{$id}' class='pointer' ondblclick=\"edit_value(this, '{$adminfile}?module={$_GET['module']}&amp;do=update&amp;type=title', '{$id}');\">{$title}</td><td width='10' align='center' class='col'>{$weight}</td><td align='center' width='60'>{$up_down}</td><td align='center' class='col' id='pos_{$id}' style='cursor: pointer;' ondblclick=\"load_case('{$adminfile}?module={$_GET['module']}&amp;do=load_case&amp;type=pos', 'pos_{$id}', '{$position}', '{$adminfile}?module={$_GET['module']}&amp;do=update&amp;type=pos', 'ajax_content')\">{$ps}</td><td align='center' id='onoff_{$id}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$_GET['module']}&amp;do=on_off&amp;id={$id}', 'onoff_{$id}')\">".($active==1 ? $main->lang['on'] : $main->lang['off'])."</td><td align='center' class='col' id='view_{$id}'>{$view_caption}</td><td align='center'>{$op}</td></tr>\n";
            $row = ($row=="row1") ? "row2" : "row1";
            $i++;
        }
    }
    return array($row, $i);
}

function delete_blcok(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".BLOCKS." WHERE id='{$_GET['id']}'");
    $result = $main->db->sql_query("SELECT id FROM ".BLOCKS." WHERE position='{$_GET['pos']}' ORDER BY weight");
    $i = 1;
    while(list($id) = $main->db->sql_fetchrow($result)) {
        $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$i}' WHERE id='{$id}'");
        $i++;
    }
    if(is_ajax()) main_blocks(); else redirect(MODULE);
}

function on_off_blcoks(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT active FROM ".BLOCKS." WHERE id='{$_GET['id']}'"));
    if($active==1){
        $main->db->sql_query("UPDATE ".BLOCKS." SET active='0' WHERE id='{$_GET['id']}'");
        echo $main->lang['off'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $main->db->sql_query("UPDATE ".BLOCKS." SET active='1' WHERE id='{$_GET['id']}'");
        echo $main->lang['on'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
}

function move_blcok(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=="up") $next = $_GET['pos']-1; else $next = $_GET['pos']+1;
    list($position) = $main->db->sql_fetchrow($main->db->sql_query("SELECT position FROM ".BLOCKS." WHERE id='{$_GET['id']}'"));
    list($id_tmp) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id FROM ".BLOCKS." WHERE weight='{$next}' AND position='{$position}'"));
    $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$_GET['pos']}' WHERE id='{$id_tmp}'");
    $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$next}' WHERE id='{$_GET['id']}'");
    if (is_ajax()) main_blocks(); else redirect(MODULE);
}

function load_case_blocks(){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=="view"){
        $arr = array($lang['alluser'], $lang['onlyguest'], $lang['onlyuser'], $lang['onlyadmin']);
        echo "<select class='ajax_edit chzn-none' id='sel_ajax'>";
        for($i=0;$i<count($arr);$i++){
            $selected = ($_POST['value']==$i+1) ? " selected='selected'" : "";
            echo "<option value='".($i+1)."'{$selected}>{$arr[$i]}</option>";
        }
        echo "</select>";
    }elseif($_GET['type']=='pos'){
        $arr = array('b' => $lang['top_baner'], 'c' => $lang['top_block'], 'l' => $lang['left_block'], 'r' => $lang['right_block'], 'd' => $lang['bottom_block'], 'f' => $lang['bottom_baner']);
        echo "<select class='ajax_edit chzn-none' id='sel_ajax'>";
        foreach ($arr as $key => $var) {
            $selected = ($_POST['value']==$key) ? " selected='selected'" : "";
            echo "<option value='{$key}'{$selected}>{$var}</option>";
        }
        echo "</select>";
    }
}

function update_block(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($_GET['type']=='title'){
        if(!empty($_POST['text'])) $main->db->sql_query("UPDATE ".BLOCKS." SET {$_POST['col']}='{$_POST['text']}' WHERE id='{$_POST['id']}'");
    }elseif($_GET['type']=='view'){
        echo "<input type='hidden' id='hide_{$_POST['id']}' value='{$_POST['value']}' />".who_view($_POST['value']);
        $arr = explode("_", $_POST['id']);
        $main->db->sql_query("UPDATE ".BLOCKS." SET view='{$_POST['value']}' WHERE id='{$arr[1]}'");
    }elseif($_GET['type']=='pos'){
        $arr = explode("_", $_POST['id']);
        list($weight) = $main->db->sql_fetchrow($main->db->sql_query("SELECT MAX(weight)+1 FROM ".BLOCKS." WHERE position='{$_POST['value']}'"));
        list($position, $weight2) = $main->db->sql_fetchrow($main->db->sql_query("SELECT position, weight FROM ".BLOCKS." WHERE id='{$arr[1]}'"));
        if($position==$_POST['value']) $weight = $weight2;
        echo "<input type='hidden' id='hide_{$_POST['id']}' value='{$_POST['value']}' />";
        $main->db->sql_query("UPDATE ".BLOCKS." SET position='{$_POST['value']}', weight='".((!isset($weight) OR $weight==0 OR empty($weight)) ? 1 : $weight)."' WHERE id='{$arr[1]}'");
        if($position!=$_POST['value']){
            $result = $main->db->sql_query("SELECT id, weight FROM ".BLOCKS." WHERE position='{$position}' ORDER BY weight");
            $i = 1;
            while (list($id, $weight) = $main->db->sql_fetchrow($result)) {
                $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$i}' WHERE id='{$id}'");
                $i++;
            }
        }
        main_blocks();
    }
}

function get_block_position($pos){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    switch($pos){
        case "b": $return = "<img src='includes/images/up_red.gif' alt='' /> ".$main->lang['top_baner']." <img src='includes/images/up_red.gif' alt='' />"; break;
        case "c": $return = "<img src='includes/images/right_green.gif' alt='' /> ".$main->lang['top_block']." <img src='includes/images/left_green.gif' alt='' />"; break;
        case "l": $return = "<img src='includes/images/left.gif' alt='' /> ".$main->lang['left_block']; break;
        case "r": $return = $main->lang['right_block']." <img src='includes/images/right.gif' alt='' />"; break;
        case "d": $return = "<img src='includes/images/right_green.gif' alt='' /> ".$main->lang['bottom_block']." <img src='includes/images/left_green.gif' alt='' />"; break;
        case "f": $return = "<img src='includes/images/down_blue.gif' alt='' /> ".$main->lang['bottom_baner']." <img src='includes/images/down_blue.gif' alt='' />"; break;
    }
    return $return;
}

function get_block_files($select=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $_sels = scan_dir('blocks/', '/block\-.*?\.php$/s'); $sels = array();
    foreach($_sels as $value) {
        $block_name = preg_replace('/block\-(.*?)\.php$/i', '\\1', $value);
        $sels[$value] = isset($lang[$block_name])?$lang[$block_name]:str_replace('_', ' ', $block_name);        
    }
    asort($sels);
    $sels = array_merge(array('' => $lang['no']), $sels);
    return in_sels('file_block', $sels, 'select', $select);
}

function get_block_pos($select=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $select = (isset($_POST['pos'])) ? $_POST['pos'] : $select;
    $arr = array('b' => $lang['top_baner'], 'c' => $lang['top_block'], 'l' => $lang['left_block'], 'r' => $lang['right_block'], 'd' => $lang['bottom_block'], 'f' => $lang['bottom_baner']);
    $sel = "<select name='pos' class='select chzn-search-hide'>";
    foreach ($arr as $key => $var) $sel .= "<option value='{$key}'".(($select==$key) ? " selected='selected'" : "").">{$var}</option>";
    return $sel."</select>";
}

function get_module($checked = array()){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $checked = (isset($_POST['modules'])) ? $_POST['modules'] : $checked;
    $arr_modules = array(); $dir = opendir("modules");
    while(($file = readdir($dir))) if ($file!="." AND $file!=".." AND is_dir("modules/{$file}")) $arr_modules[] = $file;
    closedir($dir); sort($arr_modules);
    $modules  = "<select name='modules[]' multiple='multiple' size='8' class='select2'>\n<option value='home'".(in_array("home", $checked) ? " selected='selected' " : "").">{$lang['home']}</option>\n\n";
    foreach($arr_modules as $value) $modules .= "<option value='{$value}'".((in_array($value, $checked)) ? " selected='selected' " : "").">".(isset($lang[$value]) ? $lang[$value] : str_replace("_", " ", $value))."</option>";
    return $modules."</select>\n";
}

function admin_list_blocks_tpl($value=""){
global $main,$config;
   if(hook_check(__FUNCTION__)) return hook();
   if(hook_check(__FUNCTION__)) return hook();
   $file_tpl=array('');
   $dir=TEMPLATE_PATH."{$config['template']}/";
   if (file_exists($dir)) {
      foreach(array_diff(scandir($dir), array('.', '..')) as $file) {
         if($myfile = stristr($file, "block-")) $file_tpl[$myfile]=$myfile;
      }
   } 
   return in_sels("blocktpl",$file_tpl,"select chzn-search-hide",$value);
}

function edit_block(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    list($title, $view, $position, $blockfile, $weight, $modules, $content, $language,$deftemplate) = $main->db->sql_fetchrow($main->db->sql_query("SELECT title, view, position, blockfile, weight, modules, content, language, blocktpl FROM ".BLOCKS." WHERE id='{$_GET['id']}'"));
    $groups = block_encode_prev_acc($view);
    echo "<form action='{$adminfile}?module={$_GET['module']}&amp;do=save_edit&amp;id={$_GET['id']}' method='post'>\n<table align='center' class='form' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text('title', 'input_text', $title)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name_file']}:</td><td class='form_input'>".get_block_files($blockfile)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['html_code']}:</td><td class='form_input'>".in_area('code', 'textarea', 15, $content)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['position']}:</td><td class='form_input'>".get_block_pos($position)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['def_template']}:</td><td class='form_input'>".admin_list_blocks_tpl($deftemplate)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['show_module']}:</td><td class='form_input'>".get_module(explode(",", $modules))."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['who_views']}:</td><td class='form_input'>".get_groups($groups,'view',true, $main->lang['alluser'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file($language)."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table>".in_hide('last_pos', $position).in_hide('last_weight', $weight)."</form>\n";
    ?>
    <script type="text/javascript">
       //<![CDATA[
       var grl;
       $(document).ready(function(){
             grl=$('#view');
             var opz=grl.find('option:[value="0"]').get(0);
             var exists_all = opz.selected;
             grl.on('change',function(){
                   var a=$(this).val();
                   if(a!=null){
                      if(a[0]=='0') {
                         if(exists_all&&a.length>1) opz.selected = false;
                         else {
                            grl.find('option').each(function(){this.selected=false;})
                            opz.selected = true;
                         }
                      }
                      exists_all = opz.selected;
                      grl.trigger("liszt:updated");
                   }
             })
       });
       //]]>
    </script>
    <?php
}                                                                  

function post_int_array($name){
   $value = ",1";
   if(isset($_POST[$name]) AND is_array($_POST[$name]) AND count($_POST[$name])>0) $value = ",".implode(",",$_POST[$name]);
   return $value;
}

function save_edit_blocks(){
global $main, $msg;
    if(hook_check(__FUNCTION__)) return hook();
    $bool = (empty($_POST['file_block']) AND empty($_POST['code'])) ? false : (empty($_POST['file_block']) AND !empty($_POST['code'])) ? true : (!empty($_POST['file_block']) AND empty($_POST['code'])) ? true : false;
    if ($_POST['title']!="" AND $bool){
        sql_update(array(
            'title'     => $_POST['title'],
            'view'      => post_int_array('view'),
            'position'  => $_POST['pos'],
            'weight'    => ($_POST['last_pos']!=$_POST['pos'])?999:$_POST['last_weight'],
            'blockfile' => $_POST['file_block'],
            'modules'   => (isset($_POST['modules']) AND is_array($_POST['modules'])) ? implode(",", $_POST['modules']) : "",
            'content'   => !isset($_GET['type']) ? $_POST['code'] : "",
            'language'  => $_POST['language'],
            'blocktpl'  => isset($_POST['blocktpl'])?$_POST['blocktpl']:""
        ), BLOCKS, "id='{$_GET['id']}'");
        if($_POST['last_pos']!=$_POST['pos']){
            $result = $main->db->sql_query("SELECT id, weight FROM ".BLOCKS." WHERE position='{$_POST['pos']}' ORDER BY weight");
            $i = 1;
            while(($row = $main->db->sql_fetchrow($result))){
                $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$i}' WHERE id='{$row['id']}'");
                $i++;
            }
        }
        redirect(MODULE);
    } else {
        $msg = $main->lang['no_parametrs'];
        add_block();
    }
}

function add_block(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form action='{$adminfile}?module={$_GET['module']}&amp;do=save_add' method='post'>\n<table align='center' class='form' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text('title', 'input_text')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['name_file']}:</td><td class='form_input'>".get_block_files()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['html_code']}:</td><td class='form_input'>".in_area('code', 'textarea', 15)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['position']}:</td><td class='form_input'>".get_block_pos()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['def_template']}:</td><td class='form_input'>".admin_list_blocks_tpl()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['show_module']}:</td><td class='form_input'>".get_module()."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['who_views']}:</td><td class='form_input'>".get_groups(array(),'view',true, $main->lang['alluser'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>\n";
}

function save_add_blcoks(){
global $main, $msg;
    if(hook_check(__FUNCTION__)) return hook();
    $bool = (empty($_POST['file_block']) AND empty($_POST['code'])) ? false : (empty($_POST['file_block']) AND !empty($_POST['code'])) ? true : (!empty($_POST['file_block']) AND empty($_POST['code'])) ? true : false;
    if ($_POST['title']!="" AND $bool){
        list($weight) = $main->db->sql_fetchrow($main->db->sql_query("SELECT MAX(weight)+1 FROM ".BLOCKS." WHERE position='{$_POST['pos']}'"));
        sql_insert(array(
            'title'     => $_POST['title'],
            'view'      => post_int_array('view'),
            'position'  => $_POST['pos'],
            'blockfile' => $_POST['file_block'],
            'weight'    => (!isset($weight) OR $weight==0 OR empty($weight)) ? 1 : $weight,
            'modules'   => (isset($_POST['modules']) AND is_array($_POST['modules'])) ? implode(",", $_POST['modules']) : "",
            'content'   => !isset($_GET['type']) ? $_POST['code'] : "",
            'language'  => $_POST['language'],
            'blocktpl'  => isset($_POST['blocktpl'])?$_POST['blocktpl']:""
        ), BLOCKS);
        redirect(MODULE);
    } else {
        $msg = $main->lang['no_parametrs'];
        add_block();
    }
}

function save_block_config(){
global $main, $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    $config = $copyright_file."$"."block_config = array(\n";
    $result = $main->db->sql_query("SELECT id, title, position, weight, active, view, blockfile, language, modules, content, blocktpl FROM ".BLOCKS." ORDER BY position, weight");
    if($main->db->sql_numrows($result)>0){
    while(list($id, $title, $position, $weight, $active, $view, $blockfile, $language, $modules, $content, $blocktpl) = $main->db->sql_fetchrow($result)){
        $config .= "\tarray('id' => '{$id}', 'title' => '".addslashes($title)."', 'position' => '{$position}', 'view' => '{$view}', 'active' => '{$active}', 'blockfile' => '{$blockfile}', 'modules' => '{$modules}', 'weight' => '{$weight}', 'content' => '".addslashes($content)."', 'language' => '{$language}', 'blocktpl' => '{$blocktpl}'),\n";
    }
    file_write("includes/config/config_blocks.php", mb_substr($config, 0, mb_strlen($config)-2)."\n);\n?".">");
    } else file_write("includes/config/config_blocks.php", $config."\n);\n?".">"); 
    if(!is_ajax()) redirect(MODULE);
   else main_blocks();
}

function back_block_config(){
global $block_config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM ".BLOCKS);
    foreach($block_config as $arr){
        sql_insert(array(
            'id'        => $arr['id'],
            'title'     => $arr['title'],
            'position'  => $arr['position'],
            'view'      => $arr['view'],
            'active'    => $arr['active'],
            'blockfile' => $arr['blockfile'],
            'modules'   => $arr['modules'],
            'weight'    => $arr['weight'],
            'content'   => magic_quotes($arr['content']),
            'language'  => isset($arr['language']) ? $arr['language'] : ""
        ), BLOCKS);
    }
    if(!is_ajax()) redirect(MODULE);
    else main_blocks();
}

function change_op_blcoks(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT active FROM ".BLOCKS." WHERE id='{$value}'"));
            sql_update(array('active' => (($active==1) ? 0 : 1)), BLOCKS, "id='{$value}'");
            }
        } else {
            foreach($_POST['sels'] as $value){
                 list($position) = $main->db->sql_fetchrow($main->db->sql_query("SELECT position FROM ".BLOCKS." WHERE id='{$value}'"));
                 $position_arr[$position] = $value;
                 $main->db->sql_query("DELETE FROM ".BLOCKS." WHERE id='{$value}'");
            }
            foreach($position_arr as $pos=>$lastid){
                $i = 1;
                $result = $main->db->sql_query("SELECT id FROM ".BLOCKS." WHERE position='{$pos}' ORDER BY weight");
                while(list($id) = $main->db->sql_fetchrow($result)) {
                    $main->db->sql_query("UPDATE ".BLOCKS." SET weight='{$i}' WHERE id='{$id}'");
                    $i++;
                }
            }
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else main_blocks();
}
function switch_admin_blocks(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "edit" : edit_block(); break;
         case "save_edit" : save_edit_blocks(); break;
         case "delete" : delete_blcok(); break;
         case "move" : move_blcok(); break;
         case "on_off" : on_off_blcoks(); break;
         case "update" : update_block(); break;
         case "load_case" : load_case_blocks(); break;
         case "add" : add_block(); break;
         case "save_add" : save_add_blcoks(); break;
         case "save_config" : save_block_config(); break;
         case "back_config" : back_block_config(); break;
         case "change_op" : change_op_blcoks(); break;
         default: main_blocks(); break;
      }
   } elseif($break_load==false) main_blocks();
}
switch_admin_blocks();
?>