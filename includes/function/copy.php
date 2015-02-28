<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Функция рекурсивного копирования
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/copy.php
* @version 2.0
*/

define('DS', DIRECTORY_SEPARATOR);

function rcopy($path, $dest){
    if(is_dir($path)){
        @mkdir($dest);
        $objects = scandir($path);
        if(sizeof($objects) > 0){
            foreach($objects as $file){
                if($file == "." || $file == "..") continue;
                if(is_dir($path.DS.$file)) rcopy( $path.DS.$file, $dest.DS.$file );
                else copy( $path.DS.$file, $dest.DS.$file );
            }
        }
        return true;
    } elseif(is_file($path)) return copy($path, $dest);
    else return false;
}
?>
