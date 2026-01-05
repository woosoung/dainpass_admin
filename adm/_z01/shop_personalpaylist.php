<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0 && $page <= 10000) ? $page : 1; // 최대 페이지 제한
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건 - 화이트리스트 방식으로 검증 강화
// 개인정보보호법 준수: 닉네임만 검색 가능
$allowed_sst = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'personal_id', 'order_id', 'nickname');
$allowed_sfl2 = array('', 'CHARGE', 'PAID');

$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'personal_id';
$sst = in_array($sst, $allowed_sst) ? $sst : 'personal_id';

$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sod = in_array($sod, $allowed_sod) ? $sod : 'desc';

$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$sfl = in_array($sfl, $allowed_sfl) ? $sfl : '';

$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$stx = substr($stx, 0, 100); // 최대 길이 제한
$stx = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $stx); // SQL 특수문자 이스케이프

$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : '';
$sfl2 = in_array($sfl2, $allowed_sfl2) ? $sfl2 : '';

// ORDER BY 필드에 테이블 별칭 추가
$sst = 'pp.' . $sst;

$where_sql = " WHERE pp.shop_id = " . (int)$shop_id . " ";

if ($sfl && $stx) {
    // 이미 화이트리스트로 검증된 $sfl과 이스케이프된 $stx 사용
    // 개인정보보호법 준수: 닉네임만 검색 가능
    switch ($sfl) {
        case 'personal_id':
            $where_sql .= " AND pp.personal_id = " . (int)$stx . " ";
            break;
        case 'order_id':
            $where_sql .= " AND pp.order_id ILIKE '%" . $stx . "%' ";
            break;
        case 'nickname':
            $where_sql .= " AND c.nickname ILIKE '%" . $stx . "%' ";
            break;
    }
}

if ($sfl2 !== '') {
    // 이미 화이트리스트로 검증된 값만 사용
    $where_sql .= " AND pp.status = '" . $sfl2 . "' ";
}

// 전체 레코드 수
// customers 테이블 조인하여 nickname 검색 지원
$count_sql = " SELECT COUNT(*) as cnt
               FROM personal_payment AS pp
               LEFT JOIN customers AS c ON pp.user_id = c.user_id
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
// customers 테이블 조인하여 nickname 가져오기 (개인정보보호법 준수)
$sql = " SELECT pp.personal_id,
                pp.order_id,
                pp.shop_id,
                pp.shopdetail_id,
                pp.reason,
                pp.amount,
                pp.status,
                pp.created_at,
                pp.updated_at,
                c.nickname,
                p.payment_id,
                p.payment_key,
                p.payment_method,
                p.amount as payment_amount,
                p.status as payment_status,
                p.paid_at,
                sa.appointment_no
         FROM personal_payment AS pp
         LEFT JOIN customers AS c ON pp.user_id = c.user_id
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

<div class="mb-2 sch_last">
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
        <option value="nickname"<?php echo $sfl == 'nickname' ? ' selected' : '' ?>>회원 닉네임</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8') ?>" id="stx" class="frm_input" maxlength="100">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        개인결제 청구 및 결제 내역을 관리합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
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
        <th scope="col">예약번호</th>
        <th scope="col"><?php echo subject_sort_link('order_id', $qstr) ?>주문번호</a></th>
        <th scope="col">회원 닉네임</th>
        <th scope="col">청구사유</th>
        <th scope="col"><?php echo subject_sort_link('amount', $qstr) ?>청구금액</a></th>
        <th scope="col"><?php echo subject_sort_link('status', $qstr) ?>상태</a></th>
        <th scope="col">결제정보</th>
        <th scope="col">결제일시</th>
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
            $nickname = $row['nickname'];
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

            // 개인정보보호법 준수: 닉네임만 표시
            $customer_info = $nickname ? htmlspecialchars($nickname) : '-';
            
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
        <td class="td_left"><?php echo $appointment_no ? htmlspecialchars($appointment_no) : '-' ?></td>
        <td class="td_left"><?php echo htmlspecialchars($order_id) ?></td>
        <td class="td_left"><?php echo $customer_info ?></td>
        <td class="td_left"><?php echo htmlspecialchars($reason) ?></td>
        <td class="td_num"><?php echo number_format($amount) ?>원</td>
        <td class="td_left"><?php echo $status_text ?></td>
        <td class="td_left"><?php echo $payment_info ?></td>
        <td class="td_left"><?php echo $paid_at_text ?></td>
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
        window.open(href, "copywin", "left=100, top=100, width=700, height=600, scrollbars=1");
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

