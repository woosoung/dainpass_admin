<?php
$sub_menu = "960400";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

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
