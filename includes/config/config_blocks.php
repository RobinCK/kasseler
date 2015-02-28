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

$block_config = array(
	array('id' => '1', 'title' => 'Навигация', 'position' => 'l', 'view' => '1', 'active' => '1', 'blockfile' => 'block-modules.php', 'modules' => '', 'weight' => '1', 'content' => '', 'language' => '', 'blocktpl' => ''),
	array('id' => '2', 'title' => 'Администрация', 'position' => 'l', 'view' => '4', 'active' => '1', 'blockfile' => 'block-monitoring.php', 'modules' => '', 'weight' => '2', 'content' => '', 'language' => '', 'blocktpl' => ''),
	array('id' => '4', 'title' => 'Меню пользователя', 'position' => 'r', 'view' => '1', 'active' => '1', 'blockfile' => 'block-user_menu.php', 'modules' => '', 'weight' => '1', 'content' => '', 'language' => '', 'blocktpl' => ''),
	array('id' => '3', 'title' => 'Календарь', 'position' => 'r', 'view' => '1', 'active' => '1', 'blockfile' => 'block-calendar.php', 'modules' => '', 'weight' => '2', 'content' => '', 'language' => '', 'blocktpl' => ''),
	array('id' => '5', 'title' => 'Опрос', 'position' => 'r', 'view' => '1', 'active' => '1', 'blockfile' => 'block-last_voting.php', 'modules' => '', 'weight' => '3', 'content' => '', 'language' => '', 'blocktpl' => '')
);
?>