<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * 닉네임을 아이디와 동일하게 변경
 *
 * register_form.skin.php에서 랜덤 닉네임으로 검증을 통과한 후
 * 실제 DB 저장 전에 mb_nick을 mb_id와 동일하게 변경
 */

// mb_nick을 mb_id로 변경
$mb_nick = $mb_id;

exit;
