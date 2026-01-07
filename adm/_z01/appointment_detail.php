<?php
$sub_menu = "920300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], "r");

$appointment_id = isset($_REQUEST['appointment_id']) ? (int)$_REQUEST['appointment_id'] : 0;

if (!$appointment_id) {
    alert('예약번호가 없습니다.', './appointment_list.php');
}

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

// 상태 배열 정의
$status_arr = array(
    'BOOKED' => '예약완료',
    'CONFIRMED' => '확정',
    'COMPLETED' => '완료',
    'CANCELLED' => '취소',
    'NO_SHOW' => '노쇼',
    'REFUNDED' => '환불'
);

// 요일 배열
$weekday_arr = array('일', '월', '화', '수', '목', '금', '토');

// 예약 기본 정보 조회
$appointment = sql_fetch_pg(" 
    SELECT sa.*, 
           c.name as customer_name, 
           c.user_id as customer_user_id,
           c.phone as customer_phone,
           c.email as customer_email
    FROM shop_appointments sa
    LEFT JOIN customers c ON sa.customer_id = c.customer_id
    WHERE sa.appointment_id = '{$appointment_id}' 
      AND sa.is_deleted = 'N' 
");

if (!$appointment['appointment_id']) {
    alert('존재하지 않는 예약입니다.', './appointment_list.php');
}

// 예약 가맹점 상세 정보 조회 (날짜별, 시간대별 정렬)
$shop_details_sql = "
    SELECT asd.*,
           s.shop_id,
           s.name as shop_name,
           s.shop_name as shop_display_name
    FROM appointment_shop_detail asd
    INNER JOIN shop s ON asd.shop_id = s.shop_id
    WHERE asd.appointment_id = '{$appointment_id}'
    ORDER BY asd.appointment_datetime ASC, s.shop_id ASC
";
$shop_details_result = sql_query_pg($shop_details_sql);

// 날짜별로 그룹화 및 총 결제금액 계산
$appointments_by_date = array();
$total_payment_amount = 0; // 모든 가맹점 통합 총 결제금액
$total_coupon_amount = 0; // 모든 가맹점 통합 총 쿠폰 할인 금액
$total_event_discount = 0; // 모든 서비스 통합 총 이벤트 할인 금액
$total_point_usage = 0; // 총 포인트 사용 금액

while ($shop_detail = sql_fetch_array_pg($shop_details_result->result)) {
    $date_key = date('Y-m-d', strtotime($shop_detail['appointment_datetime']));
    if (!isset($appointments_by_date[$date_key])) {
        $appointments_by_date[$date_key] = array();
    }
    $appointments_by_date[$date_key][] = $shop_detail;
    
    // 총 결제금액 계산 (balance_amount는 이미 할인 반영된 금액)
    $balance_amount = $shop_detail['balance_amount'] ?? 0;
    $total_payment_amount += $balance_amount;
    
    // 총 쿠폰 할인 금액 계산
    $coupon_amount = $shop_detail['coupon_amount'] ?? 0;
    $total_coupon_amount += $coupon_amount;
    
    // 해당 가맹점의 이벤트 할인 금액 계산
    // 원래 단가(shop_services.price)를 사용하여 계산
    $event_discount_sql = "
        SELECT SUM(
            CASE 
                WHEN sad.discount_price > 0 THEN sad.discount_price
                WHEN (sv.price * sad.quantity - sad.net_amount) > 0 THEN (sv.price * sad.quantity - sad.net_amount)
                ELSE 0
            END
        ) as event_discount
        FROM shop_appointment_details sad
        INNER JOIN shop_services sv ON sad.service_id = sv.service_id
        WHERE sad.shopdetail_id = '{$shop_detail['shopdetail_id']}'
          AND (
            sad.discount_price > 0 
            OR (sv.price * sad.quantity - sad.net_amount > 0)
          )
    ";
    $event_discount_row = sql_fetch_pg($event_discount_sql);
    $event_discount = $event_discount_row['event_discount'] ?? 0;
    if ($event_discount > 0) {
        $total_event_discount += $event_discount;
    }
}

// 포인트 사용 금액 조회
$point_usage_sql = "
    SELECT SUM(pt.amount) as total_point_usage
    FROM payments p
    INNER JOIN point_transactions pt ON p.payment_id = pt.payment_id
    WHERE p.appointment_id = '{$appointment_id}'
      AND pt.type = '사용'
      AND pt.amount > 0
";
$point_usage_row = sql_fetch_pg($point_usage_sql);
$total_point_usage = $point_usage_row['total_point_usage'] ?? 0;

$g5['title'] = '예약 상세';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <a href="./appointment_list.php" class="ov_listall">목록</a>
    <span class="btn_ov01"><span class="ov_txt">예약번호</span><span class="ov_num"><?=$appointment['appointment_no']?></span></span>
    <span class="btn_ov01"><span class="ov_txt">주문번호</span><span class="ov_num"><?=$appointment['order_id']?></span></span>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
        <caption>예약 기본 정보</caption>
        <colgroup>
            <col style="width:15%;">
            <col style="width:35%;">
            <col style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">예약번호</th>
                <td><?=$appointment['appointment_no']?></td>
                <th scope="row">주문번호</th>
                <td><?=$appointment['order_id']?></td>
            </tr>
            <tr>
                <th scope="row">고객명</th>
                <td><?=get_text($appointment['customer_name'] ?: '게스트')?></td>
                <th scope="row">고객ID</th>
                <td><?=$appointment['customer_id'] ? ($appointment['customer_user_id'] ?: 'ID:'.$appointment['customer_id']) : ($appointment['guest_id'] ? '게스트('.$appointment['guest_id'].')' : '-')?></td>
            </tr>
            <tr>
                <th scope="row">연락처</th>
                <td><?=formatPhoneNumber($appointment['customer_phone'] ?? '')?></td>
                <th scope="row">이메일</th>
                <td><?=$appointment['customer_email'] ?? ''?></td>
            </tr>
            <tr>
                <th scope="row">상태</th>
                <td><?=$status_arr[$appointment['status']] ?? $appointment['status']?></td>
                <th scope="row">예약일시</th>
                <td><?=date('Y-m-d H:i:s', strtotime($appointment['created_at']))?></td>
            </tr>
            <tr>
                <th scope="row">할인전 금액</th>
                <td colspan="3">
                    <?php 
                    // 모든 가맹점의 서비스별 원래 단가 * 수량의 합계 계산
                    $total_original_amount_sql = "
                        SELECT SUM(sv.price * sad.quantity) as total_original_amount
                        FROM appointment_shop_detail asd
                        INNER JOIN shop_appointment_details sad ON asd.shopdetail_id = sad.shopdetail_id
                        INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                        WHERE asd.appointment_id = '{$appointment_id}'
                    ";
                    $total_original_amount_row = sql_fetch_pg($total_original_amount_sql);
                    $total_original_amount = $total_original_amount_row['total_original_amount'] ?? 0;
                    ?>
                    <span style="font-weight: bold; font-size: 16px;"><?=number_format($total_original_amount)?>원</span>
                </td>
            </tr>
            <tr>
                <th scope="row">할인 정보</th>
                <td colspan="3">
                    <?php
                    $has_any_discount = false;
                    $discount_items = array();
                    $total_discount_amount = 0;
                    
                    // 이벤트 할인
                    if ($total_event_discount > 0) {
                        $has_any_discount = true;
                        $total_discount_amount += $total_event_discount;
                        $discount_items[] = array('label' => '이벤트할인', 'amount' => $total_event_discount);
                    }
                    
                    // 쿠폰 할인
                    if ($total_coupon_amount > 0) {
                        $has_any_discount = true;
                        $total_discount_amount += $total_coupon_amount;
                        $discount_items[] = array('label' => '쿠폰할인', 'amount' => $total_coupon_amount);
                    }
                    
                    // 포인트 사용
                    if ($total_point_usage > 0) {
                        $has_any_discount = true;
                        $total_discount_amount += $total_point_usage;
                        $discount_items[] = array('label' => '포인트사용', 'amount' => $total_point_usage);
                    }
                    
                    if ($has_any_discount) {
                        // 총 할인 금액을 상단에 크게 표시
                        echo '<div style="margin-bottom: 15px;">';
                        echo '<span style="color: #d9534f; font-weight: bold; font-size: 15px;">총 할인: -' . number_format($total_discount_amount) . '원</span>';
                        echo '</div>';
                        
                        // 할인 세부항목을 리스트 형식으로 나열
                        echo '<ul style="margin: 0; padding-left: 20px; list-style-type: disc;">';
                        foreach ($discount_items as $item) {
                            echo '<li style="margin-bottom: 5px; color: #666;">';
                            echo '<span style="color: #d9534f;">' . $item['label'] . ': -' . number_format($item['amount']) . '원</span>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<span style="color: #999;">할인 없음</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">최종결제금액</th>
                <td colspan="3">
                    <?php 
                    // 할인전 금액에서 모든 할인을 차감한 최종 결제 금액
                    // 할인전 금액 - 이벤트 할인 - 쿠폰 할인 - 포인트 사용 = 최종결제금액
                    $calculated_final_amount = $total_original_amount - $total_event_discount - $total_coupon_amount - $total_point_usage;
                    
                    // balance_amount는 이미 이벤트 할인과 쿠폰 할인이 반영된 금액
                    // 포인트 사용은 별도로 차감
                    $balance_based_final_amount = $total_payment_amount - $total_point_usage;
                    
                    // 두 값 중 더 정확한 값을 사용 (일반적으로 balance_based가 정확하지만, 
                    // 할인이 있는 경우 계산된 값과 비교하여 사용)
                    if ($has_any_discount) {
                        // 할인이 있는 경우: 계산된 값과 balance 기반 값 중 차이가 작은 값 사용
                        // 또는 계산된 값을 우선 사용 (할인전 금액에서 직접 차감한 값)
                        $final_payment_amount = max(0, $calculated_final_amount);
                    } else {
                        // 할인이 없는 경우: balance_amount 기반 값 사용
                        $final_payment_amount = $balance_based_final_amount;
                    }
                    ?>
                    <span style="color:rgb(84, 51, 228); font-weight: bold; font-size: 18px;"><?=number_format($final_payment_amount)?>원</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php
// 가맹점 이미지 썸네일 크기
$shop_img_wd = 150;
$shop_img_ht = 100;
$service_img_wd = 100;
$service_img_ht = 80;

foreach ($appointments_by_date as $date => $shop_details) {
    $weekday_num = date('w', strtotime($date));
    $weekday = $weekday_arr[$weekday_num];
    ?>
    <div class="appointment-date-group" style="margin-bottom: 40px; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
        <h3 class="date-header" style="font-size: 18px; font-weight: bold; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333;">
            <?=date('Y년 m월 d일', strtotime($date))?> (<?=$weekday?>)
        </h3>
        
        <?php
        foreach ($shop_details as $shop_detail) {
            // 가맹점 정보
            $shop_id = $shop_detail['shop_id'];
            
            // 가맹점 대표 이미지 조회
            $shop_img_sql = " SELECT fle_path FROM dain_file
                              WHERE fle_db_tbl = 'shop'
                                AND fle_type = 'comi'
                                AND fle_dir = 'shop/shop_img'
                                AND fle_db_idx = '{$shop_id}'
                              ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
            $shop_img = sql_fetch_pg($shop_img_sql);
            $shop_thumb_url = '';
            if (!empty($shop_img['fle_path'])) {
                $shop_thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$shop_img_wd.':'.$shop_img_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$shop_img['fle_path'];
            } else {
                $shop_thumb_url = G5_Z_URL.'/img/no_thumb.png';
            }
            ?>
            <div class="shop-appointment-item" style="margin-bottom: 30px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <div class="shop-header" style="display: flex; align-items: center; margin-bottom: 15px;">
                    <img src="<?=$shop_thumb_url?>" alt="<?=$shop_detail['shop_name']?>" style="width:<?=$shop_img_wd?>px; height:<?=$shop_img_ht?>px; border: 1px solid #ddd; margin-right: 15px; object-fit: cover;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: bold; margin: 0 0 5px 0;"><?=$shop_detail['shop_name']?></h4>
                        <p style="margin: 0; color: #666;">예약시간: <?=date('H:i', strtotime($shop_detail['appointment_datetime']))?></p>
                        <p style="margin: 0; color: #666;">방문자: <?=get_text($shop_detail['visitor'] ?? '-')?></p>
                        <p style="margin: 0; color: #666;">인원수: <?=$shop_detail['count'] ?? 1?>명</p>
                    </div>
                </div>
                
                <?php
                // 예약 세부 내역 (서비스 정보)
                $details_sql = "
                    SELECT sad.*,
                           sv.service_id,
                           sv.service_name,
                           sv.description as service_description,
                           sv.price as original_service_price
                    FROM shop_appointment_details sad
                    INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                    WHERE sad.shopdetail_id = '{$shop_detail['shopdetail_id']}'
                    ORDER BY sad.detail_id ASC
                ";
                $details_result = sql_query_pg($details_sql);
                
                if ($details_result && is_object($details_result) && isset($details_result->result)) {
                    ?>
                    <div class="service-details" style="margin-left: <?=($shop_img_wd + 20)?>px;">
                        <?php
                        while ($detail = sql_fetch_array_pg($details_result->result)) {
                            // 서비스 이미지 조회
                            $service_img_sql = " SELECT fle_path FROM dain_file
                                                 WHERE fle_db_tbl = 'shop_services'
                                                   AND fle_type = 'svci'
                                                   AND fle_dir = 'shop/service_img'
                                                   AND fle_db_idx = '{$detail['service_id']}'
                                                 ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
                            $service_img = sql_fetch_pg($service_img_sql);
                            $service_thumb_url = '';
                            if (!empty($service_img['fle_path'])) {
                                $service_thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$service_img_wd.':'.$service_img_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$service_img['fle_path'];
                            } else {
                                $service_thumb_url = G5_Z_URL.'/img/no_thumb.png';
                            }
                            ?>
                            <div class="service-item" style="display: flex; align-items: start; margin-bottom: 15px; padding: 10px; background: white; border: 1px solid #eee; border-radius: 3px;">
                                <img src="<?=$service_thumb_url?>" alt="<?=$detail['service_name']?>" style="width:<?=$service_img_wd?>px; height:<?=$service_img_ht?>px; border: 1px solid #ddd; margin-right: 15px; object-fit: cover;">
                                <div style="flex: 1;">
                                    <h5 style="font-size: 14px; font-weight: bold; margin: 0 0 5px 0;"><?=$detail['service_name']?></h5>
                                    <?php if (!empty($detail['service_description'])) { ?>
                                        <p style="margin: 5px 0; color: #666; font-size: 12px;"><?=get_text($detail['service_description'])?></p>
                                    <?php } ?>
                                    <div style="margin-top: 10px;">
                                        <?php
                                        // 원래 단가: shop_services 테이블의 원래 가격 사용
                                        $quantity = $detail['quantity'] ?? 1;
                                        $original_unit_price = $detail['original_service_price'] ?? 0;
                                        $net_amount = $detail['net_amount'] ?? 0;
                                        
                                        // 원래 금액 계산 (원래 단가 * 수량)
                                        $original_amount = $quantity * $original_unit_price;
                                        
                                        // net_amount가 0이거나 null이면 원래 금액으로 설정
                                        if ($net_amount <= 0 && $quantity > 0 && $original_unit_price > 0) {
                                            $net_amount = $original_amount;
                                        }
                                        
                                        // 할인 금액 계산: 원래 금액 - 최종 금액
                                        // net_amount는 이미 할인 반영된 최종 금액이므로
                                        $discount_amount = $original_amount - $net_amount;
                                        
                                        // 할인 정보 구성
                                        $discount_label = '';
                                        if ($discount_amount > 0) {
                                            $discount_title = $detail['discount_title'] ?? '';
                                            $discount_type = $detail['discount_type'] ?? '';
                                            $discount_value = $detail['discount_value'] ?? 0;
                                            
                                            if (!empty($discount_title)) {
                                                $discount_label = $discount_title;
                                            } elseif (!empty($discount_type)) {
                                                if ($discount_type == 'PERCENT') {
                                                    $discount_label = $discount_value . '% 할인';
                                                } elseif ($discount_type == 'AMOUNT') {
                                                    $discount_label = number_format($discount_value) . '원 할인';
                                                } else {
                                                    $discount_label = '할인';
                                                }
                                            } else {
                                                $discount_label = '할인';
                                            }
                                        }
                                        ?>
                                        <span style="margin-right: 15px;">수량: <?=$quantity?></span>
                                        <span style="margin-right: 15px;">단가: <?=number_format($original_unit_price)?>원</span>
                                        <?php if ($discount_amount > 0 && !empty($discount_label)) { ?>
                                            <span style="color: red; margin-right: 15px;"><?=$discount_label?>: -<?=number_format($discount_amount)?>원</span>
                                        <?php } ?>
                                        <span style="font-weight: bold;">합계: <?=number_format($net_amount)?>원</span>
                                    </div>
                                    <?php if (!empty($detail['memo'])) { ?>
                                        <p style="margin: 5px 0; color: #999; font-size: 11px;">메모: <?=get_text($detail['memo'])?></p>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
                
                <!-- 결제 정보 -->
                <div class="payment-info" style="margin-left: <?=($shop_img_wd + 20)?>px; margin-top: 15px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 3px;">
                    <?php
                    $balance_amount = $shop_detail['balance_amount'] ?? 0;
                    $coupon_amount = $shop_detail['coupon_amount'] ?? 0;
                    $org_balance_amount = $shop_detail['org_balance_amount'] ?? 0;
                    
                    // 해당 가맹점의 이벤트 할인 금액 계산
                    // 원래 단가(shop_services.price)를 사용하여 계산
                    $shop_event_discount_sql = "
                        SELECT SUM(
                            CASE 
                                WHEN sad.discount_price > 0 THEN sad.discount_price
                                WHEN (sv.price * sad.quantity - sad.net_amount) > 0 THEN (sv.price * sad.quantity - sad.net_amount)
                                ELSE 0
                            END
                        ) as event_discount
                        FROM shop_appointment_details sad
                        INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                        WHERE sad.shopdetail_id = '{$shop_detail['shopdetail_id']}'
                          AND (
                            sad.discount_price > 0 
                            OR (sv.price * sad.quantity - sad.net_amount > 0)
                          )
                    ";
                    $shop_event_discount_row = sql_fetch_pg($shop_event_discount_sql);
                    $shop_event_discount = $shop_event_discount_row['event_discount'] ?? 0;
                    
                    // 할인 여부 확인 (먼저 정의)
                    $has_shop_discount = ($shop_event_discount > 0 || $coupon_amount > 0);
                    
                    // 가맹점별 원래 금액 계산 (서비스별 원래 단가 * 수량의 합계)
                    $shop_original_amount_sql = "
                        SELECT SUM(sv.price * sad.quantity) as original_amount
                        FROM shop_appointment_details sad
                        INNER JOIN shop_services sv ON sad.service_id = sv.service_id
                        WHERE sad.shopdetail_id = '{$shop_detail['shopdetail_id']}'
                    ";
                    $shop_original_amount_row = sql_fetch_pg($shop_original_amount_sql);
                    $shop_original_amount = $shop_original_amount_row['original_amount'] ?? 0;
                    
                    // 할인이 있는 경우: 원래금액에서 할인을 차감한 금액이 결제금액
                    // balance_amount는 이미 할인이 반영된 금액이지만, 
                    // 원래금액과 할인 금액을 기준으로 계산한 값과 일치해야 함
                    if ($has_shop_discount) {
                        // 원래금액 - 이벤트할인 - 쿠폰할인 = 결제금액
                        $calculated_final_amount = $shop_original_amount - $shop_event_discount - $coupon_amount;
                        // balance_amount와 계산된 금액 중 더 정확한 값을 사용
                        // 일반적으로 balance_amount가 정확하지만, 계산된 값과 차이가 크면 계산된 값 사용
                        if (abs($balance_amount - $calculated_final_amount) > 100) {
                            // 차이가 크면 계산된 값 사용 (100원 이상 차이)
                            $final_amount = max(0, $calculated_final_amount);
                        } else {
                            // 차이가 작으면 balance_amount 사용
                            $final_amount = $balance_amount;
                        }
                    } else {
                        // 할인이 없으면 balance_amount = 원래금액
                        $final_amount = $balance_amount;
                    }
                    
                    // 쿠폰 정보 조회
                    $coupon_name = '쿠폰할인';
                    if ($coupon_amount > 0 && !empty($shop_detail['customer_coupon_id'])) {
                        $coupon_sql = " SELECT sc.coupon_name 
                                        FROM customer_coupons cc
                                        INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
                                        WHERE cc.customer_coupon_id = '{$shop_detail['customer_coupon_id']}'
                                        LIMIT 1 ";
                        $coupon_info = sql_fetch_pg($coupon_sql);
                        if (!empty($coupon_info['coupon_name'])) {
                            $coupon_name = $coupon_info['coupon_name'];
                        }
                    }
                    ?>
                    
                    <?php if ($has_shop_discount) { ?>
                        <!-- 할인 정보 -->
                        <div style="margin-bottom: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 3px;">
                            <p style="margin: 0 0 10px 0; font-weight: bold; color: #856404;">할인 정보</p>
                            <?php
                            $shop_discount_items = array();
                            $shop_total_discount = 0;
                            
                            // 이벤트 할인
                            if ($shop_event_discount > 0) {
                                $shop_total_discount += $shop_event_discount;
                                $shop_discount_items[] = array('label' => '이벤트할인', 'amount' => $shop_event_discount);
                            }
                            
                            // 쿠폰 할인
                            if ($coupon_amount > 0) {
                                $shop_total_discount += $coupon_amount;
                                $shop_discount_items[] = array('label' => $coupon_name, 'amount' => $coupon_amount);
                            }
                            
                            if (count($shop_discount_items) > 0) {
                                // 총 할인 금액
                                echo '<p style="margin: 0 0 8px 0; font-weight: bold; color: #d9534f; font-size: 14px;">총 할인: -' . number_format($shop_total_discount) . '원</p>';
                                
                                // 할인 세부항목
                                echo '<ul style="margin: 0; padding-left: 20px; list-style-type: disc;">';
                                foreach ($shop_discount_items as $item) {
                                    echo '<li style="margin-bottom: 3px; color: #666; font-size: 12px;">';
                                    echo '<span style="color: #d9534f;">' . $item['label'] . ': -' . number_format($item['amount']) . '원</span>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </div>
                    <?php } ?>
                    
                    <?php if ($has_shop_discount) { ?>
                        <p style="margin: 5px 0;"><strong>원래금액:</strong> <?=number_format($shop_original_amount)?>원</p>
                        <p style="margin: 5px 0;"><strong>결제금액:</strong> <span style="color:
                        rgb(12, 95, 56); font-weight: bold;"><?=number_format($final_amount)?>원</span></p>
                    <?php } else { ?>
                        <p style="margin: 5px 0;color:rgb(51, 59, 119);"><strong>결제금액:</strong> <?=number_format($balance_amount)?>원</p>
                    <?php } ?>
                    <?php if (!empty($shop_detail['requirement'])) { ?>
                        <p style="margin: 5px 0;"><strong>요구사항:</strong> <?=get_text($shop_detail['requirement'])?></p>
                    <?php } ?>
                    <p style="margin: 5px 0;"><strong>상태:</strong> <?=$status_arr[$shop_detail['status']] ?? ($shop_detail['status'] ?? '-')?></p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

