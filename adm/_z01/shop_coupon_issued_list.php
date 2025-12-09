<?php
$sub_menu = "940200";
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
            $g5['title'] = '쿠폰발급관리';
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
                $g5['title'] = '쿠폰발급관리';
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
    $g5['title'] = '쿠폰발급관리';
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
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'customer_coupon_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // status 필터

// ORDER BY 필드에 테이블 별칭이 없으면 추가
if ($sst && strpos($sst, '.') === false) {
    // 허용된 필드 목록
    $allowed_fields = array('customer_coupon_id', 'coupon_id', 'customer_id', 'status', 'issued_at', 'used_at');
    if (in_array($sst, $allowed_fields)) {
        $sst = 'cc.' . $sst;
    }
}

$where_sql = " WHERE sc.shop_id = {$shop_id} AND cc.coupon_id = sc.coupon_id ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'coupon_code':
            $where_sql .= " AND sc.coupon_code LIKE '%{$stx}%' ";
            break;
        case 'coupon_name':
            $where_sql .= " AND sc.coupon_name LIKE '%{$stx}%' ";
            break;
        case 'user_id':
            $where_sql .= " AND c.user_id LIKE '%{$stx}%' ";
            break;
        case 'customer_name':
            $where_sql .= " AND c.name LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $where_sql .= " AND cc.status = '{$sfl2}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt 
               FROM customer_coupons AS cc
               INNER JOIN shop_coupons AS sc ON cc.coupon_id = sc.coupon_id
               LEFT JOIN customers AS c ON cc.customer_id = c.customer_id
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT cc.*, 
                sc.coupon_code, 
                sc.coupon_name, 
                sc.discount_type, 
                sc.discount_value,
                c.user_id, 
                c.name as customer_name
         FROM customer_coupons AS cc
         INNER JOIN shop_coupons AS sc ON cc.coupon_id = sc.coupon_id
         LEFT JOIN customers AS c ON cc.customer_id = c.customer_id
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '쿠폰발급관리';
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
    <label for="sfl2">상태</label>
    <select name="sfl2" id="sfl2" class="frm_input">
        <option value="">전체</option>
        <option value="ISSUED"<?php echo $sfl2 == 'ISSUED' ? ' selected' : '' ?>>발급됨</option>
        <option value="USED"<?php echo $sfl2 == 'USED' ? ' selected' : '' ?>>사용됨</option>
        <option value="EXPIRED"<?php echo $sfl2 == 'EXPIRED' ? ' selected' : '' ?>>만료됨</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="coupon_code"<?php echo $sfl == 'coupon_code' ? ' selected' : '' ?>>쿠폰코드</option>
        <option value="coupon_name"<?php echo $sfl == 'coupon_name' ? ' selected' : '' ?>>쿠폰명</option>
        <option value="user_id"<?php echo $sfl == 'user_id' ? ' selected' : '' ?>>회원ID</option>
        <option value="customer_name"<?php echo $sfl == 'customer_name' ? ' selected' : '' ?>>회원명</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        고객에게 발급된 쿠폰을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" method="post" onsubmit="return flist_submit(this);">
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
        <col style="width: 270px;">
        <col style="width: 200px;">
        <col style="width: 120px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 120px;">
        <col style="width: 80px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">쿠폰코드</th>
        <th scope="col">쿠폰명</th>
        <th scope="col">회원정보</th>
        <th scope="col">발급일시</th>
        <th scope="col">사용일시</th>
        <th scope="col">상태</th>
        <th scope="col">할인정보</th>
        <th scope="col">정렬</th>
        <!-- <th scope="col">관리</th> -->
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $customer_coupon_id = $row['customer_coupon_id'];
            $coupon_id = $row['coupon_id'];
            $coupon_code = $row['coupon_code'];
            $coupon_name = $row['coupon_name'];
            $customer_id = $row['customer_id'];
            $user_id = $row['user_id'];
            $customer_name = $row['customer_name'];
            $status = $row['status'];
            $issued_at = $row['issued_at'];
            $used_at = $row['used_at'];
            $discount_type = $row['discount_type'];
            $discount_value = $row['discount_value'];
            
            // 할인 정보 표시
            $discount_text = '';
            if ($discount_type == 'PERCENT') {
                $discount_text = $discount_value . '%';
            } else if ($discount_type == 'AMOUNT') {
                $discount_text = number_format($discount_value) . '원';
            }
            
            $status_text = '';
            $status_class = '';
            switch ($status) {
                case 'ISSUED':
                    $status_text = '<span style="color:green;">발급됨</span>';
                    break;
                case 'USED':
                    $status_text = '<span style="color:blue;">사용됨</span>';
                    break;
                case 'EXPIRED':
                    $status_text = '<span style="color:red;">만료됨</span>';
                    break;
                default:
                    $status_text = htmlspecialchars($status);
            }
            
            $issued_at_text = $issued_at ? date('Y-m-d H:i', strtotime($issued_at)) : '-';
            $used_at_text = $used_at ? date('Y-m-d H:i', strtotime($used_at)) : '-';
            $customer_info = ($user_id ? htmlspecialchars($user_id) : '-') . '<br><small>' . ($customer_name ? htmlspecialchars($customer_name) : '-') . '</small>';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $customer_coupon_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_left"><?php echo htmlspecialchars($coupon_code) ?></td>
        <td class="td_left"><?php echo htmlspecialchars($coupon_name) ?></td>
        <td class="td_left"><?php echo $customer_info ?></td>
        <td class="td_left"><?php echo $issued_at_text ?></td>
        <td class="td_left"><?php echo $used_at_text ?></td>
        <td class="td_left"><?php echo $status_text ?></td>
        <td class="td_left"><?php echo $discount_text ?></td>
        <td class="td_num"><?php echo $num ?></td>
        <!-- <td class="td_mng">
            <a href="javascript:void(0);" onclick="editCoupon(<?php ;//echo $customer_coupon_id; ?>);" class="btn btn_03">수정</a>
        </td> -->
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="10" class="td_empty">발급된 쿠폰이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <button type="button" onclick="addCoupon();" class="btn btn_01">쿠폰발급</button>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_coupon_issued_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<style>
#couponModal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}
#couponModal .modal_wrap {
    display: table;
    width: 100%;
    height: 100%;
}
#couponModal .modal_content {
    position: relative;
    display: table-cell;
    width: 100%;
    height: 100%;
    vertical-align: middle;
    text-align: center;
    padding: 0 20px;
}
#couponModal .modal_bg {
    position: absolute;
    z-index: 0;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    cursor: pointer;
}
#couponModal .modal_box {
    position: relative;
    background: #fff;
    width: 100%;
    text-align: left;
    max-width: 600px;
    display: inline-block;
    padding: 20px;
    border-radius: 5px;
    z-index: 1;
    max-height: 90vh;
    overflow-y: auto;
}
#couponModal .modal_box h2 {
    font-size: 1.3em;
    padding: 0 0 15px 0;
    margin: 0 0 15px 0;
    border-bottom: 1px solid #ddd;
}
#couponModal .btn_confirm {
    text-align: center;
    padding: 15px 0 0 0;
    margin-top: 15px;
    border-top: 1px solid #ddd;
}
#couponModal .btn_confirm button {
    margin: 0 5px;
}
#couponModal .user_id_check {
    margin-top: 5px;
    font-size: 0.9em;
}
#couponModal .user_id_check.valid {
    color: green;
}
#couponModal .user_id_check.invalid {
    color: red;
}
</style>

<!-- 쿠폰발급/수정 모달 -->
<div id="couponModal">
    <div class="modal_wrap">
        <div class="modal_content">
            <div class="modal_bg" onclick="closeModal();"></div>
            <div class="modal_box">
                <h2 id="modalTitle">쿠폰발급</h2>
                <form name="frmCoupon" id="frmCoupon">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="shop_id" id="modal_shop_id" value="<?php echo $shop_id; ?>">
                    <input type="hidden" name="customer_coupon_id" id="modal_customer_coupon_id" value="">
                    
                    <div class="tbl_frm01 tbl_wrap">
                        <table>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody id="modal_tbody">
                            <!-- 신규 발급 시 -->
                            <tr id="tr_coupon_select">
                                <th scope="row"><label for="modal_coupon_id">쿠폰 선택 <strong class="sound_only">필수</strong></label></th>
                                <td>
                                    <select name="coupon_id" id="modal_coupon_id" class="frm_input required" required>
                                        <option value="">쿠폰을 선택하세요</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="tr_user_id">
                                <th scope="row"><label for="modal_user_id">회원ID <strong class="sound_only">필수</strong></label></th>
                                <td>
                                    <input type="text" name="user_id" id="modal_user_id" class="frm_input required" required>
                                    <div id="user_id_check" class="user_id_check"></div>
                                </td>
                            </tr>
                            <!-- 수정 시 -->
                            <tr id="tr_coupon_info" style="display:none;">
                                <th scope="row">쿠폰정보</th>
                                <td id="modal_coupon_info"></td>
                            </tr>
                            <tr id="tr_customer_info" style="display:none;">
                                <th scope="row">회원정보</th>
                                <td id="modal_customer_info"></td>
                            </tr>
                            <tr id="tr_issued_at" style="display:none;">
                                <th scope="row">발급일시</th>
                                <td id="modal_issued_at"></td>
                            </tr>
                            <tr id="tr_status">
                                <th scope="row"><label for="modal_status">상태 <strong class="sound_only">필수</strong></label></th>
                                <td>
                                    <select name="status" id="modal_status" class="frm_input required" required>
                                        <option value="ISSUED">발급됨</option>
                                        <option value="USED">사용됨</option>
                                        <option value="EXPIRED">만료됨</option>
                                    </select>
                                    <small style="color: #666; display: block; margin-top: 5px;">
                                        <span id="status_help_text">수정 시 조건이 부합하면 'USED' 상태를 'ISSUED'로만 변경할 수 있습니다.</span>
                                    </small>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                    
                    <div class="btn_confirm">
                        <button type="button" onclick="saveCoupon();" class="btn_submit btn">확인</button>
                        <button type="button" onclick="closeModal();" class="btn_cancel btn btn_02">취소</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once('./js/shop_coupon_issued_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
