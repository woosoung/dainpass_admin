<?php
include_once('./_common.php');
// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

// 입력값 안전하게 수신 및 검증
$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';

// 기본 응답: 0 (사용불가)
$status = '0';

// 서버 측 유효성 검사 (영문 시작, 영문숫자 5~20자)
if ($mb_id === '' || !preg_match('/^[A-Za-z][A-Za-z0-9]{4,19}$/', $mb_id)) {
    echo $status; // 형식이 틀리면 사용불가 처리
    exit;
}

// 존재 여부 조회 (행이 없을 때 경고가 출력되지 않도록 가드)
$row = sql_fetch(" SELECT mb_no FROM {$g5['member_table']} WHERE mb_id = '".$mb_id."' ");
$mb_no = isset($row['mb_no']) ? (int)$row['mb_no'] : 0;

// 존재하지 않으면 1(사용가능), 존재하면 0(사용불가)
$status = $mb_no ? '0' : '1';

echo $status;
// exit;