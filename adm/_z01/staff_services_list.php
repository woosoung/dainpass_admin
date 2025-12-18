<?php
$sub_menu = "930400";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

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

// 검색 조건
$sfl = isset($_REQUEST['sfl']) ? trim($_REQUEST['sfl']) : '';
$stx = isset($_REQUEST['stx']) ? trim($_REQUEST['stx']) : '';
$sst = isset($_REQUEST['sst']) ? trim($_REQUEST['sst']) : '';
$sod = isset($_REQUEST['sod']) ? trim($_REQUEST['sod']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

$qstr = '';
$sql_search = '';
$where = array();

// 가맹점측 관리자는 자신의 가맹점 직원만 조회 (store_id = shop_id)
$where[] = "store_id = {$shop_id}";

if ($stx) {
    switch ($sfl) {
        case 'name':
            $where[] = "name LIKE '%".addslashes($stx)."%'";
            break;
        case 'phone':
            $where[] = "phone LIKE '%".addslashes($stx)."%'";
            break;
        case 'specialty':
            $where[] = "specialty LIKE '%".addslashes($stx)."%'";
            break;
        case 'title':
            $where[] = "title LIKE '%".addslashes($stx)."%'";
            break;
    }
    $qstr .= '&sfl='.urlencode($sfl).'&stx='.urlencode($stx);
}

$sql_common = " FROM staff ";

if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "staff_id";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";

$result = sql_query_pg($sql);

$sql = " SELECT COUNT(*) AS total {$sql_common} {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 직원 이미지 썸네일 크기
$stfi_wd = 110;
$stfi_ht = 80;

// 서비스 이미지 썸네일 크기
$svci_wd = 80;
$svci_ht = 60;

$g5['title'] = '직원별서비스 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_services_list.js.php');
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건</span></span>
</div>

<form name="fsearch" method="get" class="local_sch01 local_sch">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="name"<?php echo get_selected($sfl, "name", true)?>>이름</option>
    <option value="phone"<?php echo get_selected($sfl, "phone")?>>전화번호</option>
    <option value="specialty"<?php echo get_selected($sfl, "specialty")?>>전문분야</option>
    <option value="title"<?php echo get_selected($sfl, "title")?>>직책</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?=$stx?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed tbl_sticky_100">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr class="success">
                <th scope="col" class="td_left">번호</th>
                <th scope="col" class="td_center" style="width:<?=$stfi_wd?>px">이미지</th>
                <th scope="col" class="td_left">이름</th>
                <th scope="col" class="td_left">전화번호</th>
                <th scope="col" class="td_left">직책</th>
                <th scope="col" class="td_left">전문분야</th>
                <th scope="col" class="td_center">슬롯당 최대고객수</th>
                <th scope="col" class="td_center">담당서비스</th>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_count == 0) {
                echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
            } else {
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $num = $total_count - ($page - 1) * $rows - $i;
                    $s_mod = '<a href="./staff_services_form.php?'.$qstr.'&amp;staff_id='.$row['staff_id'].'">관리</a>';

                    // 해당 직원의 이미지중에 fle_sort순으로 1개만 가져오는 쿼리
                    $fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'staff'
                                    AND fle_type = 'stfi'
                                    AND fle_dir = 'shop/staff_img'
                                    AND fle_db_idx = '{$row['staff_id']}'
                                ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
                    $fres = sql_fetch_pg($fsql);
                    // 이미지파일이 존재하면 썸네일 경로 생성
                    $row['thumb_tag'] = '';
                    if(!empty($fres['fle_path'])){
                        $row['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$stfi_wd.':'.$stfi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
                        $row['thumb_tag'] = '<img src="'.$row['thumb_url'].'" alt="'.get_text($row['name']).'" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
                    }
                    else {
                        $row['thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
                    }

                    // 해당 직원의 담당 서비스 조회
                    $service_sql = " SELECT ss.*, s.service_name, s.description, s.price, s.status as service_status
                                     FROM staff_services ss
                                     INNER JOIN shop_services s ON ss.service_id = s.service_id
                                     WHERE ss.staff_id = {$row['staff_id']}
                                       AND ss.shop_id = {$shop_id}
                                     ORDER BY ss.staff_service_id DESC ";
                    $service_result = sql_query_pg($service_sql);
                    $service_count = 0;
                    $service_list_html = '';
                    
                    if ($service_result && is_object($service_result) && isset($service_result->result)) {
                        while ($service_row = sql_fetch_array_pg($service_result->result)) {
                            $service_count++;
                            
                            // 서비스 이미지 조회
                            $svci_sql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                          WHERE fle_db_tbl = 'shop_services'
                                            AND fle_type = 'svci'
                                            AND fle_dir = 'shop/service_img'
                                            AND fle_db_idx = '{$service_row['service_id']}'
                                          ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
                            $svci_res = sql_fetch_pg($svci_sql);
                            $service_thumb = '';
                            if(!empty($svci_res['fle_path'])){
                                $svci_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$svci_wd.':'.$svci_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$svci_res['fle_path'];
                                $service_thumb = '<img src="'.$svci_url.'" alt="'.get_text($service_row['service_name']).'" width="'.$svci_wd.'" height="'.$svci_ht.'" style="border:1px solid #ddd;width:'.$svci_wd.'px;height:'.$svci_ht.'px;">';
                            } else {
                                $service_thumb = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$svci_wd.'" height="'.$svci_ht.'" style="border:1px solid #ddd;width:'.$svci_wd.'px;height:'.$svci_ht.'px;">';
                            }
                            
                            $status_text = ($service_row['service_status'] == 'active') ? '정상' : '비활성';
                            $staff_status_text = ($service_row['status'] == 'ok') ? '정상' : '대기';
                            
                            $service_list_html .= '<tr class="service-row-'.$row['staff_id'].'">';
                            $service_list_html .= '<td class="td_center">'.$service_thumb.'</td>';
                            $service_list_html .= '<td class="td_left">'.get_text($service_row['service_name']).'</td>';
                            $service_list_html .= '<td class="td_left">'.get_text(cut_str($service_row['description'] ?? '', 30)).'</td>';
                            $service_list_html .= '<td class="td_right">'.number_format($service_row['price'] ?? 0).'원</td>';
                            $service_list_html .= '<td class="td_center">'.$service_row['service_time'].'분</td>';
                            $service_list_html .= '<td class="td_center">'.$service_row['slot_max_persons_cnt'].'명</td>';
                            $service_list_html .= '<td class="td_center">'.$status_text.'</td>';
                            $service_list_html .= '<td class="td_center">'.$staff_status_text.'</td>';
                            $service_list_html .= '</tr>';
                        }
                    }

                    $bg = 'bg'.($i%2);
            ?>
            <tr class="<?=$bg?> staff-main-row" data-staff-id="<?=$row['staff_id']?>">
                <td class="td_num"><?=$num?></td>
                <td class="td_center"><?=$row['thumb_tag']?></td>
                <td class="td_left"><?=get_text($row['name'])?></td>
                <td class="td_left"><?=get_text($row['phone'] ?? '')?></td>
                <td class="td_left"><?=get_text($row['title'] ?? '')?></td>
                <td class="td_left"><?=get_text($row['specialty'] ?? '')?></td>
                <td class="td_center"><?=$row['max_customers_per_slot']?></td>
                <td class="td_center">
                    <?php if ($service_count > 0): ?>
                        <button type="button" class="btn-toggle-services btn btn_02" data-staff-id="<?=$row['staff_id']?>">
                            <span class="service-count"><?=$service_count?>개</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                    <?php else: ?>
                        <span class="text-gray-400">없음</span>
                    <?php endif; ?>
                </td>
                <td class="td_mng"><?=$s_mod?></td>
            </tr>
            <?php if ($service_count > 0): ?>
            <tr class="service-accordion-row service-accordion-<?=$row['staff_id']?>" style="display:none;">
                <td colspan="9" class="p-0">
                    <div class="service-accordion-content">
                        <table class="table table-bordered table-condensed" style="margin:0;">
                            <thead>
                                <tr class="info">
                                    <th scope="col" class="!bg-gray-400 td_center" style="width:<?=$svci_wd?>px">이미지</th>
                                    <th scope="col" class="!bg-gray-400 td_left">서비스명</th>
                                    <th scope="col" class="!bg-gray-400 td_left">설명</th>
                                    <th scope="col" class="!bg-gray-400 td_right">가격</th>
                                    <th scope="col" class="!bg-gray-400 td_center">서비스시간(분)</th>
                                    <th scope="col" class="!bg-gray-400 td_center">슬롯당 고객수</th>
                                    <th scope="col" class="!bg-gray-400 td_center">서비스상태</th>
                                    <th scope="col" class="!bg-gray-400 td_center">담당상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?=$service_list_html?>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}".($qstr ? "?$qstr&amp;page=" : "?page="));
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

