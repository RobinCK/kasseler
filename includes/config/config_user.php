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

global $userconf;
$userconf = array(
    'directory_avatar'           => 'uploads/avatars/',
    'guest_name'                 => 'Гость',
    'size_avatar'                => '30',
    'width_avatar'               => '120',
    'height_avatar'              => '120',
    'type_avatar'                => 'gif,jpg,png,jpeg',
    'password_length'            => '6',
    'default_group'              => '5',
    'load_avatar'                => 'on',
    'ratings'                    => 'on',
    'registration'               => 'email',
    'comments_sort'              => 'ASC',
    'comments'                   => '',
    'guests_comments'            => '',
    'user_name_length'           => '4',
    'email_deny'                 => '',
    'directory'                  => 'uploads/pm/',
    'attaching_files_type'       => 'zip,rar,tar,gz,jpeg,jpg,gif,png',
    'miniature_image_width'      => '300',
    'miniature_image_height'     => '500',
    'max_image_width'            => '1280',
    'max_image_height'           => '2048',
    'attaching_files_size'       => '1024',
    'file_upload_limit'          => '10',
    'attaching'                  => 'on'
);
?>