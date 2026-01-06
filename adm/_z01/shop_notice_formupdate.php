<?php
$sub_menu = "960100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

$mb_id = $member['mb_id'];

// 토큰 체크
check_admin_token();

// 입력 검증 - 화이트리스트 방식
$allowed_w = array('', 'u');
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$post_shopnotice_id = isset($_POST['shopnotice_id']) ? (int)$_POST['shopnotice_id'] : 0;
$post_shopnotice_id = ($post_shopnotice_id > 0 && $post_shopnotice_id <= 2147483647) ? $post_shopnotice_id : 0;

$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
$post_shop_id = ($post_shop_id > 0 && $post_shop_id <= 2147483647) ? $post_shop_id : 0;

$post_subject = isset($_POST['subject']) ? clean_xss_tags($_POST['subject']) : '';
$post_subject = trim($post_subject);
$post_subject = substr($post_subject, 0, 100); // 최대 길이 제한

$allowed_status = array('ok', 'pending');
$post_status = isset($_POST['status']) ? clean_xss_tags($_POST['status']) : 'ok';
$post_status = in_array($post_status, $allowed_status) ? $post_status : 'ok';

// 에디터 내용 처리
$is_dhtml_editor = false;
if ($config['cf_editor'] && (!is_mobile() || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE))) {
    $is_dhtml_editor = true;
}

$post_content = '';
// 에디터 라이브러리에 따라 필드명이 다를 수 있음 (tx_content 또는 content)
if ($is_dhtml_editor) {
    // cheditor5의 경우 tx_content 필드에 값이 들어감
    if (isset($_POST['tx_content'])) {
        $post_content = $_POST['tx_content'];
    } else if (isset($_POST['content'])) {
        $post_content = $_POST['content'];
    } else {
        $post_content = '';
    }
} else {
    $post_content = isset($_POST['content']) ? clean_xss_tags($_POST['content']) : '';
}

// content 길이 제한 (10MB)
if (strlen($post_content) > 10485760) {
    // qstr은 아직 생성되지 않았으므로 기본 리다이렉트만
    alert('내용이 너무 깁니다. (최대 10MB)', './shop_notice_form.php?w='.$w.($post_shopnotice_id ? '&shopnotice_id='.$post_shopnotice_id : ''));
    exit;
}

// content 내의 로컬 이미지 URL을 S3 URL로 변환
// 수정 모드일 때는 shopnotice_id 전달
$shopnotice_id_for_convert = 0;
if ($w == 'u' && isset($post_shopnotice_id) && $post_shopnotice_id > 0) {
    $shopnotice_id_for_convert = (int)$post_shopnotice_id;
}

if (!empty($post_content) && function_exists('convert_shop_notice_content_images_to_s3')) {
    $post_content = convert_shop_notice_content_images_to_s3($post_content, $shopnotice_id_for_convert);
}

// qstr 파라미터 검증
$allowed_sst = array('shopnotice_id', 'subject', 'status', 'create_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'subject', 'content', 'mb_id');
$allowed_sfl2 = array('', 'ok', 'pending');

$qs_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$qs_page = ($qs_page > 0 && $qs_page <= 10000) ? $qs_page : 1;

$qs_sst = isset($_POST['sst']) ? clean_xss_tags($_POST['sst']) : '';
$qs_sst = in_array($qs_sst, $allowed_sst) ? $qs_sst : '';

$qs_sod = isset($_POST['sod']) ? clean_xss_tags($_POST['sod']) : '';
$qs_sod = in_array($qs_sod, $allowed_sod) ? $qs_sod : '';

$qs_sfl = isset($_POST['sfl']) ? clean_xss_tags($_POST['sfl']) : '';
$qs_sfl = in_array($qs_sfl, $allowed_sfl) ? $qs_sfl : '';

$qs_stx = isset($_POST['stx']) ? clean_xss_tags($_POST['stx']) : '';
$qs_stx = substr($qs_stx, 0, 100);
$qs_stx = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $qs_stx);

$qs_sfl2 = isset($_POST['sfl2']) ? clean_xss_tags($_POST['sfl2']) : '';
$qs_sfl2 = in_array($qs_sfl2, $allowed_sfl2) ? $qs_sfl2 : '';

// qstr 생성
$qstr = '';
if ($qs_page > 1) {
    $qstr .= '&page=' . $qs_page;
}
if ($qs_sst) {
    $qstr .= '&sst=' . urlencode($qs_sst);
}
if ($qs_sod) {
    $qstr .= '&sod=' . urlencode($qs_sod);
}
if ($qs_sfl) {
    $qstr .= '&sfl=' . urlencode($qs_sfl);
}
if ($qs_stx) {
    $qstr .= '&stx=' . urlencode($qs_stx);
}
if ($qs_sfl2) {
    $qstr .= '&sfl2=' . urlencode($qs_sfl2);
}

// shop_id 검증
if ($post_shop_id != $shop_id) {
    alert('잘못된 가맹점 정보입니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    exit;
}

// 필수값 검증
if (!$post_subject || $post_subject == '') {
    alert('제목을 입력해주세요.', './shop_notice_form.php?w='.$w.($post_shopnotice_id ? '&shopnotice_id='.$post_shopnotice_id : '').($qstr ? '&'.ltrim($qstr, '&') : ''));
    exit;
}

// content는 에디터에서 가져온 값이므로 trim 처리
$post_content_trimmed = trim($post_content);
if (!$post_content_trimmed || $post_content_trimmed == '') {
    alert('내용을 입력해주세요.', './shop_notice_form.php?w='.$w.($post_shopnotice_id ? '&shopnotice_id='.$post_shopnotice_id : '').($qstr ? '&'.ltrim($qstr, '&') : ''));
    exit;
}

if ($w == 'u') {
    // 수정 모드
    if (!$post_shopnotice_id) {
        alert('공지사항 ID가 올바르지 않습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 기존 데이터 확인
    $exist_sql = " SELECT * FROM shop_notice 
                   WHERE shopnotice_id = {$post_shopnotice_id} 
                   AND shop_id = {$post_shop_id} ";
    $exist_row = sql_fetch_pg($exist_sql);
    
    if (!$exist_row || !$exist_row['shopnotice_id']) {
        alert('존재하지 않는 공지사항입니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 기존 content에서 사용 중인 S3 이미지 URL 추출
    $old_s3_images = array();
    if (!empty($exist_row['content']) && function_exists('extract_shop_notice_s3_images')) {
        $old_s3_images = extract_shop_notice_s3_images($exist_row['content']);
    }
    
    // UPDATE
    // PostgreSQL에서 TEXT 타입은 pg_escape_string 사용 (연결 리소스 명시 필요)
    $post_content_escaped = pg_escape_string($g5['connect_pg'], $post_content);
    $post_subject_escaped = pg_escape_string($g5['connect_pg'], $post_subject);
    
    $update_sql = " UPDATE shop_notice 
                    SET subject = '{$post_subject_escaped}', 
                        content = '{$post_content_escaped}', 
                        status = '{$post_status}',
                        update_at = CURRENT_TIMESTAMP
                    WHERE shopnotice_id = {$post_shopnotice_id} 
                    AND shop_id = {$post_shop_id} ";
    
    $result = sql_query_pg($update_sql);
    
    // 에러 체크
    if ($result === false) {
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('공지사항 수정 중 오류가 발생했습니다: ' . $error_msg, './shop_notice_form.php?w=u&shopnotice_id='.$post_shopnotice_id.($qstr ? '&'.ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 새 content에서 사용 중인 S3 이미지 URL 추출
    $new_s3_images = array();
    if (!empty($post_content) && function_exists('extract_shop_notice_s3_images')) {
        $new_s3_images = extract_shop_notice_s3_images($post_content);
    }
    
    // 사용되지 않는 이미지 찾기 (기존에 있지만 새 content에는 없는 이미지)
    $unused_images = array_diff($old_s3_images, $new_s3_images);
    
    // 사용되지 않는 이미지 삭제 (기존 방식 - content 비교)
    if (!empty($unused_images) && function_exists('delete_shop_notice_s3_images')) {
        delete_shop_notice_s3_images($unused_images);
    }
    
    // 추가: 게시물 디렉토리 전체를 스캔하여 content에 없는 이미지 모두 삭제
    // (content에서 이미지를 삭제했지만 S3에는 남아있는 경우를 처리)
    if (function_exists('delete_unused_shop_notice_s3_images')) {
        delete_unused_shop_notice_s3_images($post_shopnotice_id, $post_content);
    }
    
    alert('공지사항이 수정되었습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    // 등록 모드
    // PostgreSQL에서 TEXT 타입은 따옴표로 감싸야 하고, 특수문자 이스케이프 필요 (연결 리소스 명시 필요)
    $post_content_escaped = pg_escape_string($g5['connect_pg'], $post_content);
    $post_subject_escaped = pg_escape_string($g5['connect_pg'], $post_subject);
    $mb_id_escaped = pg_escape_string($g5['connect_pg'], $mb_id);
    // INSERT - shopnotice_id는 AUTO_INCREMENT이므로 제외 (PostgreSQL에서 자동으로 시퀀스 사용)
    // 이미지 설명에 따르면: shopnotice_id (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
    $insert_sql = " INSERT INTO shop_notice (shop_id, mb_id, subject, content, status, create_at, update_at) 
                    VALUES ({$post_shop_id}, '{$mb_id_escaped}', '{$post_subject_escaped}', '{$post_content_escaped}', '{$post_status}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ";
    
    $result = sql_query_pg($insert_sql);
    
    // 에러 체크
    if ($result === false) {
        // 에러 발생 시 상세 정보 출력 (개발 환경에서만)
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('공지사항 등록 중 오류가 발생했습니다: ' . $error_msg, './shop_notice_form.php?w=' . ($qstr ? '&'.ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 등록된 shopnotice_id 가져오기
    $new_shopnotice_id = sql_insert_id_pg('shop_notice');
    
    // 신규 등록 시 content의 이미지를 shopnotice_id 기반 경로로 업데이트
    // (임시로 년월 디렉토리에 저장된 이미지를 게시물별 디렉토리로 이동하고 content 업데이트)
    if ($new_shopnotice_id > 0 && !empty($post_content)) {
        // content에서 년월 기반 S3 이미지 찾기
        $s3_images = array();
        if (function_exists('extract_shop_notice_s3_images')) {
            $s3_images = extract_shop_notice_s3_images($post_content);
        }
        
        if (!empty($s3_images)) {
            global $set_conf;
            
            if (AWS_SDK_READY) {
                $s3 = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region'  => trim($set_conf['set_aws_region']),
                    'credentials' => [
                        'key'    => trim($set_conf['set_s3_accesskey']),
                        'secret' => trim($set_conf['set_s3_secretaccesskey']),
                    ]
                ]);
                
                $bucket = trim($set_conf['set_aws_bucket']);
                $updated_content = $post_content;
                $content_updated = false;
                
                // 게시물별 디렉토리 생성
                $notice_dir = "data/shop/notice/{$new_shopnotice_id}/";
                try {
                    $s3->putObject([
                        'Bucket' => $bucket,
                        'Key'    => $notice_dir,
                        'Body'   => '',
                    ]);
                } catch (\Exception $e) {
                    // 디렉토리 생성 실패는 무시
                }
                
                $moved_ym_dirs = array(); // 이동된 년월 디렉토리 추적
                
                foreach ($s3_images as $old_key) {
                    // 년월 기반 경로인지 확인 (임시 저장된 이미지)
                    if (preg_match('/data\/shop\/notice\/(\d{4})\/(.+)/', $old_key, $match)) {
                        $ym = $match[1];
                        $filename = $match[2];
                        $new_key = "data/shop/notice/{$new_shopnotice_id}/{$filename}";
                        
                        // 이동된 년월 디렉토리 기록
                        if (!in_array($ym, $moved_ym_dirs)) {
                            $moved_ym_dirs[] = $ym;
                        }
                        
                        try {
                            // S3에서 객체 복사 (이동)
                            $s3->copyObject([
                                'Bucket'     => $bucket,
                                'CopySource' => "{$bucket}/{$old_key}",
                                'Key'        => $new_key,
                            ]);
                            
                            // 원본 삭제
                            $s3->deleteObject([
                                'Bucket' => $bucket,
                                'Key'    => $old_key,
                            ]);
                            
                            // content에서 URL 업데이트
                            $old_url_pattern = preg_quote($old_key, '/');
                            $updated_content = preg_replace('/' . $old_url_pattern . '/', $new_key, $updated_content);
                            $content_updated = true;
                            
                        } catch (\Exception $e) {
                            // 이동 실패 시 로그만 기록
                            error_log("Failed to move S3 image: {$old_key} - " . $e->getMessage());
                        }
                    }
                }
                
                // 이동이 완료된 년월 디렉토리가 비어있는지 확인하고 삭제
                foreach ($moved_ym_dirs as $ym) {
                    $ym_dir = "data/shop/notice/{$ym}/";
                    try {
                        // 해당 디렉토리의 객체 목록 확인
                        $objects = $s3->listObjectsV2([
                            'Bucket' => $bucket,
                            'Prefix' => $ym_dir,
                        ]);
                        
                        // 디렉토리 자체를 제외하고 실제 파일이 없으면 디렉토리 삭제
                        $has_files = false;
                        if (isset($objects['Contents'])) {
                            foreach ($objects['Contents'] as $object) {
                                if ($object['Key'] !== $ym_dir) {
                                    $has_files = true;
                                    break;
                                }
                            }
                        }
                        
                        // 파일이 없으면 디렉토리 삭제
                        if (!$has_files) {
                            $s3->deleteObject([
                                'Bucket' => $bucket,
                                'Key'    => $ym_dir,
                            ]);
                        }
                    } catch (\Exception $e) {
                        // 디렉토리 삭제 실패는 무시
                    }
                }
                
                // content가 업데이트되었으면 DB에 반영
                if ($content_updated) {
                    $updated_content_escaped = pg_escape_string($g5['connect_pg'], $updated_content);
                    $update_content_sql = " UPDATE shop_notice 
                                            SET content = '{$updated_content_escaped}'
                                            WHERE shopnotice_id = {$new_shopnotice_id} 
                                            AND shop_id = {$post_shop_id} ";
                    sql_query_pg($update_content_sql);
                }
            }
        }
    }
    
    alert('공지사항이 등록되었습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>

