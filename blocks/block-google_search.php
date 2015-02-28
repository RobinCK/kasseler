<?php
/**
* Блок поиска Google
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-google_search.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

echo "<!-- Search Google -->
<form method='get' action='http://www.google.com/search'>
<table border='0'>
<tr>
<td nowrap='nowrap' valign='top' align='left' height='32'>
<img src='includes/images/google.gif' border='0' alt='Google' align='middle' /><br />
<input type='text' name='q' size='15' style='margin-right: 3px;' />".
button_search().
"</td>
</tr>
</table>
</form>
<!-- Search Google -->";
?>