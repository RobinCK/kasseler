tinyMCEPopup.requireLangPack();

var SyntaxHLDialog = {
   wrapper: document.createElement('div'),
   init : function() {
   },

   insert : function() {
      var f = document.forms[0], textarea_output, options = '';

      //If no code just return.
      if(f.syntaxhl_code.value == '') {
         tinyMCEPopup.close();
         return false;
      }


      f.syntaxhl_code.value = f.syntaxhl_code.value.replace(/</g,'&lt;'); 
      f.syntaxhl_code.value = f.syntaxhl_code.value.replace(/>/g,'&gt;'); 
      var n=tinyMCE.activeEditor.selection.getNode();
      if(n.nodeName == 'CODE'){
         n.className=f.syntaxhl_language.value;
         n.innerHTML=f.syntaxhl_code.value;
      } else {
         var textarea_output = '<pre><code '; 
         textarea_output += 'class="' + f.syntaxhl_language.value + '">'; 
         textarea_output += f.syntaxhl_code.value; 
         textarea_output += '</code></pre> '; /* note space at the end, had a bug it was inserting twice? */ 
         this.wrapper.innerHTML = textarea_output;
         tinyMCEPopup.editor.execCommand('mceInsertContent', false, this.wrapper.innerHTML); 
      }
      tinyMCEPopup.close();
   }
};

tinyMCEPopup.onInit.add(SyntaxHLDialog.init, SyntaxHLDialog);
