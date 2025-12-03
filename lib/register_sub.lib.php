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

function valid_business_no(string $business_no)
{
    // 검증 코드
    $checkNumber = '137137135';

    // 입력받은 사업자 등록 번호에서 숫자가 아닌 것은 제외
    $bizNumber = preg_replace("/[^0-9]/", "", $business_no);

    // 자릿수 체크
    if (strlen($bizNumber) != 10)
        return false;

    // check digit
    $validationKey = intval($bizNumber[9]);

    $magicKey = 10;

    $sum = 0;
    // 각 자릿수를 서로 곱해서 더함
    for ($i = 0; $i < 9; $i++) {
        $bn = $bizNumber[$i];

        $cn = $checkNumber[$i];

        $sum += intval($bn) * intval($cn);
    }

    // 마지막에서 두 번째 숫자를 서로 곱한 후에 $magicKey 로 나눈 몫 구함.
    $quotient = (intval($bizNumber[8]) * intval($checkNumber[8])) / $magicKey;

    $sum += intval($quotient);

    $remainder = $sum % $magicKey;

    // 매직키인 10에서 나머지를 뺀 수가 사업자 등록 번호의 마지막 숫자와 일치해야 함.
    if (($magicKey - $remainder) !== $validationKey) {
        // $remainder 가 0 일 경우 마지막 값이 0 인지 확인
        if ($remainder === 0 && $remainder === $validationKey) {
            return true;
        }

        return false;
    }

    return true;
}
