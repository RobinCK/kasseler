/**
* editor_plugin_src.js
*
* Copyright 2012, Kasseler CMS
* Released under LGPL License.
*
* License: http://tinymce.moxiecode.com/license
* Contributing: http://tinymce.moxiecode.com/contributing
*/
(function() {
      var ilang=tinymce.settings.language;
      var editor=null;
      KR_AJAX=parent.KR_AJAX;
      var fonts =new Array("Arial", "Comic Sans MS", "Courier New", "Georgia", "Impact", "Sans Serif", "Tahoma", "Times New Roman", "Verdana");
      var sizef = ['1', '2', '3', '4', '5', '6', '7'];
      function getLang(varlang){
         var l=ilang+'.kasseler.'+varlang;
         return tinymce.i18n[l];
      }
      function insertCite(name,val){
         return  '<div class="quotetop">'+name+'</div><div class="quotemain">'+val+'</div>';
      }
      function insertSpoiler(name,val){
         return  '<div class="spl_src"><div onclick="window.parent.switch_spoiler(this);" class="spl_head spl_show"><span>&#8203;'+name+'</span></div><div class="spl_text" style="display: block;">&#8203;'+val+'</div></div>';
      }
      window.set_mce_font=function(fontname){
         editor.execCommand('FontName', false, fontname);
         closeall('mce');
      }
      window.set_mce_size=function(fsize){
         editor.execCommand('FontSize', false, fsize);
         closeall('mce');
      }
      function c_sel(id){
         var html = '';
         html += '<div id="size_'+id+'" class="dropdown" style="position: absolute; width: 160px; height: 140px;"><table cellpadding="0" cellspacing="0" width="100%">';
         for (var i=0;i<sizef.length;i++) {
            switch (sizef[i]){
               case "1": sizepx = "8"; break;
               case "2": sizepx = "10"; break;
               case "3": sizepx = "12"; break;
               case "4": sizepx = "14"; break;
               case "5": sizepx = "18"; break;
               case "6": sizepx = "24"; break;
               case "7": sizepx = "36"; break;
            }
            html += '<tr><td onclick="set_mce_size(\''+sizepx+'\');" class="ower_button" onmouseover="this.className=\'hower_button\'" onmouseout="this.className=\'ower_button\'" style="font-size:'+sizepx+'px; height:'+sizepx+'px; cursor:pointer;"">'+sizef[i]+' ('+sizepx+')</td></tr>';
         }
         html += '</table></div><div id="font_'+id+'" class="dropdown" style="position: absolute; width: 160px; height: 140px;"><table cellpadding="0" cellspacing="0" width="100%">';
         for (var i=0;i<fonts.length;i++) {
            html += '<tr><td onclick="set_mce_font(\''+fonts[i]+'\');" class="ower_button" onmouseover="this.className=\'hower_button\'" onmouseout="this.className=\'ower_button\'" style="font-family: '+fonts[i]+'; cursor:pointer;">'+fonts[i]+'</td></tr>';
         }                
         html += '</table></div>';
         $('<div/>').html(html).appendTo('BODY');
      }
      function closeall(id){
         var elms = ['font_', 'size_'];
         for(var i=0;i<elms.length;i++) if($$(elms[i]+id)) $$(elms[i]+id).style.display='none';
      }
      function show_sel(e, id){        
         if(e.style.display!='block'){
            closeall(id);
            e.style.display='block';
         } else e.style.display='none';
      }
      c_sel('mce');
      var smileInited=false;
      var translitInited=false;
      function initPlWindow(){}
      showWindow = function(o){
         if(o.id!=''&&($$(o.id)!=null)){
            guiders.show(o.id)
         } else {
            o = $.extend({
                  buttons: [],
                  description: '',
                  id: '',
                  width:$('#ajax_content').width()-20,
                  xButton:true,
                  onHide:function(){KR_AJAX.animation('hide');},
                  onShow:function(){initPlWindow();},
                  title: ''
               }, o);
            guiders.createGuider(o).show();
            var $div = $('body');
            $('.guider').drag("start",function( ev, dd){$(this).appendTo( this.parentNode ); dd.limit = $div.offset(); dd.limit.bottom = dd.limit.top + $div.outerHeight() - $( this ).outerHeight(); dd.limit.right = dd.limit.left + $div.outerWidth() - $( this ).outerWidth();}).drag(function( ev, dd ){$( this ).css({top: Math.min( dd.limit.bottom, Math.max(dd.limit.top, dd.offsetY)),left: Math.min( dd.limit.right, Math.max(dd.limit.left, dd.offsetX))});},{ handle:".guider_title" });
            $('.guider_title').css('cursor', 'move');
         }
      }
      $('.smilesbox').parent().hide();
      tinymce.PluginManager.requireLangPack("kasseler");
      tinymce.create('tinymce.plugins.KasselerCMS', {
            init : function(ed, url) {
               var t = this;
               t.editor = ed;
               ed.addCommand('mceInsertCite', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     ed.execCommand('mceInsertContent', false, insertCite(getLang('citate'),sl));
               });
               ed.addButton('ks_cite', 
                  {title : 'kasseler.citate', 
                     cmd : 'mceInsertCite'
               });
               ed.addCommand('mceInsertSmile', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     id='mce';
                     initPlWindow=function(){
                        if(!smileInited){
                           $('#window_smile').contents().find('img').attr('onclick','');
                           $('#window_smile').contents().on('click','img',function(){
                                 ed.execCommand('mceInsertContent', false, ed.dom.createHTML('img', {src : $(this).attr('src'),alt : $(this).attr('alt'),title : $(this).attr('title'),border : 0}));
                           })
                        }
                        smileInited=true;
                     }
                     if($('#window_smile').length==0) haja({action:'index.php?module=account&do=smiles&id='+id, elm:'cont_previews'+id, animation:false}, {'text':$('#'+id).val()}, {
                           onendload:function(msg){
                              var html=
                              showWindow({id:'window_smile', width:400, title:'Select Smile', description:msg});
                           }
                     }); else guiders.show('window_smile');
               });
               ed.addButton('ks_smile', 
                  {title : 'kasseler.smile', 
                     cmd : 'mceInsertSmile'
               });
               ed.addCommand('mceInsertSpoiler', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     ed.execCommand('mceInsertContent', false, insertSpoiler(getLang('spoiler_hide'),sl));
               });
               ed.addButton('ks_spoiler', 
                  {title : 'kasseler.spoiler', 
                     cmd : 'mceInsertSpoiler'
               });
               ed.addCommand('mceInsertTranslit', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     initPlWindow=function(){
                        if(!translitInited){
                           $('#window_smile').contents().find('img').attr('onclick','');
                           $('#window_smile').contents().on('click','img',function(){
                                 ed.execCommand('mceInsertContent', false, ed.dom.createHTML('img', {src : $(this).attr('src'),alt : $(this).attr('alt'),title : $(this).attr('title'),border : 0}));
                           })
                        }
                        translitInited=true;
                     }
                     id='mce_transl';;
                     showWindow({id:'window_translit', buttons:[{name:'OK', onclick:function(){
                                    ed.execCommand('mceInsertContent', false, $$("cont_frame_translin"+id).contentWindow.document.getElementById("cyr").value);
                                    $('.guider').hide('window_translit');
                           }}], width:530, title:'Translit', description:'<iframe id="cont_frame_translin'+id+'" style="border: 0; height: 380px;" src="includes/popups/translit.html" width="100%"></iframe>'});
               });
               ed.addButton('ks_translit', 
                  {title : 'kasseler.translit', 
                     cmd : 'mceInsertTranslit'
               });
               ed.addCommand('mceInsertFont', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     editor=ed;
                     var id='mce';
                     var el=$('#'+t.editor.settings.id).parent().find('a.mce_ks_font').get(0);
                     $('#font_'+id).css(elmPos(el));
                     $$('font_'+id).style.top = parseInt($$('font_'+id).style.top)+21+'px';
                     show_sel($$('font_'+id), id);
               });
               ed.addButton('ks_font', 
                  {title : 'kasseler.font', 
                     cmd : 'mceInsertFont'
               });
               ed.addCommand('mceInsertSize', function() {
                     var sl=t.editor.selection.getContent({format : 'html'});
                     editor=ed;
                     var id='mce';
                     var el=$('#'+t.editor.settings.id).parent().find('a.mce_ks_size').get(0);
                     $('#size_'+id).css(elmPos(el));
                     $$('size_'+id).style.top = parseInt($$('size_'+id).style.top)+21+'px';
                     show_sel($$('size_'+id), id);
               });
               ed.addButton('ks_size', 
                  {title : 'kasseler.size', 
                     cmd : 'mceInsertSize'
               });
               ed.addCommand('mceInsertCode', function() {
                     var n=ed.selection.getNode();
                     if(n.nodeName == 'CODE'){
                        var code_html=n.innerHTML.replaceAll('<br>',"\n");var codeType=n.className;
                     } else {
                        var code_html="";var codeType='PHP';
                     }
                     ed.windowManager.open({
                           file : url + '/dialog.htm',
                           width : 450 + parseInt(ed.getLang('syntaxhl.delta_width', 0)),
                           height : 360 + parseInt(ed.getLang('syntaxhl.delta_height', 0)),
                           inline : 1
                        }, {
                           plugin_url : url, // Plugin absolute URL
                           data:code_html,
                           typecode:codeType
                     });
               });
               ed.addButton('ks_code', 
                  {title : 'kasseler.code', 
                     cmd : 'mceInsertCode'
               });
               ed.onNodeChange.add(function(ed, cm, n) {
                     cm.setActive('ks_code', n.nodeName == 'CODE');
               });

            },

            getInfo : function() {
               return {
                  longname : 'Insert standart Kassler button',
                  author : 'Browko Dmitrey',
                  authorurl : 'http://kasseler-cms.net',
                  infourl : 'http://kasseler-cms.net',
                  version : tinymce.majorVersion + "." + tinymce.minorVersion
               };
            },

      });
      // Register plugin
      tinymce.PluginManager.add('kasseler', tinymce.plugins.KasselerCMS);
})();