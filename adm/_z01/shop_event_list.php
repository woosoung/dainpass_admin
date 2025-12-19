<?php
$sub_menu = "940300";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'discount_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // is_active 필터
$sfl3 = isset($_GET['sfl3']) ? clean_xss_tags($_GET['sfl3']) : ''; // discount_scope 필터

// ORDER BY 필드에 테이블 별칭이 없으면 추가
if ($sst && strpos($sst, '.') === false) {
    // 허용된 필드 목록
    $allowed_fields = array('discount_id', 'discount_title', 'discount_scope', 'discount_type', 'start_datetime', 'end_datetime', 'is_active', 'created_at');
    if (in_array($sst, $allowed_fields)) {
        $sst = 'sd.' . $sst;
    }
}

$where_sql = " WHERE sd.shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'discount_title':
            $where_sql .= " AND sd.discount_title LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $is_active_value = ($sfl2 === 'active' || $sfl2 === '1') ? 'true' : 'false';
    $where_sql .= " AND sd.is_active = {$is_active_value} ";
}

if ($sfl3 !== '') {
    $where_sql .= " AND sd.discount_scope = '{$sfl3}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM shop_discounts AS sd {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT sd.*, 
                ss.service_name,
                s.shop_name
         FROM shop_discounts AS sd
         LEFT JOIN shop_services AS ss ON sd.service_id = ss.service_id
         LEFT JOIN shop AS s ON sd.shop_id = s.shop_id
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}&sfl3={$sfl3}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '이벤트관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <?php if ($result && is_object($result) && isset($result->result)) { ?>
    <span class="btn_ov01"><span class="ov_txt">조회 </span><span class="ov_num"> <?php echo number_format(min($total_count - $offset, $rows_per_page)) ?>건 </span></span>
    <?php } ?>
</div>

<form name="fsearch" id="fsearch" method="get" class="local_sch01 local_sch">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="mb-2 sch_last">
    <label for="sfl2">활성화여부</label>
    <select name="sfl2" id="sfl2" class="frm_input">
        <option value="">전체</option>
        <option value="active"<?php echo $sfl2 == 'active' ? ' selected' : '' ?>>활성</option>
        <option value="inactive"<?php echo $sfl2 == 'inactive' ? ' selected' : '' ?>>비활성</option>
    </select>

    <label for="sfl3">할인범위</label>
    <select name="sfl3" id="sfl3" class="frm_input">
        <option value="">전체</option>
        <option value="SHOP"<?php echo $sfl3 == 'SHOP' ? ' selected' : '' ?>>모든서비스</option>
        <option value="SERVICE"<?php echo $sfl3 == 'SERVICE' ? ' selected' : '' ?>>서비스별</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="discount_title"<?php echo $sfl == 'discount_title' ? ' selected' : '' ?>>이벤트제목</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 이벤트 할인을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" action="./shop_event_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="sfl3" value="<?php echo $sfl3 ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 50px;">
        <col style="width: 100px;">
        <col style="width: 200px;">
        <col style="width: 120px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 150px;">
        <col style="width: 150px;">
        <col style="width: 100px;">
        <col style="width: 80px;">
        <col style="width: 120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col">이벤트제목</th>
        <th scope="col">할인범위</th>
        <th scope="col">할인정보</th>
        <th scope="col">서비스</th>
        <th scope="col">시작일시</th>
        <th scope="col">종료일시</th>
        <th scope="col">상태</th>
        <th scope="col">정렬</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $discount_id = $row['discount_id'];
            $discount_title = $row['discount_title'];
            $discount_scope = $row['discount_scope'];
            $discount_type = $row['discount_type'];
            $discount_value = $row['discount_value'];
            $service_id = $row['service_id'];
            $service_name = $row['service_name'];
            $start_datetime = $row['start_datetime'];
            $end_datetime = $row['end_datetime'];
            $is_active = isset($row['is_active']) && ($row['is_active'] == 't' || $row['is_active'] === true || $row['is_active'] == '1' || $row['is_active'] === 'true');
            
            // 할인 정보 표시
            $discount_text = '';
            if ($discount_type == 'PERCENT') {
                $discount_text = $discount_value . '%';
            } else if ($discount_type == 'AMOUNT') {
                $discount_text = number_format($discount_value) . '원';
            }
            
            // 할인 범위 표시
            $scope_text = '';
            if ($discount_scope == 'SHOP') {
                $scope_text = '가맹점 전체';
            } else if ($discount_scope == 'SERVICE') {
                $scope_text = '서비스별';
            }
            
            $is_active_text = $is_active ? '<span style="color:green;">활성</span>' : '<span style="color:red;">비활성</span>';
            $start_datetime_text = $start_datetime ? date('Y-m-d H:i', strtotime($start_datetime)) : '-';
            $end_datetime_text = $end_datetime ? date('Y-m-d H:i', strtotime($end_datetime)) : '무기한';
            $service_text = $service_name ? htmlspecialchars($service_name) : ($service_id ? 'ID: ' . $service_id : '모든서비스');
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $discount_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $discount_id ?></td>
        <td class="td_left"><?php echo htmlspecialchars($discount_title) ?></td>
        <td class="td_left"><?php echo $scope_text ?></td>
        <td class="td_left"><?php echo $discount_text ?></td>
        <td class="td_left"><?php echo $service_text ?></td>
        <td class="td_left"><?php echo $start_datetime_text ?></td>
        <td class="td_left"><?php echo $end_datetime_text ?></td>
        <td class="td_left"><?php echo $is_active_text ?></td>
        <td class="td_num"><?php echo $num ?></td>
        <td class="td_mng">
            <a href="./shop_event_form.php?w=u&discount_id=<?php echo $discount_id; ?>&<?php echo $qstr; ?>" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="11" class="td_empty">등록된 이벤트가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <a href="./shop_event_form.php?<?php echo $qstr ? $qstr : ''; ?>" class="btn btn_01">신규등록</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_event_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<?php
include_once('./js/shop_event_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
