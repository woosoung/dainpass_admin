<?php
include_once('./_common.php');

if(!$is_manager) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.close();</script>";
}