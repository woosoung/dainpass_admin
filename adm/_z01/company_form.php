<?php
$sub_menu = "930600";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

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

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '업체 '.$html_title;
//include_once('./_top_menu_company.php');
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $com_idx = 0;
    $com['com_status'] = 'ok';
    $html_title = '추가';

}
else if ($w == 'u') {
	$com = get_table_meta('company','com_idx',$com_idx);
	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');

	$html_title = '수정';

	// 본사 com_idx_parent가 있으면 com_name_parent를 가져온다.
	$pcom = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$com['com_idx_parent']}' ");
	$com['com_name_parent'] = ($pcom['com_name']) ? $pcom['com_name'] : '';

	$com['com_name'] = get_text($com['com_name']);
	$com['com_tel'] = get_text($com['com_tel']);
	$com['com_url'] = get_text($com['com_url']);
	$com['com_addr3'] = get_text($com['com_addr3']);
	
	// 관련 파일(post_file) 추출
	// $sql = "SELECT * FROM {$g5['dain_file_table']} 
	// 		WHERE fle_db_tbl = 'company' AND fle_db_idx = '".$com['com_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	// $rs = sql_query($sql,1);

	//관련파일 추출
	$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'company' AND fle_type = 'com' AND fle_db_idx = '{$com['com_idx']}' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    //echo $rs->num_rows;echo "<br>";
    $com['com_f_arr'] = array();
    $com['com_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $com['com_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
        $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($com['com_f_arr'],array('file'=>$file_down_del));
        @array_push($com['com_fidxs'],$row2['fle_idx']);
    }
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$com[$check_array[$i]]} = ' checked';
}

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
<input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
<?=$form_input?>
<div class="local_desc01 local_desc">
    <p>업체정보를 관리해 주세요.</p>
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
			<input type="hidden" name="com_idx" value="<?=$com_idx?>">
			<input type="text" name="com_name" value="<?=$com['com_name']?>" placeholder="업체명" id="com_name" class="frm_input">
			<input type="text" name="com_branch" value="<?=$com['com_branch']?>" placeholder="지점명" id="com_branch" class="frm_input">
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
        <th scope="row">업체명(영문)</th>
		<td>
			<input type="text" name="com_name_eng" value="<?=$com['com_name_eng']?>" id="com_name_eng" class="frm_input">
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
			<input type="text" name="com_latitude" value="<?=$com['com_latitude']?>" placeholder="위도" id="com_latitude" class="frm_input">
			<input type="text" name="com_longitude" value="<?=$com['com_longitude']?>" placeholder="경도" id="com_longitude" class="frm_input">
		</td>
		<th scope="row"><label for="com_status">상태</label></th>
		<td>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="com_status" id="com_status">
				<?=$set_conf['set_com_status_option']?>
			</select>
			<script>$('select[name="com_status"]').val('<?=$com['com_status']?>');</script>
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