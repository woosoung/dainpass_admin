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

if ($sfl && $stx) {
    switch ($sfl) {
        case 'shop_id':
            $where_sql .= " AND s.shop_id = '".addslashes($stx)."' ";
            break;
        case 'name':
            $where_sql .= " AND (s.name LIKE '%".addslashes($stx)."%' OR s.shop_name LIKE '%".addslashes($stx)."%') ";
            break;
        case 'owner_name':
            $where_sql .= " AND s.owner_name LIKE '%".addslashes($stx)."%' ";
            break;
        default:
            $where_sql .= " AND s.{$sfl} LIKE '%".addslashes($stx)."%' ";
            break;
    }
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) AS cnt 
               FROM shop s
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
            s.owner_name,
            s.status
         FROM shop s
         {$where_sql} 
         ORDER BY s.shop_id DESC
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sfl={$sfl}&stx=".urlencode($stx);

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
                <option value="owner_name"<?php echo $sfl == 'owner_name' ? ' selected' : '' ?>>대표자명</option>
            </select>
            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx ?? '') ?>" id="stx" class="frm_input">
            <input type="submit" class="btn_submit" value="검색">
        </div>
    </form>

    <div class="tbl_head01 tbl_wrap" style="padding: 0 20px;">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">가맹점ID</th>
                    <th scope="col">가맹점명</th>
                    <th scope="col">대표자명</th>
                    <th scope="col">상태</th>
                    <th scope="col">선택</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if ($result && is_object($result) && isset($result->result)) {
                    for($i=0; $row = sql_fetch_array_pg($result->result); $i++) {
                        $bg = 'bg'.($i%2);
                        $shop_name_display = $row['shop_name'] ?: $row['name'];
                ?>
                <tr class="<?=$bg?>">
                    <td class="td_num"><?=$row['shop_id']?></td>
                    <td class="td_left"><?=get_text($shop_name_display)?></td>
                    <td class="td_left"><?=get_text($row['owner_name'])?></td>
                    <td class="td_status"><?=$status_arr[$row['status']] ?? $row['status']?></td>
                    <td class="td_mngsmall">
                        <button type="button" onclick="select_shop(<?=$row['shop_id']?>, '<?=addslashes($shop_name_display)?>');" class="btn_01 btn">선택</button>
                    </td>
                </tr>
                <?php
                    }
                }
                if ($i == 0) {
                    echo "<tr><td colspan=\"5\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>

<script>
function select_shop(shop_id, shop_name) {
    if (window.opener && typeof window.opener.set_selected_shop === 'function') {
        window.opener.set_selected_shop(shop_id, shop_name);
        window.close();
    } else {
        alert('부모 창을 찾을 수 없습니다.');
    }
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>

