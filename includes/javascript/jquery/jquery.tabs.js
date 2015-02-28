$(function () {
    var tc = $('div.tabs .tabs_content > div'), h = $.getHash(), db_tabs = [], sti = this_hash = '';
    tabs = false;
    if(!h.tabs){        
        ide = $('div.tabs .tabs_content .default_tab').attr('id');
        if(ide){
            tabs = true;
            $.setHash({tabs:ide});
        }
    }
    $('div.tabs .tabs_links ul.res a').each(function(index) {db_tabs[db_tabs.length] = this.rel; this.id='tab_'+this.rel;});
    for (var i=0;i<db_tabs.length;i++) if(db_tabs[i]==h.tabs){sti = db_tabs[i]; break;}
    $('div.tabs .tabs_links ul.res li').each(function(){
        this.onmousemove = function(){$(this).addClass('hover')}
        this.onmouseout = function(){$(this).removeClass('hover')}
    });
    $('div.tabs .tabs_links ul.res a').click(function () {
        var self = this;
        var show_tab = function(){
            if(self.rel!=h.tabs) $.setHash({tabs:self.rel});
            tc.hide().filter('#'+self.rel).show();
            $('div.tabs .tabs_links ul.res li').removeClass('selected');
            $(self).parent().addClass('selected');
        }
        if(self.href.indexOf('#')==-1 && self.href.length>5 && self.target != 'loaded'){
            html = $('#'+self.rel).html();
            if(h.tabs==self.rel) 
                if(self.name.indexOf('nostart')==-1 || html=='') {
                    haja({action: self.href, elm:'#'+self.rel, effect_elm:'div.tabs'}, {'history_load':'true'}, {onendload:function(){if(self.target=='reload') $('div.tabs .tabs_content div:visible').children().remove();}, oninsert:function(){
                        if(self.target!='reload') self.target = 'loaded';
                        show_tab();
                    }});
                } else show_tab();
            else $.setHash({tabs:self.rel});
        } else show_tab();
        $('div.tabs .tabs_links ul.res a').attr('name', '');
        return false;
    });
    if(tabs==false && sti=='') $('div.tabs .tabs_links ul.res a').filter(':first').click();
    else if(tabs==true) $('div.tabs .tabs_links ul.res a#'.ide).click();
    KR_AJAX.ifunction['tabs_update'] = function(){
        h = $.getHash();
        if(this_hash!=h.tabs){
            this_hash = h.tabs;
            for (i=0;i<db_tabs.length;i++) if(db_tabs[i]==h.tabs){sti = db_tabs[i]; break;}
            if(sti!='') $('#tab_'+sti).click();
        }
    }
});