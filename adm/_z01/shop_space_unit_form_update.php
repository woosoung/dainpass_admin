<?php
$sub_menu = "930600";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

check_admin_token();

$w = isset($_POST['w']) ? trim($_POST['w']) : '';
$unit_id = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

// 필수 입력값 검증
if (!isset($_POST['group_id']) || !(int)$_POST['group_id']) {
    alert('공간 그룹을 선택해 주세요.');
}

if (!isset($_POST['unit_type']) || !trim($_POST['unit_type'])) {
    alert('유닛 타입을 선택해 주세요.');
}

// 유닛 타입 화이트리스트 검증
$unit_type_whitelist = ['ROOM', 'TABLE', 'SEAT', 'VIRTUAL'];
if (!in_array(trim($_POST['unit_type']), $unit_type_whitelist)) {
    alert('올바른 유닛 타입이 아닙니다.');
}

if (!isset($_POST['name']) || !trim($_POST['name'])) {
    alert('유닛명을 입력해 주세요.');
}

// 유닛명 길이 검증
if (mb_strlen(trim($_POST['name']), 'UTF-8') > 100) {
    alert('유닛명은 최대 100자까지 입력 가능합니다.');
}

if (!isset($_POST['capacity']) || (int)$_POST['capacity'] < 1) {
    alert('수용 인원은 1 이상이어야 합니다.');
}

// 수용 인원 범위 검증
if ((int)$_POST['capacity'] > 9999) {
    alert('수용 인원은 9999 이하로 입력해 주세요.');
}

// 입력값 처리 및 XSS 방지
$group_id = (int)$_POST['group_id'];
$unit_type = clean_xss_tags(trim($_POST['unit_type']));
$name = clean_xss_tags(trim($_POST['name']));
$code = isset($_POST['code']) ? clean_xss_tags(trim($_POST['code'])) : '';
$capacity = (int)$_POST['capacity'];
$service_id = isset($_POST['service_id']) && (int)$_POST['service_id'] > 0 ? (int)$_POST['service_id'] : null;
$description = isset($_POST['description']) ? clean_xss_tags(trim($_POST['description'])) : '';

// 코드 길이 검증
if (mb_strlen($code, 'UTF-8') > 50) {
    alert('유닛 코드는 최대 50자까지 입력 가능합니다.');
}

// 설명 길이 검증
if (mb_strlen($description, 'UTF-8') > 1000) {
    alert('설명은 최대 1000자까지 입력 가능합니다.');
}

// 좌표 정보
$pos_x = isset($_POST['pos_x']) && trim($_POST['pos_x']) !== '' ? (float)$_POST['pos_x'] : null;
$pos_y = isset($_POST['pos_y']) && trim($_POST['pos_y']) !== '' ? (float)$_POST['pos_y'] : null;
$width = isset($_POST['width']) && trim($_POST['width']) !== '' ? (float)$_POST['width'] : null;
$height = isset($_POST['height']) && trim($_POST['height']) !== '' ? (float)$_POST['height'] : null;
$rotation_deg = isset($_POST['rotation_deg']) && trim($_POST['rotation_deg']) !== '' ? (float)$_POST['rotation_deg'] : null;

// 좌표 범위 검증
if ($pos_x !== null && ($pos_x < -10000 || $pos_x > 10000)) {
    alert('X 좌표는 -10000 이상 10000 이하로 입력해 주세요.');
}

if ($pos_y !== null && ($pos_y < -10000 || $pos_y > 10000)) {
    alert('Y 좌표는 -10000 이상 10000 이하로 입력해 주세요.');
}

// 크기 범위 검증
if ($width !== null && ($width < 20 || $width > 10000)) {
    alert('가로 크기는 20 이상 10000 이하로 입력해 주세요.');
}

if ($height !== null && ($height < 20 || $height > 10000)) {
    alert('세로 크기는 20 이상 10000 이하로 입력해 주세요.');
}

// 회전 각도 범위 검증
if ($rotation_deg !== null && ($rotation_deg < -360 || $rotation_deg > 360)) {
    alert('회전 각도는 -360 이상 360 이하로 입력해 주세요.');
}

// 좌석 정보 및 XSS 방지
$seat_row = isset($_POST['seat_row']) ? clean_xss_tags(trim($_POST['seat_row'])) : '';
$seat_number = isset($_POST['seat_number']) ? clean_xss_tags(trim($_POST['seat_number'])) : '';

// 좌석 정보 길이 검증
if (mb_strlen($seat_row, 'UTF-8') > 10) {
    alert('좌석 열은 최대 10자까지 입력 가능합니다.');
}

if (mb_strlen($seat_number, 'UTF-8') > 10) {
    alert('좌석 번호는 최대 10자까지 입력 가능합니다.');
}

$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$is_active = isset($_POST['is_active']) && $_POST['is_active'] == 'f' ? 'f' : 't';

// 정렬순서 범위 검증
if ($sort_order < 0 || $sort_order > 9999) {
    alert('정렬순서는 0 이상 9999 이하로 입력해 주세요.');
}

// 그룹이 현재 shop_id 소유인지 확인
$group_check_sql = " SELECT group_id FROM {$g5['shop_space_group_table']} 
                     WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
$group_check_row = sql_fetch_pg($group_check_sql);
if (!$group_check_row || !isset($group_check_row['group_id'])) {
    alert('존재하지 않거나 권한이 없는 공간 그룹입니다.');
}

if ($w == '' || $w == 'c') {
    // 신규 등록
    // SQL 인젝션 방지
    $name_escaped = sql_escape_string($name);
    $code_escaped = sql_escape_string($code);
    $description_escaped = sql_escape_string($description);
    $seat_row_escaped = sql_escape_string($seat_row);
    $seat_number_escaped = sql_escape_string($seat_number);

    $sql = " INSERT INTO {$g5['shop_space_unit_table']}
             (shop_id, group_id, unit_type, name, code, capacity, service_id,
              description, pos_x, pos_y, width, height, rotation_deg,
              seat_row, seat_number, sort_order, is_active, created_at, updated_at)
             VALUES
             ({$shop_id}, {$group_id}, '{$unit_type}', '{$name_escaped}',
              '{$code_escaped}', {$capacity},
              ".($service_id !== null ? $service_id : 'NULL').",
              '{$description_escaped}',
              ".($pos_x !== null ? $pos_x : 'NULL').",
              ".($pos_y !== null ? $pos_y : 'NULL').",
              ".($width !== null ? $width : 'NULL').",
              ".($height !== null ? $height : 'NULL').",
              ".($rotation_deg !== null ? $rotation_deg : 'NULL').",
              ".($seat_row ? "'{$seat_row_escaped}'" : 'NULL').",
              ".($seat_number ? "'{$seat_number_escaped}'" : 'NULL').",
              {$sort_order}, '{$is_active}',
              '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."')
             RETURNING unit_id
    ";
    $result = sql_query_pg($sql);
    
    if (!$result || !is_object($result) || !isset($result->result)) {
        alert('공간 유닛 등록에 실패했습니다.');
    }
    
    $unit_id_row = sql_fetch_array_pg($result->result);
    
    if (!$unit_id_row || !isset($unit_id_row['unit_id'])) {
        alert('공간 유닛 등록 후 ID를 가져오는데 실패했습니다.');
    }
    
    $unit_id = $unit_id_row['unit_id'];
    
} else if ($w == 'u' && $unit_id) {
    // 수정
    // 권한 확인
    $check_sql = " SELECT unit_id FROM {$g5['shop_space_unit_table']}
                  WHERE unit_id = {$unit_id} AND shop_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);

    if (!$check_row || !isset($check_row['unit_id'])) {
        alert('존재하지 않거나 권한이 없는 공간 유닛입니다.');
    }

    // SQL 인젝션 방지
    $name_escaped = sql_escape_string($name);
    $code_escaped = sql_escape_string($code);
    $description_escaped = sql_escape_string($description);
    $seat_row_escaped = sql_escape_string($seat_row);
    $seat_number_escaped = sql_escape_string($seat_number);

    $sql = " UPDATE {$g5['shop_space_unit_table']} SET
             group_id = {$group_id},
             unit_type = '{$unit_type}',
             name = '{$name_escaped}',
             code = '{$code_escaped}',
             capacity = {$capacity},
             service_id = ".($service_id !== null ? $service_id : 'NULL').",
             description = '{$description_escaped}',
             pos_x = ".($pos_x !== null ? $pos_x : 'NULL').",
             pos_y = ".($pos_y !== null ? $pos_y : 'NULL').",
             width = ".($width !== null ? $width : 'NULL').",
             height = ".($height !== null ? $height : 'NULL').",
             rotation_deg = ".($rotation_deg !== null ? $rotation_deg : 'NULL').",
             seat_row = ".($seat_row ? "'{$seat_row_escaped}'" : 'NULL').",
             seat_number = ".($seat_number ? "'{$seat_number_escaped}'" : 'NULL').",
             sort_order = {$sort_order},
             is_active = '{$is_active}',
             updated_at = '".G5_TIME_YMDHIS."'
             WHERE unit_id = {$unit_id}
    ";
    sql_query_pg($sql);
    
} else {
    alert('잘못된 요청입니다.');
}

// 파일 삭제 처리
if (isset($_POST['file_del']) && is_array($_POST['file_del']) && count($_POST['file_del']) > 0) {
    delete_idx_s3_file($_POST['file_del']);
}

// 파일 업로드 처리
if (isset($_FILES['unit_images']) && isset($_FILES['unit_images']['name']) && count($_FILES['unit_images']['name']) > 0) {
    upload_multi_file($_FILES['unit_images'], 'shop_space_unit', $unit_id, 'shop/shop_img', 'ssu');
}

if ($w == '' || $w == 'c') {
    alert('공간 유닛을 등록했습니다.', './shop_space_unit_list.php?'.$qstr);
} else {
    alert('공간 유닛을 수정했습니다.', './shop_space_unit_list.php?'.$qstr);
}
?>

