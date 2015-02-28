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

global $hooks;
$hooks = array(
    'pm'         => array('title' => 'Плагин личных сообщений', 'type' => 'plugin', 'status' => 'on', 'install' => true, 'minVersion' => '1055', 'maxVersion' => '*', 'info' => array('author' => 'Игорь Огниченко',   'email' => 'ognichenko.igor@gmail.com',   'link' => 'http://www.kasseler-cms.net/',   'license' => 'GNU',   'description' => 'Плагин добавляет страничку личных сообщений пользователя',   'version' => '1.0',   'create' => '01.07.2012',   'cover' => 'images/cover.png',   'logo' => 'images/logo.png')),
);
?>