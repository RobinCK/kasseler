<?php
/**
* Блок выбора локализации
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-languages.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')){
    Header("Location: ../index.php");
    exit;
}

global $lang;
echo "<div align='center'>
<a href='http://".get_host_name()."/ua' onclick=\"setCookie('lang', 'ukraine', '86400', '/');\"><img src='includes/images/flags/ukraine.png' alt='".(isset($lang['ukraine'])?$lang['ukraine']:'ukraine')."' /></a>
<a href='http://".get_host_name()."/ru' onclick=\"setCookie('lang', 'russian', '86400', '/');\"><img src='includes/images/flags/russian.png' alt='".(isset($lang['russian'])?$lang['russian']:'russian')."' /></a>
<a href='http://".get_host_name()."/en' onclick=\"setCookie('lang', 'english', '86400', '/');\"><img src='includes/images/flags/english.png' alt='".(isset($lang['english'])?$lang['english']:'english')."' /></a>
<a href='http://".get_host_name()."/de' onclick=\"setCookie('lang', 'german', '86400', '/');\"><img src='includes/images/flags/german.png' alt='".(isset($lang['german'])?$lang['german']:'german')."' /></a>
</div>";

?>