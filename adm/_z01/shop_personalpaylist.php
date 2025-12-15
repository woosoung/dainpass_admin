<?php
$sub_menu = "950200";
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
            $g5['title'] = '개인결제관리';
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
                $g5['title'] = '개인결제관리';
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
    $g5['title'] = '개인결제관리';
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
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'personal_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // status 필터

// ORDER BY 필드에 테이블 별칭이 없으면 추가
if ($sst && strpos($sst, '.') === false) {
    // 허용된 필드 목록
    $allowed_fields = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
    if (in_array($sst, $allowed_fields)) {
        $sst = 'pp.' . $sst;
    }
}

$where_sql = " WHERE pp.shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'personal_id':
            $where_sql .= " AND pp.personal_id = " . (int)$stx . " ";
            break;
        case 'order_id':
            $where_sql .= " AND pp.order_id LIKE '%{$stx}%' ";
            break;
        case 'user_id':
            $where_sql .= " AND pp.user_id LIKE '%{$stx}%' ";
            break;
        case 'name':
            $where_sql .= " AND pp.name LIKE '%{$stx}%' ";
            break;
        case 'phone':
            $where_sql .= " AND pp.phone LIKE '%{$stx}%' ";
            break;
        case 'email':
            $where_sql .= " AND pp.email LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $where_sql .= " AND pp.status = '{$sfl2}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt 
               FROM personal_payment AS pp
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT pp.*, 
                p.payment_id,
                p.payment_key,
                p.payment_method,
                p.amount as payment_amount,
                p.status as payment_status,
                p.paid_at,
                sa.appointment_no
         FROM personal_payment AS pp
         LEFT JOIN payments AS p ON pp.personal_id = p.personal_id AND p.pay_flag = 'PERSONAL'
         LEFT JOIN appointment_shop_detail AS asd ON pp.shopdetail_id = asd.shopdetail_id
         LEFT JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '개인결제관리';
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
        <option value="CHARGE"<?php echo $sfl2 == 'CHARGE' ? ' selected' : '' ?>>청구</option>
        <option value="PAID"<?php echo $sfl2 == 'PAID' ? ' selected' : '' ?>>결제완료</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="personal_id"<?php echo $sfl == 'personal_id' ? ' selected' : '' ?>>청구ID</option>
        <option value="order_id"<?php echo $sfl == 'order_id' ? ' selected' : '' ?>>주문번호</option>
        <option value="user_id"<?php echo $sfl == 'user_id' ? ' selected' : '' ?>>회원ID</option>
        <option value="name"<?php echo $sfl == 'name' ? ' selected' : '' ?>>이름</option>
        <option value="phone"<?php echo $sfl == 'phone' ? ' selected' : '' ?>>휴대폰</option>
        <option value="email"<?php echo $sfl == 'email' ? ' selected' : '' ?>>이메일</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        개인결제 청구 및 결제 내역을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" method="post" onsubmit="return flist_submit(this);" action="./shop_personalpaylistdelete.php">
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
        <col style="width: 100px;">
        <col style="width: 150px;">
        <col style="width: 120px;">
        <col style="width: 150px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 120px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 80px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('personal_id', $qstr) ?>청구ID</a></th>
        <th scope="col"><?php echo subject_sort_link('order_id', $qstr) ?>주문번호</a></th>
        <th scope="col">회원정보</th>
        <th scope="col">청구사유</th>
        <th scope="col"><?php echo subject_sort_link('amount', $qstr) ?>청구금액</a></th>
        <th scope="col"><?php echo subject_sort_link('status', $qstr) ?>상태</a></th>
        <th scope="col">결제정보</th>
        <th scope="col">결제일시</th>
        <th scope="col">예약번호</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $personal_id = $row['personal_id'];
            $order_id = $row['order_id'];
            $user_id = $row['user_id'];
            $name = $row['name'];
            $phone = $row['phone'];
            $email = $row['email'];
            $reason = $row['reason'];
            $amount = $row['amount'];
            $status = $row['status'];
            $created_at = $row['created_at'];
            $payment_id = $row['payment_id'];
            $payment_key = $row['payment_key'];
            $payment_method = $row['payment_method'];
            $payment_amount = $row['payment_amount'];
            $payment_status = $row['payment_status'];
            $paid_at = $row['paid_at'];
            $appointment_no = $row['appointment_no'];
            
            $status_text = '';
            $status_class = '';
            switch ($status) {
                case 'CHARGE':
                    $status_text = '<span style="color:orange;">청구</span>';
                    break;
                case 'PAID':
                    $status_text = '<span style="color:green;">결제완료</span>';
                    break;
                default:
                    $status_text = htmlspecialchars($status);
            }
            
            $created_at_text = $created_at ? date('Y-m-d H:i', strtotime($created_at)) : '-';
            $paid_at_text = $paid_at ? date('Y-m-d H:i', strtotime($paid_at)) : '-';
            
            $customer_info = '';
            if ($user_id) {
                $customer_info .= htmlspecialchars($user_id);
            }
            if ($name) {
                $customer_info .= ($customer_info ? '<br>' : '') . '<small>' . htmlspecialchars($name) . '</small>';
            }
            if (!$customer_info) {
                $customer_info = '-';
            }
            
            $payment_info = '';
            if ($payment_id) {
                $payment_info = $payment_method ? htmlspecialchars($payment_method) : '-';
                if ($payment_amount) {
                    $payment_info .= '<br><small>' . number_format($payment_amount) . '원</small>';
                }
            } else {
                $payment_info = '-';
            }
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $personal_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $personal_id ?></td>
        <td class="td_left"><?php echo htmlspecialchars($order_id) ?></td>
        <td class="td_left"><?php echo $customer_info ?></td>
        <td class="td_left"><?php echo htmlspecialchars($reason) ?></td>
        <td class="td_num"><?php echo number_format($amount) ?>원</td>
        <td class="td_left"><?php echo $status_text ?></td>
        <td class="td_left"><?php echo $payment_info ?></td>
        <td class="td_left"><?php echo $paid_at_text ?></td>
        <td class="td_left"><?php echo $appointment_no ? htmlspecialchars($appointment_no) : '-' ?></td>
        <td class="td_mng">
            <a href="./shop_personalpayform.php?w=u&amp;personal_id=<?php echo $personal_id; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03">수정</a>
            <a href="./shop_personalpaycopy.php?personal_id=<?php echo $personal_id; ?>" class="personalpaycopy btn btn_02">복사</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="11" class="td_empty">개인결제 내역이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <a href="./shop_personalpayform.php" class="btn btn_01">개인결제 추가</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_personalpaylist.php?'.$qstr.'&page=');
echo $write_pages;
?>

<script>
$(function() {
    $(".personalpaycopy").on("click", function() {
        var href = this.href;
        window.open(href, "copywin", "left=100, top=100, width=600, height=300, scrollbars=0");
        return false;
    });
});

function flist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

function flist_delete_submit()
{
    if (!is_checked("chk[]")) {
        alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
        return false;
    }

    document.pressed = "선택삭제";
    document.flist.act.value = "delete";
    document.flist.submit();
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

