<?php
include_once('../_common.php');
// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

auth_check_menu($auth, '920650', 'w');

$payload = json_decode(file_get_contents('php://input'), true);

$bng_id = isset($payload['bng_id']) ? (int)$payload['bng_id'] : 0;
$bnr_ids = isset($payload['bnr_ids']) ? $payload['bnr_ids'] : '';

$msg = 'ok';

if (!$bng_id) {
    echo 'no';
    exit;
}

if ($bnr_ids) {
    $bnrArr = explode(',', $bnr_ids);
    // 배열 요소(bnr_id)들을 순서대로 bnr_sort를 업데이트
    foreach($bnrArr as $sortOrder => $bnrId){
        $bnrId = (int) $bnrId;
        $sortOrder = (int) $sortOrder + 1; // 0부터 시작하므로 1더함
        
        // 동일 bng_id 내에서만 업데이트
        $updateSql = " UPDATE banner 
                        SET bnr_sort = {$sortOrder}
                        WHERE bnr_id = {$bnrId} 
                          AND bng_id = {$bng_id} ";
        sql_query_pg($updateSql);
    }   
} else {
    $msg = 'no';
}

echo $msg;
exit;

