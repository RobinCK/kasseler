function jsDebug(){
   function var_debug(obj, left, fixlevel, level){
      if(typeof level=='undefined') level = 1;
      if(typeof fixlevel=='undefined') level = 2;
      var ret = '';
      if(level<=fixlevel && typeof obj =='object'){
         for(var i in obj){
            var tpo = typeof obj[i];
            if(tpo=='object') {
               var robj = var_debug(obj[i], left + '  ', fixlevel, level+1);
               if(robj!=='') robj = "{\n"+robj+"\n"+left+"}";
               ret = ret + left + i +' of '+typeof obj[i]+robj+"\n";
            } else {
               if(tpo=='string'||tpo=='numeric') value = obj[i];
               else value = "";
               ret = ret + left + i +' of '+typeof obj[i]+" ('"+value+"')\n";
            }
         }
      }
      return ret;
   }
   function view(text){
      if(typeof text=='undefined') text = '';
      if($('#debug').length==0){
         var p = $('<pre/>',{id:'debug'}).css('text-align','left').text(text);
         p.appendTo('body');
      } else $('#debug').text(text);
   }
   function view_debug(obj, fixlevel){
      if(typeof fixlevel=='undefined') level = 2;
      view(var_debug(obj, '', fixlevel));
   }
   this.view = view;
   this.debug = view_debug;
   this.printStackTrace = function() {
      var callstack = [];
      var isCallstackPopulated = false;
      try {
         i.dont.exist+=0; //does not exist - that's the point
      } catch(e) {
         if(typeof e.stack!=='undefined') { //Firefox
            var lines = e.stack.split("\n");
            for (var i = 0, len = lines.length; i < len; i++) {
               callstack.push(lines[i]);
            }
            callstack.shift();
            isCallstackPopulated = true;
         }
         else if (window.opera && e.message) { //Opera
            var lines = e.message.split("\n");
            for (var i = 0, len = lines.length; i < len; i++) {
               if ( lines[i].match( /^\s*[A-Za-z0-9\-_\$]+\(/ ) ) {
                  var entry = lines[i];
                  if (lines[i+1]) {
                     entry += " at " + lines[i+1];
                     i++;
                  }
                  callstack.push(entry);
               }
            }
            callstack.shift();
            isCallstackPopulated = true;
         }
      }
      if (!isCallstackPopulated) { //IE and Safari
         var currentFunction = arguments.callee.caller;
         while (currentFunction) {
            var fn = currentFunction.toString();
            //If we can't get the function name set to "anonymous"
            var fname = fn.substring(fn.indexOf("function") + 8, fn.indexOf("(")) || "anonymous";
            callstack.push(fname);
            currentFunction = currentFunction.caller;
         }
      }
      return callstack;
   }
}
var jdebug = new jsDebug();
