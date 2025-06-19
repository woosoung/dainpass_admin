<?php
$sub_menu = "100290";
include_once('./_common.php');

check_demo();

if (!$is_manager)
    alert('접근권한이 없습니다.');

check_admin_token();

// print_r2($_POST);exit;
// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query($sql);

$group_code = null;
$primary_code = null;
$count = count($_POST['code']);

//g5_menu 테이블에 기존 레코드가 한 개도 없으면 무조건 시퀀스는 1부터 시작한다.
dbtable_sequence_reset($g5['menu_table']);
for ($i=0; $i<$count; $i++)
{
    $_POST = array_map_deep('trim', $_POST);

    $code    = $_POST['code'][$i];
    $me_code    = $_POST['me_code'][$i];
    $me_name = $_POST['me_name'][$i];
//    $me_link = $_POST['me_link'][$i];
    $me_link = str_replace(G5_URL,"",$_POST['me_link'][$i]);
    // $depth    = $_POST['depth'][$i];

    if(!$code || !$me_name || !$me_link)
        continue;

	
	$sql_common .= $comma."( '$me_code', '$me_name', '$me_link', '{$_POST['me_target'][$i]}', '{$_POST['me_order'][$i]}', '{$_POST['me_use'][$i]}', '{$_POST['me_mobile_use'][$i]}' )";
	$comma = ' , ';
}

// MySQL 서버간 동기화 속도가 php 속도를 못 따라가므로 sql 문장을 한개로 만들어서 업데이트합니다.
$sql = " 	INSERT INTO {$g5['menu_table']}
				( me_code, me_name, me_link, me_target, me_order, me_use, me_mobile_use )
			VALUES {$sql_common}
";
// echo $sql;
// exit;
sql_query($sql);


// 모든 관련 캐시 파일 삭제 (for문장)
if ($handle = opendir(G5_DATA_PATH."/cache/")) {
	while ('' != ($file = readdir($handle))) {
		if ($file != '..' && $file != '.') {
				$filename = basename($file);
				if ('' != strstr($filename, 'navi-')) {
					@unlink(G5_DATA_PATH."/cache/".$filename);
				}
		}
	}
	closedir($handle); // 디렉토리 핸들 해제
}
// contents.php 삭제
@unlink(G5_DATA_PATH."/cache/contents.php");

goto_url('./menu_list.php');