function check_ip_and_mask(str){
   var ipmaskregexp=new RegExp('([0-9.]+)([\-\/]+)*(.*)', "i");
   var flag=false;
   var match = ipmaskregexp.exec(str);
   if(match!=null){
      flag=check_ip(match[1]);
      if(flag && match[2]!=undefined){
         switch(match[2]){
            case "-":flag=check_ip(match[3]); break;
            case "/":if($.isNumeric(match[3])) flag=mask_num_check(match[3]);else flag=check_ip(match[3]);break;
            case "":break;
            default: flag=false; break;
         }
      }
   }
   return flag;
}
function mask_num_check(nip){return !(parseInt(nip)<8 || parseInt(nip)>31);}
function ip_num_check(nip){return !(parseInt(nip)<0 || parseInt(nip)>255);}
function check_ip(ip){
   var flag=false;
   if(ip!=""){
      var ipregexp = new RegExp('([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)',"i");
      var match = ipregexp.exec(ip);
      if(match!=null){flag=true; for(var i=1;i<=4;i++){if(match[i]=="" || !ip_num_check(match[i])) flag=false;}}
   }
   return flag;
}
