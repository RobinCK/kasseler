//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2009 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
function loadinfo(file, id){haja({action:file+'?loadinfo='+id, animation:false, elm:'blockinfo_cont'}, {'key':id}, {});}
function captcha(){var now = new Date(); $$('imgseccode').src = 'captcha.php?time='+now;}
function chk_ver(ver, lang){haja({animation:false, action:'index.php?ajaxed=chk_ver&version='+ver, elm:'ver_chk'}, {}, {onstartload:function(){$$('ver_chk').innerHTML = lang;}})}
function delete_cat_forum(lang){if(confirm(lang)) return true; else return false;}

function edit_value(object, url, id){
    var shortcut;
    KR_AJAX.include.style('test.css');
    object.innerHTML = '<input class="ajax_edit" style="width: 100%" type="text" id="text_'+object.id+'" name="'+object.id+'" value="'+object.innerHTML+'">';
    try {$$("text_"+object.id).focus();}
    catch(e) {}
    $$("text_"+object.id).onblur = function(){save_value(object, this.value, url, id);}
    object.ondblclick = function(){}
}

function save_value(object, text, url, id){
    if(text!=""){
        $$("text_"+object.id).value = "";
        object.innerHTML = text;
        object.ondblclick = function(){edit_value(object, url, id);}
        ajaxed({action : url, animation : false}, {'text' : text, 'col' : 'title', 'id' : id});
    } else {
        alert("The field can not be empty");
        try {$$("text_"+object.id).focus();}
        catch(e) {}
    }
}

function load_case(url, id, value, save, obj){
    hack_sel();
    value = ($$('hide_'+id)) ? $$('hide_'+id).value : value;
    haja({action : url, animation : false, elm : id}, {'value' : value}, {
        onstartload : function(){$$(id).innerHTML = window.small_load; $$(id).ondblclick = function(){}},
        oninsert : function(){
            try {$$('sel_ajax').focus();} catch(e) {}
            var save_func = function(){haja({action : save, animation : false, elm : obj}, {'value' : $$('sel_ajax').value, 'id' : id}, {onstartload : function(){$$(id).innerHTML = window.small_load;}, onendload : function(){$$(id).ondblclick = function(){load_case(url, id, value, save, obj);}}});};
            $$('sel_ajax').onchange = save_func;
            $$('sel_ajax').onblur = save_func;
        }}
    );
}

function ScrollTo(e){
    if($$('scroller') && $$(e)){
        var pos = elmPos($$(e));
        var pos_con = elmPos($$('mod_con'));
        var scrolls = parseInt(pos.top)-parseInt(pos_con.top);
        var step = scrolls/100;
        for(var i=1;i<=scrolls;i+=step){
            setTimeout("document.getElementById('scroller').scrollTop = "+i+";", 1);
        }
    }
}


function onoff(url, id){haja({action : url, animation : false, elm : id}, {}, {onstartload : function(){$$(id).innerHTML = window.small_load;}});}

function select_form_elm(e){
    if(e.value=='text' || e.value=='textarea') $$('def_val').style.display = '';
    else $$('def_val').style.display = 'none';
    if(e.value=='checkbox') $$('def_val_ck').style.display = '';
    else $$('def_val_ck').style.display = 'none';
    if(e.value=='radio' || e.value=='select') $$('case_val').style.display = '';
    else $$('case_val').style.display = 'none';    
    if(e.value=='file'){
        $$('must').disabled = true;
        $$('name').disabled = true;
    } else {
        $$('must').disabled = false;
        $$('name').disabled = false;
    }
}

selected = 1;
function addinput_case(id, name, clas){
    var arr_inputs = document.getElementsByTagName('input'); 
    var values_array = []; 
    for(var i=0;i<arr_inputs.length-1;i++) if(arr_inputs[i].type=='text') values_array[i] = arr_inputs[i].value; 
    cl = (clas) ? " class='"+clas+"'" : "";  
    $$(id).innerHTML+= "<table width='100%' cellspacing='0' cellpadding='0' style='margin-top: 2px;'><tr><td width='20' align='center'><input type='radio' name='selected' value='"+selected+"' /></td><td><input"+cl+" type='text' name='"+name+"[]' value='' /></td></tr></table>"; 
    selected++;
    arr_inputs = document.getElementsByTagName('input'); 
    for(var i=0;i<arr_inputs.length-1;i++){
        if(arr_inputs[i].type=='text') {
            if(arr_inputs[i].type=='text') {
                arr_inputs[i].value = (values_array[i]!='undefined' && values_array[i]!='') ? values_array[i] : ''; 
                if(arr_inputs[i].value=='undefined') arr_inputs[i].value='';
            }
        }
    }
}

$('.ico_disabled').on('click', function(){this.href='javascript.void(0)'; return false;});

function hooks_info(e, url){
    if(e.className.indexOf('ico_disabled')==-1) haja({action:url, animation:false}, {}, {
        onstartload:function(){
            KR_AJAX.animation('show');
        },
        onendload:function(data){
            d = $.parseJSON(data);
            if(d.status=='ok'){
                $.get('templates/admin/javascript/plugin_info.html', function(file) {
                    guiders.createGuider({
                        buttons: [],
                        description: "<hr /><div class='info_plugin'></div>",
                        id: "plugin_info",
                        position: 0,
                        onHide:function(){
                            KR_AJAX.animation('hide');
                            $(".guider").remove();
                        },
                        overlay: false, xButton:true,
                        width: 600,
                        title: d.content.lang.window_title
                    }).show();
                    $.template("fc", file);
                    $.tmpl("fc", d.content).appendTo(".info_plugin");
                });
            } else alert(d.message);
        }
    });
    return false;
}