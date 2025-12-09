<?php
$sub_menu = "920950";
include_once('./_common.php');

// 플랫폼 관리자 접근 권한 체크
$has_access = false;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 6 
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $has_access = true;
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.', './category_business_exceptions_list.php');
    exit;
}

@auth_check($auth[$sub_menu], 'w');

// 토큰 체크
check_admin_token();

// action 또는 act 필드 확인 (flist 폼에서는 act 사용)
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : (isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '');

// qstr 생성
$qstr = '';
if (isset($_POST['sca']) && $_POST['sca']) {
    $qstr .= '&sca=' . urlencode($_POST['sca']);
}
if (isset($_POST['page']) && $_POST['page']) {
    $qstr .= '&page=' . (int)$_POST['page'];
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $qstr .= '&sst=' . urlencode($_POST['sst']);
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $qstr .= '&sod=' . urlencode($_POST['sod']);
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $qstr .= '&sfl=' . urlencode($_POST['sfl']);
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $qstr .= '&stx=' . urlencode($_POST['stx']);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $qstr .= '&sfl2=' . urlencode($_POST['sfl2']);
}

if ($action == 'add' || $action == 'edit') {
    $post_category_id = isset($_POST['category_id']) ? trim(clean_xss_tags($_POST['category_id'])) : '';
    $post_date = isset($_POST['date']) ? clean_xss_tags($_POST['date']) : '';
    $post_is_open = isset($_POST['is_open']) ? ($_POST['is_open'] == '1' || $_POST['is_open'] === 'true' || $_POST['is_open'] == 'true' ? true : false) : false;
    $post_open_time = isset($_POST['open_time']) && $_POST['open_time'] !== '' ? clean_xss_tags($_POST['open_time']) : null;
    $post_close_time = isset($_POST['close_time']) && $_POST['close_time'] !== '' ? clean_xss_tags($_POST['close_time']) : null;
    $post_reason = isset($_POST['reason']) ? clean_xss_tags($_POST['reason']) : '';
    
    // category_id 검증 ('0'은 '업종공통'을 의미하므로 유효한 값)
    if ($post_category_id === '' || $post_category_id === null) {
        alert('업종을 선택해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // category_id가 '0'이 아닌 경우 shop_categories 테이블에 존재하는지 확인
    if ($post_category_id !== '0') {
        $check_category_sql = " SELECT category_id FROM {$g5['shop_categories_table']} WHERE category_id = '".sql_real_escape_string($post_category_id)."' ";
        $check_category_row = sql_fetch_pg($check_category_sql);
        if (!$check_category_row || !$check_category_row['category_id']) {
            alert('존재하지 않는 업종입니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
    }
    
    // 필수값 검증
    if (!$post_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_date)) {
        alert('날짜를 올바르게 입력해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 영업인 경우 영업시간 필수
    if ($post_is_open) {
        if (!$post_open_time || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $post_open_time)) {
            alert('영업시작시간을 올바르게 입력해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        if (!$post_close_time || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $post_close_time)) {
            alert('영업종료시간을 올바르게 입력해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        // 시작시간이 종료시간보다 늦으면 안됨
        if ($post_open_time >= $post_close_time) {
            alert('영업시작시간은 영업종료시간보다 빨라야 합니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
    } else {
        // 휴무인 경우 영업시간은 NULL
        $post_open_time = null;
        $post_close_time = null;
    }
    
    $post_category_id_escaped = sql_real_escape_string($post_category_id);
    
    if ($action == 'add') {
        // 중복 체크 (UNIQUE 제약조건: category_id, date)
        $check_sql = " SELECT category_id FROM default_business_exceptions 
                       WHERE category_id = '{$post_category_id_escaped}' 
                       AND date = '{$post_date}' ";
        $check_row = sql_fetch_pg($check_sql);
        
        if ($check_row && $check_row['category_id']) {
            alert('이미 등록된 날짜입니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // INSERT
        $insert_sql = " INSERT INTO default_business_exceptions (category_id, date, is_open, open_time, close_time, reason) 
                        VALUES ('{$post_category_id_escaped}', '{$post_date}', " . ($post_is_open ? 'true' : 'false') . ", ";
        if ($post_open_time !== null) {
            $insert_sql .= "'{$post_open_time}'";
        } else {
            $insert_sql .= "NULL";
        }
        $insert_sql .= ", ";
        if ($post_close_time !== null) {
            $insert_sql .= "'{$post_close_time}'";
        } else {
            $insert_sql .= "NULL";
        }
        $insert_sql .= ", " . ($post_reason ? "'" . addslashes($post_reason) . "'" : "NULL") . ") ";
        
        $insert_result = sql_query_pg($insert_sql);
        
        if (!$insert_result) {
            alert('데이터 등록에 실패했습니다. 다시 시도해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        alert('특별휴무/영업일시가 등록되었습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        
    } else if ($action == 'edit') {
        $post_original_category_id = isset($_POST['original_category_id']) ? clean_xss_tags($_POST['original_category_id']) : '';
        $post_original_date = isset($_POST['original_date']) ? clean_xss_tags($_POST['original_date']) : '';
        
        if (!$post_original_category_id || $post_original_category_id === '') {
            alert('원본 업종이 올바르지 않습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        if (!$post_original_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_original_date)) {
            alert('원본 날짜가 올바르지 않습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // 기존 데이터 확인
        $post_original_category_id_escaped = sql_real_escape_string($post_original_category_id);
        $exist_sql = " SELECT * FROM default_business_exceptions 
                       WHERE category_id = '{$post_original_category_id_escaped}' 
                       AND date = '{$post_original_date}' ";
        $exist_row = sql_fetch_pg($exist_sql);
        
        if (!$exist_row || !$exist_row['category_id']) {
            alert('존재하지 않는 데이터입니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // 날짜나 업종이 변경된 경우 중복 체크 (자기 자신 제외)
        if ($post_date != $post_original_date || $post_category_id != $post_original_category_id) {
            $check_sql = " SELECT category_id FROM default_business_exceptions 
                           WHERE category_id = '{$post_category_id_escaped}' 
                           AND date = '{$post_date}' ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['category_id']) {
                alert('이미 등록된 날짜입니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
                exit;
            }
        }
        
        // UPDATE
        $update_sql = " UPDATE default_business_exceptions 
                        SET category_id = '{$post_category_id_escaped}', 
                            date = '{$post_date}', 
                            is_open = " . ($post_is_open ? 'true' : 'false') . ", 
                            open_time = ";
        if ($post_open_time !== null) {
            $update_sql .= "'{$post_open_time}'";
        } else {
            $update_sql .= "NULL";
        }
        $update_sql .= ", close_time = ";
        if ($post_close_time !== null) {
            $update_sql .= "'{$post_close_time}'";
        } else {
            $update_sql .= "NULL";
        }
        $update_sql .= ", reason = " . ($post_reason ? "'" . addslashes($post_reason) . "'" : "NULL") . " 
                        WHERE category_id = '{$post_original_category_id_escaped}' 
                        AND date = '{$post_original_date}' ";
        
        $update_result = sql_query_pg($update_sql);
        
        if (!$update_result) {
            alert('데이터 수정에 실패했습니다. 다시 시도해주세요.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        alert('특별휴무/영업일시가 수정되었습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    }
    
} else if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $delete_conditions = array();
    
    foreach ($chk as $chk_value) {
        $chk_value = clean_xss_tags($chk_value);
        // category_id|date 형식으로 파싱
        if (strpos($chk_value, '|') !== false) {
            list($category_id, $date) = explode('|', $chk_value, 2);
            $category_id = sql_real_escape_string($category_id);
            $date = sql_real_escape_string($date);
            
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $category_id !== '') {
                $delete_conditions[] = "(category_id = '{$category_id}' AND date = '{$date}')";
            }
        }
    }
    
    if (empty($delete_conditions)) {
        alert('선택된 항목이 없습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $conditions_str = implode(' OR ', $delete_conditions);
    
    // 삭제 실행
    $delete_sql = " DELETE FROM default_business_exceptions 
                    WHERE {$conditions_str} ";
    
    sql_query_pg($delete_sql);
    
    alert('선택한 ' . $chk_count . '개의 특별휴무/영업일시가 삭제되었습니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './category_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
