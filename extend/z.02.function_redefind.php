<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// url에 http:// 를 붙인다
if(!function_exists('set_http2')){
function set_http2($url){
    if (!trim($url)) return;
    
    $htp_s = (G5_HTTPS_DOMAIN == '') ? 'http://' : 'https://';
    if (!preg_match("/^(http|https|ftp|telnet|news|mms)\:\/\//i", $url) && substr($url,0,1)!='#')
        $url = $htp_s.$url;

    return $url;
}
}