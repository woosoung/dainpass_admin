<?php
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'message' => 'PHP 실행 성공', 'time' => date('Y-m-d H:i:s')]);
exit;
?>

