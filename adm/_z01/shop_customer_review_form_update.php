<?php
$sub_menu = "960400";
include_once('./_common.php');

check_demo();

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
        
        if (!empty($mb_1_value) && $mb_1_value !== '0') {
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
    alert("접속할 수 없는 페이지 입니다.");
}

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$w = isset($_POST['w']) ? $_POST['w'] : '';

// POST로 받은 shop_id와 세션의 shop_id 일치 확인
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
if ($post_shop_id !== $shop_id) {
    alert("잘못된 접근입니다.");
}

if ($w == "u")
{
    if ($review_id <= 0) {
        alert("리뷰번호가 없습니다.");
    }
    
    // 리뷰 조회 및 검증 (해당 가맹점의 리뷰만)
    $sql = " SELECT * FROM shop_review WHERE review_id = '{$review_id}' AND shop_id = {$shop_id} AND sr_deleted = 'N' ";
    $review = sql_fetch_pg($sql);
    
    if (!isset($review['review_id']) || !$review['review_id']) {
        alert("리뷰자료가 없거나 해당 가맹점의 리뷰가 아닙니다.");
    }
    
    $posts = array();
    $check_keys = array('sr_score', 'sr_content');
    
    foreach($check_keys as $key){
        if( in_array($key, array('sr_content')) ){
            $posts[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
        } else {
            $posts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
        }
    }
    
    // 평점 검증 (1-5 사이)
    $sr_score = (int)$posts['sr_score'];
    if ($sr_score < 1 || $sr_score > 5) {
        alert("평점은 1점부터 5점까지 입력 가능합니다.");
    }
    
    // IP 주소 가져오기
    $sr_ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $sr_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    
    // 리뷰 수정
    $sql = " UPDATE shop_review 
             SET sr_score = '{$sr_score}',
                 sr_content = '".addslashes($posts['sr_content'])."',
                 sr_ip = '".addslashes($sr_ip)."',
                 sr_updated_at = NOW() 
             WHERE review_id = '{$review_id}' ";
    sql_query_pg($sql);
    
    // 파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(isset($_POST['rvwi_del']) && @count($_POST['rvwi_del'])){
        foreach($_POST['rvwi_del'] as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(is_array($merge_del) && @count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(is_array($del_arr) && @count($del_arr)) delete_idx_s3_file($del_arr);
    
    //$review_id가 반드시 있어야 파일업로드가 가능
    if($review_id){
        //멀티파일처리
        upload_multi_file($_FILES['rvwi_datas'],'shop_review',$review_id,'shop/review_img','rvwi');
    }
    
    // qstr 생성
    $qstr = '';
    if (isset($_POST['sst'])) $qstr .= '&sst='.urlencode($_POST['sst']);
    if (isset($_POST['sod'])) $qstr .= '&sod='.urlencode($_POST['sod']);
    if (isset($_POST['sfl'])) $qstr .= '&sfl='.urlencode($_POST['sfl']);
    if (isset($_POST['stx'])) $qstr .= '&stx='.urlencode($_POST['stx']);
    if (isset($_POST['sfl2'])) $qstr .= '&sfl2='.urlencode($_POST['sfl2']);
    if (isset($_POST['page'])) $qstr .= '&page='.urlencode($_POST['page']);
    
    goto_url("./shop_customer_review_form.php?w=u&review_id={$review_id}&{$qstr}");
}
else
{
    alert();
}
