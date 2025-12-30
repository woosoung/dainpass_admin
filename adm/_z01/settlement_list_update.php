<?php
$sub_menu = "920450";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if($w == 'd') {
    auth_check($auth[$sub_menu], 'd');

    $failed_settlements = array();
    $success_count = 0;

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        $k = $_POST['chk'][$i];
        $ssl_id = (int)$_POST['ssl_id'][$k];

        if (!$ssl_id) {
            $failed_settlements[] = '정산 로그 ID가 없습니다.';
            continue;
        }

        // 정산 로그 정보 조회
        $ssl = sql_fetch_pg(" SELECT * FROM shop_settlement_log WHERE ssl_id = '{$ssl_id}' ");

        if (!$ssl || !$ssl['ssl_id']) {
            $failed_settlements[] = '정산 로그 자료가 존재하지 않습니다. (ID: '.$ssl_id.')';
            continue;
        }

        // 자동 정산은 삭제 불가
        if ($ssl['settlement_type'] === 'AUTO') {
            $failed_settlements[] = '자동 정산 데이터는 삭제할 수 없습니다. (ID: '.$ssl_id.')';
            continue;
        }

        try {
            // PostgreSQL 트랜잭션 시작
            sql_query_pg("BEGIN");

            // shop_settlements 먼저 삭제
            $delete_settlements_sql = " DELETE FROM shop_settlements WHERE ssl_id = '{$ssl_id}' ";
            if (!sql_query_pg($delete_settlements_sql)) {
                throw new Exception('정산 내역 삭제에 실패했습니다.');
            }

            // shop_settlement_log 삭제
            $delete_log_sql = " DELETE FROM shop_settlement_log WHERE ssl_id = '{$ssl_id}' ";
            if (!sql_query_pg($delete_log_sql)) {
                throw new Exception('정산 로그 삭제에 실패했습니다.');
            }

            sql_query_pg("COMMIT");
            $success_count++;

        } catch (Exception $e) {
            sql_query_pg("ROLLBACK");
            $failed_settlements[] = $e->getMessage() . ' (ID: '.$ssl_id.')';
        }
    }

    // 결과 메시지 생성
    if ($success_count > 0 && count($failed_settlements) == 0) {
        $msg = '삭제 처리가 완료되었습니다. ('.$success_count.'건)';
    } else if ($success_count > 0 && count($failed_settlements) > 0) {
        $msg = '삭제 처리 완료: '.$success_count.'건'."\n\n".'실패: '.count($failed_settlements).'건'."\n".implode("\n", $failed_settlements);
    } else if (count($failed_settlements) > 0) {
        $msg = '삭제 처리 실패: '.count($failed_settlements).'건'."\n".implode("\n", $failed_settlements);
    }
}

// alert 호출 전에 JavaScript용 문자열 이스케이프
if ($msg) {
    $msg = str_replace(["\\", "'", '"'], ["\\\\", "\\'", '\\"'], $msg);
    $msg = str_replace(["\r\n", "\r", "\n"], "\\n", $msg);
    alert($msg);
}

// 추가적인 검색조건
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./settlement_list.php?'.$qstr, false);

