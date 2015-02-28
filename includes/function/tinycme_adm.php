<?php
if (!defined('FUNC_FILE')) die('Access is limited');
   function all_js_config($plagins){
      if(hook_check(__FUNCTION__)) return hook();
      $directory = "includes/javascript/editors/tiny_mce/plugins/";
      $ret=array();
      foreach($plagins as $js){
         $fname=$directory.$js."/editor_plugin.js";
         if(file_exists($fname)){
            $cont=file_get_contents($fname);
            $author="";
            $longname="";
            if (preg_match('/(?i)(getInfo\x20*:[^{]*\{[^{]*\{[^}]*\})/s', $cont, $regs)) {
               $rst = $regs[1];
               if (preg_match('/(?i)longname[^\'"]*[\'"]*([^\'"]*)/s', $rst, $regs)) $longname = $regs[1];
               if (preg_match('/(?i)author[^\'"]*[\'"]*([^\'"]*)/s', $rst, $regs)) $author = $regs[1];
            }
            $ret[$js]=array('plugin'=>$js,'name'=>$longname,'author'=>$author);
         }
      }
      return $ret;
   }
   function all_mce_plugin(){
      if(hook_check(__FUNCTION__)) return hook();
      $directory = "includes/javascript/editors/tiny_mce/plugins/";
      $js = glob($directory . "*");
      $ret=array();
      foreach($js as $jsf){
         if (preg_match('%(?i)/([^/]*$)%s', $jsf,$regs)) {
            $ret[$regs[1]]=$regs[1];
         }
      }
      return $ret;
   }

   function mce_scan_plugin_buttons($plagins){
      if(hook_check(__FUNCTION__)) return hook();
      $directory = "includes/javascript/editors/tiny_mce/plugins/";
      $btns=array();
      foreach($plagins as $plg){
         $fname=$directory.$plg."/editor_plugin.js";
         if(file_exists($fname)){
            $cont=file_get_contents($fname);
            preg_match_all('/(?i).addButton\(["\']*([^"\']*)[^,]*,[^,]*,[\r\n\x20\x09]*cmd\x20*:\x20*["\']*([^"\']*)/s', $cont, $regs, PREG_PATTERN_ORDER);
            for ($i = 0; $i < count($regs[0]); $i++) {$btns[]=array($regs[1][$i],$regs[2][$i],$plg);}
         }
      }
      return $btns;
   }

   function post_filename(){
      $filename=$_POST['filename'];
      $drs = explode('/', $filename);
      $filename=array_pop($drs);
      return "includes/config/{$filename}";
   }

   function editor_tiny_mce(){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('buttons');
      if(count($_POST)==0){
         echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=editor_tiny_mce' method='post'>".
         in_hide('filename',"").
         "<table align='center' class='form' id='form_{$main->module}'>".    
         "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_small']}</b>:</td><td class='form_input2'>".button_d('edit',"select_file('tinymce_small.js');")."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_medium']}</b>:</td><td class='form_input2'>".button_d('edit',"select_file('tinymce_medium.js');")."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_big']}</b>:</td><td class='form_input2'>".button_d('edit',"select_file('tinymce_big.js');")."</td></tr>\n".
         //"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_custom']}</b>:</td><td class='form_input2'>".button_d('edit',"select_file();")."</td></tr>\n".
         "</table></form>";
      ?>
      <script type="text/javascript">
         //<![CDATA[
         function select_file(name){
            if(name!=undefined){
               $('#filename').val(name);
               $('#block_form').submit();
            }
         }
         //]]>
      </script>
      <?php
      } else {
         main::add2script("includes/javascript/jquery/jquery-ui.min.js");
         $plugins=all_mce_plugin();
         global $config;
         $config['xhtmleditor_g']=$main->user['user_group'];
         $fname=post_filename();
         $set_plugins=array();
         $set_buttons=array();
         if(file_exists($fname)){
            $srcfile=file_get_contents($fname);
            preg_match_all('/(?i)plugins[^"\']*.([^"\']*)/s', $srcfile, $result, PREG_PATTERN_ORDER);
            $set_plugins=explode(',',$result[1][0]);
            preg_match_all('/(?i)theme_advanced_buttons([0-9]*)[^"\']*.([^"\']*)/s', $srcfile, $result, PREG_PATTERN_ORDER);
            foreach ($result[0] as $key => $value) {
               $set_buttons[$result[1][$key]]=explode(',',$result[2][$key]);
            }
         }
         main::add_css2head("
            div.btn{height:200px;overflow:auto;}
            .btn li,.abutton li{padding-left: 22px;}
            div.abutton{height:600px;overflow:auto;}
            .btn .head {margin-top: 1px; margin-bottom: 2px;}
            ul.button{padding: 5px;background: none repeat scroll 0 0 #EEEEEE;}
            .drag{cursor: move;background-color: #F6F6F6; border: 1px solid #CCCCCC; color: #1C94C4;height: 20px;}
            .title_panel {text-align: center; font-weight: bold; cursor:pointer;}
         ");
         echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_tiny_mce' method='post'>".
         in_hide('filename',$_POST['filename']).
         "<table align='center' class='form' id='form_{$main->module}'>".    
         "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['tinymce_plagin']}</b>:<br /><i>{$main->lang['tinymce_plagin_d']}</i></td><td class='form_input'>".in_sels_plagin('plugins',$plugins,'select chzn-none',$set_plugins)."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text2' rowspan='3'>".
         "<div class='title_panel initbtn'>{$main->lang['panel_mce_all']}</div><div class='abutton' style=''><ul class='button drop' id='buttons'><li class='drag' value='0'>{$main->lang['separator_mce']}</li></ul></div>"."</td><td class='form_input'>".
         "<div class='title_panel'>{$main->lang['panel_mce_1']}</div><div class='btn'><ul class='button drop' id='panel1'><li class='empty'> </li></ul></div>"."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text2'>".
         "<div class='title_panel'>{$main->lang['panel_mce_2']}</div><div class='btn'><ul class='button drop' id='panel2'><li class='empty'> </li></ul></div>"."</td></tr>\n".
         "<tr class='row_tr'><td class='form_input'>".
         "<div class='title_panel'>{$main->lang['panel_mce_3']}</div><div class='btn'><ul class='button drop' id='panel3'><li class='empty'> </li></ul></div>"."</td></tr>\n".
         "<tr><td class='form_submit' colspan='2' align='center'><input class='submit' type='image' src='".TEMPLATE_PATH."{$main->tpl}/images/done.png' onclick='return before_post();' alt='{$main->lang['send']}' /></td></tr>\n".
         "<tr><td class='form_submit' colspan='2' align='center'>".button_d('preview',"reinit_editor();")."</td></tr>\n".
         "<tr class='row_tr'><td colspan='2' class='form_input'>".editor('custom',10,5,"",0,"{theme_advanced_buttons1:'',theme_advanced_buttons2:'',theme_advanced_buttons3:'',theme_advanced_buttons4:'',theme_advanced_buttons5:''}",false)."</td></tr>\n".
         "</table></form>";
         main::add2script("if(tinyMCEvar==undefined) {var tinyMCEvar={};}\n 
            tinyMCEvar.plugins='".implode(',',$plugins)."';
            ", false);
      ?>
      <script type="text/javascript">
         //<![CDATA[
         <?php echo "btnset=".json_encode($set_buttons).";\n";   
            echo "btn_info=".json_encode(mce_scan_plugin_buttons($plugins)).";\n";
         ?>
         var stbuttons= {
            bold : 'bold_desc',
            italic : 'italic_desc',
            underline : 'underline_desc',
            strikethrough : 'striketrough_desc',
            justifyleft : 'justifyleft_desc',
            justifycenter : 'justifycenter_desc',
            justifyright : 'justifyright_desc',
            justifyfull : 'justifyfull_desc',
            forecolor : 'forecolor_desc',
            backcolor : 'backcolor_desc',
            bullist : 'bullist_desc',
            numlist : 'numlist_desc',
            outdent : 'outdent_desc',
            indent : 'indent_desc',
            cut : 'cut_desc',
            copy : 'copy_desc',
            paste : 'paste_desc',
            undo : 'undo_desc',
            redo : 'redo_desc',
            link : 'link_desc',
            unlink : 'unlink_desc',
            image : 'image_desc',
            cleanup : 'cleanup_desc',
            help : 'help_desc',
            code : 'code_desc',
            hr : 'hr_desc',
            removeformat : 'removeformat_desc',
            sub : 'sub_desc',
            sup : 'sup_desc',
            forecolorpicker : 'forecolor_desc',
            backcolorpicker : 'backcolor_desc',
            charmap : 'charmap_desc',
            visualaid : 'visualaid_desc',
            anchor : 'anchor_desc',
            newdocument : 'newdocument_desc',
         };
         var ilang='';
         var_inited('tinyMCE',function(){
               tinyMCEvar.oninit=function(){final_load_editor();}
               init_tiny_mce({theme_advanced_buttons1:'',theme_advanced_buttons2:'',theme_advanced_buttons3:'',theme_advanced_buttons4:'',theme_advanced_buttons5:''});
         });
         var buttons=[];
         var n=0;
         buttons[n]='|';
         var b=$('#buttons');;
         var separator_name;
         var max_height=0;
         function getLang(varlang){
            var l=varlang.search(/\./)!=-1?ilang+'.'+varlang:ilang+'.advanced.'+varlang;;
            return tinymce.i18n[l];
         }
         function before_post(){
            fix_plagin_select_button();
            $('.btn').each(function(){
                  var a=[];
                  $(this).find('li').each(function(i){a.push(buttons[this.value]);})
                  $('<input/>').attr('name',$(this).find('ul').attr('id')).attr('type','hidden').val($.toJSON(a)).appendTo($('#block_form'));
            });
            return true;
         }
         function find_btn_cmd(cmd){
            for(var i=0;i<btn_info.length;i++){if(btn_info[i][1].toLowerCase()==cmd.toLowerCase()) return i;}
            return -1;
         }

         function exists_button(nameb){
            for(var k=0;k<buttons.length;k++){
               if(buttons[k]==nameb) return true;
            }
            return false;
         }
         function add_separator(){
            return sp=$('<li/>').val(0).addClass('drag').text(separator_name);
         }
         function fix_plagin_select_button(){
            var pls=$('#plugins');
            $('.btn').each(function(){
                  var a='';i++;
                  $(this).find('li').each(function(i){
                        var mreg = /[^:]*:\x20([\s\S]*)/;
                        var match = mreg.exec($(this).attr('title'));
                        if (match != null) {
                           var op=pls.find('option[value=\''+match[1]+'\']');
                           $(op).attr('selected',true);
                        }
                  })
            });
         }
         function add_separator_all(){
            var l=b.find('li:[value=0]').length;
            if(l==0){
               var sp=add_separator();
               if(b.find('li:first').length==0) sp.appendTo(b);
               else sp.insertBefore(b.find('li:first'));
            } else if(l>1) b.find('li:[value=0]').not(':first').remove();
         }
         b.parents('table:first').find("li.empty").remove();
         function final_load_editor(){
            ilang=tinymce.settings.language;
            for(i in stbuttons){
               if(!exists_button(i)){
                  n++;buttons[n]=i;
                  $('<li/>').addClass('kse_'+i).val(n).attr('id',i).attr('title','Standart button').addClass('drag').text(getLang(stbuttons[i])).appendTo(b);
               }
            }
            for(i in tinyMCE.activeEditor.buttons){
               if(!exists_button(i)){
                  n++;buttons[n]=i;
                  var btn=tinyMCE.activeEditor.buttons[i];
                  var text=(btn.scope)?btn.title:getLang(btn.title);
                  var title='';
                  if(btn.cmd!=undefined){
                     var ti=find_btn_cmd(btn.cmd);
                     if(ti>=0) title="plugin: "+btn_info[ti][2];
                     else {
                        if(btn.title.search(/\./)!=-1) {
                           var myregexp = /([^.]*)\./;
                           var match = myregexp.exec(btn.title);
                           title="plugin: "+match[1];
                        }
                     }
                  }
                  $('<li/>').addClass('kse_'+i).val(n).attr('id',i).attr('title',title).addClass('drag').text(text).appendTo(b);
               }
            }
            separator_name=b.find('li:[value=0]').text();
            for(i in btnset){
               if(btnset[i].length>0){
                  var pnl=$('#panel'+i);
                  for(k=0;k<btnset[i].length;k++){
                     if(btnset[i][k]!="") {
                        if(btnset[i][k]=='|') {var sp=add_separator();sp.appendTo(pnl)}
                        else b.find('#'+btnset[i][k]).appendTo(pnl);
                     }
                  }
               }
            }
            sort();
            b.parents('table:first').find("ul").sortable({
                  connectWith: "ul",
                  stop: function(event, ui){if(ui.item[0].value=='0') add_separator_all()}
            });
         }
         $('.title_panel:not(.initbtn)').next().each(function(){max_height+=parseInt($(this).css('height'));})
         $('.title_panel:not(.initbtn)').bind('click',function(){
               var found_zero=false;
               $('.title_panel:not(.initbtn)').each(function(){
                     if($(this).next().css('height')=='0px') found_zero=true;
               })
               if(found_zero){
                  $('.title_panel:not(.initbtn)').next().css({'height':'','max-height':''});
               } else {
                  $('.title_panel:not(.initbtn)').not(this).next().css('height','0px');
                  $(this).next().css({'height':'100%','max-height':(max_height+'px')});
               }
         });
         function sort(){
            var lar=new Array();
            var i = 0;
            $("ul#buttons li").each(function() {
                  var li=$("ul#buttons li:eq(" + i + ")");
                  lar.push([li.text(),li]);
                  i++;
            });
            lar.sort(function(a, b){if (a[0] > b[0]) return 1;else if (a[0] < b[0]) return -1;else  return 0;})
            for(var i=1;i<lar.length;i++){
               var obj=lar[i][1];
               var obja=lar[i-1][1];
               obj.insertAfter(obja);
            }
            return true;
         }
         function reinit_editor(){
            //tinyMCE.activeEditor.remove();
            tinymce.execCommand('mceToggleEditor',false,'custom')
            fix_plagin_select_button();
            tinyMCEvar={};
            var opt={language:ilang};
            var tbn='theme_advanced_buttons';
            for(var i=1;i<=5;i++) opt[tbn+i]='';
            var i=0;
            $('.btn').each(function(){
                  var a='';i++;
                  $(this).find('li').each(function(i){a+=','+buttons[this.value];})
                  if(a.length>0) a=a.substring(1);
                  opt[tbn+i]=a;
            });
            opt.plugins=$('#plugins').val().join(",");
            init_tiny_mce(opt);
         }
         $('.initbtn').bind('click',sort);
         //]]>
      </script>
      <?php     
      }
   }
   function save_tiny_mce(){
      if(hook_check(__FUNCTION__)) return hook();
      $jsn=array();
      for($i=1;$i<=5;$i++){
         if(isset($_POST["panel{$i}"])){
            $panel=$_POST["panel{$i}"];
            $panel=str_replace('\"','"',$panel);
            $jsn[$i]=implode(',',json_decode($panel));
         } else $jsn[$i]='';
      }
      if(isset($_POST['plugins'])) $plugins=implode(',',$_POST['plugins']);
      else $plugins='';
      $mce_config="var tinyMCEvar={
      plugins : '{$plugins}',
      theme_advanced_buttons1 : '{$jsn[1]}',
      theme_advanced_buttons2 : '{$jsn[2]}',
      theme_advanced_buttons3 : '{$jsn[3]}',
      theme_advanced_buttons4 : '{$jsn[4]}',
      theme_advanced_buttons5 : '{$jsn[5]}',
      }";
      $filename=$_POST['filename'];
      $drs = explode('/', $filename);
      $filename=array_pop($drs);
      $file_link = "includes/config/{$filename}";
      if(file_exists($file_link)){
         if(is_writable($file_link)){
            $file = fopen($file_link, "w");
            fputs ($file, $mce_config);
            fclose ($file);
         }
      } else {
         $file = fopen($file_link, "w");
         fputs ($file, $mce_config);
         fclose ($file);
      }
      redirect(MODULE);
   }

   function in_sels_plagin($name, $options, $class, $value="", $size=0){        
      if(hook_check(__FUNCTION__)) return hook();
      $id=conv_name_to_id($name);
      $infop=all_js_config($options);
      $multiple=true;
      $is = is_array($value); $select = ""; $value = (isset($_POST[$name])) ? $_POST[$name] : $value; 
      $sel = "<select".($id!=""?" id='{$id}'":"")." name='{$name}".($is?'[]':'')."' class='{$class}'".(($is AND $multiple!=false)?" multiple='multiple'":'')."".(($is OR $size!=0)?" size='".($size==0?8:$size)."'":'').">\n";    
      foreach($options as $key=>$val){        
         $match = "";        
         if(preg_match('/(.+?)_optgroup_(.*)/i', $key, $match)){
            if($match[1]=='begin') $sel .= "<optgroup label='{$val}'>\n";
            else $sel .= "</optgroup>\n";
            continue;
         } else {
            if(!$is) $select = ($value==$key) ? " style='font-weight: bold;' selected='selected'" : "";
            else $select = (is_array($value) AND (in_array($key, $value) OR isset($value[$key]))) ? " style='font-weight: bold;' selected='selected'" : "";
            $title=isset($infop[$key])?"{$infop[$key]['name']},  Author: {$infop[$key]['author']}":"";
            $sel .=  "<option value='{$key}'{$select} title='{$title}'>{$val}</option>\n";
         }
      }
      return $sel."</select>";
   }
   main::init_class('fb');
?>
