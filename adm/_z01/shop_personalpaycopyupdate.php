<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
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
        
        if ($mb_1_value !== '0' && !empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

@auth_check($auth[$sub_menu], 'w');

$_POST = array_map('trim', $_POST);

$personal_id = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;

// shop_id 검증
if ($post_shop_id != $shop_id) {
    alert('잘못된 접근입니다.');
}

if(!$_POST['name'])
    alert('이름을 입력해 주십시오.');
if(!$_POST['amount'])
    alert('청구금액을 입력해 주십시오.');
if(preg_match('/[^0-9]/', $_POST['amount']))
    alert('청구금액은 숫자만 입력해 주십시오.');
if((int)$_POST['amount'] <= 0)
    alert('청구금액은 0보다 큰 값이어야 합니다.');

$sql = " SELECT * FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
$row = sql_fetch_pg($sql);

if(!$row || !$row['personal_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');

// PostgreSQL의 경우 문자열 이스케이프 처리
// 안전한 이스케이프 처리 (pg 연결 명시)
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$esc = function($v) use ($pg_link) {
    return $pg_link ? pg_escape_string($pg_link, (string)$v) : $v;
};

$name_escaped   = $esc($_POST['name']);
$reason_escaped = $esc($row['reason']);
$user_id_escaped = $row['user_id'] ? $esc($row['user_id']) : 'NULL';
$phone_escaped   = $row['phone'] ? $esc($row['phone']) : 'NULL';
$email_escaped   = $row['email'] ? $esc($row['email']) : 'NULL';

// 주문번호는 DB에서 자동 생성되므로 INSERT 시 제외
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
            " . ($row['shopdetail_id'] ? $row['shopdetail_id'] : 'NULL') . ",
            " . ($row['user_id'] ? "'{$user_id_escaped}'" : 'NULL') . ",
            '{$name_escaped}',
            '{$reason_escaped}',
            " . (int)$_POST['amount'] . ",
            'CHARGE',
            " . ($row['phone'] ? "'{$phone_escaped}'" : 'NULL') . ",
            " . ($row['email'] ? "'{$email_escaped}'" : 'NULL') . ",
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

