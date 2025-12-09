<?php
$sub_menu = "920900";
include_once('./_common.php');

// 플랫폼 관리자 권한 체크
@auth_check($auth[$sub_menu],'w');

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

$mode = isset($_POST['mode']) ? trim($_POST['mode']) : '';
$category_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';

// 입력값 검증
if (empty($mode)) {
    echo json_encode([
        'success' => false,
        'message' => '처리 모드가 지정되지 않았습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// category_id는 '0'도 유효한 값이므로 빈 문자열만 체크
if ($category_id === '' || $category_id === null) {
    echo json_encode([
        'success' => false,
        'message' => '카테고리 ID가 지정되지 않았습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 모드별 처리
if ($mode === 'save') {
    // 저장/수정 모드
    $prep_period = isset($_POST['prep_period_for_reservation']) ? (int)$_POST['prep_period_for_reservation'] : 0;
    
    if ($prep_period < 0) {
        echo json_encode([
            'success' => false,
            'message' => '예약 준비시간은 0 이상이어야 합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // category_id가 '0' (모든 업종)이 아닌 경우에만 shop_categories에 존재하는지 확인
    if ($category_id !== '0') {
        $check_sql = " SELECT category_id FROM {$g5['shop_categories_table']} WHERE category_id = '{$category_id}' ";
        $check_result = sql_fetch_pg($check_sql);
        
        if (!$check_result || !$check_result['category_id']) {
            echo json_encode([
                'success' => false,
                'message' => '존재하지 않는 카테고리입니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // category_default 테이블에 해당 category_id가 존재하는지 확인
    $exist_sql = " SELECT category_id FROM {$g5['category_default_table']} WHERE category_id = '{$category_id}' ";
    $exist_result = sql_fetch_pg($exist_sql);
    
    if ($exist_result && isset($exist_result['category_id']) && $exist_result['category_id'] !== null) {
        // 이미 존재하면 UPDATE
        $update_sql = " 
            UPDATE {$g5['category_default_table']} 
            SET prep_period_for_reservation = {$prep_period}
            WHERE category_id = '{$category_id}' 
        ";
        $update_result = sql_query_pg($update_sql);
        
        if ($update_result && is_object($update_result)) {
            echo json_encode([
                'success' => true,
                'message' => '예약 준비시간이 수정되었습니다.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '데이터 수정에 실패했습니다.',
                'debug_sql' => $update_sql
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // 존재하지 않으면 INSERT
        $insert_sql = " 
            INSERT INTO {$g5['category_default_table']} 
            (category_id, prep_period_for_reservation) 
            VALUES ('{$category_id}', {$prep_period}) 
        ";
        $insert_result = sql_query_pg($insert_sql);
        
        // INSERT 후 실제로 데이터가 들어갔는지 확인
        $verify_sql = " SELECT category_id, prep_period_for_reservation FROM {$g5['category_default_table']} WHERE category_id = '{$category_id}' ";
        $verify_result = sql_fetch_pg($verify_sql);
        
        if ($verify_result && isset($verify_result['category_id'])) {
            echo json_encode([
                'success' => true,
                'message' => '예약 준비시간이 등록되었습니다.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '데이터 등록에 실패했습니다.',
                'debug_sql' => $insert_sql,
                'debug_verify_sql' => $verify_sql,
                'debug_category_id' => $category_id,
                'debug_prep_period' => $prep_period,
                'debug_insert_result' => is_object($insert_result) ? 'object' : (is_bool($insert_result) ? ($insert_result ? 'true' : 'false') : 'unknown')
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
} elseif ($mode === 'delete') {
    // 삭제 모드
    $delete_sql = " 
        DELETE FROM {$g5['category_default_table']} 
        WHERE category_id = '{$category_id}' 
    ";
    $delete_result = sql_query_pg($delete_sql);
    
    if ($delete_result) {
        echo json_encode([
            'success' => true,
            'message' => '삭제되었습니다.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '삭제에 실패했습니다.'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => '알 수 없는 처리 모드입니다.'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>

