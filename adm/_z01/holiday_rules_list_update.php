<?php
$sub_menu = "930800";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : '';

// action 화이트리스트 검증
$action_whitelist = ['add', 'edit', 'delete'];
if (!in_array($action, $action_whitelist)) {
    alert('잘못된 요청입니다.');
}

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
    // check_shop_access()로 검증된 shop_id 사용 (POST 값 무시)
    $post_holiday_type = isset($_POST['holiday_type']) ? clean_xss_tags($_POST['holiday_type']) : '';
    $post_weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $post_week_of_month = isset($_POST['week_of_month']) && $_POST['week_of_month'] !== '' ? (int)$_POST['week_of_month'] : null;
    $post_description = isset($_POST['description']) ? clean_xss_tags($_POST['description']) : '';

    // 필수 입력값 검증
    if (!isset($_POST['holiday_type']) || $post_holiday_type === '') {
        alert('휴무유형을 선택해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    if (!isset($_POST['weekday'])) {
        alert('요일을 선택해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // holiday_type 화이트리스트 검증
    if (!in_array($post_holiday_type, array('weekly', 'monthly'))) {
        alert('올바른 휴무유형이 아닙니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 요일 범위 검증
    if ($post_weekday < 0 || $post_weekday > 6) {
        alert('올바른 요일을 선택해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // week_of_month 범위 검증 (NULL 가능, 값이 있으면 1~6 사이)
    if ($post_week_of_month !== null && ($post_week_of_month < 1 || $post_week_of_month > 6)) {
        alert('주차는 1 이상 6 이하로 입력해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // description 길이 검증 (최대 1000자)
    if (mb_strlen($post_description, 'UTF-8') > 1000) {
        alert('설명은 최대 1000자까지 입력 가능합니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    if ($action == 'add') {
        // 중복 체크 (UNIQUE 제약조건: shop_id, holiday_type, weekday, week_of_month)
        $check_sql = " SELECT holiday_rule_id FROM holiday_rules
                       WHERE shop_id = {$shop_id}
                       AND holiday_type = '{$post_holiday_type}'
                       AND weekday = {$post_weekday} ";
        if ($post_week_of_month !== null) {
            $check_sql .= " AND week_of_month = {$post_week_of_month} ";
        } else {
            $check_sql .= " AND week_of_month IS NULL ";
        }
        $check_row = sql_fetch_pg($check_sql);

        if ($check_row && $check_row['holiday_rule_id']) {
            alert('이미 등록된 정기휴무 규칙입니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }

        // SQL 이스케이프
        $description_escaped = $post_description ? sql_escape_string($post_description) : '';

        // INSERT
        $insert_sql = " INSERT INTO holiday_rules (shop_id, holiday_type, weekday, week_of_month, description)
                        VALUES ({$shop_id}, '{$post_holiday_type}', {$post_weekday}, ";
        if ($post_week_of_month !== null) {
            $insert_sql .= "{$post_week_of_month}";
        } else {
            $insert_sql .= "NULL";
        }
        $insert_sql .= ", " . ($post_description ? "'{$description_escaped}'" : "NULL") . ") ";

        sql_query_pg($insert_sql);

        alert('정기휴무 규칙이 등록되었습니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

    } else if ($action == 'edit') {
        $post_holiday_rule_id = isset($_POST['holiday_rule_id']) ? (int)$_POST['holiday_rule_id'] : 0;

        // 필수 입력값 검증
        if (!isset($_POST['holiday_rule_id']) || !$post_holiday_rule_id) {
            alert('규칙 ID를 입력해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }

        // 규칙 ID 범위 검증
        if ($post_holiday_rule_id < 1) {
            alert('올바른 규칙 ID가 아닙니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }

        // 기존 데이터 확인 및 권한 검증
        $exist_sql = " SELECT * FROM holiday_rules
                       WHERE holiday_rule_id = {$post_holiday_rule_id}
                       AND shop_id = {$shop_id} ";
        $exist_row = sql_fetch_pg($exist_sql);

        if (!$exist_row || !$exist_row['holiday_rule_id']) {
            alert('존재하지 않거나 권한이 없는 규칙입니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }

        // 중복 체크 (자기 자신 제외)
        $check_sql = " SELECT holiday_rule_id FROM holiday_rules
                       WHERE shop_id = {$shop_id}
                       AND holiday_type = '{$post_holiday_type}'
                       AND weekday = {$post_weekday} ";
        if ($post_week_of_month !== null) {
            $check_sql .= " AND week_of_month = {$post_week_of_month} ";
        } else {
            $check_sql .= " AND week_of_month IS NULL ";
        }
        $check_sql .= " AND holiday_rule_id != {$post_holiday_rule_id} ";
        $check_row = sql_fetch_pg($check_sql);

        if ($check_row && $check_row['holiday_rule_id']) {
            alert('이미 등록된 정기휴무 규칙입니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
            exit;
        }

        // SQL 이스케이프
        $description_escaped = $post_description ? sql_escape_string($post_description) : '';

        // UPDATE
        $update_sql = " UPDATE holiday_rules
                        SET holiday_type = '{$post_holiday_type}',
                            weekday = {$post_weekday},
                            week_of_month = ";
        if ($post_week_of_month !== null) {
            $update_sql .= "{$post_week_of_month}";
        } else {
            $update_sql .= "NULL";
        }
        $update_sql .= ", description = " . ($post_description ? "'{$description_escaped}'" : "NULL") . "
                        WHERE holiday_rule_id = {$post_holiday_rule_id}
                        AND shop_id = {$shop_id} ";

        sql_query_pg($update_sql);

        alert('정기휴무 규칙이 수정되었습니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    }
    
} else if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    // 필수 입력값 검증
    if (!isset($_POST['chk'])) {
        alert('선택된 항목이 없습니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    $chk_count = count($chk);
    $chk_ids = array();

    // ID 검증 (양수만 허용)
    foreach ($chk as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $chk_ids[] = $id;
        }
    }

    if (empty($chk_ids)) {
        alert('올바른 항목을 선택해 주세요.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    $ids_str = implode(',', $chk_ids);

    // shop_id 검증 후 삭제 (현재 가맹점 소유 규칙만 삭제)
    $delete_sql = " DELETE FROM holiday_rules
                    WHERE holiday_rule_id IN ({$ids_str})
                    AND shop_id = {$shop_id} ";

    sql_query_pg($delete_sql);

    alert('선택한 ' . count($chk_ids) . '개의 정기휴무 규칙이 삭제되었습니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

} else {
    alert('잘못된 요청입니다.', './holiday_rules_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>

