<?php
$sub_menu = "950100";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// w 파라미터 확인 (u: 상세보기)
$w = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

if ($w != 'u' || $appointment_id <= 0) {
    alert('잘못된 접근입니다.', './shop_appointment_list.php');
    exit;
}

// 예약 기본 정보 조회 (BOOKED 상태 제외)
$appointment_sql = " SELECT sa.*, 
                            c.user_id, 
                            c.name as customer_name, 
                            c.phone as customer_phone,
                            c.email as customer_email,
                            p.amount as payment_amount,
                            p.payment_method,
                            p.paid_at,
                            p.payment_key
                     FROM shop_appointments sa
                     LEFT JOIN customers c ON sa.customer_id = c.customer_id
                     LEFT JOIN payments p ON sa.appointment_id = p.appointment_id AND (p.pay_flag IS NULL OR p.pay_flag = 'GENERAL')
                     WHERE sa.appointment_id = {$appointment_id} 
                     AND sa.status != 'BOOKED'
                     AND EXISTS (
                         SELECT 1 FROM appointment_shop_detail 
                         WHERE appointment_id = sa.appointment_id 
                         AND shop_id = {$shop_id}
                         AND status != 'BOOKED'
                     ) ";
$appointment = sql_fetch_pg($appointment_sql);

if (!$appointment || !$appointment['appointment_id']) {
    alert('예약 정보를 찾을 수 없습니다.', './shop_appointment_list.php');
    exit;
}

// 가맹점별 예약 상세 조회 (해당 shop_id의 예약만, BOOKED 상태 제외)
$shop_details_sql = " SELECT asd.*, 
                             s.shop_name,
                             s.name as shop_name_alt
                      FROM appointment_shop_detail asd
                      INNER JOIN shop s ON asd.shop_id = s.shop_id
                      WHERE asd.appointment_id = {$appointment_id} 
                      AND asd.shop_id = {$shop_id}
                      AND asd.status != 'BOOKED'
                      ORDER BY asd.appointment_datetime ";
$shop_details_result = sql_query_pg($shop_details_sql);
$shop_details = array();
if ($shop_details_result && is_object($shop_details_result) && isset($shop_details_result->result)) {
    while ($row = sql_fetch_array_pg($shop_details_result->result)) {
        $shop_details[] = $row;
    }
}

if (empty($shop_details)) {
    alert('해당 가맹점의 예약 상세 정보를 찾을 수 없습니다.', './shop_appointment_list.php');
    exit;
}

// 각 shopdetail_id별 서비스 상세 조회
$appointment_details = array();
$total_service_quantity = 0; // 해당 가맹점의 모든 서비스 수량 합계
foreach ($shop_details as $shop_detail) {
    $shopdetail_id = $shop_detail['shopdetail_id'];
    $details_sql = " SELECT sad.*, 
                            ss.service_name,
                            ss.price as service_price
                     FROM shop_appointment_details sad
                     INNER JOIN shop_services ss ON sad.service_id = ss.service_id
                     WHERE sad.shopdetail_id = {$shopdetail_id}
                     ORDER BY sad.detail_id ";
    $details_result = sql_query_pg($details_sql);
    $details_list = array();
    if ($details_result && is_object($details_result) && isset($details_result->result)) {
        while ($row = sql_fetch_array_pg($details_result->result)) {
            $details_list[] = $row;
            $total_service_quantity += (int)$row['quantity'];
        }
    }
    $appointment_details[$shopdetail_id] = $details_list;
}

// 취소 정보 조회
$cancel_sql = " SELECT psc.*, 
                       SUM(pcd.cancel_amount) as total_cancel_detail_amount
                FROM payments_shop_cancel psc
                LEFT JOIN payments_cancel_detail pcd ON psc.shop_cancel_id = pcd.shop_cancel_id
                WHERE psc.appointment_id = {$appointment_id}
                GROUP BY psc.shop_cancel_id
                ORDER BY psc.created_at DESC ";
$cancel_result = sql_query_pg($cancel_sql);
$cancel_list = array();
if ($cancel_result && is_object($cancel_result) && isset($cancel_result->result)) {
    while ($row = sql_fetch_array_pg($cancel_result->result)) {
        $cancel_list[] = $row;
    }
}

// 취소 가능 여부 확인 (cancellation_period 체크)
$cancellation_period = isset($shop_info['cancellation_period']) ? (int)$shop_info['cancellation_period'] : 1;
$can_cancel = false;
if (!empty($shop_details)) {
    $first_datetime = $shop_details[0]['appointment_datetime'];
    $cancel_deadline = date('Y-m-d H:i:s', strtotime($first_datetime . ' -' . $cancellation_period . ' hours'));
    $can_cancel = (strtotime('now') < strtotime($cancel_deadline));
}

// qstr 생성
$qstr = '';
if (isset($_GET['page']) && $_GET['page']) {
    $qstr .= '&page=' . (int)$_GET['page'];
}
if (isset($_GET['sst']) && $_GET['sst']) {
    $qstr .= '&sst=' . urlencode($_GET['sst']);
}
if (isset($_GET['sod']) && $_GET['sod']) {
    $qstr .= '&sod=' . urlencode($_GET['sod']);
}
if (isset($_GET['sfl']) && $_GET['sfl']) {
    $qstr .= '&sfl=' . urlencode($_GET['sfl']);
}
if (isset($_GET['stx']) && $_GET['stx']) {
    $qstr .= '&stx=' . urlencode($_GET['stx']);
}
if (isset($_GET['sfl2']) && $_GET['sfl2']) {
    $qstr .= '&sfl2=' . urlencode($_GET['sfl2']);
}
if (isset($_GET['fr_date']) && $_GET['fr_date']) {
    $qstr .= '&fr_date=' . urlencode($_GET['fr_date']);
}
if (isset($_GET['to_date']) && $_GET['to_date']) {
    $qstr .= '&to_date=' . urlencode($_GET['to_date']);
}

$g5['title'] = '예약(주문) 상세';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_desc01 local_desc">
    <p>예약(주문) 상세 정보를 확인합니다.</p>
    <p><strong>가맹점: <?php echo get_text($shop_display_name); ?></strong></p>
    <?php if (!$can_cancel && $appointment['status'] == 'COMPLETED') { ?>
    <p style="color:red;"><strong>취소 가능 시간이 지났습니다. (취소 가능 시간: 예약 시간 <b class="text-blue-500"><?=$cancellation_period?>시간</b> 전까지)</strong></p>
    <?php } ?>
</div>

<!-- 예약 기본 정보 -->
<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>예약 기본 정보</caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">예약번호</th>
        <td><?php echo htmlspecialchars($appointment['appointment_no']) ?></td>
        <th scope="row">상태</th>
        <td>
            <?php
            // 상태 판단: CANCELLED이거나, COMPLETED이지만 모든 서비스 수량이 0이면 취소됨으로 표시
            $display_status = $appointment['status'];
            if ($appointment['status'] == 'COMPLETED' && $total_service_quantity == 0) {
                $display_status = 'CANCELLED';
            }
            
            $status_text = '';
            // BOOKED 상태는 이미 WHERE 조건에서 제외되므로 여기서는 처리하지 않음
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
            echo $status_text;
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">고객정보</th>
        <td>
            <?php
            if ($appointment['customer_id']) {
                echo '회원ID: ' . htmlspecialchars($appointment['user_id'] ? $appointment['user_id'] : 'ID: ' . $appointment['customer_id']) . '<br>';
                echo '이름: ' . htmlspecialchars($appointment['customer_name'] ? $appointment['customer_name'] : '-') . '<br>';
                echo '전화: ' . htmlspecialchars($appointment['customer_phone'] ? $appointment['customer_phone'] : '-') . '<br>';
                echo '이메일: ' . htmlspecialchars($appointment['customer_email'] ? $appointment['customer_email'] : '-');
            } else if ($appointment['guest_id']) {
                echo '비회원 (Guest ID: ' . htmlspecialchars($appointment['guest_id']) . ')';
            } else {
                echo '-';
            }
            ?>
        </td>
        <th scope="row">결제정보</th>
        <td>
            <?php
            if ($appointment['payment_amount']) {
                echo '결제금액: ' . number_format($appointment['payment_amount']) . '원<br>';
                echo '결제수단: ' . htmlspecialchars($appointment['payment_method'] ? $appointment['payment_method'] : '-') . '<br>';
                echo '결제일시: ' . ($appointment['paid_at'] ? date('Y-m-d H:i:s', strtotime($appointment['paid_at'])) : '-');
            } else {
                echo '미결제';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">생성일시</th>
        <td><?php echo $appointment['created_at'] ? date('Y-m-d H:i:s', strtotime($appointment['created_at'])) : '-' ?></td>
        <th scope="row">수정일시</th>
        <td><?php echo isset($appointment['updated_at']) && $appointment['updated_at'] ? date('Y-m-d H:i:s', strtotime($appointment['updated_at'])) : '-' ?></td>
    </tr>
    </tbody>
    </table>
</div>

<!-- 가맹점별 예약 상세 -->
<?php foreach ($shop_details as $shop_detail) { 
    $shopdetail_id = $shop_detail['shopdetail_id'];
    $details = isset($appointment_details[$shopdetail_id]) ? $appointment_details[$shopdetail_id] : array();
    $shop_name = $shop_detail['shop_name'] ? $shop_detail['shop_name'] : ($shop_detail['shop_name_alt'] ? $shop_detail['shop_name_alt'] : 'ID: ' . $shop_detail['shop_id']);
    
    // 해당 shopdetail_id의 모든 서비스 수량 합계 계산
    $shopdetail_total_quantity = 0;
    foreach ($details as $detail) {
        $shopdetail_total_quantity += (int)$detail['quantity'];
    }
?>
<div class="tbl_frm01 tbl_wrap" style="margin-top:20px;">
    <table>
    <caption>가맹점: <?php echo htmlspecialchars($shop_name) ?></caption>
    <colgroup>
        <col style="width:15%;">
        <col style="width:35%;">
        <col style="width:15%;">
        <col style="width:35%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">예약일시</th>
        <td><?php echo $shop_detail['appointment_datetime'] ? date('Y-m-d H:i:s', strtotime($shop_detail['appointment_datetime'])) : '-' ?></td>
        <th scope="row">인원수</th>
        <td><?php echo $shop_detail['count'] ?>명</td>
    </tr>
    <tr>
        <th scope="row">상태</th>
        <td>
            <?php
            // 상태 판단: CANCELLED이거나, COMPLETED이지만 해당 shopdetail_id의 모든 서비스 수량이 0이면 취소됨으로 표시
            $detail_display_status = $shop_detail['status'];
            if ($shop_detail['status'] == 'COMPLETED' && $shopdetail_total_quantity == 0) {
                $detail_display_status = 'CANCELLED';
            }
            
            $detail_status_text = '';
            // BOOKED 상태는 이미 WHERE 조건에서 제외되므로 여기서는 처리하지 않음
            switch ($detail_display_status) {
                case 'COMPLETED':
                    $detail_status_text = '<span style="color:green;">결제완료</span>';
                    break;
                case 'CANCELLED':
                    $detail_status_text = '<span style="color:red;">취소됨</span>';
                    break;
                default:
                    $detail_status_text = htmlspecialchars($detail_display_status);
            }
            echo $detail_status_text;
            ?>
        </td>
        <th scope="row">잔액</th>
        <td><?php echo number_format($shop_detail['balance_amount']) ?>원</td>
    </tr>
    <tr>
        <th scope="row">방문자명</th>
        <td><?php echo htmlspecialchars($shop_detail['visitor'] ? $shop_detail['visitor'] : '-') ?></td>
        <th scope="row">요구사항</th>
        <td><?php echo htmlspecialchars($shop_detail['requirement'] ? $shop_detail['requirement'] : '-') ?></td>
    </tr>
    <?php if ($shop_detail['coupon_status'] == 'USED') { ?>
    <tr>
        <th scope="row">쿠폰사용</th>
        <td colspan="3">
            사용됨 (할인금액: <?php echo number_format($shop_detail['coupon_amount']) ?>원)
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<!-- 서비스별 예약 상세 -->
<?php if (!empty($details)) { ?>
<div class="tbl_head01 tbl_wrap" style="margin-top:10px;">
    <table>
    <caption>서비스별 예약 상세</caption>
    <colgroup>
        <col style="width:100px;">
        <col style="width:200px;">
        <col style="width:100px;">
        <col style="width:100px;">
        <col style="width:100px;">
        <col style="width:100px;">
        <col style="width:150px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">서비스ID</th>
        <th scope="col">서비스명</th>
        <th scope="col">원본수량</th>
        <th scope="col">현재수량</th>
        <th scope="col">단가</th>
        <th scope="col">금액</th>
        <th scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($details as $detail) {
        $org_quantity = $detail['org_quantity'];
        $quantity = $detail['quantity'];
        $price = $detail['price'];
        $net_amount = $detail['net_amount'];
        $cancel_quantity = $org_quantity - $quantity;
    ?>
    <tr>
        <td class="td_num"><?php echo $detail['service_id'] ?><?php if($is_dev_manager){ echo '(shopdetail_id:'.$detail['shopdetail_id'].', detail_id:'.$detail['detail_id'].')';} ?></td>
        <td class="td_left"><?php echo htmlspecialchars($detail['service_name']) ?></td>
        <td class="td_num"><?php echo $org_quantity ?></td>
        <td class="td_num"><?php echo $quantity ?></td>
        <td class="td_num"><?php echo number_format($price??0) ?>원</td>
        <td class="td_num"><?php echo number_format($net_amount??0) ?>원</td>
        <td class="td_left">
            <?php
            if ($cancel_quantity > 0) {
                echo '<span style="color:orange;">부분취소 (' . $cancel_quantity . '개 취소)</span>';
            } else if ($quantity == 0) {
                echo '<span style="color:red;">전체취소</span>';
            } else {
                echo '<span style="color:green;">정상</span>';
            }
            ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>
<?php } ?>

<?php if ($appointment['status'] == 'COMPLETED' && $can_cancel) { ?>
<div style="margin-top:20px; text-align:right;">
    <button type="button" onclick="openPartialCancelModal(<?php echo $appointment_id; ?>, <?php echo $shopdetail_id; ?>);" class="btn btn_02">부분 취소</button>
</div>
<?php } ?>

<?php } ?>

<!-- 취소 내역 -->
<?php if (!empty($cancel_list)) { ?>
<div class="tbl_head01 tbl_wrap" style="margin-top:20px;">
    <table>
    <caption>취소 내역</caption>
    <colgroup>
        <col style="width:100px;">
        <col style="width:150px;">
        <col style="width:200px;">
        <col style="width:100px;">
        <col style="width:150px;">
        <col style="width:150px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">취소ID</th>
        <th scope="col">취소구분</th>
        <th scope="col">취소사유</th>
        <th scope="col">취소금액</th>
        <th scope="col">승인여부</th>
        <th scope="col">취소일시</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($cancel_list as $cancel) { ?>
    <tr>
        <td class="td_num"><?php echo $cancel['shop_cancel_id'] ?></td>
        <td class="td_left"><?php echo htmlspecialchars($cancel['cancel_flag'] == 'SHOP' ? '가맹점취소' : '서비스취소') ?></td>
        <td class="td_left"><?php echo htmlspecialchars($cancel['cancel_reason'] ? $cancel['cancel_reason'] : '-') ?></td>
        <td class="td_num"><?php echo number_format($cancel['cancel_amount']) ?>원</td>
        <td class="td_left"><?php echo $cancel['approval_yn'] == 'Y' ? '<span style="color:green;">승인</span>' : '<span style="color:orange;">대기</span>' ?></td>
        <td class="td_left"><?php echo $cancel['created_at'] ? date('Y-m-d H:i:s', strtotime($cancel['created_at'])) : '-' ?></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>
<?php } ?>

<div class="btn_fixed_top">
    <a href="./shop_appointment_list.php<?php echo $qstr ? '?' . ltrim($qstr, '&') : ''; ?>" class="btn btn_02">목록</a>
</div>

<!-- 부분 취소 모달 -->
<div id="partialCancelModal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; z-index:1000; background:rgba(0,0,0,0.5);">
    <div style="position:relative; display:table; width:100%; height:100%;">
        <div style="display:table-cell; vertical-align:middle; text-align:center; padding:20px;">
            <div style="position:relative; background:#fff; max-width:800px; width:100%; max-height:90vh; overflow-y:auto; display:inline-block; padding:20px; border-radius:5px; text-align:left;">
                <h2 style="font-size:1.3em; padding:0 0 15px 0; margin:0 0 15px 0; border-bottom:1px solid #ddd;">부분 취소</h2>
                <div id="partialCancelContent">
                    <!-- 동적으로 로드됨 -->
                </div>
                <div style="text-align:center; padding:15px 0 0 0; margin-top:15px; border-top:1px solid #ddd;">
                    <button type="button" onclick="submitPartialCancel();" class="btn_submit btn">확인</button>
                    <button type="button" onclick="closePartialCancelModal();" class="btn_cancel btn btn_02">취소</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once('./js/shop_appointment_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
