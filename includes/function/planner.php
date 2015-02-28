<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $file_lock;
   $file_lock = "uploads/tmpfiles/runer.locked";
   global $planner,$structure;
   $structure=array('time', 'timeout', 'lastrun', 'nextrun', 'name', 'weekdays', 'url', 'status', 'timerepeat');

   function runer_check(){
      global $file_lock, $config;
      if(hook_check(__FUNCTION__)) return hook();
      if(file_exists($file_lock)){
         $time_unlock = isset($config['run_flush'])?$config['run_flush']:1;
         $time_unlock = $time_unlock * 3600;
         $d = filectime($file_lock);
         if((time()-$d)>=$time_unlock) unlink($file_lock);
      }
   }

   function runer(){
      global $planner, $copyright_file, $file_lock, $config;
      if(hook_check(__FUNCTION__)) return hook();
      runer_check();
      main::init_function('get_sock_content');

      if(!file_exists($file_lock)){
         $save = false; 
         $file = fopen($file_lock, 'w');
         fputs ($file, '');
         fclose ($file);
         $sysdate = gmdate('U') +(intval($config['GMT_correct'])*60*60);
         foreach($planner as $id => $run){
            if(!empty($run['nextrun'])){
               $nextrun = strtotime($run['nextrun']);
               if($nextrun<=$sysdate AND in_array(gmdate('w', $sysdate), explode(',', $run['weekdays'])) AND $run['status']==ENABLED){
                  $save = true;
                  $next = gmdate('Y-m-d', $sysdate+60*60*24)." ".$run['time'];
                  $planner[$id]['nextrun']=$next;
                  $planner[$id]['lastrun']=kr_date('Y-m-d H:i');
                  $action = strpos($planner[$id]['url'], 'http://') !== false ? $planner[$id]['url'] : 'http://'.get_host_name().'/'.$planner[$id]['url'];
                  $p = parse_url($action);
                  $content = get_sock_content($p['host'], isset($p['query'])?$p['path']."?".$p['query']:$p['path'], 80, $run['timeout']*30,'  kasselerbot', $action);
                  echo  $content;
               }
            }
         }
         if($save==true){
            save_file_planer('config_planner.php');
         }
         unlink($file_lock);
      }
   }

   function save_file_planer($file_config){
      global $copyright_file,$planner,$structure; 
      if(hook_check(__FUNCTION__)) return hook();
      $string = "{$copyright_file}\$planner = array(";
      foreach ($planner as $kay=>$value){
         $row="";
         foreach ($structure as $val) {
            if(isset($value[$val]))  $row.=", '{$val}' => '".addslashes($value[$val])."'";
         }
         $row=substr($row,2);
         $string .= "\n    array({$row}),";
      }
      if(count($planner)>0) $string=substr($string,0,-1);
      $string .= "\n);\n?".">";
      $drs = explode('/', $file_config);
      $file_link = (count($drs)==1) ? "includes/config/{$file_config}" : $file_config;
      if(file_exists($file_link)){
         if(is_writable($file_link)){
            $file = fopen($file_link, "w");
            fputs ($file, $string);
            fclose ($file);
         }
      } else {
         $file = fopen($file_link, "w");
         fputs ($file, $string);
         fclose ($file);
      }
   }

?>
