<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//add_event('common_header', 'bpwg_adm_head_file_include',10);
add_event('admin_common', 'z_adm_common_head',10);
add_event('tail_sub', 'z_adm_common_tail', 10);
function z_adm_common_head(){
	global $g5,$member,$default,$default2,$config,$set_menu,$set_conf,$set_mng,$set_mngapp,$set_app,$set_plf,$set_com,$menu,$menu2,$sub_menu,$co_id,$w,$pg_anchor,$member_auth_menus,$menu_main_titles,$menu_list_tag,$auth;

	// 관리자 index.php 페이지는 _z01/_adm/index.php로 리다이렉트
	if($g5['dir_name'] == 'adm' && $g5['file_name'] == 'index'){
		header("location:".G5_ZADM_URL);
		exit;
	}

	$menu2 = $menu;//메뉴배열을 복사 - $menu는 권한에 따라 삭제된다. (employee_form.php에서 사용하기 위해)
	// print_r2($set_menu['set_hide_submenus_arr']);exit;
	// 해당 회원의 접근권한이 없는 메뉴코드는 메뉴배열에서 삭제
	$tmp_menu = array();
	if($config['cf_admin'] != $member['mb_id']){
		foreach($menu as $mcd => $marr){
			// $member_auth_menus는 z.04.auth.php에서 정의된 배열로, 해당 회원이 접근가능한 메뉴코드를 배열로 저장
			if(in_array($mcd, $set_menu['set_hide_mainmenus_arr']) || !in_array($mcd,$member_auth_menus)){
				unset($menu[$mcd]); //접근 권한이 없는 메뉴코드는 메뉴배열에서 삭제
				continue;
			}
			// echo $mcd.BR;
			$tmp_menu[$mcd] = array();
			if(count($menu[$mcd])){
				// print_r2($menu[$mcd]);
				foreach($menu[$mcd] as $i => $v){
					// echo $menu[$mcd][$i][0].BR;
					if(in_array($menu[$mcd][$i][0], $set_menu['set_hide_submenus_arr'])){
						unset($menu[$mcd][$i]); //접근 권한이 없는 서브메뉴코드는 메뉴배열에서 삭제
						continue;
					}
					// echo $menu[$mcd][$i][0].BR;
					// tmp_menu배열에 저장하는 이유는 $menu[$mcd]에서의 
					// [$i]가 중간에 누락된 index가 있을 경우가 있으므로 제대로 된 배열로 만들어 다시 $menu에 재대입하기 위함
					$tmp_menu[$mcd][] = $menu[$mcd][$i];
				}
			}
		}
		// print_r2($tmp_menu);
		// exit;
		$menu = $tmp_menu;
	}
	// print_r2($menu);
	// exit;
	// 해당 후킹디렉토리 위치에 동일한 파일이 있으면 해당 $menu배열 요소의 url경로가 후킹url경로로 변경된다.
	foreach($menu as $k => $v){
		if(count($menu[$k])){
			for($i=0;$i<count($menu[$k]);$i++){
				$dir_file_arr = explode('/',$menu[$k][$i][2]);
				$adir = $dir_file_arr[count($dir_file_arr)-2];
				$afile = $dir_file_arr[count($dir_file_arr)-1];
				$a_h_file_path = G5_Z_PATH.'/_'.$adir.'/'.$afile;
				$a_h_file_url = G5_Z_URL.'/_'.$adir.'/'.$afile;
				$as_h_file_path = G5_ZADM_PATH.'/_'.$adir.'/'.$afile;
				$as_h_file_url = G5_ZADM_URL.'/_'.$adir.'/'.$afile;
				if(is_file($a_h_file_path) && !is_file($as_h_file_path)){
					$menu[$k][$i][2] = $a_h_file_url;
				}else if(!is_file($a_h_file_path) && is_file($as_h_file_path)){
					$menu[$k][$i][2] = $as_h_file_url;
				}
			}
		}
	}
	
	// employee_form.php에서 사원별 관리자메뉴의 접근권한을 설정하기 위해 사용되는 데이터
	$menu_list_tag = '<ul class="ul1_menu">'.PHP_EOL;
	$menu_main_titles = array();
	foreach($menu2 as $k => $v){
		$menu_list_tag .= '<li class="li1_menu">'.PHP_EOL;
		$menu_list_tag .= '<span>##['.$k.']##</span>'.PHP_EOL;
		if(count($menu2[$k])){
			$menu_list_tag .= '<ul class="ul2_menu">'.PHP_EOL;
			for($i=0;$i<count($menu2[$k]);$i++){
				if($i == 0) $menu_main_titles[$k] = $menu2[$k][$i][1];
				$menu_list_tag .= '<li class="li2_menu inline-block">'.PHP_EOL;
				if (isset($menu[$k][$i])) {
					$menu_list_tag .= ' <div>(' . $menu[$k][$i][0] . ':' . $menu[$k][$i][1] . ')</div>'.PHP_EOL;
				} else {
					$menu_list_tag .= ' <div>(접근불가 또는 메뉴 없음)</div>'.PHP_EOL;
				}
				// $menu_list_tag .= ' <div>('.$menu[$k][$i][0].':'.$menu[$k][$i][1].')</div>'.PHP_EOL;
				$menu_list_tag .= '</li>'.PHP_EOL;
			}
			$menu_list_tag .= '</ul>'.PHP_EOL;
		}
		$menu_list_tag .= '</li>'.PHP_EOL;
	}
	$menu_list_tag .= '</ul>'.PHP_EOL;
}


function z_adm_common_tail(){
	global $g5,$member,$default,$default2,$set_conf,$set_mng,$set_mngapp,$set_app,$set_plf,$set_com,$config,$menu,$sub_menu,$co_id,$w,$pg_anchor,$auth;
	
	//대체한 DOM요소를 일단 표시
	@include_once(G5_ZREPLACE_PATH.'/admin.head.php');
	// echo ''.PHP_EOL;
	echo '<script>'.PHP_EOL;
	echo 'const amenu = \''.json_encode($menu).'\';'.PHP_EOL;
	echo 'const file_name = "'.$g5['file_name'].'";'.PHP_EOL;
	echo 'const dir_name = "'.$g5['dir_name'].'";'.PHP_EOL;
	echo 'const mb_name = "'.$member['mb_name'].'";'.PHP_EOL;
	echo 'const mb_level = "'.$member['mb_level'].'";'.PHP_EOL;
	echo 'const g5_community_use = "'.G5_COMMUNITY_USE.'";'.PHP_EOL;
	echo '</script>'.PHP_EOL;
	// jquery-ui 스타일시트
	add_stylesheet('<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">',0);
	// 부트스트랩 아이콘
	add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">',0);
	// adm 공통으로 적용할 커스텀 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_common_custom.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_common_custom.css">',0);
	// 기존의 스타일을 재정의하는 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_override.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_override.css">',0);
	// 추가적인 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_add.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_add.css">',0);
	// 추가적인 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_add.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/_set.css">',0);
	// _z01개별페이지에 필요한 스타일시트
	if(is_file(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	// adm후킹개별페이지에 필요한 스타일시트
	if(is_file(G5_Z_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	// shop_admin후킹개별페이지에 필요한 스타일시트
	if(is_file(G5_ZADM_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_ZADM_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	
	// jquery-ui
	add_javascript('<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>',0);
	// 관리자단에 필요한 함수를 정의한 파일
	if(is_file(G5_Z_PATH.'/js/adm_func.js')) add_javascript('<script src="'.G5_Z_URL.'/js/adm_func.js"></script>',0);
	if(is_file(G5_Z_PATH.'/js/tms_datepicker.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
	if(is_file(G5_Z_PATH.'/js/tms_timepicker.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
	// tailwindcss
	if(is_file(G5_Z_PATH.'/js/tailwind.min.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tailwind.min.js"></script>',0);
	// _z01안에 DOM객체의 편집이 필요할때 사용하느 js파일
	if(is_file(G5_Z_PATH.'/js/adm_dom_control.js.php')) include_once(G5_Z_PATH.'/js/adm_dom_control.js.php');
	// _z01개별페이지에 필요한 js파일
	if(is_file(G5_Z_PATH.'/js/'.$g5['file_name'].'.js.php')) include_once(G5_Z_PATH.'/js/'.$g5['file_name'].'.js.php');
	// adm후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/js/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/js/'.$g5['file_name'].'.js.php');
	// shop_admin후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/_shop_admin/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/_shop_admin/'.$g5['file_name'].'.js.php');
	// sms_admin후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/_sms_admin/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/_sms_admin/'.$g5['file_name'].'.js.php');
}