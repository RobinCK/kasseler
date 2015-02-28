var jobList=[];
function addJob(href,data,mfunc_afterfter,type){
   obj={href:href,data:data,func_after:mfunc_afterfter,type:type};
   if (type!=undefined) obj.type=type;
   jobList.push(obj);
}

function execJob(){
   if (jobList.length!=0){
      obj=jobList.shift();
      while (jobList.length>0){
         obj2=jobList.shift();
         if(obj2.type!=obj.type){jobList.unshift(obj2);break;}
         else obj=obj2;
      }
      $.post(obj.href,obj.data,function(data){
            setTimeout('execJob()',1000);
            if (obj.func_after!=undefined&&obj.func_after!=null) obj.func_after(obj,data);
         },'json');
   } else setTimeout('execJob()',1000);
}
execJob();