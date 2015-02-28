<?php
    if (!defined('KASSELERCMS')) die('Access is limited');
    global $masks;
    $masks=array(8=>'255.0.0.0',9=>'255.128.0.0',10=>'255.192.0.0',11=>'255.224.0.0',12=>'255.240.0.0',13=>'255.248.0.0',
        14=>'255.252.0.0',15=>'255.254.0.0',16=>'255.255.0.0',17=>'255.255.128.0',18=>'255.255.192.0',19=>'255.255.224.0',20=>'255.255.240.0',
        21=>'255.255.248.0',22=>'255.255.252.0',23=>'255.255.254.0',24=>'255.255.255.0',25=>'255.255.255.128',26=>'255.255.255.192',
        27=>'255.255.255.224',28=>'255.255.255.240',29=>'255.255.255.248',30=>'255.255.255.252',31=>'255.255.255.254',32=>'255.255.255.255');
    function calc_mask_dec($ip,$mask_ip){
        global $masks;
        $n=0;
        foreach ($masks as $key => $value) {if($value==$mask_ip) $n=$key;}
        if($n!=0) return calc_mask_info($ip,$n);
        return array();
    }
    function calc_mask_info($ip,$mask){
        if($mask<=0) return array('0.0.0.0','0.0.0.0');
        $maskBin =str_repeat("1",$mask).str_repeat("0", 32-$mask);
        $inv_mask =bindec(str_repeat("0", $mask ) . str_repeat("1",32-$mask));
        $mask_dec=bindec($maskBin);
        $ip_dec=ip2long($ip);
        $net=$ip_dec&$mask_dec;
        $first_ip=$net+1;
        $last_ip=($net | $inv_mask) -1;
        return array($first_ip,$last_ip);
    }
    function ip_dec_valid($ipstr){
        if (preg_match('/([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/i', $ipstr, $regs)) {
            $result = $regs[0];
            for($i=1;$i<=4;$i++){
                if(intval($regs[$i])<0 OR intval($regs[$i])>255) return "127.0.0.1";
            }
            return "{$regs[1]}.{$regs[2]}.{$regs[3]}.{$regs[4]}";
        }
        return "127.0.0.1";
    }
    function calc_filter_info($ip_str){
        if (preg_match('%([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)([\-/])*([^\r\n]*)%si', $ip_str, $regs)) {
            switch($regs[2]){
                case "":return array(ip2long(ip_dec_valid($regs[1]))); break;
                case "-":return array(ip2long(ip_dec_valid($regs[1])),ip2long(ip_dec_valid($regs[3]))); 
                    break;
                case "/":
                    if(is_numeric($regs[3])) return calc_mask_info($regs[1],intval($regs[3]));
                    else return calc_mask_dec($regs[1],$regs[3]);
                    break;
            }
        }
        return array();
    }
?>
