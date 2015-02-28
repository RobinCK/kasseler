(function($) {
      var kr_time_lang={hours:'Hour',minutes:'Minute'};
      var lastdiv = null;
      function tval(val){return val<10?'0'+val:val;}
      function kr_time_hours(from, to){
         var d=$('<div/>');
         for(var i=from;i<=to;i++){$('<div/>',{'class':'kr_hour'}).append($('<a/>').text(tval(i))).appendTo(d);}
         return d;
      }
      function kr_time_min(minutes){
         var d=$('<div/>');
         for(var i=0;i<minutes.length;i++){$('<div/>',{'class':'kr_hour'}).append($('<a/>').html(tval(minutes[i]))).appendTo(d);}
         return d;
      }
      function add_minutes(parent, minutes){$('<tr/>').append($('<td/>').append(kr_time_min(minutes))).appendTo(parent);}
      function hide_all_time(){$('.kr_time:visible').hide(300).find('.set').removeClass('set');lastdiv = null;}
      function hook_body(){
         $('body').on('click.kr_time',function(e){
               if(lastdiv!=null)  if(e.pageX<lastdiv.left||e.pageX>lastdiv.right||e.pageY<lastdiv.top||e.pageY>lastdiv.bottom) hide_all_time();
         });
      }
      $.fn.extend({ 
            kr_time: function(option){
               var obji=this;
               var lang = kr_time_lang;
               var main = $('<div/>').addClass('kr_time').css({position:'absolute', display:'none', 'z-index':'500'});
               main.appendTo('body');
               var tblh = $('<table/>').addClass('kr_hour_table');
               $('<tr/>').append($('<td/>',{colspan:2}).append($('<div/>',{'class':'head b',text:lang.hours}))).appendTo(tblh);
               $('<tr/>').append($('<td/>',{rowspan:2,'class':'b',text:'AM'})).append($('<td/>').append(kr_time_hours(0,5))).appendTo(tblh);
               $('<tr/>').append($('<td/>').append(kr_time_hours(6,11))).appendTo(tblh);
               $('<tr/>').append($('<td/>',{rowspan:2,'class':'b',text:'PM'})).append($('<td/>').append(kr_time_hours(12,17))).appendTo(tblh);
               $('<tr/>').append($('<td/>').append(kr_time_hours(18,23))).appendTo(tblh);
               tblh.appendTo(main);

               var tblm = $('<table/>').addClass('kr_minute_table');
               $('<tr/>').append($('<td/>',{colspan:2}).append($('<div/>',{'class':'head b',text:lang.minutes}))).appendTo(tblm);
               var gmin=[[0,5,10],[15,20,25],[30,35,40],[45,50,55]];
               for(var i=0;i<gmin.length;i++) add_minutes(tblm, gmin[i]);
               tblm.appendTo(main);
               obji.on('focus',function(){
                     var pos = obji.offset(false);
                     main.css({top: pos.top+obji.outerHeight(),left:pos.left, opacity:'0'}).show().animate({opacity:'1'},700,function(){
                           var p = main.offset();
                           lastdiv ={left:p.left,right:p.left+main.outerWidth(),top:p.top,bottom:p.top+main.outerHeight()};
                     });
               });
               main.on('click','a',function(){
                     $(this).parents('table:first').find('a.select').not(this).removeClass('select set');
                     $(this).addClass('select set');
                     if(main.find('.set').length>1){
                        main.hide(300);lastdiv = null;
                        var h = tblh.find('.set').text();
                        var m = tblm.find('.set').text();
                        obji.val(h+':'+m+':00');
                        main.find('.set').removeClass('set');
                     }
               });
               hook_body();
               return this;
            },
      });
})(jQuery);
