<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'w');

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;
$personal_id = ($personal_id > 0 && $personal_id <= 2147483647) ? $personal_id : 0;

$allowed_w = array('', 'u');
$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

// 개인정보보호법 준수: name, phone, email은 폼에서 입력받지 않음
$pp = array(
    'personal_id' => 0,
    'order_id' => '',
    'shop_id' => $shop_id,
    'shopdetail_id' => null,
    'user_id' => '',
    'reason' => '',
    'amount' => 0,
    'status' => 'CHARGE',
    'is_settlement_target' => true
);

if ($w == 'u') {
    $html_title = '개인결제 수정';
    
    $sql = " SELECT * FROM personal_payment WHERE personal_id = " . (int)$personal_id . " AND shop_id = " . (int)$shop_id . " ";
    $pp_row = sql_fetch_pg($sql);
    
    if (!$pp_row || !$pp_row['personal_id']) {
        alert('등록된 자료가 없습니다.');
    }
    
    // 개인정보보호법 준수: name, phone, email은 폼에서 표시하지 않음
    $pp = array(
        'personal_id' => $pp_row['personal_id'],
        'order_id' => $pp_row['order_id'],
        'shop_id' => $pp_row['shop_id'],
        'shopdetail_id' => $pp_row['shopdetail_id'],
        'user_id' => $pp_row['user_id'],
        'reason' => $pp_row['reason'],
        'amount' => $pp_row['amount'],
        'status' => $pp_row['status'],
        'is_settlement_target' => $pp_row['is_settlement_target']
    );
    
    // 결제 정보 조회
    $payment_sql = " SELECT * FROM payments WHERE personal_id = " . (int)$personal_id . " AND pay_flag = 'PERSONAL' ";
    $payment_row = sql_fetch_pg($payment_sql);
} else {
    $html_title = '개인결제 입력';
}

// qstr 파라미터 화이트리스트 검증
// 개인정보보호법 준수: 닉네임만 검색 가능
$allowed_sst = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'personal_id', 'order_id', 'nickname');
$allowed_sfl2 = array('', 'CHARGE', 'PAID');

$qstr_sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : '';
$qstr_sst = in_array($qstr_sst, $allowed_sst) ? $qstr_sst : '';

$qstr_sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : '';
$qstr_sod = in_array($qstr_sod, $allowed_sod) ? $qstr_sod : '';

$qstr_sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$qstr_sfl = in_array($qstr_sfl, $allowed_sfl) ? $qstr_sfl : '';

$qstr_stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$qstr_stx = substr($qstr_stx, 0, 100);

$qstr_sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : '';
$qstr_sfl2 = in_array($qstr_sfl2, $allowed_sfl2) ? $qstr_sfl2 : '';

$qstr_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$qstr_page = ($qstr_page > 0 && $qstr_page <= 10000) ? $qstr_page : 1;

$g5['title'] = $html_title;
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<div class="local_desc01 local_desc">
    <p>
        개인결제 청구 정보를 <?php echo $w == 'u' ? '수정' : '등록'; ?>합니다.<br>
        <?php echo get_shop_display_name($shop_info, $shop_id); ?>
    </p>
</div>

<form name="fpersonalpayform" action="./shop_personalpayformupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="personal_id" value="<?php echo $pp['personal_id']; ?>">
<input type="hidden" name="sst" value="<?php echo $qstr_sst; ?>">
<input type="hidden" name="sod" value="<?php echo $qstr_sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $qstr_sfl; ?>">
<input type="hidden" name="stx" value="<?php echo htmlspecialchars($qstr_stx, ENT_QUOTES, 'UTF-8'); ?>">
<input type="hidden" name="sfl2" value="<?php echo $qstr_sfl2; ?>">
<input type="hidden" name="page" value="<?php echo $qstr_page; ?>">

<section id="anc_spp_info">
    <h2 class="h2_frm">청구 정보</h2>
    <div class="local_desc">
        <p>개인결제 청구 관련 기본 정보입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>청구 정보 목록</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($w == 'u') { ?>
        <tr>
            <th scope="row">주문번호</th>
            <td>
                <?php echo htmlspecialchars($pp['order_id']); ?>
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($pp['order_id']); ?>">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label>예약 정보 <strong class="sound_only">필수</strong></label></th>
            <td>
                <?php if ($w == 'u') { ?>
                    <!-- 수정 모드: 기존 예약 정보 표시만 -->
                    <?php
                    if ($pp['shopdetail_id']) {
                        $existing_sql = " SELECT asd.shopdetail_id, asd.appointment_id, asd.appointment_datetime, sa.appointment_no, c.nickname
                                          FROM appointment_shop_detail AS asd
                                          LEFT JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
                                          LEFT JOIN customers AS c ON sa.customer_id = c.customer_id
                                          WHERE asd.shopdetail_id = " . (int)$pp['shopdetail_id'] . " ";
                        $existing_row = sql_fetch_pg($existing_sql);
                        if ($existing_row) {
                            $existing_datetime = $existing_row['appointment_datetime'] ? date('Y-m-d H:i', strtotime($existing_row['appointment_datetime'])) : '';
                            $existing_appointment_no = $existing_row['appointment_no'] ? $existing_row['appointment_no'] : '';
                            $existing_nickname = $existing_row['nickname'] ? $existing_row['nickname'] : '';
                            echo '<div style="padding: 10px; background: #f5f5f5; border-radius: 3px;">';
                            echo '<strong>예약번호:</strong> ' . htmlspecialchars($existing_appointment_no) . '<br>';
                            echo '<strong>예약일시:</strong> ' . $existing_datetime . '<br>';
                            echo '<strong>닉네임:</strong> ' . htmlspecialchars($existing_nickname);
                            echo '</div>';
                        }
                    }
                    ?>
                    <input type="hidden" name="shopdetail_id" id="shopdetail_id" value="<?php echo $pp['shopdetail_id']; ?>">
                <?php } else { ?>
                    <!-- 신규 등록: 예약 선택 버튼 -->
                    <input type="hidden" name="shopdetail_id" id="shopdetail_id" value="" required>
                    <button type="button" onclick="openAppointmentModal()" class="btn btn_02">예약 선택</button>
                    <div id="selected_appointment_info" style="margin-top: 5px; color: #666; font-size: 0.95em;"></div>
                    <small style="display:block; margin-top: 5px; color: #999;">예약 선택 버튼을 클릭하여 결제 완료된 예약을 선택해 주세요.</small>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="reason">청구사유 <strong class="sound_only">필수</strong></label></th>
            <td><textarea name="reason" id="reason" rows="5" required class="required"><?php echo html_purifier($pp['reason']); ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="amount">청구금액 <strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="amount" value="<?php echo $pp['amount']; ?>" id="amount" required class="required frm_input" size="15"> 원</td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php if ($w == 'u' && isset($payment_row) && $payment_row) { ?>
<section id="anc_spp_pay" class="cbox">
    <h2 class="h2_frm">결제 정보</h2>
    <div class="local_desc02 local_desc">
        <p>결제 관련 정보입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>결제 정보 목록</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결제ID</th>
            <td><?php echo $payment_row['payment_id']; ?></td>
        </tr>
        <tr>
            <th scope="row">결제키</th>
            <td><?php echo htmlspecialchars($payment_row['payment_key']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제방법</th>
            <td><?php echo htmlspecialchars($payment_row['payment_method']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제금액</th>
            <td><?php echo number_format($payment_row['amount']); ?>원</td>
        </tr>
        <tr>
            <th scope="row">결제상태</th>
            <td><?php echo htmlspecialchars($payment_row['status']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제일시</th>
            <td><?php echo $payment_row['paid_at'] ? date('Y-m-d H:i:s', strtotime($payment_row['paid_at'])) : '-'; ?></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>
<?php } ?>

<?php
// qstr 생성
$qstr = '';
if ($qstr_sst) $qstr .= '&sst=' . urlencode($qstr_sst);
if ($qstr_sod) $qstr .= '&sod=' . urlencode($qstr_sod);
if ($qstr_sfl) $qstr .= '&sfl=' . urlencode($qstr_sfl);
if ($qstr_stx) $qstr .= '&stx=' . urlencode($qstr_stx);
if ($qstr_sfl2) $qstr .= '&sfl2=' . urlencode($qstr_sfl2);
if ($qstr_page > 1) $qstr .= '&page=' . $qstr_page;
?>

<div class="btn_fixed_top">
    <a href="./shop_personalpaylist.php<?php echo $qstr ? '?' . ltrim($qstr, '&') : ''; ?>" class="btn btn_02">목록</a>
    <?php if($w == 'u') { ?>
        <a href="./shop_personalpayformupdate.php?w=d&amp;personal_id=<?php echo $pp['personal_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
    <?php } ?>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

<!-- 예약 선택 모달 -->
<div id="appointmentSelectModal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; z-index:1000; background:rgba(0,0,0,0.5);">
    <div style="position:relative; display:table; width:100%; height:100%;">
        <div style="display:table-cell; vertical-align:middle; text-align:center; padding:20px;">
            <div style="position:relative; background:#fff; max-width:900px; width:100%; max-height:90vh; overflow-y:auto; display:inline-block; padding:30px; border-radius:5px; text-align:left; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
                <h2 style="font-size:1.3em; padding:0 0 15px 0; margin:0 0 15px 0; border-bottom:1px solid #ddd;">예약 선택</h2>

                <!-- 검색 영역 -->
                <div class="local_sch01 local_sch" style="margin-bottom:20px; border:1px solid #ddd; padding:15px; border-radius:3px; background:#f9f9f9;">
                    <div style="margin-bottom:10px;">
                        <label for="modal_search_type" style="margin-right:10px;">검색 조건:</label>
                        <select id="modal_search_type" class="frm_input" style="margin-right:10px;">
                            <option value="nickname">닉네임</option>
                            <option value="appointment_no">예약번호</option>
                            <option value="service_name">서비스명</option>
                        </select>
                        <input type="text" id="modal_search_value" class="frm_input" placeholder="검색어 입력" style="width:300px; margin-right:10px;">
                        <button type="button" onclick="searchAppointments()" class="btn_submit btn">검색</button>
                        <button type="button" onclick="resetAppointmentSearch()" class="btn btn_02" style="margin-left:5px;">전체목록</button>
                    </div>

                    <div style="margin-top:10px; padding-top:10px; border-top:1px solid #e0e0e0;">
                        <label style="margin-right:10px; display:inline-block; vertical-align:middle;">날짜 검색:</label>
                        <div style="display:inline-block; vertical-align:middle;">
                            <button type="button" onclick="setDateRange('today')" class="btn btn_02" style="margin-right:3px; padding:5px 10px; font-size:0.9em;">오늘</button>
                            <button type="button" onclick="setDateRange('thisWeek')" class="btn btn_02" style="margin-right:3px; padding:5px 10px; font-size:0.9em;">이번주</button>
                            <button type="button" onclick="setDateRange('thisMonth')" class="btn btn_02" style="margin-right:3px; padding:5px 10px; font-size:0.9em;">이번달</button>
                            <button type="button" onclick="setDateRange('last7days')" class="btn btn_02" style="margin-right:3px; padding:5px 10px; font-size:0.9em;">최근 7일</button>
                            <button type="button" onclick="setDateRange('last30days')" class="btn btn_02" style="margin-right:10px; padding:5px 10px; font-size:0.9em;">최근 30일</button>
                        </div>
                        <div style="display:inline-block; vertical-align:middle; margin-top:5px;">
                            <input type="text" id="modal_date_from" class="frm_input" style="margin-right:5px; width:110px;" placeholder="시작일" maxlength="10" readonly>
                            <span style="margin-right:5px;">~</span>
                            <input type="text" id="modal_date_to" class="frm_input" style="margin-right:10px; width:110px;" placeholder="종료일" maxlength="10" readonly>
                            <button type="button" onclick="clearDateRange()" class="btn btn_02" style="padding:5px 10px; font-size:0.9em;">날짜 초기화</button>
                        </div>
                    </div>

                    <div style="margin-top:10px; color:#666; font-size:0.9em;">
                        ※ 결제 완료된 예약만 검색됩니다
                    </div>
                </div>

                <!-- 결과 테이블 영역 -->
                <div id="appointmentListArea">
                    <!-- 동적으로 테이블 생성 -->
                </div>

                <!-- 닫기 버튼 -->
                <div style="text-align:center; margin-top:20px;">
                    <button type="button" onclick="closeAppointmentModal()" class="btn btn_02">닫기</button>
                </div>
            </div>
        </div>
    </div>
</div>

</form>

<style>
/* 반응형 모달 스타일 */
@media (max-width: 768px) {
    #appointmentSelectModal .btn_submit {
        width: 100%;
        margin-top: 10px;
    }

    #appointmentSelectModal #modal_search_value {
        width: 100% !important;
        margin-top: 5px;
        margin-right: 0 !important;
    }
}
</style>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

