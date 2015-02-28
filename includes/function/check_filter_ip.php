<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   global $main;
   main::init_function('calc_mask_ip');
   global $proxy, $ip;
   function check_current_login($ip_filter){
      global $proxy, $ip;
      if(hook_check(__FUNCTION__)) return hook();
      $ret=true;
      $curr_ip=$proxy!="0.0.0.0"?$proxy:$ip;
      $curr_ip=ip2long($curr_ip);
      $acc=array();
      if(!isset($_SESSION['ipfilter'])){
         if(!empty($ip_filter)){
            $info=explode(',',$ip_filter);
            $acc=array();
            foreach ($info as $key => $value) {$acc[]=calc_filter_info($value);}
         }
      } else $acc=$_SESSION['ipfilter'];
      if(!empty($acc)){
         $ret=false;
         foreach ($acc as $key => $value) {
            switch(count($value)){
               case 1:if($curr_ip==$value[0]) $ret=true;break;
               case 2:if($curr_ip>=$value[0] && $curr_ip<=$value[1]) $ret=true;break;
            }
         }
      }
      if($ret AND !isset($_SESSION['ipfilter'])) $_SESSION['ipfilter']=$acc;
      if(long2ip($curr_ip)=='127.0.0.1') {$ret=true;unset($_SESSION['ipfilter']);}
      return $ret;
   }
   function die_work_block_ip(){
      global $main,$config;
      $text=isset($main->lang['block_ip_work'])?$main->lang['block_ip_work']:"Your IP address is blocked to work";
      die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
         <html xmlns="http://www.w3.org/1999/xhtml">
         <head>
         <title>'.$config['home_title'].'</title>
         <meta http-equiv="content-type" content="text/html; charset='.$config['charset'].'" />
         <meta name="copyright" content="Copyright (c) Kasseler CMS 2.0.0" />    
         <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
         </head>
         <body>
         <br /><br /><br /><br /><center><h1>'.$text.'</h1><br /><b>Contact E-mail</b>: <a href="mailto:'.$config['contact_mail'].'">'.$config['contact_mail'].'</a></center>
      </body></html>');
      exit;
   }
?>
