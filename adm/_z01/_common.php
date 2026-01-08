<?php
define('G5_IS_ADMIN', true);

// common.php를 절대 경로로 포함
// __FILE__은 /home/wsd/dpadm_www/dainpassadmin/adm/_z01/_common.php
// dirname(__FILE__)은 /home/wsd/dpadm_www/dainpassadmin/adm/_z01
// dirname(dirname(__FILE__))은 /home/wsd/dpadm_www/dainpassadmin/adm
// dirname(dirname(dirname(__FILE__)))은 /home/wsd/dpadm_www/dainpassadmin
$root_path = dirname(dirname(dirname(__FILE__)));
$common_file = $root_path . '/common.php';

if (file_exists($common_file)) {
    include_once($common_file);
} else {
    // common.php를 찾을 수 없으면 상대 경로로 시도
    @include_once('../../common.php');
}

// G5_ADMIN_PATH가 정의되지 않았을 경우 정의
if (!defined('G5_ADMIN_PATH')) {
    // G5_PATH와 G5_ADMIN_DIR이 정의되어 있는지 확인
    if (!defined('G5_PATH')) {
        // G5_PATH가 없으면 기본 경로 사용
        if (isset($g5_path) && isset($g5_path['path'])) {
            define('G5_PATH', $g5_path['path']);
        } else {
            define('G5_PATH', $root_path);
        }
    }
    if (!defined('G5_ADMIN_DIR')) {
        define('G5_ADMIN_DIR', 'adm');
    }
    // G5_ADMIN_PATH 계산 (중복 방지)
    $admin_path = G5_PATH.'/'.G5_ADMIN_DIR;
    // /adm/adm/ 같은 중복 방지
    $admin_path = preg_replace('#/adm/adm/#', '/adm/', $admin_path);
    define('G5_ADMIN_PATH', $admin_path);
}

// admin.lib.php 포함
if (defined('G5_ADMIN_PATH') && file_exists(G5_ADMIN_PATH.'/admin.lib.php')) {
    include_once(G5_ADMIN_PATH.'/admin.lib.php');
}

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

// run_event 함수가 정의되어 있는지 확인
if (function_exists('run_event')) {
    run_event('admin_common');
}