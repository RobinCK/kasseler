<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!"); 

function main_hook_config(){
global $main, $adminfile, $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$main->module}&amp;do=config&amp;id={$_GET['id']}&amp;op=save_hook_config' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory']}</b>:<br /><i>{$main->lang['directory_d']}</i></td><td class='form_input2'>".in_text('directory', 'input_text2', $userconf['directory'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_type']}</b>:<br /><i>{$main->lang['attaching_files_type_d']}</i></td><td class='form_input2'>".in_text('attaching_files_type', 'input_text2', $userconf['attaching_files_type'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_width']}</b>:<br /><i>{$main->lang['miniature_image_width_d']}</i></td><td class='form_input2'>".in_text('miniature_image_width', 'input_text2', $userconf['miniature_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['miniature_image_height']}</b>:<br /><i>{$main->lang['miniature_image_height_d']}</i></td><td class='form_input2'>".in_text('miniature_image_height', 'input_text2', $userconf['miniature_image_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_width']}</b>:<br /><i>{$main->lang['max_image_width_d']}</i></td><td class='form_input2'>".in_text('max_image_width', 'input_text2', $userconf['max_image_width'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['max_image_height']}</b>:<br /><i>{$main->lang['max_image_height_d']}</i></td><td class='form_input2'>".in_text('max_image_height', 'input_text2', $userconf['max_image_height'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching_files_size']}</b>:<br /><i>{$main->lang['attaching_files_size_d']}</i></td><td class='form_input2'>".in_text('attaching_files_size', 'input_text2', $userconf['attaching_files_size'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['file_upload_limit']}</b>:<br /><i>{$main->lang['file_upload_limit_d']}</i></td><td class='form_input2'>".in_text('file_upload_limit', 'input_text2', $userconf['file_upload_limit'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['attaching']}</b>:<br /><i>{$main->lang['attaching_d']}</i></td><td class='form_input2'>".in_chck('attaching', 'input_checkbox', $userconf['attaching'])."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_hook_settings(){
global $userconf, $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_user.php', '$userconf', $userconf);
    redirect("{$adminfile}?module={$main->module}&do=config&amp;id={$_GET['id']}");
}

if(isset($_GET['op'])){
    switch($_GET['op']){
        case "save_hook_config": save_hook_settings(); break;
        default: main_hook_config(); break;
    }
} else main_hook_config();

?>