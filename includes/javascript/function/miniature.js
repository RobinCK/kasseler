function miniature_click(obj){
   var p=$(obj).parents('div[id]:first');
   var idg=p.attr('id');
   p.find('a:has(img)').attr('onclick','return false;').addClass(idg).css('cursor','pointer');
   $('a.'+idg).colorbox({rel: idg, transition:"fade",maxHeight:window.innerHeight,maxWidth:window.innerWidth});
   return false;
}