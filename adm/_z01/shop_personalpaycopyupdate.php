<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'w');

$_POST = array_map('trim', $_POST);

$personal_id = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
$personal_id = ($personal_id > 0 && $personal_id <= 2147483647) ? $personal_id : 0;

$shopdetail_id = isset($_POST['shopdetail_id']) ? (int)$_POST['shopdetail_id'] : 0;
$shopdetail_id = ($shopdetail_id > 0 && $shopdetail_id <= 2147483647) ? $shopdetail_id : 0;

// 입력값 검증
if(!$_POST['reason'])
    alert('청구사유를 입력해 주십시오.');
if(!$_POST['amount'])
    alert('청구금액을 입력해 주십시오.');
if(preg_match('/[^0-9]/', $_POST['amount']))
    alert('청구금액은 숫자만 입력해 주십시오.');
if((int)$_POST['amount'] <= 0)
    alert('청구금액은 0보다 큰 값이어야 합니다.');

// shopdetail_id 검증
if (!$shopdetail_id) {
    alert('예약 정보가 없습니다.');
}

$sql = " SELECT * FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
$row = sql_fetch_pg($sql);

if(!$row || !$row['personal_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');

// shopdetail_id를 통해 user_id 조회
$user_id = null;
$user_sql = " SELECT c.user_id
              FROM appointment_shop_detail AS asd
              INNER JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
              INNER JOIN customers AS c ON sa.customer_id = c.customer_id
              WHERE asd.shopdetail_id = {$shopdetail_id} AND asd.shop_id = {$shop_id} ";
$user_row = sql_fetch_pg($user_sql);

if ($user_row && isset($user_row['user_id'])) {
    $user_id = $user_row['user_id'];
}

if (!$user_id) {
    alert('예약 정보에서 회원 정보를 찾을 수 없습니다.');
}

// PostgreSQL의 경우 문자열 이스케이프 처리
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$esc = function($v) use ($pg_link) {
    return $pg_link ? pg_escape_string($pg_link, (string)$v) : $v;
};

$reason_escaped = $esc($_POST['reason']);

// 주문번호는 DB에서 자동 생성되므로 INSERT 시 제외
// 개인정보보호법 준수: name, phone, email은 NULL로 저장
$sql = " INSERT INTO personal_payment (
            shop_id,
            shopdetail_id,
            user_id,
            name,
            reason,
            amount,
            status,
            phone,
            email,
            is_settlement_target,
            created_at,
            updated_at
        ) VALUES (
            {$shop_id},
            {$shopdetail_id},
            '{$user_id}',
            NULL,
            '{$reason_escaped}',
            " . (int)$_POST['amount'] . ",
            'CHARGE',
            NULL,
            NULL,
            true,
            NOW(),
            NOW()
        ) ";
sql_query_pg($sql);

$g5['title'] = '개인결제 복사';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
alert("개인결제 정보가 복사되었습니다.");
window.opener.location.reload();
self.close();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
