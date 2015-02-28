window.tinyMCEPreInit = {base : '/includes/javascript/editors/tiny_mce', suffix : '', query : ''};
var last_mce_option={};
KR_AJAX.include.script('includes/javascript/editors/tiny_mce/tiny_mce.js');
KR_AJAX.include.style('includes/css/tiny_mce.css');
function init_tiny_mce(custom){
   var cssp=custom.cssp?custom.cssp:"templates/admin/";
   var tObj={
      // General options
      mode : "textareas",
      language : "ru",
      theme : "advanced",
      plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

      // Theme options
      theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,forecolor,backcolor,ks_smile,image,link,ks_spoiler,preview',
      theme_advanced_buttons2 : 'charmap,bullist,numlist,indent,sub,sup,ks_translit,ks_cite,ks_code,ks_font,ks_size,code',
      theme_advanced_buttons3 : 'pasteword,table,col_before,col_after,row_before,row_after,delete_table,delete_row,delete_col,merge_cells',
      theme_advanced_buttons4 : '',
      theme_advanced_buttons5 : '',
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_path : false, 
      theme_advanced_resizing : true,
      theme_advanced_resizing_use_cookie : false,
      theme_advanced_resize_horizontal : 0,
      pagebreak_separator :"<cut>",
      fullpage_default_font_family : "Verdana",
      fullpage_default_font_size : "12px",
      extended_valid_elements : 'embed[src|type|wmode|allowscriptaccess|allowfullscreen|width|height],iframe[name|src|framespacing|border|frameborder|scrolling|title|height|width],object[declare|classid|codebase|data|type|codetype|archive|standby|height|width|usemap|name|tabindex|align|border|hspace|vspace]',
      forced_root_block : false,
      force_br_newlines : true,
      force_p_newlines : false,
      // Example content CSS (should be your site CSS)
      content_css : cssp+"style.css,includes/css/tiny_mce.css",
      formats : {
         italic : {inline : 'i'}
      },
      setup : function(ed) {
         ed.onChange.add(function(ed, l) {
               //console.debug('Editor contents was modified. Contents: ' + l.content);
               //console.debug(ed.getContent());
         });
      },

      // Style formats
      style_formats : [
         {title : 'Bold text', inline : 'b'},
         {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
         {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
         {title : 'Table styles'},
         {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
      ],
      // Replace values for the template plugin
      template_replace_values : {
         username : "Some User",
         staffid : "991234"
      }        
   };
   var empty = {};
   if(window.tinyMCEvar==undefined) window.tinyMCEvar={};
   if(custom==undefined) var custom={};
   var InitOption=$.extend(empty, tObj, window.tinyMCEvar,custom);
   var customInit=(InitOption.oninit!=undefined)?InitOption.oninit:function(){};
   InitOption.oninit=function(){
      $('textarea').parent().find('table:first').css('width','100%').find('table.mceToolbar').css('width','auto');
      customInit();
   }
   last_mce_option=InitOption;
   var_inited('tinyMCE',function(){tinyMCE.init(InitOption)});
}
function tiny_mce_form_submit(){
   $('textarea').each(function(i){
         var ed=tinyMCE.get(this.id);
         if(ed!=undefined) {this.value=tinyMCE.get(this.id).getContent();}
   });
}
function getFileName(path) {
   return path.match(/[-_\w]+[.][\w]+$/i)[0];
}
window.insert_attach_file=function(fileattach){
   tinyMCE.activeEditor.execCommand('mceInsertContent', false, "<a class='attachfile' href='"+fileattach+"'>"+getFileName(fileattach)+"</a>");
}
window.insert_img_file=function(fileattach){
   tinyMCE.activeEditor.execCommand('mceInsertContent', false, "<img src='"+fileattach+"' alt=' ' title=' '>");
}
window.insert_miniimg_file=function(fileattach){
   tinyMCE.activeEditor.execCommand('mceInsertContent', false, "<img class='miniature' title=' ' src='"+fileattach+"' alt=' ' align='middle'>");
}

$(document).ready(function(){
      $('form').bind('submit',tiny_mce_form_submit);
      kr_addEvent('submit',tiny_mce_form_submit,'tmce_submit');
});
