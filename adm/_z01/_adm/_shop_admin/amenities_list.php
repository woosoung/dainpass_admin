<?php
$sub_menu = '920150';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '편의시설관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";

$sfl = in_array($sfl, array('amenity_name')) ? $sfl : 'amenity_name';

if ($stx != "") {
    if ($sfl == "amenity_name") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if (isset($save_stx) && $save_stx && ($save_stx != $stx))
        $page = 1;
}

$sql_common = " FROM {$g5['amenities_table']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " SELECT COUNT(*) AS cnt " . $sql_common;

$row = sql_fetch_pg($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "amenity_id";
    $sod = "ASC";
}
$sql_order = "ORDER BY $sst $sod";

// 출력할 레코드를 얻음
$sql  = " SELECT *
             $sql_common
             $sql_order
             LIMIT $rows OFFSET $from_record ";
// if($is_ultra) echo $sql;
$result = sql_query_pg($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?=$listall?>
    <span class="btn_ov01"><span class="ov_txt">생성된 편의시설 수</span><span class="ov_num">  <?php echo number_format($total_count)?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="save_stx" value="<?=$stx?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="amenity_name"<?php echo get_selected($sfl, "amenity_name", true)?>>편의시설명</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?=$stx?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="famenitieslist" method="post" action="./amenities_list_update.php" autocomplete="off">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">

<div id="sct" class="tbl_head01 tbl_wrap tbl_sticky_100">
    <table>
    <caption><?=$g5['title']?> 목록</caption>
    <thead>
    <tr>
        <th scope="col"><?php echo subject_sort_link("amenity_id")?>편의시설 ID</a></th>
        <th scope="col" id="sct_img">활성화 아이콘</th>
        <th scope="col" id="sct_img2">비활성화 아이콘</th>
        <th scope="col" id="sct_name">편의시설명</th>
        <th scope="col" id="sct_desc">설명</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    $s_add = $s_vie = $s_upd = $s_del = '';
    $i_wd = 80;
    $i_ht = 80;
    for ($i=0; $row=sql_fetch_array_pg($result->result); $i++)
    {
        $s_upd = '<a href="./amenities_form.php?w=u&amp;amenity_id='.$row['amenity_id'].'&amp;'.$qstr.'" class="btn btn_02"><span class="sound_only">'.get_text($row['amenity_name']).' </span>수정</a> ';

        if ($is_admin == 'super' || $member['mb_level'] >= 9) {
            $s_del = '<a href="./amenities_form_update.php?w=d&amp;amenity_id='.$row['amenity_id'].'&amp;'.$qstr.'" onclick="return delete_confirm(this);" class="btn btn_02 !bg-red-600"><span class="sound_only">'.get_text($row['amenity_name']).' </span>삭제</a> ';
        }
        

        // 활성화 아이콘 이미지
        $isql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'amenities' AND fle_type = 'amnt_enabled' AND fle_dir = 'shop/amenity_img' AND fle_db_idx = '{$row['amenity_id']}' ORDER BY fle_reg_dt DESC LIMIT 1 ";
        // echo $isql."<br>";
        $rs = sql_fetch_pg($isql);
        $is_s3file_yn = (isset($rs['fle_path']) && is_s3file($rs['fle_path'])) ? 1 : 0;
        @$rs['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$i_wd.':'.$i_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$rs['fle_path'];
        $rs['thumb'] = ($is_s3file_yn) ? '<span class="inline-block bg_transparent"><img src="'.$rs['thumb_url'].'" alt="'.$rs['fle_name_orig'].'" style="width:'.$i_wd.'px;height:'.$i_ht.'px;border:1px solid #ddd;"></span>' : '<span class="inline-block bg_transparent w-['.$i_wd.'px] h-['.$i_ht.'px]" style="opacity:0.3"></span>';

        // 비활성화 아이콘 이미지
        $isql2 = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'amenities' AND fle_type = 'amnt_disabled' AND fle_dir = 'shop/amenity_img' AND fle_db_idx = '{$row['amenity_id']}' ORDER BY fle_reg_dt DESC LIMIT 1 ";
        // echo $isql2."<br>";exit;
        $rs2 = sql_fetch_pg($isql2);
        $is_s3file_yn2 = (isset($rs2['fle_path']) && is_s3file($rs2['fle_path'])) ? 1 : 0;
        @$rs2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$i_wd.':'.$i_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$rs2['fle_path'];
        $rs2['thumb'] = ($is_s3file_yn2) ? '<span class="inline-block bg_transparent"><img src="'.$rs2['thumb_url'].'" alt="'.$rs2['fle_name_orig'].'" style="width:'.$i_wd.'px;height:'.$i_ht.'px;border:1px solid #ddd;"></span>' : '<span class="inline-block bg_transparent w-['.$i_wd.'px] h-['.$i_ht.'px]" style="opacity:0.3"></span>';

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>">
        <td class="td_code">
            <input type="hidden" name="amenity_id[<?=$i?>]" value="<?=$row['amenity_id']?>">
            <a href="javascript:void(0);"><?=$row['amenity_id']?></a>
        </td>
        <td class="td_img"><?=$rs['thumb']?></td>
        <td class="td_img2"><?=$rs2['thumb']?></td>
        <td headers="sct_name" class="sct_name"><input type="text" name="amenity_name[<?=$i?>]" value="<?php echo get_text($row['amenity_name'])?>" id="amenity_name_<?=$i?>" required class="tbl_input full_input required"></td>
        <td headers="sct_desc" class="sct_desc td_desc"><input type="text" name="description[<?=$i?>]" value="<?php echo get_text($row['description'])?>" id="description_<?=$i?>" class="tbl_input"></td>
        <td class="td_mng td_mng_s">
            <?=$s_add?>
            <?=$s_vie?>
            <?=$s_upd?>
            <?=$s_del?>
        </td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan=\"6\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="일괄수정" class="btn_02 btn">

    <?php if ($is_admin == 'super' || $member['mb_level'] >= 9) {?>
    <a href="./amenities_form.php" id="amenity_add" class="btn btn_01">편의시설 추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page=")?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
