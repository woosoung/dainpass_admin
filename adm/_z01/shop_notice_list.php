<?php
$sub_menu = "960100";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0 && $page <= 10000) ? $page : 1; // 최대 페이지 제한
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건 - 화이트리스트 방식으로 검증 강화
$allowed_sst = array('shopnotice_id', 'subject', 'status', 'create_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'subject', 'content', 'mb_id');
$allowed_sfl2 = array('', 'ok', 'pending');

$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'shopnotice_id';
$sst = in_array($sst, $allowed_sst) ? $sst : 'shopnotice_id';

$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sod = in_array($sod, $allowed_sod) ? $sod : 'desc';

$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$sfl = in_array($sfl, $allowed_sfl) ? $sfl : '';

$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$stx = substr($stx, 0, 100); // 최대 길이 제한
$stx = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $stx); // SQL 특수문자 이스케이프

$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : '';
$sfl2 = in_array($sfl2, $allowed_sfl2) ? $sfl2 : '';

$where_sql = " WHERE shop_id = " . (int)$shop_id . " ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'subject':
            // 이미 이스케이프된 $stx 사용
            $where_sql .= " AND subject ILIKE '%" . $stx . "%' ";
            break;
        case 'content':
            $where_sql .= " AND content ILIKE '%" . $stx . "%' ";
            break;
        case 'mb_id':
            $where_sql .= " AND mb_id ILIKE '%" . $stx . "%' ";
            break;
    }
}

if ($sfl2 !== '') {
    // 이미 화이트리스트로 검증된 값만 사용
    $where_sql .= " AND status = '" . $sfl2 . "' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM shop_notice {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = isset($count_row['cnt']) ? (int)$count_row['cnt'] : 0;

// 페이징 계산
$total_page = $total_count > 0 ? ceil($total_count / $rows_per_page) : 0;

// 목록 조회
$sql = " SELECT * FROM shop_notice
         {$where_sql}
         ORDER BY {$sst} {$sod}
         LIMIT " . (int)$rows_per_page . " OFFSET " . (int)$offset . " ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '공지사항';
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

<form name="fsearch" id="fsearch" method="get" onsubmit="return fsearch_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="mb-3 local_sch01 local_sch">
    <div class="mb-2">
        <label for="sfl2">상태</label>
        <select name="sfl2" id="sfl2" class="frm_input">
            <option value="">전체</option>
            <option value="ok"<?php echo $sfl2 == 'ok' ? ' selected' : '' ?>>정상</option>
            <option value="pending"<?php echo $sfl2 == 'pending' ? ' selected' : '' ?>>대기</option>
        </select>
    </div>
    
    <div>
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input">
            <option value="">선택</option>
            <option value="subject"<?php echo $sfl == 'subject' ? ' selected' : '' ?>>제목</option>
            <option value="content"<?php echo $sfl == 'content' ? ' selected' : '' ?>>내용</option>
            <option value="mb_id"<?php echo $sfl == 'mb_id' ? ' selected' : '' ?>>작성자ID</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8') ?>" id="stx" class="frm_input" maxlength="100">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 공지사항을 관리합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id, ''); ?>
</div>

<form name="flist" id="flist" action="./shop_notice_listupdate.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table style="width: 100%;">
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 3%;">
        <col style="width: 8%;">
        <col style="width: 35%;">
        <col style="width: 15%;">
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 14%;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">공지ID</th>
        <th scope="col">제목</th>
        <th scope="col">작성자ID</th>
        <th scope="col">상태</th>
        <th scope="col">작성일시</th>
        <th scope="col">정렬</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $shopnotice_id = $row['shopnotice_id'];
            $subject = $row['subject'];
            $content = $row['content'];
            $mb_id = $row['mb_id'];
            $status = $row['status'];
            $create_at = $row['create_at'];
            
            $status_text = '';
            $status_class = '';
            switch ($status) {
                case 'ok':
                    $status_text = '<span style="color:green;">정상</span>';
                    break;
                case 'pending':
                    $status_text = '<span style="color:orange;">대기</span>';
                    break;
                default:
                    $status_text = htmlspecialchars($status);
            }
            
            $create_at_text = $create_at ? date('Y-m-d H:i', strtotime($create_at)) : '-';
            $subject_display = mb_strlen($subject) > 50 ? mb_substr($subject, 0, 50) . '...' : $subject;
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $shopnotice_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $shopnotice_id ?></td>
        <td class="td_left">
            <a href="./shop_notice_form.php?w=u&shopnotice_id=<?php echo $shopnotice_id ?>&<?php echo $qstr ?>" class="td_link"><?php echo htmlspecialchars($subject_display) ?></a>
        </td>
        <td class="td_left"><?php echo htmlspecialchars($mb_id) ?></td>
        <td class="td_left"><?php echo $status_text ?></td>
        <td class="td_left"><?php echo $create_at_text ?></td>
        <td class="td_num"><?php echo $num ?></td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="7" class="td_empty">등록된 공지사항이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <a href="./shop_notice_form.php?w=<?php echo $qstr ?>" class="btn btn_01">신규등록</a>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_notice_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<?php
include_once('./js/shop_notice_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

