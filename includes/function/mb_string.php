<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Получает длину строки
* 
* @param string $string
* @return string
*/
function mb_strlen($string){
    return strlen($string);
}

/**
* Находит позицию первого вхождения строки в строке 
* 
* @param string $haystack
* @param string $needle
* @param int $offset
* @return string
*/
function mb_strpos($haystack, $needle, $offset=0){
    return strpos($haystack, $needle, $offset);
}

/**
* Приводит строку к нижнему регистру 
* 
* @param string $string
* @return string
*/
function mb_strtolower($string){
    return strtolower($string);
}

/**
* Приводит строку к верхнему регистру
* 
* @param string $string
* @return string
*/
function mb_strtoupper($string){
    return strtoupper($string);
}

/**
* Находит позицию последнего вхождения строки в строке
* 
* @param string $haystack
* @param string $needle
* @param int $offset
* @return int
*/
function mb_strrpos($haystack, $needle, $offset = 0){
    return strrpos($haystack, $needle, $offset);
}

/**
* Считает число вхождений подстроки
* 
* @param string $haystack
* @param string $needle
* @param int $offset
* @param int $length
* @return int
*/
function mb_substr_count($haystack, $needle){
    return substr_count($haystack, $needle);
}

/**
* Получает часть строки
* 
* @param string $string
* @param string $start
* @param int $length
* @return string
*/
function mb_substr($string, $start, $length=null){
    return substr($string, $start, $length);
}
?>