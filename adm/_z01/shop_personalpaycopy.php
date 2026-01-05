<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'w');

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;
$personal_id = ($personal_id > 0 && $personal_id <= 2147483647) ? $personal_id : 0;

$g5['title'] = '개인결제 복사';
include_once(G5_PATH.'/head.sub.php');

$sql = " SELECT * FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
$row = sql_fetch_pg($sql);

if(!$row || !$row['personal_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');

// 예약 정보 조회
$appointment_info = array(
    'appointment_no' => '',
    'nickname' => '',
    'service_names' => '',
    'appointment_datetime' => ''
);

if ($row['shopdetail_id']) {
    $appt_sql = " SELECT
                    sa.appointment_no,
                    c.nickname,
                    asd.appointment_datetime,
                    STRING_AGG(DISTINCT ss.service_name, ', ' ORDER BY ss.service_name) as service_names
                  FROM appointment_shop_detail AS asd
                  LEFT JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
                  LEFT JOIN customers AS c ON sa.customer_id = c.customer_id
                  LEFT JOIN shop_appointment_details AS sad ON asd.shopdetail_id = sad.shopdetail_id
                  LEFT JOIN shop_services AS ss ON sad.service_id = ss.service_id
                  WHERE asd.shopdetail_id = " . (int)$row['shopdetail_id'] . "
                  GROUP BY sa.appointment_no, c.nickname, asd.appointment_datetime ";
    $appt_row = sql_fetch_pg($appt_sql);

    if ($appt_row) {
        $appointment_info['appointment_no'] = $appt_row['appointment_no'] ? $appt_row['appointment_no'] : '';
        $appointment_info['nickname'] = $appt_row['nickname'] ? $appt_row['nickname'] : '';
        $appointment_info['service_names'] = $appt_row['service_names'] ? $appt_row['service_names'] : '';
        $appointment_info['appointment_datetime'] = $appt_row['appointment_datetime'] ? date('Y-m-d H:i', strtotime($appt_row['appointment_datetime'])) : '';
    }
}
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <!-- 예약 정보 표시 -->
    <div style="background:#f5f5f5; padding:15px; margin-bottom:20px; border-radius:3px; border:1px solid #ddd;">
        <h3 style="margin:0 0 10px 0; font-size:1.1em; color:#333;">예약 정보</h3>
        <div style="line-height:1.8;">
            <strong>예약번호:</strong> <?php echo htmlspecialchars($appointment_info['appointment_no']); ?><br>
            <strong>예약일시:</strong> <?php echo htmlspecialchars($appointment_info['appointment_datetime']); ?><br>
            <strong>닉네임:</strong> <?php echo htmlspecialchars($appointment_info['nickname']); ?><br>
            <strong>서비스:</strong> <?php echo htmlspecialchars($appointment_info['service_names']); ?>
        </div>
        <p style="margin:10px 0 0 0; color:#666; font-size:0.9em;">※ 위 예약에 대한 추가 청구를 생성합니다.</p>
    </div>

    <form name="fpersonalpaycopy" method="post" action="./shop_personalpaycopyupdate.php" onsubmit="return form_check(this);">
    <input type="hidden" name="personal_id" value="<?php echo $personal_id; ?>">
    <input type="hidden" name="shopdetail_id" value="<?php echo $row['shopdetail_id']; ?>">

     <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>추가 청구 정보</caption>
        <tbody>
        <tr>
            <th scope="row"><label for="reason">청구사유 <strong class="sound_only">필수</strong></label></th>
            <td><textarea name="reason" id="reason" rows="5" required class="required"><?php echo html_purifier($row['reason']); ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="amount">청구금액 <strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="amount" value="" id="amount" required class="required frm_input" size="20"> 원</td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="복사하기" class="btn_submit">
        <button type="button" onclick="self.close();">창닫기</button>
    </div>

    </form>

</div>

<script>
// <![CDATA[
function form_check(f)
{
    if(f.amount.value.replace(/[0-9]/g, "").length > 0) {
        alert("청구금액은 숫자만 입력해 주십시오");
        f.amount.focus();
        return false;
    }

    if(parseInt(f.amount.value) <= 0) {
        alert("청구금액은 0보다 큰 값이어야 합니다");
        f.amount.focus();
        return false;
    }

    return true;
}
// ]]>
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
