<?php
/**
* Блок календарь публикаций
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-calendar.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

function calendar_create($month, $year, $relis_arr=array()){
global $lang, $main;
    $first_of_month = mktime(0, 0, 0, intval($month), 7, intval($year));
    $dayofmonth = date('t', $first_of_month);
    $day_count = 1;
    $num = 0;
    for($i = 0; $i<=6; $i++){
        $dayofweek = date('w', mktime(0, 0, 0, $month, $day_count, $year));
        $dayofweek = $dayofweek - 1;
        if($dayofweek == -1) $dayofweek = 6;
        if($dayofweek == $i){
            $week[$num][$i] = $day_count;
            $day_count++;
        } else $week[$num][$i] = "";
    }
    while(true){
        $num++;
        for($i = 0; $i<=6; $i++){
            $week[$num][$i] = $day_count;
            $day_count++;
            if($day_count > $dayofmonth) break;
        }
        if($day_count > $dayofmonth) break;
    }
    $_mon_arr = array($lang['jan'], $lang['feb'], $lang['mar'], $lang['apr'], $lang['may2'], $lang['jun'], $lang['jul'], $lang['aug'], $lang['sep'], $lang['oct'], $lang['nov'], $lang['dec']);
    $month_liter = $_mon_arr[$month-1];
    
    $prev_month = date("m", mktime(0, 0, 0, (intval($month)-1), 7, intval($year)));
    $next_month = date("m", mktime(0, 0, 0, (intval($month)+1), 7, intval($year)));
    $prev_year = date("Y", mktime(0, 0, 0, (intval($month)-1), 7, intval($year)));
    $next_year = date("Y", mktime(0, 0, 0, (intval($month)+1), 7, intval($year)));
    
    $prev = "onclick=\"haja({action:'index.php?blockfile=block-calendar.php&amp;month={$prev_month}&amp;year={$prev_year}', elm:'calendar_block', animation:false}, {}, {}); return false;\"";
    $next = "onclick=\"haja({action:'index.php?blockfile=block-calendar.php&amp;month={$next_month}&amp;year={$next_year}', elm:'calendar_block', animation:false}, {}, {}); return false;\"";
    $content = "<table align='center' id='calendar' cellpadding='3' class='calendar'>\n".
    "<tr><td colspan='7' align='center'><span class='rowdate'><a class='prev_month' href='".$main->url(array('module'=>'search', 'do'=>'date', 'id'=>"{$prev_year}-{$prev_month}"))."' {$prev}>&laquo;</a>&nbsp;&nbsp;&nbsp;&nbsp;<span class='this_mount'>{$month_liter} {$year}</span>&nbsp;&nbsp;&nbsp;&nbsp;<a class='next_month' href='".$main->url(array('module'=>'search', 'do'=>'date', 'id'=>"{$next_year}-{$next_month}"))."' {$next}>&raquo;</a></span></td></tr>\n".
    "<tr class='day_lang'><td>{$lang['pn']}</td><td>{$lang['vt']}</td><td>{$lang['sr']}</td><td>{$lang['ct']}</td><td>{$lang['pt']}</td><td class='holiday'>{$lang['sb']}</td><td class='holiday'>{$lang['vs']}</td></tr>\n";
    for($i = 0; $i<count($week); $i++){
        $content .= "<tr>";
        for($j = 0; $j<=6; $j++){
            if(!empty($week[$i][$j])){
                $d = (isset($relis_arr[$week[$i][$j]])) ? "<a href='".$main->url(array('module'=>'search', 'do'=>'date', 'id'=>$relis_arr[$week[$i][$j]]))."' class='cal_link'>{$week[$i][$j]}</a>\n" : $week[$i][$j];
                if(($j == 5 || $j == 6) AND !($week[$i][$j]==date("d") AND $month==date("m")))  $content .= "<td class='holiday'>{$d}</td>";
                elseif($week[$i][$j]==date("d") AND $month==date("m") AND $year==date("Y")) $content .= "<td class='today'>{$d}</td>"; 
                else $content .= "<td class='week'>{$d}</td>";
            } else $content .= "<td>&nbsp;</td>";
        }
        $content .= "</tr>\n";
    } 
    $content .= "</table>\n";
    return $content;
}

global $main;
if(!isset($_GET['month']) AND !isset($_GET['year'])){
    $month = date("m");
    $year = date("Y");
} else {
    $month = $_GET['month']; 
    $year = $_GET['year'];
}
$relis_arr = array();
$result = $main->db->sql_query("SELECT DATE_FORMAT(date, '%d') FROM ".CALENDAR." WHERE DATE_FORMAT(date, '%m')='{$month}' AND DATE_FORMAT(date, '%Y')='{$year}' AND status='1'");
while(list($day) = $main->db->sql_fetchrow($result)) {
    $day2 = ($day[0]=="0") ? $day[1] : $day;
    $relis_arr[$day2] = "{$year}-{$month}-{$day}";
}
if(is_ajax()) echo calendar_create($month, $year, $relis_arr);
else echo "<div id='calendar_block'>".calendar_create($month, $year, $relis_arr)."</div>";
?>