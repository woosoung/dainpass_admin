<?php
$sub_menu = "940100";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;
$shop_info = null;

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
            $g5['title'] = '쿠폰관리';
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
            $shop_sql = " SELECT shop_id, shop_name, name, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
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
                $shop_info = $shop_row;
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                $g5['title'] = '쿠폰관리';
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
    $g5['title'] = '쿠폰관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'coupon_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // is_active 필터

// ORDER BY 필드에 테이블 별칭이 없으면 추가
if ($sst && strpos($sst, '.') === false) {
    // 허용된 필드 목록
    $allowed_fields = array('coupon_id', 'coupon_code', 'coupon_name', 'discount_type', 'valid_from', 'valid_until', 'is_active', 'created_at');
    if (in_array($sst, $allowed_fields)) {
        $sst = 'c.' . $sst;
    }
}

$where_sql = " WHERE c.shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'coupon_code':
            $where_sql .= " AND c.coupon_code LIKE '%{$stx}%' ";
            break;
        case 'coupon_name':
            $where_sql .= " AND c.coupon_name LIKE '%{$stx}%' ";
            break;
        case 'description':
            $where_sql .= " AND c.description LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $is_active_value = ($sfl2 === 'active' || $sfl2 === '1') ? 'true' : 'false';
    $where_sql .= " AND c.is_active = {$is_active_value} ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM shop_coupons AS c {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT c.* 
         FROM shop_coupons AS c
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '쿠폰관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <?php if ($result && is_object($result) && isset($result->result)) { ?>
    <span class="btn_ov01"><span class="ov_txt">조회 </span><span class="ov_num"> <?php echo number_format(min($total_count - $offset, $rows_per_page)) ?>건 </span></span>
    <?php } ?>
</div>

<form name="fsearch" id="fsearch" method="get" class="local_sch01 local_sch">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="sch_last mb-2">
    <label for="sfl2">활성화여부</label>
    <select name="sfl2" id="sfl2" class="frm_input">
        <option value="">전체</option>
        <option value="active"<?php echo $sfl2 == 'active' ? ' selected' : '' ?>>활성</option>
        <option value="inactive"<?php echo $sfl2 == 'inactive' ? ' selected' : '' ?>>비활성</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="coupon_code"<?php echo $sfl == 'coupon_code' ? ' selected' : '' ?>>쿠폰코드</option>
        <option value="coupon_name"<?php echo $sfl == 'coupon_name' ? ' selected' : '' ?>>쿠폰명</option>
        <option value="description"<?php echo $sfl == 'description' ? ' selected' : '' ?>>설명</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 쿠폰을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" action="./shop_coupons_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 50px;">
        <col style="width: 120px;">
        <col style="width: 150px;">
        <col style="width: 200px;">
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 120px;">
        <col style="width: 120px;">
        <col style="width: 80px;">
        <col style="width: 120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">쿠폰코드</th>
        <th scope="col">쿠폰명</th>
        <th scope="col">할인정보</th>
        <th scope="col">유효기간시작</th>
        <th scope="col">유효기간종료</th>
        <th scope="col">발급현황</th>
        <th scope="col">상태</th>
        <th scope="col">정렬</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $coupon_id = $row['coupon_id'];
            $coupon_code = $row['coupon_code'];
            $coupon_name = $row['coupon_name'];
            $discount_type = $row['discount_type'];
            $discount_value = $row['discount_value'];
            $min_purchase_amt = $row['min_purchase_amt'];
            $max_discount_amt = $row['max_discount_amt'];
            $valid_from = $row['valid_from'];
            $valid_until = $row['valid_until'];
            $total_limit = $row['total_limit'];
            $issued_limit = $row['issued_limit'];
            $total_issued = $row['total_issued'];
            $is_active = isset($row['is_active']) && ($row['is_active'] == 't' || $row['is_active'] === true || $row['is_active'] == '1' || $row['is_active'] === 'true');
            
            // 할인 정보 표시
            $discount_text = '';
            if ($discount_type == 'PERCENT') {
                $discount_text = $discount_value . '%';
                if ($max_discount_amt) {
                    $discount_text .= ' (최대 ' . number_format($max_discount_amt) . '원)';
                }
            } else if ($discount_type == 'AMOUNT') {
                $discount_text = number_format($discount_value) . '원';
            }
            if ($min_purchase_amt) {
                $discount_text .= '<br><small>최소 ' . number_format($min_purchase_amt) . '원 이상</small>';
            }
            
            // 발급 현황
            $issue_text = '';
            if ($total_limit) {
                $issue_text = number_format($total_issued) . ' / ' . number_format($total_limit);
            } else {
                $issue_text = number_format($total_issued) . ' / 무제한';
            }
            $issue_text .= '<br><small>1인당 ' . number_format($issued_limit) . '장</small>';
            
            $is_active_text = $is_active ? '<span style="color:green;">활성</span>' : '<span style="color:red;">비활성</span>';
            $valid_from_text = $valid_from ? $valid_from : '-';
            $valid_until_text = $valid_until ? $valid_until : '무기한';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $coupon_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_left"><?php echo htmlspecialchars($coupon_code) ?></td>
        <td class="td_left"><?php echo htmlspecialchars($coupon_name) ?></td>
        <td class="td_left"><?php echo $discount_text ?></td>
        <td class="td_left"><?php echo $valid_from_text ?></td>
        <td class="td_left"><?php echo $valid_until_text ?></td>
        <td class="td_left"><?php echo $issue_text ?></td>
        <td class="td_left"><?php echo $is_active_text ?></td>
        <td class="td_num"><?php echo $num ?></td>
        <td class="td_mng">
            <a href="./shop_coupons_form.php?w=u&coupon_id=<?php echo $coupon_id; ?>&<?php echo $qstr; ?>" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="10" class="td_empty">등록된 쿠폰이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <a href="./shop_coupons_form.php?w=<?php echo $qstr ? '&' . $qstr : ''; ?>" class="btn btn_01">신규등록</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_coupons_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<?php
include_once('./js/shop_coupons_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
