<?php
use Whilegit\View\Template;

Template::addSpecialFunc('url', function($url, $url2 = array()){
    $ret = '';
    $params = '';
    if(!empty($url2)){
        foreach($url2 as $k=>$v){
            $params .= "{$k}={$v}&";
        }
        $params = rtrim($params, '&');
    }
    
    if( defined('WG_REWRITE_ON') && WG_REWRITE_ON == true ){
        $ret = "/{$url}";
        if(!empty($params)) $ret .= '?' . $params;
    } else {
        $rs = explode('/', $url);
        
        $ret = "/index.php";
        if(count($rs) > 0 || !empty($params)) {
            $ret .= '?';
            if(isset($rs[0])) $ret .= 'm=' . $rs[0];
            if(isset($rs[1])) $ret .= '&c=' . $rs[1];
            if(isset($rs[2])) $ret .= '&a=' . $rs[2];
            if(!empty($params)){
                if(count($rs) > 0) $ret .= '&';
                $ret .= $params;
            }
        }
    }
    
    return $ret;
});