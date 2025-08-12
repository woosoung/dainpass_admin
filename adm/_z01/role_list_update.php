<?php
$sub_menu = "920400";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu],"w");

//print_r2($trm_idx);
//echo "<br>==========<br>";
// print_r2($_POST);
// exit;

//-- depth 설정 및 공백 체크
$prev_depth = 0;
for($i=0;$i<sizeof($trm_depth);$i++) {
	if($i==0 && $trm_depth[$i] > 0) {
		alert('맨 처음 항목은 최상위 레벨이어야 합니다. 단계 설정을 확인해 주세요.');
	}
	if($trm_depth[$i] - $prev_depth > 1) {
		alert(trim($trm_name[$i]) + ' : 단계 설정에 문제가 있습니다. \n\n순서대로 하위 단계를 설정해 주세요.');
	}
	if(trim($trm_name[$i]) == "") {
		alert('직책명이 공백인 항목이 있습니다. \n\n확인하시고 다시 진행해 주세요.');
	}
	$prev_depth = $trm_depth[$i]; 
}
// print_r2($trm_desc);
// exit;

//-- 먼저 left, right 값 초기화
$sql = " UPDATE {$g5['term_table']} SET trm_left = '0', trm_right = '0' WHERE trm_category = '".$category."' ";
sql_query($sql, 1);

// print_r2($trm_desc);exit;
$depth_array = array();
$idx_array = array();	// 부모 idx를 입력하기 위한 정의
$prev_depth = 0;
for($i=0;$i<sizeof($trm_name);$i++) {
    
	//-- leaf node(마지막노드) 체크 / $depth_array[$trm_depth[$i]] = 1
	$depth_array[$trm_depth[$i]]++;	// 형제 갯수를 체크
	if($trm_depth[$i] < $prev_depth) {
		//echo $prev_depth - $trm_depth[$i]."만큼 작아졌네~".$prev_depth."<br>";
		for($j=$trm_depth[$i]+1;$j <= $prev_depth;$j++) {
			//echo $j.'<br>';
			$depth_array[$j] = 0;
		}
	}

    // 정렬번호
    if(!$trm_sort[$i])
        $trm_sort[$i] = $i;

	
	//-- 맨 처음 항목 입력 left=1, right=2 설정
	if($i == 0) {
		$sql = "INSERT INTO {$g5['term_table']} (trm_idx,trm_idx_parent,trm_name,trm_name2,trm_type,trm_category,trm_desc,trm_sort,trm_left,trm_right,trm_status,trm_reg_dt) 
					VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','".$trm_name[$i]."','".$trm_name2[$i]."','".$trm_type[$i]."','".$category."','".$trm_desc[$i]."','$i', 1, 2, '".$trm_status[$i]."', now())
					ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
                                            , trm_name = '".$trm_name[$i]."'
                                            , trm_name2 = '".$trm_name2[$i]."'
                                            , trm_type = '".$trm_type[$i]."'
                                            , trm_desc = '".$trm_desc[$i]."'
                                            , trm_sort = '".$trm_sort[$i]."'
                                            , trm_status = '".$trm_status[$i]."'
                                            , trm_left = 1
                                            , trm_right = 2
		";
		sql_query($sql,1);
		// echo $sql.'<br><br>';
	}
	else {

		//-- leaf_node 이면 부모 idx를 참고해서 left, right 생성
		if($depth_array[$trm_depth[$i]] == 1) {
			//echo '부모idx -> '.$idx_array[$trm_depth[$i]-1];

			sql_query("SELECT @myLeft := trm_left FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]-1]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myLeft AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myLeft AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','".$trm_name[$i]."','".$trm_name2[$i]."','".$trm_type[$i]."','".$category."','".$trm_desc[$i]."','$i',@myLeft + 1,@myLeft + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '".$trm_name[$i]."'
							, trm_name2 = '".$trm_name2[$i]."'
							, trm_type = '".$trm_type[$i]."'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myLeft + 1
							, trm_right = @myLeft + 2
			";
			sql_query($sql,1);
			// echo $sql.'<br><br>';
		}
		//-- leaf_node가 아니면 동 레벨 idx 참조해서 left, right 생성
		else {
			sql_query("SELECT @myRight := trm_right FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myRight AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myRight AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','".$trm_name[$i]."','".$trm_name2[$i]."','".$trm_type[$i]."','".$category."','".$trm_desc[$i]."','$i',@myRight + 1,@myRight + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '".$trm_name[$i]."'
							, trm_name2 = '".$trm_name2[$i]."'
							, trm_type = '".$trm_type[$i]."'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myRight + 1
							, trm_right = @myRight + 2
			";
			sql_query($sql,1);
			// echo $sql.'<br><br>';
		}
	}
	
	//echo "<br><br>";
	$prev_depth = $trm_depth[$i]; 
	$idx_array[$trm_depth[$i]] = $trm_idx[$i];	//-- left, right 기준 값 저장
	$idx_array[$trm_depth[$i]] = sql_insert_id();	//-- left, right 기준 값 저장
}


// 캐시 파일 삭제 (초기화)
$files = glob(G5_DATA_PATH.'/cache/term-'.$category.'.php');
if (is_array($files)) {
    foreach ($files as $filename)
        unlink($filename);
}


//exit;
// 앞에서 넘어온 파일명으로 다시 돌려보낸다.
goto_url("./".$file_name.".php?category=".$category);
?>