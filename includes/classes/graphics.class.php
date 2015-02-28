<?php
/**
* Класс манипуляций над изображениями
* 
* @author Igor Ognichenko
* @author Vitaly Karlyuk 
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/graphics.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

class graphics{
    /**
    * Параметры изображения
    * 
    * @var array
    */
    var $image = array(
        'name' => '',                                   //Файл
        'new_name' => '',                               //Новое имя файла без .тип_файла
        'width' => '',                                  //Ширина миниатюры
        'height' => '',                                 //Высота миниатюры
        'watermark' => 'includes/images/watermark.png', //Путь к водяному знаку
        'watermark_position' => '1',                    //Позиция водяного знака /от 0 до 5
        'directory_image' => '',                        //Каталог с изображением
        'directory_new_image' => ''                     //Каталог для сохранения изображения
    );       
    
    /**
    * Пусть к файлу шрифта
    * 
    * @var mixed
    */
    var $font_file = 'includes/fonts/arial.ttf';
    
    /**
    * Конструктор
    * 
    * @param string $image
    * @return graphics
    */
    function graphics($image){
        $this->set_attribute($image);
    }
    
    /**
    * Функция установки параметров
    * 
    * @param array $image
    * @return void
    */
    function set_attribute($image){
        foreach ($image as $var_name=>$var_value) {
            $this->image[$var_name] = $var_value;
        }
    }
    
    /**
    * Функция возвращает тип файла
    * 
    * @param string $file
    * @return string
    */
    function get_type($file){
        return preg_replace('/(.+?)[.]([a-zA-z0-9]+)$/', '\\2', $file);
    } 
    
    function type2int($type){
        if($type=="jpg" || $type=="jpeg") return 2;
        elseif ($type=="gif") return 1;
        elseif ($type=="png") return 3;
        else return false;
    }   
    
    /**
    * Возвращает тип изображения по его коду
    * 
    * @param int $type_int
    * @return string
    */
    function int2type($type_int){
        if($type_int==2) return "jpg";
        elseif ($type_int==1) return "gif";
        elseif ($type_int==3) return "png";
        else return false;
    }
    
    /**
    * Функция создаёт новое изображение из файла или URL
    * 
    * @param int $type_int
    * @param string $image
    * @return resource
    */
    function create_image($type_int, $image=""){
        $image = ($image=="") ? $this->image['directory_image'].$this->image['name'] : $image;
        if ($type_int==2) return imagecreatefromjpeg($image);
        elseif ($type_int==1) return imagecreatefromgif($image);
        elseif ($type_int==3) return imagecreatefrompng($image);
        else return false;
    }
    
    /**
    * Функция сохраняет изображение в файл
    * 
    * @param int $type_int
    * @param resource  $dest_image
    * @param string $image
    * @return bool
    */
    function save_image($type_int, &$dest_image, $image=""){
        $image = (empty($image)) ? $this->image['directory_new_image']."mini-".$this->image['new_name'].".".get_type_file($this->image['name']) : $image;
        if ($type_int==2) return imagejpeg($dest_image, $image);
        elseif ($type_int==1) return imagegif($dest_image, $image);
        elseif($type_int==3) return imagepng($dest_image, $image);
        else return false;
    }
    
    /**
    * Функция создания миниатюры изображения
    * 
    * @return void
    */
    function resize_image($new_image=""){
        $width = $this->image['width'];
        $height = $this->image['height'];
        $info = getimagesize($this->image['directory_image'].$this->image['name']);
        $ratio = $width/$height;
        $src_ratio = $info[0]/$info[1];    
        if(($info[0]<$width) && ($info[1]<$height)){
            $width=$info[0]; 
            $height=$info[1];
        }
        if ($ratio<$src_ratio) $height=$width/$src_ratio;
        else $width=$height*$src_ratio;
        $dest_image = imagecreatetruecolor($width, $height);
        if(($info[2] == 1)||($info[2] == 3)){
            imagealphablending($dest_image, false);
            imagesavealpha($dest_image,true);
            $tr = imagecolorallocatealpha($dest_image, 255, 255, 255, 127);
            imagefilledrectangle($dest_image, 0, 0, $width, $height, $tr);
        }
        $src_image = $this->create_image($info[2]);
        imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
        $result = $this->save_image($info[2], $dest_image, $new_image);
        imagedestroy($dest_image);
        imagedestroy($src_image);
        return $result;
    }  
    
    /**
    * Функция конвертирования изображения с одного типа в другой
    * 
    * @param string $image
    * @param string $new_image
    * @return void
    */
    function img_convert($image, $new_image){
        $type = $this->get_type($new_image);
        $info = getimagesize($image);
        $src_image = $this->create_image($info[2]);
        $this->save_image($this->type2int(mb_strtolower($type)), $src_image, $new_image);
        imagedestroy($src_image);
    }  
    
    /**
    * Функция накладывания водяного знака
    * 
    * @return void
    */
    function watermark(){
        if($this->image['watermark_position']==0) return false;
        $info_image = getimagesize($this->image['directory_image'].$this->image['name']);
        $info_watermark = getimagesize($this->image['watermark']);
        if ($info_watermark[0]>=$info_image[0] || $info_watermark[1]>=$info_image[1]) return false;
        $watermark_image = $this->create_image($info_watermark[2], $this->image['watermark']);
        $src_image = $this->create_image($info_image[2]);
        $ni = imagecreatetruecolor($info_image[0], $info_image[1]);
        if(($info_image[2] == 1)||($info_image[2] == 3)){
            imagealphablending($ni, false);
            imagesavealpha($ni,true);
            $tr = imagecolorallocatealpha($ni, 255, 255, 255, 127);
            imagefilledrectangle($ni, 0, 0, $info_image[0], $info_image[1], $tr);
        }
            imagecopyresampled($ni, $src_image, 0, 0, 0, 0, $info_image[0], $info_image[1], $info_image[0], $info_image[1]);
            imagealphablending($ni, true);        
        switch ($this->image['watermark_position']){
            case "1": imagecopy($ni, $watermark_image, ($info_image[0]-$info_watermark[0]), ($info_image[1]-$info_watermark[1]), 0, 0, $info_watermark[0], $info_watermark[1]); break;
            case "2": imagecopy($ni, $watermark_image, ($info_image[0]-$info_watermark[0]), 0, 0, 0, $info_watermark[0], $info_watermark[1]); break;
            case "3": imagecopy($ni, $watermark_image, 0, ($info_image[1]-$info_watermark[1]), 0, 0, $info_watermark[0], $info_watermark[1]); break;
            case "4": imagecopy($ni, $watermark_image, 0, 0, 0, 0, $info_watermark[0], $info_watermark[1]); break;
            case "5": imagecopy($ni, $watermark_image, ($info_image[0]-$info_watermark[0])/2, ($info_image[1]-$info_watermark[1])/2, 0, 0, $info_watermark[0], $info_watermark[1]); break;
        }
        $this->save_image($info_image[2], $ni, $this->image['directory_image'].$this->image['name']);
            imagedestroy($ni);
        //header ("Content-type: image/png");
        //imagepng($src_image);
        imagedestroy($src_image);
        imagedestroy($watermark_image);
        return true;
    }    
}
?>