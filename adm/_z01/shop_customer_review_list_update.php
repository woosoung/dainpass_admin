<?php
$sub_menu = "960400";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

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

$w = isset($_POST['w']) ? $_POST['w'] : '';

// 삭제할 때
if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $review = sql_fetch_pg(" SELECT * FROM shop_review WHERE review_id = '".$_POST['review_id'][$k]."' ");

        if (!$review['review_id']) {
            $msg .= $_POST['review_id'][$k].' : 리뷰자료가 존재하지 않습니다.\\n';
        } else {
            // 가맹점 리뷰인지 확인 (shop_id = 해당 가맹점)
            if ($review['shop_id'] != $shop_id) {
                $msg .= $_POST['review_id'][$k].' : 해당 가맹점의 리뷰가 아닙니다.\\n';
                continue;
            }
            
            // 삭제 처리 (논리 삭제)
            $sql = " UPDATE shop_review 
                     SET sr_deleted = 'Y', 
                         sr_deleted_at = NOW() 
                     WHERE review_id = '{$_POST['review_id'][$k]}' ";
            sql_query_pg($sql,1);
        }
    }
}

if ($msg)
    alert($msg);

// qstr 생성
$qstr = '';
if (isset($_POST['sst'])) $qstr .= '&sst='.urlencode($_POST['sst']);
if (isset($_POST['sod'])) $qstr .= '&sod='.urlencode($_POST['sod']);
if (isset($_POST['sfl'])) $qstr .= '&sfl='.urlencode($_POST['sfl']);
if (isset($_POST['stx'])) $qstr .= '&stx='.urlencode($_POST['stx']);
if (isset($_POST['sfl2'])) $qstr .= '&sfl2='.urlencode($_POST['sfl2']);
if (isset($_POST['page'])) $qstr .= '&page='.urlencode($_POST['page']);

goto_url('./shop_customer_review_list.php?'.$qstr, false);
