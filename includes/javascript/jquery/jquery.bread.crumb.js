/**
* .breadCrumb - Create Bread Crumb Plugin
*
* Version: 1.0
* Updated: 2012-01-17
*
* Copyright Kasseler CMS: Content Management System
* Copyright (c) 2012 Browko Dmitrey (browko@gmail.com, http://kasseler-cms.net/)
*
* Dual licensed under the MIT (MIT-LICENSE.txt)
* and GPL (GPL-LICENSE.txt) licenses.
**/
function mouseLayerXY(e){
   if (!e) {e = window.event; e.target = e.srcElement}
   var x = 0;var y = 0;
   if(e.originalEvent){ //Gecko
      x = e.originalEvent.layerX - parseInt($(e.target).css("border-left-width"));
      y = e.originalEvent.layerY - parseInt($(e.target).css("border-top-width"));
   } else if (e.offsetX!=undefined){//IE, Opera
      x = e.offsetX;y = e.offsetY;
   }
   return {"x":x, "y":y};
}
var brCrumbDefault={speed:600,xrend:50};
function initBreadCrumb($Obj,options){
   var op = $.extend({},brCrumbDefault, options);
   function scroll_bred(obj,mode){
      var l=obj.scrollLeft();
      var w=obj.children('div').width();
      if((mode<0&&l>0)||(mode>0&&l<(w-pWidth))){
         var ns=(mode<0&&l>0)?0:w-pWidth;
         var n=Math.abs(ns-l);
         var nspeed=n*op.speed/100;
         obj.animate({scrollLeft:ns},nspeed);
      }
   }
   function get_mode(e,obj){
      var mp=mouseLayerXY(e);
      var l=obj.scrollLeft();
      if($.browser.mozilla) mp.x=mp.x-l;
      return (mp.x<op.xrend&&l>0)?-1:((mp.x>(pWidth-op.xrend))?1:0);
   }
  function parentWidth(){
     var p=$src.parent();
     var wmin=10000;
     while(p.length!=0){ 
       if(p.width()!=0&&p.width()<wmin) wmin=p.width();
       p=p.parent();
     }
      return wmin;
   }
   function updateWidth(){
      pWidth=parentWidth();
      $src.width(pWidth);
      if(pWidth!=parentWidth()) $src.width(parentWidth());
      var nScroll=$divcs.width()-pWidth;
      if(nScroll<0) nScroll=0;
      if(nScroll!=0) $src.removeClass('rightcrumbs').addClass('leftcrumbs');
      $src.scrollLeft(nScroll);
   }
   function reInitWidth(){
      $src.width(1);
      setTimeout(function(){updateWidth();},10);
   }
   if(!$Obj.hasClass('srcbreadcumb')){
      $Obj.wrap($('<div/>')).after($('<p/>'));
      var $src=$Obj.parent().addClass('srcbreadcumb');
   } else $src=$Obj;
   $divcs=$src.children('div:first');
   var pWidth=parentWidth();
   $src.width(1);
   setTimeout(function(){updateWidth();},10);
   $(window).bind("resize", reInitWidth);
   $divcs.show();
   $('body').bind('mousemove',function(e){
         e=$.event.fix(e);
         if(($(e.target).closest('.srcbreadcumb').length==1)&&$src.queue('fx').length!=0){
            if(get_mode(e,$src)==0) $src.stop(true);
         }
   });
   $src.bind('mousemove',function(e){
         e=$.event.fix(e);
         var modeScroll=get_mode(e,$(this));
         var animate=$(this).queue('fx');
         if((modeScroll!=0)&&(animate.length==0)) scroll_bred($src,modeScroll);
   });
}
(function($){
      $.fn.breadCrumb=function(options){// init div source crumbs <div class='srcbreadcumb'><div class='srccrumbs' style='display:none;'><a href='...'>first</a>....</div><p></p></div>
         for(i=0;i<this.length;i++) initBreadCrumb(this.eq(i),options);
         return this;
      }
   }
)(jQuery)
