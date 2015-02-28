<?php
if(!defined('FUNC_FILE')) die('Access is limited');
   global $main;
   main::init_function('calc_mask_ip');
   $info=$_POST['info'];
   $a=calc_filter_info($info);
   switch (count($a)) {
      case 0:echo "";   
         break;
      case 1:echo long2ip($a[0]);
         break;
      case 2:echo long2ip($a[0])."-".long2ip($a[1]);
         break;
   }
?>
