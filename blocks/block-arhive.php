<?php
/**
* Блок архива публикация
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-arhive.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
	Header("Location: ../index.php");
	exit;
}

global $main;
$_mon_arr = array($lang['jan'], $lang['feb'], $lang['mar'], $lang['apr'], $lang['may2'], $lang['jun'], $lang['jul'], $lang['aug'], $lang['sep'], $lang['oct'], $lang['nov'], $lang['dec']);
$result = $main->db->sql_query("SELECT DATE_FORMAT(date,'%Y-%m') AS dat, COUNT(*) AS count FROM ".CALENDAR." GROUP BY dat ORDER BY DATE_FORMAT(date,'%Y-%m') desc LIMIT 12");
while(list($month, $count) = $main->db->sql_fetchrow($result)) {
    $m = explode('-', $month);    
    echo "<a class='arhive_date' href='".$main->url(array('module' => 'search', 'do' => 'date', 'id' => $month))."'>{$_mon_arr[(int)$m[1]-1]} {$m[0]} ({$count})</a><br />";
}

?>