<?php
$sub_menu = "960400";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

auth_check_menu($auth, $sub_menu, "w");

// 리뷰 수정은 개발자(mb_level 8 이상)만 가능
if (!isset($member['mb_level']) || $member['mb_level'] < 8) {
    alert("수정 권한이 없습니다.", "./shop_customer_review_list.php");
}

$review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;
$w = isset($_GET['w']) ? $_GET['w'] : '';

if ($w == 'u' && $review_id > 0) {
    // 수정 모드: 리뷰 조회
    $sql = " SELECT sr.*, 
                    c.user_id, 
                    c.name as customer_name
             FROM shop_review AS sr
             LEFT JOIN customers AS c ON sr.customer_id = c.customer_id
             WHERE sr.review_id = '{$review_id}' 
             AND sr.shop_id = {$shop_id} 
             AND sr.sr_deleted = 'N' ";
    
    $review = sql_fetch_pg($sql);
    
    if (!isset($review['review_id']) || !$review['review_id']) {
        alert("리뷰자료가 없습니다.");
    }
    
    // 리뷰 관련 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop_review' AND fle_type = 'rvwi' AND fle_dir = 'shop/review_img' AND fle_db_idx = '{$review_id}' ORDER BY fle_sort, fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    $rvwi_wd = 110;
    $rvwi_ht = 80;
    $rvwi['rvwi_f_arr'] = array();
    $rvwi['rvwi_fidxs'] = array();
    $rvwi['rvwi_lst_idx'] = 0;
    $rvwi['fle_db_idx'] = $review_id;
    if ($rs && is_object($rs) && isset($rs->result)) {
        for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
            $is_s3file_yn = is_s3file($row2['fle_path']);
            $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$rvwi_wd.':'.$rvwi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
            $row2['thumb'] = '<span class="sp_thumb"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$rvwi_wd.'px;height:'.$rvwi_ht.'px;border:1px solid #ddd;"></span>'.PHP_EOL;
            $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a class="a_download" href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">(<span class="sp_size">'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>)[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label class="lb_delchk" for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="rvwi_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
            $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
            '<br><span class="sp_sql"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
            <br><span class="sp_orig_img_url"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
            <br><span class="sp_thumb_img_url"><i class="text-blue-500 cursor-pointer copy_url fa fa-clone" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
            $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
            $rvwi['fle_db_idx'] = $row2['fle_db_idx'];
            @array_push($rvwi['rvwi_f_arr'], array('file'=>$row2['down_del'],'id'=>$row2['fle_idx']));
            @array_push($rvwi['rvwi_fidxs'], $row2['fle_idx']);
        }
    }
    
    $html_title = "고객리뷰 - 수정";
} else {
    alert("잘못된 접근입니다.");
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
include_once('./js/shop_customer_review_form.js.php');

$qstr = '';
if (isset($_GET['sst'])) $qstr .= ($qstr ? '&' : '') . 'sst='.urlencode($_GET['sst']);
if (isset($_GET['sod'])) $qstr .= ($qstr ? '&' : '') . 'sod='.urlencode($_GET['sod']);
if (isset($_GET['sfl'])) $qstr .= ($qstr ? '&' : '') . 'sfl='.urlencode($_GET['sfl']);
if (isset($_GET['stx'])) $qstr .= ($qstr ? '&' : '') . 'stx='.urlencode($_GET['stx']);
if (isset($_GET['sfl2'])) $qstr .= ($qstr ? '&' : '') . 'sfl2='.urlencode($_GET['sfl2']);
if (isset($_GET['page'])) $qstr .= ($qstr ? '&' : '') . 'page='.urlencode($_GET['page']);
?>

<?php echo get_shop_display_name($shop_info, $shop_id, ''); ?>

<form name="freviewform" id="freviewform" method="post" action="./shop_customer_review_form_update.php" enctype="multipart/form-data" onsubmit="return freviewform_submit(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="token" value="<?php echo get_token(); ?>">
<?php if ($qstr) { ?>
<input type="hidden" name="sst" value="<?php echo isset($_GET['sst']) ? htmlspecialchars($_GET['sst']) : ''; ?>">
<input type="hidden" name="sod" value="<?php echo isset($_GET['sod']) ? htmlspecialchars($_GET['sod']) : ''; ?>">
<input type="hidden" name="sfl" value="<?php echo isset($_GET['sfl']) ? htmlspecialchars($_GET['sfl']) : ''; ?>">
<input type="hidden" name="stx" value="<?php echo isset($_GET['stx']) ? htmlspecialchars($_GET['stx']) : ''; ?>">
<input type="hidden" name="sfl2" value="<?php echo isset($_GET['sfl2']) ? htmlspecialchars($_GET['sfl2']) : ''; ?>">
<input type="hidden" name="page" value="<?php echo isset($_GET['page']) ? htmlspecialchars($_GET['page']) : ''; ?>">
<?php } ?>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">리뷰ID</th>
        <td><?php echo $review['review_id']; ?></td>
    </tr>
    <tr>
        <th scope="row">고객정보</th>
        <td>
            <div class="text-sm">
                <strong><?php echo get_text($review['customer_name']); ?></strong> (<?php echo get_text($review['user_id']); ?>)
                <span class="ml-2 text-xs text-gray-500">ID: <?php echo $review['customer_id']; ?></span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="sr_score">평점 <strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="sr_score" id="sr_score" class="frm_input required" required>
                <option value="5"<?php echo ($review['sr_score'] == 5) ? ' selected' : '' ?>>5점 (매우만족)</option>
                <option value="4"<?php echo ($review['sr_score'] == 4) ? ' selected' : '' ?>>4점 (만족)</option>
                <option value="3"<?php echo ($review['sr_score'] == 3) ? ' selected' : '' ?>>3점 (보통)</option>
                <option value="2"<?php echo ($review['sr_score'] == 2) ? ' selected' : '' ?>>2점 (불만)</option>
                <option value="1"<?php echo ($review['sr_score'] == 1) ? ' selected' : '' ?>>1점 (매우불만)</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="sr_content">내용</label></th>
        <td>
            <textarea name="sr_content" id="sr_content" class="frm_input" rows="10" style="width: 100%;"><?php echo htmlspecialchars($review['sr_content']); ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">리뷰 이미지</th>
        <td>
            <?php echo help("리뷰 관련 이미지들을 등록하고 관리해 주시면 됩니다. 여러 개 등록 가능합니다."); ?>
            <?php echo help("각 이미지 요소를 드래그&드롭으로 이미지간의 순서를 변경할 수 있습니다. 첫번째 이미지가 대표 이미지입니다."); ?>
            <input type="file" id="multi_file_rvwi" name="rvwi_datas[]" multiple class="" data-maxfile="700" data-maxsize="7000">
            <input type="hidden" name="rvwi_ids" value="<?=implode(',', $rvwi['rvwi_fidxs'])?>" class="border border-black w-[400px]">
            <?php
            $rvwi_list = (isset($rvwi['rvwi_f_arr']) && is_array($rvwi['rvwi_f_arr'])) ? $rvwi['rvwi_f_arr'] : [];
            if (!empty($rvwi_list)){
                echo '<ul id="review_imgs">'.PHP_EOL;
                foreach ($rvwi_list as $i => $item) {
                    $fileHtml = is_array($item) && isset($item['file']) ? $item['file'] : '';
                    echo "<li class='review_li' data-id='".$item['id']."'>[<span class='sp_sort'>".($i+1).'</span>]'.$fileHtml."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">등록일시</th>
        <td><?php echo $review['sr_created_at'] ? date('Y-m-d H:i:s', strtotime($review['sr_created_at'])) : '-'; ?></td>
    </tr>
    <tr>
        <th scope="row">수정일시</th>
        <td><?php echo $review['sr_updated_at'] ? date('Y-m-d H:i:s', strtotime($review['sr_updated_at'])) : '-'; ?></td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./shop_customer_review_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
