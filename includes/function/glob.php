<?php
if (!defined('FUNC_FILE')) die('Access is limited');

if(!function_exists('glob')){
    function glob($pattern, $flag='') {
        $path=$output=null;
        $slash = (PHP_OS=='WIN32') ? '\\' : '/';
        $lastpos=strrpos($pattern, $slash);
        if(!($lastpos===false)){
            $_p = basename($pattern);
            $path = str_replace($_p, '', $pattern);
            $pattern = $_p;
        } else{
            $path=getcwd();
        }
        $handle = @opendir($path);
        if($handle===false) return false;
        while($dir=readdir($handle)) {
            if(pattern_match($pattern,$dir)) $output[]=$dir;
        }
        closedir($handle);
        if(is_array($output)) return $output;
        return false;
    }

    function pattern_match($pattern,$string){
        $out=null;
        $chunks=explode(';',$pattern);
        foreach($chunks as $pattern){
            $escape=array('$','^','.','{','}','(',')','[',']','|');
            while(strpos($pattern,'**')!==false) $pattern=str_replace('**','*',$pattern);
            foreach($escape as $probe) $pattern=str_replace($probe,"\\$probe",$pattern);
            $pattern=str_replace('?*','*',
                        str_replace('*?','*',
                            str_replace('*',".*",
                                str_replace('?','.{1,1}',$pattern))));
            $out[]=$pattern;
        }
        if(count($out)==1) return(preg_match("/^$out[0]$/",$string));
        else foreach($out as $tester) if(preg_match("/^$tester$/",$string)) return true;
        return false;
   }
}
?>
