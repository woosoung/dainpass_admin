<?php
include_once('./_common.php');
// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

$payload = json_decode(file_get_contents('php://input'), true);

$branchIds = isset($payload['brc_ids']) ? $payload['brc_ids'] : '';
// $branchIds에 값이 있으면 (,)기준으로 배열로 변환
$msg = 'ok';
$branchArr = array();
if($branchIds){
    $branchArr = explode(',', $branchIds);
    // 배열 요소(fle_idx)들을 순서대로  fle_sort를 업데이트
    foreach($branchArr as $sortOrder => $fleIdx){
        $fleIdx = (int) $fleIdx;
        $sortOrder = (int) $sortOrder + 1; // 0부터 시작하므로 1더함
        $updateSql = " UPDATE {$g5['dain_file_table']}
                        SET fle_sort = {$sortOrder}
                        WHERE fle_idx = {$fleIdx} ";
        sql_query_pg($updateSql);
    }   
}
else{
    $msg = 'no';
}
return $msg;