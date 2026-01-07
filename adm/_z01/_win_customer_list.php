<?php
include_once('./_common.php');

$g5['title'] = '회원 선택';
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
        case 'customer_id':
            $where_sql .= " AND c.customer_id = '".addslashes($stx)."' ";
            break;
        case 'user_id':
            $where_sql .= " AND c.user_id LIKE '%".addslashes($stx)."%' ";
            break;
        case 'name':
            $where_sql .= " AND c.name LIKE '%".addslashes($stx)."%' ";
            break;
        case 'phone':
            $where_sql .= " AND c.phone LIKE '%".addslashes($stx)."%' ";
            break;
        default:
            $where_sql .= " AND c.{$sfl} LIKE '%".addslashes($stx)."%' ";
            break;
    }
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) AS cnt 
               FROM customers c
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT 
            c.customer_id,
            c.user_id,
            c.name,
            c.phone,
            c.email
         FROM customers c
         {$where_sql} 
         ORDER BY c.customer_id DESC
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sfl={$sfl}&stx=".urlencode($stx);
?>

<div id="customer_select_frm" class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fcustomer_search" id="fcustomer_search" method="get" class="local_sch01 local_sch" style="margin: 20px 0; padding: 0 20px;">
        <div class="mb-2 sch_last">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl" class="frm_input">
                <option value="">선택</option>
                <option value="customer_id"<?php echo $sfl == 'customer_id' ? ' selected' : '' ?>>고객ID</option>
                <option value="user_id"<?php echo $sfl == 'user_id' ? ' selected' : '' ?>>회원ID</option>
                <option value="name"<?php echo $sfl == 'name' ? ' selected' : '' ?>>고객명</option>
                <option value="phone"<?php echo $sfl == 'phone' ? ' selected' : '' ?>>전화번호</option>
            </select>
            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx ?? '') ?>" id="stx" class="frm_input">
            <input type="submit" class="btn_submit" value="검색">
        </div>
    </form>

    <div class="tbl_head01 tbl_wrap" style="padding: 0 20px;">
        <table class="table table-bordered table-condensed">
            <caption>회원 목록</caption>
            <thead>
                <tr>
                    <th scope="col">고객ID</th>
                    <th scope="col">회원ID</th>
                    <th scope="col">고객명</th>
                    <th scope="col">전화번호</th>
                    <th scope="col">이메일</th>
                    <th scope="col">선택</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && is_object($result) && isset($result->result)) {
                    for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
                        $customer_id = $row['customer_id'];
                        $user_id = $row['user_id'];
                        $name = $row['name'];
                        $phone = $row['phone'];
                        $email = $row['email'];
                        $bg = 'bg'.($i%2);
                ?>
                <tr class="<?=$bg?>">
                    <td class="td_left"><?=$customer_id?></td>
                    <td class="td_left"><?=htmlspecialchars($user_id ?? '')?:'-'?></td>
                    <td class="td_left"><?=htmlspecialchars($name ?? '')?:'-'?></td>
                    <td class="td_left"><?=htmlspecialchars($phone ?? '')?:'-'?></td>
                    <td class="td_left"><?=htmlspecialchars($email ?? '')?:'-'?></td>
                    <td class="td_mng">
                        <button type="button" onclick="select_customer(<?=$customer_id?>, '<?=addslashes($name ?? '')?>');" class="btn_01 btn">선택</button>
                    </td>
                </tr>
                <?php
                    }
                }
                
                if ($i == 0) {
                    echo '<tr><td colspan="6" class="td_empty">등록된 회원이 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // 페이징
    $write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './_win_customer_list.php?'.$qstr.'&page=');
    echo $write_pages;
    ?>
</div>

<script>
function select_customer(customer_id, customer_name) {
    if (opener && opener.set_selected_customer) {
        opener.set_selected_customer(customer_id, customer_name);
        window.close();
    } else {
        alert('부모 창을 찾을 수 없습니다.');
    }
}
</script>

<?php
require_once G5_PATH . '/tail.sub.php';
?>

