<?php
$sub_menu = "920350";
include_once('./_common.php');

@auth_check($auth[$sub_menu], "r");

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;

if (!$personal_id) {
    alert('결제번호가 없습니다.', './personalpayments_list.php');
}

// 상태 배열 정의
$status_arr = array(
    'CHARGE' => '청구',
    'PAID' => '결제완료',
    'CANCELLED' => '취소완료',
);

// 예약 상태 배열 정의 (예약(주문)관리 참조)
$appointment_status_arr = array(
    'CONFIRMED' => '확정',
    'COMPLETED' => '완료',
    'CANCELLED' => '취소',
    'NO_SHOW' => '노쇼',
    'REFUNDED' => '환불',
);

// 기본 정보 조회
$payment = sql_fetch_pg(" 
    SELECT 
        pp.*,
        s.name as shop_name,
        s.shop_name as shop_display_name,
        c.name as customer_name,
        c.phone as customer_phone,
        c.email as customer_email,
        asd.appointment_datetime,
        asd.status as appointment_status,
        asd.appointment_id
    FROM personal_payment pp
    LEFT JOIN shop s ON pp.shop_id = s.shop_id
    LEFT JOIN customers c ON pp.user_id = c.user_id
    LEFT JOIN appointment_shop_detail asd ON pp.shopdetail_id = asd.shopdetail_id
    WHERE pp.personal_id = '{$personal_id}'
");

if (!$payment['personal_id']) {
    alert('존재하지 않는 결제 정보입니다.', './personalpayments_list.php');
}

// 취소 정보 조회
$cancel_sql = "
    SELECT 
        ppc.*
    FROM personal_payment_cancel ppc
    WHERE ppc.personal_id = '{$personal_id}'
    ORDER BY ppc.created_at DESC
";
$cancel_result = sql_query_pg($cancel_sql);

$cancels = array();
if ($cancel_result && is_object($cancel_result) && isset($cancel_result->result)) {
    while ($cancel = sql_fetch_array_pg($cancel_result->result)) {
        $cancels[] = $cancel;
    }
}

$g5['title'] = '개인결제 상세';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <a href="./personalpayments_list.php" class="ov_listall">목록</a>
    <span class="btn_ov01"><span class="ov_txt">결제번호</span><span class="ov_num"><?=$payment['personal_id']?></span></span>
    <span class="btn_ov01"><span class="ov_txt">주문번호</span><span class="ov_num"><?=$payment['order_id']?></span></span>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
        <caption>결제 기본 정보</caption>
        <colgroup>
            <col style="width:15%;">
            <col style="width:35%;">
            <col style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">결제번호</th>
                <td><?=$payment['personal_id']?></td>
                <th scope="row">주문번호</th>
                <td><?=$payment['order_id']?></td>
            </tr>
            <tr>
                <th scope="row">결제일시</th>
                <td><?=date('Y-m-d H:i:s', strtotime($payment['created_at']))?></td>
                <th scope="row">수정일시</th>
                <td><?=date('Y-m-d H:i:s', strtotime($payment['updated_at']))?></td>
            </tr>
            <tr>
                <th scope="row">상태</th>
                <td><?=$status_arr[$payment['status']] ?? $payment['status']?></td>
                <th scope="row">정산 대상 여부</th>
                <td><?=$payment['is_settlement_target'] ? '예' : '아니오'?></td>
            </tr>
            <tr>
                <th scope="row">결제자명</th>
                <td><?=get_text($payment['name'])?></td>
                <th scope="row">고객ID</th>
                <td><?=get_text($payment['user_id'] ?: '-')?></td>
            </tr>
            <tr>
                <th scope="row">연락처</th>
                <td><?=formatPhoneNumber($payment['phone'] ?? '')?></td>
                <th scope="row">이메일</th>
                <td><?=$payment['email'] ?? ''?></td>
            </tr>
            <?php if (!empty($payment['customer_name'])) { ?>
            <tr>
                <th scope="row">회원 고객명</th>
                <td><?=get_text($payment['customer_name'])?></td>
                <th scope="row">회원 연락처</th>
                <td><?=formatPhoneNumber($payment['customer_phone'] ?? '')?></td>
            </tr>
            <?php } ?>
            <tr>
                <th scope="row">가맹점명</th>
                <td>
                    <?php if (!empty($payment['shop_display_name'])) { ?>
                        <?=get_text($payment['shop_display_name'])?>
                    <?php } elseif (!empty($payment['shop_name'])) { ?>
                        <?=get_text($payment['shop_name'])?>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <th scope="row">가맹점 고유번호</th>
                <td><?=$payment['shop_id']?></td>
            </tr>
            <?php if (!empty($payment['shopdetail_id'])) { ?>
            <tr>
                <th scope="row">예약일시</th>
                <td><?=$payment['appointment_datetime'] ? date('Y-m-d H:i:s', strtotime($payment['appointment_datetime'])) : '-'?></td>
                <th scope="row">예약상태</th>
                <td><?=$appointment_status_arr[$payment['appointment_status']] ?? ($payment['appointment_status'] ?? '-')?></td>
            </tr>
            <?php } ?>
            <tr>
                <th scope="row">결제사유</th>
                <td colspan="3"><?=get_text($payment['reason'])?></td>
            </tr>
            <tr>
                <th scope="row">결제금액</th>
                <td colspan="3">
                    <span style="color:rgb(84, 51, 228); font-weight: bold; font-size: 18px;"><?=number_format($payment['amount'])?>원</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php if (count($cancels) > 0) { ?>
<div class="tbl_frm01 tbl_wrap" style="margin-top: 30px;">
    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #333;">취소 정보</h3>
    
    <?php
    $total_cancel_amount = 0;
    foreach ($cancels as $idx => $cancel) {
        $total_cancel_amount += $cancel['cancel_amount'];
    ?>
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: <?=$cancel['approval_yn'] == 'Y' ? '#fff3cd' : '#f9f9f9'?>;">
        <table style="width: 100%;">
            <caption style="text-align: left; font-weight: bold; margin-bottom: 10px;">취소 정보 #<?=$idx + 1?></caption>
            <colgroup>
                <col style="width:15%;">
                <col style="width:35%;">
                <col style="width:15%;">
                <col style="width:35%;">
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row">취소 번호</th>
                    <td><?=$cancel['personal_payment_cancel_id']?></td>
                    <th scope="row">취소 금액</th>
                    <td>
                        <span style="color: #d9534f; font-weight: bold;"><?=number_format($cancel['cancel_amount'])?>원</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">취소 사유</th>
                    <td colspan="3"><?=get_text($cancel['cancel_reason'] ?? '-')?></td>
                </tr>
                <tr>
                    <th scope="row">승인 여부</th>
                    <td>
                        <?php if ($cancel['approval_yn'] == 'Y') { ?>
                            <span style="color: #5cb85c; font-weight: bold;">승인</span>
                        <?php } else { ?>
                            <span style="color: #f0ad4e; font-weight: bold;">미승인</span>
                        <?php } ?>
                    </td>
                    <th scope="row">승인일시</th>
                    <td><?=$cancel['approval_date'] ? date('Y-m-d H:i:s', strtotime($cancel['approval_date'])) : '-'?></td>
                </tr>
                <tr>
                    <th scope="row">거래키</th>
                    <td><?=get_text($cancel['transaction_key'] ?? '-')?></td>
                    <th scope="row">취소 요청일시</th>
                    <td><?=date('Y-m-d H:i:s', strtotime($cancel['created_at']))?></td>
                </tr>
                <tr>
                    <th scope="row">취소 수정일시</th>
                    <td colspan="3"><?=date('Y-m-d H:i:s', strtotime($cancel['updated_at']))?></td>
                </tr>
                <?php if (!empty($cancel['request'])) { ?>
                <tr>
                    <th scope="row">요청 정보</th>
                    <td colspan="3">
                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;"><?=htmlspecialchars(json_encode(json_decode($cancel['request']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))?></pre>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    
    <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 3px;">
        <p style="margin: 0; font-weight: bold;">
            총 취소 금액: <span style="color: #d9534f; font-size: 16px;"><?=number_format($total_cancel_amount)?>원</span>
            <?php if ($total_cancel_amount >= $payment['amount']) { ?>
                <span style="color: #d9534f;">(전체 취소)</span>
            <?php } elseif ($total_cancel_amount > 0) { ?>
                <span style="color: #f0ad4e;">(부분 취소)</span>
            <?php } ?>
        </p>
        <?php if ($total_cancel_amount < $payment['amount']) { ?>
            <p style="margin: 5px 0 0 0; color: #666;">
                최종 결제 금액: <span style="font-weight: bold;"><?=number_format($payment['amount'] - $total_cancel_amount)?>원</span>
            </p>
        <?php } ?>
    </div>
</div>
<?php } ?>

<div class="btn_fixed_top" style="">
    <a href="./personalpayments_list.php" class="btn btn_02">목록</a>
    <?php if (!empty($payment['appointment_id'])) { ?>
        <a href="./appointment_detail.php?appointment_id=<?=$payment['appointment_id']?>" class="btn btn_01">예약 상세</a>
    <?php } ?>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

