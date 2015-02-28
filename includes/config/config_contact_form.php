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

$contact_form = array (
  0 => 
  array (
    'title' => 'Ваше имя',
    'name' => 'name',
    'type' => 'text',
    'class' => 'input_text',
    'default' => 'user_name',
    'option' => false,
    'must' => true,
  ),
  1 => 
  array (
    'title' => 'Ваш E-mail',
    'name' => 'email',
    'type' => 'text',
    'class' => 'input_text',
    'default' => 'user_email',
    'option' => false,
    'must' => true,
  ),
  2 => 
  array (
    'title' => 'Тема',
    'name' => 'subject',
    'type' => 'text',
    'class' => 'input_text',
    'default' => '',
    'option' => false,
    'must' => true,
  ),
  3 => 
  array (
    'title' => 'Сообщение',
    'name' => 'description',
    'type' => 'textarea',
    'class' => 'textarea',
    'default' => '',
    'option' => false,
    'must' => false,
  ),
  4 => 
  array (
    'title' => 'Загрузить файл с компьютера',
    'name' => 'fileloader[]',
    'type' => 'file',
    'class' => '',
    'default' => '',
    'option' => '',
    'must' => false,
  ),
);
?>