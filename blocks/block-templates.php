<?php
/**
* Блок выбора шаблонов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-templates.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')){
    Header("Location: ../index.php");
    exit;
}

global $lang, $load_tpl;
if(($handle = opendir(TEMPLATE_PATH))){
    echo "{$lang['template']}: <select name='template' style='width:140px;' onchange='setCookie('them', this.value, '86400', '/'); location.href='http://'+location.host+'/';'>";
    while(false !== ($file = readdir($handle))) if(is_dir(TEMPLATE_PATH.$file) AND $file!='admin' AND $file!='pda' AND preg_match('/([a-z_\-]+)/i', $file)) echo "<option value='{$file}'".(($load_tpl==$file)?" selected='selected'":"").">".(isset($lang[$file])?$lang[$file]:$file)."</option>";
    echo "</select>";
closedir($handle);
}
?>