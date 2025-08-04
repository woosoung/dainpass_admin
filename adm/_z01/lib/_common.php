<?php
include_once ('../../../common.php');

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
    // echo $val.'<br>';
}
