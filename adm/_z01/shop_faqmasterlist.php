<?php
$sub_menu = '960200';
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// PostgreSQL FAQ 테이블 (dainpass_pg) 구조 사용
// faq_master: fm_id (PK, bigserial), shop_id (bigint), fm_subject (varchar), fm_order (int)
// faq       : fa_id (PK, bigserial), fm_id (bigint), fa_question (text), fa_answer (text), fa_order (int)

// 페이징 설정
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건 (제목 검색만 제공)
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';

$where_sql = " WHERE shop_id = {$shop_id} ";
if ($stx !== '') {
    $stx_escaped = pg_escape_string($g5['connect_pg'], $stx);
    $where_sql .= " AND fm_subject ILIKE '%{$stx_escaped}%' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) AS cnt
               FROM faq_master
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;

// 페이징 계산
$total_page = $total_count > 0 ? ceil($total_count / $rows_per_page) : 1;

// 목록 조회
$list_sql = " SELECT fm_id, shop_id, fm_subject, fm_order
              FROM faq_master
              {$where_sql}
              ORDER BY fm_order, fm_id
              LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($list_sql);

// qstr 생성
$qstr = "page={$page}&stx=".urlencode($stx);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = 'FAQ관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name']
    ? $shop_info['shop_name']
    : (isset($shop_info['name']) && $shop_info['name'] ? $shop_info['name'] : 'ID: '.$shop_id);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count); ?>건 </span></span>
    <?php if ($result && is_object($result) && isset($result->result)) { ?>
    <span class="btn_ov01"><span class="ov_txt">조회 </span><span class="ov_num"> <?php echo number_format(min($total_count - $offset, $rows_per_page)); ?>건 </span></span>
    <?php } ?>
</div>

<form name="fsearch" id="fsearch" method="get" class="mb-3 local_sch01 local_sch">
    <div>
        <label for="stx" class="sound_only">FAQ마스터 제목</label>
        <input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="stx" class="frm_input" placeholder="FAQ마스터 제목 검색">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</form>

<div class="local_desc01 local_desc">
    <p>
        해당 가맹점의 FAQ 마스터를 관리합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
    <ol class="mt-2">
        <li>FAQ 마스터는 가맹점별로 별도 관리됩니다.</li>
        <li>마스터 제목을 클릭하면 해당 마스터에 속한 FAQ 항목을 관리할 수 있습니다.</li>
    </ol>
</div>

<div class="btn_fixed_top">
    <a href="./shop_faqmasterform.php" class="btn btn_01">FAQ마스터 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <colgroup>
            <col style="width:80px;">
            <col>
            <col style="width:120px;">
            <col style="width:160px;">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">제목</th>
                <th scope="col">FAQ수</th>
                <th scope="col">순서</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && is_object($result) && isset($result->result)) {
            for ($i = 0; $row = sql_fetch_array_pg($result->result); $i++) {
                $fm_id = (int) $row['fm_id'];

                // 해당 마스터에 속한 FAQ 개수
                $fa_cnt_sql = " SELECT COUNT(*) AS cnt
                                FROM faq
                                WHERE fm_id = {$fm_id} ";
                $fa_cnt_row = sql_fetch_pg($fa_cnt_sql);
                $fa_cnt = isset($fa_cnt_row['cnt']) ? (int) $fa_cnt_row['cnt'] : 0;

                $bg = 'bg'.($i % 2);
        ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_num"><?php echo $fm_id; ?></td>
                <td class="td_left">
                    <a href="./shop_faqlist.php?fm_id=<?php echo $fm_id; ?>&amp;fm_subject=<?php echo urlencode($row['fm_subject']); ?>">
                        <?php echo get_text($row['fm_subject']); ?>
                    </a>
                </td>
                <td class="td_num"><?php echo number_format($fa_cnt); ?></td>
                <td class="td_num"><?php echo (int) $row['fm_order']; ?></td>
            </tr>
        <?php
            }
        }

        if (!isset($i) || $i == 0) {
            echo '<tr><td colspan="4" class="empty_table"><span>등록된 FAQ마스터가 없습니다.</span></td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<?php
// 페이징 (기존 관리자 페이징 함수 재사용)
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "./shop_faqmasterlist.php?stx=".urlencode($stx)."&amp;page=");

include_once(G5_ADMIN_PATH.'/admin.tail.php');
