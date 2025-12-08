<?php
$sub_menu = "930200";
include_once('./_common.php');

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
            $g5['title'] = '서비스/메뉴 관리';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                $g5['title'] = '서비스/메뉴 관리';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    $g5['title'] = '서비스/메뉴 관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

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

$svci = (isset($svci) && is_array($svci)) ? $svci : ['svci_f_arr'=>[], 'svci_fidxs'=>[], 'svci_lst_idx'=>0, 'fle_db_idx'=>0];

// 안전한 기본값 설정 및 입력 수신
$w = isset($w) ? (string)$w : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$service_id = isset($service_id) ? (int)$service_id : (isset($_REQUEST['service_id']) ? (int)$_REQUEST['service_id'] : 0);

if ($w == '') {
    $html_title = '추가';
    $com = array();
    $com['shop_id'] = $shop_id;
    $com['service_name'] = '';
    $com['description'] = '';
    $com['price'] = 0;
    $com['status'] = 'active';
    $com['link_yn'] = 'N';
    $com['option_yn'] = 'N';
    $com['main_yn'] = 'N';
    $com['signature_yn'] = 'N';
    $com['service_time'] = 0;
} else if ($w == 'u') {
    $html_title = '수정';
    
    // 서비스 데이터 조회
    $sql = " SELECT * FROM shop_services WHERE service_id = {$service_id} LIMIT 1 ";
    $result = sql_query_pg($sql);
    $com = array();
    if ($result && is_object($result) && isset($result->result)) {
        $com = sql_fetch_array_pg($result->result);
    }
    
    if (!$com || !isset($com['service_id']) || !$com['service_id'])
        alert('존재하지 않는 서비스자료입니다.');
    
    // 가맹점측 관리자는 자신의 가맹점 서비스만 수정 가능
    if ($com['shop_id'] != $shop_id) {
        alert('접속할 수 없는 페이지 입니다.');
    }
    
    $com['service_name'] = get_text($com['service_name']);
    $com['description'] = get_text($com['description']);
    
    // 서비스관련 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop_services' AND fle_type = 'svci' AND fle_dir = 'shop/service_img' AND fle_db_idx = '{$service_id}' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query_pg($sql);
    $svci_wd = 110;
    $svci_ht = 80;
    $svci['svci_f_arr'] = array();
    $svci['svci_fidxs'] = array();
    $svci['svci_lst_idx'] = 0;
    $svci['fle_db_idx'] = $service_id;
	if ($rs && is_object($rs) && isset($rs->result)) {
		for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
			$is_s3file_yn = is_s3file($row2['fle_path']);
			$row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$svci_wd.':'.$svci_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
			$row2['thumb'] = '<span class="sp_thumb"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$svci_wd.'px;height:'.$svci_ht.'px;border:1px solid #ddd;"></span>'.PHP_EOL;
			$row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a class="a_download" href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">(<span class="sp_size">'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>)[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label class="lb_delchk" for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="svci_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
			'<br><span class="sp_sql"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
			<br><span class="sp_orig_img_url"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
			<br><span class="sp_thumb_img_url"><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
			$row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
			$svci['fle_db_idx'] = $row2['fle_db_idx'];
			@array_push($svci['svci_f_arr'], array('file'=>$row2['down_del'],'id'=>$row2['fle_idx']));
			@array_push($svci['svci_fidxs'], $row2['fle_idx']);
		}
	}
}

$g5['title'] = '서비스/메뉴 '.$html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
include_once('./js/store_service_form.js.php');
?>
<form name="form01" id="form01" action="./store_service_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
<?=$form_input??''?>
<div class="local_desc01 local_desc">
    <p>서비스/메뉴 정보를 관리해 주세요.</p>
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
		<th scope="row">서비스명<strong class="sound_only">필수</strong></th>
		<td colspan="3">
			<input type="text" name="service_name" value="<?=$com['service_name']??''?>" placeholder="서비스명" id="service_name" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">서비스 설명</th>
		<td colspan="3">
			<textarea name="description" id="description" class="w-[100%]" rows="5"><?=$com['description']??''?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row">가격</th>
		<td>
			<input type="number" name="price" value="<?=$com['price']??0?>" id="price" class="frm_input text-right w-[200px]" min="0">
			<span>원</span>
		</td>
		<th scope="row">소요시간(분)<strong class="sound_only">필수</strong></th>
		<td>
			<input type="number" name="service_time" value="<?=$com['service_time']??0?>" id="service_time" class="frm_input text-right w-[100px]" min="0" required>
			<span>분</span>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="status">상태</label></th>
		<td>
			<select name="status" id="status">
				<option value="active">정상<?=(($is_dev_manager)?'(active)':'')?></option>
				<option value="inactive">비활성<?=(($is_dev_manager)?'(inactive)':'')?></option>
			</select>
			<script>$('select[name="status"]').val('<?=$com['status']??'active'?>');</script>
		</td>
		<th scope="row">예약링크유무</th>
		<td>
			<select name="link_yn" id="link_yn">
				<option value="N">::링크없음::</option>
				<option value="Y">링크있음</option>
			</select>
			<script>$('select[name="link_yn"]').val('<?=$com['link_yn']??'N'?>');</script>
		</td>
	</tr>
	<tr>
		<th scope="row">옵션구분</th>
		<td>
			<?php echo help("예: 사이드메뉴여부"); ?>
			<select name="option_yn" id="option_yn">
				<option value="N">::옵션아님::</option>
				<option value="Y">옵션</option>
			</select>
			<script>$('select[name="option_yn"]').val('<?=$com['option_yn']??'N'?>');</script>
		</td>
		<th scope="row">대표서비스여부</th>
		<td>
			<select name="main_yn" id="main_yn">
				<option value="N">::대표아님::</option>
				<option value="Y">대표</option>
			</select>
			<script>$('select[name="main_yn"]').val('<?=$com['main_yn']??'N'?>');</script>
		</td>
	</tr>
	<tr>
		<th scope="row">시그니처서비스여부</th>
		<td colspan="3">
			<select name="signature_yn" id="signature_yn">
				<option value="N">::시그니처아님::</option>
				<option value="Y">시그니처</option>
			</select>
			<script>$('select[name="signature_yn"]').val('<?=$com['signature_yn']??'N'?>');</script>
		</td>
	</tr>
	<tr>
        <th scope="row">서비스 이미지</th>
        <td colspan="3">
            <?php echo help("서비스 관련 이미지들을 등록하고 관리해 주시면 됩니다. 여러 개 등록 가능합니다."); ?>
            <?php echo help("각 이미지 요소를 드래그&드롭으로 이미지간의 순서를 변경할 수 있습니다. 첫번째 이미지가 대표 이미지입니다."); ?>
			<input type="file" id="multi_file_svci" name="svci_datas[]" multiple class="" data-maxfile="700" data-maxsize="7000">
			<input type="hidden" name="svci_ids" value="<?=implode(',', $svci['svci_fidxs'])?>" class="border border-black w-[400px]">
            <?php
			$svci_list = (isset($svci['svci_f_arr']) && is_array($svci['svci_f_arr'])) ? $svci['svci_f_arr'] : [];
			if (!empty($svci_list)){
				echo '<ul id="service_imgs">'.PHP_EOL;
				foreach ($svci_list as $i => $item) {
					$fileHtml = is_array($item) && isset($item['file']) ? $item['file'] : '';
					echo "<li class='service_li' data-id='".$item['id']."'>[<span class='sp_sort'>".($i+1).'</span>]'.$fileHtml."</li>".PHP_EOL;
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
    <a href="./store_service_list.php<?=$list_qstr ? '?'.ltrim($list_qstr, '&') : ''?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

