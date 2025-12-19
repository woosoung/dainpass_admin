<?php
$sub_menu = "930300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

// 가맹점측 관리자 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 검색 조건
$sfl = isset($_REQUEST['sfl']) ? trim($_REQUEST['sfl']) : '';
$stx = isset($_REQUEST['stx']) ? trim($_REQUEST['stx']) : '';
$sst = isset($_REQUEST['sst']) ? trim($_REQUEST['sst']) : '';
$sod = isset($_REQUEST['sod']) ? trim($_REQUEST['sod']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

$qstr = '';
$sql_search = '';
$where = array();

// 가맹점측 관리자는 자신의 가맹점 직원만 조회 (store_id = shop_id)
$where[] = "store_id = {$shop_id}";

if ($stx) {
    switch ($sfl) {
        case 'name':
            $where[] = "name LIKE '%".addslashes($stx)."%'";
            break;
        case 'phone':
            $where[] = "phone LIKE '%".addslashes($stx)."%'";
            break;
        case 'specialty':
            $where[] = "specialty LIKE '%".addslashes($stx)."%'";
            break;
        case 'title':
            $where[] = "title LIKE '%".addslashes($stx)."%'";
            break;
    }
    $qstr .= '&sfl='.urlencode($sfl).'&stx='.urlencode($stx);
}

$sql_common = " FROM staff ";

if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "staff_id";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";

$result = sql_query_pg($sql);

$sql = " SELECT COUNT(*) AS total {$sql_common} {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 직원 이미지 썸네일 크기
$stfi_wd = 110;
$stfi_ht = 80;

$g5['title'] = '직원 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_list.js.php');
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건</span></span>
</div>

<form name="fsearch" method="get" class="local_sch01 local_sch">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="name"<?php echo get_selected($sfl, "name", true)?>>이름</option>
    <option value="phone"<?php echo get_selected($sfl, "phone")?>>전화번호</option>
    <option value="specialty"<?php echo get_selected($sfl, "specialty")?>>전문분야</option>
    <option value="title"<?php echo get_selected($sfl, "title")?>>직책</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?=$stx?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fstafflist" method="post" action="./staff_list_update.php" autocomplete="off">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="shop_id" value="<?=$shop_id?>">

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed tbl_sticky_100">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr class="success">
                <th scope="col">
                    <label for="chkall" class="sound_only">직원 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col" class="td_left">번호</th>
                <th scope="col" class="td_center" style="width:<?=$stfi_wd?>px">이미지</th>
                <th scope="col" class="td_left">이름</th>
                <th scope="col" class="td_left">전화번호</th>
                <th scope="col" class="td_left">직책</th>
                <th scope="col" class="td_left">전문분야</th>
                <th scope="col" class="td_center">슬롯당 최대고객수</th>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_count == 0) {
                echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
            } else {
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $num = $total_count - ($page - 1) * $rows - $i;
                    $s_mod = '<a href="./staff_form.php?'.$qstr.'&amp;w=u&amp;staff_id='.$row['staff_id'].'">수정</a>';

                    // 해당 직원의 이미지중에 fle_sort순으로 1개만 가져오는 쿼리
                    $fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'staff'
                                    AND fle_type = 'stfi'
                                    AND fle_dir = 'shop/staff_img'
                                    AND fle_db_idx = '{$row['staff_id']}'
                                ORDER BY fle_sort ASC, fle_reg_dt DESC LIMIT 1 ";
                    $fres = sql_fetch_pg($fsql);
                    // 이미지파일이 존재하면 썸네일 경로 생성
                    $row['thumb_tag'] = '';
                    if(!empty($fres['fle_path'])){
                        $row['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$stfi_wd.':'.$stfi_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
                        $row['thumb_tag'] = '<img src="'.$row['thumb_url'].'" alt="'.get_text($row['name']).'" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
                    }
                    else {
                        $row['thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$stfi_wd.'" class="inline-block" height="'.$stfi_ht.'" style="border:1px solid #ddd;width:'.$stfi_wd.'px;height:'.$stfi_ht.'px;">';
                    }

                    $bg = 'bg'.($i%2);
            ?>
            <tr class="<?=$bg?>">
                <td class="td_chk">
                    <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['name'])?></label>
                    <input type="checkbox" name="chk[]" value="<?=$row['staff_id']?>" id="chk_<?=$i?>">
                </td>
                <td class="td_num"><?=$num?></td>
                <td class="td_center"><?=$row['thumb_tag']?></td>
                <td class="td_left"><?=get_text($row['name'])?></td>
                <td class="td_left"><?=get_text($row['phone'] ?? '')?></td>
                <td class="td_left"><?=get_text($row['title'] ?? '')?></td>
                <td class="td_left"><?=get_text($row['specialty'] ?? '')?></td>
                <td class="td_center">
                    <input type="hidden" name="staff_id[<?=$i?>]" value="<?=$row['staff_id']?>">
                    <input type="number" name="max_customers_per_slot[<?=$i?>]" value="<?=$row['max_customers_per_slot']?>" class="text-center frm_input" style="width:70px;" min="1" max="100" required>
                </td>
                <td class="td_mng"><?=$s_mod?></td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./staff_form.php?<?=$qstr?>" class="btn btn_01">직원 추가</a>
</div>

</form>

<?php
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}".($qstr ? "?$qstr&amp;page=" : "?page="));
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

