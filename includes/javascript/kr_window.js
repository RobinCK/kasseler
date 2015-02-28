//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2012 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
var win_timer;

(function($_){$_.extend($_, {
            set:{
               opacity:function(elm, opacity){$(elm).css({opacity : opacity/100});},
               atrib:function(a, el){for (var i in a) el[i] = a[i];}
            },

            kr_window:{
               version:'1.0',
               wns:new Array(),
               design:'includes/javascript/templates/kasseler',
               ef:function() {},

               window:{
                  height:function(){return (window.innerHeight ? window.innerHeight:(document.documentElement.clientHeight ? document.documentElement.clientHeight:document.body.offsetHeight))},
                  width:function(){return (window.innerWidth ? window.innerWidth:(document.documentElement.clientWidth ? document.documentElement.clientWidth:document.body.offsetWidth));},
                  docheight:function(){return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;},
                  docwidth:function(){return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;},

                  scrollY:function(){
                     var scrollY = 0;
                     if(document.documentElement && document.documentElement.scrollTop) scrollY = document.documentElement.scrollTop;
                     else if(window.pageYOffset) scrollY = window.pageYOffset;
                     else if(window.scrollY) scrollY = window.scrollY;
                     else if(document.body && document.body.scrollTop) scrollY = document.body.scrollTop;
                     return scrollY;
                  },

                  scrollX:function(){
                     var scrollX = 0;
                     if(document.documentElement && document.documentElement.scrollLeft) scrollX = document.documentElement.scrollLeft;
                     else if(window.pageXOffset) scrollX = window.pageXOffset;
                     else if(window.scrollX) scrollX = window.scrollX;
                     else if(document.body && document.body.scrollLeft) scrollX = document.body.scrollLeft;
                     return scrollX;
                  }
               },

               init:function(id, width, height, top, left, options){
                  this.effect.fone.init();
                  KR_AJAX.kr_window.onstartcreate();
                  if(options.fone==true) this.effect.fone.show();
                  if($$(id)) {
                     $$(id).style.display='block';
                     $_.kr_window.onendcreate();
                     return false;
                  }
                  if(options.design) $_.kr_window.design = options.design;
                  if(options.buttons) buttons_ck = true;
                  else  buttons_ck = false;
                  $_.kr_window.wns[id] = {id:id, width:width, height:height, options:options, left:0, top:0, flag:0, buttons:buttons_ck};
                  $_.kr_window.add.window(id, width, height, options);
                  setTimeout("KR_AJAX.kr_window.update('"+id+"')", 10);
                  if(!top || !left) $_.kr_window.set.center({id:id, width:width, height:height});
                  else $_.kr_window.set.position($$(id), left+'px', top+'px');
                  window.foneHide = false;
                  $_.kr_window.onendcreate();
                  $_.kr_window.set.nulled();
               },

               nosel:function(w){
                  if(!$$('no_select'+w.id)) 
                     var e = $('body').append($('<input>').attr({id:'no_select'+w.id, disabled:true}).css('display', 'none'));            
                  else var e = $$('no_select'+w.id);
                  try {e.focus()} catch(e) {}
               },

               add:{
                  window:function(id, width, height, options){
                     var e = $('<div></div>').attr({id:id, 'class':'kr_window'}).css({width:width+'px', height:height+'px', position:'absolute', zIndex:99, display:(options.showEfect==true)?'none':''});
                     $('body').append(e);
                     if(options.showEfect==true){
                        KR_AJAX.set.opacity(e, 0);
                        e.css({display:''});
                        for(var i=1;i<=200;i++) setTimeout("KR_AJAX.set.opacity('"+e.id+"', "+(i/2)+")", 10*i);
                     }
                     var w_but='';
                     if(options.buttons){
                        w_but='<table align="center"><tr>';
                        for (var i in options.buttons){
                           w_but += "<td><a class='button' href='#' ";
                           for (var y in options.buttons[i].options){
                              w_but+=y+"='"+options.buttons[i].options[y]+"'";
                           }
                           w_but += ">"+options.buttons[i].title+"</a></td>";
                        }
                        w_but += "</tr></table>";
                     }
                     if(w_but) w_but = "<div class='kwbase' align='center'>"+w_but+"</div>";
                     var clos = (options.close) ? "<a id='close_bottom_"+id+"' href='#' onclick=\"KR_AJAX.kr_window.close('"+id+"', true); return false;\" class='close' title='Close'><img src='includes/images/pixel.gif' alt='' border='0' /></a>":"";
                     var maximize = (options.maximize) ? "<a id='maximize_bottom_"+id+"' href='#' onclick=\"KR_AJAX.kr_window.maximize('"+id+"'); return false;\" class='fullscreen' title='Maximize'><img src='includes/images/pixel.gif' alt='' border='0' /></a>":"";
                     var minimize = (options.minimize) ? "<a id='minimize_bottom_"+id+"' href='#' onclick=\"KR_AJAX.kr_window.minimize('"+id+"'); return false;\" class='svern' title='Minimize'><img src='includes/images/pixel.gif' alt='' border='0' /></a>":"";
                     var content = '';
                     if(options.content && options.content.length>0) content = options.content;
                     else content = (options.iframe) ? '<iframe id="cont_frame_'+id+'" src="'+options.iframe+'" width="100%" style="height:'+(height-25)+'px; border: 0px;"></iframe>':'';
                     e.html("<div class='kaswindow' id='win_div'><table cellpadding='0' cellspacing='0' class='table_win' style='height: 100%; width: 100%;' id='table_w_"+id+"'><tr id='tw_"+id+"'><td class='top_left'></td><td style='height: 20px; clear: left;'><div class='kwheading'><span>"+clos+maximize+minimize+"</span><strong><img src='includes/images/pixel.gif' alt='Kasseler CMS' /></strong><b id='title_"+id+"' class='win_title'>"+options.title+"</b></td><td class='top_right'></td></tr><tr class='win_source'><td></td><td valign='top' height='100%'><div class='kwbase'><div class='kwbaseinn' style='height: "+height+"px;' id='cont_"+id+"'>"+content+"</div></div>"+w_but+"<b class='kwborder'></b></td><td id='e-resize_"+id+"'><img src='includes/images/pixel.gif' alt='' width='1' height='1' /></td></tr><tr><td class='bottom_left'></td><td style='height:5px;' id='s-resize_"+id+"'><img src='includes/images/pixel.gif' alt='' width='1' height='1' /></td><td class='bottom_right' id='nw-resize_"+id+"'><img src='includes/images/pixel.gif' alt='' width='1' height='1' /></td></tr></table></div>");
                     $_.kr_window.add.event($$("tw_"+id), id, options.mooved, options.resize);
                  },

                  event:function(e, id, mooved, resize){
                     if(mooved==true) $(e).css({cursor:'move'});
                     if(resize==true){
                        $("#nw-resize_"+id).css({cursor:'nw-resize'});
                        $("#s-resize_"+id).css({cursor:'s-resize'});
                        $("#e-resize_"+id).css({cursor:'e-resize'});
                        var omd = function(e){
                           if($_.kr_window.wns[id].flag!=0) return false;
                           ps = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
                           var win = $$(id);
                           var s = {w:parseInt(win.style.width), h:parseInt(win.style.height)};
                           var t = this.id;
                           $_.kr_window.effect.moved($$(id), $$("cont_"+id));
                           document.onmousemove = function(e){
                              $_.kr_window.nosel($$(id));
                              $_.kr_window.onresize();
                              p = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
                              if(t.indexOf('e-resize')!=-1){
                                 if(200<s.w+(p.x-ps.x)) {
                                    $(win).css({width:(s.w+(p.x-ps.x))+'px'});
                                    $('#win_div').css({width:(s.w+(p.x-ps.x))+'px'});
                                 }
                              } else if(t.indexOf('s-resize')!=-1){
                                 if(25<s.h+(p.y-ps.y)){
                                    $(win).css({height:(s.h+(p.y-ps.y))+'px'});
                                    $("#table_w_"+id).css({height:(s.h+(p.y-ps.y))+'px'});
                                    $("cont_"+id).css({height:(s.h+(p.y-ps.y))+'px'});
                                 }      
                              } else {
                                 if(200<s.w+(p.x-ps.x)) {
                                    $(win).css({width:(s.w+(p.x-ps.x))+'px'});
                                    $('#win_div').css({width:(s.w+(p.x-ps.x))+'px'});
                                 }
                                 if(25<s.h+(p.y-ps.y)){
                                    $(win).css({height:(s.h+(p.y-ps.y))+'px'});
                                    $("#table_w_"+id).css({height:(s.h+(p.y-ps.y))+'px'});
                                    $("#cont_"+id).css({height:(s.h+(p.y-ps.y))+'px'});
                                 }
                              }
                           }
                           document.onmouseup = function(){
                              $_.kr_window.effect.stoped($$(id), $$("cont_"+id));
                              document.onmousemove = $_.kr_window.ef
                              document.onmouseup = $_.kr_window.ef
                           }
                        }
                        $("#e-resize_"+id+",s-resize_"+id+",nw-resize_"+id).on('onmousedown',omd);
                     }
                     $$(id).onmousedown = function(){
                        var twn=$$(id);
                        $_.kr_window.onfocus();
                        for (var i in $_.kr_window.wns){
                           if(!$.isFunction($_.kr_window.wns[i])){
                              var wn=$_.kr_window.wns[i].id;
                              if(wn!=id){
                                 wn=$$($_.kr_window.wns[i].id);
                                 $(wn).css({zIndex:99}).find('.win_title').removeClass('active').addClass('noactive');
                              }
                           }
                        }
                        $(twn).css({zIndex:100}).find('.win_title').removeClass('noactive').addClass('active');
                     }
                     e.onmousedown = function(e){
                        var $id=$('#'+id);
                        ps = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
                        x = ps.x - parseInt($$(id).style.left);
                        y = ps.y - parseInt($$(id).style.top);
                        if(mooved==true) win_timer = setTimeout(function(){$_.kr_window.effect.moved($$(id), $$("cont_"+id))}, 500);
                        document.onmousemove = (mooved==true) ? function(e){
                           if($_.kr_window.wns[id].flag==3) return false;
                           if(mooved==true) $_.kr_window.effect.moved($$(id), $$("cont_"+id));
                           $_.kr_window.nosel($$(id));
                           p = ($.browser.mozilla) ? {x:e.pageX, y:e.pageY}:{x:event.clientX, y:event.clientY};
                           $_.kr_window.onmove();
                           $_.kr_window.set.position($$(id), (p.x+$id.width()-x<KR_AJAX.kr_window.window.width()-2) ? (p.x-x)+'px':(KR_AJAX.kr_window.window.width()-parseInt($id.width()-2))+'px', (p.y-y)+'px');
                        }:$_.kr_window.ef;
                        document.onmouseup = function(){
                           clearTimeout(win_timer);
                           $_.kr_window.effect.stoped($$(id), $$("cont_"+id));
                           document.onmousemove = $_.kr_window.ef
                           document.onmouseup = $_.kr_window.ef
                        }
                     }
                  }
               },

               update:function(id){
                  var $win=$('#'+id);
                  if($win.length>0){
                     var wns = $_.kr_window.wns[id];
                     if(wns.flag==0){
                        var pos =$win.offset();
                        $_.kr_window.wns[id] = {id:id, width:$win.width(), height:$win.height(), options:wns.options, left: pos.left, top:pos.top, flag:wns.flag};
                     }
                     //setTimeout("KR_AJAX.kr_window.update('"+id+"')", 10);
                  }
               },

               close:function(id, offdialog){
                  var wns = $_.kr_window.wns[id];
                  if(offdialog!=true){
                     if(confirm(window.js_lang.real_close)){
                        this.effect.fone.hide();
                        $('#'+id).hide();
                        $_.kr_window.onclose();
                        if(wns.options.onclose) wns.options.onclose();
                     } else return false;
                  } else {
                     this.effect.fone.hide();
                     $('#'+id).hide();
                     $_.kr_window.onclose();
                     if(wns.options.onclose) wns.options.onclose();
                  }
               },

               minimize:function(id){
                  var wns = $_.kr_window.wns[id];
                  var $win = $('#'+wns.id);
                  if(wns.flag==3) {$_.kr_window.restorewin(id); return false;}
                  if(wns.flag==1)$_.kr_window.restorewin(id);
                  else {
                     wns.pos=$win.position();
                     $win.find('.win_source').hide(1000,function(){$win.css({position:'fixed',top:$(window).height()-$win.height()});});
                     wns.flag = 1;
                  }
               },
               restorewin:function(id){
                  var corect = $.browser.msie ? 10 : 15;
                  var wns = $_.kr_window.wns[id];
                  var $win = $('#'+wns.id);
                  if(wns.flag==3){
                     $_.kr_window.effect.moved($$(id));
                     var res = {left:wns.left, top:wns.top, bottom: $win.height()-wns.height, right: $win.width()-wns.width+corect};
                     $_.kr_window.effect.maximize(id,res.left,res.top,res.bottom,res.right, 'normal');
                     $_.kr_window.effect.stoped('#'+id, 'cont_'+id);
                     wns.flag=0;document.body.overflow = '';
                  }
                  if(wns.flag==1){
                     $win.css({position:'absolute',top:wns.pos.top, left:wns.pos.left});
                     $win.find('.win_source').show(1000);
                     wns.flag = 0;
                  }
               },
               maximize:function(id){
                  var i, y, speed=50;
                  var corect = $.browser.msie ? 10 : 15;
                  var wns = $_.kr_window.wns[id];
                  var $win = $('#'+wns.id);
                  if(wns.flag==1) {$_.kr_window.restorewin(id); return false;}
                  if(wns.flag==0){
                     $_.kr_window.effect.moved($$(id));
                     scroll(0,0);
                     var res = {left:wns.left-10, top:wns.top-10, bottom:KR_AJAX.kr_window.window.height()-wns.height-20, right:KR_AJAX.kr_window.window.width()-wns.width-20-corect};
                     $_.kr_window.effect.maximize(id,res.left,res.top,res.bottom,res.right, 'full');
                     $_.kr_window.effect.stoped('#'+id, 'cont_'+id); document.body.overflow = 'hidden';
                     wns.flag=3;
                  } else $_.kr_window.restorewin(id);
               },

               effect:{
                  moved:function(e, eC){$(e).css({opacity: 0.8, visibility:''});},
                  stoped:function(e, eC){$(e).css({opacity: 1, visibility:''});},
                  maximize:function(id, left, top, bottom, right, cl){
                     var welm=$_.kr_window.wns[id];
                     var $welm=$('#'+welm.id);
                     if(cl=='full'){
                        var css_temp={top:(welm.top-top)+'px', left:(welm.left-left)+'px', width:(welm.width+right)+'px', height:(welm.height+bottom)+'px'};
                        $welm.css(css_temp);
                     } else {
                        var krw=KR_AJAX.kr_window.window;
                        var css_temp={height:(krw.height()-20-bottom)+'px', width:(krw.width()-20-right)+'px', top:top+'px', left:left+'px'};
                        $welm.css(css_temp);
                     }
                  },

                  fone:{
                     init:function(){
                        var layer = $('<div></div>').attr({id:'window_layer', 'class':'fone_ajax'}).css('display', 'none');
                        $('body').append(layer);
                        layer.height(((document.body.scrollHeight>=KR_AJAX.kr_window.window.height())?document.body.scrollHeight:KR_AJAX.kr_window.window.height())+'px');
                        $_.set.opacity(layer.get(0), 60);
                        this.update();
                     },

                     show:function(loader){if($$('window_layer')) $$('window_layer').style.display = 'block';},
                     hide:function(){if($$('window_layer')) $$('window_layer').style.display = 'none';},

                     update : function(){
                        if($$('window_layer')){
                           w_height = (document.body.scrollHeight>$_.kr_window.window.height()) ? document.body.scrollHeight : $_.kr_window.window.height();
                           $$('window_layer').style.height = w_height+'px';
                           setTimeout("KR_AJAX.kr_window.effect.fone.update()", 1);
                        }
                     }
                  }
               },

               set:{
                  ext:function(methods){for (var i in methods) $[i] = methods[i];},
                  position:function(e, x, y){$(e).css({left:x, top:y});},
                  nulled:function(){$_.kr_window.onendcreate = $_.kr_window.onstartcreate = $_.kr_window.onclose = $_.kr_window.onfocus = $_.kr_window.onresize = $_.kr_window.onminimize = $_.kr_window.onmaximize = $_.kr_window.onmove = function(){}},
                  center:function(e){var krw=KR_AJAX.kr_window.window; $('#'+e.id).css({left:((krw.width()-e.width)/2)+'px', top:(krw.scrollY()+(krw.height()/3)-e.height/3)+'px'});}
               }
            }
      })
})(KR_AJAX);

KR_AJAX.kr_window.set.nulled();