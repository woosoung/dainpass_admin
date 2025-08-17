<?php
$sub_menu = '920700';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$ca_id = isset($_GET['category_id']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['category_id']) : '';
$ca = array(
    'name'=>'',
    'description'=>'',
    'sort_order'=>0,
    'use_yn'=>'Y',
    'cert_use_yn'=>'N',
    'adult_use_yn'=>'N',
    'img_url'=>'',
    'img2_url'=>'',
);

$sql_common = " from {$g5['shop_categories_table']} ";


if ($w == "")
{
    if ((!$is_dev_manager && !$ca_id) || ($member['mb_level'] < 8 && !$ca_id))
        alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");

    $len = strlen($ca_id);
    if ($len == 4) //($len == 6)($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n2단계 업종까지만 가능합니다."); //alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 업종까지만 가능합니다.");

    $len2 = $len + 1;

    $sql = " SELECT MAX(SUBSTRING(category_id FROM {$len2} FOR 2)) AS max_subid
                FROM {$g5['shop_categories_table']}
            WHERE SUBSTRING(category_id FROM 1 FOR {$len}) = '{$ca_id}' ";
    $row = sql_fetch_pg($sql);

    $subid = base_convert((string)$row['max_subid'], 36, 10);
    $subid += 36;
    if ($subid >= 36 * 36)
    {
        //alert("분류를 더 이상 추가할 수 없습니다.");
        // 빈상태로
        $subid = "  ";
    }
    $subid = base_convert($subid, 10, 36);
    $subid = substr("00" . $subid, -2);
    $subid = $ca_id . $subid;

    $sublen = strlen($subid);

    if ($ca_id) // 2단계이상 분류
    {
        $sql = " SELECT * FROM {$g5['shop_categories_table']} WHERE category_id = '$ca_id' ";
        $ca = sql_fetch_pg($sql);
        $html_title = $ca['name'] . " 하위업종추가";
        $ca['name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계업종추가";
        $ca['use_yn'] = 'Y';
    }

    $cert_use_y = '';
    $cert_use_n = 'checked="checked"';
    $adult_use_y = '';
    $adult_use_n = 'checked="checked"';
}
else if ($w == "u")
{
    /*
    [fle_idx] => 11
    [fle_mb_id] => super
    [fle_db_tbl] => set
    [fle_db_idx] => afavicon
    [fle_width] => 80
    [fle_height] => 80
    [fle_desc] => 
    [fle_mime_type] => image/png
    [fle_type] => cat_off / cat_on
    [fle_dir] => admin/category
    [fle_size] => 1531
    [fle_path] => data/admin/conf/Favicondainpass_688c50b5644d1.png
    [fle_name] => Favicondainpass_688c50b5644d1.png
    [fle_name_orig] => Favicon-dainpass.png
    [fle_sort] => 0
    [fle_status] => ok
    [fle_reg_dt] => 2025-08-01 14:29:25
    [fle_update_dt] => 2025-08-01 05:29:25.514486
    */
    // 업종(분류)의 아이콘 off 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop_categories' AND fle_type = 'cat_off' AND fle_dir = 'admin/category' AND fle_db_idx = '{$ca_id}' ORDER BY fle_reg_dt DESC ";
    // echo $sql;exit;
    $rs = sql_query_pg($sql);
    $cof_wd = 80;
    $cof_ht = 80;
    $cof['cof_f_arr'] = array();
    $cof['cof_fidxs'] = array();
    $cof['cof_lst_idx'] = 0;
    $cof['fle_db_idx'] = $ca_id;
    for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$cof_wd.':'.$cof_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$cof_wd.'px;height:'.$cof_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $cof['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($cof['cof_f_arr'], array('file'=>$row2['down_del']));
        @array_push($cof['cof_fidxs'], $row2['fle_idx']);
    }

    // 업종(분류)의 아이콘 on 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'shop_categories' AND fle_type = 'cat_on' AND fle_dir = 'admin/category' AND fle_db_idx = '{$ca_id}' ORDER BY fle_reg_dt DESC ";
    // echo $sql;exit;
    $rs = sql_query_pg($sql);
    $con_wd = 80;
    $con_ht = 80;
    $con['con_f_arr'] = array();
    $con['con_fidxs'] = array();
    $con['con_lst_idx'] = 0;
    $con['fle_db_idx'] = $ca_id;
    for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$con_wd.':'.$con_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$con_wd.'px;height:'.$con_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $con['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($con['con_f_arr'], array('file'=>$row2['down_del']));
        @array_push($con['con_fidxs'], $row2['fle_idx']);
    }



    $sql = " SELECT * FROM {$g5['shop_categories_table']} WHERE category_id = '$ca_id' ";
    
    $ca = sql_fetch_pg($sql);
    if (! (isset($ca['category_id']) && $ca['category_id']))
        alert("자료가 없습니다.");

    $html_title = $ca['name'] . " 수정";
    $ca['name'] = get_text($ca['name']);

    $cert_use_y = ($ca['cert_use_yn'] == 'Y') ? 'checked="checked"' : '';
    $cert_use_n = ($ca['cert_use_yn'] != 'Y') ? 'checked="checked"' : '';
    $adult_use_y = ($ca['adult_use_yn'] == 'Y') ? 'checked="checked"' : '';
    $adult_use_n = ($ca['adult_use_yn'] != 'Y') ? 'checked="checked"' : '';
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');


$pg_anchor ='<ul class="anchor">
<li><a href="#anc_scatefrm_basic">필수입력</a></li>
<li><a href="#anc_cf_icon">아이콘이미지</a></li>';
if ($w == 'u') $pg_anchor .= '<li><a href="#frm_etc">기타설정</a></li>';
$pg_anchor .= '</ul>';

add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<form name="fcategoryform" action="./categoryformupdate.php" onsubmit="return fcategoryformcheck(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<?php if($is_dev_manager) { ?>
<div class="local_desc02 local_desc">
    <p>
        <span class="text-red-800">여기의 데이터는 dain_file 테이블관련 데이터입니다.</span>
    </p>
</div>
<?php } ?>

<section id="anc_scatefrm_basic">
    <h2 class="h2_frm">필수입력</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>업종 추가 필수입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="category_id">분류코드</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">category_id</span><?php } ?></th>
            <td>
            <?php if ($w == "") { ?>
                <?php echo help("자동으로 보여지는 업종코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n업종코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n업종코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 업종당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 업종을 사용할 수 있습니다."); ?>
                <input type="text" name="category_id" value="<?php echo $subid; ?>" id="category_id" required class="required frm_input" size="<?php echo $sublen; ?>" maxlength="<?php echo $sublen; ?>">
            <?php } else { ?>
                <input type="hidden" name="category_id" value="<?php echo $ca['category_id']; ?>">
                <span class="frm_ca_id"><?php echo $ca['category_id']; ?></span>
                <a href="./categoryform.php?category_id=<?php echo $ca_id; ?>&amp;<?php echo $qstr; ?>" class="btn_frmline">하위업종 추가</a>
            <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="name">업종명</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">name</span><?php } ?></th>
            <td><input type="text" name="name" value="<?php echo $ca['name']; ?>" id="name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="description">설명</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">description</span><?php } ?></th>
            <td><input type="text" name="description" value="<?php echo $ca['description']; ?>" id="description" class="frm_input w-[400px]"></td>
        </tr>
        <tr>
            <th scope="row"><label for="sort_order">출력순서</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">sort_order</span><?php } ?></th>
            <td>
                <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
                <input type="text" name="sort_order" value="<?php echo $ca['sort_order']; ?>" id="sort_order" class="frm_input" size="12">
            </td>
        </tr>
        <tr>
            <th scope="row">본인확인 체크<?php if($is_dev_manager) { ?><br><span class="text-red-800">cert_use_yn</span><?php } ?></th>
            <td>
                <input type="radio" name="cert_use_yn" value="Y" id="cert_use_yes" <?=$cert_use_y?>>
                <label for="cert_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="cert_use_yn" value="N" id="ca_cert_use_no" <?=$cert_use_n?>>
                <label for="ca_cert_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">성인인증 체크<?php if($is_dev_manager) { ?><br><span class="text-red-800">adult_use_yn</span><?php } ?></th>
            <td>
                <input type="radio" name="adult_use_yn" value="Y" id="adult_use_yes" <?=$adult_use_y?>>
                <label for="adult_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="adult_use_yn" value="N" id="adult_use_no" <?=$adult_use_n?>>
                <label for="adult_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="use_yn">예약가능</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">de_card_test [Y/N]</span><?php } ?></th>
            <td>
                <?php echo help("일시적으로 예약 중단하시려면 체크 해제하십시오.\n체크 해제하시면 가맹점 출력을 하지 않으며, 예약도 받지 않습니다."); ?>
                <input type="checkbox" name="use_yn" <?php echo ($ca['use_yn'] == 'Y') ? 'checked="checked"' : ""; ?> value="1" id="use_yn">
                예
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_cf_icon">
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
                <th scope="row"><label for="caticon_off">비활성화상태 아이콘</label></th>
                <td colspan="3">
                    <?php echo help("해당 업종관련 비활성화 상태 아이콘을 관리합니다. (3KB 이하의 사이즈로 업로드 해 주세요.)"); ?>
                    <div>
                        <input type="file" id="caticon_off" name="caticon_off[]" multiple class="multifile maxsize-3">
                        <?php
                        if(@count($cof['cof_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($cof['cof_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$cof['cof_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="caticon_on">활성화상태 아이콘</label></th>
                <td colspan="3">
                    <?php echo help("해당 업종관련 활성화 상태 아이콘을 관리합니다. (3KB 이하의 사이즈로 업로드 해 주세요.)"); ?>
                    <div>
                        <input type="file" id="caticon_on" name="caticon_on[]" multiple class="multifile maxsize-3">
                        <?php
                        if(@count($con['con_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($con['con_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$con['con_f_arr'][$i]['file']."</li>".PHP_EOL;
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

<?php if ($w == "u") { ?>
<section id="frm_etc">
    <h2 class="h2_frm">기타설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>업종 추가 기타설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">하위분류</th>
            <td>
                <?php echo help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>"); ?>
                <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
                <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php } ?>
<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./categorylist.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<script>
$('#caticon_off').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif|svg',
});
$('#caticon_on').MultiFile({
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


function fcategoryformcheck(f)
{
    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax.ca_id.php",
            type: "POST",
            data: {
                "category_id": f.category_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });

        if (error) {
            alert(error);
            return false;
        }
    }


    return true;
}



/*document.fcategoryform.ca_name.focus(); 포커스 해제*/
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');