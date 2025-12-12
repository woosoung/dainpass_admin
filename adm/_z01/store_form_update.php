<?php
$sub_menu = "930100";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

@auth_check($auth[$sub_menu], 'w');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        // mb_1 = '0'인 경우: 플랫폼 관리자
        if ($mb_1_value === '0' || $mb_1_value === '') {
            // 플랫폼 관리자는 shop_id = 0에 해당하는 레코드가 없으므로 '업체 데이터가 없습니다.' 표시
            alert('업체 데이터가 없습니다.');
        }
        
        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                alert('업체 데이터가 없습니다.');
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

// 입력 기본값 안전 초기화
$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : (isset($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : 0);

// 가맹점측 관리자는 자신의 가맹점만 수정 가능
if ($post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

if ($w == 'u')
    check_demo();

//check_admin_token();
if(!trim($_POST['category_ids'])) alert('업종(분류)을 반드시 선택해 주세요.');
if(!trim($_POST['name'])) alert('업체명을 입력해 주세요.');
if(!trim($_POST['contact_email'])) alert('이메일을 입력해 주세요.');
if(!trim($_POST['owner_name'])) alert('대표자명을 입력해 주세요.');
if(!trim($_POST['contact_phone'])) alert('업체전화번호를 입력해 주세요.');

$name = trim($_POST['name']);
$shop_name = trim($_POST['shop_name']);
$business_no = trim($_POST['business_no']);
$business_no = preg_replace('/[^0-9]/', '', $business_no); // 사업자번호 숫자만 추출
$owner_name = trim($_POST['owner_name']);
$contact_email = trim($_POST['contact_email']);
$contact_phone = trim($_POST['contact_phone']);
$contact_phone = preg_replace('/[^0-9]/', '', $contact_phone); // 전화번호 숫자만 추출
$zipcode = trim($_POST['zipcode']);
$addr1 = trim($_POST['addr1']);
$addr2 = trim($_POST['addr2']);
$addr3 = trim($_POST['addr3']);
$latitude = trim($_POST['latitude']);
$longitude = trim($_POST['longitude']);
$url = trim($_POST['url']);
$max_capacity = isset($_POST['max_capacity']) ? (int)$_POST['max_capacity'] : 0;
$reservelink_yn = (isset($_POST['reservelink_yn']) && $_POST['reservelink_yn'] == '') ? $_POST['reservelink_yn'] : '';
$reservelink = isset($_POST['reservelink']) ? trim($_POST['reservelink']) : '';
$reserve_tel = isset($_POST['reserve_tel']) ? trim($_POST['reserve_tel']) : '';
$shop_description = isset($_POST['shop_description']) ? conv_unescape_nl(stripslashes($_POST['shop_description'])) : '';
$bank_account = isset($_POST['bank_account']) ? trim($_POST['bank_account']) : '';
$bank_account = preg_replace('/[^0-9]/', '', $bank_account); // 계좌번호 숫자만 추출
$bank_name = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : ''; //은행명
$bank_holder = isset($_POST['bank_holder']) ? trim($_POST['bank_holder']) : ''; //예금주
// 가맹점측 관리자는 정산 관련 필드 수정 불가
// $settlement_type = isset($_POST['settlement_type']) ? trim($_POST['settlement_type']) : ''; //정산타입(수동/자동)
// $settlement_cycle = isset($_POST['settlement_cycle']) ? trim($_POST['settlement_cycle']) : ''; //정산주기(monthly, weekly, 2monthly)
// $settlement_day = isset($_POST['settlement_day']) ? (int)$_POST['settlement_day'] : 0; //정산일(25 | 01 ...)
$tax_type = isset($_POST['tax_type']) ? trim($_POST['tax_type']) : ''; //과세유형
// $settlement_memo = isset($_POST['settlement_memo']) ? conv_unescape_nl(stripslashes($_POST['settlement_memo'])) : ''; //정산메모
// $is_active = (isset($_POST['is_active']) && $_POST['is_active'] != '') ? $_POST['is_active'] : 'N'; //활성화여부
$cancel_policy = isset($_POST['cancel_policy']) ? conv_unescape_nl(stripslashes($_POST['cancel_policy'])) : '';
// $point_rate = isset($_POST['point_rate']) ? (float)$_POST['point_rate'] : 0;
// $point_rate = number_format($point_rate,2,'.','');
// names 업체명 히스토리
$branch = trim($_POST['branch']);
// shop_parent_id 본사가맹점 id
// shop_names = 가맹점명 히스토리
$mng_menus = ($w == 'u') ? addslashes(trim($_POST['mng_menus'])) : '';

// 공간관리(930600)와 공간그룹관리(930550) 동기화 처리
if ($w == 'u' && $mng_menus) {
    $mng_menus_arr = array_map('trim', explode(',', $mng_menus));
    $has_space_menu = in_array('930600', $mng_menus_arr); // 공간관리
    $has_space_group_menu = in_array('930550', $mng_menus_arr); // 공간그룹관리
    
    // 둘 중 하나만 있으면 나머지도 추가
    if ($has_space_menu && !$has_space_group_menu) {
        $mng_menus_arr[] = '930550';
    } else if ($has_space_group_menu && !$has_space_menu) {
        $mng_menus_arr[] = '930600';
    }
    
    // 중복 제거 후 다시 문자열로 변환
    $mng_menus_arr = array_unique($mng_menus_arr);
    $mng_menus = addslashes(implode(',', $mng_menus_arr));
}

// 추가 필드 처리
$notice = isset($_POST['notice']) ? conv_unescape_nl(stripslashes($_POST['notice'])) : '';
$cancellation_period = isset($_POST['cancellation_period']) ? (int)$_POST['cancellation_period'] : 1;
$blog_url = isset($_POST['blog_url']) ? trim($_POST['blog_url']) : '';
$instagram_url = isset($_POST['instagram_url']) ? trim($_POST['instagram_url']) : '';
$kakaotalk_url = isset($_POST['kakaotalk_url']) ? trim($_POST['kakaotalk_url']) : '';
$amenities_id_list = isset($_POST['amenities_id_list']) ? trim($_POST['amenities_id_list']) : '';
$reservation_mode = isset($_POST['reservation_mode']) ? trim($_POST['reservation_mode']) : 'SERVICE_ONLY';
$prep_period_for_reservation = isset($_POST['prep_period_for_reservation']) && $_POST['prep_period_for_reservation'] !== '' ? (int)$_POST['prep_period_for_reservation'] : null;

// 이메일 형식 체크
if(!preg_match("/^[a-z0-9_+.-]+@([a-z0-9-]+\.)+[a-z0-9]{2,4}$/",$contact_email)) {
    alert('이메일 형식이 올바르지 않습니다.');
}

//위도형식에 맞지 않으면 경고창 띄우기
if($latitude){
    if(!preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,})?)|(?:[1-8]?\d(?:\.\d{1,})?))$/', $latitude)){
        alert('위도의 형식이 올바르지 않습니다.');
    }
}
//경도형식에 맞지 않으면 경고창 띄우기
if($longitude){
    if(!preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,})?)|(?:1[0-7]?\d(?:\.\d{1,})?)|(?:\d?\d(?:\.\d{1,})?))$/', $longitude)){
        alert('경도의 형식이 올바르지 않습니다.');
    }
}

if ($w=='u'){
    // 업체정보 추출
    $com = sql_fetch_pg(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '$shop_id' ");
    
    if (!$com['shop_id']) {
        alert('존재하지 않는 가맹점자료입니다.');
    }
    
    // 가맹점측 관리자는 정산 관련 필드 수정 불가이므로 기존 값 유지
    $settlement_type = $com['settlement_type'] ?? 'manual';
    $settlement_cycle = $com['settlement_cycle'] ?? 'monthly';
    $settlement_day = $com['settlement_day'] ?? 25;
    $settlement_memo = $com['settlement_memo'] ?? '';
    $is_active = $com['is_active'] ?? 'N';
    // shop table에 존재하지 않는 column
    // $point_rate = $com['point_rate'] ?? 0;
    
    // 관리메뉴 선택한것과 하지 않은것에 대한 auth테이블 업데이트
    // 이전 mng_menus 정보가져와서 새로 넘어온 mng_menus와 비교
    // $prev_mng_sql = " SELECT mng_menus FROM {$g5['shop_table']} WHERE shop_id = '{$shop_id}' ";
    // $prev_mng_res = sql_fetch_pg($prev_mng_sql);
    $menus = (isset($mng_menus) && $mng_menus != '') ? explode(',',$mng_menus) : array();
    // 관리메뉴 데이터가 존재하거나 변경된 경우 관리메뉴 권한 업데이트
    // 우선 auth테이블 업데이트를 위해서 해당 shop_id의 관리회원들을 추출
    $auth_mbs_sql = " SELECT GROUP_CONCAT(mb_id) AS mb_ids FROM {$g5['member_table']}
                        WHERE mb_level IN (4,5)
                        AND mb_1 = '{$shop_id}'
                        AND mb_2 = 'Y' ";
    $auth_mbs_res = sql_fetch($auth_mbs_sql);
    // 관리회원이 존재하면 우선 해당 회원들의 이전 메뉴권한을 삭제하고 새로 넘어온 메뉴권한을 다시 모든 관리자에게 부여
    if(isset($auth_mbs_res['mb_ids']) && $auth_mbs_res['mb_ids']) {
        $mb_ids_arr = explode(',',$auth_mbs_res['mb_ids']);
        $mb_ids_str = '';
        foreach($mb_ids_arr as $mb_id){
            $mb_ids_str .= "'{$mb_id}',";
        }
        $mb_ids_str = rtrim($mb_ids_str,',');
        // 이전 회원의 권한을 모두 삭제
        $auth_del_sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id IN ($mb_ids_str) ";
        // echo $auth_del_sql;exit;
        sql_query($auth_del_sql,1);
        
        // 새로 넘어온 메뉴구성으로 다시 권한부여
        $auth_values = array();
        foreach($mb_ids_arr as $mb_id){
            // 기본 메뉴 권한 추가 (중복 방지를 위해 배열로 관리)
            $added_menus = array();
            
            // 기본 100000 메뉴 권한 추가
            $auth_values[] = "('{$mb_id}','100000','r')";
            $added_menus['100000'] = true;
            
            // 선택한 관리메뉴 권한 추가 (count($menus) > 0인 경우에만)
            if(@count($menus) > 0){
                foreach($menus as $menu){
                    if(!isset($added_menus[$menu])){
                        $auth_values[] = "('{$mb_id}','{$menu}','r,w,d')";
                        $added_menus[$menu] = true;
                    }
                }
            }
            
            // 기본 메뉴 권한 추가 (중복 체크)
            if(isset($set_conf['set_shopmanager_basic_menu_arr']) && @count($set_conf['set_shopmanager_basic_menu_arr']) > 0){
                foreach($set_conf['set_shopmanager_basic_menu_arr'] as $menu_code => $menu_arr){
                    if(!isset($added_menus[$menu_code])){
                        $auth_values[] = "('{$mb_id}','{$menu_code}','{$menu_arr['auth']}')";
                        $added_menus[$menu_code] = true;
                    }
                }
            }
        }
        if(count($auth_values) > 0){
            $auth_sql = " INSERT INTO {$g5['auth_table']} (mb_id,au_menu,au_auth) VALUES " . implode(',', $auth_values);
            // echo $auth_sql;exit;
            sql_query($auth_sql,1);
        }
    }
}

// 업체명 히스토리
if($w == 'u' && $com['name'] != $name) {
	$names = $com['names'].', '.$name.'('.substr(G5_TIME_YMD,2).'~)';
    if($w == 'u')
        change_com_names($shop_id, $com['name']);
}
else if($w == '') {
	$names = $_POST['name'].'('.substr(G5_TIME_YMD,2).'~)';
}

// 가맹점명 히스토리
if($w == 'u' && $com['shop_name'] != $shop_name) {
	$shop_names = $com['shop_names'].', '.$shop_name.'('.substr(G5_TIME_YMD,2).'~)';
}
else if($w == '') {
	$shop_names = $_POST['shop_name'].'('.substr(G5_TIME_YMD,2).'~)';
}
else {
	$shop_names = $com['shop_names'] ?? '';
}

$sql_common = "	name = '".addslashes($name)."'
                , shop_name = '".addslashes($shop_name)."'
                , business_no = '{$business_no}'
                , owner_name = '{$owner_name}'
                , contact_email = '{$contact_email}'
                , contact_phone = '{$contact_phone}'
                , zipcode = '{$zipcode}'
                , addr1 = '{$addr1}'
                , addr2 = '{$addr2}'
                , addr3 = '{$addr3}'
                , latitude = '{$latitude}'
                , longitude = '{$longitude}'
                , url = '{$url}'
                , max_capacity = {$max_capacity}
                , status = '{$_POST['status']}'
                , reservelink_yn = '{$reservelink_yn}'
                , reservelink = '{$reservelink}'
                , reserve_tel = '{$reserve_tel}'
                , shop_description = '".addslashes($shop_description)."'
                , cancel_policy = '".addslashes($cancel_policy)."'
                , names = '".addslashes($names)."'
                , tax_type = '{$tax_type}'
                , branch = '".addslashes($branch)."'
                , mng_menus = '".$mng_menus."'
                , settlement_type = '{$settlement_type}'
                , settlement_cycle = '{$settlement_cycle}'
                , settlement_day = {$settlement_day}
                , settlement_memo = '".addslashes($settlement_memo)."'
                , is_active = '{$is_active}'
                , notice = '".addslashes($notice)."'
                , cancellation_period = {$cancellation_period}
                , shop_names = '".addslashes($shop_names)."'
                , blog_url = '".addslashes($blog_url)."'
                , instagram_url = '".addslashes($instagram_url)."'
                , kakaotalk_url = '".addslashes($kakaotalk_url)."'
                , amenities_id_list = '".addslashes($amenities_id_list)."'
                , reservation_mode = '".addslashes($reservation_mode)."'
                , prep_period_for_reservation = ".($prep_period_for_reservation !== null ? $prep_period_for_reservation : 'NULL')."
";

// 수정
if ($w == 'u') {
	if (!$com['shop_id'])
		alert('존재하지 않는 업체자료입니다.');
 
    $sql = "	UPDATE {$g5['shop_table']} SET 
					{$sql_common}
					, updated_at = '".G5_TIME_YMDHIS."'
				WHERE shop_id = '{$shop_id}' 
	";
    sql_query_pg($sql);
}

// 먼저 shop 해당 업체(shop_id)와 관계되는 category_id들을 전부 삭제
if($w == 'u'){
    $cdsql = " DELETE FROM {$g5['shop_category_relation_table']} WHERE shop_id = '{$shop_id}' ";
    sql_query_pg($cdsql);
}

// $category_ids 라는 (,)로 구분된 문자열을 (,)구분자로 배열에 담는다.
// $shop_id 가 반드시 있어야 카테고리 등록이 가능
if($shop_id){
    $category_ids_arr = (isset($_POST['category_ids']) && !empty(trim($_POST['category_ids'] ?? ''))) ? explode(',', $_POST['category_ids']) : array();
    if(count($category_ids_arr)){
        $cisql = " INSERT INTO {$g5['shop_category_relation_table']} (shop_id, category_id, sort) VALUES ";
        $values = array();
        $n = 1;
        foreach($category_ids_arr as $category_id){
            $values[] = "('{$shop_id}', '{$category_id}', '{$n}')";
            $n++;
        }
        $cisql .= implode(',', $values);
        sql_query_pg($cisql);
    }
}

if($w == '' || $w == 'u'){
    
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(isset($comf_del) && @count($comf_del)){
        foreach($comf_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    if(isset($comi_del) && @count($comi_del)){
        foreach($comi_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(is_array($merge_del) && @count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(is_array($del_arr) && @count($del_arr)) delete_idx_s3_file($del_arr);

    // 새롭게 가맹점이미지파일을 업로드 하려는 파일의 갯수를 파악하는 코드
    $comi_new_file_count = 0;
    if(isset($_FILES['comi_datas']) && @count($_FILES['comi_datas']['name'])){
        $comi_new_file_count += @count($_FILES['comi_datas']['name']);
    }
    // 해당 가맹점이미지가 기존에 저장된 파일의 갯수를 파악하는 코드
    $comi_old_file_count = 0;
    // 직접 dain_file_table에서 해당 가맹점이미지의 파일갯수를 파악하는 코드
    $fsql = " SELECT COUNT(*) AS fle_idxs_count FROM {$g5['dain_file_table']}
                WHERE fle_db_tbl = 'shop'
                    AND fle_type = 'comi'
                    AND fle_dir = 'shop/shop_img'
                    AND fle_db_idx = '{$shop_id}' ";
    $fres = sql_fetch_pg($fsql);
    if(isset($fres['fle_idxs_count']) && $fres['fle_idxs_count']){
        $comi_old_file_count = (int) $fres['fle_idxs_count'];
    }
    
    // 가맹점 이미지 중에 삭제하려는 파일의 갯수를 파악하는 코드
    $comi_del_file_count = 0;
    if(isset($comi_del) && is_array($comi_del) && @count($comi_del)){
        $comi_del_file_count = @count($comi_del);
    }
    // 가맹점이미지의 총 파일갯수가 10개를 초과하는지 체크
    $comi_total_file_count = $comi_old_file_count - $comi_del_file_count + $comi_new_file_count;
    if($comi_total_file_count > 10){
        alert('가맹점이미지는 최대 10개까지 등록할 수 있습니다.');
    }
    
    //$shop_id가 반드시 있어야 파일업로드가 가능
    if($shop_id){
        //멀티파일처리
        upload_multi_file($_FILES['comf_datas'],'shop',$shop_id,'shop/shop_file','comf');
        upload_multi_file($_FILES['comi_datas'],'shop',$shop_id,'shop/shop_img','comi');
    }
}

// 가맹점 키워드 처리
if (isset($_POST['shop_keywords']) && trim($_POST['shop_keywords']) !== '') {
    $shop_keywords = trim($_POST['shop_keywords']);
    $sql = " WITH raw_terms AS (
                SELECT
                    '{$shop_id}'::bigint AS shop_id,
                    trim(both ' ' FROM term_txt) AS term,
                    ord
                FROM regexp_split_to_table('{$shop_keywords}', ',')
                     WITH ORDINALITY AS t(term_txt, ord)
            ),
            filtered AS (
                SELECT *
                FROM raw_terms
                WHERE term <> ''
            ),
            normalized AS (
                SELECT
                    shop_id,
                    term,
                    ord,
                    regexp_replace(lower(immutable_unaccent(term)), '\\s+', ' ', 'g') AS term_norm
                FROM filtered
            ),
            dedup AS (
                SELECT
                    shop_id,
                    term,
                    term_norm,
                    ord,
                    row_number() OVER (PARTITION BY term_norm ORDER BY ord) AS rn
                FROM normalized
            ),
            ranked AS (
                SELECT
                    shop_id,
                    term,
                    term_norm,
                    ord,
                    (MAX(ord) OVER (PARTITION BY shop_id) - ord + 1) AS weight
                FROM dedup
                WHERE rn = 1
            ),
            insert_keywords AS (
                INSERT INTO {$g5['keywords_table']} (term)
                SELECT r.term
                FROM ranked r
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM {$g5['keywords_table']} k
                    WHERE k.term_norm = r.term_norm
                )
                RETURNING keyword_id AS keyword_id,
                          regexp_replace(lower(immutable_unaccent(term)), '\\s+', ' ', 'g') AS term_norm
            ),
            resolved AS (
                SELECT
                    r.shop_id,
                    k.keyword_id AS keyword_id,
                    r.weight,
                    r.term_norm
                FROM ranked r
                JOIN {$g5['keywords_table']} k ON k.term_norm = r.term_norm
            ),
            upsert AS (
                INSERT INTO {$g5['shop_keyword_table']} (shop_id, keyword_id, weight)
                SELECT shop_id, keyword_id, weight
                FROM resolved
                ON CONFLICT (shop_id, keyword_id) DO UPDATE
                  SET weight = EXCLUDED.weight
                RETURNING shop_id
            ),
            deleted AS (
                DELETE FROM {$g5['shop_keyword_table']} sk
                USING {$g5['keywords_table']} k
                WHERE sk.shop_id = '{$shop_id}'
                  AND sk.keyword_id = k.keyword_id
                  AND NOT EXISTS (
                        SELECT 1
                        FROM ranked r
                        WHERE r.term_norm = k.term_norm
                    )
                RETURNING sk.shop_id, sk.keyword_id
            )
            SELECT
                (SELECT count(*) FROM ranked)          AS parsed_terms,
                (SELECT count(*) FROM insert_keywords) AS keywords_inserted,
                (SELECT count(*) FROM upsert)          AS shop_keyword_upserted,
                (SELECT count(*) FROM deleted)         AS shop_keyword_deleted ";
    $result = sql_query_pg($sql);
    if (!$result) {
        alert('키워드 처리 중 오류가 발생했습니다.');
    }

    // Register shop_id in the shop_search_refresh_queue table for asynchronous cache refresh
    $queue_table = $g5['shop_search_refresh_queue_table'];
    $queue_sql = "INSERT INTO {$queue_table} (shop_id)
                   VALUES ('{$shop_id}')
                   ON CONFLICT (shop_id) DO NOTHING";
    sql_query_pg($queue_sql);
}

// 가맹점 편의시설 처리
if ($shop_id && isset($_POST['amenities_id_list'])) {
    $amenities_id_list = trim($_POST['amenities_id_list']);
    
    // 기존 shop_amenities 데이터 삭제 (해당 shop_id의 모든 레코드)
    $delete_sql = " DELETE FROM {$g5['shop_amenities_table']} WHERE shop_id = '{$shop_id}' ";
    sql_query_pg($delete_sql);
    
    // amenities_id_list가 있으면 새로운 레코드 추가
    if (!empty($amenities_id_list)) {
        $amenity_ids_arr = explode(',', $amenities_id_list);
        $amenity_ids_arr = array_map('trim', $amenity_ids_arr);
        $amenity_ids_arr = array_filter($amenity_ids_arr, function($id) {
            return !empty($id) && is_numeric($id);
        });
        
        if (count($amenity_ids_arr) > 0) {
            $insert_sql = " INSERT INTO {$g5['shop_amenities_table']} (shop_id, amenity_id, available_yn) VALUES ";
            $values = array();
            foreach ($amenity_ids_arr as $amenity_id) {
                $amenity_id = (int)$amenity_id;
                if ($amenity_id > 0) {
                    $values[] = "('{$shop_id}', '{$amenity_id}', 'Y')";
                }
            }
            if (count($values) > 0) {
                $insert_sql .= implode(',', $values);
                sql_query_pg($insert_sql);
            }
        }
    }
}

foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

if($w == 'u') {
    goto_url('./store_form.php?'.$qstr.'&w=u&shop_id='.$shop_id, false);
}
else {
    alert('가맹점 추가는 플랫폼 관리자만 가능합니다.');
}

