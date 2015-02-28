//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2009 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
if (!window.bbeditor) window.bbeditor = {};

bbeditor.extend = function(dest, src, skipexist){
    for (var i in src) if (!skipexist || !dest.hasOwnProperty(i)) dest[i] = src[i];
    return dest;
};

var jl = window.js_lang;
(function($_){$_.extend($_, {
    version:'1.1',
    active_editor:'',
    procent:100,
    editors:[], //id, obj, width, height
    fonts:["Arial", "Comic Sans MS", "Courier New", "Georgia", "Impact", "Sans Serif", "Tahoma", "Times New Roman", "Verdana"],
    buttonHeight:20,
    style:'',
    timeout:function(){},
    classCache:{},
    cachebars:{},
    buttons:{
        "seperator": ['seperator',                           3,  0],
        "b":         [jl.bb_Bold,                20, 0],
        "i":         [jl.bb_Italic,              20, 0],
        "u":         [jl.bb_Underline,           20, 0],
        "s":         [jl.bb_Strikethrough,       20, 0],
        "left":      [jl.bb_Justifyleft,         20, 0],
        "center":    [jl.bb_Justifycenter,       20, 0],
        "right":     [jl.bb_Justifyright,        20, 0],
        "justify":   [jl.bb_JustifyFull,         20, 0],
        "li":        [jl.bb_InsertUnorderedList, 20, 0],
        "blockquote":[jl.bb_Indent,              20, 0],
        "sub":       [jl.bb_Subscript,           20, 0],
        "sup":       [jl.bb_Superscript,         20, 0],
        "hr":        [jl.bb_InsertHorizontalRule,20, 0],
        "font":      [jl.bb_SelectFont,          50, 1],
        "size":      [jl.bb_SelectSize,          50, 1],
        "color":     [jl.bb_ForeColor,           20, 1],
        "smiles":    [jl.bb_Smiles,              20, 1],
        "img":       [jl.bb_InsertImage,         20, 0],
        "link":      [jl.bb_CreateLink,          20, 1],
        "code":      [jl.bb_InsertCode,          20, 0],
        "cite":      [jl.bb_InsertQuote,         20, 0],
        "hide":      [jl.bb_InsertHide,          20, 0],
        "translit":  [jl.bb_Translit,            20, 1],
        "preview":   [jl.bb_Preview,             20, 1],
        "charset":   [jl.bb_CharMap,             20, 0],
        "backcolor": [jl.bb_BackColor,           20, 1],
        "attach":    [jl.bb_Attach,              20, 1],
	    "spoiler":   [jl.bb_Spoiler,             20, 1]
    },
    
    buttonBars:[
        ["b", "i", "u", "s", "seperator", "left", "center", "right", "justify", "seperator", "li", "seperator", "blockquote", "seperator", "sub", "sup", "seperator", "translit", "charset"],
        ["font", "size", "seperator", "color", "backcolor", "seperator", "smiles", "img", "link", "spoiler", "seperator", "code", "cite", "hide", "preview", "hr"]
    ],

    init:function(id, buttons){
       $_.active_editor = id;
       var obj = $('#'+id+':last');
       if(obj.length>0){
          obj=obj.get(0);
          if(obj.style.width.indexOf('%')>-1) {
             $_.procent = obj.style.width.replace('%', '');
          } else {
             $_.procent =100;
             obj.style.width="100%";
          }

          if($(obj).width()>=390) $_.buttonBars=[["b", "i", "u", "s", "seperator", "left", "center", "right", "justify", "seperator", "font", "size", "seperator", "color", "backcolor", "seperator", "smiles", "img", "link", "spoiler", "preview", "seperator", "translit", "charset", "li", "blockquote", "sub", "sup", "seperator", "code", "cite", "hide", "seperator", "hr"], []];

          if($.browser.msie && obj.style.height) obj.style.height = (parseInt(obj.style.height)-2)+'px';
          r = $_.createBars({id:id, obj:obj, width:parseInt($(obj).width()), height:obj.clientHeight ? obj.clientHeight:obj.offsetHeight});
          //$('head').append("<style type='text/css'>"+$_.style+"</style>");
          ////////////
          var style = document.createElement('style');
          style.type = 'text/css';
          if(style.styleSheet) style.styleSheet.cssText = $_.style;
          else if ($.browser.mozilla || $.browser.opera) style.innerHTML = $_.style;
          else style.appendChild(document.createTextNode($_.style));
          document.getElementsByTagName('head')[0].appendChild(style);
          ////////////
          $('#'+id).before(r.b);
          $('#'+id).after(r.a);

          $_.createUniquButtons(id);

          $_.c_sel(id);        
          $('#'+id).focus(function(){
                bbeditor.active_editor = this.id;
                $_.closeall(this.id);
          });
          if($$('resize_'+id)) $$('resize_'+id).onmousedown = function(e){
             var start = $$(id).offsetHeight;
             ps = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
             document.onmousemove = function(e){
                p = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
                $$(id).style.height = start+(p.y-ps.y)+'px';
                hack_sel();
             }            
             document.onmouseup = function(){document.onmousemove = function(){}}
          }
       }
    }, 
    
    c_sel:function(id){
        var size = ['1', '2', '3', '4', '5', '6', '7'];
        var html = '';
        html += '<div id="size_'+id+'" class="dropdown" style="position: absolute; width: 160px; height: 140px;"><table cellpadding="0" cellspacing="0" width="100%">';
        for (var i=0;i<size.length;i++) {
            switch (size[i]){
                case "1": sp = "8"; break;
                case "2": sp = "10"; break;
                case "3": sp = "12"; break;
                case "4": sp = "14"; break;
                case "5": sp = "18"; break;
                case "6": sp = "24"; break;
                case "7": sp = "36"; break;
            }
            html += '<tr><td onclick="bbeditor.insert(\'[size='+sp+']\', \'[/size]\', \''+id+'\');" class="ower_button" onmouseover="this.className=\'hower_button\'" onmouseout="this.className=\'ower_button\'" style="font-size:'+sp+'px; height:'+sp+'px; cursor:pointer;"">'+size[i]+' ('+sp+')</td></tr>';
        }
        html += '</table></div><div id="font_'+id+'" class="dropdown" style="position: absolute; width: 160px; height: 140px;"><table cellpadding="0" cellspacing="0" width="100%">';
        for (var i=0;i<$_.fonts.length;i++) {
            html += '<tr><td onclick="bbeditor.insert(\'[family='+$_.fonts[i]+']\', \'[/family]\', \''+id+'\');" class="ower_button" onmouseover="this.className=\'hower_button\'" onmouseout="this.className=\'ower_button\'" style="font-family: '+$_.fonts[i]+'; cursor:pointer;">'+$_.fonts[i]+'</td></tr>';
        }                
        html += '</table></div>';
        $('#sels_elm_'+id).after(html);
    },
    
    createUniquButtons:function(id){        
        
        showWindow = function(o){
            o = $.extend({buttons: [], description: '', id: '', width:$('#ajax_content').width()-20, xButton:true, onHide:function(){KR_AJAX.animation('hide');}, title: ''}, o);
            guiders.createGuider(o).show();
            var $div = $('body');
            $('.guider').drag("start",function( ev, dd){$(this).appendTo( this.parentNode ); dd.limit = $div.offset(); dd.limit.bottom = dd.limit.top + $div.outerHeight() - $( this ).outerHeight(); dd.limit.right = dd.limit.left + $div.outerWidth() - $( this ).outerWidth();}).drag(function( ev, dd ){$( this ).css({top: Math.min( dd.limit.bottom, Math.max(dd.limit.top, dd.offsetY)),left: Math.min( dd.limit.right, Math.max(dd.limit.left, dd.offsetX))});},{ handle:".guider_title" });
            $('.guider_title').css('cursor', 'move');
        }
        
        $(".click_preview"+id).click(function(){
            haja({action:'index.php?ajaxed=preview', elm:'cont_previews'+id, animation:false}, {'text':$('#'+id).val()}, {
                onstartload:function(){KR_AJAX.animation('show');},
                onendload:function(msg){showWindow({title:'Preview', description:msg});}
            });
            return false;
        });
        
        $(".click_backcolor"+id).click(function(){
            showWindow({buttons:[{name:'OK', onclick:function(){
                bbeditor.insert("[backcolor=#"+$$("cont_frame_backcolor"+id).contentWindow.document.getElementById("plugHEX").innerHTML+"]", "[/backcolor]", id);
                $('.guider').remove();
            }}], width:220, title:'Backcolor case', description:'<iframe id="cont_frame_backcolor'+id+'" style="border: 0; height: 190px;" src="includes/popups/color.html" width="100%"></iframe>'});
            return false;
        });
        
        $(".click_color"+id).click(function(){
            showWindow({buttons:[{name:'OK', onclick:function(){
                bbeditor.insert("[color=#"+$$("cont_frame_backcolor"+id).contentWindow.document.getElementById("plugHEX").innerHTML+"]", "[/color]", id);
                $('.guider').remove();
            }}], width:220, title:'Color case', description:'<iframe id="cont_frame_backcolor'+id+'" style="border: 0; height: 190px;" src="includes/popups/color.html" width="100%"></iframe>'});
            return false;
        });
        
        $(".click_smiles"+id).click(function(){
            if($('#window_smile').length==0) haja({action:'index.php?module=account&do=smiles&id='+id, elm:'cont_previews'+id, animation:false}, {'text':$('#'+id).val()}, {
                onendload:function(msg){showWindow({id:'window_smile', width:400, title:'Select Smile', description:msg});}
            }); else guiders.show('window_smile');
            return false;
        });
        
      $(".click_cite"+id).click(function(){
            var text=(window.getSelection!=undefined) ? window.getSelection() :
            ((document.getSelection!=undefined) ? document.getSelection() : document.selection.createRange().text);
            var author='';
            if(text['anchorNode']!=undefined){
               var r=$(text.anchorNode).parents('tr:first').prev();
               text=text.toString();
               var a=r.find('td:first').find('a:eq(2)').text();
               var d=r.find('td:eq(2)').text();
               var regexp=new RegExp('[^:]*:\x20*([0-9.:]*\x20*[0-9.:]*)', "img");
               var match = regexp.exec(d);
               if(match!=null) author=a+', '+match[1];
               bbeditor.insert("[cite="+author+']'+text, "[/cite]", id);
            } else  bbeditor.insert("[cite]"+text, "[/cite]", id);
            return false;
      });

        if($(".click_translit"+id).length>0) $(".click_translit"+id).get(0).onclick = function(){
            showWindow({id:'window_translit', buttons:[{name:'OK', onclick:function(){
                $_.insert_focus($$("cont_frame_translin"+id).contentWindow.document.getElementById("cyr").value);
                $('.guider').remove();
            }}], width:530, title:'Translit', description:'<iframe id="cont_frame_translin'+id+'" style="border: 0; height: 380px;" src="includes/popups/translit.html" width="100%"></iframe>'});
            return false;
        } 
        
        $(".click_charset"+id).click(function(){
            showWindow({id:'window_charset', buttons:[], width:460, title:'Charset case', description:'<iframe id="cont_frame_chset'+id+'" style="border: 0; height: 370px;" src="includes/popups/charset.html" width="100%"></iframe>'});
            check_char = setInterval(function(){
                v = $$("cont_frame_chset"+id).contentWindow.document.getElementById('charset_string').value;
                if(v!='') {
                    $_.insert_focus(v);
                    $('#window_charset').remove();
                    clearInterval(check_char);
                }
            }, 500);
            return false;
        });
                
        $(".click_link"+id).click(function(){
            showWindow({id:'window_link', buttons:[{name:'OK', onclick:function(){
                var url = $$("cont_frame_hylink"+id).contentWindow.document.getElementById('url').value;
                url = url.replace('http//', '').replace('ftp://', '').replace('https://', '').replace('http://', '').replace('mailto:', '');
                if(url) $_.insert('[url=' + $$("cont_frame_hylink"+id).contentWindow.document.getElementById('linkType').value + url + ']' + $$("cont_frame_hylink"+id).contentWindow.document.getElementById('titlelink').value + '[/url]', '', id);
                $('.guider').remove();
            }}], width:310, title:'Link', description:'<iframe id="cont_frame_hylink'+id+'" style="border: 0; height: 120px;" src="includes/popups/hyperlink.html" width="100%"></iframe>'});
            return false;
        });
        
        $(".click_img"+id).click(function(){
            showWindow({id:'window_img', buttons:[{name:'OK', onclick:function(){
                var url = $$("cont_frame_image"+id).contentWindow.document.getElementById('img_url').value;
                if (url) $_.insert('[img='+$$("cont_frame_image"+id).contentWindow.document.getElementById('img_align').value+' alt='+$$("cont_frame_image"+id).contentWindow.document.getElementById('img_title').value+']'+url+'[/img]', '', id);
                $('.guider').remove();
            }}], width:310, title:'Link', description:'<iframe id="cont_frame_image'+id+'" style="border: 0; height: 100px;" src="includes/popups/image.html" width="100%"></iframe>'});
            return false;
        });
        
        $(".click_size"+id).click(function(){
            $('#size_'+id).css($(".click_size"+id).offset());
            $$('size_'+id).style.top = parseInt($$('size_'+id).style.top)+21+'px';
            $_.show_sel($$('size_'+id), id);
            return false;
        });
        
        $(".click_font"+id).click(function(){
            $('#font_'+id).css($(".click_font"+id).offset());
            $$('font_'+id).style.top = parseInt($$('font_'+id).style.top)+21+'px';
            $_.show_sel($$('font_'+id), id);
            return false;
        });
    },

    show_sel:function(e, id){        
        if($(e).is(':visible')) $(e).hide();
        else {
            $_.closeall(id);
            $(e).show();
        }
    },
    
    insert_focus:function(st){
        var e=$$(bbeditor.active_editor);
        var nid='#'+e.id;
        if($(nid).filter(':visible').length==0){
           for(i=0;i<bbeditor.editors.length;i++){if($('#'+bbeditor.editors[i].id).is(':visible')){var nm='#'+bbeditor.editors[i].id;$(nm).val($(nm).val()+st);return true;}}
        }
        if(typeof document.selection != 'undefined'){
            if(document.selection.type=='Text'){
               var range = document.selection.createRange().duplicate();
               range.text = st;
               range = document.selection.createRange();
               range.select();
            } else {
               var scrollPos = e.scrollTop; 
               var strPos = 0; 
               $(nid).focus(); 
               var range = document.selection.createRange(); 
               var val=$(nid).val();
               range.moveStart ('character', -val.length); 
               strPos = range.text.length;
               var front = val.substring(0,strPos); 
               var back = val.substring(strPos,val.length); 
               e.value=front+st+back; strPos = strPos + st.length; 
               e.focus(); 
               var range = document.selection.createRange(); 
               range.moveStart ('character', -e.value.length); 
               range.moveStart ('character', strPos); 
               range.moveEnd ('character', 0); range.select(); 
            }
        } else if(typeof e.selectionStart != 'undefined'){
            var start = e.selectionStart;
            var end   = e.selectionEnd;
            var scroll = e.scrollTop;
            var caret  = e.value.substr(0, start) + st + e.value.substr(end);
            e.value = caret;
            e.selectionStart = start+st.length;
            e.selectionEnd = end+st.length;
            e.scrollTop = scroll;
        } else e.value += st;
        setTimeout(function(){e.focus();}, 100);
        clearInterval($_.timeout);
    },
    
    createBars:function(e){
        if($_.cachebars[e.id]) return false;
        
        $_.editors.push(e);
        b = '<div style="background: url(includes/images/editor/background_silver.jpg); width:'+$_.procent+'%;" class="editorBars" id="topmenu_'+e.id+'">'+$_.createButtons(e, $_.buttonBars[0], 'top')+'</div>';
        a = $_.buttonBars[1].length>0 ? '<div class="resizeBar" id="resize_'+e.id+'" style="width: '+$_.procent+'%"><img src="includes/images/pixel.gif" alt="" /></div><div style="background: url(includes/images/editor/background_silver.jpg); width:'+$_.procent+'%;" class="editorBars" id="bottommenu_'+e.id+'">'+$_.createButtons(e, $_.buttonBars[1], 'bottom')+'</div><span id="sels_elm_'+e.id+'"></span>' :
                                        '<div class="resizeBar" id="resize_'+e.id+'" style="width: '+$_.procent+'%"><img src="includes/images/pixel.gif" alt="" /></div><span id="sels_elm_'+e.id+'"></span>'
        $_.cachebars[e.id] = true;
        return {a:a, b:b};
    },
    
    createButtons:function(e, arrB, pos){
        if(arrB.length==0) return false;
        var elms = {id:0, sum:8};
        var sum = 8;
        for(var i=0; i<arrB.length;i++) sum += $_.buttons[arrB[i]][1];
        for(var i=0; i<arrB.length;i++){
            if(elms.sum+$_.buttons[arrB[i]][1]<e.width-$_.buttons[arrB[i]][1]){
                elms.id = i;
                elms.sum += $_.buttons[arrB[i]][1]-0.4;
            } else break;
        }
        var other_width = sum-elms.sum, buttons = '', buttons2 = '', lastImg = '';
        var seperator = "<td><img src='includes/images/editor/seperator.png' alt='' style='margin: px;'></td>";
        for(var i=0; i<=elms.id; i++){
            if(arrB[i]!="seperator"){
                $_.style += '.click_'+arrB[i]+e.id+' {background: transparent url(includes/images/editor/'+arrB[i]+'.png) top left repeat-x;} .click_'+arrB[i]+e.id+':hover {background: url(includes/images/editor/'+arrB[i]+'.png) bottom left repeat-x;} ';
                onclicks = "bbeditor.insert('["+arrB[i]+"]', '[/"+arrB[i]+"]', '"+e.id+"');";
                buttons += '<td><a href="javascript:'+onclicks+'" style="width: '+$_.buttons[arrB[i]][1]+'px; height: '+$_.buttonHeight+'px; display: block; float: left;" title="'+$_.buttons[arrB[i]][0]+'" class="editor_click click_'+arrB[i]+e.id+'"></a></td>';
                lastImg = arrB[i];
            } else {
                buttons += seperator;
                lastImg = "seperator";
            }
        }                      
        for(i=elms.id+1; i<=arrB.length; i++){
            if(!$_.buttons[arrB[i]]) break;
            if(arrB[i]!="seperator"){
                $_.style += '.click_'+arrB[i]+e.id+' {background: url(includes/images/editor/'+arrB[i]+'.png) top left repeat-x;} .click_'+arrB[i]+e.id+':hover {background: url(includes/images/editor/'+arrB[i]+'.png) bottom left repeat-x;} ';
                onclicks = "bbeditor.insert('["+arrB[i]+"]', '[/"+arrB[i]+"]', '"+e.id+"'); ";
                buttons2 += '<td><a href="javascript:'+onclicks+'" style="width: '+$_.buttons[arrB[i]][1]+'px; height: '+$_.buttonHeight+'px; display: block; float: left;" title="'+$_.buttons[arrB[i]][0]+'" class="editor_click click_'+arrB[i]+e.id+'"></a></td>';
                lastImg = arrB[i];
            } else if(i!=elms.id+1){  
                buttons2 += seperator;
                lastImg = "seperator";
            }
        }  
        if(elms.id<arrB.length-1){
            $_.style += '.click_other'+e.id+' {background: url(includes/images/editor/other.png) top left repeat-x;} .click_other'+e.id+':hover {background: url(includes/images/editor/other.png) bottom left repeat-x;} ';
            if(lastImg!="seperator") buttons += seperator;
            buttons += '<td><a onclick="bbeditor.show_other_button(this, \''+"other_buttons_"+pos+"_"+e.id+'\', '+other_width+'); return false;" href="#" style="width: 9px; height: '+$_.buttonHeight+'px; display: block; float: left;" title="" class="editor_click click_other'+e.id+'"></a></td>';
        }
        return "<div style='height: "+($_.buttonHeight+2)+"px; display: block; width: "+$_.procent+"%'><span style='clear: left'><table cellpadding='0' cellspacing='0'><tr><td><img src='includes/images/editor/seperator2.png' alt=''></td>"+buttons+"</tr></table></span><span style='border:0px; display: none; position: absolute;' id='other_buttons_"+pos+"_"+e.id+"' class='other_bar'><table cellpadding='0' cellspacing='0' style='background: url(includes/images/editor/background_silver.jpg);' cellpadding='0' cellspacing='0'><tr>"+buttons2+"</tr></table></span>";
    },
    
    show_other_button:function(obj, id, width){
        if($$(id).style.display!='block') $$(id).style.display = 'block';
        else $$(id).style.display = 'none';
        $('#'+id).css(elmPos(obj));
        $$(id).style.top = parseInt($$(id).style.top)+21+'px';
        $$(id).style.left = parseInt($$(id).style.left)-width+'px';        
    },              
    
    insert:function(open, close, id){
        if(open=='[hr]'){close = '';}
        if(id) e = $$(id);
        else e = $$($_.active_editor);
        if(!e) return false;
        e.focus();
        if(typeof document.selection != 'undefined'){
            var range = document.selection.createRange();
            range.text = open+range.text+close;
            range = document.selection.createRange();
            range.select();
        } else if(typeof e.selectionStart != 'undefined'){
            var start = e.selectionStart;
            var end   = e.selectionEnd;
            var scroll = e.scrollTop;
            var caret  = e.value.substr(0, start) + open + e.value.substring(start, end) + close + e.value.substr(end);
            e.value = caret;
            e.selectionStart = start;
            e.selectionEnd = end+open.length+close.length;
            e.scrollTop = scroll;
        } else e.value += open + close;
        setTimeout(function(){e.focus();}, 100);
        $_.closeall(id);
    },
    
    closeall:function(id){
        var elms = ['font_', 'size_', 'other_buttons_top_', 'other_buttons_bottom_'];
        for(var i=0;i<elms.length;i++) $('#'+elms[i]+id).hide();
    }
})
})(bbeditor)