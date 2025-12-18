<?php
$sub_menu = "930400";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
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
        
        // mb_1 = '0'인 경우: 플랫폼 관리자
        if ($mb_1_value === '0' || $mb_1_value === '') {
            // 플랫폼 관리자는 shop_id = 0에 해당하는 레코드가 없으므로 '업체 데이터가 없습니다.' 표시
            $g5['title'] = '직원별서비스 관리';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
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
                // shop_id에 해당하는 레코드가 없는 경우
                $g5['title'] = '직원별서비스 관리';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    $g5['title'] = '직원별서비스 관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

// 검색 조건 및 페이징 정보
$sfl = isset($_REQUEST['sfl']) ? trim($_REQUEST['sfl']) : '';
$stx = isset($_REQUEST['stx']) ? trim($_REQUEST['stx']) : '';
$sst = isset($_REQUEST['sst']) ? trim($_REQUEST['sst']) : '';
$sod = isset($_REQUEST['sod']) ? trim($_REQUEST['sod']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

$qstr = '';
$list_qstr = '';
if ($sfl) {
    $qstr .= '&sfl='.urlencode($sfl);
    $list_qstr .= '&sfl='.urlencode($sfl);
}
if ($stx) {
    $qstr .= '&stx='.urlencode($stx);
    $list_qstr .= '&stx='.urlencode($stx);
}
if ($sst) {
    $qstr .= '&sst='.urlencode($sst);
    $list_qstr .= '&sst='.urlencode($sst);
}
if ($sod) {
    $qstr .= '&sod='.urlencode($sod);
    $list_qstr .= '&sod='.urlencode($sod);
}
if ($page > 1) {
    $qstr .= '&page='.$page;
    $list_qstr .= '&page='.$page;
}

// 안전한 기본값 설정 및 입력 수신
$staff_id = isset($staff_id) ? (int)$staff_id : (isset($_REQUEST['staff_id']) ? (int)$_REQUEST['staff_id'] : 0);

if (!$staff_id) {
    alert('직원 정보가 없습니다.');
}

// 직원 정보 조회
$sql = " SELECT * FROM staff WHERE staff_id = {$staff_id} AND store_id = {$shop_id} LIMIT 1 ";
$result = sql_query_pg($sql);
$staff = array();
if ($result && is_object($result) && isset($result->result)) {
    $staff = sql_fetch_array_pg($result->result);
}

if (!$staff || !isset($staff['staff_id']) || !$staff['staff_id']) {
    alert('존재하지 않는 직원자료입니다.');
}

// 해당 직원의 이미지
$fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
            WHERE fle_db_tbl = 'staff'
                AND fle_type = 'stfi'
                AND fle_dir = 'shop/staff_img'
                AND fle_db_idx = '{$staff_id}'
            ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
$fres = sql_fetch_pg($fsql);
$stfi_wd = 110;
$stfi_ht = 80;
$staff_thumb = '';
if(!empty($fres['fle_path'])){
    $staff_thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$stfi_wd.':'.$stfi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
    $staff_thumb = '<img src="'.$staff_thumb_url.'" alt="'.get_text($staff['name']).'" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
} else {
    $staff_thumb = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
}

// 해당 가맹점의 서비스 목록 조회 (등록되지 않은 서비스만)
$service_sql = " SELECT s.*
                  FROM shop_services s
                  WHERE s.shop_id = {$shop_id}
                    AND s.status = 'active'
                    AND NOT EXISTS (
                        SELECT 1 FROM staff_services ss
                        WHERE ss.service_id = s.service_id
                          AND ss.staff_id = {$staff_id}
                          AND ss.shop_id = {$shop_id}
                    )
                  ORDER BY s.service_name ASC ";
$service_result = sql_query_pg($service_sql);

// 이미 등록된 직원별 서비스 목록 조회
$registered_sql = " SELECT ss.*, s.service_name, s.description, s.price
                     FROM staff_services ss
                     INNER JOIN shop_services s ON ss.service_id = s.service_id
                     WHERE ss.staff_id = {$staff_id}
                       AND ss.shop_id = {$shop_id}
                     ORDER BY ss.staff_service_id DESC ";
$registered_result = sql_query_pg($registered_sql);

$g5['title'] = '직원별서비스 관리';

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_services_form.js.php');
?>

<form name="form01" id="form01" action="./staff_services_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">

<div class="local_desc01 local_desc">
    <p>직원별 담당 서비스를 관리해 주세요.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>직원 정보</caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">직원 이미지</th>
        <td colspan="3"><?=$staff_thumb?></td>
    </tr>
    <tr>
        <th scope="row">이름</th>
        <td><?=get_text($staff['name'])?></td>
        <th scope="row">전화번호</th>
        <td><?=get_text($staff['phone'] ?? '')?></td>
    </tr>
    <tr>
        <th scope="row">직책</th>
        <td><?=get_text($staff['title'] ?? '')?></td>
        <th scope="row">전문분야</th>
        <td><?=get_text($staff['specialty'] ?? '')?></td>
    </tr>
    </tbody>
    </table>
</div>

<div class="tbl_frm01 tbl_wrap" style="margin-top:20px;">
    <table>
    <caption>서비스 선택</caption>
    <colgroup>
        <col class="grid_4" style="width:20%;">
        <col style="width:80%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">서비스 선택</th>
        <td>
            <select name="service_select" id="service_select" class="frm_input" style="width:300px;">
                <option value="">::서비스를 선택하세요::</option>
                <?php
                if ($service_result && is_object($service_result) && isset($service_result->result)) {
                    while ($service_row = sql_fetch_array_pg($service_result->result)) {
                        $service_desc = get_text(cut_str($service_row['description'] ?? '', 50));
                        $service_price = number_format($service_row['price'] ?? 0);
                        echo '<option value="'.$service_row['service_id'].'" 
                                data-service-name="'.htmlspecialchars($service_row['service_name'], ENT_QUOTES).'" 
                                data-service-desc="'.htmlspecialchars($service_desc, ENT_QUOTES).'" 
                                data-service-price="'.$service_price.'" 
                                data-service-time="'.$service_row['service_time'].'">'.get_text($service_row['service_name']).'</option>';
                    }
                }
                ?>
            </select>
            <button type="button" id="btn_add_service" class="btn btn_01" style="margin-left:10px;">등록</button>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="tbl_frm01 tbl_wrap" style="margin-top:20px;">
    <table>
    <caption>등록된 담당 서비스 목록</caption>
    <colgroup>
        <col style="width:5%;">
        <col style="width:10%;">
        <col style="width:20%;">
        <col style="width:25%;">
        <col style="width:10%;">
        <col style="width:15%;">
        <col style="width:10%;">
        <col style="width:5%;">
    </colgroup>
    <thead>
        <tr class="success">
            <th scope="col" class="td_center">번호</th>
            <th scope="col" class="td_center">서비스명</th>
            <th scope="col" class="td_left">설명</th>
            <th scope="col" class="td_right">가격</th>
            <th scope="col" class="td_center">서비스시간(분)</th>
            <th scope="col" class="td_center">슬롯당 고객수</th>
            <th scope="col" class="td_center">상태</th>
            <th scope="col" class="td_center">삭제</th>
        </tr>
    </thead>
    <tbody id="selected_services_list">
        <?php
        $registered_count = 0;
        if ($registered_result && is_object($registered_result) && isset($registered_result->result)) {
            while ($registered_row = sql_fetch_array_pg($registered_result->result)) {
                $registered_count++;
                $status_text = ($registered_row['status'] == 'ok') ? '정상' : '대기';
                echo '<tr data-service-id="'.$registered_row['service_id'].'" data-staff-service-id="'.$registered_row['staff_service_id'].'">';
                echo '<td class="td_num">'.$registered_count.'</td>';
                echo '<td class="td_left">'.get_text($registered_row['service_name']).'</td>';
                echo '<td class="td_left">'.get_text(cut_str($registered_row['description'] ?? '', 50)).'</td>';
                echo '<td class="td_right">'.number_format($registered_row['price'] ?? 0).'원</td>';
                echo '<td class="td_center">';
                echo '<input type="hidden" name="staff_service_id[]" value="'.$registered_row['staff_service_id'].'">';
                echo '<input type="hidden" name="service_id[]" value="'.$registered_row['service_id'].'">';
                echo '<input type="number" name="service_time[]" value="'.$registered_row['service_time'].'" class="text-center frm_input" style="width:80px;" min="0" max="1440" required>';
                echo '</td>';
                echo '<td class="td_center">';
                echo '<input type="number" name="slot_max_persons_cnt[]" value="'.$registered_row['slot_max_persons_cnt'].'" class="text-center frm_input" style="width:80px;" min="1" max="100" required>';
                echo '</td>';
                echo '<td class="td_center">';
                echo '<select name="status[]" class="frm_input">';
                echo '<option value="ok">정상</option>';
                echo '<option value="pending">대기</option>';
                echo '</select>';
                echo '<script>$("select[name=\'status[]\']").eq('.($registered_count-1).').val("'.$registered_row['status'].'");</script>';
                echo '</td>';
                echo '<td class="td_center">';
                echo '<button type="button" class="btn-remove-service btn btn_02" data-service-id="'.$registered_row['service_id'].'">취소</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./staff_services_list.php<?=$list_qstr ? '?'.ltrim($list_qstr, '&') : ''?>" class="btn btn_02">목록</a>
    <input type="submit" value="등록" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

