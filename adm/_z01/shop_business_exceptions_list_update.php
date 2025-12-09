<?php
$sub_menu = "930900";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        if ($mb_1_value === '0' || $mb_1_value === '') {
            alert('업체 데이터가 없습니다.', './shop_business_exceptions_list.php');
            exit;
        }
        
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                alert('업체 데이터가 없습니다.', './shop_business_exceptions_list.php');
                exit;
            }
        }
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.', './shop_business_exceptions_list.php');
    exit;
}

// 토큰 체크
check_admin_token();

// action 또는 act 필드 확인 (flist 폼에서는 act 사용)
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : (isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '');

// qstr 생성
$qstr = '';
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
    $post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
    $post_date = isset($_POST['date']) ? clean_xss_tags($_POST['date']) : '';
    $post_is_open = isset($_POST['is_open']) ? ($_POST['is_open'] == '1' ? true : false) : false;
    $post_open_time = isset($_POST['open_time']) && $_POST['open_time'] !== '' ? clean_xss_tags($_POST['open_time']) : null;
    $post_close_time = isset($_POST['close_time']) && $_POST['close_time'] !== '' ? clean_xss_tags($_POST['close_time']) : null;
    $post_reason = isset($_POST['reason']) ? clean_xss_tags($_POST['reason']) : '';
    
    // shop_id 검증
    if ($post_shop_id != $shop_id) {
        alert('잘못된 가맹점 정보입니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 필수값 검증
    if (!$post_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_date)) {
        alert('날짜를 올바르게 입력해주세요.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 영업인 경우 영업시간 필수
    if ($post_is_open) {
        if (!$post_open_time || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $post_open_time)) {
            alert('영업시작시간을 올바르게 입력해주세요.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        if (!$post_close_time || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $post_close_time)) {
            alert('영업종료시간을 올바르게 입력해주세요.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        // 시작시간이 종료시간보다 늦으면 안됨
        if ($post_open_time >= $post_close_time) {
            alert('영업시작시간은 영업종료시간보다 빨라야 합니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
    } else {
        // 휴무인 경우 영업시간은 NULL
        $post_open_time = null;
        $post_close_time = null;
    }
    
    if ($action == 'add') {
        // 중복 체크 (UNIQUE 제약조건: shop_id, date)
        $check_sql = " SELECT shop_id FROM business_exceptions 
                       WHERE shop_id = {$post_shop_id} 
                       AND date = '{$post_date}' ";
        $check_row = sql_fetch_pg($check_sql);
        
        if ($check_row && $check_row['shop_id']) {
            alert('이미 등록된 날짜입니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // INSERT
        $insert_sql = " INSERT INTO business_exceptions (shop_id, date, is_open, open_time, close_time, reason) 
                        VALUES ({$post_shop_id}, '{$post_date}', " . ($post_is_open ? 'true' : 'false') . ", ";
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
        
        sql_query_pg($insert_sql);
        
        alert('특별휴무/영업일이 등록되었습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        
    } else if ($action == 'edit') {
        $post_original_date = isset($_POST['original_date']) ? clean_xss_tags($_POST['original_date']) : '';
        
        if (!$post_original_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_original_date)) {
            alert('원본 날짜가 올바르지 않습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // 기존 데이터 확인
        $exist_sql = " SELECT * FROM business_exceptions 
                       WHERE shop_id = {$post_shop_id} 
                       AND date = '{$post_original_date}' ";
        $exist_row = sql_fetch_pg($exist_sql);
        
        if (!$exist_row || !$exist_row['shop_id']) {
            alert('존재하지 않는 데이터입니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }
        
        // 날짜가 변경된 경우 중복 체크 (자기 자신 제외)
        if ($post_date != $post_original_date) {
            $check_sql = " SELECT shop_id FROM business_exceptions 
                           WHERE shop_id = {$post_shop_id} 
                           AND date = '{$post_date}' ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['shop_id']) {
                alert('이미 등록된 날짜입니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
                exit;
            }
        }
        
        // UPDATE
        $update_sql = " UPDATE business_exceptions 
                        SET date = '{$post_date}', 
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
                        WHERE shop_id = {$post_shop_id} 
                        AND date = '{$post_original_date}' ";
        // echo $update_sql;exit;
        sql_query_pg($update_sql);
        
        alert('특별휴무/영업일이 수정되었습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    }
    
} else if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $chk_dates = array();
    
    foreach ($chk as $date) {
        $date = clean_xss_tags($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $chk_dates[] = "'{$date}'";
        }
    }
    
    if (empty($chk_dates)) {
        alert('선택된 항목이 없습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $dates_str = implode(',', $chk_dates);
    
    // shop_id 검증 후 삭제
    $delete_sql = " DELETE FROM business_exceptions 
                    WHERE shop_id = {$shop_id} 
                    AND date IN ({$dates_str}) ";
    
    sql_query_pg($delete_sql);
    
    alert('선택한 ' . $chk_count . '개의 특별휴무/영업일이 삭제되었습니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_business_exceptions_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>

