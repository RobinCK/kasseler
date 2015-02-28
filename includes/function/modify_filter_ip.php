<?php
if (!defined('KASSELERCMS')) die('Access is limited');
   function button_editor_filter_ip(){
      global $main;
      return "<a class='admino ico_add pixel' title='{$main->lang['add']}'></a>".
      "<a class='admino ico_delete pixel' title='{$main->lang['delete']}'></a>";
   }
   function gen_html_editor_filter_ip($info, $class_input='input_text'){
      global $main;
      $ret="<div id='div_ip_filter' style='max-height:230px;height:auto; width:99%;overflow:auto;'>".
      "<table>";
      if(!empty($info)) $list=explode(',',$info);
      else $list=array();
      if(empty($list)) $list[]='';
      foreach ($list as $key => $value) {
         $ret.="<tr>".
         "<td class=''>".in_text('ip_filter[]', $class_input,$value)."</td>".
         "<td class=''>".button_editor_filter_ip()."</td>".
         "</tr>";      
      }
      $ret.= "</table>".
      "</div>".
      "<div id='allip' style='height:20px;'>&nbsp;</div>";
      return $ret;
   }
   function gen_jscript_editor_filter_ip(){
      global $main;
      main::add_js_function('check_ip');
      main::add_css2head(".bed_filter_ip {border: 1px solid red !important;}");
      $href='index.php?ajaxed=module&do=ip_mask';
      main::add_javascript2body("href_mask='{$href}';\n");
   ?>
   <script type="text/javascript">
      //<![CDATA[
      var check_value='';
      var str_ajax="&nbsp;<img src='includes/images/loading/small.gif' alt='Loading' />";
      function show_all_ip(value){
         if(check_value!=value){
            check_value=value;
            $('#allip').html(str_ajax);
            $.post(href_mask,{info:value},function(data){$('#allip').html(data);});
         }
      }
      $('#div_ip_filter').on("click", ".ico_add",function(){
            var tbl=$(this).parents('table:first');
            var tr=$(this).parents('tr:first');
            tr.clone().appendTo(tbl).find('input').val('');
      }).on("click", ".ico_delete",function(){
            var tbl=$(this).parents('table:first');
            var tr=$(this).parents('tr:first');
            if(tbl.find('tr').length>1) tr.remove();
            else tr.find('input').val('');
      }).on('keyup','input',function(e){
            if(this.value!=""){
               if(check_ip_and_mask(this.value)) {
                  $(this).removeClass('bed_filter_ip');
                  show_all_ip(this.value);
               } else $(this).addClass('bed_filter_ip');
            } else $(this).removeClass('bed_filter_ip');
      }).on('focus','input',function(){show_all_ip(this.value);});
      //]]>
   </script>
   <?php
   }
?>
