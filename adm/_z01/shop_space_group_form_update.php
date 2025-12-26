<?php
$sub_menu = "930550";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

check_admin_token();

$w = isset($_POST['w']) ? trim($_POST['w']) : '';
$group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

// w 값 화이트리스트 검증
$w_whitelist = ['', 'c', 'u'];
if (!in_array($w, $w_whitelist)) {
    alert('잘못된 요청입니다.');
}

// 필수 입력값 검증
if (!isset($_POST['group_type']) || !trim($_POST['group_type'])) {
    alert('그룹 타입을 선택해 주세요.');
}

if (!isset($_POST['name']) || !trim($_POST['name'])) {
    alert('그룹명을 입력해 주세요.');
}

// 입력값 처리
$group_type = trim($_POST['group_type']);
$name = trim($_POST['name']);
$level_no = isset($_POST['level_no']) && trim($_POST['level_no']) !== '' ? (int)$_POST['level_no'] : null;
$canvas_width = isset($_POST['canvas_width']) && trim($_POST['canvas_width']) !== '' ? (int)$_POST['canvas_width'] : null;
$canvas_height = isset($_POST['canvas_height']) && trim($_POST['canvas_height']) !== '' ? (int)$_POST['canvas_height'] : null;
$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$is_active = isset($_POST['is_active']) ? trim($_POST['is_active']) : 't';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// 그룹 타입 화이트리스트 검증
$group_type_whitelist = ['FLOOR', 'HALL', 'ZONE'];
if (!in_array($group_type, $group_type_whitelist)) {
    alert('올바른 그룹 타입이 아닙니다.');
}

// 그룹명 길이 검증
if (mb_strlen($name, 'UTF-8') > 100) {
    alert('그룹명은 최대 100자까지 입력 가능합니다.');
}

// 층 번호 범위 검증 (입력된 경우)
if ($level_no !== null && ($level_no < -999 || $level_no > 999)) {
    alert('층 번호는 -999 이상 999 이하로 입력해 주세요.');
}

// 캔버스 크기 검증 (둘 다 입력하거나 둘 다 비워야 함)
if (($canvas_width !== null && $canvas_height === null) || ($canvas_width === null && $canvas_height !== null)) {
    alert('캔버스 크기는 가로와 세로를 모두 입력해 주세요.');
}

// 캔버스 크기 범위 검증
if ($canvas_width !== null) {
    if ($canvas_width < 100 || $canvas_width > 10000) {
        alert('캔버스 가로 크기는 100 이상 10000 이하로 입력해 주세요.');
    }
}

if ($canvas_height !== null) {
    if ($canvas_height < 100 || $canvas_height > 10000) {
        alert('캔버스 세로 크기는 100 이상 10000 이하로 입력해 주세요.');
    }
}

// 정렬순서 범위 검증
if ($sort_order < 0 || $sort_order > 9999) {
    alert('정렬순서는 0 이상 9999 이하로 입력해 주세요.');
}

// is_active 화이트리스트 검증
$is_active_whitelist = ['t', 'f'];
$is_active = in_array($is_active, $is_active_whitelist) ? $is_active : 't';

// 설명 길이 검증
if (mb_strlen($description, 'UTF-8') > 1000) {
    alert('설명은 최대 1000자까지 입력 가능합니다.');
}

if ($w == '' || $w == 'c') {
    // 신규 등록
    $sql = " INSERT INTO {$g5['shop_space_group_table']} 
             (shop_id, group_type, name, level_no, canvas_width, canvas_height, 
              sort_order, is_active, description, created_at, updated_at)
             VALUES 
             ({$shop_id}, '{$group_type}', '".addslashes($name)."', 
              ".($level_no !== null ? $level_no : 'NULL').", 
              ".($canvas_width !== null ? $canvas_width : 'NULL').", 
              ".($canvas_height !== null ? $canvas_height : 'NULL').", 
              {$sort_order}, '{$is_active}', '".addslashes($description)."', 
              '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."')
             RETURNING group_id
    ";
    $result = sql_query_pg($sql);
    
    if (!$result || !is_object($result) || !isset($result->result)) {
        alert('공간 그룹 등록에 실패했습니다.');
    }
    
    $group_id_row = sql_fetch_array_pg($result->result);
    
    if (!$group_id_row || !isset($group_id_row['group_id'])) {
        alert('공간 그룹 등록 후 ID를 가져오는데 실패했습니다.');
    }
    
    $group_id = $group_id_row['group_id'];
    
} else if ($w == 'u' && $group_id) {
    // 수정
    // 권한 확인
    $check_sql = " SELECT group_id FROM {$g5['shop_space_group_table']} 
                  WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !isset($check_row['group_id'])) {
        alert('존재하지 않거나 권한이 없는 공간 그룹입니다.');
    }
    
    $sql = " UPDATE {$g5['shop_space_group_table']} SET
             group_type = '{$group_type}',
             name = '".addslashes($name)."',
             level_no = ".($level_no !== null ? $level_no : 'NULL').",
             canvas_width = ".($canvas_width !== null ? $canvas_width : 'NULL').",
             canvas_height = ".($canvas_height !== null ? $canvas_height : 'NULL').",
             sort_order = {$sort_order},
             is_active = '{$is_active}',
             description = '".addslashes($description)."',
             updated_at = '".G5_TIME_YMDHIS."'
             WHERE group_id = {$group_id}
    ";
    sql_query_pg($sql);
    
} else {
    alert('잘못된 요청입니다.');
}

// 파일 삭제 처리
if (isset($_POST['file_del']) && is_array($_POST['file_del']) && count($_POST['file_del']) > 0) {
    $validated_file_del = array();
    foreach ($_POST['file_del'] as $fle_idx) {
        $fle_idx = (int)$fle_idx;
        if ($fle_idx > 0) {
            $validated_file_del[] = $fle_idx;
        }
    }
    if (count($validated_file_del) > 0) {
        delete_idx_s3_file($validated_file_del);
    }
}

// 파일 업로드 처리
if (isset($_FILES['group_images']) && isset($_FILES['group_images']['name']) && count($_FILES['group_images']['name']) > 0) {
    upload_multi_file($_FILES['group_images'], 'shop_space_group', $group_id, 'shop/shop_img', 'ssg');
}

if ($w == '' || $w == 'c') {
    alert('공간 그룹을 등록했습니다.', './shop_space_group_list.php?'.$qstr);
} else {
    alert('공간 그룹을 수정했습니다.', './shop_space_group_list.php?'.$qstr);
}
?>

