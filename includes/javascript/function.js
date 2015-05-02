//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2012 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
KR_AJAX.ifunction['isReady'] = function(){KR_AJAX.isReady = KR_AJAX.loadCount==0 ? true : false;}
jQuery.krReady = function(a){if(document.readyState==='complete') {if(KR_AJAX.isReady) setTimeout(function(){a.call()}, 100); else setTimeout(function(){$.krReady(a)}, 100);} else jQuery(document).ready(a);};

(function(a){a.fn.autoResize=function(b){var c=a.extend({onResize:function(){},animate:true,animateDuration:150,animateCallback:function(){},extraSpace:20,limit:1000},b);this.filter("textarea").each(function(){var e=a(this).css({resize:"none","overflow-y":"hidden"}),g=e.height(),h=(function(){var i=["height","width","lineHeight","textDecoration","letterSpacing"],j={};a.each(i,function(k,l){j[l]=e.css(l)});return e.clone().removeAttr("id").removeAttr("name").css({position:"absolute",top:0,left:-9999}).css(j).attr("tabIndex","-1").insertBefore(e)})(),f=null,d=function(){h.height(0).val(a(this).val()).scrollTop(10000);var j=Math.max(h.scrollTop(),g)+c.extraSpace,i=a(this).add(h);if(f===j){return}f=j;if(j>=c.limit){a(this).css("overflow-y","");return}c.onResize.call(this);c.animate&&e.css("display")==="block"?i.stop().animate({height:j},c.animateDuration,c.animateCallback):i.height(j)};e.unbind(".dynSiz").bind("keyup.dynSiz",d).bind("keydown.dynSiz",d).bind("focus.dynSiz",d).bind("update.dynSiz",d).bind("change.dynSiz",d).trigger("update.dynSiz")});return this}})(jQuery);

dcc = {};var varloc={};

var lat2 = ["JO","SCH","ZH","CH","SH","JE","JU","JA","jo","sch","zh","ch","sh","je","ju","ja", "ya"];
var cyr2 = ["Ё", "Щ",  "Ж", "Ч", "Ш", "Э", "Ю", "Я", "ё", "щ", "ж", "ч", "ш",  "э", "ю", "я", "я"];
var lat = ["A", "B", "V", "G", "D", "E", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "H", "C", "``", "Y", "''", "a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "`","y", "'"];
var cyr = ["А", "Б", "В", "Г", "Д", "Е", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ъ", "Ы", "Ь", "а", "б", "в", "г", "д", "е", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ъ", "ы", "ь"];

var lat2_rewrite = ["JO","SCH","ZH","CH","SH","JE","JU","JA","jo","sch","zh","ch","sh","je","ju","ja", "ya", "-", "", "", "_", "_", "_", "", "", "", "", "_"];
var cyr2_rewrite = ["Ё", "Щ",  "Ж", "Ч", "Ш", "Э", "Ю", "Я", "ё", "щ", "ж", "ч", "ш",  "э", "ю", "я", "я", " ", "'", '"', '\\(', '\\)', '\\/', '\\\\', '\\[', '\\]', '\\{', '\\}',];
var lat_rewrite = ["A", "B", "V", "G", "D", "E", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "H", "C", "", "Y", "", "a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "","y", "", 'i', 'j', 'e'];
var cyr_rewrite = ["А", "Б", "В", "Г", "Д", "Е", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ъ", "Ы", "Ь", "а", "б", "в", "г", "д", "е", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ъ", "ы", "ь", 'і', 'ї', 'є'];

var kr_events=[], codeInterval, CheckON='on', CheckOFF='off', upload1, small_load = "<img src='includes/images/loading/small.gif' alt='Loading...'>";

function syntaxConfig(){codeInterval=setInterval(function(){sb=$(".syntaxhighlighter").not(".syntaxconfig");if(sb.length>0){sb.each(function(){pw=$(this).parent().width()-2;$(this).parent().css({width:pw});$(this).css({width:pw}).addClass("syntaxconfig").show();ph=$(this).parent().height();$(this).parent().addClass("syntaxBorder").attr("title",ph+20)});clearInterval(codeInterval)}},100)}
function show_code(a){a=$(a).nextAll("div:first");a.slideToggle('slow');return false}
function displays(id, values){if($$(id)) $$(id).style.display = values;}
function addEvent(obj, name, handler){if (obj.attachEvent) obj.attachEvent('on' + name, handler); else obj.addEventListener(name, handler, false);}
function is_voted(table, id){var str = getCookie("rating"); return (str && str.indexOf(table+id+',') != -1 && str!='') ? 1 : 0;}
function datacase(d, m, y, start_year, l_d, l_m, l_y){addEvent(window, 'load', function(){create_datacase(d, m, y, start_year, l_d, l_m, l_y)});}
function id(elm){return (typeof elm === "string") ? $$(elm) : elm;}
function update_ajax(url, id, lang){if(confirm(lang)) ajaxed({action : url, animation : true, elm : id}, {}); else return false;}
function captcha(){var time = new Date(); document.getElementById('imgseccode').src = 'captcha.php?time='+time.getSeconds();}
function window_open(url, p1, p2){url = url.replace("&amp;", "&"); return window.open(url, p1, p2);}
function name2id(name){var e = document.getElementsByTagName('a'); for(var i=0; i<e.length; i++) if(e[i].name == name) return e[i]; return false;}
function translit(id, buf){for (i = 0; i < cyr2.length; i++) buf = buf.replace(eval('regexp = /'+cyr2[i]+'/g'), lat2[i]); for (i = 0; i < cyr.length; i++) buf = buf.replace(eval('regexp = /'+cyr[i]+'/g'), lat[i]);  if($$(id)) $$(id).value = buf;  return;}
function rewrite_key(id, buf){for (i = 0; i < cyr2_rewrite.length; i++) buf = buf.replace(eval('regexp = /'+cyr2_rewrite[i]+'/g'), lat2_rewrite[i]); for (i = 0; i < cyr_rewrite.length; i++) buf = buf.replace(eval('regexp = /'+cyr_rewrite[i]+'/g'), lat_rewrite[i]); if($$(id)) $$(id).value = buf.toLowerCase(); else return buf.toLowerCase();return;}
function ckeck_uncheck_all(){var chekeds = document.getElementsByTagName('input'); for(i=0; i<chekeds.length; i++) if(chekeds[i].type == 'checkbox' && chekeds[i].id!="checkbox_sel") chekeds[i].checked = (chekeds[i].checked) ? false : true;}
function set_avatar(file, directory){var dir = window.opener.$$('cat').value;if(window.opener.$$('avatarset')) window.opener.$$('avatarset').src = directory+dir+'/'+file; if(window.opener.$$('id_set_avatar')) window.opener.$$('id_set_avatar').value = dir+'/'+file;}
function hack_sel(){if(!$$('no_select')) var e = $('body').append($('<input>').attr({id:'no_select', disabled:true}).css('display', 'none')); else var e = $$('no_select'); try {e.focus()} catch(e) {}}
function elmPos(e){e = $$(e); var X = 0; var Y = 0; while(e != null){X += e.offsetLeft; Y += e.offsetTop; e = e.offsetParent;} return {left:X+'px', top:Y+'px'}}
function scroll2elm(Elm){Elm = $$(Elm); var selectedPosX = 0; var selectedPosY = 0; while(Elm != null){selectedPosX += Elm.offsetLeft; selectedPosY += Elm.offsetTop; Elm = Elm.offsetParent;} scroll(selectedPosX,selectedPosY);}
function delete_comment(cid, id, table){if(confirm(window.js_lang.delcomm)){$$('comment_'+cid).style.display = 'none'; ajaxed({action : 'index.php?ajaxed=delete_comment', animation : false}, {'id' : id, 'cid' : cid, 'table' : table});}}
function number_znak(num){return (num<0) ? num*-1 : num;}
function varloc_add(obj){$.extend(true, varloc, obj);}
function value_rating(r_up,r_down){
    r_up=parseInt(r_up);r_down=parseInt(r_down);var val=r_up-r_down; 
    var ac=r_up+r_down;
    var cv=val>=0?'r_good':'r_bed';
    return "<span class='r_result "+cv+"' title='"+js_lang.r_vote+" ("+ac+") : +"+r_up+" ~ -"+r_down+"'>"+number_znak(val)+"</span>";
}
function display_rating(id,r_up,r_down,user_vote){
  $(document).ready(function(){
     if(user_vote!=undefined){
       if(user_vote!=0) $('#r_'+id).addClass(user_vote>0?'r_rs_up':'r_rs_down');
       else $('#r_'+id).removeClass('r_rs_up').removeClass('r_rs_down');
     }
     $('#rating'+id).html(value_rating(r_up,r_down));
  });
}
function set_rating(id, table, voted){
   var p=$('#r_'+id);
   if(!p.hasClass('r_rs_up') && !p.hasClass('r_rs_down')){
      haja({action : 'index.php?ajaxed=rating',animation:false,dataType:'json'}, {'id' : id, 'table' : table,'voted' : voted},{
            oninsert:function(data){display_rating(id,data.up,data.down,voted);}});
   } else {
      if((p.hasClass('r_rs_up') && voted>0) || (p.hasClass('r_rs_down') && voted<0)){
         haja({action : 'index.php?ajaxed=rating',animation:false,dataType:'json'}, {'id' : id, 'table' : table,'voted' : 0},{
               oninsert:function(data){display_rating(id,data.up,data.down,0);}});
      }
   }
   return false;
}
function rating(id, table, r_up,r_down){
   if(is_voted(table, id)==0){  
      var nrating="<div class='ratingsrc'><div style='float:left'>"+value_rating(r_up,r_down)+"</div>"+
      "<a class='rating'  title='"+window.js_lang.r4+"' onclick=\"set_rating('"+id+"', '"+table+"', 1); return false;\"><div class='hand_up'></div></a>"+
      "<a class='rating' title='"+window.js_lang.r1+"' onclick=\"set_rating('"+id+"', '"+table+"', -1); return false;\"><div class='hand_down'></div></a><div style='clear:left'></div></div>";
      var html = "<div class='rating' id='rating"+id+"'>"+nrating+"</div>";
      addEvent(window, 'load', function(){if($$('r_'+id)) $$('r_'+id).innerHTML = html;});
   } else display_rating(id,r_up,r_down);
} 

function edit_comment(cid){var pos = elmPos($$('content_comment_'+cid)); if(!$$('layer_editcomment')) $('body').append($('<div></div>').attr({id:'layer_editcomment', 'class':'comment_loading'}).css('display', 'none'));var heig = ($$('content_comment_'+cid).offsetHeight>=40) ? $$('content_comment_'+cid).offsetHeight : 40; $('#layer_editcomment').css({display:'block', left:pos.left, top:pos.top, height:heig+'px', width:$$('content_comment_'+cid).offsetWidth+'px', position: 'absolute', zIndex: '80'}); haja({action:'index.php?ajaxed=edit_comment', elm:'content_comment_'+cid, animation:false}, {id:cid, height:heig}, {onendload:function(){$$('layer_editcomment').style.display='none'}});}
function cancel_edit_comment(cid){$$('content_conteiner_'+cid).style.display = ''; $$('edit_conteiner_'+cid).style.display = 'none'; return false;}
function apply_edit_comment(cid){haja({elm:'content_comment_'+cid, animation:false, action:'index.php?ajaxed=apply_edit_comment'}, {id:cid, comment:$$('edit_comment_area_'+cid).value}, {}); return false;}
function empty_chosen_select(id){$('#'+id).find('option:[value!=""]').remove().end().trigger("liszt:updated");}
function select_add_options(id, action, value, sel){if(!window.sels) sels = []; haja({action:action.replace('amp;', ''), animation:false},{value:value}, {onstartload:function(){sels = [];empty_chosen_select(id);}, onendload:function(){eval(KR_AJAX.result); empty_chosen_select(id); if(sels.length>0){for(i=0;i<sels.length;i++){$$(id).options[i] = new Option(sels[i][1], sels[i][0]); if(sel==sels[i][0]) $$(id).options[i].selected=true;}} else $$(id).options[0] = new Option(window.js_lang.casemodule, ""); $('#'+id).trigger("liszt:updated");}});}
function showuploadereffect(obj){if(!obj){if($$('up_table')) obj = $$('up_table'); else obj = $$('upl_up');} var pos = elmPos(obj); if(!$$('layer_svfuploader')) $('body').append($('<div></div>').attr({id:'layer_svfuploader'}).css('display', 'none'));$$('layer_svfuploader').className = 'up_process_update'; $('#layer_svfuploader').css({display:'block', position:'absolute', zIndex:'80', left:(parseInt(pos.left)-1)+'px', top:pos.top, height:obj.offsetHeight+'px', width:obj.offsetWidth+'px'});}
function delete_attach(url, lang){if(confirm(lang)){showuploadereffect(); haja({elm:'upl_up', action:url.replace('&amp;', '&'), animation:false}, {'options':$$('update_upload_options').value}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}})} else return false;}
function create_dir(){if($$('uploaddir')){showuploadereffect(); haja({elm:'upl_up', action:'index.php?ajaxed=mkdir', animation:false}, {'dir':$$('uploaddir').value}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}})} return false; }
function rename(dir, file){var dialog = prompt(window.js_lang['new_name'], ''); if (dialog && dialog!=file){ showuploadereffect(); haja({elm:'upl_up', action:'index.php?ajaxed=rename', animation:false}, {'dir':dir, 'file':file, 'new_name':dialog, 'options':$$('update_upload_options').value}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}}
function update_upload(dir){$$('uploaddir').value = dir; showuploadereffect(); haja({elm:'upl_up', action:'index.php?ajaxed=update_upload', animation:false}, {'dir':dir,'options':$$('update_upload_options').value}, {onendload:function(){if($$('layer_svfuploader')) $$('layer_svfuploader').style.display = 'none'}});}
function dialog(lang, func){if(confirm(lang)){return true; if(func) func();} else return false;}
function set_vote(e, langparam){var varvote = 0; $(e).find('input').each(function(){if(this.type=='radio' && this.checked == true) {varvote = this.value;}});if(varvote!=0) haja({elm:'voting_result', action:e.action, animation:false}, {'var':varvote, 'voteblock':true}, {onstartload:function(){$(e).find(':submit').attr('dusabled', 'disabled');}}); else alert(langparam);}
function clear_ajax(url, id, lang){if(confirm(lang)) ajaxed({action : url, animation : true, elm : id}, {}); else return false;}
function move_ajax(url, id){ajaxed({action : url, animation : true, elm : id}, {});}
function set_favorite(id, el, module, lang, cl){if($$(el).className.indexOf('favorite_off')>-1) $('#'+el).removeClass('favorite_off').addClass('favorite_on'); else $('#'+el).removeClass('favorite_on').addClass('favorite_off'); $$(el).title = ($$(el).title==lang[0]) ? lang[1] : lang[0];  ajaxed({action:'index.php?ajaxed=favorite', animation:false, history:false}, {'id' : id, 'module' : module});}
function send_form(a,b){a=$$(a);KR_AJAX.ajaxForm.init({selector:"#"+a.id,elm:b})}
function switch_spoiler(a){$(a).toggleClass("spl_show");$(a).next().slideToggle("fast")}
function add_comment(id, sort, rows, page) {kr_execEvent('submit');haja({elm: 'add_comment',action: $$('comment_form').action,animation: true,addType: 'prepend'}, {'id': id,'comment': $('#comment').val(),'seccode': (($$('seccode')) ? $('#seccode').val() : ''),'this_page': page}, {onendload:function(){$('.main_editor').remove();$('#add_comment').html('');},oninsert: function () {$('#comment').val('').change();}});}
function case_authors(a,b){KR_AJAX.animation("show");$.get("index.php?module="+a+"&do=case_author",function(a){guiders.createGuider({buttons:[{name:"OK",onclick:function(){var a="";$(".guider input").each(function(){if(this.type=="checkbox"&&this.checked==true)a+=this.value+","});$$("authors").value=a.substring(0,a.length-1);guiders.hideAll()}},{name:window.js_lang.cancel,onclick:function(){guiders.hideAll()}}],description:a,id:"caseauthor",position:0,onHide:function(){KR_AJAX.animation("hide")},overlay:false,xButton:true,title:b}).show()})}
function addinput(id, name, clas, placeholder) {var values_array = [], i = 0; $('#' + id + ' input').each(function () {if (this.type == 'text') values_array.push(this.value);}); cl = (clas) ? " class='" + clas + "'" : ""; pl = (placeholder) ? " placeholder='" + placeholder + "'" : ""; $('#' + id).append("<table width='100%' cellspacing='0' cellpadding='0' style='margin-top: 2px;'><tr><td><input" + cl + pl + " type='text' name='" + name + "[]' value='' /></td></tr></table>"); $('#' + id + ' input').each(function () {if (this.type == 'text') {$(this).val(values_array[i]);i++;}});}
function var_inited(var_name,func_exec){if(window[var_name]==undefined){setTimeout(function(){var_inited(var_name,func_exec);},200);} else setTimeout(func_exec,100);}
function switcher(e, a){
    $(e).find('.pico_hide').toggleClass('pico_show');
    $(e).next('div:first').find('.'+a).slideToggle('slow');
}

function set_checked_callback(PhpFunc, JSCallback, time, vars){
    if(!$.isFunction(JSCallback)) {vars = time; time = JSCallback}
    time = time || 10000; vars = vars || {};
    dcc[PhpFunc] = {time:time, checked:time, vars:vars, callback:JSCallback};
}

$(document).ready(function(){
    //init KR_AJAX
    KR_AJAX.init();
    //remove disable rating
    $('.rating').filter('.disable').find('div').remove();
    //user info popup
   // $("a.user_info").live("click", function(event){
     $(document).on("click", "a.user_info", function(event){
        haja({animation: false, action:this.href}, {'show':'json'}, {
            onstartload:function(){KR_AJAX.animation('show');},
            onendload:function(msg){
                data = $.parseJSON(msg);
                for(var i=0; i<data.buttons.length;i++) data.buttons[i].onclick = new Function(data.buttons[i].onclick);
                $.get(data.json_tpl, function(file) {
                    var target_id=event.target.id!=''?event.target.id:'custom_target_id';
                    guiders.createGuider({
                        attachTo: "#"+target_id,
                        buttons: data.buttons,
                        description: "<hr /><div class='info"+target_id+" popup_infouser'></div>",
                        id: "user_info"+target_id,
                        position: 0,
                        onHide:function(){KR_AJAX.animation('hide');},
                        overlay: false, xButton:true,
                        title: data.lang.user+": "+data.user_name
                    }).show();
                    $.template("fc", file);
                    $.tmpl("fc", data).appendTo(".info"+target_id);
                });
            }
        });
        return false;
    });
       
    //chosen setup
     $(document).on("click", "select.chzn-select", function(event){
        setTimeout(function(){
            $('select').trigger("liszt:updated");
        }, 10);
    });
    
    $.DOMlive.reg("select:not(.chzn-none):not(.CodeMirror-completions select)", function(){
        $("select").not('.chzn-none').chosen();
    }, false);
    
    //syntax chcked
    $.DOMlive.reg(".syntaxNotReady",function(){path="includes/javascript/syntax/";if(window["SyntaxHighlighter"]!==undefined){if(KR_AJAX.cache.script["shAutoloader"]){if(window.loadedSyntaxCount==0){autoLoadSyntax()}}}else{if(!KR_AJAX.cache.script["shCore"]){KR_AJAX.cache.script["shCore"]=true;KR_AJAX.include.style(path+"styles/shCore.css");KR_AJAX.include.style(path+"styles/shCoreDefault.css");$.getScript(path+"scripts/shCore.js",function(){$.getScript(path+"scripts/shAutoloader.js",function(){KR_AJAX.cache.script["shAutoloader"]=true})})}}return false},false);$.DOMlive.reg(".syntaxReady",function(){if(window.loadedSyntaxCount==0&&$(".syntaxNotReady").length==0){SyntaxHighlighter.highlight();syntaxConfig();return true}else return false},false)

    //lazyload checked
    $.DOMlive.reg("img.lazyload, img.miniature",function(){var a=$(document).scrollTop();$(this).lazyload({placeholder:"includes/javascript/jquery/lazyload/images/pixel.gif",effect:"fadeIn"});$(document).scrollTop(a)})
    
    $.DOMlive.reg('.pixel', function(){
        if(jQuery.support.boxModel == false) $(this).append('<img src="includes/images/pixel.gif" alt="" />');
        else $(this).append('<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="" />');
    });
    
     $.DOMlive.reg('.message_notify', function(){
         var self = this;
         var h = $(self).height();
         setTimeout(function(){
             $(self).fadeTo( 1500, 0.0, function(){
                $(self).remove();
                $.each($('.message_notify').get().reverse(), function(){
                    $(this).animate({bottom:'-='+(h+20)}, {duration:300, step:function(){}, complete:function(){}});
                });
             });
         }, 5000);
    });
    
    $('body').append('<div style="display:none;" id="hide_conteiner"></div>');
    if($.fn.breadCrumb) $('.srcbreadcumb').breadCrumb({speed:600});
    KR_AJAX.interval();
    var step = 300;
    $('.post_options').each(function(){
        $(this).slideToggle(step);
        step *= 2;
    });
    
    var interval_checking = 1000;
    
    (data_checking = function(){
        var send_request = {};
        for(var i in dcc){
            var data = dcc[i];
            if(data.checked<=0 && data.time>0){
                send_request[i] = data;
                dcc[i].checked = data.time-interval_checking;
            } else dcc[i].checked -= interval_checking
        }
        if($.keys(send_request).length>0){
            haja({action:'index.php?ajaxed=module&do=data_checking', animation:false}, {data:$.toJSON(send_request)}, {onendload:function(json){
                json = $.parseJSON(json);
                for(var i in json){
                    if($.isFunction(dcc[i].callback)) func = dcc[i].callback; else func = window[dcc[i].callback];
                    if(typeof func!=='undefined'){
                       try {
                          if($.isArray(json[i])) func.apply({}, json[i]); else func.call({}, json[i]);
                       } catch (exception_var) {}
                    }
                }
            }});
        }
        setTimeout(data_checking, interval_checking);
    })();
    
    //Check DOMNodeInserted
    $('<div>').appendTo('body').bind('DOMNodeInserted', function(){$.DOMNodeInserted = true;}).html('test').remove();    
    //Init DOMNodeInserted
    if($.DOMNodeInserted)  $(document).on("DOMNodeInserted", "body", function(event){$.DOMlive.check(event.target);}); else $.getScript('includes/javascript/DOMNodeInserted.js');
    //First checked KR live
    $.DOMlive.checkAll();
});

var jsAbstract = {}
function attache_load(){var a="";haja({animation:false,action:$("#attache_page").val()},{},{onstartload:function(){},oninsert:function(a){guiders.createGuider({buttons:[],description:"<hr />"+KR_AJAX.result.content,id:"attache_window",position:0,offset:{top:$(".attache_button").offset().top,left:$("#ajax_content").offset().left},onHide:function(){$(".guider").remove()},overlay:false,xButton:true,width:$("#ajax_content").width()-20,title:KR_AJAX.result.lang.title}).show();var b=$("body");$(".guider").drag("start",function(a,c){c.limit=b.offset();c.limit.bottom=c.limit.top+b.outerHeight()-$(this).outerHeight();c.limit.right=c.limit.left+b.outerWidth()-$(this).outerWidth()}).drag(function(a,b){$(this).css({top:Math.min(b.limit.bottom,Math.max(b.limit.top,b.offsetY)),left:Math.min(b.limit.right,Math.max(b.limit.left,b.offsetX))})},{handle:".guider_title"});$(".guider_title").css("cursor","move")}});return false}
function testAttribute(element, attribute){var test = document.createElement(element); return (attribute in test) ? true : false;}
function kr_addEvent(eventName,fn,fnName){if(kr_events[eventName]==undefined) kr_events[eventName]=[];if(fnName==undefined) kr_events[eventName].push(fn); else kr_events[eventName][fnName]=fn;}
function kr_execEvent(eventName){if(kr_events[eventName]!=undefined){for(i in kr_events[eventName]) kr_events[eventName][i]();}}
$.fn.autocomplete=function(a,b,c){$(this).focus(function(){var c=this;if(!jsAbstract.autocomplete)$.getScript("includes/javascript/jquery/jquery.autocomplete.js",function(d,e,f){if(e=="success")$(c).autocomplete(a,b,d);else alert("Error load autocomplete.js file")});jsAbstract["autocomplete"]=true})}
$.fn.autocompleteArray = function(data, options){$.autocomplete(null, data, options)}
window.insert_attach_file=function(fileattach){bbeditor.insert('[attach='+fileattach+']', '');}
window.insert_img_file=function(fileattach){bbeditor.insert('[img]'+fileattach+'[/img]', '');}
window.insert_miniimg_file=function(fileattach){bbeditor.insert('[miniature='+fileattach+' align=middle]', '');}
var waitInited=[];
function checkInited(obj, vi) {var cm;if (obj === '') {obj = window;cm = vi.slice(0);} else cm = vi;var p = cm.shift();if (typeof obj[p] !== 'undefined') {return (cm.length > 0) ? checkInited(obj[p], cm) : true;} else return false;}
function checkTimerInited() {var obj, nextf = [];if (waitInited.length > 0) {while (waitInited.length>0) {obj = waitInited.shift();if (checkInited('', obj.find)) {obj.func();} else nextf.push(obj);}}if(nextf.length>0) waitInited = waitInited.concat(nextf);}
function varInited(variable, fn){var o = {find: variable.split('.'), func: fn};waitInited.push(o);}
function isInited(obj, variable){if(typeof obj!=='undefined'){var find = variable.split('.'); return checkInited(obj, find);} else false;}
setInterval(checkTimerInited,200);
