<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 기본관리자는 관리자페이지 접근가능
$is_adm_accessable = ($is_admin && !$member['mb_leave_date'] && !$member['mb_intercept_date']) ? true : false;

// 내가 접근가능한 메인메뉴의 코드를 배열로 저장
$auth_sql = " SELECT DISTINCT LEFT(au_menu, 3) AS menu_cd
                FROM {$g5['auth_table']}
                WHERE mb_id = '{$member['mb_id']}'
                ORDER BY menu_cd ";
$auth_res = sql_query($auth_sql,1);
$member_auth_menus = array();
if($auth_res->num_rows && !$member['mb_leave_date'] && !$member['mb_intercept_date']){
    $is_adm_accessable = true; //사원회원이 관리자페이지에 접근가능한 상태
    while($auth_row = sql_fetch_array($auth_res)){
        array_push($member_auth_menus,'menu'.$auth_row['menu_cd']);
    }
}
unset($auth_sql);
unset($auth_res);

// memeber일 경우 meta_table에 회원정보가 있으면 $member배열에 추가
if($is_member){
    $mta_mb_arr = get_meta('member',$member['mb_id']);
    if(count($mta_mb_arr)){
        $member = array_merge($member,$mta_mb_arr);
    }
}
unset($mta_mb_arr);

// 수퍼관리자 여부
$is_super = ($member['mb_level'] >= 9) ? true : false;
// 관리자 여부
$is_manager = ($member['mb_level'] >= 8) ? true : false;