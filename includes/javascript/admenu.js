//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2009 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
var def_menu = $$('menu_js').innerHTML;
var def_ac;
var timeout;
var ac_clear = function(){for (var b in menu) $$(b).className = '';}
var mouseout = function(){
    $$('menu_js').innerHTML = def_menu;
    ac_clear();
    if(def_ac) def_ac.className = 'ac';
}
for (var d in menu)  if($$(d).className=='ac')  def_ac = $$(d);
$$('menuheadline').onmouseover = function(){clearTimeout(timeout);}
$$('menuheadline').onmouseout = function(){timeout = setTimeout(mouseout, 100);}
for (var i in menu){
    if($$(i)){
        $$(i).onmouseover = function(){
            ac_clear();
            $$(this.id).className = 'ac';
            clearTimeout(timeout);
            var links = '';
            for(var y=0;y<menu[this.id].length;y++) links += '<a href="'+menu[this.id][y][1]+'">'+menu[this.id][y][0]+'</a>';
            $$('menu_js').innerHTML = links;
        }
        $$(i).onmouseout = function(){timeout = setTimeout(mouseout, 100);}
    }
}