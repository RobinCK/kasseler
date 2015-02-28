function sendpost(url){kr_execEvent('submit'); haja({action: url, animation: true, elm: 'post_table', add: true, addType: 'append'}, {'message':document.getElementById('message').value, 'last_post':document.getElementById('last_post').value, 'count_post':document.getElementById('count_post').value, 'pages':document.getElementById('pages').value, 'num':document.getElementById('num').value, 'page':document.getElementById('page').value}, {});}
function name_user_set(user){if($$('message')){$$('message').value = $$('message').value + '[b]'+user+'[/b], '; $$('quickreply').style.display = ''; scroll(0, 999999); try {$$('message').focus()} catch(e) {} return false;}}
function send_report(a,b){haja({action:a,dataType:"json",animation:false},{post_id:b},{oninsert:function(){alert(KR_AJAX.result.text)}});return false}

function bind_delete(jA, lang){
    if(confirm(lang)){
        jt=$(jA).parents('.cattable:first');
        var id_post = $(jA).parents('.cattable:first');
        j.ajax({type: 'post', url: jA.href, scriptCharset:'UTF-8', async: true, data: {ajax:true},
            success: function(msg){
                //KR_AJAX.flag_insert = false;
                data = j.parseJSON(msg);
                if(data.status=='ok' || data.status=='content') {
                    id_post.remove();
                    if(data.status=='content'){
                        $('#post_table').append(data.data);
                        $('.forumnum').html(data.num);
                    }
                    $('#pages').val(data.count_pages);
                    $('#page_number').html(data.page_number);
                    /// Update hidden input
                    $('#count_post').val($('table.cattable').length-1);
                    if(data.status!='content') $('#num').val(parseInt($('#num').val())-1);
                    ///
                    ind = (1*data.page>1) ? (data.count_in_page*(data.page-1))+1 : 1*data.page;
                    $('.post_number').each(function(index) {
                        $(this).text('#'+ind);
                        $(this).attr('href', $(this).attr('href').replace(/#entry([0-9]*)/g, '#entry'+ind));
                        ind++;
                    });
                    postrow = 'postrow2';
                    $('.cattable').each(function(i) {
                        $(this).find('tr').attr('class', postrow);
                        postrow = (postrow=='postrow1') ? 'postrow2' : 'postrow1';
                    });
                } else if(data.status=='redirect') document.location.href = data.data;
            },
            error:function(xhr, txt, err){},
            complete:function(XMLHttpRequest, textStatus){},
            beforeSend:function(XMLHRequest){
                $(jA).toggleClass('forum_button3');
            }
        })
    }
    return false;
}