<?php
$sub_menu = "920220";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// print_r2($_POST);exit;
//-- 필드명 추출 mb_ 와 같은 앞자리 3자 추출, 앞자리3글자추출 --//
$rsql = " SELECT column_name 
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = '{$g5['setting_table']}'
    ORDER BY ordinal_position
";
// echo $rsql;exit;
$r = sql_query_pg($rsql);

$db_fields = [];
while ( $d = sql_fetch_array_pg($r) ) {$db_fields[] = $d['column_name'];}
$db_prefix = substr($db_fields[0],0,3);



//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "set_bg_pattern";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "var_name";	// 건너뛸 변수명은 배열로 추가해 준다.

foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트, array 타입 변수들도 저장 안 함 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix && gettype($value) != 'array') {
		// echo $key."=".$value."<br>";continue;
		set_update(array(
			"set_shop_id"=>$conf_com_idx,	// 가맹점 ID 값
			"set_key"=>$_POST['set_key'],	// set_XXX.php단에서 unset($set_key);이 있으므로 $_POST['set_key']로 받아야 함
			"set_type"=>$_POST['set_type'],	// set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
			"set_name"=>$key,
			"set_value"=>$value,
			"set_auto_yn"=>'Y'
		));
	}
}

@include_once('./'.$file_name.'_file_update.php');

//exit;
goto_url('./'.$file_name.'.php?'.$qstr, false);