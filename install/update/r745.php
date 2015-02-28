<?php
   if(!defined('UPDATECMS')) die('Update access is limited');
   global $database,$main;
   $update_table=array($database['prefix'].'_audio',
   $database['prefix'].'_faq',
   $database['prefix'].'_files',
   $database['prefix'].'_jokes',
   $database['prefix'].'_media',
   $database['prefix'].'_news',
   $database['prefix'].'_pages',
   $database['prefix'].'_shop');
   foreach ($update_table as $key => $value) {
      $count=0;
      $dbrs=$main->db->sql_query("select id,cid from {$value}  where cid REGEXP '[a-zA-Z]+' ");
      while ($row=$main->db->sql_fetchrow($dbrs)){
         $arv=array();
         preg_match_all('/,([0-9]*),/sm', $row['cid'], $reg, PREG_PATTERN_ORDER);
         for ($i = 0; $i < count($reg[0]); $i++) $arv[]=$reg[1][$i];
         if (count($arv)>0) {
            sql_update(array('cid'=>",".implode(',',$arv).","),$value," id={$row['id']}");
            if ($main->db->sql_affectedrows()>0) $count++;
         }
      }
      if ($count>0) echo "<span style='color: green;'>Fixed table <b>{$value}</b>, rows = <b>{$count}</b> </span><br />";
   }
   echo "<span style='color: green;'>Fixed finish</span>";
?>