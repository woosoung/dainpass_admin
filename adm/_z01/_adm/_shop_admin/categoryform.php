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
    if (($is_admin != 'super' && !$ca_id) || ($member['mb_level'] < 9 && !$ca_id))
        alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");

    $len = strlen($ca_id);
    if ($len == 6) //($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n3단계 업종까지만 가능합니다."); //alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 업종까지만 가능합니다.");

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



//caicon파일 추출 ###########################################################
$sql = " SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = 'category' AND fle_db_idx = 'caicon' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$fvc['cac_f_arr'] = array();
$fvc['cac_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$fvc['cac_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($fvc['fvc_f_arr'],array('file'=>$file_down_del));
    @array_push($fvc['fvc_fidxs'],$row2['fle_idx']);
}




$pg_anchor ='<ul class="anchor">
<li><a href="#anc_scatefrm_basic">필수입력</a></li>
<li><a href="#anc_cf_icon">아이콘이미지</a></li>';
if ($w == 'u') $pg_anchor .= '<li><a href="#frm_etc">기타설정</a></li>';
$pg_anchor .= '</ul>';


?>

<form name="fcategoryform" action="./categoryformupdate.php" onsubmit="return fcategoryformcheck(this);" method="post" enctype="multipart/form-data">

<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

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
            <th scope="row"><label for="category_id">분류코드</label></th>
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
            <th scope="row"><label for="name">업종명</label></th>
            <td><input type="text" name="name" value="<?php echo $ca['name']; ?>" id="name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="sort_order">출력순서</label></th>
            <td>
                <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
                <input type="text" name="sort_order" value="<?php echo $ca['sort_order']; ?>" id="sort_order" class="frm_input" size="12">
            </td>
        </tr>
        <tr>
            <th scope="row">본인확인 체크</th>
            <td>
                <input type="radio" name="cert_use_yn" value="1" id="cert_use_yes" <?=$cert_use_y?>>
                <label for="cert_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="cert_use_yn" value="0" id="ca_cert_use_no" <?=$cert_use_n?>>
                <label for="ca_cert_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">성인인증 체크</th>
            <td>
                <input type="radio" name="adult_use_yn" value="1" id="adult_use_yes" <?=$adult_use_y?>>
                <label for="adult_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="adult_use_yn" value="0" id="adult_use_no" <?=$adult_use_n?>>
                <label for="adult_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="use_yn">판매가능</label></th>
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