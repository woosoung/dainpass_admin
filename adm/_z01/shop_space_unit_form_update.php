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

if (!isset($_POST['name']) || !trim($_POST['name'])) {
    alert('유닛명을 입력해 주세요.');
}

if (!isset($_POST['capacity']) || (int)$_POST['capacity'] < 1) {
    alert('수용 인원은 1 이상이어야 합니다.');
}

// 입력값 처리
$group_id = (int)$_POST['group_id'];
$unit_type = trim($_POST['unit_type']);
$name = trim($_POST['name']);
$code = isset($_POST['code']) ? trim($_POST['code']) : '';
$capacity = (int)$_POST['capacity'];
$service_id = isset($_POST['service_id']) && (int)$_POST['service_id'] > 0 ? (int)$_POST['service_id'] : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// 좌표 정보
$pos_x = isset($_POST['pos_x']) && trim($_POST['pos_x']) !== '' ? (float)$_POST['pos_x'] : null;
$pos_y = isset($_POST['pos_y']) && trim($_POST['pos_y']) !== '' ? (float)$_POST['pos_y'] : null;
$width = isset($_POST['width']) && trim($_POST['width']) !== '' ? (float)$_POST['width'] : null;
$height = isset($_POST['height']) && trim($_POST['height']) !== '' ? (float)$_POST['height'] : null;
$rotation_deg = isset($_POST['rotation_deg']) && trim($_POST['rotation_deg']) !== '' ? (float)$_POST['rotation_deg'] : null;

// 좌석 정보
$seat_row = isset($_POST['seat_row']) ? trim($_POST['seat_row']) : '';
$seat_number = isset($_POST['seat_number']) ? trim($_POST['seat_number']) : '';

$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$is_active = isset($_POST['is_active']) && $_POST['is_active'] == 'f' ? 'f' : 't';

// 유닛 타입 검증
if (!in_array($unit_type, ['ROOM', 'TABLE', 'SEAT', 'VIRTUAL'])) {
    alert('올바른 유닛 타입이 아닙니다.');
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
    $sql = " INSERT INTO {$g5['shop_space_unit_table']} 
             (shop_id, group_id, unit_type, name, code, capacity, service_id, 
              description, pos_x, pos_y, width, height, rotation_deg, 
              seat_row, seat_number, sort_order, is_active, created_at, updated_at)
             VALUES 
             ({$shop_id}, {$group_id}, '{$unit_type}', '".addslashes($name)."', 
              '".addslashes($code)."', {$capacity}, 
              ".($service_id !== null ? $service_id : 'NULL').", 
              '".addslashes($description)."',
              ".($pos_x !== null ? $pos_x : 'NULL').",
              ".($pos_y !== null ? $pos_y : 'NULL').",
              ".($width !== null ? $width : 'NULL').",
              ".($height !== null ? $height : 'NULL').",
              ".($rotation_deg !== null ? $rotation_deg : 'NULL').",
              ".($seat_row ? "'".addslashes($seat_row)."'" : 'NULL').",
              ".($seat_number ? "'".addslashes($seat_number)."'" : 'NULL').",
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
    
    $sql = " UPDATE {$g5['shop_space_unit_table']} SET
             group_id = {$group_id},
             unit_type = '{$unit_type}',
             name = '".addslashes($name)."',
             code = '".addslashes($code)."',
             capacity = {$capacity},
             service_id = ".($service_id !== null ? $service_id : 'NULL').",
             description = '".addslashes($description)."',
             pos_x = ".($pos_x !== null ? $pos_x : 'NULL').",
             pos_y = ".($pos_y !== null ? $pos_y : 'NULL').",
             width = ".($width !== null ? $width : 'NULL').",
             height = ".($height !== null ? $height : 'NULL').",
             rotation_deg = ".($rotation_deg !== null ? $rotation_deg : 'NULL').",
             seat_row = ".($seat_row ? "'".addslashes($seat_row)."'" : 'NULL').",
             seat_number = ".($seat_number ? "'".addslashes($seat_number)."'" : 'NULL').",
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

