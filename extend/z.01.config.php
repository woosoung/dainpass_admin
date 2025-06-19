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

$g5['dain_default_table']                = 'dain_default';
$g5['dain_file_table']                   = 'dain_file';
$g5['meta_table']                        = 'meta';
$g5['setting_table']                     = 'setting';
$g5['term_table']                        = 'term'; //G5_Z_TABLE_PREFIX.'_term';

$g5['shop_table']                        = 'shop'; //가맹점
$g5['shop_categories_table']             = 'shop_categories'; //업종(분류)
$g5['shop_category_relation_table']      = 'shop_category_relation'; //업종-가맹점 크로스 테이블