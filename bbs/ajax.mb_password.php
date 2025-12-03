<?php
include_once('./_common.php');
include_once(G5_LIB_PATH . '/register_sub.lib.php');

$mb_password = isset($_POST['reg_mb_password']) ? trim($_POST['reg_mb_password']) : '';
$mb_password_re = isset($_POST['reg_mb_password_re']) ? trim($_POST['reg_mb_password_re']) : '';

set_session('ss_check_mb_password', '');

if ($msg = empty_mb_password($mb_password))     die($msg);
if ($msg = valid_mb_password($mb_password))     die($msg);
if ($msg = count_mb_password($mb_password))     die($msg);

set_session('ss_check_mb_password', $mb_password);
