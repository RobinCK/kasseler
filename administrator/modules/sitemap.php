<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

function main_sitemap(){
global $main, $adminfile, $modules_sitemap, $modules, $file_sitemap;
    if(hook_check(__FUNCTION__)) return hook();
    clearstatcache();
    if(file_exists($file_sitemap)) {
        $file_txt = file_get_contents($file_sitemap);
        $all_link = !empty($file_txt) ? preg_match_all("#\<loc\>(.*)\<\/loc\>#", $file_txt, $matches) : '0';
        $file = stat($file_sitemap);
        echo "<table width='100%'>".
            "<tr><td align='left' width='130'><b>{$main->lang['file']}</b>:</td><td colspan='3'><b style='color: green;'>{$file_sitemap}</b></td></tr>\n".
            "<tr><td align='left'><b>{$main->lang['sizes']}</b>:</td><td>".get_size($file['size'])."</td><td align='left'><b>{$main->lang['all_link']}</b>:</td><td>{$all_link}</td></tr>\n".
            "<tr><td align='left'><b>{$main->lang['create_date']}</b>:</td><td>".format_date(date('d.m.Y G:i:s', $file['ctime']), 'd.m.Y G:i:s')."</td><td align='left'><b>{$main->lang['update_date']}</b>:</td><td>".format_date(date('d.m.Y G:i:s', $file['mtime']), 'd.m.Y G:i:s')."</td></tr></table><hr />\n";
    } else warning($main->lang['mess_sitemap']);
    $i  = 1;
    $sm = 0;
    $tr = 'row1';
    echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['module']}</th><th width='80'>{$main->lang['priority']}</th><th width='100'>{$main->lang['changefreq']}</th><th width='70'>{$main->lang['all_link']}</th><th width='70'>{$main->lang['functions']}</th></tr>";    
    $result = $main->db->sql_query("SELECT * FROM ".MODULES." WHERE active='1' AND ( view='1' OR view='2')");    
    $row=array();
    while (($rows=$main->db->sql_fetchrow())) $row[] = $rows;
    foreach ($row as $key=>$value) if(!isset($modules_sitemap[$value['module']]) AND $value['module']!='forum') unset($row[$key]);    
    foreach ($row as $key=>$value) {
        $set = get_setting_sitemap($value['sitemap']);
        if(isset($modules_sitemap[$value['module']]) AND $modules_sitemap[$value['module']]!='') {
            $res = $main->db->sql_query("SELECT * FROM {$modules_sitemap[$value['module']]} WHERE status='1'"); 
            $lin = $main->db->sql_numrows($res);
            $lin = ($lin==0) ? 1 : $lin;
        } elseif ($value['module']=='forum'){
            $res = $main->db->sql_query("SELECT topic_id FROM ".TOPICS.""); 
            $lin = $main->db->sql_numrows($res);
            $lin = ($lin==0) ? 1 : $lin;
        } else $lin = 1;
        $sm  = $sm + $lin;
        $update = "";
        switch($set['changefreq']){
            case 'always': $update = "<span style='color: #FF0000;'>{$main->lang['always']}</span>"; break;
            case 'hourly': $update = "<span style='color: #DF0000;'>{$main->lang['hourly']}</span>"; break;
            case 'daily': $update = "<span style='color: #BF0000;'>{$main->lang['daily']}</span>"; break;
            case 'weekly': $update = "<span style='color: #9F0000;'>{$main->lang['weekly']}</span>"; break;
            case 'monthly': $update = "<span style='color: #7F0000;'>{$main->lang['monthly']}</span>"; break;
            case 'yearly': $update = "<span style='color: #5F0000;'>{$main->lang['yearly']}</span>"; break;
            case 'never': $update = "<span style='color: #3F0000;'>{$main->lang['never']}</span>"; break;
        }
        $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$value['id']}")."</td></tr></table>";
        echo "<tr class='{$tr}'><td align='center'>{$i}</td><td>{$value['title']}</td><td align='center'>{$set['priority']}</td><td align='center'>{$update}</td><td align='center'>{$lin}</td><td align='center'>{$op}</td></tr>\n";
        $tr = ($tr=='row1') ? 'row2' : 'row1';
        $i++;
    }
    echo "<tr class='{$tr}'><td colspan='2'>{$main->lang['alles']}:</td><td align='center'>&nbsp;</td><td>&nbsp;</td><td align='center'>{$sm}</td><td>&nbsp;</td></tr></table>";
}

function edit_sitemap(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $priority = array (
        '0.1'=>'0.1',
        '0.2'=>'0.2',
        '0.3'=>'0.3',
        '0.4'=>'0.4',
        '0.5'=>'0.5',
        '0.6'=>'0.6',
        '0.7'=>'0.7',
        '0.8'=>'0.8',
        '0.9'=>'0.9',
        '1.0'=>'1.0'
    );
    $changefreq = array (
        'always' => $main->lang['always'],
        'hourly' => $main->lang['hourly'],
        'daily'  => $main->lang['daily'],
        'weekly' => $main->lang['weekly'],
        'monthly'=> $main->lang['monthly'],
        'yearly' => $main->lang['yearly'],
        'never'  => $main->lang['never']
    );
    $result = $main->db->sql_query("SELECT * FROM ".MODULES." WHERE id='{$_GET['id']}' LIMIT 1");
    if($main->db->sql_numrows($result)>0){
        $row = $main->db->sql_fetchrow($result);
        $set = get_setting_sitemap($row['sitemap']);	
        echo "<form enctype='multipart/form-data' action='{$adminfile}?module={$main->module}&amp;do=save&amp;id={$_GET['id']}' method='post'>".
             "<table class='form' width='100%'>".
             "<tr class='row_tr'><td class='form_text'>{$main->lang['module']}:</td><td class='form_input'><b>{$row['title']}</b></td></tr>\n".    
             "<tr class='row_tr'><td class='form_text'>{$main->lang['priority']}:</td><td class='form_input'>".in_sels('priority', $priority, 'input_text', $set['priority'])."</td></tr>\n".
             "<tr class='row_tr'><td class='form_text'>{$main->lang['changefreq']}:</td><td class='form_input'>".in_sels('changefreq', $changefreq, 'input_text', $set['changefreq'])."</td></tr>\n".
             "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
             "</table></form>";
    }
}

function save_edit_sitemap(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    sql_update(array( 'sitemap'=>"{$_POST['priority']}|{$_POST['changefreq']}"), MODULES, "id='{$_GET['id']}'");
    redirect(MODULE);
}

function get_setting_sitemap($str=''){
    if(hook_check(__FUNCTION__)) return hook();
    $set = array('priority'=>'0.5', 'changefreq'=>'monthly');
    if ($str!='') {
        $modul = explode ('|', $str); 
        if (isset($modul[0])) $set['priority']   = $modul[0];
        if (isset($modul[1])) $set['changefreq'] = $modul[1];
    }
    return $set;
}
function switch_admin_sitemap(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "edit": edit_sitemap(); break;
         case "save": save_edit_sitemap(); break;
         case "create": generate_sitemap(); break;
         default: main_sitemap(); break;
      }
   } else main_sitemap();
}
switch_admin_sitemap();
?>
