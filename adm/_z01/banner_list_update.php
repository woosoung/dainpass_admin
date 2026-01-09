<?php
$sub_menu = "920650";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

if($w == 'u') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $bg = sql_fetch_pg(" SELECT * FROM banner_group WHERE bng_id = '".$_POST['bng_id'][$k]."' ");

        if (!$bg['bng_id']) {
            $msg .= $bg['bng_id'].' : 배너그룹 자료가 존재하지 않습니다.\\n';
        } else {
            // 상태 업데이트
            $status = isset($_POST['bng_status'][$k]) ? trim($_POST['bng_status'][$k]) : '';
            
            $sql = " UPDATE banner_group SET
                        bng_status = ".($status ? "'".addslashes($status)."'" : "'ok'").",
                        bng_update_at = CURRENT_TIMESTAMP
                    WHERE bng_id = '{$_POST['bng_id'][$k]}' ";
            sql_query_pg($sql);
        }
    }
}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $bng_id = $_POST['bng_id'][$k];
        $bg = sql_fetch_pg(" SELECT * FROM banner_group WHERE bng_id = '{$bng_id}' ");

        if (!$bg['bng_id']) {
            $msg .= $bng_id.' : 배너그룹 자료가 존재하지 않습니다.\\n';
            continue;
        }
        
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
            $sql = " DELETE FROM banner_group WHERE bng_id = '{$bng_id}' ";
        }
        else{
            // 레코드 삭제상태로 변경
            $sql = " UPDATE banner_group SET bng_status = 'del', bng_update_at = CURRENT_TIMESTAMP WHERE bng_id = '{$bng_id}' ";
        }
        sql_query_pg($sql, 1);
    }
}

if ($msg)
    alert($msg);

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

goto_url('./banner_list.php?'.$qstr, false);

