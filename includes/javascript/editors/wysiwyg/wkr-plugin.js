(function () {
   var imgSel = 'none';
   var wkrpImage = $();
   var plugin = $.wkr_editor.plugins['insertImage'] = {
      init:function(){
         wkrpImage = $('#wkrp_image');
         $.wkr_editor.upload.files = [];
         if(isInited('','$.wkr_editor.defaults.plugins.image.listfiles')){
            $.post($.wkr_editor.defaults.plugins.image.listfiles,{ajax:true,dir:$.wkr_editor.upload.uploaddir},function(data){
               if(data){
                  $.wkr_editor.upload.files = data;
                  plugin.init_image_list();
               }
               },'json');
         }
         var ifile = wkrpImage.find('input[type="file"]');
         var ajx = ifile.parent().find('.ajax16');
         ifile.kr_upload({className:'kr_uplod_mini',caption:'',ext:['jpeg','jpg','png','gif']})
         .on('start',function(){
            ajx.show();
         }).on('finish',function(){
            ajx.hide();
            plugin.init_image_list();
         });
         wkrpImage.find('select').on('change',function(){
            var ilink = $(this).parents('tr:first').prev();
            imgSel = this.value;
            if(this.value=='none') ilink.removeClass('disable');
            else ilink.addClass('disable');
         });
      },
      get_image_param:function(){
         var info = {
            title : wkrpImage.find('.img_title').val(),
            align : wkrpImage.find('.img_align').val(),
         };
         if(imgSel=='none') info.src = wkrpImage.find('.img_url').val();
         else info.src = $.wkr_editor.upload.uploaddir+imgSel;
         return info;
      },
      init_image_list:function (){
         var imgl = $('#wkrp_image').find('.img_upload');
         imgl.find('option:[value!="none"]').remove();
         for(var i=0;i<$.wkr_editor.upload.files.length;i++){
            if ($.wkr_editor.upload.files[i].match(/\.(jpeg|jpg|png|gif)$/im)) {
               imgl.append($('<option/>').val($.wkr_editor.upload.files[i]).text($.wkr_editor.upload.files[i]));
            }
         }
         imgl.trigger("liszt:updated");
      },
      create:function(editor, button, a){
         editor.on('dblclick','img',function(e){
            var img = $(this);
            wkrpImage.find('.img_url').val(img.attr('src'));
            wkrpImage.find('.img_title').val(img.attr('title'));
            wkrpImage.find('.img_align').val(img.attr('align'));
            $.wkr_editor.cmd.showWindowPlugin({id:'wkrp_image_show', title:$.wkr_editor.lang('insertImage'), divId:'#wkrp_image', success:function(){
               var imgp = plugin.get_image_param();
               if(imgp.src!==''){
                  img.attr('src',imgp.src);
                  img.attr('title',imgp.title);
                  if(imgp.align!=='none') img.attr('align', imgp.align);
                  else img.removeAttr('align');
               }
            }});
         })
      },
      command:function(){
         wkrpImage.find('.img_url').val('');
         wkrpImage.find('.img_title').val('');
         wkrpImage.find('.img_align').val('middle');
         $.wkr_editor.cmd.showWindowPlugin({id:'wkrp_image_show', title:$.wkr_editor.lang('insertImage'), divId:'#wkrp_image', success:function(){
            var imgp = plugin.get_image_param();
            if(imgp.src!==''){
               var img = $('<img/>',{src:imgp.src, title: imgp.title});
               if(imgp.align!='none') img.attr('align', imgp.align);
               $.wkr_editor.cmd.htmlInsert(img.get(0));
            }
         }});
      }
   }
})();
$.wkr_editor.defaults.buttons['insertImage'] = {title: 'insertImage',className: 'icon-picture', cmd: 'insertImage', plugin: $.wkr_editor.plugins['insertImage']};


(function () {
   var wkrpLink;
   var plugin = $.wkr_editor.plugins['link'] = {
      create:function(editor, button, a){
         var b = $.wkr_editor.defaults.buttons[button];
         var ul = $('<ul/>',{'class':'dropdown-menu'});
         a.append(ul).attr('data-toggle','dropdown');
         $('<li/>').append($('<a/>',{'data-edit':'createLink', 'data-button':button, text: $.wkr_editor.lang('createLink')})).appendTo(ul);
         $('<li/>').append($('<a/>',{'data-edit':'unlink', 'data-button':button, text: $.wkr_editor.lang('removeLink')})).appendTo(ul);
      },
      command:function(cmd){
         if(cmd=='createLink'){
            wkrpLink.find('textarea').val(selections.asHtml());
            $.wkr_editor.cmd.showWindowPlugin({id:'wkrp_link_show', title:$.wkr_editor.lang('createLink'), divId:'#wkrp_link', success:function(){
               var l = wkrpLink.find('input').val();
               var t = wkrpLink.find('textarea').val();
               var lnk = $('<a/>',{href: l}).html(t);
               $.wkr_editor.cmd.htmlInsert(lnk.get(0));
            }});
         } else if(cmd=='unlink'){
            if(selections.asHtml()==''){
               var a = selections.activeElement();
               if(a.nodeName!='A') a = $(a).parents('a:first').get(0);
               selections.selectElement(a);
            }
            $.wkr_editor.cmd.exec('unlink','');
         }
      },
      init:function(){
         wkrpLink = $('#wkrp_link');
      },
   }
})();
$.wkr_editor.defaults.buttons['link'] = {title: 'Link',className: 'icon-link', plugin: $.wkr_editor.plugins['link']};





//http://caniuse.com/filereader
(function($){
   $.fn.kr_upload = function(option){
      if(!isset(option)) option = {};
      var prg = $();
      this.each(function(){
         if(isset(option.className)){
            var caption = (!isset(option.caption))?'Upload':option.caption;
            var d =$('<div/>',{text:caption,'class':'file'});
            var ds = $('<div/>',{'class':option.className}).insertBefore(this);
            if(isset(option.progress) && option.progress){
               prg = $('<div/>',{'class':'progress'});
               ds.append(prg);
            }
            ds.append(d);
            d.append(this);
         }
         var $obj = $(this);
         $(this).change(function(){
            var objf = this;
            var cf = this.files.length;
            var i = 0;
            var boundary = 28042630015812+Math.round((new Date()).getTime() / 1000);
            var strb = "---------------------------"+boundary;
            var fn = this.files[i];
            var fReader = new FileReader();
            var sendv = '';
            fReader.onload = function (e) {
               i++;
               sendv = sendv + '--'+strb+"\n"+
               "Content-Disposition: form-data; name='"+objf.name+"'; filename='"+fn.name+"'\n"+
               "Content-Type: "+fn.type+"\n\n"+e.target.result+"\n";
               if(i==cf){
                  sendv = sendv +'--'+ strb +"--";
                  $obj.trigger('start');
                  $.ajax({
                     xhr: function(){
                        var xhr;
                        if(window.ActiveXObject) xhr = window.ActiveXObject("Microsoft.XMLHTTP");
                        else var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt){
                           if (evt.lengthComputable) {
                              $obj.trigger('progress', evt);
                              var percentComplete = evt.loaded / evt.total;
                              var nw = 115*percentComplete;
                              prg.css('width',nw+'px').show();
                           }
                           }, false);
                        return xhr;
                     },
                     data: sendv,
                     url: $.wkr_editor.upload.link,
                     type: "POST",
                     cache: false,
                     processData: false,
                     //dataType: 'json',
                     contentType: 'multipart/form-data; boundary='+strb,
                     'Content-Length': sendv.length,
                     success: function(data){
                        var files = $.parseJSON(data);
                        if(files!==null) $.wkr_editor.upload.files = files;
                        $obj.trigger('finish');
                        prg.css('width','115px').show();
                        setTimeout(function(){prg.hide();},500);
                     }
                  });
               } else {
                  fn = objf.files[i];
                  fReader.readAsDataURL(objf.files[i]);
               }
            };
            fReader.readAsDataURL(this.files[i]);
         });
      })
      return this;
   };
})(jQuery);   
(function ($) {
   $.fn.iframePostForm = function (a) {
      var b, returnReponse, element, status = true,
      iframe;
      a = $.extend({}, $.fn.iframePostForm.defaults, a);
      if (!$('#' + a.iframeID).length) $('body').append('<iframe id="' + a.iframeID + '" name="' + a.iframeID + '" style="display:none" />');
      return $(this).each(function () {
         element = $(this);
         element.attr('target', a.iframeID);
         element.submit(function () {
            status = a.post.apply(this);
            if (status === false) return status;
            iframe = $('#' + a.iframeID).load(function () {
               b = iframe.contents().find('body');
               returnReponse = (a.json) ? $.parseJSON($.trim(b.html())) : b.html();
               a.complete.apply(this, [returnReponse]);
               iframe.unbind('load');
               setTimeout(function () {
                  b.html('')
                  }, 1)
            })
         })
      })
   };
   $.fn.iframePostForm.defaults = {
      iframeID: 'iframe-post-form',
      json: true,
      post: function () {},
      complete: function (a) {}
   }
})(jQuery);   
