<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   * @tutorial хуки для функционирования модуля forum
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");

   global $main,  $tpl_create;
   function forum_hook_logout(){
      if(hook_check(__FUNCTION__)) return hook();
      main::required("modules/forum/function.php");
      forum_save_read_info();
      if(isset($_SESSION['forum_read'])) unset($_SESSION['forum_read']);
      if(isset($_SESSION['forum_access'])) unset($_SESSION['forum_access']);
      logout();
   }

   hook_register('logout','forum_hook_logout',false); 
?>
