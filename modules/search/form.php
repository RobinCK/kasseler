<?php
/**
* Форма поиска
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource modules/shop/form.php
* @version 2.0
*/
if (!defined('SEARCH_MODULE')) die("Hacking attempt!");

global $main;

open();

$sel = get_search_module();
foreach($sel as $key => $value) $sel[$key] = "     {$value}";
$sel = array_merge(array('' => $main->lang['findallmodule']), $sel);
if(isset($_GET['do']) AND $_GET['do']=='result') foreach($_GET as $key => $value) if(!isset($_POST[$key])) $_POST[$key] = $value;

echo "<form action='index.php?module={$main->module}&amp;do=result' method='get'>
    <table cellpadding='0' cellspacing='0' width='100%' class='form'>
    <tr>
        <td>
            <div align='center'>
                ".in_hide('module', $main->module).in_hide('do', 'result')."
                <table cellpadding='0' cellspacing='8' width='100%'>
                    <tr style='vertical-align: top;'>
                        <td>
                            <fieldset style='padding-top:5px'>
                                <legend>{$main->lang['findcontent']}</legend>
                                <table cellpadding='0' cellspacing='3' border='0' width='100%'>
                                    <tr><td style='padding-bottom: 5px;'><div align='left'>{$main->lang['findtext']}</div><div>".in_text('story', 'input_text2')."</div></td></tr>
                                    <tr><td>".in_sels('search_type', array($main->lang['findtitle'], $main->lang['findonlycontent'], $main->lang['findtitleandcontent']), 'select')."</td></tr>
                                </table>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset style='padding-bottom:14px'>
                                <legend>{$main->lang['findauthor']}</legend>
                                <table cellpadding='0' cellspacing='3' border='0' width='100%'>
                                    <tr><td style='padding-bottom: 5px;' colspan='2'><div align='left'>{$main->lang['findauthorname']}</div><div align='left'>".in_text('author', 'input_text2')."</div></td></tr>
                                    <tr><td width='15'>".in_chck('author_full', 'checkbox')."</td><td>{$main->lang['find_fullname']}</td></tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr style='vertical-align: top;'>
                        <td width='50%' valign='top'>
                        <fieldset style='padding-top:10px'>
                            <legend>{$main->lang['findresult']}</legend>
                            <div style='padding:3px'>
                                ".in_sels('sortby', array('key' => $main->lang['findsortaskey'], 'date' => $main->lang['findsortasdate'], 'title' => $main->lang['findsortastitle'], 'author' => $main->lang['findsortasauthor']), 'select')."
                                <div style='padding-bottom: 5px;'></div>
                                ".in_sels('sort_type', array('desc' => $main->lang['findsortasdesc'], 'asc' => $main->lang['findsortasasc']), 'select')."
                            </div>
                        </fieldset>
                        <fieldset style='padding-top:10px'>
                            <legend>{$main->lang['viewresult']}</legend>
                            <table cellpadding='0' cellspacing='3' border='0'>
                                <tr align='left' valign='middle'>
                                    <td width='140'><span>{$main->lang['resulttopage']}: </span></td><td>".in_text('result_in_page', 'input_text2', 15)."</td>
                                </tr>
                                <tr align='left' valign='middle'>
                                    <td>{$main->lang['viewresultsearch']}: </td><td align='left' style='padding-top: 5px;'>".in_radio('view_type', 0, $main->lang['findshowtitle'], 'tt', true)."<br />".in_radio('view_type', 1, $main->lang['findshowcontent'], 'tc')."</td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                        <td width='50%' valign='top'>
                            <fieldset>
                                <legend>{$main->lang['findmodule']}</legend>
                                <div style='padding: 6px 4px 9px 4px;'><div>".in_sels('sel_modules', $sel, 'select', array(''), '', true, 10)."</div></div>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div style='margin-top:6px' align='center'>
                                <input type='submit' style='margin:0px 20px 0 0px' value='{$main->lang['search']}' />
                            </div><br />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr></table></form>";
close();
?>