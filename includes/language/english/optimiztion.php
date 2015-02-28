<?php
/**
* Языковый файл
* 
* @author Dmitrey Browko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

$lang = array(
    'optimization_add'                  => 'optimize',
    'optimization_selected'             => 'Selected optimization',
    'optimization_master'               => 'Optimization Wizard Kasseler CMS',
    'optimize_description'              => 'Welcome to the Optimization Wizard.<br />Here you can delete the old data, to remove inactive users, to optimize the database.',
    'optimize_forum'                    => 'Cleaning the old forum topics',
    'optimize_forum_d'                  => 'Cleaning the forum topics on which there were no new positions after the selected date',
    'optimize_comment'                  => 'Cleanup of obsolete comments',
    'optimize_comment_d'                => 'Removing all the comments that were added to the selected date',
    'optimize_log_access'               => 'Cleaning logs of security-related',
    'optimize_log_access_d'             => 'Delete error logs authentication, php, http, SQL.',
    'optimize_user'                     => 'Deleting users',
    'optimize_user_activation'          => 'Removal is not activated by the user',
    'optimize_user_activation_d'        => 'Removal of users who have registered <b>before</b> the chosen date, and have not activated your account. <br/><b>Note, disabled users also fall within the selected category.</b>',
    'optimize_user_last_visit'          => 'Removal of users for a long time did not go',
    'optimize_user_last_visit_d'        => 'Remove users who have not visited, <b>later</b> date selected',
    'optimize_custom'                   => 'Purification of information on the module "{MODULE}"',
    'optimize_custom_d'                 => 'Removal of all publications that have been added <b>before</b> the selected date',
    'optimize_config'                   => 'Setting the date (default) to clear the information',
    'clear_to'                          => 'um alle zu löschen',
    'change_table'                      => 'Changes in the table <b>{TABLE}</b>: delete records {COUNT}',
    'period_day'                        => 'Tag',
    'period_month'                      => 'months',
    'period_year'                       => 'years',
);                          
?>