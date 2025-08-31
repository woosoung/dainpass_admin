<?php
include_once('./_common.php');

// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

// 1) 우선 x-www-form-urlencoded(표준 폼)에서 수신
$mb_hp = isset($_POST['mb_hp']) ? trim($_POST['mb_hp']) : '';
$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$w     = isset($_POST['w'])     ? trim($_POST['w'])     : '';

// 2) 폴백: JSON으로 전달된 경우 지원
if ($mb_hp === '' && empty($_POST)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $d = json_decode($raw, true);
        if (is_array($d)) {
            $mb_hp = isset($d['mb_hp']) ? trim($d['mb_hp']) : $mb_hp;
            $mb_id = isset($d['mb_id']) ? trim($d['mb_id']) : $mb_id;
            $w     = isset($d['w'])     ? trim($d['w'])     : $w;
        }
    }
}

// 기본 응답: 0 (사용불가)
$status = '0';

// 숫자만 남기기 및 서버측 형식 검증(국내 휴대폰 010/011/016/017/018/019, 10~11자리)
$mb_hp_digits = preg_replace('/[^0-9]/', '', (string)$mb_hp);
if ($mb_hp_digits === '' || !preg_match('/^01(?:0|1|6|7|8|9)\d{7,8}$/', $mb_hp_digits)) {
    echo $status; // 형식이 틀리면 사용불가 처리
    exit;
}

// 업데이트 모드에서는 본인 아이디는 제외하고 중복 검사
$mb_id_where = ($w === 'u' && $mb_id !== '') ? " AND mb_id <> '".$mb_id."' " : '';

// 존재 여부 조회 (행이 없을 때 경고가 출력되지 않도록 가드)
$row = sql_fetch(" SELECT mb_no FROM {$g5['member_table']} WHERE mb_hp = '".$mb_hp_digits."' {$mb_id_where} ");
$mb_no = isset($row['mb_no']) ? (int)$row['mb_no'] : 0;

// 존재하지 않으면 1(사용가능), 존재하면 0(사용불가)
$status = $mb_no ? '0' : '1';

echo $status;