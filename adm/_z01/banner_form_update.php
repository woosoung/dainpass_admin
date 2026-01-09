<?php
$sub_menu = '920650';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$bng_id = isset($_REQUEST['bng_id']) ? (int)$_REQUEST['bng_id'] : 0;

if( ! $bng_id && $w == 'u' ){
    alert('배너그룹 ID가 없습니다.', './banner_list.php');
}

if( $bng_id && $w == 'u' ){
    $sql = " SELECT * FROM banner_group WHERE bng_id = '$bng_id' ";
    $bg = sql_fetch_pg($sql);

    if ($bg && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

if ($w == "u" || $w == "d")
    check_demo();

if ($w == "d")
    auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

// 입력값 받기
$bng_code = isset($_POST['bng_code']) ? trim($_POST['bng_code']) : '';
$bng_name = isset($_POST['bng_name']) ? trim($_POST['bng_name']) : '';
$bng_desc = isset($_POST['bng_desc']) ? trim($_POST['bng_desc']) : '';
$bng_desc = ($bng_desc == '') ? 'NULL' : "'".addslashes($bng_desc)."'";
$bng_start_dt = isset($_POST['bng_start_dt']) ? trim($_POST['bng_start_dt']) : '';
$bng_start_dt = ($bng_start_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bng_start_dt).":00'";
$bng_end_dt = isset($_POST['bng_end_dt']) ? trim($_POST['bng_end_dt']) : '';
$bng_end_dt = ($bng_end_dt == '') ? 'NULL' : "'".str_replace('T', ' ', $bng_end_dt).":00'";
$bng_status = isset($_POST['bng_status']) ? trim($_POST['bng_status']) : 'ok';
$bng_status = ($bng_status == '') ? "'ok'" : "'".addslashes($bng_status)."'";

// 유효성 검사
if (!$bng_code) {
    alert('배너그룹 코드를 입력해 주세요.');
}

// 코드 형식 검증
if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $bng_code)) {
    alert('배너그룹 코드는 영문으로 시작하고 영문, 숫자, 언더스코어(_)만 사용 가능합니다.');
}

if (!$bng_name) {
    alert('배너그룹명을 입력해 주세요.');
}

if ($w == "") {
    // 신규 등록: 중복 체크
    $sql = " SELECT COUNT(*) AS cnt FROM banner_group WHERE bng_code = '".addslashes($bng_code)."' ";
    $row = sql_fetch_pg($sql);
    if ($row['cnt']) {
        alert('이미 존재하는 배너그룹 코드입니다.');
    }

    $sql = " INSERT INTO banner_group 
                (bng_code, bng_name, bng_desc, bng_start_dt, bng_end_dt, bng_status, bng_created_at, bng_update_at)
              VALUES 
                ('".addslashes($bng_code)."', '".addslashes($bng_name)."', ".$bng_desc.", ".$bng_start_dt.", ".$bng_end_dt.", ".$bng_status.", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ";
    sql_query_pg($sql);
    $bng_id = sql_insert_id_pg('banner_group');
} else if ($w == "u") {
    // 수정
    $sql_common = " bng_name = '".addslashes($bng_name)."',
                    bng_desc = ".$bng_desc.",
                    bng_start_dt = ".$bng_start_dt.",
                    bng_end_dt = ".$bng_end_dt.",
                    bng_status = ".$bng_status.",
                    bng_update_at = CURRENT_TIMESTAMP ";
    
    $sql = " UPDATE banner_group 
                SET {$sql_common}
              WHERE bng_id = '$bng_id' ";
    sql_query_pg($sql);
}
else if ($w == "d")
{
    // 1. 배너그룹 이미지 파일 삭제 (S3 및 dain_file 테이블)
    // PC용 이미지 삭제
    if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
        $bng_del_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                         FROM {$g5['dain_file_table']}
                         WHERE fle_db_tbl = 'banner_group'
                         AND fle_db_idx = '{$bng_id}'
                         AND fle_type = 'bng_img'
                         AND fle_dir = 'plt/banner' ";
        $bng_del_row = @sql_fetch_pg($bng_del_sql, 0);
        if ($bng_del_row && !empty($bng_del_row['fle_idxs'])) {
            $bng_fle_idx_array = explode(',', $bng_del_row['fle_idxs']);
            if (!empty($bng_fle_idx_array) && is_array($bng_fle_idx_array)) {
                // S3 및 dain_file 테이블에서 파일 삭제
                delete_idx_s3_file($bng_fle_idx_array);
            }
        }
        
        // 모바일용 이미지 삭제
        $bng_mo_del_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                            FROM {$g5['dain_file_table']}
                            WHERE fle_db_tbl = 'banner_group'
                            AND fle_db_idx = '{$bng_id}'
                            AND fle_type = 'bng_mo_img'
                            AND fle_dir = 'plt/banner' ";
        $bng_mo_del_row = @sql_fetch_pg($bng_mo_del_sql, 0);
        if ($bng_mo_del_row && !empty($bng_mo_del_row['fle_idxs'])) {
            $bng_mo_fle_idx_array = explode(',', $bng_mo_del_row['fle_idxs']);
            if (!empty($bng_mo_fle_idx_array) && is_array($bng_mo_fle_idx_array)) {
                // S3 및 dain_file 테이블에서 파일 삭제
                delete_idx_s3_file($bng_mo_fle_idx_array);
            }
        }
    } else {
        // dain_file_table이 없으면 기본 함수 사용
        delete_db_s3_file('banner_group', $bng_id, 'bng_img');
        delete_db_s3_file('banner_group', $bng_id, 'bng_mo_img');
    }
    
    // 2. 해당 bng_id를 가진 모든 배너(banner) 레코드 조회
    $banner_sql = " SELECT bnr_id FROM banner WHERE bng_id = '{$bng_id}' ";
    $banner_result = @sql_query_pg($banner_sql, 0);
    
    if ($banner_result && is_object($banner_result) && isset($banner_result->result)) {
        // 각 배너의 이미지 파일 삭제 및 레코드 삭제
        while ($banner_row = sql_fetch_array_pg($banner_result->result)) {
            $bnr_id = $banner_row['bnr_id'];
            
            // 2-1. 각 배너의 이미지 파일 삭제 (S3 및 dain_file 테이블)
            // PC용 이미지 삭제
            if (isset($g5['dain_file_table']) && !empty($g5['dain_file_table'])) {
                $del_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                             FROM {$g5['dain_file_table']}
                             WHERE fle_db_tbl = 'banner'
                             AND fle_db_idx = '{$bnr_id}'
                             AND fle_type = 'banner_img'
                             AND fle_dir = 'plt/banner' ";
                $del_row = @sql_fetch_pg($del_sql, 0);
                if ($del_row && !empty($del_row['fle_idxs'])) {
                    $fle_idx_array = explode(',', $del_row['fle_idxs']);
                    if (!empty($fle_idx_array) && is_array($fle_idx_array)) {
                        // S3 및 dain_file 테이블에서 파일 삭제
                        delete_idx_s3_file($fle_idx_array);
                    }
                }
                
                // 모바일용 이미지 삭제
                $del_mo_sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                                FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'banner'
                                AND fle_db_idx = '{$bnr_id}'
                                AND fle_type = 'banner_mo_img'
                                AND fle_dir = 'plt/banner' ";
                $del_mo_row = @sql_fetch_pg($del_mo_sql, 0);
                if ($del_mo_row && !empty($del_mo_row['fle_idxs'])) {
                    $fle_mo_idx_array = explode(',', $del_mo_row['fle_idxs']);
                    if (!empty($fle_mo_idx_array) && is_array($fle_mo_idx_array)) {
                        // S3 및 dain_file 테이블에서 파일 삭제
                        delete_idx_s3_file($fle_mo_idx_array);
                    }
                }
            } else {
                // dain_file_table이 없으면 기본 함수 사용
                delete_db_s3_file('banner', $bnr_id, 'banner_img');
                delete_db_s3_file('banner', $bnr_id, 'banner_mo_img');
            }
            
            // 2-2. banner 테이블에서 해당 bnr_id 레코드 삭제
            if($set_conf['set_del_yn']){
                $banner_del_sql = " DELETE FROM banner WHERE bnr_id = '{$bnr_id}' ";
            } else {
                $banner_del_sql = " UPDATE banner SET bnr_status = 'del', bnr_update_at = CURRENT_TIMESTAMP WHERE bnr_id = '{$bnr_id}' ";
            }
            @sql_query_pg($banner_del_sql, 0);
        }
    }

    // 3. banner_group 테이블에서 해당 bng_id 레코드 삭제
    if($set_conf['set_del_yn']){
        // 레코드 삭제
        $sql = " DELETE FROM banner_group WHERE bng_id = '$bng_id' ";
    }
    else{
        // 레코드 삭제상태로 변경
        $sql = " UPDATE banner_group SET bng_status = 'del', bng_update_at = CURRENT_TIMESTAMP WHERE bng_id = '$bng_id' ";
    }
    sql_query_pg($sql);
}

// 파일 처리
if($w == '' || $w == 'u'){
    // PC용 이미지 삭제처리
    $merge_del = array();
    $del_arr = array();

    $bng_img_del = isset($_POST['bng_img_'.$bng_id.'_del']) && is_array($_POST['bng_img_'.$bng_id.'_del']) ? $_POST['bng_img_'.$bng_id.'_del'] : array();

    if(!empty($bng_img_del)){
        foreach($bng_img_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    
    // 모바일용 이미지 삭제처리
    $bng_mo_img_del = isset($_POST['bng_mo_img_'.$bng_id.'_del']) && is_array($_POST['bng_mo_img_'.$bng_id.'_del']) ? $_POST['bng_mo_img_'.$bng_id.'_del'] : array();

    if(!empty($bng_mo_img_del)){
        foreach($bng_mo_img_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    
    if(!empty($del_arr)) delete_idx_s3_file($del_arr);
    
    // 배너그룹 PC용 이미지 업로드
    upload_multi_file($_FILES['banner_group_img'],'banner_group',$bng_id,'plt/banner','bng_img');
    
    // 배너그룹 모바일용 이미지 업로드
    upload_multi_file($_FILES['banner_group_mo_img'],'banner_group',$bng_id,'plt/banner','bng_mo_img');
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./banner_form.php?w=u&amp;bng_id=$bng_id&amp;$qstr");
} else {
    goto_url("./banner_list.php?$qstr");
}

