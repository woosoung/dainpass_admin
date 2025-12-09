<?php
include_once('./_common.php');

// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

// 입력값 안전하게 수신 및 검증
$user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';

// 기본 응답: 0 (사용불가)
$status = '0';

// 서버 측 유효성 검사
if ($user_id === '') {
    echo $status;
    exit;
}

// customers 테이블에서 status가 'active'이고 user_id가 일치하는 회원 확인
$sql = " SELECT customer_id 
         FROM customers 
         WHERE user_id = '".addslashes($user_id)."' 
         AND status = 'active' 
         LIMIT 1 ";
$row = sql_fetch_pg($sql);

// 존재하고 활성화된 회원이면 1(사용가능), 아니면 0(사용불가)
$status = ($row && isset($row['customer_id']) && $row['customer_id']) ? '1' : '0';

echo $status;
exit;
?>
