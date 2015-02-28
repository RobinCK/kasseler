<?php
/**
* Блок поиска Яндекс
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-yandex_search.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

echo "<!-- Search Yandex -->
<form method='get' action='http://www.yandex.ru/yandsearch'>
<table width='100%'>
<tr>
<td nowrap='nowrap' valign='top' align='left' height='32'>
<img src='includes/images/yandex.gif' border='0' alt='Yandex' align='middle' /><br/>
<input type='text' name='text' size='15' maxlength='255' value='' style='margin-right: 3px;' />".
button_search().
"</td>
</tr>
</table>
</form>
<!-- Search Yandex -->";
?>