<?php
$sub_menu = "920200";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if($w == 'u') {
    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch_pg(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '".$_POST['shop_id'][$k]."' ");
		// $mb = get_member($com['mb_id']);

        $shop_id = $com["shop_id"];
        
        $sql = " UPDATE {$g5['shop_table']} SET
                    status = '{$_POST['status'][$k]}'
                WHERE shop_id = '{$shop_id}' ";
        sql_query_pg($sql);
    }
}
// 폐업처리 (논리적 삭제)
else if($w == 'd') {
    auth_check($auth[$sub_menu], 'd');

    $failed_shops = array();
    $success_count = 0;

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch_pg(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '".$_POST['shop_id'][$k]."' ");

        if (!$com || !$com["shop_id"]) {
            $failed_shops[] = '가맹점 자료가 존재하지 않습니다. (ID: '.$_POST['shop_id'][$k].')';
            continue;
        }

        $shop_id = $com["shop_id"];
        $shop_name = $com["name"] ? $com["name"] : $com["shop_name"];
        $original_status = $com["status"];

        // 이미 폐업된 가맹점 체크
        if ($original_status === 'closed') {
            $failed_shops[] = $shop_name.' : 이미 폐업 처리된 가맹점입니다. (ID: '.$_POST['shop_id'][$k].')';
            continue;
        }

        $pg_success = false;
        $mysql_success = false;

        try {
            // PostgreSQL 트랜잭션 시작
            sql_query_pg("BEGIN");

            // 1. PostgreSQL shop 테이블 status 업데이트
            $update_shop_sql = " UPDATE {$g5['shop_table']}
                                SET status = 'closed',
                                    updated_at = '".G5_TIME_YMDHIS."'
                                WHERE shop_id = '{$shop_id}' ";

            if (!sql_query_pg($update_shop_sql)) {
                throw new Exception($shop_name.' : 가맹점 상태 업데이트에 실패했습니다.');
            }

            // PostgreSQL 커밋
            sql_query_pg("COMMIT");
            $pg_success = true;

            // MySQL 트랜잭션 시작
            sql_query("START TRANSACTION", 1);

            // 해당 shop_id 가진 모든 회원 업데이트
            $current_datetime = date("Ymd", strtotime(G5_TIME_YMD));
            $memo_text = "[{$current_datetime}] 플랫폼 관리자에 의한 가맹점 탈퇴 처리";

            $update_member_sql = " UPDATE {$g5['member_table']}
                                    SET mb_leave_date = '{$current_datetime}',
                                        mb_memo = CASE
                                                    WHEN mb_memo LIKE '%{$memo_text}%' THEN mb_memo
                                                    ELSE CONCAT(mb_memo, '\n', '{$memo_text}')
                                                END
                                    WHERE mb_1 = '{$shop_id}'
                                    AND mb_level < 6 ";

            if (!sql_query($update_member_sql, 1)) {
                throw new Exception($shop_name.' : 회원 정보 업데이트에 실패했습니다.');
            }

            // MySQL 커밋
            sql_query("COMMIT", 1);
            $mysql_success = true;

            $success_count++;

        } catch (Exception $e) {
            // 오류 발생 시 롤백
            if ($pg_success) {
                // PostgreSQL 롤백 (이미 커밋되었으므로 보상 트랜잭션 수행)
                $rollback_sql = " UPDATE {$g5['shop_table']}
                                SET status = '{$original_status}',
                                    updated_at = '".G5_TIME_YMDHIS."'
                                WHERE shop_id = '{$shop_id}' ";
                sql_query_pg($rollback_sql);
            } else {
                sql_query_pg("ROLLBACK");
            }

            if (!$mysql_success) {
                sql_query("ROLLBACK", 1);
            }

            $failed_shops[] = $e->getMessage();
        }
    }

    // 결과 메시지 생성
    if ($success_count > 0 && count($failed_shops) == 0) {
        // 모두 성공
        $msg = '폐업 처리가 완료되었습니다. ('.$success_count.'건)';
    } else if ($success_count > 0 && count($failed_shops) > 0) {
        // 일부 성공, 일부 실패
        $msg = '폐업 처리 완료: '.$success_count.'건'."\n\n".'실패: '.count($failed_shops).'건'."\n".implode("\n", $failed_shops);
    } else if (count($failed_shops) > 0) {
        // 모두 실패
        $msg = '폐업 처리 실패: '.count($failed_shops).'건'."\n".implode("\n", $failed_shops);
    }
}

// alert 호출 전에 JavaScript용 문자열 이스케이프
if ($msg) {
    // 백슬래시, 작은따옴표, 큰따옴표 이스케이프
    $msg = str_replace(["\\", "'", '"'], ["\\\\", "\\'", '\\"'], $msg);
    // 개행 문자를 JavaScript 개행으로 변환
    $msg = str_replace(["\r\n", "\r", "\n"], "\\n", $msg);
    alert($msg);
}

    
// 추가적인 검색조건
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
    //    print_r3($key.'='.$value);
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
//                print_r3($key.$k2.'='.$v2);
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./company_list.php?'.$qstr, false);