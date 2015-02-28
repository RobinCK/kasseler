<?php
   if(!defined('UPDATECMS')) die('Update access is limited');
   global $database,$main;
   $result = $main->db->sql_query("SELECT id, title, date, result, vote_case, count_vote FROM ".VOTING." where multisel=0");
   $count_fix=0;
   if($main->db->sql_numrows($result)){
      $i = $sum = 0; 
      while(($row = $main->db->sql_fetchrow($result))){
         $_result = explode(",", $row['result']);
         sql_update(array('count_vote'=>(count($_result)-1)),VOTING," id={$row['id']}");
         $count_fix++;
      }
   };
   echo "<span style='color: green;'>Fixed finish, {$count_fix} records</span>";
?>