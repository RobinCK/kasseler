<?php
/**********************************************/
/* Kasseler CMS: Content Management System    */
/**********************************************/
/*                                            */
/* Copyright (c)2007-2015 by Igor Ognichenko  */
/* http://www.kasseler-cms.net/               */
/*                                            */
/**********************************************/

if (!defined('FUNC_FILE')) die('Access is limited');

global $database;
$database = array(
    'host'                => 'localhost',
    'user'                => 'root',
    'password'            => '',
    'name'                => 'kasseler2',
    'prefix'              => 'kasseler',
    'type'                => 'mysql',
    'charset'             => 'utf8',
    'cache'               => '',
    'sql_cache_clear'     => 'INSERT,UPDATE,DELETE',
    'no_cache_tables'     => 'sessions',
    'revision'            => '1251'
);
?>