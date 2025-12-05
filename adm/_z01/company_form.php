<?php
$sub_menu = "920200";
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/shop_category.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
@auth_check($auth[$sub_menu],'w');

$form_input = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}
$com = (isset($com) && is_array($com)) ? $com : [];
$comf = (isset($comf) && is_array($comf)) ? $comf : ['comf_f_arr'=>[], 'comf_fidxs'=>[], 'comf_lst_idx'=>0, 'fle_db_idx'=>$shop_id??0];
$comi = (isset($comi) && is_array($comi)) ? $comi : ['comi_f_arr'=>[], 'comi_fidxs'=>[], 'comi_lst_idx'=>0, 'fle_db_idx'=>$shop_id??0];
/*
$cats
(
    [10] => Array
        (
            [name] => 반려동물
            [mid] => Array
                (
                    [1010] => 반려동물 > 미용실
                    [1020] => 반려동물 > 호텔
                    [1030] => 반려동물 > 병원
                    [1040] => 반려동물 > 카페
                    [1050] => 반려동물 > 유치원
                    [1060] => 반려동물 > 장례
                    [1070] => 반려동물 > 훈련센터
                    [1080] => 반려동물 > 산책서비스
                    [1090] => 반려동물 > 사진촬영
                    [10a0] => 반려동물 > 용품대여
                    [10b0] => 반려동물 > 반려동물스파
                )

        )
*/
//echo $g5['container_sub_title'];
/*
$com['shop_id'] => 600
$com['category_id'] => a020
$com['name'] => 워시존스페셜
$com['business_no'] => 012-31-87659
$com['owner_name'] => 정서현
$com['contact_email'] => washzonespecial600@carwash.com
$com['contact_phone'] => 010-6000-0600
$com['zipcode'] => 63334 
$com['addr1'] => 부산광역시 북구 낙동대로1766번길 81-24
$com['addr2'] => 1층
$com['addr3'] => 
$com['latitude'] => 35.207
$com['longitude'] => 129.002
$com['url'] => https://washzonespecial600.co.kr
$com['max_capacity'] => 50
$com['status'] => active
$com['created_at'] => 2025-04-17 22:50:00
$com['updated_at'] => 2025-04-17 22:50:00
$com['reservelink_yn'] => N
$com['reservelink'] => 
$com['reserve_tel'] => 
$com['shop_description'] => 워시존스페셜에 오신 것을 진심으로 환영합니다. 저희는 친환경 세차, 프리미엄 스팀 세차, 버블 세차 등 다양한 차량 관리 서비스를 제공하며, 고객님의 소중한 차량을 깨끗하고 산뜻하게 유지해드립니다. 편안한 공간과 전문적인 서비스로 만족을 드리겠습니다. 워시존스페셜에서 내 차의 새로운 변화를 경험해보세요!
$com['bank_account'] => 318-83-247789
$com['bank_name'] => 
$com['bank_holder'] => 
$com['settlement_type'] => manual
$com['settlement_cycle'] => monthly
$com['settlement_day'] => 25
$com['tax_type'] => with_vat
$com['settlement_memo'] => 
$com['is_active'] => Y
$com['shop_name'] => 워시존스페셜
$com['cancel_policy'] => 예약취소규정입니다
$com['point_rate'] => 5.00
$com['names'] => 워시존스페셜
$com['branch'] => 본사
$com['shop_parent_id'] => 0
*/
// 안전한 기본값 설정 및 입력 수신
$w = isset($w) ? (string)$w : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');

$shop_id = isset($shop_id) ? (int)$shop_id : (isset($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : 0);

// 카테고리 연결 조회 (shop_id가 있을 때만)
$carr = [];

if (isset($shop_id) && $shop_id) {
	$csql = " SELECT * FROM {$g5['shop_category_relation_table']} WHERE shop_id = '".$shop_id."' ORDER BY sort, category_id ";
	$cres = sql_query_pg($csql);
	if ($cres && is_object($cres) && isset($cres->result)) {
		for($i=0;$row=sql_fetch_array_pg($cres->result);$i++) {
	        $carr[] = $row['category_id'];
		}
	}
}

if ($w == '') {
    $shop_id = 0;
    $com['status'] = 'ok';
    $html_title = '추가';

}
else if ($w == 'u') {
	$com = get_table_meta_pg('shop','shop_id',$shop_id);
	// print_r2($com);exit;
	
	if (!$com['shop_id'])
		alert('존재하지 않는 가맹점자료입니다.');

	$html_title = '수정';

	// 본사 com_idx_parent가 있으면 com_name_parent를 가져온다.
	$sql = " SELECT name FROM {$g5['shop_table']} WHERE shop_id = '{$com['shop_parent_id']}' ";
	
	$pcom = sql_fetch_pg($sql);
	$com['com_name_parent'] = (!empty($pcom['name'])) ? $pcom['name'] : '';


	$com['name'] = get_text($com['name']);
	$com['contact_phone'] = get_text($com['contact_phone']);
	$com['url'] = get_text($com['url']);
	$com['addr1'] = ($com['addr1'])?get_text($com['addr1']):'';
	$com['addr2'] = ($com['addr2'])?get_text($com['addr2']):'';
	$com['addr3'] = ($com['addr3'])?get_text($com['addr3']):'';
	
	
	// 업체관련 파일(사업자등록증등등...)
	$sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop' AND fle_type = 'comf' AND fle_dir = 'shop/shop_file' AND fle_db_idx = '{$shop_id}' ORDER BY fle_reg_dt DESC ";
	// echo $sql;exit;
	$rs = sql_query_pg($sql);

	$comf['comf_f_arr'] = array();
	$comf['comf_fidxs'] = array();
	$comf['comf_lst_idx'] = 0;
	$comf['fle_db_idx'] = $shop_id;
	for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
		$is_s3file_yn = is_s3file($row2['fle_path']);
		$row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="comf_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
		$row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
		'<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
		<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
		$comf['fle_db_idx'] = $row2['fle_db_idx'];
		@array_push($comf['comf_f_arr'], array('file'=>$row2['down_del']));
		@array_push($comf['comf_fidxs'], $row2['fle_idx']);
	}


	// 가맹점관련 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop' AND fle_type = 'comi' AND fle_dir = 'shop/shop_img' AND fle_db_idx = '{$shop_id}' ORDER BY fle_sort, fle_reg_dt DESC ";
    // echo $sql;exit;
	$rs = sql_query_pg($sql);
    $comi_wd = 110;
    $comi_ht = 80;
    $comi['comi_f_arr'] = array();
    $comi['comi_fidxs'] = array();
    $comi['comi_lst_idx'] = 0;
    $comi['fle_db_idx'] = $shop_id;
	if ($rs && is_object($rs) && isset($rs->result)) {
		for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
			$is_s3file_yn = is_s3file($row2['fle_path']);
			$row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$comi_wd.':'.$comi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
			$row2['thumb'] = '<span class="sp_thumb"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$comi_wd.'px;height:'.$comi_ht.'px;border:1px solid #ddd;"></span>'.PHP_EOL;
			$row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a class="a_download" href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">(<span class="sp_size">'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>)[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label class="lb_delchk" for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="comi_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
			'<br><span class="sp_sql"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
			<br><span class="sp_orig_img_url"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
			<br><span class="sp_thumb_img_url"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
			$comi['fle_db_idx'] = $row2['fle_db_idx'];
			@array_push($comi['comi_f_arr'], array('file'=>$row2['down_del'],'id'=>$row2['fle_idx']));
			@array_push($comi['comi_fidxs'], $row2['fle_idx']);
		}
	}

	// 가맹점관리담당자
	$mng_sql = " SELECT mb_id, mb_name, mb_hp, mb_email, mb_2 FROM {$g5['member_table']} WHERE mb_1 = '{$shop_id}' AND mb_level < 6 AND mb_leave_date IS NULL AND mb_intercept_date IS NULL ORDER BY mb_id,mb_name,mb_datetime ";
	$mng_res = sql_query($mng_sql,1);
	$com['shop_managers_text'] = '';
	for($i=0;$row=sql_fetch_array($mng_res);$i++) {
		$com['shop_managers_text'] .= ($com['shop_managers_text'] ? ', ' : '').$row['mb_name'].' <span class="sp_rank">'.$rank_arr[$row['mb_2']].'</span> <span class="">'.$row['mb_hp'].'</span> ['.$row['mb_id'].']<br>'.PHP_EOL;
	}

	// Fetch keywords for the shop
	$ksql = " SELECT k.term AS keyword 
			FROM {$g5['shop_keyword_table']} sk
			JOIN {$g5['keywords_table']} k ON sk.keyword_id = k.keyword_id
			WHERE sk.shop_id = '" . $shop_id . "' 
			ORDER BY sk.weight DESC ";
	// echo $ksql;exit;
	$kresult = sql_query_pg($ksql);
	$keywords = [];
	while ($row = sql_fetch_array_pg($kresult->result)) {
		$keywords[] = $row['keyword'];
	}
	$com['shop_keywords'] = implode(',', $keywords);
	
	// shop_amenities 테이블에서 현재 선택된 편의시설 ID 목록 가져오기
	$sa_sql = " SELECT amenity_id FROM {$g5['shop_amenities_table']} WHERE shop_id = '{$shop_id}' AND available_yn = 'Y' ORDER BY amenity_id ";
	$sa_result = sql_query_pg($sa_sql);
	$selected_amenity_ids = [];
	if ($sa_result && is_object($sa_result) && isset($sa_result->result)) {
		while ($row = sql_fetch_array_pg($sa_result->result)) {
			$selected_amenity_ids[] = $row['amenity_id'];
		}
	}
	// shop 테이블의 amenities_id_list가 없거나 비어있으면 shop_amenities 테이블에서 가져온 값으로 설정
	if (empty($com['amenities_id_list']) && !empty($selected_amenity_ids)) {
		$com['amenities_id_list'] = implode(',', $selected_amenity_ids);
	}
	
	// 편의시설 목록 조회
	$amenities_sql = " SELECT amenity_id, amenity_name, icon_url_enabled, icon_url_disabled 
						FROM {$g5['amenities_table']} 
						ORDER BY amenity_id ";
	$amenities_result = sql_query_pg($amenities_sql);
	$amenities_list = [];
	if ($amenities_result && is_object($amenities_result) && isset($amenities_result->result)) {
		while ($row = sql_fetch_array_pg($amenities_result->result)) {
			$amenities_list[] = $row;
		}
	}
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 편의시설 목록 조회 (신규 등록 시에도 필요)
if ($w == '') {
	$amenities_sql = " SELECT amenity_id, amenity_name, icon_url_enabled, icon_url_disabled 
						FROM {$g5['amenities_table']} 
						ORDER BY amenity_id ";
	$amenities_result = sql_query_pg($amenities_sql);
	$amenities_list = [];
	if ($amenities_result && is_object($amenities_result) && isset($amenities_result->result)) {
		while ($row = sql_fetch_array_pg($amenities_result->result)) {
			$amenities_list[] = $row;
		}
	}
}

// print_r2($rank_arr);exit;
// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
// $check_array=array();
// for ($i=0;$i<sizeof($check_array);$i++) {
// 	${$check_array[$i].'_'.$com[$check_array[$i]]} = ' checked';
// }

$html_title = ($w=='')?'추가':'수정'; 
// exit;
$g5['title'] = '가맹점 '.$html_title;
//include_once('./_top_menu_company.php');

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<script>
let cats = <?=json_encode($cats)?>;
</script>
<form name="form01" id="form01" action="./company_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<?=$form_input??''?>
<div class="local_desc01 local_desc">
    <p>가맹점정보를 관리해 주세요.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
	</colgroup>
	<tbody>
	<tr>
		<th scope="row">업종(분류)<strong class="sound_only">필수</strong></th>
		<td<?=(($w=='u') ? '':' colspan="3"')?>>
			<?php echo help("대분류와 중분류를 선택해서 '업종추가'를 클릭하면 아래에 항목이 추가됩니다."); ?>
			<select id="cat1" class="frm_input">
				<option value="">::대분류선택::</option>
				<?php foreach($cats as $k1 => $v1){ ?>
					<option value="<?=$k1?>"><?=$v1['name']?></option>
				<?php } ?>
			</select>
			<select id="cat2" class="frm_input">
				<option value="">::중분류선택::</option>
			</select>
			<a href="javascript:" id="cat_add" class="mm-blue-btn">업종추가</a>			
			<div id="cat_box" class="mt-2">
				<?php ;//echo help("업종(분류)항목을 위아래로 이동시켜 순서를 변경할 수 있습니다."); ?>
				<input type="hidden" name="category_ids" value="<?=implode(',', $carr)?>" class="border border-black w-[400px]">
				<ul id="cat_ul" class="ca-ul">
					<?php 
					$n = 1;
					foreach($carr as $category_id) { ?>
					<li class="cat_li" data-id="<?=$category_id?>">
						<span class="sp_sort"><?=$n?></span>
						<span class="sp_cat"><?=$cats[substr($category_id, 0, 2)]['mid'][$category_id]?></span>
						<i class="fa fa-times" aria-hidden="true"></i>
					</li>
					<?php 
					$n++;
					}
					?>
				</ul>
			</div>
		</td>
		<?php if($w=='u') { 
			$m_sql = " SELECT mb_name, mb_id, mb_hp FROM {$g5['member_table']} WHERE mb_level IN (4,5) AND mb_1 = '{$shop_id}' AND mb_2 = 'Y' ";
			$m_res = sql_query($m_sql,1);
			$com['shop_managers_text'] = '';
			for($j=0; $m_row=sql_fetch_array($m_res); $j++){
				// print_r2($m_row);
				$mbt = get_gmeta('member',$m_row['mb_id']);
                // print_r2($mbt);
                if(count($mbt)){
                    $m_row = array_merge($m_row,$mbt);
                }
				// print_r2($m_row);
				$com['shop_managers_text'] .= $m_row['mb_name'].'<span>'.($rank_arr[$m_row['mb_rank']]??'').'</span> <span class="font_size_8">('.$m_row['mb_id'].')</span> ['.formatPhoneNumber($m_row['mb_hp']).']<br>';
			}
		?>
		<th scope="row">가맹점관리자<strong class="sound_only">필수</strong>
			<a href="javascript:" shop_id="<?=$com['shop_id']?>" id="btn_manager" class="ml-2 !text-blue-500 text-[16px]"><i class="fa fa-pencil-square-o"></i></a>
		</th>
		<td>
			<?php echo $com['shop_managers_text']; ?>
		</td>
		<?php } ?>
	</tr>
	<tr>
		<th scope="row">업체명<strong class="sound_only">필수</strong></th>
		<td>
			<input type="hidden" name="shop_id" value="<?=$shop_id?>">
			<input type="text" name="name" value="<?=$com['name']??''?>" placeholder="업체명" id="name" class="frm_input">
		</td>
		<th scope="row">사업자등록번호<strong class="sound_only">필수</strong></th>
		<td>
			<input type="text" name="business_no" value="<?=formatBizNumber($com['business_no']??'')?>" class="frm_input" size="20" minlength="2" maxlength="12">
		</td>
	</tr>
    <tr>
        <th scope="row">가맹점명<strong class="sound_only">필수</strong></th>
		<td>
			<input type="text" name="shop_name" value="<?=$com['shop_name']??''?>" id="shop_name" class="frm_input">
		</td>
        <th scope="row">지점명<strong class="sound_only">필수</strong></th>
        <td>
			<input type="text" name="branch" value="<?=$com['branch']??''?>" placeholder="지점명" id="branch" class="frm_input"></td>
        </td>
    </tr>
	<tr> 
		<th scope="row">대표이메일<strong class="sound_only">필수</strong></th>
		<td>
			<?php echo help("세금계산서, 계약서, 약정서 등 모든 거래 시 소통할 수 있는 이메일 정보를 필수로 등록하세요."); ?>
			<input type="text" name="contact_email" value="<?=$com['contact_email']??''?>" id="contact_email" class="frm_input" style="width:60%;">
		</td>
		<th scope="row">홈페이지주소</th>
		<td>
			<?php echo help("http(s):// 없이 그냥 홈페이지 주소만 입력해 주세요. ex. www.naver.com "); ?>
			<input type="text" name="url" value="<?=$com['url']??''?>" id="com_url" class="frm_input" style="width:60%">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="owner_name">대표자명<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="owner_name" value="<?=$com['owner_name']??''?>" id="owner_name" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row"><label for="contact_phone">업체전화번호<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="contact_phone" value="<?=formatPhoneNumber($com['contact_phone']??'')?>" id="contact_phone" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>	
	<tr>
		<th scope="row">사업장 주소<strong class="sound_only">필수</strong></th>
		<td class="td_addr_line" style="line-height:280%;">
			<?php echo help("사업장 주소가 명확하지 않은 경우 [주소검색]을 통해 정확히 입력해 주세요."); ?>
			<label for="zipcode" class="sound_only">우편번호</label>
			<input type="text" name="zipcode" value="<?=$com['zipcode']??''?>" id="zipcode" readonly class="frm_input readonly" maxlength="6" style="width:65px;">
			<?php if(!@auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'zipcode', 'addr1', 'addr2', 'addr3');">주소 검색</button>
			<?php } ?>
			<br>
			<input type="text" name="addr1" value="<?=$com['addr1']??''?>" id="addr1" readonly class="w-[400px] frm_input readonly">
			<label for="addr1">기본주소</label><br>
			<input type="text" name="addr2" value="<?=$com['addr2']??''?>" id="addr2" class="w-[400px] frm_input">
			<label for="addr2">상세주소</label>
			<br>
			<input type="text" name="addr3" value="<?=$com['addr3']??''?>" id="addr3" class="w-[400px] frm_input">
			<label for="addr3">참고항목</label>
		</td>
		<th scope="row">업체명 히스토리</th>
		<td colspan="3" class="align-top">
			<?php echo help("업체명이 바뀌면 자동으로 히스토리가 기록됩니다."); ?>
			<textarea rows="10" name="names" readonly class="readonly frm_input w-[100%]"><?= $is_team_manager ? ($com['names'] ?? '') : '' ?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row">최대수용인원</th>
		<td>
			<input type="number" name="max_capacity" value="<?=$com['max_capacity']??''?>" id="max_capacity" class="frm_input text-center w-[80px]" size="10" maxlength="5">
		</td>
		<th scope="row">예약링크</th>
		<td>
			<?php echo help("예약할 링크주소를 입력해 주세요."); ?>
			<input type="text" name="reservelink" value="<?=$com['reservelink']??''?>" id="reservelink" class="frm_input" style="width:60%;">
		</td>
	</tr>
	<tr>
		<th scope="row">예약전화번호</th>
		<td>
			<input type="text" name="reserve_tel" value="<?=formatPhoneNumber($com['reserve_tel']??'')?>" id="reserve_tel" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row">정산 은행계좌번호</th>
		<td>
			<input type="text" name="bank_account" value="<?=$com['bank_account']??''?>" id="bank_account" class="frm_input w-[200px]" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">정산 은행명</th>
		<td>
			<input type="text" name="bank_name" value="<?=$com['bank_name']??''?>" id="bank_name" class="frm_input w-[200px]" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row">정산 예금주명</th>
		<td>
			<input type="text" name="bank_holder" value="<?=$com['bank_holder']??''?>" id="bank_holder" class="frm_input w-[200px]" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">정산유형</th>
		<td>
			<select name="settlement_type" id="settlement_type">
				<option value="manual">수동정산</option>
				<option value="auto">자동정산</option>
			</select>
			<script>$('select[name="settlement_type"]').val('<?=($w == '' ? 'manual' : $com['settlement_type'])?>');</script>
		</td>
		<th scope="row">정산주기</th>
		<td>
			<select name="settlement_cycle" id="settlement_cycle">
				<option value="monthly">월별</option>
				<option value="weekly">주별</option>
				<option value="daily">일별</option>
			</select>
			<script>$('select[name="settlement_cycle"]').val('<?=($w == '' ? 'monthly' : $com['settlement_cycle'])?>');</script>
			&nbsp;&nbsp;
			<input type="number" name="settlement_day" value="<?=($w == '' ? 25 : $com['settlement_day'])?>" id="settlement_day" class="frm_input text-center w-[60px]" size="10" maxlength="2">
			<?php echo help("정산주기가 '월별'일 경우 매월 정산일을, '주별'일 경우 매주 정산요일(1:월요일~7:일요일)을, '일별'일 경우 매일 정산을 의미합니다."); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">과세유형</th>
		<td>
			<select name="tax_type" id="tax_type">
				<option value="normal">일반과세자</option>
				<option value="simple">간이과세자</option>
				<option value="exempt">면세사업자</option>
				<option value="freelancer">프리랜서</option>
			</select>
			<script>$('select[name="tax_type"]').val('<?=(($w == '' || $com['tax_type']=='') ? 'normal' : $com['tax_type'])?>');</script>
		</td>
		<th scope="row">정산대상여부</th>
		<td>
			<label for="is_active_yes"><input type="radio" name="is_active" id="is_active_yes" value="Y" <?=($w == '' || $com['is_active']=='Y')?'checked':'';?>> 정산대상</label>&nbsp;&nbsp;
			<label for="is_active_no"><input type="radio" name="is_active" id="is_active_no" value="N" <?=($com['is_active']=='N')?'checked':'';?>> 정산제외</label>
		</td>
	</tr>
	<tr>
        <th scope="row">가맹점관련파일</th>
        <td colspan="3">
            <?php echo help("가맹점관련 파일들(사업장등록증)을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file_comf" name="comf_datas[]" multiple class="">
            <?php
			$comf_list = (isset($comf['comf_f_arr']) && is_array($comf['comf_f_arr'])) ? $comf['comf_f_arr'] : [];
			if (!empty($comf_list)){
				echo '<ul>'.PHP_EOL;
				foreach ($comf_list as $i => $item) {
					$fileHtml = is_array($item) && isset($item['file']) ? $item['file'] : '';
					echo "<li>[".($i+1).']'.$fileHtml."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
            ?>
        </td>
	</tr>
	<tr>
        <th scope="row">가맹점관련이미지</th>
        <td colspan="3">
            <?php echo help("가맹점관련 이미지들을 등록하고 관리해 주시면 됩니다. 최대10개까지 등록 가능합니다."); ?>
            <?php echo help("각 이미지 요소를 드래그&드롭으로 이미지간의 순서를 변경할 수 있습니다. 첫번째 이미지가 메인이미지입니다."); ?>
			<input type="file" id="multi_file_comi" name="comi_datas[]" multiple class="" maxlength="10" data-maxfile="700" data-maxsize="7000">
			<input type="hidden" name="branch_ids" value="<?=implode(',', $comi['comi_fidxs'])?>" class="border border-black w-[400px]">
            <?php
			$comi_list = (isset($comi['comi_f_arr']) && is_array($comi['comi_f_arr'])) ? $comi['comi_f_arr'] : [];
			// print_r2($comi_list);
			if (!empty($comi_list)){
				echo '<ul id="branch_imgs">'.PHP_EOL;
				foreach ($comi_list as $i => $item) {
					$fileHtml = is_array($item) && isset($item['file']) ? $item['file'] : '';
					// echo "<li class='".$branch_li_class."'>[".($i+1).']'.$fileHtml."</li>".PHP_EOL;
					echo "<li class='branch_li' data-id='".$item['id']."'>[<span class='sp_sort'>".($i+1).'</span>]'.$fileHtml."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
	</tr>
	<tr>
		<th scope="row">위도/경도<strong class="sound_only">필수</strong></th>
		<td>
			<input type="text" name="latitude" value="<?=$com['latitude']??''?>" placeholder="위도" id="latitude" class="frm_input">&nbsp;&nbsp;/&nbsp;
			<input type="text" name="longitude" value="<?=$com['longitude']??''?>" placeholder="경도" id="longitude" class="frm_input">
		</td>
		<th scope="row"><label for="status">상태</label></th>
		<td>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="status" id="status">
				<option value="active">정상<?=(($is_dev_manager)?'(active)':'')?></option>
				<option value="pending">대기<?=(($is_dev_manager)?'(pending)':'')?></option>
				<option value="closed">폐업<?=(($is_dev_manager)?'(closed)':'')?></option>
				<option value="shutdown">금지<?=(($is_dev_manager)?'(shutdown)':'')?></option>
			</select>
			<script>$('select[name="status"]').val('<?=($w == '' ? 'active' : $com['status'])?>');</script>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="reservation_mode">예약 모드</label></th>
		<td colspan="3">
			<?php echo help("예약 모드를 선택해 주세요. SERVICE_ONLY(공간 미사용), SPACE_ONLY(공간만 예약), SERVICE_AND_SPACE(서비스+공간 둘 다 사용)"); ?>
			<select name="reservation_mode" id="reservation_mode" class="frm_input">
				<option value="SERVICE_ONLY">공간 미사용 (SERVICE_ONLY)</option>
				<option value="SPACE_ONLY">공간만 예약 (SPACE_ONLY)</option>
				<option value="SERVICE_AND_SPACE">서비스+공간 (SERVICE_AND_SPACE)</option>
			</select>
			<script>$('select[name="reservation_mode"]').val('<?=($w == '' ? 'SERVICE_ONLY' : ($com['reservation_mode'] ?? 'SERVICE_ONLY'))?>');</script>
		</td>
	</tr>
	<?php if($w == 'u') { ?>
	<tr>
		<th scope="row"><label for="shop_management_menu">관리옵션선택</label></th>
        <td colspan="3">
			<?php
			$mng_menu_strs = (isset($set_conf['set_shop_management_menu']) && $set_conf['set_shop_management_menu']) ? explode(',', $set_conf['set_shop_management_menu']) : [];
			$mng_menu_opts = '';
			$mn_cnt = 1;
			foreach($mng_menu_strs as $mstr) {
				$mstr = trim($mstr);
				if ($mstr) {
					$marr = explode('=', $mstr);
					if (isset($marr[0]) && $marr[0]) {
						$mng_menu_opts .= '<label for="mng_menu_'.$mn_cnt.'"><input type="checkbox" data-val="'.$marr[0].'" id="mng_menu_'.$mn_cnt.'" class="mng_menu" value="1"'.(isset($com['mng_menus']) && $com['mng_menus'] && in_array($marr[0], explode(',', $com['mng_menus'])) ? ' checked' : '').'> '.$marr[1].'</label>'.PHP_EOL;
					}
				}
				$mn_cnt++;
			}
			?>
			<div>

			</div>
			<input type="hidden" name="mng_menus" id="shop_management_menu" value="<?=$com['mng_menus']??''?>" class="border border-black w-[400px]">
			<div class="chk_list_box">
				<?=$mng_menu_opts??''?>
			</div>
        </td>
	</tr>
	<?php } ?>
	<tr>
		<th scope="row"><label for="">가맹점의 검색키워드</label></th>
		<td colspan="3">
			<?php
			if($w == 'u'){
				$kwds = (isset($com['shop_keywords']) && $com['shop_keywords']) ? explode(',', $com['shop_keywords']) : [];
			}
			?>
			<?php 
			echo help("등록을 원하시는 키워드를 한 개씩 입력한 후 '키워드추가'버튼을 클릭해 주세요.(최대 10개까지 가능)"); 
			echo help("드래그&드롭으로 키워드의 순서를 변경할 수 있습니다.(중요한 키워드일수록 앞쪽에 배치해 주세요.)"); 
			?>
			<input type="text" id="input_keyword" placeholder="" class="frm_input w-[200px]">
			<a href="javascript:" id="add_keyword" class="mm-blue-btn">키워드추가</a>
			<input type="hidden" name="shop_keywords" id="shop_keywords" value="<?=$com['shop_keywords']??''?>" class="border w-full">
			<div id="keyword_box" class="border border-4 border-gray-200 pl-2 pt-2 mt-2 min-h-[40px]">
				<?php 
				if(!empty($kwds)){
					$kn = 0;
					foreach($kwds as $kwd){ 
				?>
					<span class="keyword-chip inline-flex items-center bg-gray-200 rounded px-2 py-1 mr-2 mb-2" data-index="<?=$kn?>">
						<span class="sp_cont"><?=$kwd?></span>	
						<button type="button" class="ml-2 text-red-500" aria-label="키워드 삭제">x</button>
					</span>
				<?php 
					}
				}
				?>
			</div>
		</td>
	</tr>
    <tr>
        <th scope="row"><label for="settlement_memo">메모</label></th>
        <td colspan="3">
            <textarea name="settlement_memo" id="settlement_memo"><?=$com['settlement_memo']??''?></textarea>
        </td>
    </tr>
	<tr>
		<th scope="row"><label for="cancel_policy">예약취소규정</label></th>
		<td colspan="3">
			<textarea name="cancel_policy" id="cancel_policy" class="w-[100%]" rows="5"><?=$com['cancel_policy']??''?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="shop_description">업체설명</label></th>
		<td colspan="3">
			<?php echo help("업체에 대한 상세 설명을 입력해 주세요."); ?>
			<textarea name="shop_description" id="shop_description" class="w-[100%]" rows="5"><?=$com['shop_description']??''?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="point_rate">포인트 적립율</label></th>
		<td>
			<?php echo help("결제 시 포인트 적립율을 설정합니다. (이 필드는 사용하지 않습니다. 포인트는 플랫폼에서 제공합니다.)"); ?>
			<input type="number" name="point_rate" value="<?=$com['point_rate']??0?>" id="point_rate" class="frm_input text-center w-[100px]" step="0.01" min="0" max="100">
			<span>%</span>
		</td>
		<th scope="row"><label for="cancellation_period">결제취소가능시간</label></th>
		<td>
			<?php echo help("예약일시를 기준으로 취소 및 환불처리가 가능한 유효시간(시간 단위)을 설정합니다."); ?>
			<input type="number" name="cancellation_period" value="<?=$com['cancellation_period']??1?>" id="cancellation_period" class="frm_input text-center w-[100px]" min="1">
			<span>시간</span>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="notice">공지 및 알림</label></th>
		<td colspan="3">
			<?php echo help("가맹점 관련 공지사항 및 알림 내용을 입력해 주세요."); ?>
			<textarea name="notice" id="notice" class="w-[100%]" rows="5"><?=$com['notice']??''?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row">소셜미디어 URL</th>
		<td colspan="3">
			<div class="grid grid-cols-3 gap-4">
				<div>
					<label for="blog_url">블로그 URL</label>
					<?php echo help("대표 블로그 URL을 입력해 주세요."); ?>
					<input type="text" name="blog_url" value="<?=$com['blog_url']??''?>" id="blog_url" class="frm_input w-full" placeholder="https://blog.example.com">
				</div>
				<div>
					<label for="instagram_url">인스타그램 URL</label>
					<?php echo help("인스타그램 URL을 입력해 주세요."); ?>
					<input type="text" name="instagram_url" value="<?=$com['instagram_url']??''?>" id="instagram_url" class="frm_input w-full" placeholder="https://instagram.com/example">
				</div>
				<div>
					<label for="kakaotalk_url">카카오톡 채널 URL</label>
					<?php echo help("카카오톡 채널 URL을 입력해 주세요. (자사홈피가 없으면 KEY값 발급을 받을 수 없습니다.)"); ?>
					<input type="text" name="kakaotalk_url" value="<?=$com['kakaotalk_url']??''?>" id="kakaotalk_url" class="frm_input w-full" placeholder="https://pf.kakao.com/example">
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="amenities_id_list">편의시설 ID 목록</label></th>
		<td colspan="3">
			<?php echo help("편의시설을 선택해 주세요. 버튼을 클릭하여 활성/비활성 상태를 전환할 수 있습니다."); ?>
			<input type="hidden" name="amenities_id_list" value="<?=$com['amenities_id_list']??''?>" id="amenities_id_list" class="frm_input w-full" placeholder="23,34,543">
			<div id="amenities_button_container" class="mt-3 flex flex-wrap gap-2">
				<?php 
				$selected_amenities = isset($com['amenities_id_list']) && $com['amenities_id_list'] ? explode(',', $com['amenities_id_list']) : [];
				$selected_amenities = array_map('trim', $selected_amenities);
				if (!empty($amenities_list)) {
					foreach ($amenities_list as $amenity) {
						$is_active = in_array((string)$amenity['amenity_id'], $selected_amenities);
						$active_class = $is_active ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700';
						$icon_url = $is_active ? ($amenity['icon_url_enabled'] ?? '') : ($amenity['icon_url_disabled'] ?? '');
				?>
					<button type="button" 
							class="amenity-btn px-4 py-2 rounded border border-gray-300 <?=$active_class?> hover:opacity-80 transition-opacity" 
							data-amenity-id="<?=$amenity['amenity_id']?>"
							data-amenity-name="<?=htmlspecialchars($amenity['amenity_name'])?>"
							data-icon-enabled="<?=htmlspecialchars($amenity['icon_url_enabled'] ?? '')?>"
							data-icon-disabled="<?=htmlspecialchars($amenity['icon_url_disabled'] ?? '')?>">
						<?php if ($icon_url): ?>
							<img src="<?=$icon_url?>" alt="<?=htmlspecialchars($amenity['amenity_name'])?>" class="amenity-icon inline-block w-5 h-5 mr-2" onerror="this.style.display='none'">
						<?php else: ?>
							<img src="" alt="<?=htmlspecialchars($amenity['amenity_name'])?>" class="amenity-icon inline-block w-5 h-5 mr-2" style="display:none;">
						<?php endif; ?>
						<?=htmlspecialchars($amenity['amenity_name'])?>
					</button>
				<?php 
					}
				} else {
					echo '<p class="text-gray-500">등록된 편의시설이 없습니다.</p>';
				}
				?>
			</div>
		</td>
	</tr>
	<?php if($w == 'u') { ?>
	<tr>
		<th scope="row">가맹점명 히스토리</th>
		<td colspan="3">
			<?php echo help("가맹점명이 변경되면 자동으로 히스토리가 기록됩니다."); ?>
			<textarea rows="10" name="shop_names" readonly class="readonly frm_input w-[100%]"><?= $is_team_manager ? ($com['shop_names'] ?? '') : '' ?></textarea>
		</td>
	</tr>
	<?php } ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./company_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');