<?php
$sub_menu = "930600";
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 권한 체크 (AJAX용) - 쓰기 권한 필요
if (!isset($auth[$sub_menu]) || strpos($auth[$sub_menu], 'w') === false) {
    echo json_encode(['success' => false, 'message' => '쓰기 권한이 없습니다.']);
    exit;
}

// 가맹점 접근 권한 체크 (JSON 모드)
$result = check_shop_access(array(
    'output_mode' => 'json',
));
$shop_id = $result['shop_id'];

// JSON 입력 받기
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['group_id']) || !isset($data['units'])) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 없습니다.']);
    exit;
}

$group_id = (int)$data['group_id'];
$units = $data['units'];

if (!is_array($units)) {
    echo json_encode(['success' => false, 'message' => '유닛 데이터가 올바르지 않습니다.']);
    exit;
}

// group_id 소유권 확인
$group_check_sql = " SELECT group_id FROM {$g5['shop_space_group_table']} 
                     WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
$group_check_row = sql_fetch_pg($group_check_sql, 0);

if (!$group_check_row || !isset($group_check_row['group_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => '공간 그룹을 찾을 수 없습니다.',
        'group_id' => $group_id,
        'shop_id' => $shop_id
    ]);
    exit;
}

// 각 유닛 업데이트
$updated_count = 0;
$error_units = array();

foreach ($units as $unit_data) {
    if (!isset($unit_data['unit_id'])) {
        continue;
    }
    
    $unit_id = (int)$unit_data['unit_id'];
    $pos_x = isset($unit_data['pos_x']) ? (float)$unit_data['pos_x'] : null;
    $pos_y = isset($unit_data['pos_y']) ? (float)$unit_data['pos_y'] : null;
    $width = isset($unit_data['width']) ? (float)$unit_data['width'] : null;
    $height = isset($unit_data['height']) ? (float)$unit_data['height'] : null;
    $rotation_deg = isset($unit_data['rotation_deg']) ? (float)$unit_data['rotation_deg'] : null;
    
    // unit 소유권 확인
    $unit_check_sql = " SELECT unit_id FROM {$g5['shop_space_unit_table']} 
                       WHERE unit_id = {$unit_id} 
                       AND shop_id = {$shop_id} 
                       AND group_id = {$group_id} ";
    $unit_check_row = sql_fetch_pg($unit_check_sql, 0);
    
    if (!$unit_check_row || !isset($unit_check_row['unit_id'])) {
        $error_units[] = $unit_id;
        continue;
    }
    
    // 업데이트 SQL
    $update_sql = " UPDATE {$g5['shop_space_unit_table']} SET
                   pos_x = ".($pos_x !== null ? $pos_x : 'NULL').",
                   pos_y = ".($pos_y !== null ? $pos_y : 'NULL').",
                   width = ".($width !== null ? $width : 'NULL').",
                   height = ".($height !== null ? $height : 'NULL').",
                   rotation_deg = ".($rotation_deg !== null ? $rotation_deg : 'NULL').",
                   updated_at = NOW()
                   WHERE unit_id = {$unit_id} ";
    
    $result = sql_query_pg($update_sql, 0);
    if ($result !== false) {
        $updated_count++;
    }
}

// 응답 생성
$response = [
    'success' => true,
    'message' => "{$updated_count}개 유닛의 좌표가 업데이트되었습니다.",
    'updated_count' => $updated_count,
    'total_units' => count($units),
    'shop_id' => $shop_id,
    'group_id' => $group_id
];

if (count($error_units) > 0) {
    $response['warning'] = count($error_units) . '개 유닛을 찾을 수 없었습니다.';
    $response['error_units'] = $error_units;
}

echo json_encode($response);
exit;
?>
