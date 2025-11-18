<?php
$sub_menu = '920150';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$amenity_id = isset($_GET['amenity_id']) ? (int)$_GET['amenity_id'] : 0;
$am = array(
    'amenity_name'=>'',
    'description'=>'',
);

$sql_common = " from {$g5['amenities_table']} ";

// 신규등록 모드일 때도 배열 초기화
$aen = array(
    'aen_f_arr' => array(),
    'aen_fidxs' => array(),
    'aen_lst_idx' => 0,
    'fle_db_idx' => 0
);
$ads = array(
    'ads_f_arr' => array(),
    'ads_fidxs' => array(),
    'ads_lst_idx' => 0,
    'fle_db_idx' => 0
);

if ($w == "")
{
    $html_title = '편의시설 추가';
}
else if ($w == "u")
{
    // 편의시설의 활성화 아이콘 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'amenities' AND fle_type = 'amnt_enabled' AND fle_dir = 'shop/amenity_img' AND fle_db_idx = '{$amenity_id}' ORDER BY fle_reg_dt DESC ";
    // echo $sql;exit;
    $rs = sql_query_pg($sql);
    $aen_wd = 80;
    $aen_ht = 80;
    $aen['aen_f_arr'] = array();
    $aen['aen_fidxs'] = array();
    $aen['aen_lst_idx'] = 0;
    $aen['fle_db_idx'] = $amenity_id;
    for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$aen_wd.':'.$aen_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$aen_wd.'px;height:'.$aen_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $aen['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($aen['aen_f_arr'], array('file'=>$row2['down_del']));
        @array_push($aen['aen_fidxs'], $row2['fle_idx']);
    }

    // 편의시설의 비활성화 아이콘 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'amenities' AND fle_type = 'amnt_disabled' AND fle_dir = 'shop/amenity_img' AND fle_db_idx = '{$amenity_id}' ORDER BY fle_reg_dt DESC ";
    // echo $sql;exit;
    $rs = sql_query_pg($sql);
    $ads_wd = 80;
    $ads_ht = 80;
    $ads['ads_f_arr'] = array();
    $ads['ads_fidxs'] = array();
    $ads['ads_lst_idx'] = 0;
    $ads['fle_db_idx'] = $amenity_id;
    for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$ads_wd.':'.$ads_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$ads_wd.'px;height:'.$ads_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $ads['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($ads['ads_f_arr'], array('file'=>$row2['down_del']));
        @array_push($ads['ads_fidxs'], $row2['fle_idx']);
    }

    $sql = " SELECT * FROM {$g5['amenities_table']} WHERE amenity_id = '$amenity_id' ";
    
    $am = sql_fetch_pg($sql);
    if (! (isset($am['amenity_id']) && $am['amenity_id']))
        alert("자료가 없습니다.");

    $html_title = $am['amenity_name'] . " 수정";
    $am['amenity_name'] = get_text($am['amenity_name']);
    $am['description'] = get_text($am['description']);
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_amenityfrm_basic">필수입력</a></li>
<li><a href="#anc_af_icon">아이콘이미지</a></li>
</ul>';

add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<form name="famenitiesform" action="./amenities_form_update.php" onsubmit="return famenitiesformcheck(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="amenity_id" value="<?php echo $amenity_id; ?>">

<?php if($is_dev_manager) { ?>
<div class="local_desc02 local_desc">
    <p>
        <span class="text-red-800">여기는 amenities 테이블관련 데이터입니다.</span>
    </p>
</div>
<?php } ?>

<section id="anc_amenityfrm_basic">
    <h2 class="h2_frm">필수입력</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>편의시설 추가 필수입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($w == "u") { ?>
        <tr>
            <th scope="row"><label for="amenity_id">편의시설 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">amenity_id</span><?php } ?></th>
            <td>
                <span class="frm_amenity_id"><?php echo $am['amenity_id']; ?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label for="amenity_name">편의시설명</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">amenity_name</span><?php } ?></th>
            <td><input type="text" name="amenity_name" value="<?php echo $am['amenity_name']; ?>" id="amenity_name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="description">설명</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">description</span><?php } ?></th>
            <td><textarea name="description" id="description" class="frm_input w-[400px]" rows="5"><?php echo $am['description']; ?></textarea></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_af_icon">
    <h2 class="h2_frm">아이콘이미지</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>아이콘이미지 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row"><label for="amenityicon_enabled">활성화상태 아이콘</label></th>
                <td colspan="3">
                    <?php echo help("해당 편의시설 관련 활성화 상태 아이콘을 관리합니다. (최소 가로/세로 32px 이상, 용량은 10KB 이하의 사이즈로 업로드 해 주세요.)"); ?>
                    <div>
                        <input type="file" id="amenityicon_enabled" name="amenityicon_enabled[]" multiple class="multifile maxsize-10">
                        <?php
                        if(isset($aen['aen_f_arr']) && is_array($aen['aen_f_arr']) && count($aen['aen_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($aen['aen_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$aen['aen_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="amenityicon_disabled">비활성화상태 아이콘</label></th>
                <td colspan="3">
                    <?php echo help("해당 편의시설 관련 비활성화 상태 아이콘을 관리합니다. (최소 가로/세로 32px 이상, 용량은 10KB 이하의 사이즈로 업로드 해 주세요.)"); ?>
                    <div>
                        <input type="file" id="amenityicon_disabled" name="amenityicon_disabled[]" multiple class="multifile maxsize-10">
                        <?php
                        if(isset($ads['ads_f_arr']) && is_array($ads['ads_f_arr']) && count($ads['ads_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($ads['ads_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$ads['ads_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./amenities_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<script>
$('#amenityicon_enabled').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif|svg',
});
$('#amenityicon_disabled').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif|svg',
});

document.addEventListener("DOMContentLoaded", function () {
  const copyIcons = document.querySelectorAll(".copy_url");

  copyIcons.forEach(icon => {
    icon.addEventListener("click", function () {
      const targetSpan = this.nextElementSibling;
      if (targetSpan && targetSpan.classList.contains("copied_url")) {
        const text = targetSpan.textContent;
        navigator.clipboard.writeText(text)
          .then(() => alert("텍스트가 복사되었습니다!"))
          .catch(err => {
            alert("복사에 실패했습니다.");
            console.error("Clipboard copy failed:", err);
          });
      }
    });
  });
});

function famenitiesformcheck(f)
{
    if (!f.amenity_name.value.trim()) {
        alert('편의시설명을 입력해 주세요.');
        f.amenity_name.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
