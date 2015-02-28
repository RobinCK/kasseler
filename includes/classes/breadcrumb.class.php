<?php
   /** $bread_crumb_array[]=array('caption'=>...,'href'=>....) **/
   global $tpl_create,$category_bread_crumb,$bread_crumb_array;
   class bcrumb{
      /**
      * класс генерации "хлебных крошек"
      * 
      */
      function bcrumb(){
      }
      /**
      * Добавление элемента в "хлебные крошки"
      * 
      * @param string $caption описание
      * @param string $href ссылка
      * @return mixed
      */
      public static function &add($caption, $href='', $title=''){
         global $bread_crumb_array;
         if(hook_check(__FUNCTION__)) return hook();
         $ret=array();
         if(!empty($caption)){
            $ret['caption']=$caption;
            $ret['href']=$href;
            if(!empty($title)) $ret['title']=$title;
            $bread_crumb_array[]=&$ret;
         }
         return $ret;
      }
      /**
      * Вывод информации "хлебных крошек"
      *    
      * @param array $arr масиив значений по типу $bread_crumb_array
      * @param boolean $remove_last удалять последний элемент массива?
      * @return mixed
      */
      public static function bread_crumb($arr=array(),$remove_last=false){
         global $bread_crumb_array,$tpl_create, $config, $main;
         if(hook_check(__FUNCTION__)) return hook();
         if(in_array($main->module,explode(',',$config['module_br']))){
            if(count($arr)==0) $arr=$bread_crumb_array;
            $ret="";
            if($remove_last&&count($arr)!=0) $arr=array_slice($arr, 0, -1);
            if(count($arr)!=0){
               main::add2script("includes/javascript/jquery/jquery.bread.crumb.js");
               foreach ($arr as $key => $value){
                  if($value['caption']!=""){
                     $caption=$value['caption'];
                     $tag=!empty($value['href'])?"a":"span";
                     $href=(!empty($value['href']))?" href='{$value['href']}'":"";
                     $title=isset($value['title'])?" title='{$value['title']}'":"";
                     $id=!empty($value['id'])?" id='{$value['id']}'":"";
                     if($ret!="") $ret.="<span class='bcraquo'>&raquo;</span> ";
                     $ret.="<div class='buttonbc'><span class='label'><{$tag}{$id}{$href}{$title} style='white-space: nowrap;'>{$caption}</{$tag}></span><span class='arrow'><span></span></span></div>";
                  }
               }
            }
            $stylec=$config['method_br']==0?" style='white-space: nowrap;'":" style='white-space: normal;'";
            return $ret!=""?"<div class='srcbreadcumb'><div class='srccrumbs'{$stylec}>{$ret}</div></div>":"";//<span>&nbsp;</span>
         } else return "";
      }
   }
   if(isset($bread_crumb_array)) $bread_crumb_array=array();
?>
