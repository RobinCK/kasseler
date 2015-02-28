jQuery.fn.tabs = function(o){
    var defaults = {classTab:'tabDef'};
    var o = $.extend(defaults, o);
    var self = $(this);
    var tabs = '';
    self.find('.tabTitle').each(function(){
        var i = $(this);
        var key = rewrite_key(null, i.text());
        tabs += "<li><a href='#"+key+"'>"+i.text()+"</a></li>";
        i.parents('.tabContent').attr('id', key);
    });
    self.prepend("<div><input type='hidden' value='' name='hash' class='hash' /><div style='clear: left;'><ul class='tabUl "+o.classTab+"'>"+tabs+"</ul><br /></div></div>");
    self.find('.'+o.classTab+' li a').click(function(){
        self.find('.'+o.classTab+' li a').removeClass('active');
        $(this).addClass('active');
        h = $(this).attr('href').replace('#', '');
        self.find('.hash').val(h);
        self.find('.tabContent:visible').hide();
        self.find('#'+h).show();
        $.setHash({tab:h}, {def:''});
        return false;
    });
};

$.krReady(function(){
    $(window).hashchange(function(){
        $_h = $.getHash();
        if($_h.tab==null) {
            var a = $('.tabUl li a:first');
            $.setHash({tab:a.attr('href').replace('#', '')}, {def:''});
            a.trigger('click');
        } else $('.tabUl li a[href="#'+$_h.tab+'"]').trigger('click');
    });
    setTimeout(function(){$(window).hashchange();}, 100);
});