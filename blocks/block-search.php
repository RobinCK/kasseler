<?php
/**
* Блок поиска по сайту
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-search.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
	Header("Location: ../index.php");
	exit;
}

global $main;
echo "<form action='index.php?module={$main->module}&amp;do=result' method='get'>".
in_hide('module', 'search').
in_hide('do', 'result').
in_hide('search_type', '2').
"<table width='100%'><tr>
<td><input type='text' name='story' class='textsearchblock' style='width: 100%;' /></td>
<td align='right'>".button_search()."</td>
</tr></table></form>";
?>
