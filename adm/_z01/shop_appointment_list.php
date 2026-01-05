<?php
$sub_menu = "950100";
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
$allowed_sst = array('appointment_id', 'appointment_no', 'status', 'created_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'appointment_no', 'nickname');
$allowed_sfl2 = array('', 'COMPLETED', 'CANCELLED');

$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'appointment_id';
$sst = in_array($sst, $allowed_sst) ? $sst : 'appointment_id';

$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sod = in_array($sod, $allowed_sod) ? $sod : 'desc';

$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$sfl = in_array($sfl, $allowed_sfl) ? $sfl : '';

$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$stx = substr($stx, 0, 100); // 최대 길이 제한
$stx = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $stx); // SQL 특수문자 이스케이프

$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : '';
$sfl2 = in_array($sfl2, $allowed_sfl2) ? $sfl2 : '';

$fr_date = isset($_GET['fr_date']) ? clean_xss_tags($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? clean_xss_tags($_GET['to_date']) : '';

// 날짜 형식 검증 (YYYY-MM-DD)
if ($fr_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fr_date)) {
    $fr_date = '';
}
if ($to_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
    $to_date = '';
}

// ORDER BY 필드에 테이블 별칭 추가
$sst = 'sa.' . $sst;

$where_sql = " WHERE asd.shop_id = " . (int)$shop_id . "
               AND sa.status != 'BOOKED'
               AND asd.status != 'BOOKED' ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'appointment_no':
            // 이미 이스케이프된 $stx 사용
            $where_sql .= " AND sa.appointment_no ILIKE '%" . $stx . "%' ";
            break;
        case 'nickname':
            $where_sql .= " AND c.nickname ILIKE '%" . $stx . "%' ";
            break;
    }
}

if ($sfl2 !== '') {
    // 이미 화이트리스트로 검증된 값만 사용
    $where_sql .= " AND sa.status = '" . $sfl2 . "' ";
}

if ($fr_date && $to_date) {
    $where_sql .= " AND asd.appointment_datetime >= '" . $fr_date . " 00:00:00' AND asd.appointment_datetime <= '" . $to_date . " 23:59:59' ";
} else if ($fr_date) {
    $where_sql .= " AND asd.appointment_datetime >= '" . $fr_date . " 00:00:00' ";
} else if ($to_date) {
    $where_sql .= " AND asd.appointment_datetime <= '" . $to_date . " 23:59:59' ";
}

// 전체 레코드 수 (중복 제거를 위해 DISTINCT 사용)
$count_sql = " SELECT COUNT(DISTINCT sa.appointment_id) as cnt 
               FROM shop_appointments AS sa
               INNER JOIN appointment_shop_detail AS asd ON sa.appointment_id = asd.appointment_id
               LEFT JOIN customers AS c ON sa.customer_id = c.customer_id
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회 (각 예약의 첫 번째 가맹점 정보와 결제 정보를 가져옴)
// 결제금액은 해당 shop_id에 관련된 서비스들의 금액 합산만 표시
$sql = " SELECT DISTINCT ON (sa.appointment_id)
                sa.appointment_id,
                sa.appointment_no,
                sa.order_id,
                sa.customer_id,
                sa.guest_id,
                sa.status,
                sa.created_at,
                c.nickname,
                MIN(asd.appointment_datetime) as first_appointment_datetime,
                COALESCE(SUM(asd.balance_amount), 0) as total_payment_amount,
                COALESCE(SUM(DISTINCT psc.cancel_amount), 0) as total_cancel_amount,
                COALESCE(SUM(sad.quantity), 0) as total_service_quantity
         FROM shop_appointments AS sa
         INNER JOIN appointment_shop_detail AS asd ON sa.appointment_id = asd.appointment_id
         LEFT JOIN customers AS c ON sa.customer_id = c.customer_id
         LEFT JOIN payments_shop_cancel AS psc ON sa.appointment_id = psc.appointment_id
         LEFT JOIN shop_appointment_details AS sad ON asd.shopdetail_id = sad.shopdetail_id
         {$where_sql}
         GROUP BY sa.appointment_id, sa.appointment_no, sa.order_id, sa.customer_id, sa.guest_id, sa.status, sa.created_at, c.nickname
         ORDER BY {$sst} {$sod}
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}&fr_date={$fr_date}&to_date={$to_date}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '예약(주문)관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <?php if ($result && is_object($result) && isset($result->result)) { ?>
    <span class="btn_ov01"><span class="ov_txt">조회 </span><span class="ov_num"> <?php echo number_format(min($total_count - $offset, $rows_per_page)) ?>건 </span></span>
    <?php } ?>
</div>

<form name="fsearch" id="fsearch" method="get" class="local_sch01 local_sch" onsubmit="return fsearch_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="mb-2 sch_last">
    <label for="sfl2">상태</label>
    <select name="sfl2" id="sfl2" class="frm_input">
        <option value="">전체</option>
        <option value="COMPLETED"<?php echo $sfl2 == 'COMPLETED' ? ' selected' : '' ?>>결제완료</option>
        <option value="CANCELLED"<?php echo $sfl2 == 'CANCELLED' ? ' selected' : '' ?>>취소됨</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="appointment_no"<?php echo $sfl == 'appointment_no' ? ' selected' : '' ?>>예약번호</option>
        <option value="nickname"<?php echo $sfl == 'nickname' ? ' selected' : '' ?>>닉네임</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8') ?>" id="stx" class="frm_input" maxlength="100">
    
    <label for="fr_date" class="sound_only">예약일시 시작</label>
    <input type="text" name="fr_date" value="<?php echo htmlspecialchars($fr_date, ENT_QUOTES, 'UTF-8') ?>" id="fr_date" class="frm_input" size="10" placeholder="시작일" maxlength="10" readonly>
    <label for="to_date" class="sound_only">예약일시 종료</label>
    <input type="text" name="to_date" value="<?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8') ?>" id="to_date" class="frm_input" size="10" placeholder="종료일" maxlength="10" readonly>
    
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 예약(주문)을 관리합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id, ''); ?>
</div>

<form name="flist" id="flist" action="./shop_appointment_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="fr_date" value="<?php echo $fr_date ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date ?>">
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
    <col style="width: 150px;">
    <col style="width: 150px;">
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
        <th scope="col">예약번호</th>
        <th scope="col">회원 닉네임</th>
        <th scope="col">예약일시</th>
        <th scope="col">결제금액</th>
        <th scope="col">취소금액</th>
        <th scope="col">상태</th>
        <th scope="col">생성일시</th>
        <th scope="col">정렬</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $appointment_id = $row['appointment_id'];
            $appointment_no = $row['appointment_no'];
            $order_id = $row['order_id'];
            $customer_id = $row['customer_id'];
            $guest_id = $row['guest_id'];
            $status = $row['status'];
            $nickname = $row['nickname'];
            $first_appointment_datetime = $row['first_appointment_datetime'];
            $total_payment_amount = $row['total_payment_amount'];
            $total_cancel_amount = $row['total_cancel_amount'];
            $total_service_quantity = isset($row['total_service_quantity']) ? (int)$row['total_service_quantity'] : 0;
            $created_at = $row['created_at'];
            
            // 상태 판단: CANCELLED이거나, COMPLETED이지만 모든 서비스 수량이 0이면 취소됨으로 표시
            // BOOKED 상태는 이미 WHERE 조건에서 제외되므로 여기서는 처리하지 않음
            $display_status = $status;
            if ($status == 'COMPLETED' && $total_service_quantity == 0) {
                $display_status = 'CANCELLED';
            }
            
            $status_text = '';
            $status_class = '';
            switch ($display_status) {
                case 'COMPLETED':
                    $status_text = '<span style="color:green;">결제완료</span>';
                    break;
                case 'CANCELLED':
                    $status_text = '<span style="color:red;">취소됨</span>';
                    break;
                default:
                    $status_text = htmlspecialchars($display_status);
            }
            
            $customer_info = '';
            if ($customer_id) {
                $customer_info = $nickname ? htmlspecialchars($nickname) : '-';
            } else if ($guest_id) {
                $customer_info = '비회원';
            } else {
                $customer_info = '-';
            }
            
            $appointment_datetime_text = $first_appointment_datetime ? date('Y-m-d H:i', strtotime($first_appointment_datetime)) : '-';
            $created_at_text = $created_at ? date('Y-m-d H:i', strtotime($created_at)) : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $appointment_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_left"><?php echo htmlspecialchars($appointment_no) ?></td>
        <td class="td_left"><?php echo $customer_info ?></td>
        <td class="td_left"><?php echo $appointment_datetime_text ?></td>
        <td class="td_num"><?php echo number_format($total_payment_amount) ?>원</td>
        <td class="td_num"><?php echo number_format($total_cancel_amount) ?>원</td>
        <td class="td_left">
            <select name="status[<?php echo $appointment_id ?>]" class="frm_input">
                <option value="COMPLETED"<?php echo $status == 'COMPLETED' ? ' selected' : '' ?>>결제완료</option>
                <option value="CANCELLED"<?php echo $status == 'CANCELLED' ? ' selected' : '' ?>>취소됨</option>
            </select>
        </td>
        <td class="td_left"><?php echo $created_at_text ?></td>
        <td class="td_num"><?php echo $num ?></td>
        <td class="td_mng">
            <a href="./shop_appointment_form.php?w=u&appointment_id=<?php echo $appointment_id; ?>&<?php echo $qstr; ?>" class="btn btn_03">상세</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="10" class="td_empty">등록된 예약이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_status_update_submit();" class="btn btn_02">선택항목 상태변경</button>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_appointment_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<script>
$(function(){
    // 시작일 datepicker
    $("#fr_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-10:c+1",
        maxDate: "+1y",
        onClose: function(selectedDate) {
            if (selectedDate) {
                $("#to_date").datepicker("option", "minDate", selectedDate);
            }
        }
    });

    // 종료일 datepicker
    $("#to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-10:c+1",
        maxDate: "+1y",
        onClose: function(selectedDate) {
            if (selectedDate) {
                $("#fr_date").datepicker("option", "maxDate", selectedDate);
            }
        }
    });
});
</script>

<?php
include_once('./js/shop_appointment_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
