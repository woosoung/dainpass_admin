<?php
include_once('./_common.php');

$g5['title'] = '가맹점 선택';
require_once G5_PATH . '/head.sub.php';

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 20;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';

$where_sql = " WHERE 1=1 ";

// status가 'closed'가 아닌 가맹점만 조회
$where_sql .= " AND (status IS NULL OR status != 'closed') ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'shop_id':
            $where_sql .= " AND s.shop_id = '".addslashes($stx)."' ";
            break;
        case 'name':
            $where_sql .= " AND (s.name LIKE '%".addslashes($stx)."%' OR s.shop_name LIKE '%".addslashes($stx)."%') ";
            break;
        case 'business_no':
            $where_sql .= " AND s.business_no LIKE '%".addslashes($stx)."%' ";
            break;
        case 'owner_name':
            $where_sql .= " AND s.owner_name LIKE '%".addslashes($stx)."%' ";
            break;
        case 'contact_phone':
            $where_sql .= " AND s.contact_phone LIKE '%".addslashes($stx)."%' ";
            break;
        default:
            $where_sql .= " AND s.{$sfl} LIKE '%".addslashes($stx)."%' ";
            break;
    }
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) AS cnt 
               FROM {$g5['shop_table']} s
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT 
            s.shop_id,
            s.name,
            s.shop_name,
            s.business_no,
            s.owner_name,
            s.contact_phone,
            s.status
         FROM {$g5['shop_table']} s
         {$where_sql} 
         ORDER BY s.shop_id DESC
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sfl={$sfl}&stx=".urlencode($stx);

// 상태 한글 변환 배열
$status_arr = array(
    'active' => '정상',
    'stopped' => '일시휴업',
    'closed' => '폐업',
    'pending' => '승인대기',
    'shutdown' => '금지'
);
?>

<div id="shop_select_frm" class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fshop_search" id="fshop_search" method="get" class="local_sch01 local_sch" style="margin: 20px 0; padding: 0 20px;">
        <div class="mb-2 sch_last">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl" class="frm_input">
                <option value="">선택</option>
                <option value="shop_id"<?php echo $sfl == 'shop_id' ? ' selected' : '' ?>>가맹점ID</option>
                <option value="name"<?php echo $sfl == 'name' ? ' selected' : '' ?>>가맹점명</option>
                <option value="business_no"<?php echo $sfl == 'business_no' ? ' selected' : '' ?>>사업자번호</option>
                <option value="owner_name"<?php echo $sfl == 'owner_name' ? ' selected' : '' ?>>대표자명</option>
                <option value="contact_phone"<?php echo $sfl == 'contact_phone' ? ' selected' : '' ?>>연락처</option>
            </select>
            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx ?? '') ?>" id="stx" class="frm_input">
            <input type="submit" class="btn_submit" value="검색">
        </div>
    </form>

    <div class="tbl_head01 tbl_wrap" style="padding: 0 20px;">
        <table class="table table-bordered table-condensed">
            <caption>가맹점 목록</caption>
            <thead>
                <tr>
                    <th scope="col">가맹점ID</th>
                    <th scope="col">가맹점명</th>
                    <th scope="col">사업자번호</th>
                    <th scope="col">대표자명</th>
                    <th scope="col">연락처</th>
                    <th scope="col">상태</th>
                    <th scope="col">선택</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if ($result && is_object($result) && isset($result->result)) {
                    for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
                        $shop_id = $row['shop_id'];
                        $shop_name = !empty($row['name']) ? $row['name'] : ($row['shop_name'] ?? '');
                        $business_no = $row['business_no'] ?? '';
                        $owner_name = $row['owner_name'] ?? '';
                        $contact_phone = $row['contact_phone'] ?? '';
                        $status = $row['status'] ?? '';
                        $status_display = isset($status_arr[$status]) ? $status_arr[$status] : ($status ?: '-');
                        $bg = 'bg'.($i%2);
                ?>
                <tr class="<?=$bg?>">
                    <td class="td_left"><?=$shop_id?></td>
                    <td class="td_left"><strong><?=htmlspecialchars($shop_name)?></strong></td>
                    <td class="td_left"><?=htmlspecialchars($business_no)?:'-'?></td>
                    <td class="td_left"><?=htmlspecialchars($owner_name)?:'-'?></td>
                    <td class="td_left"><?=htmlspecialchars($contact_phone)?:'-'?></td>
                    <td class="td_center"><?=htmlspecialchars($status_display)?></td>
                    <td class="td_mng">
                        <button type="button" onclick="select_shop(<?=$shop_id?>, '<?=addslashes($shop_name)?>');" class="btn_01 btn">선택</button>
                    </td>
                </tr>
                <?php
                    }
                }
                
                if ($i == 0) {
                    echo '<tr><td colspan="7" class="td_empty">등록된 가맹점이 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // 페이징
    $write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './_win_shop_select.php?'.$qstr.'&page=');
    echo $write_pages;
    ?>
</div>

<script>
function select_shop(shop_id, shop_name) {
    if (opener && opener.set_selected_shop) {
        opener.set_selected_shop(shop_id, shop_name);
        window.close();
    } else {
        alert('부모 창을 찾을 수 없습니다.');
    }
}
</script>

<?php
require_once G5_PATH . '/tail.sub.php';
?>

