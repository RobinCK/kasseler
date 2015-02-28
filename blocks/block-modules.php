<?php
   /**
   * Блок модули сайта
   * 
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource blocks/block-modules.php
   * @version 2.0
   */
   if (!defined('BLOCK_FILE')){
      Header("Location: ../index.php");
      exit;
   }

   global $modules, $lang, $main;
   main::init_function('module_access');
   echo "<ul class='navs'>\n";
   echo "<li><a class='modules_menu' href='".$main->url(array())."' title='{$lang['home']}'><span>{$lang['home']}</span></a></li>\n";
   $hide = "";
   foreach ($modules as $name=>$array){
      $grl = modulelist_encode_prev($array);
      $group = implode(",",$grl);
      if($array['active']==1 AND check_user_group($group)) echo "<li><a class='modules_menu' href='".$main->url(array('module' => $name))."' title='{$array['title']}'><span>{$array['title']}</span></a></li>\n";
   }
   echo "</ul>";

?>