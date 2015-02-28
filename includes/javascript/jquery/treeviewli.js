(function() {jQuery.fn['bounds'] = function (full) {
      var bounds = {
         left: Number.POSITIVE_INFINITY,
         top: Number.POSITIVE_INFINITY,
         right: Number.NEGATIVE_INFINITY,
         bottom: Number.NEGATIVE_INFINITY,
         width: Number.NaN,
         height: Number.NaN
      };
      this.each(function (i,el) {
         var elQ = $(el);
         var off = elQ.offset();
         var flag=(full!=undefined&&full);
         off.right = off.left + (flag?$(elQ).outerWidth():$(elQ).width());
         off.bottom = off.top + (flag?$(elQ).outerHeight():$(elQ).height());
         if (off.left < bounds.left) bounds.left = off.left;
         if (off.top < bounds.top) bounds.top = off.top;
         if (off.right > bounds.right) bounds.right = off.right;
         if (off.bottom > bounds.bottom) bounds.bottom = off.bottom;
      });
      bounds.width = bounds.right - bounds.left;
      bounds.height = bounds.bottom - bounds.top;
      return bounds;
}})();
/*
* Treeview 1.0 - jQuery plugin for tree based tag li ul
* 
* Copyright (c) 2011 Dmitrey Browko
*
* Dual licensed under the MIT and GPL licenses:
*   http://www.opensource.org/licenses/mit-license.php
*   http://www.gnu.org/licenses/gpl.html
*
*/
(function($) {
   var treedefault={selected:false,multiselect:false,togglediv:false,dragdrop:false,dragsrc:''};
   var TDModeDefault={before:false,child:false,after:false};
   // event - (change(),event())
   var dinfo={down:false,x:0,y:0,draged:false};
   $.fn.treeCollapse=function(full){
      if(full) this.find('li:has(ul)').addClass('collapse');
      else this.addClass('collapse');
      return this;
   }
   $.fn.treeExpand=function(full){
      if(full) this.find('li.collapse').removeClass('collapse');
      else this.removeClass('collapse');
      return this;
   }
   $.fn.treeMove=function(dest,mode){ // src - source and == this, dest- destination, mode (c-child,a-after,b-before)
      var $src=$(this.get(0)); if($src.is('span')) $src=$src.parent();
      var $dest=$(dest); if($dest.is('span')) $dest=$dest.parent();
      var psrc=$src.parent();
      if(mode=='c'){
         if($dest.find('ul').length==0) $('<ul/>').appendTo($dest); 
         if($dest.find('div.node').length==0) $dest.find('span:first').before($('<div/>').addClass('node'));
      }
      var ul=$dest.find('ul:first');
      if(mode=='a') $dest.after($src);
      else if(mode=='b') $dest.before($src);
      else $src.appendTo(ul);
      if(psrc.find('li').length==0)  psrc.parent().find('div.node').remove();
   }
   $.fn.setTDMode=function(objSet){// {before:true,child:false,after:true}
      if(this.length!=0){
         var currMode=this.data('dragAccept');
         if(currMode==undefined||objSet==undefined) currMode=TDModeDefault;
         var val = $.extend({},currMode, objSet);
         this.data('dragAccept',val);
      }
      return this;
   }
   $.fn.getTDMode=function(objSet){// {before:true,child:false,after:true}
      return this.data('dragAccept');
   }
   $.fn.treeviewli=function(options){
      var op=this.data('treeinfo');
      var alredy=(op!=undefined);
      if(!alredy){
         op = $.extend({},treedefault, options);
         op.root=this;
         op.prev=null;
         this.data('treeinfo',op);
      } else op = $.extend({},op, options);
      if(op.dragdrop){
         op.spandrags=$.isArray(op.dragsrc)?op.dragsrc:op.root.find('span'+((op.dragsrc!='')?'.'+op.dragsrc:''));
      }
      op.root.find('li:has(ul)').not('li:has(.node)').each(function(i){
         $(this).prepend($('<div/>').addClass('node'));
         if(op.collapsed) $(this).addClass('collapse');
      });
      if(!alredy) {
         op.root.unbind('.treeviewli').bind('click.treeviewli',function(e){
            e=$.event.fix(e);$th=$(e.target);
            var li=$th.parent();
            var ok_click=$th.is('span')||$th.is('div');
            var toggle_click=op.togglediv?$th.is('div'):ok_click;
            if(ok_click){
               if(li.is('ul')) li=li.parent();
               if(op.selected&&$th.is('span')&&li.is('li')) {
                  var span=li.children('span');
                  if(!op.multiselect) op.root.find('span.select').removeClass('select');
                  span.toggleClass('select');
                  if(op.change&&op.prev!=li[0]) {op.prev=li[0];op.change(li[0])}
               }
               if (toggle_click&&li.is('li')&&li.has('ul').length!=0) {
                  var toggle=true;
                  if(op.toggle) toggle=op.toggle(li.get(0),!li.hasClass('collapse'));
                  if(toggle) li.toggleClass('collapse');
               }
            }
            return true;
         });
         function check_out_tree(e){
            var p=op.root.bounds(true);
            var y=e.pageY;
            var x=e.pageX;
            return  (!(x>p.left&&x<p.right&&y>p.top&&y<p.bottom));
         }
         function drag_move(e){
            e=$.event.fix(e);
            var obj=$(e.target);
            var y=e.pageY;
            dinfo.drag.css({left:e.pageX+25+'px',top:e.pageY+5+'px'});
            if(dinfo.aobj==e.target&&e.target!=dinfo.src){
               var ps=obj.bounds(true);
               if((y>=ps.bottom-6)&&(y<=ps.bottom)) inf='a';
               else if((y>=ps.top)&&(y<=ps.top+6)) inf='b';
               else inf='c';
               var drg=obj.getTDMode();
               if(inf=='a'&&drg.after!=true) inf='';
               if(inf=='b'&&drg.before!=true) inf='';
               if(inf==''&&drg.child==true) inf='c';
               dinfo.dragmode=inf;
               var pa=$(dinfo.aobj).parents('li:first');
               if(inf=='a'&&(pa.hasClass('after')||pa.next().children('span:first').get(0)==dinfo.src)) inf='';
               if(inf=='b'&&(pa.hasClass('before')||pa.prev().children('span:first').get(0)==dinfo.src)) inf='';
               var span=pa.find('span:first');
               if(inf!=''&&(!span.hasClass('seldrag'))) span.addClass('selafbe');
               if(inf!='') dinfo.okdrag=true;
               switch(inf){
                  case 'a':
                  if(!pa.next().hasClass('before')) {
                     op.root.find('.before').not(pa).removeClass('before').find('span:first').removeClass('selafbe');
                     pa.removeClass('before').addClass('after');
                  }  else pa.next().find('span:first').removeClass('selafbe');
                  break;
                  case 'b':
                  if(!pa.prev().hasClass('after')) {
                     op.root.find('.after').not(pa).removeClass('after').find('span:first').removeClass('selafbe');
                     pa.removeClass('after').addClass('before');
                  }  else pa.prev().find('span:first').removeClass('selafbe');
                  break;
                  case 'c':
                     op.root.find('.before,.after').removeClass('before').removeClass('after');
                     op.root.find('.selafbe').removeClass('selafbe');
                     break;
               }
            };
         }
         function drag_stop(){
            dinfo.down=false;
            dinfo.draged=false;
            if(dinfo.drag) dinfo.drag.remove();
            if(dinfo.dragmode=='c') var dest=op.root.find('.seldrag');
            else {
               var dest=dest=op.root.find('.selafbe'); 
               if(dest.length==0) dest=op.root.find('.after,.before').find('span:first');
            }
            if(dinfo.okdrag&&dest.length!=0){
               dinfo.dest=dest.get(0);
               $(dinfo.src).treeMove(dest.get(0),dinfo.dragmode);
               if(op.event) op.event('drag stop',dinfo);
            }
            op.root.find('span').css('cursor','pointer').unbind('.dragtree');
            op.root.find('.seldrag').removeClass('seldrag');
            op.root.find('.before,.after').removeClass('before').removeClass('after');
            op.root.find('.selafbe').removeClass('selafbe');
            dinfo.okdrag=false;
         }
         function drag_start(){
            dinfo.rect=op.root.bounds();
            if(op.event) op.event('drag start',dinfo);
            op.root.find('span').bind('mouseover.dragtree',function(){
               var obj=$(this);
               var drg=obj.getTDMode();
               if(drg&&drg.child) $(this).addClass('seldrag');
               if(drg&&(drg.child||drg.after||drg.before)) {
                  dinfo.okdrag=true;
                  dinfo.aobj=this;
               } else dinfo.okdrag=false;
            })
            .bind('mouseout.dragtree',function(){
               $(this).removeClass('seldrag');
               var mode=$(this).getTDMode();
               if(!(mode.before||mode.after)){
                  dinfo.rect=$(this).bounds();
                  dinfo.okdrag=false; 
                  dinfo.aobj=null;
               }
            });
         }
         if(op.dragdrop){
            op.spandrags.bind('mousedown.treeviewli',function(e){
               e=$.event.fix(e);
               dinfo.down=true;
               var html = document.documentElement;
               var body = document.body;
               dinfo.x=e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
               dinfo.y=e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
               dinfo.src=e.target;
               return false;
            });
            op.root.bind('mousemove.treeviewli',function(e){
               e=$.event.fix(e);
               if (dinfo.down&&((Math.abs(e.clientX-dinfo.x)>10)||(Math.abs(e.clientY-dinfo.y)>10))) {
                  obj=dinfo.src;
                  dinfo.down=false;dinfo.draged=true;dinfo.okdrag=false;
                  pobj=$(obj).parent();
                  op.root.find('span').setTDMode({before:true,after:true,child:true});
                  $(obj).parents('li:eq(1)').find('span:first').setTDMode({before:true,after:true,child:false}); //-parent child
                  e.pageX=dinfo.x;e.pageY=dinfo.y;
                  dinfo.drag=$(obj).clone().css({position:'absolute'});
                  dinfo.drag.appendTo(op.root).removeClass('select');
                  drag_start();
                  $(obj).setTDMode();// - this
                  pobj.prev().setTDMode({after:false});
                  pobj.next().setTDMode({before:false});
                  pobj.find('span').setTDMode();// - this children
                  return false;
               }
               if(dinfo.draged){drag_move(e);return false;}
            }).bind('mouseout.treeviewli',function(e){
               e=$.event.fix(e);
               if(check_out_tree(e)){
                  op.root.find('.seldrag').removeClass('seldrag');
                  op.root.find('.before,.after').removeClass('before').removeClass('after');
                  op.root.find('.selafbe').removeClass('selafbe');
               }
            });
            $(document).bind('mouseup.treeviewli',function(e){
               e=$.event.fix(e);
               if(check_out_tree(e)) dinfo.okdrag=false;
               drag_stop();
            });
         }
      }
      return this;
   }
})(jQuery);