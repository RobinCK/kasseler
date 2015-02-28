<?php
if(!defined('FUNC_FILE')) die('Access is limited');
/**
* Функции работы с архивами ZIP
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/zip.php
* @version 2.0
*/

/**
* EXEMPLE
* zip_create('file.zip', 'uploads/', '.svn');
* zip_extract ('file.zip', 'uploads/');
*/

class ZIPFOLDER {
    protected $zip;
    protected $root;
    protected $ignored_names;
    
    public function __construct($file, $folders, $ignored=null) {
        $this->zip = new ZipArchive();
        $this->ignored_names = is_array($ignored) ? $ignored : $ignored ? array($ignored) : array();
        if($this->zip->open($file, ZIPARCHIVE::CREATE)!==true){
            throw new Exception("cannot open <{$file}>\n");
        }
        $folders = is_array($folders) ? $folders : array($folders);
        foreach($folders as $folder){
            $folder = mb_substr($folder, -1) == '/' ? mb_substr($folder, 0, mb_strlen($folder)-1) : $folder;
            if(mb_strstr($folder, '/')) {
                $this->root = mb_substr($folder, 0, mb_strrpos($folder, '/')+1);
                $folder = mb_substr($folder, mb_strrpos($folder, '/')+1);
            }
            $this->zip($folder, null);
        }
        $this->zip->close();
    }
    
    public function zip($folder, $parent=null) {
        $full_path = $this->root.$parent.$folder;
        $zip_path = $parent.$folder;
        $this->zip->addEmptyDir($zip_path);
        foreach(scandir($full_path) as $file) {
            if(!in_array($file, array('.', '..')) AND !in_array($file, $this->ignored_names)) {
                if(is_dir($full_path.'/'.$file)) $this->zip($file, $zip_path.'/');
                else $this->zip->addFile($full_path.'/'.$file, $zip_path.'/'.$file);
            }
        }
    }
}

function zip_extract($file, $extractPath) {
    if(class_exists('ZipArchive')){
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            return true;
        } else return false;
    } else trigger_error('SYSTEM ERROR: Class "ZipArchive" is not supported, please install zip module.', E_USER_ERROR);
}

function zip_create($file, $folder, $ignored=null){
    if(class_exists('ZipArchive')) {
        if(is_dir($folder)){
            $zip = new ZipFolder($file, $folder, $ignored);
            return true;
        } else {
            $zip = new ZipArchive;
            if($zip->open($file, ZIPARCHIVE::OVERWRITE)===true){
                $zip->addFile($folder, basename($folder));
                $zip->close();
                return true;
            }
        }
        return false;
    } else trigger_error('SYSTEM ERROR: Class "ZipArchive" is not supported, please install zip module.', E_USER_ERROR);
}
?>