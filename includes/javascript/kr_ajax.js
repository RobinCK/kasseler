//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2012 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////

(function($,e,b){
    var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);
  function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}
  $.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};
  $.fn[c].delay=50;
  g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});
  f=(function(){
    var j={},p,m=a(),k=function(q){return q},l=k,o=k;
    j.start=function(){p||n()};
    j.stop=function(){p&&clearTimeout(p);p=b};
    function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}
    if((navigator.userAgent.search("MSIE") >= 0) == true){(function(){
      var q,r;
      j.start=function(){
        if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};
        j.stop=k;
        o=function(){return a(q.location.href)};
        l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}
    })(); }
    return j})()})(jQuery,this);

var j = $ = jQuery.noConflict();
var ajax_history = {};
var this_crc_request = '';

var initSPost =function(){if(top===self){var cr = document.cookie.split(';');for (var i = 0; i < cr.length; i++) {var a = cr[i].trim().split('=');if (a[0] == 'nIhDgOTW6j') jsSecretID = a[1];}}}

$.originalAJAX = $.ajax;
$.ajax = function(origSettings){
   var method = origSettings.type.toLowerCase();
   if(typeof jsSecretID!=='undefined'){
      if(method=='post'){
         initSPost();
         var td = typeof origSettings.data;         
         if(td!=='undefined'){
            if(td=='string') origSettings.data = origSettings.data +'&secID='+jsSecretID;
            else origSettings.data.secID = jsSecretID;
         }
         else origSettings.data={secID: jsSecretID};
      }
   }
   return $.originalAJAX(origSettings);
}

$(document).on('submit', 'form', function () {
    if(typeof jsSecretID!=='undefined'){
        if ($(this).find('.secID').length == 0) {
            initSPost();
            $(this).append($('<input/>', {
                type: 'hidden',
                'class': 'secID',
                name: 'secID'
            }).val(jsSecretID));
        }
    }
});

function crc32(a){var b="00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";if(typeof crc=="undefined"){crc=0}var c=y=0;crc=crc^-1;for(var d=0,e=a.length;d<e;d++){y=(crc^a.charCodeAt(d))&255;c="0x"+b.substr(y*9,8);crc=crc>>>8^c}return crc^-1}
jQuery.extend({
    DOMNodeInserted:false,
    keys: function(obj){var a = [];$.each(obj, function(k){a.push(k)}); return a;},
    notify: function(title, content, link, image){                 
        image = image || '';
        var sh = 10; 
        if($('.message_notify').length>0) $.each($('.message_notify'), function(k){sh+=$(this).height()+20});
        $("<div class='message_notify'><h3>"+title+"</h3><div class='content_notify'>"+image+content+"</div></div>").prependTo("body").css({bottom:sh, 'cursor':'pointer'}).click(function(){
            if(link !== undefined) location.href = link;
            return false;
        });
    },
    getHash:function(){s=location.hash.replace("#","");if(s.indexOf(":")>-1){hash={};vv=s.split(":");for(i=0;i<=vv.length;i++){if(vv[i]&&vv[i].indexOf("=")>-1){kv=vv[i].split("=");hash[kv[0]]=kv[1]}}return hash}else return{def:s}},
    setHash:function(a,b){hash=$.getHash();if(a!="undefined")for(i in a)hash[i]=a[i];if(b!="undefined")for(i in b)if(b[i]!="undefined")delete hash[i];lh="";for(i in hash)lh+=i+"="+hash[i]+":";location.hash=lh},
    DOMlive:{register:{},reg:function(a,b){this.register[a]=[b];},check:function(e){for(var a in this.register) if($(e).not('.domchecked').is(a)){if(this.register[a][0].call(e)!=false) $(e).addClass("domchecked"); }},checkAll:function(){for(var e in this.register){o=$(e).not(".domchecked");if(o.length>0){o.each(function(){$.DOMlive.check(this)})}}}},
    getScript:function( url, callback ){return $.get(url, null, callback, "script").success(function(result){$.DOMlive.checkAll();});}
});


$(function() {document.write = function(a) {$('body').append(a);}});
function setCookie(a,b,c,d,e,f){document.cookie=a+"="+escape(b)+(c?"; expires="+c:"")+(d?"; path="+d:"")+(e?"; domain="+e:"")+(f?"; secure":"")}
function getCookie(a){var b=" "+document.cookie;var c=" "+a+"=";var d="";var e=0;var f=0;if(b.length>0){e=b.indexOf(c);if(e!=-1){e+=c.length;f=b.indexOf(";",e);if(f==-1)f=b.length;d=unescape(b.substring(e,f))}}return d}
function haja(a,b,c){var d={};for(var e in a)d[e]=a[e];for(var e in c)KR_AJAX[e]=c[e];d.data=b;KR_AJAX.processResponse(d)}
function ajax(a,b){ajaxed({action:a,animation:false,elm:b},{})}
function ajaxed(a,b){haja(a,b,{})}
function $$(a){return typeof a==="string"?document.getElementById(a):a}

function isElement(o){return o && o.nodeType == 1;}
function isArray(o){return $.isArray(o);}
function isFunction(o){return $.isFunction(o);}
function isObject(o){return $.isPlainObject(o)}
function isHash(o){return o instanceof Hash;}
function isString(o){return typeof o == "string";}
function isNumber(o){return typeof o == "number";}
function isUndefined(o){return typeof o == "undefined";}
function basename(path, suffix) {var b = path.replace(/^.*[\/\\]/g, '');if (typeof(suffix) == 'string' && b.substr(b.length-suffix.length) == suffix) {b = b.substr(0, b.length-suffix.length);}return b;}

Array.prototype.remove = function(obj) {var a = []; for (var i=0; i<this.length; i++) if (this[i] != obj) a.push(this[i]); return a;}
String.prototype.trim = function(){ return this.replace(/\s*((\S+\s*)*)/, "$1").replace(/((\s*\S+)*)\s*/, "$1");}
String.prototype.replaceAll = function(s1, s2) {return this.split(s1).join(s2)}

if (!window.KR_AJAX) KR_AJAX = {};
KR_AJAX.extend = function(dest, src, skipexist){var overwrite = !skipexist; for (var i in src) if (overwrite || !dest.hasOwnProperty(i)) dest[i] = src[i]; return dest;};

(function($_){$.extend($_, {
    varsion:'1.0.2',
    charset:'UTF-8',
    status:0,
    href:'',
    timer:Object,
    isReady:false,
    loadCount:0,
    ajaxed:false,
    options:{action:'', method:'POST', elm:'#hide_conteiner', animation:true, addType:'', async:true, user:'', passswd:'', cache:false, dataType:'html', contentType:'application/x-www-form-urlencoded', data:'', global:true},
    cache:{link:[], script:[]},
    action:{image : 'includes/images/loading/loading_zindex.gif', text : 'Loading...'},
    alert:"Your browser does not support.",
    headers:{'Content-Type':'application/x-www-form-urlencoded; Charset='+$_.charset, 'AJAX_ENGINE':'KR_AJAX', 'HTTP_X_REQUESTED_WITH':'XMLHttpRequest', 'If-Modified-Since':'Sat, 1 Jan 2000 00:00:00 GMT'},
    onload:false,
    flag_insert:true,
    ifunction:{},
    plugin:{},
    
    interval:function(){setTimeout(KR_AJAX.interval, 100); for(var i in $_.ifunction) {if($.isFunction($_.ifunction[i])) $_.ifunction[i]();}},
    
    init:function(){
        if((navigator.userAgent.search("MSIE") >= 0) == true){try {$_.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");} catch (e) {try {$_.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");} catch (err) {$_.xmlhttp = null;}}}
        if(!$_.xmlhttp && typeof XMLHttpRequest != "undefined") $_.xmlhttp = new XMLHttpRequest();
        if(!$_.xmlhttp) alert($_.alert);
        else $(document).ready(function(){KR_AJAX.isReady = true; KR_AJAX.onload = true;});
    },
    
    set_var:function(name, value){$_.options.vars += "&"+name+"="+encodeURIComponent(value);},
    ext:function(methods){for (var i in methods) $[i] = methods[i];},
    onstartload:function(){},
    onendload:function(){},
    oninsert:function(){},
    
    scriptEval:function(msg){
        var js_reg = /<script.*?>(.|[\r\n])*?<\/script>/ig, js_str = js_reg.exec(msg), js_arr = [];
        if (js_str != null){
            js_arr = new Array(js_str.shift());
            while((js_str = js_reg.exec(msg))!=null) {
                js_arr.push(js_str.shift());
            }
            if (js_arr.length>0) {
                var set_js = [];
                for(var i=0; i<js_arr.length;i++){ 
                    msg = msg.replace(js_arr[i], '');
                    if(js_arr[i].indexOf('KR_AJAX.include')>=0) $('body').append(js_arr[i]);
                    else set_js.push(js_arr[i]);
                }
                return [msg, set_js];
            } else return [msg, []];
        } else return [msg, []];
    },

    processResponse:function(o){
        $(document).ready(function(){
            for(var i in $_.options) if(o[i]==null) o[i] = $_.options[i];
            if(o.elm!='' && o.elm[0]!='.' && o.elm[0]!='#') o.elm = '#'+o.elm;
            if(o.action=='') return false;
            if(!o.data['ajax']) o.data['ajax'] = 'true';
            $.ajax({type: o.method, url: o.action, scriptCharset:'UTF-8', cache: o.cache, dataType: o.dataType, async: o.async, username: o.user, password: o.passswd, contentType: o.contentType, data: o.data, global: o.global,
                success: function(msg){
                    $_.result=msg;
                    var data = $_.scriptEval(msg);
                    msg = data[0];
                    $_.onendload(msg); $_.ajaxed = false;
                    if(o.elm!='') {
                        if($_.flag_insert==true){
                            if(o.addType=='') $(o.elm).html(msg); else if(o.addType=='prepend') $(o.elm).prepend(msg); else if(o.addType=='append') $(o.elm).append(msg);
                            $.DOMlive.checkAll();
                        } else $_.flag_insert=true;
                    }
                    if(data[1].length>0) for(var i=0; i<data[1].length;i++) $('body').append(data[1][i]);
                    $_.oninsert(msg);
                    $_.loadCount--;
                    $_.onendload = $_.onstartload = $_.oninsert = function(){};
                },
                error:function(xhr, txt, err){$_.loadCount--;},
                complete:function(XMLHttpRequest, textStatus){if(o.animation==true) KR_AJAX.animation('hide'); $_.ajaxed = false;},
                beforeSend:function(XMLHRequest){
                    $_.isReady = false; $_.loadCount++;
                    if(o.animation==true) KR_AJAX.animation('show'); $_.onstartload(); $_.ajaxed = true;
                }
            });
        });
    },

    include : {
        ignore_cache:{'kr_bbeditor':true, 'kr_calendar':true, 'kr_calendar2':true},
        script : function(url, handler){
            if($('script[src="'+url+'"]').length==0 || this.ignore_cache[basename(url).replace('.js', '')]) {
                $_.cache.script[basename(url).replace('.js', '')] = true; 
                KR_AJAX.loadCount++; 
                var f = function(){
                    KR_AJAX.loadCount--;
                    if(isFunction(handler)) handler.call();
                } 
                $.getScript(url, f).fail(function(){ KR_AJAX.loadCount--;});
            }
        },
        style : function(url){$('head').append($('<link>').attr({href:url, rel:'stylesheet'}));}
    },
    
    animation:function(type){
        if(!$(".fone_ajax").get(0)){
            $("<div class='fone_ajax'><div class='loading_ajax'><br /></div></div>").prependTo("body");
            $(window).bind("resize", function(){$(".fone_ajax").css("height", $(window).height());});
        }
        $(".fone_ajax").css({"height": $(document).height(), 'display':'none'});
        $(".fone_ajax").fadeTo('slow', type=='show'?0.50:0.0);
        if(type=='hide') setTimeout(function(){$(".fone_ajax").css("display", 'none');}, 700)
    },
    
    ajaxForm : {
        defaults:{selector:'.ajaxform', onInit:null, onStartSend:null, onEndSend:null, element:['select', 'input', 'textarea'], resultType:'html', elm:'#hide_conteiner', nullOnSend:false, data:{}},
        
        init:function(options){
            o = $.extend(this.defaults, options);
            $(document).ready(function(){
                $(o.selector).off('submit.kr').on('submit.kr',function(e){
                    KR_AJAX.ajaxForm.send(o, this); return false;
                }).submit();
                if(o.onInit!=null) o.onInit();
                $('input.submit').after("<img style='visibility: hidden;' class='form_sender' src='includes/images/loading/small.gif' alt='Loading...' />");
            });
        },
        
        send:function(o, obj){
            if(KR_AJAX.ajaxed==false){
                haja({effect:false, elm:o.elm, action:obj.action, method:obj.method, dataType:o.resultType}, $(obj).serializeArray(), {
                    onstart:function(){$('.form_sender').css('visibility','visible'); if(o.onStartSend!=null) o.onStartSend(o);},
                    onend:function(msg){if(o.onEndSend!=null) o.onEndSend(o, msg); if(o.nullOnSend==true) o.onEndSend = o.onStartSend = null; $('.form_sender').css('visibility','hidden');}
                });
            }
        }
    },

    iframeWrite:function(doc, text){doc.open(); doc.write(text); doc.close(); doc.body.innerHTML = text;}
})
})(KR_AJAX);

