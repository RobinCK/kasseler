$.krReady(function(){
    var load_search_tpl = null;
    var intevalPm;
    
    $("#search_recipient").autocomplete('index.php?module=account&do=pm&op=search&ajaxonly=true', {
        delay:10,
        minChars:1,
        matchSubset:1,
        autoFill:false,
        matchContains:1,
        cacheLength:10,
        selectFirst:false,
        formatItem:liFormat,
        maxItemsToShow:20,
        lineSeparator:'\r',
        onInput:function(msg){
            if(msg.length==0){ 
                $('.list_users_pm, .autoloadclass').show();
                $('.close_search').css('visibility', 'hidden');
                $('.search_users_pm').hide();
            }
        },
        onShow:function(){
            if(load_search_tpl==null) $.get(window.pm_path+'tpl/javascript/list_row.html', function(file){load_search_tpl = file;});
            if($('.hidden_res').length>0) {
                $('.list_users_pm, .autoloadclass').hide();
                $('.close_search').css('visibility', 'visible');
                $('.search_users_pm').show();
                intevalPm = setInterval(function(){
                    if(load_search_tpl!=null){
                        b = $.browser.safari ? $('body') : $('html');
                        offset = $('#margin_options').offset().top;
                        if(offset<b.scrollTop()){
                            if($.browser.safari) b.animate({scrollTop: offset }, 1000);
                            else b.animate({scrollTop: offset}, 1000);
                        }
                        row = '';
                        $('.search_users_pm').html("<table width='100%' class='ressearch'>"+row+"</table>");
                        pm_row = 'pm_row1';
                        $('.hidden_res').each(function(index) {
                            res = $(this).val().split('{([])}');
                            $.tmpl( load_search_tpl, {
                                'class_row'         : pm_row,
                                'avatar'            : res[1],
                                'small_avatar'      : res[2],
                                'sender'            : res[3],
                                'date'              : res[4],
                                'subject'           : res[5],
                                'small_text'        : res[6],
                                'small_text_link'   : res[7],
                                'text'              : res[8]
                            }).appendTo('.ressearch');
                            pm_row = pm_row=='pm_row1' ? 'pm_row2' : 'pm_row1';
                        });
                        clearInterval(intevalPm);
                    }
                }, 100);
            }
            $('.ac_results').hide();
        }
    });
    
    $(".pm_table tr, .ressearch tr").live("mouseover mouseout", function(event){
        if( event.type === 'mouseover' ) {
            self = $(this).find('.delte_pm:first');
            parentel = $(self).parent('td');
            $(self).css({zIndex: 2, position: 'absolute', left:parentel.offset().left+parentel.width()-self.width(), top:parentel.offset().top}).show();
        } else $(this).find('.delte_pm:first').hide();
    });
    
    if($('.autoloadpage').length>0) $('.autoloadpage').scrollLoad({
        action:'index.php?module=account&do=pm&op=autoload',
        tpl:window.pm_path+'tpl/javascript/list_row.html',
        appendTo:'.pm_table tbody',
        correction:-250,
        onstar:function(o){
            o.data['page'] = parseInt($('.autoloadpage').val())+1;
            $('.autoloadpage').val(o.data['page']);
        },
        onappend:function(){
            r = 'pm_row1';
            $('.pm_table tr').each(function(){
                $(this).removeClass('pm_row1').removeClass('pm_row2').addClass(r);
                r = (r=='pm_row1') ? 'pm_row2' : 'pm_row1';
            });
        }
    });
    
    $('.delte_pm a').live('click', function(){
        self = this;
        if(confirm(window.js_lang.realdelete)) {
            haja({action:'index.php?module=account&do=pm&op=delete', animation:false}, {user:$(self).parents('tr:first').find('a.user_info:last').text()}, {
                onstartload:function(){$(self).parents('tr:first').hide();}
            });
        } 
        return false;
    });
    
    $('.pm_message1, .pm_message2').live('click', function(){
        $(this).toggleClass('pm_message_sel');
        var cl = $(this).attr('class').indexOf('pm_message1')>-1 ? 'pm_message1' : 'pm_message2';
        if($(this).next().length>0 && $(this).next().attr('class').indexOf(cl)>-1) el = $(this).next();
        else el = $(this).prev();
        el.toggleClass('pm_message_sel');
        if($('.pm_message_sel').length>0) $('.delete_message').show();
        else $('.delete_message').hide();
    });
});

function delete_messages(){
    var ids = [];
    $('.pm_message_sel').each(function(){
        v = $(this).find('.hide_id').val();
        if($.inArray(v, ids)==-1) ids.push(v);
    }).remove();
    if(ids.length>0){
        $('.delete_message').hide();
        haja({action:'index.php?module=account&do=pm&op=delete_message', animation:false}, {data:ids}, {
            onstartload:function(){
                setTimeout(function(){
                    if($('.pm_message1, .pm_message2').length==0) location.href = $('.backlink').attr('href');
                }, 100);
            }
        });
    }
    return false;
}

function close_search_pm(){
    $('#search_recipient').val('');
    $('.list_users_pm').show();
    $('.close_search').css('visibility', 'hidden');
    $('.search_users_pm').hide();
    return false;
}

function liFormat (row, i, num) {
    return "<input class='hidden_res' type='hidden' value=\""+row[0]+"|"+row[1]+"|"+row[2]+"\">";
}


jQuery.fn.fixFloat = function(options){
    var defaults = {enabled: true};
    var options = $.extend(defaults, options);
    var offsetTop;
    var s;
    var fixMe = true;
    var repositionMe = true;
    var tbh = $(this);
    var originalOffset = tbh.offset().top;
    var originalWidth = tbh.width();
    tbh.css({'position':'absolute', 'width': originalWidth});
    if(options.enabled){
        $(window).scroll(function(){
            var offsetTop = tbh.offset().top;   /**Get the current distance of the element from the top**/
            var s = parseInt($(window).scrollTop(), 10);    /**Get distance from the top of window through which we have scrolled**/
            var fixMe = true;
            if(s > offsetTop){fixMe = true;}
            else{fixMe = false;} 
            if(s < parseInt(originalOffset, 10)){repositionMe = true;}
            else{repositionMe = false;}
            if(fixMe){
                var cssObj = {'position' : 'fixed', 'top' : '0px'}
                tbh.css(cssObj);
            }
            if(repositionMe){
                var cssObj = {'position' : 'absolute', 'top' : originalOffset}
                tbh.css(cssObj);
            }
        });
    }
};
 
jQuery.fn.scrollLoad = function(o){
    var defaults = {onstar:function(){},onend:function(){},onappend:function(){}, action:'', data:{}, method:'get', tpl:'', appendTo:'', correction:0};
    var o = $.extend(defaults, o);
    var stopLoad = false;
    var loaded = false;
    if(o.action!=''){
        var self = this;
        $(window).scroll(function(){
            if($(self).is(":visible"))
            if($(self).offset().top + o.correction < $(window).scrollTop()+$(window).height() && loaded==false && stopLoad == false){
                o.onstar(o)
                haja({action:o.action, method:o.method, animation:false}, o.data, {
                    onstartload:function(){loaded = true;},
                    onendload:function(msg){
                        o.onend();
                        data = $.parseJSON(msg);
                        if(data.content.length>0) {
                            $.get(o.tpl, function(load_tpl){
                                for(i=0;i<data.content.length; i++) $.tmpl(load_tpl, data.content[i]).appendTo(o.appendTo);
                                o.onappend();
                            });
                        } else stopLoad = true;
                        loaded = false;
                    }
                });
            }
        }); 
    }
};

var load_show_tpl;

function show_paste(data, cl){
    $.tmpl( load_show_tpl.replaceAll('replce_class', cl), data).prependTo('.pm_message_table tbody:first');
}

function send_pm_message(){
    kr_execEvent('submit');
    haja({animation:false, action:$('.message_post').attr('action')}, {
        lastid:$('.message_id:first').val(),
        message:$('#message').val(),
        recipient:$('#recipient').val(),
        subj:$('#subj').val()
    }, {
        onstartload:function(){
            $('body,html').animate({scrollTop :0}, 900);
        },
        onendload:function(data){
            $('#message').val('');
            data = $.parseJSON( data );
            c = $('.pm_message_table tr:first').attr('class');
            rowc = c=='pm_message1' ? 'pm_message2' : 'pm_message1';
            if(data.status=='ok'){
                $.get(window.pm_path+'tpl/javascript/show_row.html', function(con){
                    load_show_tpl = con;
                    for(var i=0;i<data.content.length;i++) {
                        show_paste(data.content[i], rowc);
                        rowc = rowc=='pm_message1' ? 'pm_message2' : 'pm_message1';
                    }
                });
                
                'pm_message1'?'pm_message2':'pm_message1'
            } else alert(data.message);            
        }
    });
}