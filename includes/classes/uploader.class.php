<?php
/**
* Класс загрузки файлов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/uploader.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

class upload {
    /**
    * Имя загруженного файла
    * 
    * @var string
    */
    var $file;
    
    /**
    * Флаг о наличии ошибок загрузки
    * 
    * @var mbool
    */
    var $error = false;
    
    /**
    * Перезаписывать файлы с одинаковыми названиями
    * 
    * @var bool
    */
    var $overwrite = false;
    
    /**
    * Уведомление об ошибке
    * 
    * @var string
    */
    var $error_msg;
    
    /**
    * Код ошибки
    * 
    * @var int
    */
    var $error_number;
        
    /**
    * Тип загруженного файла
    *     
    * @var string
    */
    var $expansion;
    
    /**
    * Флаг выполнения загрузки
    * 
    * @var bool
    */
    var $is_upload = false;

    /**
    * Конструктор
    * 
    * @param array $atrib
    * @return upload
    */
    function upload($atrib){
        /*
        $atrib = array(
            'dir'         => 'uploads/',                              //Каталог для загрузки
            'file'        => $_FILES['userfile'],                     //Загружаемый файл
            ['size'       => 1024,]                                   //Максимальный размер загружаемого файла (Kb)
            ['type'       => array('gif', 'png', 'jpg', 'jpeg'),]     //Допустимые разрешения загружаемых файлов
            ['width'      => 800,]                                    //Максимальная ширина загружаемого изображения (px)
            ['height'     => 600,]                                    //Максимальная высота загружаемого изображения (px)
            ['name'       => 'new_file_name',]                        //Новое имя загружаемого файла
            ['overwrite'] => false]                                   //Параметр отвечающий за перезаписывание файлов
        );
        */
        if(!file_exists($atrib['dir'])) {
            mkdir($atrib['dir']);file_write($atrib['dir']."index.html", "", "a");
        }
        if(isset($atrib['overwrite'])) $this->overwrite = $atrib['overwrite'];
        $this->set_error($this->upload_file($atrib));
    }
   /**
   * Проверка файла на закодированость base64
   *  
   * @param mixed $file
   */
   function check_file_64encode($file){
      if(file_exists($file)){
         $cont = file_get_contents($file);
         if (preg_match('%data:([A-z]*/[^;]*)*;base64%im', $cont, $regs)) {
            $cont = base64_decode(substr($cont, strlen($regs[0])));
            $f = fopen($file, 'w');
            fputs ($f, $cont);
            fclose ($f);
            return true;
         }
      }
      return false;
   }

    /**
    * Функция загрузки файла
    * 
    * @param array $atrib
    * @return int
    */
    function upload_file($atrib){
        $atrib['size'] = $atrib['size'] * 1024;
        if(isset($atrib['file'])){            
            //Определяем новое имя файла            
            $this->file = cyr2lat(!isset($atrib['name']) ? $atrib['file']['name'] : $atrib['name'].".".$this->get_expansion($atrib['file']['name']),true);
            //Проверяем имеем ли право перезаписать этот файл
            if(!$this->overwrite AND file_exists($atrib['dir'].$this->file)) return 1;
            if(is_uploaded_file($atrib['file']['tmp_name']) AND isset($atrib['file']["size"])){                
                //Возвращаем тип загруженного файла
                if($this->check_file_64encode($atrib['file']['tmp_name'])) $atrib['file']['size'] = filesize($atrib['file']['tmp_name']);
                $this->expansion = $this->get_expansion($atrib['file']['name']);
                //Проверяем размер загруженного файла
                if(isset($atrib['size']) AND $atrib['file']['size'] > $atrib['size']) return 2;
                elseif(isset($atrib['type']) AND !in_array(mb_strtoupper($this->expansion), $atrib['type']) AND !in_array(mb_strtolower($this->expansion), $atrib['type'])) return 3;                
                //Выполняем копирование файла
                //if(!copy($atrib['file']['tmp_name'], $atrib['dir'].$this->file)) return 5;
                if(!move_uploaded_file($atrib['file']['tmp_name'], $atrib['dir'].$this->file)) return 3;
                //Если загружаем изображение, то проверяем его пиксельную величину
                if(mb_strpos($atrib['file']['type'], 'image')!==false AND isset($atrib['width']) AND isset($atrib['height'])){
                    $size_image = getimagesize($atrib['dir'].$this->file);
                    if($size_image[0] > $atrib['width'] OR $size_image[1] > $atrib['height']){
                        unlink($atrib['dir'].$this->file);
                        return 4;
                    }
                } 
                $this->is_upload = true;
            } else return 5;
        }
        return 0;
    }

    /**
    * Функция определения ошибки
    * 
    * @param int $int
    * @return void
    */
    function set_error($int){
    global $lang;
        $this->error = ($int>0) ? true : false;
        $this->error_number = $int;
        switch($int){
            case 1: $this->error_msg = $lang['upload_file_exists']; break;   //Файл с таким именем уже загружен!
            case 2: $this->error_msg = $lang['upload_file_size']; break;     //Привышен допустимый размер файла!
            case 3: $this->error_msg = $lang['upload_file_type']; break;     //Недопустимый формат файла!
            case 4: $this->error_msg = $lang['upload_file_image']; break;    //Привышен допустимый размер изображения!
            case 5: $this->error_msg = $lang['upload_file_error']; break;    //Ошибка загрузки файла!
        }
    }

    /**
    * Функция возвращает тип загружаемого файла
    * 
    * @param string $file
    * @return string
    */
    function get_expansion($file){
        return preg_replace('/(.+?)[.]([a-zA-Z0-9]+)$/', '\\2', $file);
    }

    /**
    * Функция возвращает код ошибки при загрузки файла
    * 
    * @return int
    */
    function get_error(){
        return $this->error_number;
    }

    /**
    * Функция возвращает текст ошибки при загрузки файла
    * 
    * @return string
    */
    function get_error_msg(){
        return $this->error_msg;
    }
}
?>