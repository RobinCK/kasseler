<?php
if(!defined('FUNC_FILE')) die('Access is limited');
   function editor_smile_conv($text){
      if(hook_check(__FUNCTION__)) return hook();
      $ntext=str_replace('\"','"',$text);
      $ntext=bb($ntext);
      return $ntext;
   }
?>
