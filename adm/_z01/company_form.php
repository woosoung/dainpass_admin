<?php
$sub_menu = "920800";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

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
if ($w == '') {
    $com_idx = 0;
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
	
	// 관련 파일(post_file) 추출
	// $sql = "SELECT * FROM {$g5['dain_file_table']} 
	// 		WHERE fle_db_tbl = 'company' AND fle_db_idx = '".$com['com_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	// $rs = sql_query($sql,1);
	// exit;
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array();
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$com[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '가맹점 '.$html_title;
//include_once('./_top_menu_company.php');
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

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
		<th scope="row">업체명<strong class="sound_only">필수</strong>/지점명</th>
		<td>
			<input type="hidden" name="shop_id" value="<?=$shop_id?>">
			<input type="text" name="name" value="<?=$com['name']?>" placeholder="업체명" id="name" class="frm_input">
			<input type="text" name="branch" value="<?=$com['branch']?>" placeholder="지점명" id="branch" class="frm_input">
		</td>
		<th scope="row">업체구분</th>
		<td>
			<select name="com_type" id="com_type" class="frm_input">
                <?=$set_conf['set_com_type_option']?>
            </select>
            <?php if($w == 'u') { ?>
            <script>$('#com_type').val('<?=$com['com_type']?>');</script>
            <?php } ?>
		</td>
	</tr>
    <tr>
        <th scope="row">가맹점명</th>
		<td>
			<input type="text" name="shop_name" value="<?=$com['shop_name']??''?>" id="shop_name" class="frm_input">
		</td>
        <th scope="row">본사선택</th>
        <td>
			<?php echo help("본사설정을 해제하려면 '본사설정해제'에 체크를 넣고 확인을 눌러 주세요."); ?>
            <input type="hidden" name="com_idx_parent" id="com_idx_parent" value="<?=$com['com_idx_parent']?>">
            <input type="text" name="com_name_parent" id="com_name_parent" value="<?=$com['com_name_parent']?>" readonly class="readonly frm_input">
            <a href="javascript:" data-url="./_win_company_select.php" class="mm-btn com_select">본사선택</a>
			<label for="head_clear" class="ml-2">
                <input type="checkbox" name="head_clear" id="head_clear" value="1" class="border"> 본사설정해제
            </label>
        </td>
    </tr>
	<tr>
		<th scope="row">업체명 히스토리</th>
		<td>
			<?php echo help("업체명이 바뀌면 자동으로 히스토리가 기록됩니다."); ?>
			<input type="<?=($is_admin)?'text':'hidden';?>" name="com_names" value="<?php echo $com['com_names'] ?>" id="com_names" readonly class="readonly frm_input w-[100%]" <?=(!$is_admin)?'readonly':''?>>
            <span style="display:<?=($is_admin)?'none':'';?>"><?php echo $com['com_names'] ?></span>
		</td>
		<th scope="row">API Key</th>
		<td>
			<?php echo help("API Key 할당 또는 갱신할 필요가 있으면 'Key설정'에 체크를 넣고 확인을 눌러 주세요."); ?>
			<input type="text" name="com_api_key" value="<?=$com['com_api_key']?>" id="com_api_key" readonly class="readonly frm_input w-[60%]">
			<label for="key_renewal" class="ml-2 text-blue-600">
				<input type="checkbox" name="key_renewal" id="key_renewal" value="1" class="border"> Key설정
            </label>
			<label for="key_clear" class="ml-2 text-red-600">
				<input type="checkbox" name="key_clear" id="key_clear" value="1" class="border"> Key삭제
            </label>
			<?php echo help("API Key를 삭제하려면 'Key삭제'에 체크를 넣고 확인을 눌러 주세요."); ?>
		</td>
	</tr>
	<tr> 
		<th scope="row">대표이메일<strong class="sound_only">필수</strong></th>
		<td>
			<?php echo help("세금계산서, 계약서, 약정서 등 모든 거래 시 소통할 수 있는 이메일 정보를 필수로 등록하세요."); ?>
			<input type="text" name="com_email" value="<?php echo $com['com_email'] ?>" id="com_email" class="frm_input" style="width:60%;">
			<?=$saler_mark?>
		</td>
		<th scope="row">홈페이지주소</th>
		<td>
			<?php echo help("http(s):// 없이 그냥 홈페이지 주소만 입력해 주세요. ex. www.naver.com "); ?>
			<input type="text" name="com_url" value="<?php echo $com['com_url'] ?>" id="com_url" class="frm_input" style="width:60%">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_president">대표자<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_president" value="<?php echo $com['com_president'] ?>" id="com_president" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row"><label for="com_tel">업체전화<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_tel" value="<?=formatPhoneNumber($com['com_tel'])?>" id="com_tel" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">사업자등록번호</th>
		<td>
			<input type="text" name="com_biz_no" value="<?=formatBizNumber($com['com_biz_no'])?>" class="frm_input" size="20" minlength="2" maxlength="12">
		</td>
		<th scope="row">팩스</th>
		<td>
			<input type="text" name="com_fax" value="<?=formatPhoneNumber($com['com_fax'])?>" id="com_fax" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">업종</th>
		<td>
			<input type="text" name="com_biz_type2" value="<?=$com['com_biz_type2']?>" class="frm_input w-[70%]">
		</td>
		<th scope="row">업태</th>
		<td>
			<input type="text" name="com_biz_type" value="<?=$com['com_biz_type']?>" class="frm_input w-[70%]">
		</td>
	</tr>	
	<tr>
		<th scope="row">사업장 주소 <?=$saler_mark?></th>
		<td class="td_addr_line" style="line-height:280%;">
			<?php echo help("사업장 주소가 명확하지 않은 경우 [주소검색]을 통해 정확히 입력해 주세요."); ?>
			<label for="com_zip" class="sound_only">우편번호</label>
			<input type="text" name="com_zip" value="<?php echo $com['com_zip']; ?>" id="com_zip" readonly class="frm_input readonly" maxlength="6" style="width:65px;">
			<?php if(!auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'com_zip', 'com_addr', 'com_addr2', 'com_addr3', 'com_addr_jibeon');">주소 검색</button>
			<?php } ?>
			<br>
			<input type="text" name="com_addr" value="<?php echo $com['com_addr'] ?>" id="com_addr" readonly class="w-[400px] frm_input readonly">
			<label for="com_addr1">기본주소</label><br>
			<input type="text" name="com_addr2" value="<?php echo $com['com_addr2'] ?>" id="com_addr2" readonly class="w-[400px] frm_input readonly">
			<label for="com_addr2">상세주소</label>
			<br>
			<input type="text" name="com_addr3" value="<?php echo $com['com_addr3'] ?>" id="com_addr3" class="w-[400px] frm_input">
			<label for="com_addr3">참고항목</label>
			<input type="hidden" name="com_addr_jibeon" value="<?php echo $com['com_addr_jibeon']; ?>" id="com_addr_jibeon" class="w-[400px] frm_input">
		</td>
        <th scope="row">업체관련파일</th>
        <td>
            <?php echo help("업체관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file_com" name="com_datas[]" multiple class="">
            <?php
            if(@count($com['com_f_arr'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($com['com_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$com['com_f_arr'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
	</tr>
	<tr>
		<th scope="row">위도/경도</th>
		<td>
			<input type="text" name="latitude" value="<?=$com['latitude']?>" placeholder="위도" id="latitude" class="frm_input">
			<input type="text" name="longitude" value="<?=$com['longitude']?>" placeholder="경도" id="longitude" class="frm_input">
		</td>
		<th scope="row"><label for="status">상태</label></th>
		<td>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="status" id="status">
				<?=$set_conf['set_shop_status_option']?>
			</select>
			<script>$('select[name="status"]').val('<?=$com['status']?>');</script>
		</td>
	</tr>
    <tr>
        <th scope="row"><label for="com_memo">메모</label></th>
        <td colspan="3">
            <textarea name="com_memo" id="mb_memo"><?=$com['com_memo']?></textarea>
        </td>
    </tr>
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