<?php
$sub_menu = "930200";
include_once('./_common.php');

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
            $g5['title'] = '서비스/메뉴 관리';
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
                $g5['title'] = '서비스/메뉴 관리';
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
    $g5['title'] = '서비스/메뉴 관리';
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

// 가맹점측 관리자는 자신의 가맹점 서비스만 조회
$where[] = "shop_id = {$shop_id}";

if ($stx) {
    switch ($sfl) {
        case 'service_name':
            $where[] = "service_name LIKE '%".addslashes($stx)."%'";
            break;
        case 'description':
            $where[] = "description LIKE '%".addslashes($stx)."%'";
            break;
    }
    $qstr .= '&sfl='.urlencode($sfl).'&stx='.urlencode($stx);
}

$sql_common = " FROM shop_services ";

if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "service_id";
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

// 서비스 이미지 썸네일 크기
$svci_wd = 110;
$svci_ht = 80;

$status_arr = array(
    'active' => '정상',
    'inactive' => '비활성'
);

$g5['title'] = '서비스/메뉴 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/store_service_list.js.php');
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
    <option value="service_name"<?php echo get_selected($sfl, "service_name", true)?>>서비스명</option>
    <option value="description"<?php echo get_selected($sfl, "description")?>>설명</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?=$stx?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fstorelist" method="post" action="./store_service_list_update.php" autocomplete="off">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="shop_id" value="<?=$shop_id?>">

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed tbl_sticky_100">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr class="success">
                <th scope="col">
                    <label for="chkall" class="sound_only">서비스 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col" class="td_left">번호</th>
                <th scope="col" class="td_center" style="width:<?=$svci_wd?>px">이미지</th>
                <th scope="col" class="td_left">서비스명</th>
                <th scope="col" class="td_left">설명</th>
                <th scope="col" class="td_right">가격</th>
                <th scope="col" class="td_center">소요시간(분)</th>
                <th scope="col" class="td_center">상태</th>
                <th scope="col" class="td_center">예약링크</th>
                <th scope="col" class="td_center">옵션구분</th>
                <th scope="col" class="td_center">대표서비스</th>
                <th scope="col" class="td_center">시그니처</th>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_count == 0) {
                echo '<tr><td colspan="13" class="empty_table">자료가 없습니다.</td></tr>';
            } else {
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $num = $total_count - ($page - 1) * $rows - $i;
                    $s_mod = '<a href="./store_service_form.php?'.$qstr.'&amp;w=u&amp;service_id='.$row['service_id'].'">수정</a>';

                    // 해당 서비스의 이미지중에 fle_sort순으로 1개만 가져오는 쿼리
                    $fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'shop_services'
                                    AND fle_type = 'svci'
                                    AND fle_dir = 'shop/service_img'
                                    AND fle_db_idx = '{$row['service_id']}'
                                ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
                    $fres = sql_fetch_pg($fsql);
                    // 이미지파일이 존재하면 썸네일 경로 생성
                    $row['thumb_tag'] = '';
                    if(!empty($fres['fle_path'])){
                        $row['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$svci_wd.':'.$svci_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
                        $row['thumb_tag'] = '<img src="'.$row['thumb_url'].'" alt="'.get_text($row['service_name']).'" width="'.$svci_wd.'" class="inline-block" height="'.$svci_ht.'" style="border:1px solid #ddd;width:'.$svci_wd.'px;height:'.$svci_ht.'px;">';
                    }
                    else {
                        $row['thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$svci_wd.'" class="inline-block" height="'.$svci_ht.'" style="border:1px solid #ddd;width:'.$svci_wd.'px;height:'.$svci_ht.'px;">';
                    }

                    $bg = 'bg'.($i%2);
            ?>
            <tr class="<?=$bg?>">
                <td class="td_chk">
                    <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['service_name'])?></label>
                    <input type="checkbox" name="chk[]" value="<?=$row['service_id']?>" id="chk_<?=$i?>">
                </td>
                <td class="td_num"><?=$num?></td>
                <td class="td_center"><?=$row['thumb_tag']?></td>
                <td class="td_left"><?=get_text($row['service_name'])?></td>
                <td class="td_left"><?php 
                    $description = trim($row['description'] ?? '');
                    if (empty($description)) {
                        echo '<span class="text-gray-400">- 설명이 없습니다.</span>';
                    } else {
                        echo get_text(cut_str($description, 50));
                    }
                ?></td>
                <td class="td_right"><?=number_format($row['price'] ?? 0)?>원</td>
                <td class="td_center">
                    <input type="hidden" name="service_id[<?=$i?>]" value="<?=$row['service_id']?>">
                    <input type="number" name="service_time[<?=$i?>]" value="<?=$row['service_time']?>" class="frm_input text-center" style="width:70px;" min="0">
                </td>
                <td class="td_center">
                    <select name="status[<?=$i?>]" class="frm_input">
                        <option value="active">정상</option>
                        <option value="inactive">비활성</option>
                    </select>
                    <script>$('select[name="status[<?=$i?>]"]').val('<?=$row['status'] ?? 'active'?>');</script>
                </td>
                <td class="td_center">
                    <select name="link_yn[<?=$i?>]" class="frm_input">
                        <option value="N">::없음::</option>
                        <option value="Y">있음</option>
                    </select>
                    <script>$('select[name="link_yn[<?=$i?>]"]').val('<?=$row['link_yn'] ?? 'N'?>');</script>
                </td>
                <td class="td_center">
                    <select name="option_yn[<?=$i?>]" class="frm_input">
                        <option value="N">::옵션아님::</option>
                        <option value="Y">옵션</option>
                    </select>
                    <script>$('select[name="option_yn[<?=$i?>]"]').val('<?=$row['option_yn'] ?? 'N'?>');</script>
                </td>
                <td class="td_center">
                    <select name="main_yn[<?=$i?>]" class="frm_input">
                        <option value="N">::대표아님::</option>
                        <option value="Y">대표</option>
                    </select>
                    <script>$('select[name="main_yn[<?=$i?>]"]').val('<?=$row['main_yn'] ?? 'N'?>');</script>
                </td>
                <td class="td_center">
                    <select name="signature_yn[<?=$i?>]" class="frm_input signature_select" data-service-id="<?=$row['service_id']?>">
                        <option value="N">::시그니처아님::</option>
                        <option value="Y">시그니처</option>
                    </select>
                    <script>$('select[name="signature_yn[<?=$i?>]"]').val('<?=$row['signature_yn'] ?? 'N'?>');</script>
                </td>
                <td class="td_mng"><?=$s_mod?></td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./store_service_form.php?<?=$qstr?>" class="btn btn_01">서비스 추가</a>
</div>

</form>

<?php
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}".($qstr ? "?$qstr&amp;page=" : "?page="));
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

