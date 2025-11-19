<?php
$sub_menu = '920500'; // 적절한 메뉴 번호로 변경 필요
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0;

if( ! $customer_id && $w == 'u' ){
    alert('고객 ID가 없습니다.', './customer_list.php');
}

if( $customer_id && $w == 'u' ){
    $sql = " SELECT * FROM customers WHERE customer_id = '$customer_id' ";
    $cu = sql_fetch_pg($sql);

    if ($cu && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

// 입력값 받기
$user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$identify_key = isset($_POST['identify_key']) ? trim($_POST['identify_key']) : '';
$customer_key = isset($_POST['customer_key']) ? trim($_POST['customer_key']) : '';
$encrypted_pwd = isset($_POST['encrypted_pwd']) ? trim($_POST['encrypted_pwd']) : '';
$birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
$birth_date = ($birth_date == '') ? 'NULL' : "'".addslashes($birth_date)."'";
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$gender = ($gender == '') ? 'NULL' : "'".addslashes($gender)."'";
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$status = ($status == '') ? "'active'" : "'".addslashes($status)."'";
$nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
$nickname = ($nickname == '') ? 'NULL' : "'".addslashes($nickname)."'";
$zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
$zipcode = ($zipcode == '') ? 'NULL' : "'".addslashes($zipcode)."'";
$addr1 = isset($_POST['addr1']) ? trim($_POST['addr1']) : '';
$addr1 = ($addr1 == '') ? 'NULL' : "'".addslashes($addr1)."'";
$addr2 = isset($_POST['addr2']) ? trim($_POST['addr2']) : '';
$addr2 = ($addr2 == '') ? 'NULL' : "'".addslashes($addr2)."'";
$addr3 = isset($_POST['addr3']) ? trim($_POST['addr3']) : '';
$addr3 = ($addr3 == '') ? 'NULL' : "'".addslashes($addr3)."'";
// 라디오버튼 필드들 - 값이 없으면 기본값 설정
$agreed_marketing_email = isset($_POST['agreed_marketing_email']) ? trim($_POST['agreed_marketing_email']) : 'N';
$is_real_name_verified = isset($_POST['is_real_name_verified']) ? trim($_POST['is_real_name_verified']) : 'N';
$withdraw = isset($_POST['withdraw']) ? trim($_POST['withdraw']) : 'N';
$agreed_push = isset($_POST['agreed_push']) ? trim($_POST['agreed_push']) : 'N';
$apple_id = isset($_POST['apple_id']) ? trim($_POST['apple_id']) : '';
$apple_id = ($apple_id == '') ? 'NULL' : "'".addslashes($apple_id)."'";
$google_id = isset($_POST['google_id']) ? trim($_POST['google_id']) : '';
$google_id = ($google_id == '') ? 'NULL' : "'".addslashes($google_id)."'";
$naver_id = isset($_POST['naver_id']) ? trim($_POST['naver_id']) : '';
$naver_id = ($naver_id == '') ? 'NULL' : "'".addslashes($naver_id)."'";
$kakao_id = isset($_POST['kakao_id']) ? trim($_POST['kakao_id']) : '';
$kakao_id = ($kakao_id == '') ? 'NULL' : "'".addslashes($kakao_id)."'";

// 유효성 검사
if (!$user_id) {
    alert('아이디를 입력해 주세요.');
}

if (!$name) {
    alert('이름을 입력해 주세요.');
}

if ($w != 'd'){
    // 패스워드 암호화 처리
    $pwd_sql = '';
    if($encrypted_pwd) {
        $encrypted_pwd = sql_password($encrypted_pwd);
        $pwd_sql = " encrypted_pwd = '".addslashes($encrypted_pwd)."',";
    }

    $sql_common = " user_id = '".addslashes($user_id)."',
                    name = '".addslashes($name)."',
                    phone = ".($phone ? "'".addslashes($phone)."'" : "NULL").",
                    email = ".($email ? "'".addslashes($email)."'" : "NULL").",
                    identify_key = ".($identify_key ? "'".addslashes($identify_key)."'" : "NULL").",
                    customer_key = '".addslashes($customer_key)."',
                    ".$pwd_sql."
                    birth_date = ".$birth_date.",
                    gender = ".$gender.",
                    status = ".$status.",
                    nickname = ".$nickname.",
                    zipcode = ".$zipcode.",
                    addr1 = ".$addr1.",
                    addr2 = ".$addr2.",
                    addr3 = ".$addr3.",
                    agreed_marketing_email = '".$agreed_marketing_email."',
                    is_real_name_verified = '".$is_real_name_verified."',
                    withdraw = '".addslashes($withdraw)."',
                    agreed_push = '".addslashes($agreed_push)."',
                    apple_id = ".$apple_id.",
                    google_id = ".$google_id.",
                    naver_id = ".$naver_id.",
                    kakao_id = ".$kakao_id."
    ";
    // echo $sql_common;exit;
}

if ($w == "") {
    $sql_common_columns = "user_id, name, phone, email, identify_key, customer_key, encrypted_pwd, birth_date, gender, status, nickname, zipcode, addr1, addr2, addr3, agreed_marketing_email, is_real_name_verified, withdraw, agreed_push, apple_id, google_id, naver_id, kakao_id, created_at, updated_at";
    
    $sql_common_values = "'".addslashes($user_id)."', '".addslashes($name)."', ".($phone ? "'".addslashes($phone)."'" : "NULL").", ".($email ? "'".addslashes($email)."'" : "NULL").", ".($identify_key ? "'".addslashes($identify_key)."'" : "NULL").", '".addslashes($customer_key)."', '".addslashes(sql_password($encrypted_pwd))."', ".$birth_date.", ".$gender.", ".$status.", ".$nickname.", ".$zipcode.", ".$addr1.", ".$addr2.", ".$addr3.", '".$agreed_marketing_email."', '".$is_real_name_verified.", '".addslashes($withdraw)."', '".addslashes($agreed_push)."', ".$apple_id.", ".$google_id.", ".$naver_id.", ".$kakao_id.", '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."'";

    $sql = " INSERT INTO {$g5['customers_table']}
            (".$sql_common_columns.")
              VALUES
            (".$sql_common_values.") ";

    sql_query_pg($sql);
    $customer_id = sql_insert_id_pg('customers');
} else if ($w == "u") {
    $sql = " UPDATE {$g5['customers_table']}
                SET ".$sql_common."
                    , updated_at = '".G5_TIME_YMDHIS."'
              WHERE customer_id = '$customer_id' ";
    // echo $sql;exit;
    sql_query_pg($sql);
}
else if ($w == "d")
{
    // 먼저 해당 고객 관련 이미지를 전부 삭제처리
    $fsql = " SELECT string_agg(fle_idx::text, ',' ORDER BY fle_reg_dt DESC) AS fle_idxs
                FROM {$g5['dain_file_table']}
                WHERE fle_db_tbl = 'customers'
                    AND fle_type = 'profile_img'
                    AND fle_dir = 'user/profile'
                    AND fle_db_idx = '{$customer_id}' ";

    $frow = sql_fetch_pg($fsql);
    if(isset($frow['fle_idxs'])){
        $del_arr = explode(',', $frow['fle_idxs']);
        if(count($del_arr)) delete_idx_s3_file($del_arr);
    }
    
    // 해당 고객 삭제
    $sql = " DELETE FROM {$g5['customers_table']} WHERE customer_id = '$customer_id' ";
    sql_query_pg($sql);
}

// exit;
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();

    $profile_img_del = isset($_POST['profile_img_'.$customer_id.'_del']) && is_array($_POST['profile_img_'.$customer_id.'_del']) ? $_POST['profile_img_'.$customer_id.'_del'] : array();

    if(!empty($profile_img_del)){
        foreach($profile_img_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(!empty($del_arr)) delete_idx_s3_file($del_arr);
    
    //file의 name, fle_db_idx, fle_idx, fle_dir, fle_type
    upload_multi_file($_FILES['customer_profile_img'],'customers',$customer_id,'user/profile','profile_img');
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./customer_form.php?w=u&amp;customer_id=$customer_id&amp;$qstr");
} else {
    goto_url("./customer_list.php?$qstr");
}