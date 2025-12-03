<?php
if (!defined('_GNUBOARD_')) exit;

function empty_mb_password($reg_mb_password)
{
    if (!trim($reg_mb_password))
        return "비밀번호를 입력해 주십시오.";
    else
        return "";
}

function count_mb_password($reg_mb_password)
{
    if (strlen($reg_mb_password) < 8)
        return "비밀번호를 8자 이상 입력하십시오.";
    else
        return "";
}

function valid_mb_password($reg_mb_password)
{
    // 영문, 숫자, 특수문자 조합 체크
    $hasLetter = preg_match("/[a-zA-Z]/", $reg_mb_password);
    $hasNumber = preg_match("/[0-9]/", $reg_mb_password);
    $hasSpecial = preg_match("/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/", $reg_mb_password);

    if (!$hasLetter || !$hasNumber || !$hasSpecial)
        return "비밀번호는 영문, 숫자, 특수문자를 모두 포함해야 합니다.";

    return "";
}

function match_mb_password($reg_mb_password, $reg_mb_password_re)
{
    if ($reg_mb_password != $reg_mb_password_re)
        return "비밀번호가 같지 않습니다.";
    else
        return "";
}
