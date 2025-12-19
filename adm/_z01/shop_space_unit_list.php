<?php
$sub_menu = "930600";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'r');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

$g5['title'] = '공간유닛 관리';

// group_id 필터
$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

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
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // unit_type 필터
$sfl3 = isset($_GET['sfl3']) ? clean_xss_tags($_GET['sfl3']) : ''; // is_active 필터

$where_sql = " WHERE u.shop_id = {$shop_id} ";

if ($group_id > 0) {
    $where_sql .= " AND u.group_id = {$group_id} ";
}

if ($sfl && $stx) {
    switch ($sfl) {
        case 'name':
            $where_sql .= " AND u.name LIKE '%{$stx}%' ";
            break;
        case 'code':
            $where_sql .= " AND u.code LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2) {
    $where_sql .= " AND u.unit_type = '{$sfl2}' ";
}

if ($sfl3 !== '') {
    $where_sql .= " AND u.is_active = '{$sfl3}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt 
               FROM {$g5['shop_space_unit_table']} u
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회 (그룹 정보 포함)
$sql = " SELECT u.*, g.name as group_name, g.group_type
         FROM {$g5['shop_space_unit_table']} u
         LEFT JOIN {$g5['shop_space_group_table']} g ON u.group_id = g.group_id
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// 공간 그룹 목록 조회 (필터용)
$group_list_sql = " SELECT group_id, name, group_type, canvas_width, canvas_height 
                    FROM {$g5['shop_space_group_table']} 
                    WHERE shop_id = {$shop_id} 
                    ORDER BY sort_order, name ";
$group_list_result = sql_query_pg($group_list_sql);
$group_list = array();
if ($group_list_result && is_object($group_list_result) && isset($group_list_result->result)) {
    while ($g_row = sql_fetch_array_pg($group_list_result->result)) {
        $group_list[] = $g_row;
    }
}

// 선택된 그룹의 캔버스 크기
$selected_canvas_width = 1000;  // 기본값
$selected_canvas_height = 800;   // 기본값
if ($group_id > 0) {
    foreach ($group_list as $g) {
        if ($g['group_id'] == $group_id) {
            $selected_canvas_width = $g['canvas_width'] ? (int)$g['canvas_width'] : 1000;
            $selected_canvas_height = $g['canvas_height'] ? (int)$g['canvas_height'] : 800;
            break;
        }
    }
}

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx);
$qstr .= "&sfl2={$sfl2}&sfl3={$sfl3}&group_id={$group_id}";

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

<div class="mb-3 local_sch01 local_sch">
    <div class="mb-2">
        <label for="group_id_filter">공간그룹</label>
        <select name="group_id" id="group_id_filter" class="frm_input">
            <option value="">전체</option>
            <?php foreach ($group_list as $g): ?>
            <option value="<?php echo $g['group_id'] ?>"<?php echo $group_id == $g['group_id'] ? ' selected' : '' ?>>
                <?php echo htmlspecialchars($g['name']) ?> (<?php echo $g['group_type'] ?>)
            </option>
            <?php endforeach; ?>
        </select>
        
        <label for="sfl2" class="ml-3">유닛타입</label>
        <select name="sfl2" id="sfl2" class="frm_input">
            <option value="">전체</option>
            <option value="ROOM"<?php echo $sfl2 == 'ROOM' ? ' selected' : '' ?>>룸</option>
            <option value="TABLE"<?php echo $sfl2 == 'TABLE' ? ' selected' : '' ?>>테이블</option>
            <option value="SEAT"<?php echo $sfl2 == 'SEAT' ? ' selected' : '' ?>>좌석</option>
            <option value="VIRTUAL"<?php echo $sfl2 == 'VIRTUAL' ? ' selected' : '' ?>>가상</option>
        </select>
        
        <label for="sfl3" class="ml-3">활성화</label>
        <select name="sfl3" id="sfl3" class="frm_input">
            <option value="">전체</option>
            <option value="t"<?php echo $sfl3 == 't' ? ' selected' : '' ?>>활성</option>
            <option value="f"<?php echo $sfl3 == 'f' ? ' selected' : '' ?>>비활성</option>
        </select>
    </div>
    
    <div>
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input">
            <option value="">선택</option>
            <option value="name"<?php echo $sfl == 'name' ? ' selected' : '' ?>>유닛명</option>
            <option value="code"<?php echo $sfl == 'code' ? ' selected' : '' ?>>유닛코드</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</div>
</form>

<div class="local_desc01 local_desc">
    <p>공간 유닛(룸/테이블/좌석/가상공간)을 관리합니다. 도면 에디터를 통해 공간의 위치를 배치할 수 있습니다.</p>
</div>

<form name="flist" id="flist" action="./shop_space_unit_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="sfl3" value="<?php echo $sfl3 ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 50px;">
        <col style="width: 80px;">
        <col style="width: 150px;">
        <col style="width: 100px;">
        <col style="width: 150px;">
        <col style="width: 100px;">
        <col style="width: 80px;">
        <col style="width: 80px;">
        <col style="width: 80px;">
        <col style="width: 100px;">
        <col>
        <col style="width: 120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">공간유닛ID</th>
        <th scope="col">공간그룹</th>
        <th scope="col">유닛타입</th>
        <th scope="col">유닛명</th>
        <th scope="col">유닛코드</th>
        <th scope="col">수용인원</th>
        <th scope="col">정렬순서</th>
        <th scope="col">좌표설정</th>
        <th scope="col">이미지</th>
        <th scope="col" class="!w-[80px]">활성화</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $unit_id = $row['unit_id'];
            
            // 이미지 조회
            $img_sql = " SELECT * FROM {$g5['dain_file_table']} 
                        WHERE fle_db_tbl = 'shop_space_unit' 
                        AND fle_db_idx = '{$unit_id}' 
                        AND fle_type = 'ssu' 
                        AND fle_dir = 'shop/shop_img' 
                        ORDER BY fle_reg_dt DESC 
                        LIMIT 1 ";
            $img_row = sql_fetch_pg($img_sql);
            
            $img_html = '-';
            if ($img_row && isset($img_row['fle_path'])) {
                $is_s3file_yn = is_s3file($img_row['fle_path']);
                if ($is_s3file_yn) {
                    $thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:60:60:1/plain/'.$set_conf['set_s3_basicurl'].'/'.$img_row['fle_path'];
                    $img_html = '<img src="'.$thumb_url.'" alt="이미지" style="width:60px;height:60px;border:1px solid #ddd;display:block;margin:0 auto;">';
                }
            }
            
            $unit_type_text = '';
            switch ($row['unit_type']) {
                case 'ROOM': $unit_type_text = '룸'; break;
                case 'TABLE': $unit_type_text = '테이블'; break;
                case 'SEAT': $unit_type_text = '좌석'; break;
                case 'VIRTUAL': $unit_type_text = '가상'; break;
            }
            
            $is_active_text = $row['is_active'] == 't' ? '활성' : '비활성';
            $has_coord = ($row['pos_x'] !== null && $row['pos_y'] !== null) ? 'O' : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $unit_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $unit_id ?></td>
        <td class="td_left"><?php echo htmlspecialchars($row['group_name']) ?></td>
        <td class="td_left"><?php echo $unit_type_text ?></td>
        <td class="td_left"><?php echo htmlspecialchars($row['name']) ?></td>
        <td class="td_left"><?php echo htmlspecialchars($row['code']) ?></td>
        <td class="td_num"><?php echo $row['capacity'] ?></td>
        <td class="td_num">
            <input type="text" name="sort_order[<?php echo $unit_id ?>]" value="<?php echo $row['sort_order'] ?>" class="frm_input text-center w-[60px]">
        </td>
        <td class="td_center"><?php echo $has_coord ?></td>
        <td class="td_center"><?php echo $img_html ?></td>
        <td class="td_center"><?php echo $is_active_text ?></td>
        <td class="td_mng">
            <a href="./shop_space_unit_form.php?w=u&unit_id=<?php echo $unit_id ?>&<?php echo $qstr ?>" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="12" class="td_empty">등록된 공간 유닛이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <a href="./shop_space_group_list.php" class="btn btn_02">그룹목록</a>
    <?php if ($group_id > 0): ?>
        <button type="button" onclick="flist_sort_submit();" class="btn btn_02">정렬순서 저장</button>
        <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
        <button type="button" onclick="open_layout_editor(<?php echo $group_id ?>, <?php echo $selected_canvas_width ?>, <?php echo $selected_canvas_height ?>, '<?php echo $qstr ?>');" class="btn btn_01">도면편집</button>
    <?php endif; ?>
    <a href="./shop_space_unit_form.php?<?php echo $qstr ?>" class="btn btn_01">유닛 등록</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_space_unit_list.php?'.$qstr.'&page=');
echo $write_pages;

include_once('./js/shop_space_unit_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

