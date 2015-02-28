String.prototype.zf = function(l) { return '0'.string(l - this.length) + this; }
String.prototype.string = function(l) { var s = '', i = 0; while (i++ < l) { s += this; } return s; }
Number.prototype.zf = function(l) { return this.toString().zf(l); }
Date.prototype.format = function(f)
{
   if (!this.valueOf())
      return '&nbsp;';
   var d = this;
   return f.replace(/(yyyy|mmmm|mmm|mm|dddd|ddd|dd|hh|nn|ss|a\/p)/gi,
      function($1)
      {
         switch ($1.toLowerCase())
         {
            case 'yyyy': return d.getFullYear();
            case 'mmmm': return gsMonthNames[d.getMonth()];
            case 'mmm':  return gsMonthNames[d.getMonth()].substr(0, 3);
            case 'mm':   return (d.getMonth() + 1).zf(2);
            case 'dddd': return gsDayNames[d.getDay()];
            case 'ddd':  return gsDayNames[d.getDay()].substr(0, 3);
            case 'dd':   return d.getDate().zf(2);
            case 'hh':   return ((h = d.getHours() % 12) ? h : 12).zf(2);
            case 'nn':   return d.getMinutes().zf(2);
            case 'ss':   return d.getSeconds().zf(2);
            case 'a/p':  return d.getHours() < 12 ? 'a' : 'p';
         }
      }
   );
}
function elmPos(e){e = $$(e); var X = 0; var Y = 0; while(e != null){X += e.offsetLeft; Y += e.offsetTop; e = e.offsetParent;} return {left:X+'px', top:Y+'px'}}

(function($kr){$kr.extend($kr, {   
            kr_calendar:{
               weekdays:[window.js_lang.Mo, window.js_lang.Tu, window.js_lang.We, window.js_lang.Th, window.js_lang.Fr, window.js_lang.Sa, window.js_lang.Su],
               months:[window.js_lang.january, window.js_lang.february, window.js_lang.march, window.js_lang.april, window.js_lang.may, window.js_lang.june, window.js_lang.july, window.js_lang.august, window.js_lang.september, window.js_lang.october, window.js_lang.november, window.js_lang.december],
               mDays:[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
               cells:{width:19, height:15},
               offset:1,  
               ids:{},      
               date:{},
               set_date:{},
               onchange:function(){},
               init:function(e, options){
                  var img=e; e=e+'_div' ;
                  var d = $$(options.el).value.split('.');
                  if(d.length<3) d = $$(options.el).value.split('-');
                  if(d.length<3) {var t=new Date();d = t.format('dd.mm.yyyy').split('.');}
                  d[0] = (d[0][0]=='0') ? d[0].replace('0', '') : d[0];
                  d[1] = (d[1][0]=='0') ? d[1].replace('0', '') : d[1];
                  this.date[e] = {year:d[2], month:d[1], day:d[0]};
                  this.ids[e] = options;
                  this.ids[e].stop_edit=(options.diapazon!=undefined)?2:1;
                  this.ids[e].num_edit=0;
                  $('<div/>').attr('id',e).addClass('cal_conteiner').css({'display':'none','position': 'absolute', 'z-index': '500'}).appendTo('body');
                  $('#'+img).on('click',function(){                
                     $kr.kr_calendar.ids[e].num_edit=0;
                     var d = $$($kr.kr_calendar.ids[e].el).value.split('.');
                     if(d.length<3) d = $$($kr.kr_calendar.ids[e].el).value.split('-');
                     if(d.length<3) {var t=new Date();d = t.format('dd.mm.yyyy').split('.');}
                     d[0] = (d[0][0]=='0') ? d[0].replace('0', '') : d[0];
                     d[1] = (d[1][0]=='0') ? d[1].replace('0', '') : d[1];
                     $kr.kr_calendar.set_date[e] = {year:d[2], month:d[1], day:d[0]};
                     if($$(e).style.display=='none'){                                                                  
                        var pos = elmPos(this);                                                                                                                                   
                        $$(e).innerHTML = $kr.kr_calendar.create.get_html($kr.kr_calendar.date[e].year, $kr.kr_calendar.date[e].month, $kr.kr_calendar.date[e].day, e);                                        
                        setTimeout(function(){
                              document.onclick=function(){KR_AJAX.kr_calendar.hideAll(); document.onclick=function(){}}
                              $$(e).onmouseout=function(){document.onclick=function(){KR_AJAX.kr_calendar.hideAll(); document.onclick=function(){}}}; 
                              $$(e).onmousemove=function(){document.onclick=function(){}};
                              $$(e).onclick=function(){};
                           }, 100);
                        $($$(e)).css({left:pos.left, top:parseInt(pos.top)+17+'px', display:'block'});
                     } else $$(e).style.display='none';
                  })
               },        

               create:{
                  get_html:function(y, m, d, n){               
                     if(m==13) {m=1, y++;} else if(m==0) {m=12, y--;}
                     $kr.kr_calendar.date[n] = {year:y, month:m, day:d};
                     setTimeout(function(){
                           $$('backYear'+n).onclick=function(){$$(n).innerHTML = $kr.kr_calendar.create.get_html(parseInt($kr.kr_calendar.date[n].year)-1, $kr.kr_calendar.date[n].month, $kr.kr_calendar.date[n].day, n);}
                           $$('nextYear'+n).onclick=function(){$$(n).innerHTML = $kr.kr_calendar.create.get_html(parseInt($kr.kr_calendar.date[n].year)+1, $kr.kr_calendar.date[n].month, $kr.kr_calendar.date[n].day, n);}
                           $$('backMonth'+n).onclick=function(){$$(n).innerHTML = $kr.kr_calendar.create.get_html($kr.kr_calendar.date[n].year, parseInt($kr.kr_calendar.date[n].month)-1, $kr.kr_calendar.date[n].day, n);}
                           $$('nextMonth'+n).onclick=function(){$$(n).innerHTML = $kr.kr_calendar.create.get_html($kr.kr_calendar.date[n].year, parseInt($kr.kr_calendar.date[n].month)+1, $kr.kr_calendar.date[n].day, n);}
                        }, 100);       
                     var i, days, daycount = 1;
                     if($kr.kr_calendar.LeapYear(y)) $kr.kr_calendar.mDays[1] = 29; else $kr.kr_calendar.mDays[1] = 28;            
                     for(i=days=0;i<m-1;i++) days += $kr.kr_calendar.mDays[i];
                     var start = $kr.kr_calendar.getMonDay(y, days);
                     var html = '<table cellspacing=1 cellpadding=2>'+this.head(y, $kr.kr_calendar.months[m-1], n);
                     while(daycount <= $kr.kr_calendar.mDays[m-1]){
                        html += '<tr>';
                        for(i=1; i<=7; i++){
                           if((daycount == 1 && i < start) || daycount > $kr.kr_calendar.mDays[m-1]) html += this.cell('&nbsp;', 'cellspase', n);
                           else {
                              classname = (y==$kr.kr_calendar.set_date[n].year && m==$kr.kr_calendar.set_date[n].month && daycount==$kr.kr_calendar.set_date[n].day) ? "thisdate" : "cell";                            
                              html += this.cell(daycount, classname, i, n);
                              daycount++;
                           }
                        }
                        html += '</tr>';                
                     }
                     return html+'</table>';    
                  },

                  head:function(year, month, n){                                                    
                     var head = '<tr><td colspan="7" align="center"><table cellspacing="0" cellpadding="0" width="100%"><tr><td width="'+$kr.kr_calendar.cells.width+'" align="center"><span style="cursor: pointer; font-weight: bold;" id="backYear'+n+'">&#171</span>&nbsp;&nbsp;&nbsp;<span style="cursor: pointer; font-weight: bold;" id="backMonth'+n+'">&#8249</span></td><td align="center"><b>'+month+'&nbsp;'+year+'</b></td><td width="'+$kr.kr_calendar.cells.width+'" align="center"><span style="cursor: pointer; font-weight: bold;" id="nextMonth'+n+'">&#8250</span>&nbsp;&nbsp;&nbsp;<span style="cursor: pointer; font-weight: bold;" id="nextYear'+n+'">&#187</span></td></tr></table></td></tr><tr>';                
                     for (var i=0;i<=6;i++) head += this.cell($kr.kr_calendar.weekdays[(i+$kr.kr_calendar.offset-1) % 7], 'cellspace', 0, n);
                     return head+'</tr>';
                  },

                  cell:function(content, classname, day, n){
                     if(classname!='thisdate') classname = (day==6)?"sa":((day==7)?"su":classname);
                     onclic = (content!='&nbsp;') ? ' onclick="KR_AJAX.kr_calendar.setDate(\''+content+'\', \''+n+'\')"' : '';
                     return '<td '+onclic+' class="'+classname+'" align="center" width="'+$kr.kr_calendar.cells.width+'" height="'+$kr.kr_calendar.cells.height+'">'+content+'</td>';
                  }
               },

               setDate:function(day, n){
                  var month = ($kr.kr_calendar.date[n].month<10) ? '0'+$kr.kr_calendar.date[n].month:$kr.kr_calendar.date[n].month;
                  var day = (day<10) ? '0'+day:day;
                  var num_edit=$kr.kr_calendar.ids[n].num_edit;
                  var stop_edit=$kr.kr_calendar.ids[n].stop_edit;
                  num_edit++;
                  obj=$$($kr.kr_calendar.ids[n].el); ndate=day+'.'+month+'.'+$kr.kr_calendar.date[n].year;
                  if (num_edit==1) obj.value = ndate;
                  else if (obj.value!=ndate) obj.value =obj.value+' - '+ ndate;
                  $kr.kr_calendar.onchange($$($kr.kr_calendar.ids[n].el));
                  if (num_edit==stop_edit) this.hideAll();
                  $kr.kr_calendar.ids[n].num_edit=num_edit;
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
