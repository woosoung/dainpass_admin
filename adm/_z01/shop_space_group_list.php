<?php
$sub_menu = "930550";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'r');

$g5['title'] = '공간그룹 관리';

// 가맹점 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (mb_level >= 6 OR (mb_level < 6 AND mb_2 = 'Y'))
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        if ($mb_1_value === '0' || $mb_1_value === '') {
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        if (!empty($mb_1_value)) {
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

if (!$has_access) {
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'sort_order';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'asc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';

$where_sql = " WHERE shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'name':
            $where_sql .= " AND name LIKE '%{$stx}%' ";
            break;
        case 'group_type':
            $where_sql .= " AND group_type = '{$stx}' ";
            break;
    }
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_space_group_table']} {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT * FROM {$g5['shop_space_group_table']} 
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form name="fsearch" id="fsearch" method="get">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="local_sch01 local_sch">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="name"<?php echo $sfl == 'name' ? ' selected' : '' ?>>그룹명</option>
        <option value="group_type"<?php echo $sfl == 'group_type' ? ' selected' : '' ?>>그룹타입</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>공간 그룹(층/홀/존)을 관리합니다. 각 그룹에 도면 이미지를 업로드하고 공간 유닛을 배치할 수 있습니다.</p>
</div>

<form name="flist" id="flist" action="./shop_space_group_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 50px;">
        <col style="width: 80px;">
        <col style="width: 100px;">
        <col style="width: 150px;">
        <col style="width: 80px;">
        <col style="width: 80px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 80px;">
        <col>
        <col style="width: 150px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">공간그룹ID</th>
        <th scope="col">그룹타입</th>
        <th scope="col">그룹명</th>
        <th scope="col">층번호</th>
        <th scope="col">정렬순서</th>
        <th scope="col">캔버스크기</th>
        <th scope="col">도면이미지</th>
        <th scope="col">활성화</th>
        <th scope="col">설명</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $group_id = $row['group_id'];
            
            // 도면 이미지 조회
            $img_sql = " SELECT * FROM {$g5['dain_file_table']} 
                        WHERE fle_db_tbl = 'shop_space_group' 
                        AND fle_db_idx = '{$group_id}' 
                        AND fle_type = 'ssg' 
                        AND fle_dir = 'shop/shop_img' 
                        ORDER BY fle_reg_dt DESC 
                        LIMIT 1 ";
            $img_row = sql_fetch_pg($img_sql);
            
            $img_html = '-';
            if ($img_row && isset($img_row['fle_path'])) {
                $is_s3file_yn = is_s3file($img_row['fle_path']);
                if ($is_s3file_yn) {
                    $thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:80:60:1/plain/'.$set_conf['set_s3_basicurl'].'/'.$img_row['fle_path'];
                    $img_html = '<img src="'.$thumb_url.'" alt="도면" style="width:80px;height:60px;border:1px solid #ddd;display:block;margin:0 auto;">';
                }
            }
            
            $group_type_text = '';
            switch ($row['group_type']) {
                case 'FLOOR': $group_type_text = '층'; break;
                case 'HALL': $group_type_text = '홀'; break;
                case 'ZONE': $group_type_text = '존'; break;
            }
            
            $is_active_text = $row['is_active'] == 't' ? '활성' : '비활성';
            $canvas_text = ($row['canvas_width'] && $row['canvas_height']) ? $row['canvas_width'].' × '.$row['canvas_height'] : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $group_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $group_id ?></td>
        <td class="td_left"><?php echo $group_type_text ?></td>
        <td class="td_left"><?php echo htmlspecialchars($row['name']) ?></td>
        <td class="td_num"><?php echo $row['level_no'] ? $row['level_no'] : '-' ?></td>
        <td class="td_num">
            <input type="text" name="sort_order[<?php echo $group_id ?>]" value="<?php echo $row['sort_order'] ?>" class="frm_input text-center w-[60px]">
        </td>
        <td class="td_num"><?php echo $canvas_text ?></td>
        <td class="td_center"><?php echo $img_html ?></td>
        <td class="td_center"><?php echo $is_active_text ?></td>
        <td class="td_left" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?php echo htmlspecialchars($row['description']) ?>
        </td>
        <td class="td_mng">
            <a href="./shop_space_unit_list.php?group_id=<?php echo $group_id ?>&<?php echo $qstr ?>" class="btn btn_03">유닛목록</a>
            <?php if ($row['canvas_width'] && $row['canvas_height']): ?>
            <br><button type="button" onclick="open_layout_editor(<?php echo $group_id ?>, <?php echo (int)$row['canvas_width'] ?>, <?php echo (int)$row['canvas_height'] ?>, '<?php echo $qstr ?>');" class="btn btn_03">도면편집</button>
            <?php endif; ?>
            <br><a href="./shop_space_group_form.php?w=u&group_id=<?php echo $group_id ?>&<?php echo $qstr ?>" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="11" class="td_empty">등록된 공간 그룹이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_sort_submit();" class="btn btn_02">정렬순서 저장</button>
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <a href="./shop_space_group_form.php?<?php echo $qstr ?>" class="btn btn_01">그룹 등록</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_space_group_list.php?'.$qstr.'&page=');
echo $write_pages;

include_once('./js/shop_space_group_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

