<?php
$sub_menu = "930300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 검색 조건 및 페이징 정보
$sfl = isset($_REQUEST['sfl']) ? trim($_REQUEST['sfl']) : '';
$stx = isset($_REQUEST['stx']) ? trim($_REQUEST['stx']) : '';
$sst = isset($_REQUEST['sst']) ? trim($_REQUEST['sst']) : '';
$sod = isset($_REQUEST['sod']) ? trim($_REQUEST['sod']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

$qstr = '';
$list_qstr = '';
if ($sfl) {
    $qstr .= '&sfl='.urlencode($sfl);
    $list_qstr .= '&sfl='.urlencode($sfl);
}
if ($stx) {
    $qstr .= '&stx='.urlencode($stx);
    $list_qstr .= '&stx='.urlencode($stx);
}
if ($sst) {
    $qstr .= '&sst='.urlencode($sst);
    $list_qstr .= '&sst='.urlencode($sst);
}
if ($sod) {
    $qstr .= '&sod='.urlencode($sod);
    $list_qstr .= '&sod='.urlencode($sod);
}
if ($page > 1) {
    $qstr .= '&page='.$page;
    $list_qstr .= '&page='.$page;
}

$form_input = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $list_qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $list_qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$stfi = (isset($stfi) && is_array($stfi)) ? $stfi : ['stfi_f_arr'=>[], 'stfi_fidxs'=>[], 'stfi_lst_idx'=>0, 'fle_db_idx'=>0];

// 안전한 기본값 설정 및 입력 수신
$w = isset($w) ? (string)$w : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$staff_id = isset($staff_id) ? (int)$staff_id : (isset($_REQUEST['staff_id']) ? (int)$_REQUEST['staff_id'] : 0);

if ($w == '') {
    $html_title = '추가';
    $com = array();
    $com['store_id'] = $shop_id;
    $com['name'] = '';
    $com['phone'] = '';
    $com['specialty'] = '';
    $com['max_customers_per_slot'] = 1;
    $com['title'] = '';
} else if ($w == 'u') {
    $html_title = '수정';
    
    // 직원 데이터 조회
    $sql = " SELECT * FROM staff WHERE staff_id = {$staff_id} LIMIT 1 ";
    $result = sql_query_pg($sql);
    $com = array();
    if ($result && is_object($result) && isset($result->result)) {
        $com = sql_fetch_array_pg($result->result);
    }
    
    if (!$com || !isset($com['staff_id']) || !$com['staff_id'])
        alert('존재하지 않는 직원자료입니다.');
    
    // 가맹점측 관리자는 자신의 가맹점 직원만 수정 가능
    if ($com['store_id'] != $shop_id) {
        alert('접속할 수 없는 페이지 입니다.');
    }
    
    $com['name'] = get_text($com['name']);
    $com['phone'] = get_text($com['phone']);
    $com['specialty'] = get_text($com['specialty']);
    $com['title'] = get_text($com['title']);
    
    // 직원관련 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'staff' AND fle_type = 'stfi' AND fle_dir = 'shop/staff_img' AND fle_db_idx = '{$staff_id}' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query_pg($sql);
    $stfi_wd = 110;
    $stfi_ht = 80;
    $stfi['stfi_f_arr'] = array();
    $stfi['stfi_fidxs'] = array();
    $stfi['stfi_lst_idx'] = 0;
    $stfi['fle_db_idx'] = $staff_id;
	if ($rs && is_object($rs) && isset($rs->result)) {
		for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
			$is_s3file_yn = is_s3file($row2['fle_path']);
			$row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$stfi_wd.':'.$stfi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
			$row2['thumb'] = '<span class="sp_thumb"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;border:1px solid #ddd;"></span>'.PHP_EOL;
			$row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a class="a_download" href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">(<span class="sp_size">'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>)[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label class="lb_delchk" for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="stfi_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
			'<br><span class="sp_sql"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
			<br><span class="sp_orig_img_url"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
			<br><span class="sp_thumb_img_url"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
			$stfi['fle_db_idx'] = $row2['fle_db_idx'];
			@array_push($stfi['stfi_f_arr'], array('file'=>$row2['down_del'],'id'=>$row2['fle_idx']));
			@array_push($stfi['stfi_fidxs'], $row2['fle_idx']);
		}
	}
}

$g5['title'] = '직원 '.$html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
include_once('./js/staff_form.js.php');
?>
<form name="form01" id="form01" action="./staff_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">
<?=$form_input??''?>
<div class="local_desc01 local_desc">
    <p>직원 정보를 관리해 주세요.</p>
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
		<th scope="row">이름<strong class="sound_only">필수</strong></th>
		<td colspan="3">
			<input type="text" name="name" value="<?=$com['name']??''?>" placeholder="이름" id="name" class="frm_input" maxlength="10" required>
		</td>
	</tr>
	<tr>
		<th scope="row">전화번호</th>
		<td>
			<input type="text" name="phone" value="<?=$com['phone']??''?>" placeholder="01012345678" id="phone" class="frm_input" maxlength="20" pattern="[0-9]*" inputmode="numeric">
		</td>
		<th scope="row">직책</th>
		<td>
			<input type="text" name="title" value="<?=$com['title']??''?>" placeholder="직책" id="title" class="frm_input" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">전문분야</th>
		<td>
			<input type="text" name="specialty" value="<?=$com['specialty']??''?>" placeholder="전문분야" id="specialty" class="frm_input" maxlength="100">
		</td>
		<th scope="row">슬롯당 최대고객수<strong class="sound_only">필수</strong></th>
		<td>
			<input type="number" name="max_customers_per_slot" value="<?=$com['max_customers_per_slot']??1?>" id="max_customers_per_slot" class="frm_input text-right w-[100px]" min="1" max="100" required>
			<span>명</span>
		</td>
	</tr>
	<tr>
        <th scope="row">직원 이미지</th>
        <td colspan="3">
            <?php echo help("직원 관련 이미지들을 등록하고 관리해 주시면 됩니다. 여러 개 등록 가능합니다."); ?>
            <?php echo help("각 이미지 요소를 드래그&드롭으로 이미지간의 순서를 변경할 수 있습니다. 첫번째 이미지가 대표 이미지입니다."); ?>
			<input type="file" id="multi_file_stfi" name="stfi_datas[]" multiple class="" data-maxfile="700" data-maxsize="7000">
			<input type="hidden" name="stfi_ids" value="<?=implode(',', $stfi['stfi_fidxs'])?>" class="border border-black w-[400px]">
            <?php
			$stfi_list = (isset($stfi['stfi_f_arr']) && is_array($stfi['stfi_f_arr'])) ? $stfi['stfi_f_arr'] : [];
			if (!empty($stfi_list)){
				echo '<ul id="staff_imgs">'.PHP_EOL;
				foreach ($stfi_list as $i => $item) {
					$fileHtml = is_array($item) && isset($item['file']) ? $item['file'] : '';
					echo "<li class='staff_li' data-id='".$item['id']."'>[<span class='sp_sort'>".($i+1).'</span>]'.$fileHtml."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./staff_list.php<?=$list_qstr ? '?'.ltrim($list_qstr, '&') : ''?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

