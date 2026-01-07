<?php
$sub_menu = "930300";
include_once("./_common.php");

@auth_check($auth[$sub_menu], 'w');

// 가맹점측 관리자 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

$w = isset($_POST['w']) ? trim($_POST['w']) : '';

$sst = isset($_POST['sst']) ? trim($_POST['sst']) : '';
$sod = isset($_POST['sod']) ? trim($_POST['sod']) : '';
$sfl = isset($_POST['sfl']) ? trim($_POST['sfl']) : '';
$stx = isset($_POST['stx']) ? trim($_POST['stx']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

$qstr = '';
if ($sfl) $qstr .= '&sfl='.urlencode($sfl);
if ($stx) $qstr .= '&stx='.urlencode($stx);
if ($sst) $qstr .= '&sst='.urlencode($sst);
if ($sod) $qstr .= '&sod='.urlencode($sod);
if ($page > 1) $qstr .= '&page='.$page;

$act_button = isset($_POST['act_button']) ? trim($_POST['act_button']) : '';

if ($act_button == '선택수정') {
    check_demo();
    
    if (!isset($_POST['chk']) || !is_array($_POST['chk']) || count($_POST['chk']) == 0) {
        alert('수정할 항목을 선택해 주세요.');
    }
    
    $chk = $_POST['chk'];
    
    // 선택된 직원들 업데이트
    if (isset($_POST['staff_id']) && is_array($_POST['staff_id'])) {
        foreach ($_POST['staff_id'] as $idx => $staff_id_val) {
            $staff_id_val = (int)$staff_id_val;
            
            // 선택된 직원만 처리
            if (!in_array($staff_id_val, $chk)) {
                continue;
            }
            
            // 해당 직원이 해당 가맹점의 것인지 확인
            $check_sql = " SELECT staff_id FROM staff WHERE staff_id = {$staff_id_val} AND store_id = {$shop_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['staff_id']) {
                $max_customers_per_slot = isset($_POST['max_customers_per_slot'][$idx]) ? (int)$_POST['max_customers_per_slot'][$idx] : 1;

                // 슬롯당 최대고객수 범위 검증
                if ($max_customers_per_slot < 1 || $max_customers_per_slot > 100) {
                    alert('슬롯당 최대고객수는 1명 이상 100명 이하로 입력해 주세요.');
                }

                if ($max_customers_per_slot < 1) $max_customers_per_slot = 1;

                $update_sql = " UPDATE staff SET 
                                max_customers_per_slot = {$max_customers_per_slot},
                                updated_at = '".G5_TIME_YMDHIS."'
                            WHERE staff_id = {$staff_id_val} AND store_id = {$shop_id} ";
                sql_query_pg($update_sql);
            }
        }
    }
    
    alert('선택한 항목을 수정했습니다.', './staff_list.php?'.$qstr);
}

if ($act_button == '선택삭제') {
    @auth_check($auth[$sub_menu], 'd');

    check_demo();
    
    if (!isset($_POST['chk']) || !is_array($_POST['chk']) || count($_POST['chk']) == 0) {
        alert('삭제할 항목을 선택해 주세요.');
    }
    
    $chk = $_POST['chk'];
    foreach ($chk as $staff_id) {
        $staff_id = (int)$staff_id;
        
        // 해당 직원이 해당 가맹점의 것인지 확인
        $check_sql = " SELECT staff_id FROM staff WHERE staff_id = {$staff_id} AND store_id = {$shop_id} ";
        $check_row = sql_fetch_pg($check_sql);
        
        if ($check_row && $check_row['staff_id']) {
            // 관련 이미지 파일 삭제
            $img_sql = " SELECT fle_idx FROM {$g5['dain_file_table']}
                         WHERE fle_db_tbl = 'staff'
                           AND fle_db_idx = '{$staff_id}'
                           AND fle_type = 'stfi'
                           AND fle_dir = 'shop/staff_img' ";
            $img_result = sql_query_pg($img_sql);
            if ($img_result && is_object($img_result) && isset($img_result->result)) {
                $del_arr = array();
                while ($img_row = sql_fetch_array_pg($img_result->result)) {
                    $del_arr[] = $img_row['fle_idx'];
                }
                if (count($del_arr) > 0) {
                    delete_idx_s3_file($del_arr);
                }
            }
            
            // 직원 삭제
            $delete_sql = " DELETE FROM staff WHERE staff_id = {$staff_id} AND store_id = {$shop_id} ";
            sql_query_pg($delete_sql);
        }
    }
    
    alert('선택한 항목을 삭제했습니다.', './staff_list.php?'.$qstr);
}

goto_url('./staff_list.php?'.$qstr);
?>

