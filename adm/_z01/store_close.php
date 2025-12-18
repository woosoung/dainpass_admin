<?php
$sub_menu = "930100";
include_once("./_common.php");

@auth_check($auth[$sub_menu], 'd');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;
$is_platform_admin = false; // 플랫폼 관리자 여부

if ($is_member && $member['mb_id']) {
    // 회원 정보 확인
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date
                FROM {$g5['member_table']}
                WHERE mb_id = '{$member['mb_id']}' ";

    $mb_row = sql_fetch($mb_sql, 1);

    if ($mb_row && $mb_row['mb_id']) {
        $is_platform_admin = ($mb_row['mb_level'] >= 6); // 플랫폼 관리자 여부

        // 플랫폼 관리자가 아닌 경우에만 추가 검증
        if (!$is_platform_admin) {
            // mb_level 5 미만이면 접근 불가 (가맹점 오너만 탈퇴 가능)
            if ($mb_row['mb_level'] < 5) {
                alert('가맹점 오너만 탈퇴 처리를 할 수 있습니다.');
            }

            // 이미 탈퇴한 회원인지 확인
            if (!empty($mb_row['mb_leave_date'])) {
                alert('이미 탈퇴한 회원입니다.');
            }
        }

        $mb_1 = trim($mb_row['mb_1']);

        // 플랫폼 관리자인 경우: POST로 전달받은 shop_id 사용
        if ($is_platform_admin) {
            // POST로 전달된 shop_id 확인
            $post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
            if (!$post_shop_id) {
                alert('탈퇴할 가맹점을 선택해 주세요.');
            }

            // PostgreSQL에서 shop_id 확인
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$post_shop_id} ";
            $shop_row = sql_fetch_pg($shop_sql);

            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_status = $shop_row['status'];
            } else {
                alert('업체 데이터가 없습니다.');
            }
        }
        // 가맹점 오너인 경우: mb_1의 shop_id 사용
        else {
            // mb_1 = '0'인 경우: 오류
            if ($mb_1 === '0' || $mb_1 === '') {
                alert('업체 데이터가 없습니다.');
            }

            // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
            if (!empty($mb_1)) {
                // PostgreSQL에서 shop_id 확인
                $shop_id_check = (int)$mb_1;
                $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
                $shop_row = sql_fetch_pg($shop_sql);

                if ($shop_row && $shop_row['shop_id']) {
                    $has_access = true;
                    $shop_id = (int)$shop_row['shop_id'];
                    $shop_status = $shop_row['status'];
                } else {
                    alert('업체 데이터가 없습니다.');
                }
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('잘못된 접근입니다.');
}

// POST로 전달된 shop_id 확인
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;

// 가맹점 오너는 자신의 가맹점만 탈퇴 가능 (플랫폼 관리자는 모든 가맹점 탈퇴 가능)
if (!$is_platform_admin && $post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

// 이미 탈퇴된 가맹점인지 확인
if ($shop_status === 'closed') {
    alert('이미 탈퇴 처리된 가맹점입니다.');
}

// 트랜잭션 시작을 위한 변수
$pg_success = false;
$mysql_success = false;

try {
    // PostgreSQL 트랜잭션 시작
    sql_query_pg("BEGIN");

    // 1. PostgreSQL: shop 테이블 status 업데이트
    $update_shop_sql = " UPDATE {$g5['shop_table']}
                         SET status = 'closed',
                             updated_at = '".G5_TIME_YMDHIS."'
                         WHERE shop_id = {$shop_id} ";

    if (!sql_query_pg($update_shop_sql)) {
        throw new Exception('가맹점 정보 업데이트에 실패했습니다.');
    }

    // PostgreSQL 커밋
    sql_query_pg("COMMIT");
    $pg_success = true;

    // MySQL 트랜잭션 시작
    sql_query("START TRANSACTION", 1);

    // 2. MySQL: 해당 shop_id를 가진 모든 회원 업데이트
    // - mb_1 필드에 shop_id가 저장되어 있음
    // - 가맹점 오너 (mb_level=5) 및 가맹점 관리자 (mb_level<=4) 모두 포함
    $current_datetime = date("Ymd", strtotime(G5_TIME_YMD));

    // 탈퇴 처리 주체에 따라 메모 텍스트 구분
    $memo_text = "";

    if ($is_platform_admin) {
        $memo_text = "[{$current_datetime}] 플랫폼 관리자에 의한 가맹점 탈퇴 처리";
    } else {
        $memo_text = "[{$current_datetime}] 가맹점 탈퇴로 인한 계정 비활성화";
    }

    // mb_memo에 개행 문자가 있으면 그대로 사용, 없으면 추가
    $update_member_sql = " UPDATE {$g5['member_table']}
                           SET mb_level = 1,
                               mb_leave_date = '{$current_datetime}',
                               mb_memo = CASE
                                   WHEN mb_memo IS NULL OR mb_memo = '' THEN '{$memo_text}'
                                   ELSE CONCAT(mb_memo, '\n{$memo_text}')
                               END
                           WHERE mb_1 = '{$shop_id}' ";

    // 플랫폼 관리자가 처리하는 경우, 플랫폼 관리자(mb_level >= 6)는 탈퇴 대상에서 제외
    if ($is_platform_admin) {
        $update_member_sql .= " AND mb_level < 6 ";
    }

    if (!sql_query($update_member_sql, 1)) {
        throw new Exception('회원 정보 업데이트에 실패했습니다.');
    }

    // 회원이 없어도 정상 처리 (shop만 탈퇴)

    // MySQL 커밋
    sql_query("COMMIT", 1);
    $mysql_success = true;

} catch (Exception $e) {
    // 오류 발생 시 롤백
    if ($pg_success) {
        // PostgreSQL 롤백 (이미 커밋되었으므로 롤백 불가 - 보상 트랜잭션 필요)
        sql_query_pg("UPDATE {$g5['shop_table']} SET status = '{$shop_status}' WHERE shop_id = {$shop_id}");
    } else {
        sql_query_pg("ROLLBACK");
    }

    if (!$mysql_success) {
        sql_query("ROLLBACK", 1);
    }

    alert($e->getMessage());
}

// 가맹점 오너가 처리한 경우: 세션 파기 및 로그아웃 처리
// 플랫폼 관리자가 처리한 경우: 로그아웃하지 않음
if (!$is_platform_admin) {
    // 세션 파기 및 로그아웃 처리
    session_destroy();
    set_session('', '');

    // 쿠키 삭제
    setcookie('ck_mb_id', '', time()-3600, '/', G5_COOKIE_DOMAIN);
    setcookie(session_name(), '', time()-3600, '/', G5_COOKIE_DOMAIN);
}

// 메인으로 이동
alert('가맹점 탈퇴 처리가 완료되었습니다.', G5_URL);
