<?php
$sub_menu = "920300"; // 예약(주문)내역 관리 메뉴 코드
include_once('./_common.php');

@auth_check($auth[$sub_menu], "r");

$form_input = '';
$qstr = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$sql_common = " FROM shop_appointments sa
                LEFT JOIN customers c ON sa.customer_id = c.customer_id
                WHERE sa.is_deleted = 'N' 
                  AND sa.status != 'BOOKED' ";

$where = array();

// 검색 조건 추가
$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';
$stx = isset($_GET['stx']) ? trim($_GET['stx']) : '';

if ($stx) {
    $stx = addslashes($stx);
    switch ($sfl) {
        case 'appointment_no':
            $where[] = " sa.appointment_no = '{$stx}' ";
            break;
        case 'order_id':
            $where[] = " sa.order_id = '{$stx}' ";
            break;
        case 'customer_name':
            $where[] = " c.name LIKE '%{$stx}%' ";
            break;
        case 'customer_id':
            $where[] = " sa.customer_id = '{$stx}' ";
            break;
        case 'guest_id':
            $where[] = " sa.guest_id = '{$stx}' ";
            break;
        default:
            $where[] = " (sa.appointment_no::text LIKE '%{$stx}%' OR sa.order_id LIKE '%{$stx}%' OR c.name LIKE '%{$stx}%') ";
            break;
    }
}

// 상태 필터
$ser_status = isset($_GET['ser_status']) ? trim($_GET['ser_status']) : '';
if (!empty($ser_status)) {
    $ser_status = addslashes($ser_status);
    $where[] = " sa.status = '{$ser_status}' ";
}

// 날짜 범위 필터
$ser_date_from = isset($_GET['ser_date_from']) ? trim($_GET['ser_date_from']) : '';
$ser_date_to = isset($_GET['ser_date_to']) ? trim($_GET['ser_date_to']) : '';
if (!empty($ser_date_from)) {
    $ser_date_from = addslashes($ser_date_from);
    $where[] = " DATE(sa.created_at) >= '{$ser_date_from}' ";
}
if (!empty($ser_date_to)) {
    $ser_date_to = addslashes($ser_date_to);
    $where[] = " DATE(sa.created_at) <= '{$ser_date_to}' ";
}

$sql_search = '';
if ($where)
    $sql_search = ' AND '.implode(' AND ', $where);

// 정렬
$sst = isset($_GET['sst']) ? $_GET['sst'] : '';
$sod = isset($_GET['sod']) ? $_GET['sod'] : '';
if (!$sst) {
    $sst = "sa.appointment_id";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

// 페이징
$rows = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT sa.*, 
                c.name as customer_name, 
                c.user_id as customer_user_id,
                c.phone as customer_phone
         {$sql_common}
         {$sql_search}
         {$sql_order}
         LIMIT {$rows} OFFSET {$from_record} ";

$result = sql_query_pg($sql);

// 전체 개수
$sql = " SELECT COUNT(*) AS total {$sql_common} {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

// 상태 배열 정의
// 주의사항 : 'BOOKED'상태값은 시스템상에서 자동으로 삭제되는 항목이므로 데이터 추출에 고려할 필요가 없다.
$status_arr = array(
    'CONFIRMED' => '확정',
    'COMPLETED' => '완료',
    'CANCELLED' => '취소',
    'NO_SHOW' => '노쇼',
    'REFUNDED' => '환불'
);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 11;

$g5['title'] = '예약(주문)내역 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <select name="ser_status" id="ser_status" class="cp_field" title="상태선택">
        <option value="">전체상태</option>
        <?php foreach($status_arr as $key => $val) { ?>
            <option value="<?=$key?>" <?=($ser_status == $key) ? 'selected' : ''?>><?=$val?></option>
        <?php } ?>
    </select>
    
    <label for="ser_date_from" class="sound_only">시작일</label>
    <input type="date" name="ser_date_from" value="<?=$ser_date_from?>" id="ser_date_from" class="frm_input">
    
    <label for="ser_date_to" class="sound_only">종료일</label>
    <input type="date" name="ser_date_to" value="<?=$ser_date_to?>" id="ser_date_to" class="frm_input">
    
    <select name="sfl" id="sfl">
        <option value="appointment_no"<?php echo get_selected($_GET['sfl'], "appointment_no"); ?>>예약번호</option>
        <option value="order_id"<?php echo get_selected($_GET['sfl'], "order_id"); ?>>주문번호</option>
        <option value="customer_name"<?php echo get_selected($_GET['sfl'], "customer_name"); ?>>고객명</option>
        <option value="customer_id"<?php echo get_selected($_GET['sfl'], "customer_id"); ?>>고객ID</option>
        <option value="guest_id"<?php echo get_selected($_GET['sfl'], "guest_id"); ?>>게스트ID</option>
    </select>
    
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed tbl_sticky_100">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr class="success">
                <th scope="col" class="td_left">예약번호</th>
                <th scope="col" class="td_left">주문번호</th>
                <th scope="col" class="td_left">고객명</th>
                <th scope="col" class="td_left">고객ID</th>
                <th scope="col" class="td_center">예약일시</th>
                <th scope="col" class="td_center">상태</th>
                <th scope="col" class="td_right">할인전금액</th>
                <th scope="col" class="td_right">할인금액</th>
                <th scope="col" class="td_right">결제금액</th>
                <th scope="col" class="td_center">등록일</th>
                <th scope="col" class="td_center">상세</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                // 각 예약의 할인전 금액 계산 (서비스별 원래 단가 * 수량의 합계)
                $original_amount_sql = "
                    SELECT SUM(sv.price * sad.quantity) as original_amount
                    FROM appointment_shop_detail asd
                    INNER JOIN shop_appointment_details sad ON asd.shopdetail_id = sad.shopdetail_id
                    INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                    WHERE asd.appointment_id = '{$row['appointment_id']}'
                ";
                $original_amount_row = sql_fetch_pg($original_amount_sql);
                $original_amount = $original_amount_row['original_amount'] ?? 0;
                
                // 각 예약의 총 결제 금액 계산 (balance_amount는 이미 할인 반영된 금액)
                $amount_sql = " SELECT SUM(balance_amount) as total_amount 
                               FROM appointment_shop_detail 
                               WHERE appointment_id = '{$row['appointment_id']}' ";
                $amount_row = sql_fetch_pg($amount_sql);
                $total_amount = $amount_row['total_amount'] ?? 0;
                
                // 할인 금액 계산
                $discount_amount = 0;
                
                // 쿠폰 할인 금액
                $coupon_amount_sql = " SELECT SUM(coupon_amount) as total_coupon_amount 
                                      FROM appointment_shop_detail 
                                      WHERE appointment_id = '{$row['appointment_id']}' 
                                        AND coupon_amount > 0 ";
                $coupon_amount_row = sql_fetch_pg($coupon_amount_sql);
                $coupon_amount = $coupon_amount_row['total_coupon_amount'] ?? 0;
                $discount_amount += $coupon_amount;
                
                // 이벤트 할인 금액
                $event_discount_sql = "
                    SELECT SUM(
                        CASE 
                            WHEN sad.discount_price > 0 THEN sad.discount_price
                            WHEN (sv.price * sad.quantity - sad.net_amount) > 0 THEN (sv.price * sad.quantity - sad.net_amount)
                            ELSE 0
                        END
                    ) as event_discount
                    FROM appointment_shop_detail asd
                    INNER JOIN shop_appointment_details sad ON asd.shopdetail_id = sad.shopdetail_id
                    INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                    WHERE asd.appointment_id = '{$row['appointment_id']}'
                      AND (
                        sad.discount_price > 0 
                        OR (sv.price * sad.quantity - sad.net_amount > 0)
                      )
                ";
                $event_discount_row = sql_fetch_pg($event_discount_sql);
                $event_discount = $event_discount_row['event_discount'] ?? 0;
                $discount_amount += $event_discount;
                
                // 포인트 사용 금액
                $point_usage_sql = "
                    SELECT SUM(pt.amount) as total_point_usage
                    FROM payments p
                    INNER JOIN point_transactions pt ON p.payment_id = pt.payment_id
                    WHERE p.appointment_id = '{$row['appointment_id']}'
                      AND pt.type = '사용'
                      AND pt.amount > 0
                ";
                $point_usage_row = sql_fetch_pg($point_usage_sql);
                $point_usage = $point_usage_row['total_point_usage'] ?? 0;
                $discount_amount += $point_usage;
                
                // 최종 결제 금액 = 할인전 금액 - 할인 금액
                $final_payment_amount = $original_amount - $discount_amount;
                
                $bg = 'bg'.($i%2);
            ?>
            <tr class="<?=$bg?>">
                <td class="td_left"><?=$row['appointment_no']?></td>
                <td class="td_left"><?=get_text($row['order_id'])?></td>
                <td class="td_left"><?=get_text($row['customer_name'] ?: '게스트')?></td>
                <td class="td_left"><?=$row['customer_id'] ? ($row['customer_user_id'] ?: 'ID:'.$row['customer_id']) : ($row['guest_id'] ? '게스트('.$row['guest_id'].')' : '-')?></td>
                <td class="td_center"><?=date('Y-m-d H:i', strtotime($row['created_at']))?></td>
                <td class="td_center"><?=$status_arr[$row['status']] ?? $row['status']?></td>
                <td class="td_right"><?=number_format($original_amount)?>원</td>
                <td class="td_right">
                    <?php if ($discount_amount > 0) { ?>
                        <span style="color: #d9534f;">-<?=number_format($discount_amount)?>원</span>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td class="td_right">
                    <span style="font-weight: bold;"><?=number_format($final_payment_amount)?>원</span>
                </td>
                <td class="td_center"><?=date('Y-m-d', strtotime($row['created_at']))?></td>
                <td class="td_center">
                    <a href="./appointment_detail.php?appointment_id=<?=$row['appointment_id']?>&<?=$qstr?>" class="btn btn_01">상세</a>
                </td>
            </tr>
            <?php
            }
            if ($i == 0)
                echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
        </tbody>
    </table>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

