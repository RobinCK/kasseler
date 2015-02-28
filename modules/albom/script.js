var steps_loads, this_step_load, this_step_photom;
var jcrop_api;

function JcropInit(a, p){
    jcrop_api = $.Jcrop('.guider_description img', {
        bgColor: 'black',
        bgOpacity: .7,
        minSize: [p.width, p.height],
        onChange:function(c){KR_AJAX.jcrop = c;},
        aspectRatio: p.width / p.height
    });
    jcrop_api.animateTo([ 20, 20, $(a).width()-20, 20]);
}

function JcropDestroy(){
    jcrop_api.destroy();
}

function processed_photo(patch, img, p){
    $('#cropHS img').attr('src', patch+img+'?rand='+Math.floor(Math.random() * (1000000 - 1 + 1) ) + 1).bind('load', function(){
        KR_AJAX.animation('show');
        guiders.createGuider({
            buttons: [{name: 'OK', onclick:function(){
                //////////////////////////
                if(!KR_AJAX.jcrop) return false;
                if(KR_AJAX.jcrop.w==0) {alert(window.js_lang.err_sel_crop); return false;}
                var info = {cid:$('.guider #cid').val(), title:$('.guider #title').val(), description:$('.guider #description').val()};
                haja({action:$$('crop_img').value, animation:false}, {
                    'width':KR_AJAX.jcrop.w,
                    'height':KR_AJAX.jcrop.h,
                    'imageWidth':$('.guider #crop_image').width(),
                    'imageHeight':$('.guider #crop_image').height(),
                    'cropLeft':KR_AJAX.jcrop.x,
                    'cropTop':KR_AJAX.jcrop.y,
                    'image':$$('crop_image').src
                }, {
                    onendload:function(){
                        var status = true;
                        try {eval(KR_AJAX.result);} catch(e) {status = false;}
                        if(status==false) alert(window.js_lang.err_crop);
                        else haja({action:$('#updateUrl').val(), animation:false, elm:'update_process'}, {'cid':info.cid, 'title':info.title, 'description':info.description, 'image':$$('crop_image').src}, {
                            onstartload:function(){
                                
                            },
                            onendload:function(){
                                $$('title').value = '';
                                $$('cid').value = '';
                                $$('description').value = '';
                            }
                        });
                        JcropDestroy();
                        guiders.hideAll();
                    }
                });
                //////////////////////////
            }}],
            description: $('#cropHS').html(),
            id: "crop",
            position: 0,
            onHide:function(){
                KR_AJAX.animation('hide');
                $('#cropHS img').unbind('load');
                $('.guider').remove();
            },
            offset:{left:0, top:0},
            overlay: false, xButton:true,
            width:$('#cropHS table:first').width(),
            title: "Crop"
        }).show();
        setTimeout(function(){
            JcropInit('.guider #crop_image', {width:p.w, height:p.h});
        },100)
        setTimeout(function(){
            $('.guider select').removeClass('chzn-none').addClass('chzn-search-hide');
        }, 10);
    });
    
    return false;
}

function upload_ch(d,u,w,h){
    $('#crop_cat').remove(); jcrop_api=null;
    $('.crop_content img').attr('src', d+'category/'+u+'.png?rand='+Math.floor(Math.random() * (1000000 - 1 + 1) ) + 1).bind('load', function(){
        KR_AJAX.animation('show');
        guiders.createGuider({
            buttons: [{name: 'OK', onclick:function(){
                if(KR_AJAX.jcrop.w==0) {
                    alert(window.js_lang.err_sel_crop);
                    return false;
                }
                haja({action:$$('crop_img').value, animation:false}, {
                    'width':KR_AJAX.jcrop.w,
                    'height':KR_AJAX.jcrop.h,
                    'imageWidth':$('#crop_cat').width(),
                    'imageHeight':$('#crop_cat').height(),
                    'cropLeft':KR_AJAX.jcrop.x,
                    'cropTop':KR_AJAX.jcrop.y,
                    'image':$$('crop_cat').src,
                    'type':'cat'
                }, {
                    onendload:function(){
                        var status = true;
                        try {eval(KR_AJAX.result);} catch(e) {status = false;}
                        JcropDestroy();
                        if(status==false) alert(window.js_lang.err_crop);
                        else {if(croped_image) $$('image_prev').innerHTML = "<img style='border: 1px #dfe5ea solid;' src='"+croped_image + '?' + (Math.floor(Math.random() * (1000000 - 1 + 1) ) + 1) + "' alt='' /><input type='hidden' name='image' value='"+croped_image+"' />";}
                    }
                });
                guiders.hideAll();
            }}],
            description: $('.crop_content').html(),
            id: "crop",
            position: 0,
            onHide:function(){
                KR_AJAX.animation('hide');
                $("#Progress").fadeTo('slow', 0.0).fadeTo('slow', 1.0);
                setTimeout(function(){$("#Progress").css("width", '0'); $('#AddPhotos').val(window.defVal);}, 700);
                $('.guider').remove();
                $('.crop_content img').unbind('load');
            },
            overlay: false, xButton:true,
            width:$(this).width(),
            title: "Crop"
        }).show();
        
        $('.guider_description img').attr('id', 'crop_cat');
        setTimeout(function(){JcropInit('#crop_cat', {width:w, height:h})}, 100);
    });
}