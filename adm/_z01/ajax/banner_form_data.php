<?php
// 출력 버퍼 시작 (가장 먼저)
while (ob_get_level() > 0) {
    @ob_end_clean();
}
@ob_start();

// 전역 에러 수집 배열
$GLOBALS['banner_form_error_collector'] = array(
    'errors' => array(),
    'warnings' => array(),
    'notices' => array(),
    'output' => ''
);

// shutdown 함수로 치명적 에러 캡처
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
        // 출력 버퍼 정리
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        
        // JSON 응답 (headers_sent 체크)
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        $error_response = json_encode(array(
            'success' => false,
            'message' => 'PHP 치명적 오류: ' . $error['message'],
            'error_details' => array(
                'type' => 'Fatal Error',
                'file' => basename($error['file']),
                'line' => $error['line'],
                'full_file' => $error['file'],
                'error_type' => $error['type']
            ),
            'php_errors' => isset($GLOBALS['banner_form_error_collector']) ? $GLOBALS['banner_form_error_collector'] : array()
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo $error_response;
        exit;
    }
    
    // 치명적 에러가 아니어도 응답이 비어있는지 확인
    $output = ob_get_contents();
    if (empty($output) && !headers_sent()) {
        // 응답이 비어있으면 최소한의 에러 메시지라도 반환
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'message' => '서버에서 응답을 생성하지 못했습니다.',
            'error_details' => array(
                'type' => 'Empty Response',
                'last_error' => $error
            )
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
});

// 커스텀 에러 핸들러
function banner_form_error_handler($errno, $errstr, $errfile, $errline) {
    $error_info = array(
        'level' => $errno,
        'level_name' => '',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline,
        'full_file' => $errfile
    );
    
    switch ($errno) {
        case E_ERROR:
            $error_info['level_name'] = 'E_ERROR';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_WARNING:
            $error_info['level_name'] = 'E_WARNING';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        case E_PARSE:
            $error_info['level_name'] = 'E_PARSE';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_NOTICE:
            $error_info['level_name'] = 'E_NOTICE';
            $GLOBALS['banner_form_error_collector']['notices'][] = $error_info;
            break;
        case E_CORE_ERROR:
            $error_info['level_name'] = 'E_CORE_ERROR';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_CORE_WARNING:
            $error_info['level_name'] = 'E_CORE_WARNING';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        case E_COMPILE_ERROR:
            $error_info['level_name'] = 'E_COMPILE_ERROR';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_COMPILE_WARNING:
            $error_info['level_name'] = 'E_COMPILE_WARNING';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        case E_USER_ERROR:
            $error_info['level_name'] = 'E_USER_ERROR';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_USER_WARNING:
            $error_info['level_name'] = 'E_USER_WARNING';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        case E_USER_NOTICE:
            $error_info['level_name'] = 'E_USER_NOTICE';
            $GLOBALS['banner_form_error_collector']['notices'][] = $error_info;
            break;
        case E_STRICT:
            $error_info['level_name'] = 'E_STRICT';
            $GLOBALS['banner_form_error_collector']['notices'][] = $error_info;
            break;
        case E_RECOVERABLE_ERROR:
            $error_info['level_name'] = 'E_RECOVERABLE_ERROR';
            $GLOBALS['banner_form_error_collector']['errors'][] = $error_info;
            break;
        case E_DEPRECATED:
            $error_info['level_name'] = 'E_DEPRECATED';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        case E_USER_DEPRECATED:
            $error_info['level_name'] = 'E_USER_DEPRECATED';
            $GLOBALS['banner_form_error_collector']['warnings'][] = $error_info;
            break;
        default:
            $error_info['level_name'] = 'UNKNOWN(' . $errno . ')';
            $GLOBALS['banner_form_error_collector']['notices'][] = $error_info;
            break;
    }
    
    // 기본 에러 처리도 실행 (로깅용)
    return false;
}

set_error_handler('banner_form_error_handler');

// 에러 처리 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 출력 버퍼 시작 (모든 출력을 캡처)
// 여러 레벨의 출력 버퍼를 정리
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

try {
    include_once('../_common.php');
    
    // include 후 출력 버퍼 확인
    $buffer_after_include = ob_get_contents();
    if (!empty($buffer_after_include)) {
        ob_end_clean();
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => false, 
            'message' => '파일 로드 후 예상치 못한 출력이 발생했습니다.',
            'output_after_include' => $buffer_after_include
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $error_response = array(
        'success' => false, 
        'message' => '파일 로드 오류: ' . $e->getMessage(),
        'error_details' => array(
            'type' => 'Exception',
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ),
        'php_errors' => $GLOBALS['banner_form_error_collector']
    );
    echo json_encode($error_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
} catch (Error $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $error_response = array(
        'success' => false, 
        'message' => '파일 로드 오류: ' . $e->getMessage(),
        'error_details' => array(
            'type' => 'Error',
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ),
        'php_errors' => $GLOBALS['banner_form_error_collector']
    );
    echo json_encode($error_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Content-Type 헤더 설정
header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$response = array('success' => false, 'message' => '');

// 토큰 체크
try {
    auth_check_menu($auth, '920650', 'w');
    
    // 토큰 수동 체크 (check_admin_token()의 alert 출력 방지)
    $token = get_session('ss_admin_token');
    set_session('ss_admin_token', '');
    
    if (!$token || !isset($_REQUEST['token']) || $token != $_REQUEST['token']) {
        throw new Exception('올바른 방법으로 이용해 주십시오.');
    }
    
    // 인증 체크 후 출력 버퍼 확인
    $buffer_after_auth = ob_get_contents();
    if (!empty($buffer_after_auth)) {
        ob_end_clean();
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => false,
            'message' => '인증 체크 후 예상치 못한 출력이 발생했습니다.',
            'output_after_auth' => $buffer_after_auth
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Exception $e) {
    $buffer = ob_get_contents();
    ob_end_clean();
    ob_start();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    $response['message'] = '인증 오류: ' . $e->getMessage();
    if (!empty($buffer)) {
        $response['output_buffer'] = $buffer;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
} catch (Error $e) {
    $buffer = ob_get_contents();
    ob_end_clean();
    ob_start();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    $response['message'] = '인증 오류: ' . $e->getMessage();
    if (!empty($buffer)) {
        $response['output_buffer'] = $buffer;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
if ($action == 'insert') {
    // 배너 등록
    $bng_id = isset($_POST['bng_id']) ? (int)$_POST['bng_id'] : 0;
    $shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
    $bnr_name = isset($_POST['bnr_name']) ? trim($_POST['bnr_name']) : '';
    $bnr_name = ($bnr_name == '') ? 'NULL' : "'".addslashes($bnr_name)."'";
    $bnr_desc = isset($_POST['bnr_desc']) ? trim($_POST['bnr_desc']) : '';
    $bnr_desc = ($bnr_desc == '') ? 'NULL' : "'".addslashes($bnr_desc)."'";
    $bnr_link = isset($_POST['bnr_link']) ? trim($_POST['bnr_link']) : '';
    $bnr_link = ($bnr_link == '') ? 'NULL' : "'".addslashes($bnr_link)."'";
    $bnr_mo_link = isset($_POST['bnr_mo_link']) ? trim($_POST['bnr_mo_link']) : '';
    $bnr_mo_link = ($bnr_mo_link == '') ? 'NULL' : "'".addslashes($bnr_mo_link)."'";
    $bnr_target = isset($_POST['bnr_target']) ? trim($_POST['bnr_target']) : '_self';
    $bnr_youtube = isset($_POST['bnr_youtube']) ? trim($_POST['bnr_youtube']) : '';
    $bnr_youtube = ($bnr_youtube == '') ? 'NULL' : "'".addslashes($bnr_youtube)."'";
    $bnr_start_dt = isset($_POST['bnr_start_dt']) ? trim($_POST['bnr_start_dt']) : '';
    $bnr_start_dt = ($bnr_start_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bnr_start_dt).":00'";
    $bnr_end_dt = isset($_POST['bnr_end_dt']) ? trim($_POST['bnr_end_dt']) : '';
    $bnr_end_dt = ($bnr_end_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bnr_end_dt).":00'";
    $bnr_status = isset($_POST['bnr_status']) ? trim($_POST['bnr_status']) : 'ok';

    if (!$bng_id) {
        throw new Exception('배너그룹 ID가 필요합니다.');
    }

    // 이미지 또는 유튜브 필수 체크
    $has_image = false;
    $banner_img_files = array();
    
    // FormData에서 banner_img[]로 전송하면 $_FILES['banner_img']로 접근 (PC용)
    if (isset($_FILES['banner_img'])) {
        // 배열 형태로 전송된 경우 (banner_img[])
        if (isset($_FILES['banner_img']['name'])) {
            if (is_array($_FILES['banner_img']['name'])) {
                // 이미 배열 형태 (여러 파일)
                // 파일 업로드 에러 확인
                $valid_files = true;
                foreach ($_FILES['banner_img']['error'] as $error) {
                    if ($error != UPLOAD_ERR_OK) {
                        $valid_files = false;
                        break;
                    }
                }
                if ($valid_files) {
                    $has_image = true;
                    $banner_img_files = $_FILES['banner_img'];
                }
            } else {
                // 단일 파일을 배열 형태로 변환
                if (!empty($_FILES['banner_img']['name']) && $_FILES['banner_img']['error'] == UPLOAD_ERR_OK) {
                    $has_image = true;
                    $banner_img_files = array(
                        'name' => array($_FILES['banner_img']['name']),
                        'type' => array($_FILES['banner_img']['type']),
                        'tmp_name' => array($_FILES['banner_img']['tmp_name']),
                        'error' => array($_FILES['banner_img']['error']),
                        'size' => array($_FILES['banner_img']['size'])
                    );
                }
            }
        }
    }
    
    // 모바일용 이미지 파일 처리
    $has_mo_image = false;
    $banner_mo_img_files = array();
    
    if (isset($_FILES['banner_mo_img'])) {
        if (isset($_FILES['banner_mo_img']['name'])) {
            if (is_array($_FILES['banner_mo_img']['name'])) {
                $valid_mo_files = true;
                foreach ($_FILES['banner_mo_img']['error'] as $error) {
                    if ($error != UPLOAD_ERR_OK) {
                        $valid_mo_files = false;
                        break;
                    }
                }
                if ($valid_mo_files) {
                    $has_mo_image = true;
                    $banner_mo_img_files = $_FILES['banner_mo_img'];
                }
            } else {
                if (!empty($_FILES['banner_mo_img']['name']) && $_FILES['banner_mo_img']['error'] == UPLOAD_ERR_OK) {
                    $has_mo_image = true;
                    $banner_mo_img_files = array(
                        'name' => array($_FILES['banner_mo_img']['name']),
                        'type' => array($_FILES['banner_mo_img']['type']),
                        'tmp_name' => array($_FILES['banner_mo_img']['tmp_name']),
                        'error' => array($_FILES['banner_mo_img']['error']),
                        'size' => array($_FILES['banner_mo_img']['size'])
                    );
                }
            }
        }
    }
    
    $has_youtube = !empty($bnr_youtube) && $bnr_youtube != 'NULL';
    
    if (!$has_image && !$has_youtube) {
        throw new Exception('이미지 또는 유튜브 영상 URL 중 하나는 필수입니다.');
    }

    // 최신 bnr_sort 값 조회
    $sort_sql = " SELECT COALESCE(MAX(bnr_sort), 0) + 1 AS next_sort FROM banner WHERE bng_id = '{$bng_id}' ";
    $sort_row = @sql_fetch_pg($sort_sql);
    $next_sort = isset($sort_row['next_sort']) ? (int)$sort_row['next_sort'] : 1;

    // 배너 INSERT
    $sql = " INSERT INTO banner 
                (bng_id, shop_id, bnr_name, bnr_desc, bnr_link, bnr_mo_link, bnr_target, bnr_youtube, bnr_start_dt, bnr_end_dt, bnr_sort, bnr_status, bnr_created_at, bnr_update_at)
              VALUES 
                ({$bng_id}, {$shop_id}, {$bnr_name}, {$bnr_desc}, {$bnr_link}, {$bnr_mo_link}, '{$bnr_target}', {$bnr_youtube}, {$bnr_start_dt}, {$bnr_end_dt}, {$next_sort}, '{$bnr_status}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ";
    
    $result = @sql_query_pg($sql);
    if (!$result) {
        throw new Exception('배너 등록에 실패했습니다: 데이터베이스 오류');
    }
    
    $bnr_id = @sql_insert_id_pg('banner');
    if (!$bnr_id) {
        throw new Exception('배너 ID를 가져올 수 없습니다.');
    }

    // 이미지 파일 업로드
    if ($has_image && !empty($banner_img_files)) {
        try {
            upload_multi_file($banner_img_files, 'banner', $bnr_id, 'plt/banner', 'banner_img');
            
            // 배너당 이미지는 1개만 유지: 업로드 후 가장 최신 파일만 남기고 나머지 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $check_sql = " SELECT fle_idx, fle_reg_dt
                               FROM {$g5['dain_file_table']}
                               WHERE fle_db_tbl = 'banner'
                               AND fle_db_idx = '{$bnr_id}'
                               AND fle_type = 'banner_img'
                               AND fle_dir = 'plt/banner'
                               ORDER BY fle_reg_dt DESC ";
                $check_result = @sql_query_pg($check_sql);
                $uploaded_files = array();
                if ($check_result && is_object($check_result) && isset($check_result->result)) {
                    while ($file_row = sql_fetch_array_pg($check_result->result)) {
                        $uploaded_files[] = $file_row;
                    }
                }
                
                // 2개 이상 업로드된 경우 가장 최신 것(첫 번째)만 남기고 나머지 삭제
                if (count($uploaded_files) > 1) {
                    // 첫 번째 파일(fle_idx)을 제외한 나머지 파일들의 fle_idx 추출
                    $delete_idx_array = array();
                    for ($i = 1; $i < count($uploaded_files); $i++) {
                        $delete_idx_array[] = $uploaded_files[$i]['fle_idx'];
                    }
                    if (!empty($delete_idx_array)) {
                        delete_idx_s3_file($delete_idx_array);
                    }
                }
            }
        } catch (Exception $e) {
            // 파일 업로드 실패 시 배너는 이미 등록되었으므로 경고만 표시
            // 필요시 배너를 삭제할 수도 있음
            $response['success'] = true;
            $response['message'] = '배너는 등록되었으나 PC용 이미지 업로드에 실패했습니다: ' . $e->getMessage();
            $response['bnr_id'] = $bnr_id;
            echo json_encode($response);
            exit;
        }
    }

    // 모바일용 이미지 파일 업로드
    if ($has_mo_image && !empty($banner_mo_img_files)) {
        try {
            upload_multi_file($banner_mo_img_files, 'banner', $bnr_id, 'plt/banner', 'banner_mo_img');
            
            // 배너당 모바일 이미지는 1개만 유지: 업로드 후 가장 최신 파일만 남기고 나머지 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $check_mo_sql = " SELECT fle_idx, fle_reg_dt
                                  FROM {$g5['dain_file_table']}
                                  WHERE fle_db_tbl = 'banner'
                                  AND fle_db_idx = '{$bnr_id}'
                                  AND fle_type = 'banner_mo_img'
                                  AND fle_dir = 'plt/banner'
                                  ORDER BY fle_reg_dt DESC ";
                $check_mo_result = @sql_query_pg($check_mo_sql);
                $uploaded_mo_files = array();
                if ($check_mo_result && is_object($check_mo_result) && isset($check_mo_result->result)) {
                    while ($file_row = sql_fetch_array_pg($check_mo_result->result)) {
                        $uploaded_mo_files[] = $file_row;
                    }
                }
                
                // 2개 이상 업로드된 경우 가장 최신 것(첫 번째)만 남기고 나머지 삭제
                if (count($uploaded_mo_files) > 1) {
                    $delete_mo_idx_array = array();
                    for ($i = 1; $i < count($uploaded_mo_files); $i++) {
                        $delete_mo_idx_array[] = $uploaded_mo_files[$i]['fle_idx'];
                    }
                    if (!empty($delete_mo_idx_array)) {
                        delete_idx_s3_file($delete_mo_idx_array);
                    }
                }
            }
        } catch (Exception $e) {
            // 파일 업로드 실패 시 배너는 이미 등록되었으므로 경고만 표시
            $response['success'] = true;
            $response['message'] = '배너는 등록되었으나 모바일용 이미지 업로드에 실패했습니다: ' . $e->getMessage();
            $response['bnr_id'] = $bnr_id;
            echo json_encode($response);
            exit;
        }
    }

    $response['success'] = true;
    $response['message'] = '배너가 등록되었습니다.';
    $response['bnr_id'] = $bnr_id;

} else if ($action == 'update') {
    // 배너 수정
    $bnr_id = isset($_POST['bnr_id']) ? (int)$_POST['bnr_id'] : 0;
    
    if (!$bnr_id) {
        throw new Exception('배너 ID가 필요합니다.');
    }

    // 기존 데이터 조회
    $sql = " SELECT * FROM banner WHERE bnr_id = '{$bnr_id}' ";
    $bnr = @sql_fetch_pg($sql);
    
    if (!$bnr) {
        throw new Exception('배너를 찾을 수 없습니다.');
    }

    // 수정할 필드 처리
    $shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
    $bnr_name = isset($_POST['bnr_name']) ? trim($_POST['bnr_name']) : '';
    $bnr_name = ($bnr_name == '') ? 'NULL' : "'".addslashes($bnr_name)."'";
    $bnr_desc = isset($_POST['bnr_desc']) ? trim($_POST['bnr_desc']) : '';
    $bnr_desc = ($bnr_desc == '') ? 'NULL' : "'".addslashes($bnr_desc)."'";
    $bnr_link = isset($_POST['bnr_link']) ? trim($_POST['bnr_link']) : '';
    $bnr_link = ($bnr_link == '') ? 'NULL' : "'".addslashes($bnr_link)."'";
    $bnr_mo_link = isset($_POST['bnr_mo_link']) ? trim($_POST['bnr_mo_link']) : '';
    $bnr_mo_link = ($bnr_mo_link == '') ? 'NULL' : "'".addslashes($bnr_mo_link)."'";
    $bnr_target = isset($_POST['bnr_target']) ? trim($_POST['bnr_target']) : '_self';
    $bnr_youtube = isset($_POST['bnr_youtube']) ? trim($_POST['bnr_youtube']) : '';
    $bnr_youtube = ($bnr_youtube == '') ? 'NULL' : "'".addslashes($bnr_youtube)."'";
    $bnr_start_dt = isset($_POST['bnr_start_dt']) ? trim($_POST['bnr_start_dt']) : '';
    $bnr_start_dt = ($bnr_start_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bnr_start_dt).":00'";
    $bnr_end_dt = isset($_POST['bnr_end_dt']) ? trim($_POST['bnr_end_dt']) : '';
    $bnr_end_dt = ($bnr_end_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bnr_end_dt).":00'";
    $bnr_status = isset($_POST['bnr_status']) ? trim($_POST['bnr_status']) : 'ok';

    // 이미지 또는 유튜브 필수 체크
    $has_image = false;
    $banner_img_files = array();
    
    // FormData에서 banner_img[]로 전송하면 $_FILES['banner_img']로 접근 (PC용)
    if (isset($_FILES['banner_img'])) {
        // 배열 형태로 전송된 경우 (banner_img[])
        if (isset($_FILES['banner_img']['name'])) {
            if (is_array($_FILES['banner_img']['name'])) {
                // 이미 배열 형태 (여러 파일)
                // 파일 업로드 에러 확인
                $valid_files = true;
                foreach ($_FILES['banner_img']['error'] as $error) {
                    if ($error != UPLOAD_ERR_OK) {
                        $valid_files = false;
                        break;
                    }
                }
                if ($valid_files) {
                    $has_image = true;
                    $banner_img_files = $_FILES['banner_img'];
                }
            } else {
                // 단일 파일을 배열 형태로 변환
                if (!empty($_FILES['banner_img']['name']) && $_FILES['banner_img']['error'] == UPLOAD_ERR_OK) {
                    $has_image = true;
                    $banner_img_files = array(
                        'name' => array($_FILES['banner_img']['name']),
                        'type' => array($_FILES['banner_img']['type']),
                        'tmp_name' => array($_FILES['banner_img']['tmp_name']),
                        'error' => array($_FILES['banner_img']['error']),
                        'size' => array($_FILES['banner_img']['size'])
                    );
                }
            }
        }
    }
    
    // 모바일용 이미지 파일 처리
    $has_mo_image = false;
    $banner_mo_img_files = array();
    
    if (isset($_FILES['banner_mo_img'])) {
        if (isset($_FILES['banner_mo_img']['name'])) {
            if (is_array($_FILES['banner_mo_img']['name'])) {
                $valid_mo_files = true;
                foreach ($_FILES['banner_mo_img']['error'] as $error) {
                    if ($error != UPLOAD_ERR_OK) {
                        $valid_mo_files = false;
                        break;
                    }
                }
                if ($valid_mo_files) {
                    $has_mo_image = true;
                    $banner_mo_img_files = $_FILES['banner_mo_img'];
                }
            } else {
                if (!empty($_FILES['banner_mo_img']['name']) && $_FILES['banner_mo_img']['error'] == UPLOAD_ERR_OK) {
                    $has_mo_image = true;
                    $banner_mo_img_files = array(
                        'name' => array($_FILES['banner_mo_img']['name']),
                        'type' => array($_FILES['banner_mo_img']['type']),
                        'tmp_name' => array($_FILES['banner_mo_img']['tmp_name']),
                        'error' => array($_FILES['banner_mo_img']['error']),
                        'size' => array($_FILES['banner_mo_img']['size'])
                    );
                }
            }
        }
    }
    
    $has_youtube = !empty($bnr_youtube) && $bnr_youtube != 'NULL';
    $has_existing_image = false;
    
    // 기존 이미지 확인 (PC용 또는 모바일용)
    if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
        $img_sql = " SELECT COUNT(*) AS cnt FROM {$g5['dain_file_table']} 
                     WHERE fle_db_tbl = 'banner' AND (fle_type = 'banner_img' OR fle_type = 'banner_mo_img') AND fle_dir = 'plt/banner' AND fle_db_idx = '{$bnr_id}' ";
        $img_row = @sql_fetch_pg($img_sql);
        if ($img_row && isset($img_row['cnt']) && $img_row['cnt'] > 0) {
            $has_existing_image = true;
        }
    }
    
    if (!$has_image && !$has_youtube && !$has_existing_image) {
        throw new Exception('이미지 또는 유튜브 영상 URL 중 하나는 필수입니다.');
    }

    // UPDATE
    $sql = " UPDATE banner SET
                shop_id = {$shop_id},
                bnr_name = {$bnr_name},
                bnr_desc = {$bnr_desc},
                bnr_link = {$bnr_link},
                bnr_mo_link = {$bnr_mo_link},
                bnr_target = '{$bnr_target}',
                bnr_youtube = {$bnr_youtube},
                bnr_start_dt = {$bnr_start_dt},
                bnr_end_dt = {$bnr_end_dt},
                bnr_status = '{$bnr_status}',
                bnr_update_at = CURRENT_TIMESTAMP
              WHERE bnr_id = '{$bnr_id}' ";
    
    $result = @sql_query_pg($sql);
    if (!$result) {
        throw new Exception('배너 수정에 실패했습니다: 데이터베이스 오류');
    }

    // 이미지 파일 업로드
    if ($has_image && !empty($banner_img_files)) {
        try {
            // 새 이미지를 업로드하기 전에 기존 이미지 삭제 (배너당 1개만 유지)
            // fle_db_tbl, fle_db_idx, fle_type, fle_dir로 기존 파일 조회 및 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $del_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                             FROM {$g5['dain_file_table']}
                             WHERE fle_db_tbl = 'banner'
                             AND fle_db_idx = '{$bnr_id}'
                             AND fle_type = 'banner_img'
                             AND fle_dir = 'plt/banner' ";
                $del_row = @sql_fetch_pg($del_sql);
                if ($del_row && !empty($del_row['fle_idxs'])) {
                    $fle_idx_array = explode(',', $del_row['fle_idxs']);
                    if (!empty($fle_idx_array) && is_array($fle_idx_array)) {
                        // S3 및 dain_file 테이블에서 기존 파일 삭제
                        delete_idx_s3_file($fle_idx_array);
                    }
                }
            }
            
            // 새로운 이미지 업로드
            upload_multi_file($banner_img_files, 'banner', $bnr_id, 'plt/banner', 'banner_img');
            
            // 배너당 이미지는 1개만 유지: 업로드 후 가장 최신 파일만 남기고 나머지 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $check_sql = " SELECT fle_idx, fle_reg_dt
                               FROM {$g5['dain_file_table']}
                               WHERE fle_db_tbl = 'banner'
                               AND fle_db_idx = '{$bnr_id}'
                               AND fle_type = 'banner_img'
                               AND fle_dir = 'plt/banner'
                               ORDER BY fle_reg_dt DESC ";
                $check_result = @sql_query_pg($check_sql);
                $uploaded_files = array();
                if ($check_result && is_object($check_result) && isset($check_result->result)) {
                    while ($file_row = sql_fetch_array_pg($check_result->result)) {
                        $uploaded_files[] = $file_row;
                    }
                }
                
                // 2개 이상 업로드된 경우 가장 최신 것(첫 번째)만 남기고 나머지 삭제
                if (count($uploaded_files) > 1) {
                    // 첫 번째 파일(fle_idx)을 제외한 나머지 파일들의 fle_idx 추출
                    $delete_idx_array = array();
                    for ($i = 1; $i < count($uploaded_files); $i++) {
                        $delete_idx_array[] = $uploaded_files[$i]['fle_idx'];
                    }
                    if (!empty($delete_idx_array)) {
                        delete_idx_s3_file($delete_idx_array);
                    }
                }
            }
        } catch (Exception $e) {
            // 파일 업로드 실패 시에도 배너는 수정되었으므로 경고만 표시
            $response['success'] = true;
            $response['message'] = '배너는 수정되었으나 PC용 이미지 업로드에 실패했습니다: ' . $e->getMessage();
            echo json_encode($response);
            exit;
        }
    }

    // 모바일용 이미지 파일 업로드
    if ($has_mo_image && !empty($banner_mo_img_files)) {
        try {
            // 새 이미지를 업로드하기 전에 기존 모바일 이미지 삭제 (배너당 1개만 유지)
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $del_mo_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                                FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'banner'
                                AND fle_db_idx = '{$bnr_id}'
                                AND fle_type = 'banner_mo_img'
                                AND fle_dir = 'plt/banner' ";
                $del_mo_row = @sql_fetch_pg($del_mo_sql);
                if ($del_mo_row && !empty($del_mo_row['fle_idxs'])) {
                    $fle_mo_idx_array = explode(',', $del_mo_row['fle_idxs']);
                    if (!empty($fle_mo_idx_array) && is_array($fle_mo_idx_array)) {
                        // S3 및 dain_file 테이블에서 기존 파일 삭제
                        delete_idx_s3_file($fle_mo_idx_array);
                    }
                }
            }
            
            upload_multi_file($banner_mo_img_files, 'banner', $bnr_id, 'plt/banner', 'banner_mo_img');
            
            // 배너당 모바일 이미지는 1개만 유지: 업로드 후 가장 최신 파일만 남기고 나머지 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $check_mo_sql = " SELECT fle_idx, fle_reg_dt
                                  FROM {$g5['dain_file_table']}
                                  WHERE fle_db_tbl = 'banner'
                                  AND fle_db_idx = '{$bnr_id}'
                                  AND fle_type = 'banner_mo_img'
                                  AND fle_dir = 'plt/banner'
                                  ORDER BY fle_reg_dt DESC ";
                $check_mo_result = @sql_query_pg($check_mo_sql);
                $uploaded_mo_files = array();
                if ($check_mo_result && is_object($check_mo_result) && isset($check_mo_result->result)) {
                    while ($file_row = sql_fetch_array_pg($check_mo_result->result)) {
                        $uploaded_mo_files[] = $file_row;
                    }
                }
                
                // 2개 이상 업로드된 경우 가장 최신 것(첫 번째)만 남기고 나머지 삭제
                if (count($uploaded_mo_files) > 1) {
                    $delete_mo_idx_array = array();
                    for ($i = 1; $i < count($uploaded_mo_files); $i++) {
                        $delete_mo_idx_array[] = $uploaded_mo_files[$i]['fle_idx'];
                    }
                    if (!empty($delete_mo_idx_array)) {
                        delete_idx_s3_file($delete_mo_idx_array);
                    }
                }
            }
        } catch (Exception $e) {
            // 파일 업로드 실패 시에도 배너는 수정되었으므로 경고만 표시
            $response['success'] = true;
            $response['message'] = '배너는 수정되었으나 모바일용 이미지 업로드에 실패했습니다: ' . $e->getMessage();
            echo json_encode($response);
            exit;
        }
    }

    $response['success'] = true;
    $response['message'] = '배너가 수정되었습니다.';

} else if ($action == 'get') {
    // 배너 데이터 조회 (수정용)
    $bnr_id = isset($_POST['bnr_id']) ? (int)$_POST['bnr_id'] : 0;
    
    if (!$bnr_id) {
        throw new Exception('배너 ID가 필요합니다.');
    }

    // 배너 데이터 조회
    $sql = " SELECT * FROM banner WHERE bnr_id = '{$bnr_id}' ";
    $bnr = @sql_fetch_pg($sql);
    
    if (!$bnr) {
        throw new Exception('배너를 찾을 수 없습니다.');
    }

    // 이미지 파일 조회 (PC용)
    $img_url = null;
    $thumb_wd = 120;
    $thumb_ht = 80;
    
    if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
        $img_sql = " SELECT * FROM {$g5['dain_file_table']} 
                     WHERE fle_db_tbl = 'banner' AND fle_type = 'banner_img' AND fle_dir = 'plt/banner' AND fle_db_idx = '{$bnr_id}' 
                     ORDER BY fle_reg_dt DESC LIMIT 1 ";
        $img_row = @sql_fetch_pg($img_sql);
        
        if ($img_row && !empty($img_row['fle_path'])) {
            global $set_conf;
            if (isset($set_conf['set_imgproxy_url']) && isset($set_conf['set_s3_basicurl'])) {
                $img_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$img_row['fle_path'];
            } else {
                // imgproxy가 없으면 기본 URL 사용
                $img_url = (isset($set_conf['set_s3_basicurl']) ? $set_conf['set_s3_basicurl'] : '').'/'.$img_row['fle_path'];
            }
        }
    }

    // 이미지 파일 조회 (모바일용)
    $img_mo_url = null;
    if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
        $img_mo_sql = " SELECT * FROM {$g5['dain_file_table']} 
                        WHERE fle_db_tbl = 'banner' AND fle_type = 'banner_mo_img' AND fle_dir = 'plt/banner' AND fle_db_idx = '{$bnr_id}' 
                        ORDER BY fle_reg_dt DESC LIMIT 1 ";
        $img_mo_row = @sql_fetch_pg($img_mo_sql);
        
        if ($img_mo_row && !empty($img_mo_row['fle_path'])) {
            global $set_conf;
            if (isset($set_conf['set_imgproxy_url']) && isset($set_conf['set_s3_basicurl'])) {
                $img_mo_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$img_mo_row['fle_path'];
            } else {
                // imgproxy가 없으면 기본 URL 사용
                $img_mo_url = (isset($set_conf['set_s3_basicurl']) ? $set_conf['set_s3_basicurl'] : '').'/'.$img_mo_row['fle_path'];
            }
        }
    }

    // 유튜브 썸네일 URL 생성
    $youtube_thumb_url = null;
    if (!empty($bnr['bnr_youtube'])) {
        $youtube_id = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $bnr['bnr_youtube'], $matches)) {
            $youtube_id = $matches[1];
            $youtube_thumb_url = 'https://img.youtube.com/vi/'.$youtube_id.'/maxresdefault.jpg';
        }
    }

    // 가맹점 정보 조회
    $shop_name = '';
    if (!empty($bnr['shop_id']) && $bnr['shop_id'] > 0) {
        $shop_sql = " SELECT COALESCE(shop_name, name) AS shop_name FROM shop WHERE shop_id = '{$bnr['shop_id']}' LIMIT 1 ";
        $shop_row = @sql_fetch_pg($shop_sql);
        if ($shop_row && !empty($shop_row['shop_name'])) {
            $shop_name = $shop_row['shop_name'];
        }
    }

    // datetime-local 형식으로 변환
    $bnr_start_dt_local = '';
    if (!empty($bnr['bnr_start_dt'])) {
        $bnr_start_dt_local = substr(str_replace(' ', 'T', $bnr['bnr_start_dt']), 0, 16);
    }
    
    $bnr_end_dt_local = '';
    if (!empty($bnr['bnr_end_dt'])) {
        $bnr_end_dt_local = substr(str_replace(' ', 'T', $bnr['bnr_end_dt']), 0, 16);
    }

    $response['success'] = true;
    $response['data'] = array(
        'bnr_id' => $bnr['bnr_id'],
        'shop_id' => isset($bnr['shop_id']) ? $bnr['shop_id'] : 0,
        'shop_name' => $shop_name,
        'bnr_name' => $bnr['bnr_name'],
        'bnr_desc' => $bnr['bnr_desc'],
        'bnr_link' => $bnr['bnr_link'],
        'bnr_mo_link' => isset($bnr['bnr_mo_link']) ? $bnr['bnr_mo_link'] : '',
        'bnr_target' => $bnr['bnr_target'],
        'bnr_youtube' => $bnr['bnr_youtube'],
        'bnr_start_dt' => $bnr_start_dt_local,
        'bnr_end_dt' => $bnr_end_dt_local,
        'bnr_status' => $bnr['bnr_status'],
        'img_url' => $img_url,
        'img_mo_url' => $img_mo_url,
        'youtube_thumb_url' => $youtube_thumb_url
    );
    $response['message'] = '배너 데이터를 조회했습니다.';

} else if ($action == 'delete') {
    // 배너 삭제
    $bnr_id = isset($_POST['bnr_id']) ? (int)$_POST['bnr_id'] : 0;
    
    if (!$bnr_id) {
        throw new Exception('배너 ID가 필요합니다.');
    }

    // 기존 데이터 조회
    $sql = " SELECT * FROM banner WHERE bnr_id = '{$bnr_id}' ";
    $bnr = @sql_fetch_pg($sql);
    
    if (!$bnr) {
        throw new Exception('배너를 찾을 수 없습니다.');
    }

    // 관련 이미지 파일 삭제 (S3 및 dain_file 테이블에서 삭제)
    // PC용 이미지 삭제
    if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
        $del_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                     FROM {$g5['dain_file_table']}
                     WHERE fle_db_tbl = 'banner'
                     AND fle_db_idx = '{$bnr_id}'
                     AND fle_type = 'banner_img'
                     AND fle_dir = 'plt/banner' ";
        $del_row = @sql_fetch_pg($del_sql);
        if ($del_row && !empty($del_row['fle_idxs'])) {
            $fle_idx_array = explode(',', $del_row['fle_idxs']);
            if (!empty($fle_idx_array) && is_array($fle_idx_array)) {
                // S3 및 dain_file 테이블에서 파일 삭제
                // 출력 버퍼 정리 후 삭제 함수 호출
                $buffer_before_delete = ob_get_contents();
                if (!empty($buffer_before_delete)) {
                    ob_end_clean();
                    ob_start();
                }
                delete_idx_s3_file($fle_idx_array);
                // 삭제 함수 호출 후 출력 버퍼 정리
                $buffer_after_delete = ob_get_contents();
                if (!empty($buffer_after_delete)) {
                    ob_end_clean();
                    ob_start();
                }
            }
        }
        
        // 모바일용 이미지 삭제
        $del_mo_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                        FROM {$g5['dain_file_table']}
                        WHERE fle_db_tbl = 'banner'
                        AND fle_db_idx = '{$bnr_id}'
                        AND fle_type = 'banner_mo_img'
                        AND fle_dir = 'plt/banner' ";
        $del_mo_row = @sql_fetch_pg($del_mo_sql);
        if ($del_mo_row && !empty($del_mo_row['fle_idxs'])) {
            $fle_mo_idx_array = explode(',', $del_mo_row['fle_idxs']);
            if (!empty($fle_mo_idx_array) && is_array($fle_mo_idx_array)) {
                // S3 및 dain_file 테이블에서 파일 삭제
                // 출력 버퍼 정리 후 삭제 함수 호출
                $buffer_before_delete = ob_get_contents();
                if (!empty($buffer_before_delete)) {
                    ob_end_clean();
                    ob_start();
                }
                delete_idx_s3_file($fle_mo_idx_array);
                // 삭제 함수 호출 후 출력 버퍼 정리
                $buffer_after_delete = ob_get_contents();
                if (!empty($buffer_after_delete)) {
                    ob_end_clean();
                    ob_start();
                }
            }
        }
    } else {
        // dain_file_table이 없으면 기본 함수 사용
        $buffer_before_delete = ob_get_contents();
        if (!empty($buffer_before_delete)) {
            ob_end_clean();
            ob_start();
        }
        delete_db_s3_file('banner', $bnr_id, 'banner_img');
        delete_db_s3_file('banner', $bnr_id, 'banner_mo_img');
        // 삭제 함수 호출 후 출력 버퍼 정리
        $buffer_after_delete = ob_get_contents();
        if (!empty($buffer_after_delete)) {
            ob_end_clean();
            ob_start();
        }
    }

    // 배너 삭제
    if($set_conf['set_del_yn']){
        $sql = " DELETE FROM banner WHERE bnr_id = '{$bnr_id}' ";
    } else {
        $sql = " UPDATE banner SET bnr_status = 'del', bnr_update_at = CURRENT_TIMESTAMP WHERE bnr_id = '{$bnr_id}' ";
    }
    
    // SQL 실행 (에러 표시 비활성화)
    $result = @sql_query_pg($sql, 0);
    
    // PostgreSQL의 DELETE/UPDATE는 성공해도 특정 경우 false를 반환할 수 있으므로
    // 실제로 레코드가 존재하는지 확인
    $check_sql = " SELECT bnr_id FROM banner WHERE bnr_id = '{$bnr_id}' ";
    $check_result = @sql_fetch_pg($check_sql, 0);
    
    if ($set_conf['set_del_yn']) {
        // DELETE인 경우: 레코드가 여전히 존재하면 실패
        if ($check_result && isset($check_result['bnr_id'])) {
            throw new Exception('배너 삭제에 실패했습니다. 레코드가 여전히 존재합니다.');
        }
    } else {
        // UPDATE인 경우: 레코드가 존재하고 상태가 'del'인지 확인
        if (!$check_result || !isset($check_result['bnr_id'])) {
            throw new Exception('배너를 찾을 수 없습니다.');
        }
        // 상태 확인 (선택사항)
        $status_check_sql = " SELECT bnr_status FROM banner WHERE bnr_id = '{$bnr_id}' ";
        $status_result = @sql_fetch_pg($status_check_sql, 0);
        if ($status_result && isset($status_result['bnr_status']) && $status_result['bnr_status'] != 'del') {
            // 상태가 'del'로 변경되지 않았으면 경고 (하지만 계속 진행)
            error_log("배너 삭제: 상태가 'del'로 변경되지 않았습니다. bnr_id: {$bnr_id}");
        }
    }

    // 삭제 성공 후 출력 버퍼 정리 및 즉시 응답
    $buffer_after_sql = ob_get_contents();
    ob_end_clean();
    
    // 삭제 성공 시 즉시 응답하고 종료 (파일 끝 부분의 출력 버퍼 체크 로직을 우회)
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    $response['success'] = true;
    $response['message'] = '배너가 삭제되었습니다.';
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;

} else {
    $response['message'] = '잘못된 요청입니다.';
}

} catch (Exception $e) {
    $output = ob_get_contents();
    ob_end_clean();
    
    $response['success'] = false;
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
    $response['error_details'] = array(
        'type' => 'Exception',
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    );
    $response['php_errors'] = $GLOBALS['banner_form_error_collector'];
    if (!empty($output)) {
        $response['output_buffer'] = $output;
    }
    
    // 마지막 에러 확인
    $last_error = error_get_last();
    if ($last_error) {
        $response['last_error'] = $last_error;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
} catch (Error $e) {
    $output = ob_get_contents();
    ob_end_clean();
    
    $response['success'] = false;
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
    $response['error_details'] = array(
        'type' => 'Error',
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    );
    $response['php_errors'] = $GLOBALS['banner_form_error_collector'];
    if (!empty($output)) {
        $response['output_buffer'] = $output;
    }
    
    // 마지막 에러 확인
    $last_error = error_get_last();
    if ($last_error) {
        $response['last_error'] = $last_error;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 출력 버퍼에서 실제 출력 확인
$output = ob_get_contents();
ob_end_clean();

// 에러가 수집되었는지 확인
$has_errors = !empty($GLOBALS['banner_form_error_collector']['errors']) || !empty($GLOBALS['banner_form_error_collector']['warnings']) || !empty($GLOBALS['banner_form_error_collector']['notices']);

// 출력 버퍼에 내용이 있거나 에러가 있으면
if (!empty($output) || $has_errors) {
    $response['success'] = false;
    $response['message'] = '처리 중 오류가 발생했습니다.';
    
    if (!empty($output)) {
        $response['output_buffer'] = $output;
        $response['message'] .= ' (출력 버퍼에 내용이 있습니다)';
    }
    
    if ($has_errors) {
        $response['php_errors'] = $GLOBALS['banner_form_error_collector'];
        if (!empty($GLOBALS['banner_form_error_collector']['errors'])) {
            $first_error = $GLOBALS['banner_form_error_collector']['errors'][0];
            $response['message'] = 'PHP 오류: ' . $first_error['message'] . ' (파일: ' . $first_error['file'] . ', 라인: ' . $first_error['line'] . ')';
        }
    }
    
    // 마지막 에러 확인
    $last_error = error_get_last();
    if ($last_error) {
        $response['last_error'] = $last_error;
        if (!isset($response['message']) || strpos($response['message'], '오류') === false) {
            $response['message'] = 'PHP 오류: ' . $last_error['message'] . ' (파일: ' . basename($last_error['file']) . ', 라인: ' . $last_error['line'] . ')';
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 마지막 에러 확인 (일반적인 경우에도)
$last_error = error_get_last();
if ($last_error && in_array($last_error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
    $response['success'] = false;
    $response['message'] = '치명적 오류: ' . $last_error['message'] . ' (파일: ' . basename($last_error['file']) . ', 라인: ' . $last_error['line'] . ')';
    $response['last_error'] = $last_error;
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 성공 시에도 에러 정보 포함 (디버깅용)
if (!empty($GLOBALS['banner_form_error_collector']['warnings']) || !empty($GLOBALS['banner_form_error_collector']['notices'])) {
    $response['php_errors'] = $GLOBALS['banner_form_error_collector'];
}

// 최종 출력 전에 출력 버퍼 내용 확인
$final_output = ob_get_contents();
ob_end_clean();

// 출력 버퍼에 내용이 있으면 에러로 처리
if (!empty($final_output)) {
    header('Content-Type: application/json; charset=utf-8');
    $response['success'] = false;
    $response['message'] = 'JSON 응답 전에 예상치 못한 출력이 발생했습니다.';
    $response['unexpected_output'] = $final_output;
    $response['php_errors'] = $GLOBALS['banner_form_error_collector'];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 정상 응답
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;

