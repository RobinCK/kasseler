<?php
/**********************************************/
/* Kasseler CMS: Content Management System    */
/**********************************************/
/*                                            */
/* Copyright (c)2007-2012 by Igor Ognichenko  */
/* http://www.kasseler-cms.net/               */
/*                                            */
/**********************************************/

if (!defined('FUNC_FILE')) die('Access is limited');

global $forum;
$forum = array(
    'forum_title'                => 'SiteName Forum',
    'directory'                  => 'uploads/forum/',
    'rss_title'                  => 'sitename - Forum',
    'attaching_files_type'       => 'zip,rar,tar,gz,jpeg,jpg,gif,png,pdf',
    'miniature_image_width'      => '300',
    'miniature_image_height'     => '500',
    'max_image_width'            => '1280',
    'max_image_height'           => '2048',
    'attaching_files_size'       => '1024',
    'file_upload_limit'          => '10',
    'topic_views_num'            => '30',
    'post_views_num'             => '15',
    'attaching'                  => 'on',
    'timeout'                    => '100',
    'change_acc'                 => '3'
);
?>