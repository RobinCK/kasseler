/**
* editor_plugin_src.js
*
* Copyright 2009, Moxiecode Systems AB
* Released under LGPL License.
*
* License: http://tinymce.moxiecode.com/license
* Contributing: http://tinymce.moxiecode.com/contributing
*/

(function() {
      function rep_img(str, p1, offset, s) {
         var rg1 = p1.match(/src=["']*([^"']*)/i);
         var rg2 = p1.match(/alt=["']*([^"']*)/i);
         var rg3 = p1.match(/align=["']*([^"']*)/i);
         var rg4 = p1.match(/class=["']*([^"']*)/i);
         if(rg4!=null && rg4[1]=='miniature'){
           var sr="[miniature="+rg1[1]+" align=middle]";
         } else {
            var align=(rg3!=null?(rg3[1]):"middle");
            var sr="[img="+align;
            if(rg2!=null && rg2[1].trim()!='') sr+=" alt="+rg2[1];
            sr+="]"+rg1[1]+"[/img]";
         }
         return sr;
      }
      function rep_imgbb(str, p1, offset, s) {
         var rg1 = str.match(/\[img(=([^ \]]*))* *(alt=([^]]*))*]([^\[]*)/i);
         var sv="<img src='"+rg1[5]+"' ";
         if(rg1[2]!=undefined && rg1[2].trim()!="") sv+=" align='"+rg1[2]+"'";
         if(rg1[4]!=undefined && rg1[4].trim()!="") sv+=" alt='"+rg1[4]+"'";
         return sv+" />";
      }
      function tags(html){
         var reg1 = /</g;
         var reg2 = />/g;
         var reg3 = /\n/g;
         var reg4 = /\n/g;
         var var1 = html.replace(reg1, "&lt;");
         var var2 = var1.replace(reg2, "&gt;");
         return  var2;
      }
      function rep_code(str, p1,p2, offset, s) {
        var src=tags(p2);
        src=src.replaceAll('&lt;br /&gt;',"\n");
         return "<pre><code class='"+p1+"'>"+src+"</code></pre>";
      }
      function getFileName(path) {return path.match(/[-_\w]+[.][\w]+$/i)[0];}
      function rep_attach(str, p1, offset, s) {
         return "<a class=\"attachfile\" href=\""+p1+"\">"+getFileName(p1)+"<\/a>";
      }
      tinymce.create('tinymce.plugins.BBCodePlugin', {
            init : function(ed, url) {
               var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

               ed.onBeforeSetContent.add(function(ed, o) {
                     o.content = t['_' + dialect + '_bbcode2html'](o.content);
               });
               ed.onPostProcess.add(function(ed, o) {
                     if (o.set) o.content = t['_' + dialect + '_bbcode2html'](o.content);
                     if (o.get) o.content = t['_' + dialect + '_html2bbcode'](o.content);
               });
            },
            getInfo : function() {
               return {
                  longname : 'BBCode Plugin',
                  author : 'Moxiecode Systems AB',
                  authorurl : 'http://tinymce.moxiecode.com',
                  infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
                  version : tinymce.majorVersion + "." + tinymce.minorVersion
               };
            },
            // Private methods
            // HTML -> BBCode in PunBB dialect
            _punbb_html2bbcode : function(s) {
               s = tinymce.trim(s);

               function rep(re, str) {
                  s = s.replace(re, str);
               };
               // example: <strong> to [b]
               rep(/<a class=\"attachfile\" href=\"(.*?)\".*?>.*?<\/a>/gi,"[attach=$1]");
               rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
               //rep(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
               rep(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
               //rep(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
               rep(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
               rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
               rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
               rep(/<span style=\"font-size: ?(.*?)px;\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
               rep(/<font>(.*?)<\/font>/gi,"$1");
               //rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
               //rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
               rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
               //rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
               rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
               //rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
               rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
               //rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
               rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
               rep(/<\/(strong|b)>/gi,"[/b]");
               rep(/<(strong|b)>/gi,"[b]");
               rep(/<\/(em|i)>/gi,"[/i]");
               rep(/<(em|i)>/gi,"[i]");
               rep(/<\/u>/gi,"[/u]");
               rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
               rep(/<u>/gi,"[u]");
               rep(/<blockquote[^>]*>/gi,"[quote]");
               rep(/<\/blockquote>/gi,"[/quote]");
               rep(/<br \/>/gi,"\n");
               rep(/<br\/>/gi,"\n");
               rep(/<br>/gi,"\n");
               rep(/&nbsp;|\u00a0/gi," ");
               rep(/&quot;/gi,"\"");
               rep(/&lt;/gi,"<");
               rep(/&gt;/gi,">");
               rep(/&amp;/gi,"&");
               rep(/<p align="center">([\s\S]*?)<\/p>/gi,"[center]$1[/center]");
               rep(/<span\x20*style="text-decoration: ?line-through;">([\s\S]*?)<\/span>/gi,"[s]$1[/s]");
               rep(/<(div|p) style="text-align: ?left;">([\s\S]*?)<\/(div|p)>/gi,"[left]$2[/left]");
               rep(/<(div|p) style="text-align: ?right;">([\s\S]*?)<\/(div|p)>/gi,"[right]$2[/right]");
               rep(/<(div|p) style="text-align: ?center;">([\s\S]*?)<\/(div|p)>/gi,"[center]$2[/center]");
               rep(/<(div|p) style="text-align: ?justify;">([\s\S]*?)<\/(div|p)>/gi,"[justify]$2[/justify]");
               rep(/<span style=\"font-family: ?(.*?);\">([\s\S]*?)<\/span>/gmi,"[family=$1]$2[/family]");
               rep(/<span style=\"background-color: ?(.*?);\">([\s\S]*?)<\/span>/gi,"[backcolor=$1]$2[/backcolor]");
               s = s.replace(/<img([\s\S]*?)\/>/gi, rep_img);
               rep(/<sub>/gi,"[sub]");rep(/<\/sub>/gi,"[/sub]");
               rep(/<sup>/gi,"[sup]");rep(/<\/sup>/gi,"[/sup]");
               rep(/<li>/gi,"[li]");rep(/<\/li>/gi,"[/li]");
               rep(/<pre><code *class=["']*([^'"]*)["']*>([\s\S]*?)<\/code><\/pre>/gi,"[$1]$2[/$1]");
               rep(/<div ?class="quotetop">([^<]*)<\/div>[\s\S]*?<div class="quotemain">([\s\S]*?)<\/div>/img,"[cite=$1]$2[/cite]");
               rep(/<p>/gi,"");
               //rep(/<\/p>/gi,"\n");
               rep(/<\/p>/gi,"");
               return s; 
            },

            // BBCode -> HTML from PunBB dialect
            _punbb_bbcode2html : function(s) {
               s = tinymce.trim(s);

               function rep(re, str) {
                  s = s.replace(re, str);
               };

               // example: [b] to <strong>
               rep(/\n/gi,"<br />");
               rep(/\[b\]/gi,"<strong>");
               rep(/\[\/b\]/gi,"</strong>");
               rep(/\[i\]/gi,"<em>");
               rep(/\[\/i\]/gi,"</em>");
               rep(/\[u\]/gi,"<u>");
               rep(/\[\/u\]/gi,"</u>");
               rep(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
               rep(/\[url\]([\s\S]*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
               //rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
               rep(/\[color=(.*?)\]([\s\S]*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
               //rep(/\[code\]([\s\S]*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>&nbsp;");
               rep(/\[quote.*?\]([\s\S]*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>&nbsp;");
               rep(/\[s\]([\s\S]*?)\[\/s\]/gi,"<span style='text-decoration: line-through;'>$1</span>");
               rep(/\[left\]([\s\S]*?)\[\/left\]/gi,"<div style='text-align: left;'>$1</div>");
               rep(/\[right\]([\s\S]*?)\[\/right\]/gi,"<div style='text-align: right;'>$1</div>");
               rep(/\[center\]([\s\S]*?)\[\/center\]/gi,"<div style='text-align: center;'>$1</div>");
               rep(/\[justify\]([\s\S]*?)\[\/justify\]/gi,"<div style='text-align: justify;'>$1</div>");
               rep(/\[family=(.*?)\]([\s\S]*?)\[\/family\]/gi,"<span style=\"font-family: $1\">$2</span>");
               rep(/\[size=(.*?)\]([\s\S]*?)\[\/size\]/gi,"<span style=\"font-size: $1px;\">$2</span>");
               rep(/\[backcolor=(.*?)\]([\s\S]*?)\[\/backcolor\]/gi,"<span style=\"background-color: $1\">$2</span>");
               s = s.replace(/\[img[^\]]*\]([\s\S]*?)\[\/img\]/gi, rep_imgbb);
               rep(/\[sub\]/gi,"<sub>");rep(/\[\/sub\]/gi,"</sub>");
               rep(/\[sup\]/gi,"<sup>");rep(/\[\/sup\]/gi,"</sup>");
               rep(/\[li\]/gi,"<li>");rep(/\[\/li\]/gi,"</li>");
               
               s = s.replace(/\[(code)\]([\s\S]*?)\[\/code\]/gi, rep_code);
               s = s.replace(/\[(php)\]([\s\S]*?)\[\/php\]/gi, rep_code);
               s = s.replace(/\[(html)\]([\s\S]*?)\[\/html\]/gi, rep_code);
               s = s.replace(/\[(css)\]([\s\S]*?)\[\/css\]/gi, rep_code);
               s = s.replace(/\[(xml)\]([\s\S]*?)\[\/xml\]/gi, rep_code);
               s = s.replace(/\[(javascript)\]([\s\S]*?)\[\/javascript\]/gi, rep_code);
               s = s.replace(/\[(java)\]([\s\S]*?)\[\/java\]/gi, rep_code);
               s = s.replace(/\[(cpp)\]([\s\S]*?)\[\/cpp\]/gi, rep_code);
               s = s.replace(/\[(delphi)\]([\s\S]*?)\[\/delphi\]/gi, rep_code);
               s = s.replace(/\[(python)\]([\s\S]*?)\[\/python\]/gi, rep_code);
               s = s.replace(/\[(ruby)\]([\s\S]*?)\[\/ruby\]/gi, rep_code);
               s = s.replace(/\[(sql)\]([\s\S]*?)\[\/sql\]/gi, rep_code);
               
               //rep(/\[code\]([\s\S]*?)\[\/code\]/gi,"<code>$1</code>");
               rep(/\[cite=(.*?)\]([\s\S]*?)\[\/cite\]/gi,"<div class='quotetop'>$1</div><div class='quotemain'>$2</div>");
               rep(/\[attach=([^\]]+)\]/gi,rep_attach);
               rep(/\[miniature=([^\x20]+)[^\]]+\]/gi,"<img class='miniature' src='$1' alt=' ' title=' ' align='middle'>");
               return s; 
            }
      });

      // Register plugin
      tinymce.PluginManager.add('bbcode', tinymce.plugins.BBCodePlugin);
})();