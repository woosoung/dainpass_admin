<?php
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 입력값 검증
$search_type = isset($_POST['search_type']) ? clean_xss_tags($_POST['search_type']) : '';
$search_value = isset($_POST['search_value']) ? clean_xss_tags($_POST['search_value']) : '';
$search_value = substr($search_value, 0, 100); // 길이 제한

// 날짜 범위 검증
$date_from = isset($_POST['date_from']) ? clean_xss_tags($_POST['date_from']) : '';
$date_to = isset($_POST['date_to']) ? clean_xss_tags($_POST['date_to']) : '';

// 날짜 형식 검증 (YYYY-MM-DD)
if ($date_from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    echo json_encode(array('success' => false, 'message' => '잘못된 시작일 형식입니다.'));
    exit;
}
if ($date_to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    echo json_encode(array('success' => false, 'message' => '잘못된 종료일 형식입니다.'));
    exit;
}

// 페이징 설정
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$page = ($page > 0 && $page <= 10000) ? $page : 1; // 최대 페이지 제한
$rows_per_page = 10; // 페이지당 10개
$offset = ($page - 1) * $rows_per_page;

// 화이트리스트 검증 - user_id 검색 제외!
$allowed_types = array('nickname', 'appointment_no', 'service_name');
if ($search_type && !in_array($search_type, $allowed_types)) {
    echo json_encode(array('success' => false, 'message' => '잘못된 검색 조건입니다.'));
    exit;
}

// WHERE 조건 생성
$where_search = '';
if ($search_value && $search_type) {
    // SQL injection 방지: clean_xss_tags() + str_replace() 패턴 사용
    // pg_escape_string() 사용 금지 (커스텀 구조로 작동 안함)
    $search_value_escaped = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $search_value);

    if ($search_type == 'nickname') {
        $where_search = " AND c.nickname ILIKE '%" . $search_value_escaped . "%' ";
    } else if ($search_type == 'appointment_no') {
        // appointment_no는 int 타입이므로 TEXT로 변환 후 검색
        // 숫자만 입력된 경우 정확히 일치하는 것만 검색, 아니면 부분 검색
        if (ctype_digit($search_value)) {
            // 숫자만 있는 경우: 정수 비교 (더 빠름) 또는 TEXT 변환 후 부분 검색
            $where_search = " AND (sa.appointment_no = " . (int)$search_value . " OR CAST(sa.appointment_no AS TEXT) ILIKE '%" . $search_value_escaped . "%') ";
        } else {
            // 숫자 외 문자가 있는 경우: TEXT 변환 후 검색
            $where_search = " AND CAST(sa.appointment_no AS TEXT) ILIKE '%" . $search_value_escaped . "%' ";
        }
    } else if ($search_type == 'service_name') {
        // 서비스명 검색 (집계 함수 사용하기 전 조인된 테이블에서 검색)
        $where_search = " AND ss.service_name ILIKE '%" . $search_value_escaped . "%' ";
    }
}

// 날짜 범위 조건 추가
$where_date = '';
if ($date_from && $date_to) {
    // 시작일 00:00:00부터 종료일 23:59:59까지
    $where_date = " AND asd.appointment_datetime >= '" . $date_from . " 00:00:00' AND asd.appointment_datetime <= '" . $date_to . " 23:59:59' ";
} else if ($date_from) {
    // 시작일만 지정된 경우 (시작일 00:00:00 이후)
    $where_date = " AND asd.appointment_datetime >= '" . $date_from . " 00:00:00' ";
} else if ($date_to) {
    // 종료일만 지정된 경우 (종료일 23:59:59 이전)
    $where_date = " AND asd.appointment_datetime <= '" . $date_to . " 23:59:59' ";
}

// 총 개수 조회 (페이지네이션용)
$count_sql = " SELECT COUNT(DISTINCT asd.shopdetail_id) as cnt
               FROM appointment_shop_detail AS asd
               INNER JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
               INNER JOIN customers AS c ON sa.customer_id = c.customer_id
               INNER JOIN payments AS p ON sa.appointment_id = p.appointment_id
               LEFT JOIN shop_appointment_details AS sad ON asd.shopdetail_id = sad.shopdetail_id
               LEFT JOIN shop_services AS ss ON sad.service_id = ss.service_id
               WHERE asd.shop_id = " . (int)$shop_id . "
                 AND p.status = 'DONE'
                 AND (p.pay_flag IS NULL OR p.pay_flag = 'GENERAL')
                 " . $where_search . "
                 " . $where_date . " ";
$count_row = sql_fetch_pg($count_sql);
$total_count = (int)$count_row['cnt'];
$total_page = $total_count > 0 ? ceil($total_count / $rows_per_page) : 1;

// SQL 쿼리 - 결제 완료된 예약만 조회
$sql = " SELECT DISTINCT
            asd.shopdetail_id,
            sa.appointment_no,
            asd.appointment_datetime,
            c.nickname,
            STRING_AGG(DISTINCT ss.service_name, ', ' ORDER BY ss.service_name) as service_names
         FROM appointment_shop_detail AS asd
         INNER JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
         INNER JOIN customers AS c ON sa.customer_id = c.customer_id
         INNER JOIN payments AS p ON sa.appointment_id = p.appointment_id
         LEFT JOIN shop_appointment_details AS sad ON asd.shopdetail_id = sad.shopdetail_id
         LEFT JOIN shop_services AS ss ON sad.service_id = ss.service_id
         WHERE asd.shop_id = " . (int)$shop_id . "
           AND p.status = 'DONE'
           AND (p.pay_flag IS NULL OR p.pay_flag = 'GENERAL')
           " . $where_search . "
           " . $where_date . "
         GROUP BY asd.shopdetail_id, sa.appointment_no, asd.appointment_datetime, c.nickname
         ORDER BY asd.appointment_datetime DESC
         LIMIT {$rows_per_page} OFFSET {$offset} ";

$result = sql_query_pg($sql);

$appointments = array();
if ($result && is_object($result) && isset($result->result)) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $appointment_datetime = $row['appointment_datetime'] ? date('Y-m-d H:i', strtotime($row['appointment_datetime'])) : '';
        $appointments[] = array(
            'shopdetail_id' => (int)$row['shopdetail_id'],
            // ⚠️ user_id는 JSON에 포함하지 않음! 개인정보보호법 준수
            'appointment_no' => $row['appointment_no'],
            'appointment_datetime' => $appointment_datetime,
            'nickname' => $row['nickname'],
            'service_names' => $row['service_names'] ? $row['service_names'] : '-',
            'appointment_info' => $row['appointment_no'] . ' (' . $appointment_datetime . ')'
        );
    }
}

echo json_encode(array(
    'success' => true,
    'appointments' => $appointments,
    'total_count' => $total_count,
    'current_page' => $page,
    'total_page' => $total_page,
    'rows_per_page' => $rows_per_page
));

exit;
