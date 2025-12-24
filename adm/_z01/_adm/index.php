<?php
$sub_menu = '100000';
require_once './_common.php';

@require_once G5_ADMIN_PATH.'/safe_check.php';


$g5['title'] = '관리자메인';
require_once G5_ADMIN_PATH.'/admin.head.php';

if ($is_member && $member['mb_id']) {

    if($is_ultra == false){ // 작업 완료되면 이 조건문은 주석처리 해야 한다.
        echo '<div class="local_desc01 local_desc text-center py-[200px]">';
        echo '<p>작업중인 페이지 입니다.</p>';
        echo '</div>';
        include_once(G5_ADMIN_PATH.'/admin.tail.php');
        exit;
    }
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date ".
              " FROM {$g5['member_table']} ".
              " WHERE mb_id = '{$member['mb_id']}' ".
              " AND mb_level >= 4 ".
              " AND ( ".
              "     mb_level >= 6 ".
              "     OR (mb_level < 6 AND mb_2 = 'Y') ".
              " ) ".
              " AND (mb_leave_date = '' OR mb_leave_date IS NULL) ".
              " AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);

    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);

        // mb_1 = '0'인 경우: 플랫폼 관리자 (가맹점 지정 안됨)
        if ($mb_1_value === '0' || $mb_1_value === '') {
            include_once(G5_ZADM_PATH.'/index_plt_mng.php');
        }

        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, shop_name, name, status ".
                       " FROM {$g5['shop_table']} ".
                       " WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);

            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending'){
                    if($is_manager){
                        echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                        echo '<p>아직 승인이 되지 않은 업체입니다.</p>';
                        echo '</div>';
                    }else{
                        alert('아직 승인이 되지 않았습니다.');
                    }
                }
                if ($shop_row['status'] == 'closed'){
                    if($is_manager){
                        echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                        echo '<p>폐업된 업체입니다.</p>';
                        echo '</div>';
                    }else{
                        alert('폐업되었습니다.');
                    }
                }
                if ($shop_row['status'] == 'shutdown'){
                    if($is_manager){
                        echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                        echo '<p>접근이 제한된 업체입니다.</p>';
                        echo '</div>';
                    }else{
                        alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                    }
                }

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_info = $shop_row;

                include_once(G5_ZADM_PATH.'/index_shop_mng.php');
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
            }
        }
    }
}
else{
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>관리자 로그인 후 이용해주세요.</p>';
    echo '</div>';
}

require_once G5_ADMIN_PATH.'/admin.tail.php';