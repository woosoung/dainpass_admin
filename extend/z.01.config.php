<?php
// 서비스 시작할때는 반드시 아래 3줄을 주석처리 해제해야 함
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 공통변수, 상수 선언
define('BR',      		                '<br>');
define('G5_Z_DIR',      		        '_z01');
define('G5_SQL_DIR',      		        '_sql');
define('G5_SET_DIR',      		        '__set');
define('G5_REPLACE_DIR',                '_replace');
define('G5_NDR_DIR',      		        'ndr');
define('G5_NA_DIR',                     '_a');
define('G5_MODAL_DIR',                  'modal');
define('G5_VENDOR_DIR',                 'vendor');
define('G5_VENDOR_PATH',                G5_PATH.'/'.G5_VENDOR_DIR);
define('G5_VENDOR_URL',                 G5_URL.'/'.G5_VENDOR_DIR);
define('G5_Z_PATH',  				    G5_ADMIN_PATH.'/'.G5_Z_DIR);
define('G5_Z_URL',  				    G5_ADMIN_URL.'/'.G5_Z_DIR);
define('G5_ZSET_PATH',                  G5_Z_PATH.'/'.G5_SET_DIR);
define('G5_ZSET_URL',                   G5_Z_URL.'/'.G5_SET_DIR);
define('G5_ZSQL_PATH',                  G5_Z_PATH.'/'.G5_SQL_DIR);
define('G5_ZSQL_URL',                   G5_Z_URL.'/'.G5_SQL_DIR);
define('G5_ZREPLACE_PATH',              G5_Z_PATH.'/'.G5_REPLACE_DIR);
define('G5_ZREPLACE_URL',               G5_Z_URL.'/'.G5_REPLACE_DIR);
define('G5_ZADM_PATH',                  G5_Z_PATH.'/_adm');
define('G5_ZADM_URL',                   G5_Z_URL.'/_adm');
define('G5_ZMODAL_PATH',                G5_Z_PATH.'/'.G5_MODAL_DIR);
define('G5_ZMODAL_URL',                 G5_Z_URL.'/'.G5_MODAL_DIR);
define('G5_ZSHOP_ADMIN_PATH',           G5_ZADM_PATH.'/_shop_admin');
define('G5_ZSHOP_ADMIN_URL',            G5_ZADM_URL.'/_shop_admin');
define('G5_DATA_NDR_PATH',              G5_DATA_PATH.'/'.G5_NDR_DIR);
define('G5_DATA_NDR_URL',               G5_DATA_URL.'/'.G5_NDR_DIR);
define('G5_NA_PATH',                    G5_THEME_PATH.'/'.G5_NA_DIR);
define('G5_NA_URL',                     G5_THEME_URL.'/'.G5_NA_DIR);
define('G5_WMODAL_PATH',                G5_PATH.'/w'.G5_MODAL_DIR);
define('G5_WMODAL_URL',                 G5_URL.'/w'.G5_MODAL_DIR);

define('G5_DATA_Z_PERMISSION',  0707); // 디렉토리 생성시 퍼미션
define('G5_Z_TABLE_PREFIX', G5_TABLE_PREFIX.'1'); //g5_1
// mysql 테이블
$g5['term_table']                        = G5_Z_TABLE_PREFIX.'_term'; //G5_Z_TABLE_PREFIX.'_term';
$g5['gmeta_table']                       = G5_Z_TABLE_PREFIX.'_meta'; //G5_Z_TABLE_PREFIX.'_meta';
// pgsql 테이블
$g5['dain_default_table']                = 'dain_default';
$g5['dain_file_table']                   = 'dain_file';
$g5['meta_table']                        = 'meta';
$g5['setting_table']                     = 'setting';

$g5['default_business_hour_slots_table'] = 'default_business_hour_slots'; //기본업무시간 슬롯 테이블
$g5['service_terms_table']               = 'service_terms'; //서비스약관 테이블

$g5['shop_table']                        = 'shop'; //가맹점
$g5['shop_categories_table']             = 'shop_categories'; //업종(분류)
$g5['shop_category_relation_table']      = 'shop_category_relation'; //업종-가맹점 크로스 테이블
$g5['category_default_table']            = 'category_default'; //업종별 기본값(예약준비시간 등)
$g5['shop_keyword_table']                = 'shop_keyword'; //가맹점별 키워드 테이블
$g5['shop_search_refresh_queue_table']   = 'shop_search_refresh_queue'; //가맹점 검색색인 갱신 큐 테이블
$g5['shop_amenities_table']             = 'shop_amenities'; //가맹점-편의시설 크로스 테이블
$g5['shop_qna_table']                   = 'shop_qna'; //고객회원이 가맹점 및 플랫폼에 문의
$g5['shop_admin_inquiry_table']         = 'shop_admin_inquiry'; //가맹점이 플랫폼에 문의
$g5['shop_services_table']              = 'shop_services'; //가맹점 서비스
$g5['shop_space_group_table']           = 'shop_space_group'; //공간 그룹 (층/홀/존)
$g5['shop_space_unit_table']            = 'shop_space_unit'; //공간 유닛 (룸/테이블/좌석)

$g5['keywords_table']                    = 'keywords'; //키워드 테이블
$g5['amenities_table']                  = 'amenities'; //편의시설
$g5['customers_table']                  = 'customers'; //고객(사용자)회원