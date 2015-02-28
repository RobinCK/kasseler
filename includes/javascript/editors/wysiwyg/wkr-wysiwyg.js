//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2013 by Dmitrey Browko      //
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
"use strict";
var wkr_lang = {};
function wkr_init_lang(newlang){
   var i;
   for (i in newlang){if(typeof i ==='string') {wkr_lang[i] = newlang[i];}}
   $('.btn-toolbar').find('.btn').each(function(){
      var bn = $(this).attr('data-button');
      if(isset($.wkr_editor.defaults.buttons[bn])){
         var b = $.wkr_editor.defaults.buttons[bn];
         if(isset(wkr_lang[b.title])) this.title = wkr_lang[b.title];
      }
   })
}
function wkr_lang_html(html){
   var myregexp = /\{lang\.([A-z_0-9]*)\}/img;
   var rhtml = html;
   var match = myregexp.exec(html);
   while (match != null) {
      if(isset(wkr_lang[match[1]])) rhtml = rhtml.replace(match[0],wkr_lang[match[1]]);
      match = myregexp.exec(html);
   }
   return rhtml;
}
function isset(variable){
   return typeof variable!=='undefined';
}
function outconsole(e){if(isset(console)&&isset(console.log)) console.log(e);}
function crossContent(){
   var oldIE = isset(document.selection);
   function select(){
      if (isset(window.getSelection)){
         return window.getSelection();// Firefox/Chrome/Safari/Opera/IE9
      } else if(isset(document.getSelection)) {
         return document.getSelection();
      } else if(isset(document.selection)) {
         return document.selection
      }
   }
   function fixIERangeObject(e){if(!isset(e))return null;if(!isset(e.startContainer)&&isset(window.document.selection)){var t=function(e,t){var n=null,r=-1;for(var i=e.firstChild;i;i=i.nextSibling){if(i.nodeType==3){var s=i.nodeValue;var o=t.indexOf(s);if(o==0&&t!=s){t=t.substring(s.length)}else{n=i;r=t.length-1;break}}}return{node:n,offset:r}};var n=e.duplicate(),r=e.duplicate();var i=e.duplicate(),s=e.duplicate();n.collapse(true);n.moveEnd("character",1);r.collapse(false);r.moveStart("character",-1);var o=n.parentElement(),u=r.parentElement();if(o instanceof HTMLInputElement||u instanceof HTMLInputElement){return null}i.moveToElementText(o);i.setEndPoint("EndToEnd",n);s.moveToElementText(u);s.setEndPoint("EndToEnd",r);var a=i.text;var f=s.text;var l=t(o,a);var c=t(u,f);e.startContainer=l.node;e.startOffset=l.offset;e.endContainer=c.node;e.endOffset=c.offset+1}return e;}

   function range(){
      var sel = select();
      return oldIE?fixIERangeObject(sel.createRange()):(sel.getRangeAt && sel.rangeCount?sel.getRangeAt(0):document.createRange());
   }
   function html(){
      var ran = range();
      return oldIE?ran.htmlText:$('<div/>').append(ran.cloneContents()).html();
   }
   function empty(){
      var sel = select();
      oldIE?sel.empty():sel.removeAllRanges();
   }
   function currentNode(){
      return oldIE?select():range().startContainer;
   }
   function activeContainer(){
      var ran = range();
      return oldIE?ran.parentElement():ran.commonAncestorContainer;
   }
   function activeElement(){
      var ac = activeContainer();
      if(ac.nodeType!==1){while (ac.nodeType!==1) {ac = ac.parentNode;}}
      return ac;
   }
   function modeSelect(){
      var AObj = activeElement();
      if(AObj.children.length==0){
         if($(AObj).text()===text()) return 1; //full tag
         else return 0;// sub text
      } else return 2;
   }
   function htmlText(){
      var ran = range();
      var mode = modeSelect();
      switch(mode){
         case 0: return text(); break;
         case 1: return text(); break;
         case 2: return oldIE?ran.htmlText:$('<div/>').append(ran.cloneContents()).html(); break;
      }
   }
   function selectElement(elem){
      var s = select();
      if(oldIE){
         var r = document.body.createTextRange();
         r.moveToElementText(elem);
         r.select();
      } else {
         var r = document.createRange();
         r.selectNodeContents(elem);
         s.removeAllRanges();
         s.addRange(r);
      }
   }
   function selectBeforeNext(elem){
      var s = select();
      var p = elem.parentNode;
      var i, nobj;
      for(i=0;i<p.childNodes.length;i++){
         if(p.childNodes[i]==elem){
            if(isset(p.childNodes[i+1])) nobj = p.childNodes[i+1];
            break;
         }
      }
      if(isset(nobj)){
         selectElement(nobj);
         range().collapse(true);
      } else {
         selectElement(elem);
         range().collapse(false);
      }
   }
   function text(){
      var r = range();
      return oldIE?r.text:r.toString();
   }
   this.getSelect = select;
   this.getRange = range;
   this.empty = empty;
   this.currentNode = currentNode;
   this.oldIE = function(){return oldIE;}
   this.activeContainer = activeContainer;
   this.activeElement = activeElement;
   this.selectElement = selectElement;
   this.asText = text;
   this.asHtml = htmlText;
   this.selectMode = modeSelect;
   this.fixIERangeObject = fixIERangeObject;
   this.selectBeforeNext = selectBeforeNext;
}
var selections = new crossContent();
(function ($) {
   var selectedRange;
   var block_selected = false;
   var fn_success = [];
   $.wkr_editor = {};
   $.wkr_editor.cmd = {
      exec: cmd_default, 
      htmlInsert: function(htmlobj){
         var selection = selections.getSelect();
         var editor = $.wkr_editor.lasteditor;
         var range = selections.getRange();
         var repl = document.createTextNode(range);
         if(selections.oldIE()){
            range.pasteHTML("<span class='htmlinsert'>&nbsp;</span>");
            range.collapse(true);
         } else {
            var span = document.createElement("span");
            span.style.fontSize = '0px';
            $(span).addClass('htmlinsert');
            range.deleteContents(); 
            span.appendChild(repl); 
            range.insertNode(span);
         }
         editor.find('span.htmlinsert').each(function(){
            $(this).parent().get(0).replaceChild(htmlobj, this);
         });
         saveSelection();
      }, 
      textInsert: function(text){
         if(selections.oldIE()){
            var range = selections.getRange();
            range.pasteHTML(text);
            range.collapse(false);
         } else {
            $.wkr_editor.cmd.exec('insertHTML',text);
            selections.getRange().collapse(false);
         }
         saveSelection();
      }, 
      htmlFormat: function(obj){
         var selection = selections.getSelect();
         var range = selections.getRange();
         $(obj).html(selections.asHtml());
         range.deleteContents();       
         range.insertNode(obj);
         saveSelection();
      }, 
      cssFormat: function(css){
         var selection = selections.getSelect();
         var range = selections.getRange();
         var m = selections.selectMode();
         if(m == 1){
            var obj = selections.activeElement();
            $(obj).css(css);
         } else {
            var obj = document.createElement("span");
            $(obj).css(css).html(selections.asHtml());
            range.deleteContents();       
            range.insertNode(obj);
         }
         saveSelection();
      }, 
      showWindowPlugin:function(o){
         fn_success[o.id]=o.success;
         if(o.id!=''&&($$(o.id)!=null)){
            guiders.show(o.id)
         } else {
            o = $.extend({
               buttons: [{name: "&nbsp;&nbsp;&nbsp;Ok&nbsp;&nbsp;&nbsp;", onclick:function(){guiders.hideAll(); fn_success[o.id]();}},
                  { name: "Cansel", onclick: function(){guiders.hideAll(); }}],
               description: '',
               id: '',
               width:$(o.divId).width()-20+'px',
               xButton:true,
               onHide:function(){$.wkr_editor.lasteditor.focus(); $.wkr_editor.cmd.restoreSelection(false);},
               onShow:function(e){
                  e.elem.css('width',$(o.divId).width()+20+'px');
                  var d = e.elem.find('.guider_description');
                  $(o.divId).appendTo(d);
               },
               title: '',
               }, o);
            guiders.createGuider(o).show();
            var $div = $('body');
            $('.guider').drag("start",function( ev, dd){$(this).appendTo( this.parentNode ); dd.limit = $div.offset(); dd.limit.bottom = dd.limit.top + $div.outerHeight() - $( this ).outerHeight(); dd.limit.right = dd.limit.left + $div.outerWidth() - $( this ).outerWidth();}).drag(function( ev, dd ){$( this ).css({top: Math.min( dd.limit.bottom, Math.max(dd.limit.top, dd.offsetY)),left: Math.min( dd.limit.right, Math.max(dd.limit.left, dd.offsetX))});},{ handle:".guider_title" });
            $('.guider_title').css('cursor', 'move');
         }
      },
      restoreSelection: restoreSelection,
      saveSelection: saveSelection,
   };
   $.wkr_editor.lang = function(name){
      if(isset(wkr_lang[name])) return wkr_lang[name];
      else return name;
   }
   $.wkr_editor.upload = {};
   $.wkr_editor.lasteditor = $();
   $.wkr_editor.lastbutton = $();
   $.wkr_editor.plugins = [];
   var countJS = 0;
   var timerJS = 0;
   var pluginFiles = {
      image : {listfiles:'index.php?ajaxed=update_upload_json'},
      list : [{'css':'wkr-plugin.css'}, {'html':'wkr-plugin.html'},{'js': 'wkr-plugin.js'},{'js': 'wkrp_mapchar.js'}],
      add : function(objplug){
         var exec = this.list.length>0;
         if(typeof objplug=='array'||typeof objplug=='object'){
            while(objplug.length>0){this.list.push(objplug.shift());}
         } else this.list.push(objplug);
         if(!exec) setTimeout(function(){$.wkr_editor.defaults.plugins.loading();}, 200);
      },
      loading: function(){
         if(this.list.length>0){
            var patch_param = function(str){return (str.substr(0,1)!=='/')?patchScript+str:str;}
            while(this.list.length>0){
               var pl = this.list.shift();
               if(isset(pl['html'])){
                  countJS++;
                  $.get(patch_param(pl['html']),function(html){
                     countJS--;
                     html = wkr_lang_html(html);
                     $('body').append($('<div/>',{id:'wkr_plugin','style':'position: absolute; top: 0;visibility: hidden;'}).html(html));
                     if(isset($.DOMlive)) $.DOMlive.checkAll();
                     show_wait_button();
                  })
               } else if(isset(pl['js'])){
                  countJS++;
                  if(timerJS===0) {
                     timerJS = setInterval(function(){if(countJS===0) {clearInterval(timerJS); timerJS = 0; reInitWaitButton();}},500);
                  }
                  $.getScript(patch_param(pl['js'])).fail(function(jqxhr, settings, exception){outconsole(exception);}).success(function(){countJS--;});
               } else if(isset(pl['css'])){
                  var cssLink = $('<link/>');
                  $('head').append(cssLink);
                  cssLink.attr({rel:  'stylesheet',type: 'text/css',href: patch_param(pl['css'])});
               }
            }
         }
      }
   }
   $.wkr_editor.defaults = {
      buttons:{
         'bold' : {title: 'bold',className: 'icon-bold', cmd: 'bold'},
         'italic' : {title: 'italic',className: 'icon-italic', cmd: 'italic'},
         'strikethrough' : {title: 'strikethrough',className: 'icon-strikethrough', cmd: 'strikethrough'},
         'underline' : {title: 'underline',className: 'icon-underline', cmd: 'underline'},
         'justifyleft' : {title: 'justifyleft',className: 'icon-align-left', cmd: 'justifyleft'},
         'justifycenter' : {title: 'justifycenter',className: 'icon-align-center', cmd: 'justifycenter'},
         'justifyright' : {title: 'justifyright',className: 'icon-align-right', cmd: 'justifyright'},
         'justifyfull' : {title: 'justifyFull',className: 'icon-align-justify', cmd: 'justifyfull'},
         //'fontname' : {title: 'selectFont',className: 'icon-font', create_func:create_fontname},
         'insertunorderedlist' : {title: 'insertUnorderedList',className: 'icon-list-ul', cmd: 'insertunorderedlist'},
         'insertorderedlist' : {title: 'insertOrderedList',className: 'icon-list-ol', cmd: 'insertorderedlist'},
         'outdent' : {title: 'outdent',className: 'icon-indent-left', cmd: 'outdent'},
         'indent' : {title: 'indent',className: 'icon-indent-right', cmd: 'indent'},
         'createlink' : {title: 'createLink',className: 'icon-link', cmd: 'createLink'},
         'unlink' : {title: 'removeLink',className: 'icon-cut', cmd: 'unlink'},
         'undo' : {title: 'undo',className: 'icon-undo', cmd: 'undo'},
         'redo' : {title: 'redo',className: 'icon-repeat', cmd: 'redo'},
         'hr' : {title: 'insertHorizontalRule',className: 'icon-hr', cmd: 'InsertHorizontalRule'}
      },
      fonts :['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
         'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
         'Times New Roman', 'Verdana'],
      toolbarbottons:[
         ['html'],
         ['bold', 'italic', 'strikethrough', 'underline'], 
         ['justifyleft', 'justifycenter', 'justifyright', 'justifyfull'],
         ['fontname', 'fontsize'],
         ['insertunorderedlist', 'insertorderedlist', 'outdent', 'indent'],
         ['undo', 'redo'],
         [],
         ['insertImage', 'link', 'smiles', 'color', 'backColor', 'spoiler', 'mapchar', 'sup', 'sub', 'cite', 'hide', 'hr', 'code']
      ],
      plugins: pluginFiles
   }

   var patchScript = $.wkr_editor.patch = getPatch();
   var wait_buttons = [];
   var cout_waitb = 5;
   function getPatch(){var p = '';$('script').each(function(i,s){if (s.src){var regexp = new RegExp(/\/wkr-wysiwyg\.min\.js|\/wkr-wysiwyg\.js/);if (s.src.match(regexp)) p = s.src.replace(regexp, '')+'/';}});return p;}
   function create_btn(editor, button, dest){
      if(isset($.wkr_editor.defaults.buttons[button])){
         var b = $.wkr_editor.defaults.buttons[button];
         var title = isset(wkr_lang[b.title])? wkr_lang[b.title]: b.title;
         var a = $('<a/>',{'class':'btn', 'data-button':button, 'title' : title}).append($('<i/>',{'class':b.className})).appendTo(dest);
         if(isset(b.cmd)) a.attr('data-edit',b.cmd);
         if(isset(b.create_func)) b.create_func(editor, button, a);
         if(isset(b.plugin) && isset(b.plugin.create)) b.plugin.create(editor, button, a);
      } else {
         var a = $('<a/>',{'class':'btn', 'data-button':button}).append($('<i/>',{'class':'icon_empty'})).appendTo(dest);
         wait_buttons.push({btn: a, 'button': button, 'editor': editor});
      }
   }
   function reInitWaitButton(){
      var nb = [];
      while(wait_buttons.length>0){
         var info = wait_buttons.pop();
         if(isset($.wkr_editor.defaults.buttons[info.button])){
            var b = $.wkr_editor.defaults.buttons[info.button];
            var title = isset(wkr_lang[b.title])? wkr_lang[b.title]: b.title;
            info.btn.attr('title', title).find('i').attr('class', b.className);
            if(isset(b.cmd)) info.btn.attr('data-edit',b.cmd);
            if(isset(b.create_func)) b.create_func(info.editor, info.button, info.btn);
            if(isset($.wkr_editor.plugins[info.button])){
               var plugin = $.wkr_editor.plugins[info.button];
               if(isset(plugin.create)) plugin.create(info.editor, info.button.button, info.btn);
               if(isset(plugin.init)) plugin.init();
            }
         } else nb.push(info);
      }
      if(nb.length>0){wait_buttons = nb;}
   }
   function show_wait_button(){
      reInitWaitButton();
      if(wait_buttons.length>0 && cout_waitb>0){
         cout_waitb--;
         setTimeout(show_wait_button, 1000);
      }
   }
   $(document).ready(function(){
      $.wkr_editor.defaults.plugins.loading();
   });

   $.fn.wkr_editor = function (userOptions){
      var textarea = this;
      var source = $('<div/>').insertBefore(this);
      var editor = $('<div/>',{'class': 'wkr_editor', id: this.attr('id')+'_wkr'}).html(this.val()).appendTo(source);
      source.append(this);
      var editorId = this.attr('id')+'_wkr';
      var editorStyle = textarea.attr('style')||'';
      var editorHeight = this.height();
      editor.height(editorHeight);
      this.height(1).css({visibility:'hidden',padding : '0'});
      var options;
      var thisEditor = {
         switchHtml:function(isHtml){
            if(isHtml){
               editor.hide();
               textarea.attr('style',editorStyle);
            } else {
               textarea.height(1).css({visibility:'hidden',padding : '0'});
               editor.show();
            }
         },
         save:function(){
            textarea.val(editor.html());
         },
         load: function(html){
            if(!isset(html)) html = textarea.val();
            editor.html(html)
         }
      };
      $(this).data('obj',thisEditor);
      function init(){

         var toolbar = $('<div/>',{'class':'btn-toolbar', 'data-role':'editor-toolbar', 'data-target':'#'+editorId});
         var tblbtn = $.wkr_editor.defaults.toolbarbottons;
         var l = tblbtn.length;
         var i,j;
         var tline = $('<div/>',{'class':'btn-toolbar-line'}).appendTo(toolbar);
         for(i=0;i<l;i++){
            var r = tblbtn[i];
            var dg = $('<div/>',{'class':'btn-group'}).appendTo(tline);
            if($.isArray(r)){
               if(r.length>0){for(j=0;j<r.length;j++) create_btn(editor, r[j], dg);}
               else tline = $('<div/>',{'class':'btn-toolbar-line'}).appendTo(toolbar);
            } else create_btn(editor, r, dg);
         }
         toolbar.insertBefore(editor);
         toolbar.on('click','[data-edit]', function (even) {
            if($(this).hasClass('disabled')){
               even.stopPropagation();
               even.preventDefault();
            } else {
               $.wkr_editor.lastbutton = this;
               restoreSelection();
               editor.focus();
               execCommand($(this).data('edit'), '', $(this).data('button'));
               saveSelection();
            }
         }).on('click','[data-toggle=dropdown]', function(even){
            if($(this).hasClass('disabled')){
               even.stopPropagation();
               even.preventDefault();
            } else {
               if(show_drop_down){show_drop_down = false;obj_drop_down.hide();}
               restoreSelection();
               var obj = this;
               even.stopPropagation();
               even.preventDefault();
               show_drop_down = true;
               var bn = $(obj).attr('data-button');
               if(isset($.wkr_editor.defaults.buttons[bn])){
                  var b = $.wkr_editor.defaults.buttons[bn];
                  if(isset(b.plugin) && isset(b.plugin.show)) b.plugin.show();
               }
               obj_drop_down = $(obj).find('.dropdown-menu').show();
            }
         }).on('click','.dropdown-menu li',function(even){
            even.stopPropagation();
            even.preventDefault();
            show_drop_down = false;
            $(this).parent().hide();
         });
         $('#'+editorId).parents('form').on('submit',function(){
            thisEditor.save();
         });
      }
      function updateToolbar() {
         $('[data-role=editor-toolbar]').find('[data-edit]').each(function () {
            var command = $(this).data('edit');
            var commandArr = command.split(' '),
            command = commandArr.shift();
            try{
               if (document.queryCommandState(command)) $(this).addClass('btn-info');
               else $(this).removeClass('btn-info');
            } catch (e){
            }
         });
      };
      function execCommand(commandWithArgs, valueArg, button) {
         var commandArr = commandWithArgs.split(' ');
         var command = commandArr.shift();
         var args = commandArr.join(' ') + (valueArg || '');
         button = button || '';
         var buttons = $.wkr_editor.defaults.buttons;
         if((button!=='')&&(isset(buttons[button]))){
            var cExec = false;
            if(isset(buttons[button].command)) {cExec = true; buttons[button].command(command, args);}
            if(isset(buttons[button].plugin) && isset(buttons[button].plugin.command)) {cExec = true; buttons[button].plugin.command(command, args);}
            if(!cExec) cmd_default(command, args);
         } else cmd_default(command, args);
         updateToolbar();
      }
      function bindHotkeys(hotKeys) {
         $.each(hotKeys, function (hotkey, command) {
            editor.keydown(hotkey, function (e) {
               if (editor.attr('contenteditable') && editor.is(':visible')) {
                  e.preventDefault();
                  e.stopPropagation();
                  execCommand(command);
               }
            }).keyup(hotkey, function (e) {
               if (editor.attr('contenteditable') && editor.is(':visible')) {
                  e.preventDefault();
                  e.stopPropagation();
               }
            });
         });
      }
      options = $.extend({}, $.fn.wkr_editor.defaults, userOptions);
      bindHotkeys(options.hotKeys);
      editor.attr('contenteditable', true)
      .on('mouseup keyup', function() {
         block_selected = false;
         saveSelection();
         updateToolbar();
      }).on('mouseout',function(even){
         if(this===document.activeElement) saveSelection(true);
      }).on('mouseover',function(){
         $.wkr_editor.lasteditor = editor;
         block_selected = false;
      });
      $(window).bind('touchend', function (e) {
         var isInside = (editor.is(e.target) || editor.has(e.target).length > 0),
         currentRange = selections.getRange(),
         clear = currentRange && (currentRange.startContainer === currentRange.endContainer && currentRange.startOffset === currentRange.endOffset);
         if (!clear || isInside) {
            saveSelection();
            updateToolbar();
         }
      });
      var show_drop_down = false;
      var obj_drop_down;
      $(window).on('click',function(){
         if(show_drop_down){
            show_drop_down = false;
            obj_drop_down.hide();
         }
      })
      init();
      return this;
   };
   $.fn.wkr_editor.defaults = {
      hotKeys: {},
   };
   function create_fontname(editor, button, a){
      if(isset($.wkr_editor.defaults.buttons[button])){
         var b = $.wkr_editor.defaults.buttons[button];
         var ul = $('<ul/>',{'class':'dropdown-menu'});
         a.append(ul).attr('data-toggle','dropdown');
         var fonts = $.wkr_editor.defaults.fonts;
         for(var i=0;i<fonts.length;i++){
            $('<li/>').append($('<a/>',{'data-edit':'FontName ' + fonts[i], 'data-button':button, text: fonts[i]}).css('font-family',fonts[i])).appendTo(ul);
         }
      }
   }

   function cmd_default(command, args){
      try{
         return document.execCommand(command, false, args);
      } catch (e){
         if(e && e.result == 2147500037){
            var range = window.getSelection().getRangeAt(0);
            var dummy = document.createElement('p');
            var ceNode = range.startContainer.parentNode;
            while(ceNode && ceNode.contentEditable != 'true') ceNode = ceNode.parentNode;
            if(!ceNode) return false;
            ceNode.insertBefore(dummy, ceNode.childNodes[0]);
            var r = document.execCommand(command, false, args);
            dummy.parentNode.removeChild(dummy);
            return r;
         } else outconsole(e);
      }     
   }
   function saveSelection(set_block) {
      if(!block_selected) selectedRange = selections.getRange();
      if(isset(set_block)) block_selected = set_block;
   }
   function restoreSelection(set_block) {
      if (selectedRange){
         if(selections.oldIE()){
            document.body.createTextRange().select();
            document.selection.empty();
            selectedRange.select();
         } else {
            var sel = selections.getSelect();
            sel.removeAllRanges();
            sel.addRange(selectedRange);
         }
      }
      if(isset(set_block)) block_selected = set_block;
   }
   }(window.jQuery));
/******** mini plugins *********/

(function () {
   var fontsizes = $.wkr_editor.defaults.plugins.fontsizes = ['8px','10px','12px','14px','18px','24px','36px'];
   var plugin = $.wkr_editor.plugins['fontsize'] = {
      create:function(editor, button, a){
         if(isset($.wkr_editor.defaults.buttons[button])){
            var b = $.wkr_editor.defaults.buttons[button];
            var ul = $('<ul/>',{'class':'dropdown-menu'});
            a.append(ul).attr('data-toggle','dropdown');
            for(var i=0;i<fontsizes.length;i++){
               $('<li/>').append($('<a/>',{'data-edit':'fontSize ' + fontsizes[i], 'data-button':button, text: fontsizes[i]}).css('font-size',fontsizes[i])).appendTo(ul);
            }
         }
      },
      command:function(cmd, args){
         document.execCommand('Fontsize', false, '0')
         $.wkr_editor.lasteditor.find('font:[size]').css('font-size',args).removeAttr('size');
      },
   }
})();
$.wkr_editor.defaults.buttons['fontsize'] = {title: 'selectSize',className: 'icon-text-height', plugin: $.wkr_editor.plugins['fontsize']};

(function () {
   var fonts = $.wkr_editor.defaults.plugins.fonts =['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
      'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
      'Times New Roman', 'Verdana'];
   var plugin = $.wkr_editor.plugins['fontname'] = {
      create:function(editor, button, a){
         if(isset($.wkr_editor.defaults.buttons[button])){
            var b = $.wkr_editor.defaults.buttons[button];
            var ul = $('<ul/>',{'class':'dropdown-menu'});
            a.append(ul).attr('data-toggle','dropdown');
            //var fonts = $.wkr_editor.defaults.fonts;
            for(var i=0;i<fonts.length;i++){
               $('<li/>').append($('<a/>',{'data-edit':'FontName ' + fonts[i], 'data-button':button, text: fonts[i]}).css('font-family',fonts[i])).appendTo(ul);
            }
         }
      },
   }
})();
$.wkr_editor.defaults.buttons['fontname'] = {title: 'selectFont',className: 'icon-font', plugin: $.wkr_editor.plugins['fontname']};


(function () {
   var btn;
   var plugin = $.wkr_editor.plugins['html'] = {
      create:function(editor, button, a){
         btn = a;
      },
      command:function(cmd){
         var b = $($.wkr_editor.lastbutton);
         var p = b.parents('.btn-toolbar:first');
         var ps = p.parent();
         var ab = p.find('.btn').not($.wkr_editor.lastbutton);
         var isHtml = ab.not('.disabled').length==0;
         var obj = ps.find('textarea').data('obj');
         if (!isHtml) {
            ab.addClass('disabled');
            obj.save();
            obj.switchHtml(true);
         } else {
            ab.removeClass('disabled');
            obj.switchHtml(false);
            obj.load();
         }
      },
   }
})();
$.wkr_editor.defaults.buttons['html'] = {title: 'htmlcode',className: 'icon-html', cmd: 'custom', plugin: $.wkr_editor.plugins['html']};

(function () {
   var crtl = false;
   var cont;
   var smiles = $.wkr_editor.defaults.plugins.smiles = ['act-up.png','airplane.png','alien.png','angel.png','angry-2.png','arrogant.png','bad.png','bashful.png','beat-up.png','beauty.png','beer.png','blowkiss.png','bomb.png','bowl.png','boy.png','brb-1.png','bye.png','cake.png','call-me.png','camera-2.png','can.png','car.png','cat.png','chicken.png','clap.png','clock-1.png','cloudy.png','clover.png','clown.png','coffee-1.png','coins.png','computer-2.png','confused-1.png','console.png','cow.png','cowboy.png','crying.png','curl-lip.png','curse.png','cute.png','dance.png','dazed.png','desire.png','devil.png','disapointed.png','disdain.png','doctor.png','dog.png','doh.png','dont-know.png','drink.png','drool.png','eat.png','embarrassed.png','excruciating.png','eyeroll.png','film-2.png','fingers-crossed.png','flag.png','foot-mouth.png','freaked-out.png','ghost.png','giggle.png','girl.png','glasses-cool.png','glasses-nerdy.png','go-away.png','goat.png','good.png','hammer.png','handcuffs.png','handshake.png','highfive.png','hug-left.png','hug-right.png','hungry.png','hypnotized.png','in-love-1.png','island.png','jump.png','kiss-1.png','knife.png','lamp.png','lashes.png','laugh-1.png','liquor.png','love-1.png','love-over.png','lying.png','mad-tongue.png','mail-7.png','mean.png','meeting.png','mobile.png','moneymouth.png','monkey.png','moon.png','msn.png','msn-away.png','msn-busy.png','msn-online.png','musical-note.png','nailbiting.png','neutral.png','party.png','peace.png','phone-4.png','pig.png','pill.png','pissed-off.png','pizza.png','plate.png','poop.png','pray.png','present.png','pumpkin-2.png','qq.png','question-1.png','quiet.png','rain.png','rainbow.png','rose.png','rose-dead.png','rotfl.png','sad-1.png','sarcastic.png','search-1.png','secret.png','shame.png','sheep.png','shock.png','shout-1.png','shut-mouth.png','sick-1.png','sigarette.png','silly.png','skeleton.png','skywalker.png','sleepy.png','smile-1.png','smile-big.png','smirk.png','snail.png','snicker.png','snowman-1.png','soccerball.png','soldier.png','star-4.png','struggle.png','sun.png','sweat-1.png','teeth.png','terror.png','thinking.png','thunder.png','tongue.png','tremble.png','turtle.png','tv-2.png','umbrella.png','vampire.png','victory.png','waiting.png','watermelon.png','weep.png','wilt.png','wink.png','worship.png','yawn.png','yin-yang.png'];
   var plugin = $.wkr_editor.plugins['smiles']={
      create:function(editor, button, a){
         var b = $.wkr_editor.defaults.buttons[button];
         cont = $('<div/>',{'class':'dropdown-menu smilediv', style:'padding:5px'});
         a.append(cont).attr('data-toggle','dropdown');
         var psmile = $.wkr_editor.patch + 'smiles/';
         for(var i=0;i<smiles.length;i++){
            var sd = $('<div/>').appendTo(cont);
            $('<img/>').attr('src', psmile + smiles[i]).appendTo(sd);
            if((i+1) % 15==0) $('<br/>').appendTo(cont);
         }
         cont.on('click','img',function(even){
            even.stopPropagation();
            even.preventDefault();
            $.wkr_editor.lasteditor.focus(); $.wkr_editor.cmd.restoreSelection(false);
            var img = $('<img/>',{src:this.src, title: ''});
            $.wkr_editor.cmd.htmlInsert(img.get(0));
            selections.selectBeforeNext(img.get(0));
            $.wkr_editor.cmd.saveSelection();
            if(!even.ctrlKey) cont.hide();
            else crtl = true;
         });
         $(document).on('keyup.smiles',function(even){
            if(crtl && !even.ctrlKey){cont.hide();crtl = false;}
         });
      },
      command:function(cmd){},
      show:function(){
         crtl = false;
      }
   }
})();   
$.wkr_editor.defaults.buttons['smiles'] = {title: 'Smiles',className: 'icon-smile', plugin: $.wkr_editor.plugins['smiles']};

(function () {
   var colorLib = $.wkr_editor.defaults.plugins.colors = ['#330000','#333300','#336600','#339900','#33CC00','#33FF00','#66FF00','#66CC00','#669900','#666600','#663300','#660000','#FF0000','#FF3300','#FF6600','#FF9900','#FFCC00','#FFFF00','#330033','#333333','#336633','#339933','#33CC33','#33FF33','#66FF33','#66CC33','#669933','#666633','#663333','#660033','#FF0033','#FF3333','#FF6633','#FF9933','#FFCC33','#FFFF33','#330066','#333366','#336666','#339966','#33CC66','#33FF66','#66FF66','#66CC66','#669966','#666666','#663366','#660066','#FF0066','#FF3366','#FF6666','#FF9966','#FFCC66','#FFFF66','#330099','#333399','#336699','#339999','#33CC99','#33FF99','#66FF99','#66CC99','#669999','#666699','#663399','#660099','#FF0099','#FF3399','#FF6699','#FF9999','#FFCC99','#FFFF99','#3300CC','#3333CC','#3366CC','#3399CC','#33CCCC','#33FFCC','#66FFCC','#66CCCC','#6699CC','#6666CC','#6633CC','#6600CC','#FF00CC','#FF33CC','#FF66CC','#FF99CC','#FFCCCC','#FFFFCC','#3300FF','#3333FF','#3366FF','#3399FF','#33CCFF','#33FFFF','#66FFFF','#66CCFF','#6699FF','#6666FF','#6633FF','#6600FF','#FF00FF','#FF33FF','#FF66FF','#FF99FF','#FFCCFF','#FFFFFF','#0000FF','#0033FF','#0066FF','#0099FF','#00CCFF','#00FFFF','#99FFFF','#99CCFF','#9999FF','#9966FF','#9933FF','#9900FF','#CC00FF','#CC33FF','#CC66FF','#CC99FF','#CCCCFF','#CCFFFF','#0000CC','#0033CC','#0066CC','#0099CC','#00CCCC','#00FFCC','#99FFCC','#99CCCC','#9999CC','#9966CC','#9933CC','#9900CC','#CC00CC','#CC33CC','#CC66CC','#CC99CC','#CCCCCC','#CCFFCC','#000099','#003399','#006699','#009999','#00CC99','#00FF99','#99FF99','#99CC99','#999999','#996699','#993399','#990099','#CC0099','#CC3399','#CC6699','#CC9999','#CCCC99','#CCFF99','#000066','#003366','#006666','#009966','#00CC66','#00FF66','#99FF66','#99CC66','#999966','#996666','#993366','#990066','#CC0066','#CC3366','#CC6666','#CC9966','#CCCC66','#CCFF66','#000033','#003333','#006633','#009933','#00CC33','#00FF33','#99FF33','#99CC33','#999933','#996633','#993333','#990033','#CC0033','#CC3333','#CC6633','#CC9933','#CCCC33','#CCFF33','#000000','#003300','#006600','#009900','#00CC00','#00FF00','#99FF00','#99CC00','#999900','#996600','#993300','#990000','#CC0000','#CC3300','#CC6600','#CC9900','#CCCC00','#CCFF00','#000000','#111111','#222222','#333333','#444444','#555555','#666666','#777777','#888888','#999999','#AAAAAA','#BBBBBB','#CCCCCC','#DDDDDD','#EEEEEE','#FFFFFF'];
   var colorContent;
   function create_color_content(){
      if(typeof colorContent=='undefined'){
         var d = $('<div/>',{'class':'dropdown-menu colordiv', style:'padding:5px'});
         for(var i=0;i<colorLib.length;i++){
            var sd = $('<div/>',{'class':'fcolor'}).css('background-color',colorLib[i]).appendTo(d);
            if((i+1) % 18==0) $('<br/>').appendTo(d);
         }
         colorContent = d.clone();
         return d;
      } else return colorContent;
   }
   var pluginColor = $.wkr_editor.plugins['color']={
      create:function(editor, button, a){
         var b = $.wkr_editor.defaults.buttons[button];
         var d = create_color_content();
         a.append(d).attr('data-toggle','dropdown');
         d.on('click','.fcolor',function(even){
            even.stopPropagation();
            even.preventDefault();
            d.hide();
            $.wkr_editor.lasteditor.focus(); $.wkr_editor.cmd.restoreSelection(false);
            var c = $(this).css('background-color');
            if(!$.wkr_editor.cmd.exec('ForeColor',c)) $.wkr_editor.cmd.cssFormat({'color': c});
         });
      },
      command:function(cmd){},
   }
   var pluginBkColor = $.wkr_editor.plugins['backColor'] = {
      create:function(editor, button, a){
         var b = $.wkr_editor.defaults.buttons[button];
         var d = create_color_content();
         a.append(d).attr('data-toggle','dropdown');
         d.on('click','.fcolor',function(even){
            even.stopPropagation();
            even.preventDefault();
            d.hide();
            $.wkr_editor.lasteditor.focus(); $.wkr_editor.cmd.restoreSelection(false);
            var c = $(this).css('background-color');
            if ( !$.wkr_editor.cmd.exec('HiliteColor',c)) {
               if(!$.wkr_editor.cmd.exec('BackColor',c)) $.wkr_editor.cmd.cssFormat({'background-color': c});
            }
         });
      },
      command:function(cmd){},
   }
})();
$.wkr_editor.defaults.buttons['color'] = {title: 'foreColor',className: 'icon-color', plugin: $.wkr_editor.plugins['color']};
$.wkr_editor.defaults.buttons['backColor'] = {title: 'backColor',className: 'icon-bkcolor', plugin: $.wkr_editor.plugins['backColor']};

(function () {
   var plugin = $.wkr_editor.plugins['spoiler'] = {
      command:function(cmd){
         var d = $('<div/>',{'class':'spl_src'});
         var head = $('<div/>',{'class':'spl_head'}).append($('<span/>',{text: $.wkr_editor.lang('spoiler_text')})).appendTo(d);
         $('<div/>',{'class':'spl_text',text:' message '}).appendTo(d);
         $.wkr_editor.cmd.htmlInsert(d.get(0));
      },
   }
})();
$.wkr_editor.defaults.buttons['spoiler'] = {title: 'spoiler',className: 'icon-spoiler', cmd: 'custom', plugin: $.wkr_editor.plugins['spoiler']};

(function () {
   var plugin = $.wkr_editor.plugins['sup'] = {
      command:function(cmd){
         var sp = $('<'+cmd+'/>',{html: '&nbsp;'});
         var msp = sp.get(0);
         $.wkr_editor.cmd.htmlInsert(msp);
         selections.selectElement(msp);
         //selections.getRange().collapse(true);
         var p = sp.parent().get(0);
         var tn = document.createTextNode(' ');
         var k;
         var clen = p.childNodes.length;
         for(var i=0;i<clen; i++){
            if(p.childNodes[i].nodeType==1 && p.childNodes[i]==msp) {k=i; break;}
         }
         if(k!=(clen-1)) p.insertBefore(tn, p.childNodes[i+1]);
         else p.appendChild(tn);
      },
   }
})();
$.wkr_editor.defaults.buttons['sup'] = {title: 'superscript',className: 'icon-sup', cmd: 'sup', plugin: $.wkr_editor.plugins['sup']};
$.wkr_editor.defaults.buttons['sub'] = {title: 'subscript',className: 'icon-sub', cmd: 'sub', plugin: $.wkr_editor.plugins['sup']};

(function () {
   var plugin = $.wkr_editor.plugins['cite'] = {
      command:function(cmd){
         var d = $('<div/>',{style:'padding: 4px;','align':'left'});
         var head = $('<div/>',{'class':'quotetop'}).append($('<span/>',{text: $.wkr_editor.lang('insertQuote')})).appendTo(d);
         $('<div/>',{'class':'quotemain',text:' message '}).appendTo(d);
         $.wkr_editor.cmd.htmlInsert(d.get(0));
      },
   }
})();
$.wkr_editor.defaults.buttons['cite'] = {title: 'insertQuote',className: 'icon-cite', cmd: 'custom', plugin: $.wkr_editor.plugins['cite']};

(function () {
   var plugin = $.wkr_editor.plugins['hide'] = {
      command:function(cmd){
         $('#wkrp_hide').find('textarea').val(selections.asHtml());
         $.wkr_editor.cmd.showWindowPlugin({id:'wkrp_hide_show', title:$.wkr_editor.lang('spoiler_text'), divId:'#wkrp_hide', success:function(){
            var ut = $('#wkrp_hide').find('textarea').val();
            var c = $('#wkrp_hide').find('input').val();
            if(isset(c) && c.trim()!=='') c = '='+c;
            if(!isset(ut)||ut==='') ut = ' hide message ';
            var d = $('<div/>',{'class':'hidetext' ,style:'padding: 4px;','align':'left',html:ut});
            $.wkr_editor.cmd.htmlInsert(d.get(0));
            var comment = document.createComment('hide_content_begin'+c);
            d.before(comment);
            var comment = document.createComment('hide_content_end');
            d.after(comment);
         }});
      },
   }
})();
$.wkr_editor.defaults.buttons['hide'] = {title: 'insertHide',className: 'icon-hide', cmd: 'custom', plugin: $.wkr_editor.plugins['hide']};

(function () {
   var aliases = $.wkr_editor.defaults.plugins.SyntaxHighlighter = {
      applescript: "@shBrushAppleScript.js",
      actionscript3: "@shBrushAS3.js",
      as3: "@shBrushAS3.js",
      bash: "@shBrushBash.js",
      shell: "@shBrushBash.js",
      coldfusion: "@shBrushColdFusion.js",
      cf: "@shBrushColdFusion.js",
      cpp: "@shBrushCpp.js",
      c: "@shBrushCpp.js",
      "c#": "@shBrushCSharp.js",
      "c-sharp": "@shBrushCSharp.js",
      csharp: "@shBrushCSharp.js",
      css: "@shBrushCss.js",
      delphi: "@shBrushDelphi.js",
      pascal: "@shBrushDelphi.js",
      diff: "@shBrushDiff.js",
      pas: "@shBrushDiff.js",
      patch: "@shBrushDiff.js",
      erl: "@shBrushErlang.js",
      erlang: "@shBrushErlang.js",
      groovy: "@shBrushGroovy.js",
      java: "@shBrushJava.js",
      jfx: "@shBrushJavaFX.js",
      javafx: "@shBrushJavaFX.js",
      jscript: "@shBrushJScript.js",
      js: "@shBrushJScript.js",
      javascript: "@shBrushJScript.js",
      perl: "@shBrushPerl.js",
      pl: "@shBrushPerl.js",
      php: "@shBrushPhp.js",
      text: "@shBrushPlain.js",
      plain: "@shBrushPlain.js",
      py: "@shBrushPython.js",
      python: "@shBrushPython.js",
      rails: "@shBrushRuby.js",
      "ruby ": "@shBrushRuby.js",
      rb: "@shBrushRuby.js",
      ror: "@shBrushRuby.js",
      sass: "@shBrushSass.js",
      scss: "@shBrushSass.js",
      scala: "@shBrushScala.js",
      sql: "@shBrushSql.js",
      vb: "@shBrushVb.js",
      vbnet: "@shBrushVb.js",
      code: "@shBrushXml.js",
      html: "@shBrushXml.js",
      xslt: "@shBrushXml.js",
      xhtml: "@shBrushXml.js",
      xml: "@shBrushXml.js"
   };
   function get_new_syntax(d, func_after_load){
      var i, found = false;
      var rg = new RegExp('brush\x20*:\x20*([^\'"\x20]*)', 'img');
      var cn = d.find('pre').attr('class');
      var reg = rg.exec(cn);
      if(reg!==null){
         var fc = reg[1].toLowerCase();
         for(i in SyntaxHighlighter.brushes){
            if(isset(SyntaxHighlighter.brushes[i].aliases[fc])){found = true; break;}
            if(found) break;
         }
      }
      if(!found){
         if(isset(aliases[fc])){
            var sp = aliases[fc].replace("@", "includes/javascript/syntax/scripts/");
            SyntaxHighlighter.vars.discoveredBrushes=null;
            $.getScript(sp).fail(function(jqxhr, settings, exception) {outconsole(exception);})
            .success(function(){func_after_load();});
         }
      }
   }
   function runSyntaxHighlighter(){
      SyntaxHighlighter.defaults['toolbar'] = false;
      SyntaxHighlighter.config.bloggerMode = true;
      SyntaxHighlighter.highlight(undefined, null);
   }
   var plugin = $.wkr_editor.plugins['code'] = {
      command:function(cmd){
         $.wkr_editor.cmd.showWindowPlugin({id:'wkrp_code_show', title:$.wkr_editor.lang('insertCode'), divId:'#wkrp_code', success:function(){
            var ut = $('#wkrp_code').find('textarea').val();
            var utc = $('#wkrp_code').find('select').val();
            var d = $('<div/>',{style:'padding: 4px;', align:'left'});
            $('<a/>',{style:'cursor: pointer;','class':'codeshow',text: $.wkr_editor.lang('codeShow')}).append($('<b/>',{text:' ['+utc.toUpperCase()+']'})).appendTo(d);
            $('<br/>').appendTo(d);
            $('<pre/>',{style:'overflow: visible; width: 200px; display:none;', 'class': 'syntaxNotReady brush: '+utc, text: ut}).appendTo(d);
            $.wkr_editor.cmd.htmlInsert(d.get(0));
            get_new_syntax(d, runSyntaxHighlighter);
         }});
      },
   }
})();
$.wkr_editor.defaults.buttons['code'] = {title: 'insertCode',className: 'icon-code', cmd: 'custom', plugin: $.wkr_editor.plugins['code']};
$.wkr_editor.defaults.plugins.add([{'js':'/includes/javascript/syntax/scripts/shCore.js'}, {'css':'/includes/javascript/syntax/styles/shCore.css'},
   {'css': '/includes/javascript/syntax/styles/shCoreDefault.css'}]);
