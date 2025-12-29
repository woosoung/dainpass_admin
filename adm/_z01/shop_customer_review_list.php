<?php
$sub_menu = "960400";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 20;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'review_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // 평점 필터

// ORDER BY 필드에 테이블 별칭이 없으면 추가
if ($sst && strpos($sst, '.') === false) {
    // 허용된 필드 목록
    $allowed_fields = array('review_id', 'sr_score', 'sr_created_at', 'sr_updated_at');
    if (in_array($sst, $allowed_fields)) {
        $sst = 'sr.' . $sst;
    }
}

$where_sql = " WHERE sr.shop_id = {$shop_id} AND sr.sr_deleted = 'N' ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'customer_id':
            $where_sql .= " AND c.customer_id::text LIKE '%{$stx}%' ";
            break;
        case 'user_id':
            $where_sql .= " AND c.user_id LIKE '%{$stx}%' ";
            break;
        case 'customer_name':
            $where_sql .= " AND c.name LIKE '%{$stx}%' ";
            break;
        case 'sr_content':
            $where_sql .= " AND sr.sr_content LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $where_sql .= " AND sr.sr_score = '{$sfl2}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt 
               FROM shop_review AS sr
               LEFT JOIN customers AS c ON sr.customer_id = c.customer_id
               {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT sr.*, 
                c.user_id, 
                c.name as customer_name,
                c.customer_id
         FROM shop_review AS sr
         LEFT JOIN customers AS c ON sr.customer_id = c.customer_id
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '고객리뷰관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
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
    <label for="sfl2">평점</label>
    <select name="sfl2" id="sfl2" class="frm_input">
        <option value="">전체</option>
        <option value="5"<?php echo $sfl2 == '5' ? ' selected' : '' ?>>5점</option>
        <option value="4"<?php echo $sfl2 == '4' ? ' selected' : '' ?>>4점</option>
        <option value="3"<?php echo $sfl2 == '3' ? ' selected' : '' ?>>3점</option>
        <option value="2"<?php echo $sfl2 == '2' ? ' selected' : '' ?>>2점</option>
        <option value="1"<?php echo $sfl2 == '1' ? ' selected' : '' ?>>1점</option>
    </select>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="frm_input">
        <option value="">선택</option>
        <option value="user_id"<?php echo $sfl == 'user_id' ? ' selected' : '' ?>>회원ID</option>
        <option value="customer_name"<?php echo $sfl == 'customer_name' ? ' selected' : '' ?>>회원명</option>
        <option value="customer_id"<?php echo $sfl == 'customer_id' ? ' selected' : '' ?>>고객ID</option>
        <option value="sr_content"<?php echo $sfl == 'sr_content' ? ' selected' : '' ?>>내용</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        고객이 작성한 리뷰를 관리합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id, ''); ?>
</div>

<form name="flist" id="flist" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
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
        <col style="width: 100px;">
        <col style="width: 300px;">
        <col style="width: 150px;">
        <col style="width: 150px;">
        <col style="width: 100px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('review_id', $qstr, 1); ?>리뷰ID</a></th>
        <th scope="col">고객정보</th>
        <th scope="col"><?php echo subject_sort_link('sr_score', $qstr, 1); ?>평점</a></th>
        <th scope="col">내용</th>
        <th scope="col"><?php echo subject_sort_link('sr_created_at', $qstr, 1); ?>등록일시</a></th>
        <th scope="col"><?php echo subject_sort_link('sr_updated_at', $qstr, 1); ?>수정일시</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $review_id = $row['review_id'];
            $customer_id = $row['customer_id'];
            $user_id = $row['user_id'];
            $customer_name = $row['customer_name'];
            $sr_score = $row['sr_score'];
            $sr_content = $row['sr_content'];
            $sr_created_at = $row['sr_created_at'];
            $sr_updated_at = $row['sr_updated_at'];
            
            // 평점 표시
            $score_text = '';
            $score_class = '';
            switch ($sr_score) {
                case 5:
                    $score_text = '<span style="color:green;">5점 (매우만족)</span>';
                    break;
                case 4:
                    $score_text = '<span style="color:blue;">4점 (만족)</span>';
                    break;
                case 3:
                    $score_text = '<span style="color:orange;">3점 (보통)</span>';
                    break;
                case 2:
                    $score_text = '<span style="color:#ff6b6b;">2점 (불만)</span>';
                    break;
                case 1:
                    $score_text = '<span style="color:red;">1점 (매우불만)</span>';
                    break;
                default:
                    $score_text = htmlspecialchars($sr_score) . '점';
            }
            
            $created_at_text = $sr_created_at ? date('Y-m-d H:i', strtotime($sr_created_at)) : '-';
            $updated_at_text = $sr_updated_at ? date('Y-m-d H:i', strtotime($sr_updated_at)) : '-';
            $customer_info = ($user_id ? htmlspecialchars($user_id) : '-') . '<br><small>' . ($customer_name ? htmlspecialchars($customer_name) : '-') . '</small>';
            $content_preview = $sr_content ? cut_str(strip_tags($sr_content), 50, '...') : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="hidden" name="review_id[<?php echo $i ?>]" value="<?php echo $review_id ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $review_id ?></td>
        <td class="td_left"><?php echo $customer_info ?></td>
        <td class="td_left"><?php echo $score_text ?></td>
        <td class="td_left"><?php echo htmlspecialchars($content_preview) ?></td>
        <td class="td_left"><?php echo $created_at_text ?></td>
        <td class="td_left"><?php echo $updated_at_text ?></td>
        <td class="td_mng">
            <a href="./shop_customer_review_form.php?w=u&review_id=<?php echo $review_id ?>&<?php echo $qstr ?>" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="8" class="td_empty">등록된 리뷰가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_customer_review_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<?php
include_once('./js/shop_customer_review_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
