$(document).ready(function(){
    if(!(!window.ajaxload || window.ajaxload!=true)) {
        $('<a href="#">ajax_load</a>').appendTo('body').attr('class', 'sys_link hide_ajax_load').css('display', 'none');
        var class_link=(window.classes)?window.classes:[], cl=0; 
        for(cl=0;cl<class_link.length;cl++) class_link[cl] = '.'+ class_link[cl];
        var sel = class_link.join(', ');        
        
        $(sel).live("click", function(event){
            if(this.href!='#' && this.href.indexOf('logout')==-1){
                //////////////////
                var href = this.href.replace('http://'+location.host+'/', '');
                if(href.indexOf(KR_AJAX.this_module)!=-1){
                    crc = crc32(href+'ajax_content');
                    crc = 'l'+((crc<0) ? crc*-1 : crc);
                    $.setHash({url:href}, {def:''});
                    if(!ajax_history[crc]) {
                        haja({elm:'ajax_content', action:this.href, history:true}, {'module':KR_AJAX.this_module,'load_module':'true'}, {
                            onstartload : function(){},
                            onendload : function(msg){
                                
                            }, 
                            oninsert : function(){
                                if (/^<title>(.*?)<\/title>/.test(KR_AJAX.result)) {
                                    match = KR_AJAX.result.match(/<title>(.*?)<\/title>/);
                                    document.title = title= match[1];
                                } else title=document.title;
                                ajax_history[crc] = {title:title, content:KR_AJAX.result};
                                this_crc_request = crc;
                            }
                        });
                    } else {
                        $('#ajax_content').html(ajax_history[crc].content);
                        document.title = ajax_history[crc].title;
                        this_crc_request = crc;
                    }
                    return false;
                } else location.href = this.href;
                //////////////////
            } else return true;
        });
        
        $(window).hashchange(function(){
            $_h = $.getHash();
            if($_h.url==null) {
                $.setHash({url:'~'}, {def:''});
                crc_ = crc32('~ajax_content');
                window.this_crc_request = crc_ = 'l'+((crc_<0) ? crc_*-1 : crc_);
                window.ajax_history[crc_] = {};
                window.ajax_history[crc_].content = $('#ajax_content').html();
                window.ajax_history[crc_].title = document.title;
            }
            crc = crc32($_h.url+'ajax_content');
            crc = 'l'+((crc<0) ? crc*-1 : crc);
            if(window.ajax_history && window.ajax_history[crc] && crc!=this_crc_request){
                $('#ajax_content').html(window.ajax_history[crc].content);
                document.title = window.ajax_history[crc].title;
                window.this_crc_request = crc;
            } else if($_h.url!=null && window.this_crc_request=='' && $_h.url != '~'){
                window.this_crc_request = crc;
                $('.hide_ajax_load').attr('href', $_h.url).trigger('click');
            }
        });
        
        $(window).hashchange();
    }
});