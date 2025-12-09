<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * 닉네임을 아이디와 동일하게 변경
 *
 * register_form.skin.php에서 랜덤 닉네임으로 검증을 통과한 후
 * 실제 DB 저장 전에 mb_nick을 mb_id와 동일하게 변경
 */

// mb_nick을 mb_id로 변경
$mb_nick = $mb_id;
$_POST['mb_nick'] = $mb_id;

// dainpass_pg 데이터
$pg_shop_name          = isset($_POST['shop_name']) ? sql_escape_string(trim($_POST['shop_name'])) : "";
$pg_business_no_raw    = isset($_POST['business_no']) ? trim($_POST['business_no']) : "";
$pg_business_no        = preg_replace('/[^0-9]/', '', $pg_business_no_raw); // 숫자만 추출
$pg_owner_name         = sql_escape_string($mb_name);
$pg_contact_email      = sql_escape_string($mb_email);
$pg_contact_phone      = sql_escape_string($mb_hp);
$pg_zipcode            = sql_escape_string($mb_zip1 . $mb_zip2);
$pg_addr1              = sql_escape_string($mb_addr1);
$pg_addr2              = sql_escape_string($mb_addr2);
$pg_addr3              = sql_escape_string($mb_addr3);
$pg_max_capacity       = 0;
$pg_status             = "pending";
$pg_shop_description   = "";
$pg_settlement_memo    = "";
$pg_cancel_policy      = "";
$pg_names = $pg_shop_name . '(' . date('y-m-d') . '~)';
$pg_shop_names = $pg_shop_name . '(' . date('y-m-d') . '~)';

$pg_category_id        = isset($_POST['category_id']) ? sql_escape_string(trim($_POST['category_id'])) : "";
$pg_shop_id = "";

/**
 * 사업자등록증 파일 검증
 */
function validate_business_license_file($file)
{
    $max_size_mb = 5;
    $max_size_bytes = $max_size_mb * 1024 * 1024;
    $allowed_extensions = ['gif', 'jpg', 'jpeg', 'png', 'webp', 'pdf'];

    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return '사업자등록증을 업로드해주세요.';
    }

    if ($file['size'] > $max_size_bytes) {
        return "{$max_size_mb}MB 이하의 파일만 업로드 가능합니다.";
    }

    if ($file['size'] == 0) {
        return '빈 파일은 업로드할 수 없습니다.';
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        return '허용되지 않는 파일 형식입니다.';
    }

    return '';
}

// 사업자등록증 파일 검증
if (!isset($_FILES['business_license_file']['name'][0]) || empty($_FILES['business_license_file']['name'][0])) {
    alert('사업자등록증을 업로드해주세요.');
}

$file = [
    'name' => $_FILES['business_license_file']['name'][0],
    'type' => $_FILES['business_license_file']['type'][0],
    'tmp_name' => $_FILES['business_license_file']['tmp_name'][0],
    'error' => $_FILES['business_license_file']['error'][0],
    'size' => $_FILES['business_license_file']['size'][0]
];

$error_msg = validate_business_license_file($file);
if ($error_msg) {
    alert($error_msg);
}

// 신규 회원가입인 경우에만 shop 테이블에 INSERT
if ($w == '') {
    $sql = " INSERT INTO {$g5['shop_table']} (
                name, business_no, owner_name, contact_email, contact_phone,
                zipcode, addr1, addr2, addr3, max_capacity,
                status, shop_description, settlement_memo, shop_name, cancel_policy, names, shop_names
            ) VALUES (
                '{$pg_shop_name}', '{$pg_business_no}', '{$pg_owner_name}', '{$pg_contact_email}', '{$pg_contact_phone}',
                '{$pg_zipcode}', '{$pg_addr1}', '{$pg_addr2}', '{$pg_addr3}', '{$pg_max_capacity}',
                'active', '{$pg_shop_description}', '{$pg_settlement_memo}', '{$pg_shop_name}', '{$pg_cancel_policy}', '{$pg_names}', '{$pg_shop_names}'
            ) ";

    sql_query_pg($sql);

    // 생성된 shop_id 가져오기
    $pg_shop_id = sql_insert_id_pg($g5['shop_table']);

    // shop_category_relation_table에 INSERT
    if ($pg_category_id && $pg_shop_id) {
        $sql_relation = " INSERT INTO {$g5['shop_category_relation_table']} (
                            shop_id, category_id
                        ) VALUES (
                            '{$pg_shop_id}', '{$pg_category_id}'
                        ) ";
        sql_query_pg($sql_relation);
    }

    // shop_id 및 dainpass_pg 관련 데이터 갱신
    $mb_1 = $pg_shop_id;
    $mb_2 = 'Y';
    $mb_10 = 'pending';

    // 사업자등록증 파일 업로드
    upload_multi_file($_FILES['business_license_file'], 'shop', $pg_shop_id, 'shop/shop_file', 'comf');
}
