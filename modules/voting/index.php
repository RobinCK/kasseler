<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('KASSELERCMS')) die("Hacking attempt!");   
global $navi, $main, $tpl_create;
//Создаем навигацию модуля
$navi = navi(array(), false, false);
main::required("modules/{$main->module}/global.php");

function main_voting(){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_home()) echo $navi;
    $result = $main->db->sql_query("SELECT id, title, date, result, vote_case, count_vote FROM ".VOTING." WHERE ((module is null) or module='') and status='1' AND (language='{$main->language}' OR language='') ORDER BY date DESC");
    if($main->db->sql_numrows($result)){
        open();
        echo "<table class='table2' width='100%'>";
        $i = $sum = 0; $tr = 'row1';
        while(($row = $main->db->sql_fetchrow($result))){
            $_result = explode(",", $row['result']);
            $tmpcase = explode("|", $row['vote_case']);
            $sum = 0;
            for($y=0; $y<count($tmpcase); $y++){
                $case = $tmpcase[$y];
                $vote[$case] = 0;
                for($j=0; $j<count($_result); $j++){
                    if($_result[$j]==$y+1) {$vote[$case]++; $sum++;}
                }
            }
            if($row['count_vote']==0){ // fix old version vote
              sql_update(array('count_vote'=>$sum),VOTING," id={$row['id']}");
            } else $sum=$row['count_vote'];
            echo "<tr class='{$tr} pointer' onclick=\"location.href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $row['id']))."'\"><td width='80'>".format_date($row['date'])."</td><td><a href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $row['id']))."'><b>{$row['title']}</b></a></td><td width='110' align='right'>{$main->lang['votes']} ({$sum})</td></tr>";             
            $tr = ($tr=='row1') ? 'row2' : 'row1';
        }
        echo "</table>";
        close();
    } else info($main->lang['noinfo']);
}

function more_voting($msg=""){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    echo $navi;
    $vote_id=intval($_GET['id']);
    $links="<a class='result_votes link_button' href='".$main->url(array('module' => $main->module, 'do' => 'result', 'id' => $vote_id))."' title='{$main->lang['result_vote']}'>{$main->lang['result_vote']}</a> <a class='other_votes link_button' href='".$main->url(array('module' => $main->module))."' title='{$main->lang['other_votes']}'>{$main->lang['other_votes']}</a>";
    echo global_more_voting($vote_id,$links,$msg);
}

function result_voting($msg=""){
global $main, $navi, $pull;
    if(hook_check(__FUNCTION__)) return hook();
    //Подключаем модуль комментариев
    echo global_result_voting(intval($_GET['id']),$msg);
}

function set_vote_voting(){
global $main, $points, $ip;
    if(hook_check(__FUNCTION__)) return hook();
    $ret=global_set_vote_voting();
      switch ($ret[0]){
         case 0:
            if(!is_ajax()) redirect($main->url(array('module' => $main->module, 'do' => 'result', 'id' => $_GET['id'])));            
            else result_voting();
            break;
         case 1:meta_refresh(5, $main->url(array('module' => $main->module, 'do' => 'result', 'id' => $_GET['id'])), $main->lang['yourisvoted']);
            break;
         case 2:more_voting($main->lang['yourisnotselectvote']);
            break;
      }
}
function switch_module_voting(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "more": more_voting(); break;
         case "result": result_voting(); break;
         case "set_votes": set_vote_voting(); break;
         default: main_voting(); break;
      }
   } else main_voting();
}
switch_module_voting();
?>