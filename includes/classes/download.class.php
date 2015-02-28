<?php
/**
* Класс скачивание файлов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/download.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

class file_download {
    /**
    * Идентификатор возможности докачки файлов
    * 
    * @var bool
    */
    var $range;
    
    /**
    * Файловые параметры
    * 
    * @var array
    */
    var $file = array(
        'size'   => '',
        'name'   => '',
        'type'   => '',
        'resume' => '',
        'speed'  => ''
    );
    
    /**
    * Функция определения параметров для скачивания файлов
    * 
    * @param string $file
    * @param int $resume
    * @param int $speed
    * @return void
    */
    function file_download($file, $resume=0, $speed=0, $name=''){
        if(is_dir($file)) kr_http_ereor_logs("403"); elseif (!is_file($file)) kr_http_ereor_logs("404");
        $fileinfo = pathinfo($file);
        $this->file = array( 
            'ptch'   => $file,
            'size'   => filesize($file),
            'name'   => strstr(get_user_agent(), 'MSIE') ? preg_replace('/\./', '%2e', (!empty($name)?$name:$fileinfo['basename']), mb_substr_count((!empty($name)?$name:$fileinfo['basename']), '.') - 1) : (!empty($name)?$name:$fileinfo['basename']),
            'type'   => isset($fileinfo['extension']) ? mb_strtolower($fileinfo['extension']) : "",
            'resume' => $resume,
            'speed'  => $speed
        );     
        if($this->file['resume']){
            //Если разрешена докачка
            if(isset($_SERVER['HTTP_RANGE'])) $this->range = str_replace("-", "", str_replace("bytes=", "", $_SERVER['HTTP_RANGE']));
            else $this->range = 0;
        } else $this->range = 0;
    }
    
    /**
    * Функция скачивания файлов
    * 
    * @return bool
    */
    function download(){
    global $MIME;        
        //Если разрешена докачка
        if($this->range) header($_SERVER['SERVER_PROTOCOL']." 206 Partial Content");
        else header($_SERVER['SERVER_PROTOCOL']." 200 OK");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: ".(isset($MIME[$this->file['type']]) ? $MIME[$this->file['type']] : 'application/force-download'));
        header('Content-Disposition: attachment; filename="'.$this->file['name'].'";');
        header("Content-Transfer-Encoding: binary");        
        if($this->file['resume']) header("Accept-Ranges: bytes");
        if($this->range){
            //Если разрешена докачка
            header("Content-Range: bytes {$this->range}-".($this->file['size']-1)."/".$this->file['size']);
            header("Content-Length: ".($this->file['size']-$this->range));
        } else header("Content-Length: ".$this->file['size']);
        if(($speed = $this->file['speed']) > 0) $sleep_time = (8 / $speed) * 1e6;
        else $sleep_time = 0;
        /*[X]*/
        if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit(0);
        else @ini_set('max_execution_time', 0);
        //Открываем файл для чтения
        $handle = fopen($this->file['ptch'], 'rb');
        fseek($handle,$this->range);
        if($handle === false) return false;
        //Читаем файл с заданной скоростью скачивания
        if(@ob_get_length()>0) @ob_end_clean();
        while(!feof($handle)){
            print(fread($handle, 1024*8));
            if(function_exists('ob_flush') AND function_exists('flush') AND @ob_get_length()>0){
                ob_flush();
                flush();
            }
            usleep($sleep_time);
        } 
        fclose($handle);
        return true;
    }
}
?>