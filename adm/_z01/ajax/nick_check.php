<?php
include_once('./_common.php');

// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

// 1) 우선 x-www-form-urlencoded(표준 폼)에서 수신
$mb_nick = isset($_POST['mb_nick']) ? trim($_POST['mb_nick']) : '';
$mb_id   = isset($_POST['mb_id'])   ? trim($_POST['mb_id'])   : '';
$w       = isset($_POST['w'])       ? trim($_POST['w'])       : '';

// 2) 폴백: JSON으로 전달된 경우 지원
if ($mb_nick === '' && empty($_POST)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $d = json_decode($raw, true);
        if (is_array($d)) {
            $mb_nick = isset($d['mb_nick']) ? trim($d['mb_nick']) : $mb_nick;
            $mb_id   = isset($d['mb_id'])   ? trim($d['mb_id'])   : $mb_id;
            $w       = isset($d['w'])       ? trim($d['w'])       : $w;
        }
    }
}

// 기본 응답: 0 (사용불가)
$status = '0';

// 서버측 유효성 검사: 한글/영문으로 시작, 이후 한글/영문/숫자 조합 허용
// (프론트 패턴: /^[a-zA-Z가-힣]+[a-zA-Z가-힣0-9]*$/)
if ($mb_nick === '' || !preg_match('/^[A-Za-z\x{AC00}-\x{D7A3}][A-Za-z0-9\x{AC00}-\x{D7A3}]*$/u', $mb_nick)) {
    echo $status; // 형식이 틀리면 사용불가 처리
    exit;
}

// 업데이트 모드에서는 본인 아이디는 제외하고 중복 검사
$mb_id_where = ($w === 'u' && $mb_id !== '') ? " AND mb_id <> '".$mb_id."' " : '';

// 존재 여부 조회 (행이 없을 때 경고가 출력되지 않도록 가드)
$row = sql_fetch(" SELECT mb_no FROM {$g5['member_table']} WHERE mb_nick = '".$mb_nick."' {$mb_id_where} ");
$mb_no = isset($row['mb_no']) ? (int)$row['mb_no'] : 0;

// 존재하지 않으면 1(사용가능), 존재하면 0(사용불가)
$status = $mb_no ? '0' : '1';

echo $status;