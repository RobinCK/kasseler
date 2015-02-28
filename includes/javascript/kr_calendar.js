//////////////////////////////////////////////
// Kasseler CMS: Content Management System  //
// =========================================//
// Copyright (c)2007-2009 by Igor Ognichenko//
// http://www.kasseler-cms.net/             //
//////////////////////////////////////////////
if(!window.KR_AJAX) KR_AJAX = {};


(function($_){$_.extend($_, {
    calendar:{
        weekdays:[window.js_lang.Mo, window.js_lang.Tu, window.js_lang.We, window.js_lang.Th, window.js_lang.Fr, window.js_lang.Sa, window.js_lang.Su],
        months:[window.js_lang.january, window.js_lang.february, window.js_lang.march, window.js_lang.april, window.js_lang.may, window.js_lang.june, window.js_lang.july, window.js_lang.august, window.js_lang.september, window.js_lang.october, window.js_lang.november, window.js_lang.december],
        mDays:[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        cells:{width:19, height:15},
        offset:1,
        ids:{},
        date:{},
        set_date:{},

        init:function(e, options){
            if($$(options.year).value=='0000' && $$(options.month).value=='00' && $$(options.day).value=='00'){
                var localTime = new Date();  
                this.date[e] = {year:localTime.getFullYear(), month:localTime.getMonth()+1, day:localTime.getDate()};
            } else this.date[e] = {year:$$(options.year).value, month:$$(options.month).value, day:$$(options.day).value};
            this.ids[e] = options;
            $('body').append($('<div></div>').attr({id:e, 'class':'cal_conteiner'}).css('display', 'none'));
            $$('button_'+e).onclick=function(){
                $_.calendar.set_date[e] = {year:$$(options.year).value, month:$$(options.month).value, day:$$(options.day).value};
                if($$(e).style.display=='none'){
                    var pos = elmPos(this);
                    $$(e).innerHTML = $_.calendar.create.get_html($_.calendar.date[e].year, $_.calendar.date[e].month, $_.calendar.date[e].day, e);
                    setTimeout(function(){
                        document.onclick=function(){KR_AJAX.calendar.hideAll(); document.onclick=function(){}}
                        $$(e).onmouseout=function(){document.onclick=function(){KR_AJAX.calendar.hideAll(); document.onclick=function(){}}}; 
                        $$(e).onmousemove=function(){document.onclick=function(){}};
                        $$(e).onclick=function(){hack_sel();};
                    }, 100);
                    $('#'+e).css({left:pos.left, top:parseInt(pos.top)+17+'px', display:'block'});
                } else $$(e).style.display='none';
            }
        },

        create:{
            get_html:function(y, m, d, n){
               if(m==13) {m=1, y++;} else if(m==0) {m=12, y--;}
               $_.calendar.date[n] = {year:y, month:m, day:d};
               setTimeout(function(){
                   $$('backYear'+n).onclick=function(){$$(n).innerHTML = $_.calendar.create.get_html(parseInt($_.calendar.date[n].year)-1, $_.calendar.date[n].month, $_.calendar.date[n].day, n);}
                   $$('nextYear'+n).onclick=function(){$$(n).innerHTML = $_.calendar.create.get_html(parseInt($_.calendar.date[n].year)+1, $_.calendar.date[n].month, $_.calendar.date[n].day, n);}
                   $$('backMonth'+n).onclick=function(){$$(n).innerHTML = $_.calendar.create.get_html($_.calendar.date[n].year, parseInt($_.calendar.date[n].month)-1, $_.calendar.date[n].day, n);}
                   $$('nextMonth'+n).onclick=function(){$$(n).innerHTML = $_.calendar.create.get_html($_.calendar.date[n].year, parseInt($_.calendar.date[n].month)+1, $_.calendar.date[n].day, n);}
               }, 100);
                var i, days, daycount = 1;
                if($_.calendar.LeapYear(y)) $_.calendar.mDays[1] = 29; else $_.calendar.mDays[1] = 28;
                for(i=days=0;i<m-1;i++) days += $_.calendar.mDays[i];
                var start = $_.calendar.getMonDay(y, days);
                var html = '<table cellspacing=1 cellpadding=2>'+this.head(y, $_.calendar.months[m-1], n);
                while(daycount <= $_.calendar.mDays[m-1]){
                    html += '<tr>';
                    for(i=1; i<=7; i++){
                        if((daycount == 1 && i < start) || daycount > $_.calendar.mDays[m-1]) html += this.cell('&nbsp;', 'cellspase', n);
                        else {
                            classname = (y==$_.calendar.set_date[n].year && m==$_.calendar.set_date[n].month && daycount==$_.calendar.set_date[n].day) ? "thisdate" : "cell";                            
                            html += this.cell(daycount, classname, i, n);
                            daycount++;
                        }
                    }
                    html += '</tr>';
                }
                return html+'</table>';
            },
                
            head:function(year, month, n){
                var head = '<tr><td colspan="7" align="center"><table cellspacing="0" cellpadding="0" width="100%"><tr><td width="'+$_.calendar.cells.width+'" align="center"><span style="cursor: pointer; font-weight: bold;" id="backYear'+n+'">&#171</span>&nbsp;&nbsp;&nbsp;<span style="cursor: pointer; font-weight: bold;" id="backMonth'+n+'">&#8249</span></td><td align="center"><b>'+month+'&nbsp;'+year+'</b></td><td width="'+$_.calendar.cells.width+'" align="center"><span style="cursor: pointer; font-weight: bold;" id="nextMonth'+n+'">&#8250</span>&nbsp;&nbsp;&nbsp;<span style="cursor: pointer; font-weight: bold;" id="nextYear'+n+'">&#187</span></td></tr></table></td></tr><tr>';                
                for (var i=0;i<=6;i++) head += this.cell($_.calendar.weekdays[(i+$_.calendar.offset-1) % 7], 'cellspace', 0, n);
                return head+'</tr>';
            },

            cell:function(content, classname, day, n){
                if(classname!='thisdate') classname = (day==6)?"sa":((day==7)?"su":classname);
                onclic = (content!=='&nbsp;') ? " onclick=\"KR_AJAX.calendar.setDate('"+content+"', '"+n+"')\"" : '';
                return '<td '+onclic+' class="'+classname+'" align="center" width="'+$_.calendar.cells.width+'" height="'+$_.calendar.cells.height+'">'+content+'</td>';
            }
        },

        setDate:function(day, n){
            $$($_.calendar.ids[n].year).value = $_.calendar.date[n].year;
            $$($_.calendar.ids[n].month).value = $_.calendar.date[n].month;
            var month = $$($_.calendar.ids[n].month).value;
            $$($_.calendar.ids[n].month).value = month;
            $$($_.calendar.ids[n].day).value = day;
            $('#'+$_.calendar.ids[n].day).trigger("liszt:updated");
            $('#'+$_.calendar.ids[n].month).trigger("liszt:updated");
            this.hideAll();
        },

        getMonDay:function(year, days) {
            var a = days;
            if(year) a += (year-1)*365;
            for(var i=1;i<year;i++) if(this.LeapYear(i)) a++;
            if(year>1582 || (year==1582 && days>=277)) a -= 10.5;
            if(a) a = (a-this.offset)%7;
            else if(this.offset) a += 7-this.offset;
            return a;
        },

        hideAll:function(){document.onclick=function(){}; for(var i in this.ids){$$(i).style.display='none'; $$(i).onmouseout=function(){}; $$(i).onmousemove=function(){};}},
        LeapYear:function(year){return(!(year%4) && (year<1582 || year%100 || !(year%400)))?true:false;}
    }
})
})(KR_AJAX)