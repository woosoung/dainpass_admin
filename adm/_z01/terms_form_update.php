<?php
$sub_menu = "910280";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

check_admin_token();

// 여러 약관을 한 번에 처리
if (isset($_POST['terms']) && is_array($_POST['terms'])) {
    $terms = $_POST['terms'];
    $success_count = 0;
    $error_messages = array();
    
    foreach ($terms as $index => $term_data) {
        $st_id = isset($term_data['st_id']) ? (int)$term_data['st_id'] : 0;
        $st_code = isset($term_data['st_code']) ? trim($term_data['st_code']) : '';
        $st_title = isset($term_data['st_title']) ? trim($term_data['st_title']) : '';
        $st_version = isset($term_data['st_version']) ? trim($term_data['st_version']) : '';
        $st_content_field = isset($term_data['st_content_field']) ? trim($term_data['st_content_field']) : 'st_content_'.$index;
        $st_content = isset($_POST[$st_content_field]) ? $_POST[$st_content_field] : '';
        $st_required = isset($term_data['st_required']) ? trim($term_data['st_required']) : 'Y';
        $st_order = isset($term_data['st_order']) ? (int)$term_data['st_order'] : 1;
        $st_summary = isset($term_data['st_summary']) ? trim($term_data['st_summary']) : '';
        
        // 필수 입력값 검증
        if (empty($st_code)) {
            $error_messages[] = "약관코드가 입력되지 않았습니다. (항목 #".($index + 1).")";
            continue;
        }
        
        if (empty($st_title)) {
            $error_messages[] = "약관제목이 입력되지 않았습니다. (항목 #".($index + 1).": ".$st_code.")";
            continue;
        }
        
        if (empty($st_version)) {
            $error_messages[] = "버전이 입력되지 않았습니다. (항목 #".($index + 1).": ".$st_code.")";
            continue;
        }
        
        if ($st_order < 1) {
            $error_messages[] = "순서는 1 이상이어야 합니다. (항목 #".($index + 1).": ".$st_code.")";
            continue;
        }
        
        // 입력값 정리
        $st_code = addslashes($st_code);
        $st_title = addslashes($st_title);
        $st_version = addslashes($st_version);
        $st_content = addslashes($st_content);
        $st_required = addslashes($st_required);
        $st_summary = addslashes($st_summary);
        
        if ($st_id > 0) {
            // 기존 약관 수정
            // 중복 체크 (본인 제외)
            $sql = " SELECT st_id FROM {$g5['service_terms_table']} WHERE st_code = '{$st_code}' AND st_id != '{$st_id}' ";
            $row = sql_fetch_pg($sql);
            
            if ($row && $row['st_id']) {
                $error_messages[] = "이미 존재하는 약관코드입니다. (항목 #".($index + 1).": ".$st_code.")";
                continue;
            }
            
            $sql = " UPDATE {$g5['service_terms_table']} 
                     SET st_code = '{$st_code}',
                         st_title = '{$st_title}',
                         st_version = '{$st_version}',
                         st_content = '{$st_content}',
                         st_required = '{$st_required}',
                         st_order = '{$st_order}',
                         st_summary = '{$st_summary}'
                     WHERE st_id = '{$st_id}' ";
            
            sql_query_pg($sql);
            $success_count++;
        } else {
            // 신규 약관 등록
            // 중복 체크
            $sql = " SELECT st_id FROM {$g5['service_terms_table']} WHERE st_code = '{$st_code}' ";
            $row = sql_fetch_pg($sql);
            
            if ($row && $row['st_id']) {
                $error_messages[] = "이미 존재하는 약관코드입니다. (항목 #".($index + 1).": ".$st_code.")";
                continue;
            }
            
            // st_id는 시퀀스에서 자동 생성되므로 INSERT 시 제외
            $sql = " INSERT INTO {$g5['service_terms_table']} 
                     (st_code, st_title, st_version, st_content, st_required, st_order, st_summary, st_created_at)
                     VALUES 
                     ('{$st_code}', '{$st_title}', '{$st_version}', '{$st_content}', '{$st_required}', '{$st_order}', '{$st_summary}', CURRENT_TIMESTAMP) ";
            
            sql_query_pg($sql);
            $success_count++;
        }
    }
    
    // 결과 메시지 생성
    $msg_parts = array();
    if ($success_count > 0) {
        $msg_parts[] = $success_count."개의 약관이 저장되었습니다.";
    }
    if (!empty($error_messages)) {
        $msg_parts[] = "오류: ".implode("\\n", $error_messages);
    }
    
    $msg = !empty($msg_parts) ? implode("\\n\\n", $msg_parts) : "저장된 내용이 없습니다.";
    
    if (!empty($error_messages)) {
        // 오류가 있으면 메시지를 보여주고 페이지를 새로고침하지 않음
        echo "<script>alert('".$msg."'); history.back();</script>";
        exit;
    } else {
        alert($msg, './terms_form.php');
    }
} else {
    alert('저장할 약관 정보가 없습니다.', './terms_form.php');
}
