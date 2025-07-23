<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// URL에서 디렉토리명, 파일명 추출
//echo basename($_SERVER["SCRIPT_FILENAME"]);
$path_info=pathinfo($_SERVER['SCRIPT_FILENAME']);
$path_info['dirname'] = preg_replace("/\\\/", "/", $path_info['dirname']);
$g5['dir_name'] = substr($path_info['dirname'],strrpos($path_info['dirname'],'/')+1,strlen($path_info['dirname']));
$g5['dir_path'] = preg_replace("|".G5_PATH."|", "", $path_info['dirname']);
$g5['file_name'] = $path_info['filename'];
$g5['file_path'] = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/'.$g5['file_name']));

// 사이트의 루트 디렉토리명을 추출
$root_path_arr = explode('/',$_SERVER['DOCUMENT_ROOT']);
$g5['root_dir'] = $root_path_arr[sizeof($root_path_arr)-1];
unset($root_path_arr);
// 웹 index인지를 확인하는 변수
$g5['is_web_index'] = ($g5['dir_name'] == $g5['root_dir'] && $g5['file_name'] == 'index') ? 1 : 0;
// 쇼핑몰 index인지를 확인하는 변수
$g5['is_shop_index'] = ($g5['dir_name'] == 'shop' && $g5['file_name'] == 'index') ? 1 : 0;
// 또는 웹/쇼핑몰 구분없이 index(단, 그 외 다른경로의 index는 제외)인지를 확인하는 변수
$g5['is_index'] = ($g5['is_web_index'] || $g5['is_shop_index']) ? 1 : 0;

include_once(G5_ZSQL_PATH.'/set_conf.php');
include_once(G5_ZSQL_PATH.'/set_plf.php');
include_once(G5_ZSQL_PATH.'/set_com.php');

// 접근가능한 IP인지, 접근차단된 IP인지 확인 접근불가능시 차단메세지 출력
if(!$is_admin && $g5['dir_name'] != 'bbs' && $g5['file_name'] != 'login'){
    // 접근가능한 IP인지 확인
    // $set_possible_ip = trim($set_com['set_possible_ip']);
    $set_possible_ip = isset($set_com['set_possible_ip']) ? trim($set_com['set_possible_ip']) : '';

    if($set_possible_ip){
        $is_possible_ip = false;
        $pattern = explode("\n", $set_possible_ip);
        for ($i=0; $i<count($pattern); $i++) {
            $pattern[$i] = trim($pattern[$i]);
            if (empty($pattern[$i]))
                continue;

            $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
            $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
            $pat = "/^{$pattern[$i]}$/";
            $is_possible_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
            if ($is_possible_ip)
                break;
        }
        if (!$is_possible_ip){
            die ("<meta charset=utf-8>접근이 가능하지 않습니다.");
        }
    }

    // 접근차단 IP
    $is_intercept_ip = false;
    $pattern = isset($set_com['set_intercept_ip']) ? explode("\n", trim($set_com['set_intercept_ip'])) : array();
    for ($i=0; $i<count($pattern); $i++) {
        $pattern[$i] = trim($pattern[$i]);
        if (empty($pattern[$i]))
            continue;
        
        $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
        $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
        $pat = "/^{$pattern[$i]}$/";
        $is_intercept_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
        if ($is_intercept_ip){
            die ("<meta charset=utf-8>접근 불가합니다.");
        }
    }
}

// 관리자가 아니면서, 준비중이면서, bbs폴더가 아니면서, login페이지가 아니면 준비중 페이지로 이동
// if(!$is_admin && $set_conf['set_preparing_yn'] && $g5['dir_name'] != 'bbs' && $g5['file_name'] != 'login'){
//     include_once(G5_PATH.'/preparing.php');
//     exit;
// }

if(defined('G5_IS_ADMIN') && is_file(G5_Z_PATH.'/_adm_custom.php')){
    include_once(G5_ZSQL_PATH.'/set_menu.php');//솔루션 환경설정에서 menu에 해당하는 데이터를 가져온다.
    include_once(G5_Z_PATH.'/_adm_custom.php');
    // 관리자단 공통적으로 사용되는 tailwind스타일시트
    if(is_file(G5_Z_PATH.'/css/_common.css.php')) 
        include_once(G5_Z_PATH.'/css/_common.css.php');
    // _z01개별페이지에 필요한 taiwind스타일시트
    if(is_file(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php')) 
        include_once(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php');
    // adm후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_Z_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'); 
    // shop_admin후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_ZADM_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_ZADM_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php');
}
if(!defined('G5_IS_ADMIN') && defined('G5_THEME_PATH') && is_file(G5_NA_PATH.'/_theme_custom.php')){
    include_once(G5_NA_PATH.'/_theme_custom.php');
    // 사용자단 공통적으로 사용되는 tailwind스타일시트
    if(is_file(G5_NA_PATH.'/css/_common.css.php')) 
        include_once(G5_NA_PATH.'/css/_common.css.php');
    // _a 후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_NA_PATH.'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/css/'.$g5['file_name'].'.css.php'); 
    // _a/bbs 또는 _a/shop 후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_NA_PATH.'/css/'.$g5['dir_name'].'/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/css/'.$g5['dir_name'].'/'.$g5['file_name'].'.css.php'); 
}


//브라우저
$g5['browser_name'] = tms_browserCheck();
//echo $g5['browser_name'];
//익스여부
$g5['is_explorer'] = tms_is_explorer();
//echo $g5['is_explorer'];
//익스버전
$g5['ie_version'] = 0;
if (preg_match("/ie/", $g5['browser_name']) && $g5['is_explorer']){
	$g5['ie_version'] = (int) substr($g5['browser_name'],2);
}

//실제모바일 디바이스여부
$g5['is_real_mobile'] = is_mobile();

//브라우저 기본정보
$g5['user_agent'] = $_SERVER["HTTP_USER_AGENT"];
//echo $g5['user_agent'];

//디바이스 타입
$g5['device_type'] = tms_deviceCheck();
//echo $g5['device_type'];

//PC유사한 디바이스인가?
$g5['is_device_etc'] = ($g5['device_type'] == 'etc') ? 1 : 0;

//안드로이드 디바이스인가?
$g5['is_device_android'] = ($g5['device_type'] == 'android') ? 1 : 0;

//iphone 디바이스인가?
$g5['is_device_iphone'] = ($g5['device_type'] == 'iphone') ? 1 : 0;

//ipad 디바이스인가?
$g5['is_device_ipad'] = ($g5['device_type'] == 'ipad') ? 1 : 0;

//blackberry 디바이스인가?
$g5['is_device_blackberry'] = ($g5['device_type'] == 'blackberry') ? 1 : 0;

//DB에 dain_default_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['dain_default_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_dain_default.php');
}

//DB에 setting_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['setting_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_setting.php');
}

//DB에 meta_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['meta_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_meta.php');
}


//DB에 file_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['dain_file_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_dain_file.php');
}


//PostgreSQL DB에 term_table이 존재하는지 확인하고 없으면 설치
// if(!pg_table_exists($g5['term_table'])){
// 	// 테이블 생성 ------------------------------------
// 	include_once(G5_ZSET_PATH.'/db_term.php');
// }
//MySQL DB에 term_table이 존재하는지 확인하고 없으면 설치
$chk_db_tbl = @sql_query(" DESC ".$g5['term_table']." ", false);
if(!$chk_db_tbl){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_term.php');
}
unset($chk_db_tbl);



//app data/app_asset
$data_app_asset_dir_path = G5_DATA_PATH.'/app_asset';
$app_asset_permission_str = "chmod 707 -R ".$data_app_asset_dir_path;
if(!is_dir($data_app_asset_dir_path)){
    @mkdir($data_app_asset_dir_path, G5_DATA_Z_PERMISSION);
    @chmod($data_app_asset_dir_path, G5_DATA_Z_PERMISSION);

    exec($app_asset_permission_str);
}

// data폴더에 ndr폴더(각종 파일을 저장하는 디렉토리)생성
$data_ndr_dir_path = G5_DATA_PATH.'/ndr';
$ndr_permission_str = "chmod 707 -R ".$data_ndr_dir_path;
if(!is_dir($data_ndr_dir_path)){
    @mkdir($data_ndr_dir_path, G5_DATA_Z_PERMISSION);
    @chmod($data_ndr_dir_path, G5_DATA_Z_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/set';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/file';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/board';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/main';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/content';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/banner';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/shop';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/seo';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/temp';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    exec($ndr_permission_str);
}

unset($data_ndr_dir_path);
unset($data_ndr_in_dir_path);
unset($ndr_permission_str);

// 검색어 필터링추가
if (isset($_REQUEST['sfl2']))  {
    $sfl2 = trim($_REQUEST['sfl2']);
    $sfl2 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sfl2);
    if ($sfl2)
        $qstr .= '&amp;sfl=' . urlencode($sfl2); // search field (검색 필드)
} else {
    $sfl2 = '';
}
// 검색어 추가
if (isset($_REQUEST['stx2']))  { // search text (검색어)
    $stx2 = get_search_string(trim($_REQUEST['stx2']));
    if ($stx2 || $stx2 === '0')
        $qstr .= '&amp;stx=' . urlencode(cut_str($stx2, 20, ''));
} else {
    $stx2 = '';
}
// 검색시 정렬 필드 추가
if (isset($_REQUEST['sst2']))  {
    $sst2 = trim($_REQUEST['sst2']);
    $sst2 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sst2);
    if ($sst2)
        $qstr .= '&amp;sst2=' . urlencode($sst2); // search sort (검색 정렬 필드)
} else {
    $sst2 = '';
}
// 검색시 정렬 순서타입 추가
if (isset($_REQUEST['sod2']))  { // search order (검색 오름, 내림차순)
    $sod2 = preg_match("/^(asc|desc)$/i", $sod2) ? $sod2 : '';
    if ($sod2)
        $qstr .= '&amp;sod2=' . urlencode($sod2);
} else {
    $sod2 = '';
}

// 검색시 정렬 필드 추가
if (isset($_REQUEST['sst3']))  {
    $sst3 = trim($_REQUEST['sst3']);
    $sst3 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sst3);
    if ($sst3)
        $qstr .= '&amp;sst3=' . urlencode($sst3); // search sort (검색 정렬 필드)
} else {
    $sst3 = '';
}
// 검색시 정렬 순서타입 추가
if (isset($_REQUEST['sod3']))  { // search order (검색 오름, 내림차순)
    $sod3 = preg_match("/^(asc|desc)$/i", $sod3) ? $sod3 : '';
    if ($sod3)
        $qstr .= '&amp;sod3=' . urlencode($sod3);
} else {
    $sod3 = '';
}
