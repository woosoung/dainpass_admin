<?php
$sub_menu = "920350";
include_once('./_common.php');

@auth_check($auth[$sub_menu], "r");

$form_input = '';
$qstr = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.htmlspecialchars($v2).'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$sql_common = " FROM personal_payment pp
            LEFT JOIN shop s ON pp.shop_id = s.shop_id
            ";

$where = array();

$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';
$ser_start_date = isset($_GET['ser_start_date']) ? trim($_GET['ser_start_date']) : '';
$ser_end_date = isset($_GET['ser_end_date']) ? trim($_GET['ser_end_date']) : '';
$ser_status = isset($_GET['ser_status']) ? trim($_GET['ser_status']) : '';

// 날짜 필터
if (!empty($ser_start_date)) {
    $ser_start_date = addslashes($ser_start_date);
    $where[] = " DATE(pp.created_at) >= '{$ser_start_date}' ";
    $qstr .= '&ser_start_date='.$ser_start_date;
}
if (!empty($ser_end_date)) {
    $ser_end_date = addslashes($ser_end_date);
    $where[] = " DATE(pp.created_at) <= '{$ser_end_date}' ";
    $qstr .= '&ser_end_date='.$ser_end_date;
}

// 상태 필터
if (!empty($ser_status) && $ser_status != 'all') {
    $ser_status = addslashes($ser_status);
    $where[] = " pp.status = '{$ser_status}' ";
    $qstr .= '&ser_status='.$ser_status;
}

// 기본 검색어 처리
if ($stx) {
    $stx = addslashes($stx);
    switch ($sfl) {
        case 'personal_id':
            $where[] = " pp.personal_id = '{$stx}' ";
            break;
        case 'shop_id':
            $where[] = " pp.shop_id = '{$stx}' ";
            break;
        case 'user_id':
        case 'name':
        case 'phone':
        case 'email':
        case 'reason':
        case 'order_id':
            $where[] = " pp.{$sfl} LIKE '%{$stx}%' ";
            break;
        default:
            $where[] = " (pp.name LIKE '%{$stx}%' OR pp.user_id LIKE '%{$stx}%' OR pp.order_id LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
$sql_search = '';
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "created_at";
    $sod = "DESC";
}

$sql_order = " ORDER BY pp.{$sst} {$sod}, pp.personal_id DESC ";
$rows = 20;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT 
    pp.personal_id,
    pp.order_id,
    pp.user_id,
    pp.name,
    pp.phone,
    pp.email,
    pp.reason,
    pp.amount,
    pp.status,
    pp.shop_id,
    pp.shopdetail_id,
    pp.is_settlement_target,
    pp.created_at,
    pp.updated_at,
    s.name as shop_name,
    s.shop_name as shop_display_name,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM personal_payment_cancel 
            WHERE personal_id = pp.personal_id 
            AND approval_yn = 'Y'
        ) THEN 'Y'
        ELSE 'N'
    END as cancel_yn,
    (
        SELECT SUM(cancel_amount) 
        FROM personal_payment_cancel 
        WHERE personal_id = pp.personal_id 
        AND approval_yn = 'Y'
    ) as cancel_amount
            {$sql_common}
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";

$result = sql_query_pg($sql);

// 전체 건수 조회
$sql = " SELECT COUNT(*) AS total {$sql_common} {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

// 취소 건수 조회
$cancel_where = array();
$cancel_where[] = " EXISTS (
    SELECT 1 FROM personal_payment_cancel 
    WHERE personal_id = pp.personal_id 
    AND approval_yn = 'Y'
) ";
// 기존 검색 조건을 취소 건수 조회에도 적용
if ($where) {
    foreach ($where as $w) {
        $cancel_where[] = $w;
    }
}
$cancel_sql_search = '';
if ($cancel_where) {
    $cancel_sql_search = ' WHERE ' . implode(' AND ', $cancel_where);
}
$sql = " SELECT COUNT(*) AS cnt FROM personal_payment pp {$cancel_sql_search} ";
$row = sql_fetch_pg($sql);
$cancel_count = $row['cnt'] ?? 0;

// 결과 검증
if (!$result || !is_object($result) || !isset($result->result)) {
    $result = null;
}

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 13;

$status_arr = array(
    'CHARGE' => '청구',
    'PAID' => '결제완료',
    'CANCELLED' => '취소완료',
);

$g5['title'] = '개인결제내역';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">취소</span><span class="ov_num"> <?php echo number_format($cancel_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="ser_start_date" class="sound_only">시작일</label>
    <input type="date" name="ser_start_date" id="ser_start_date" value="<?php echo htmlspecialchars($ser_start_date) ?>" class="frm_input" style="width:150px;">
    <label for="ser_end_date" class="sound_only">종료일</label>
    <input type="date" name="ser_end_date" id="ser_end_date" value="<?php echo htmlspecialchars($ser_end_date) ?>" class="frm_input" style="width:150px;">

    <select name="ser_status" id="ser_status" class="cp_field" title="상태선택">
        <option value="all"<?php echo get_selected($ser_status, "all"); ?>>전체상태</option>
        <option value="CHARGE"<?php echo get_selected($ser_status, "CHARGE"); ?>>청구</option>
        <option value="PAID"<?php echo get_selected($ser_status, "PAID"); ?>>결제완료</option>
        <option value="CANCELLED"<?php echo get_selected($ser_status, "CANCELLED"); ?>>취소완료</option>
    </select>

    <select name="sfl" id="sfl">
        <option value="name"<?php echo get_selected($_GET['sfl'], "name"); ?>>결제자명</option>
        <option value="personal_id"<?php echo get_selected($_GET['sfl'], "personal_id"); ?>>결제번호</option>
        <option value="order_id"<?php echo get_selected($_GET['sfl'], "order_id"); ?>>주문번호</option>
        <option value="user_id"<?php echo get_selected($_GET['sfl'], "user_id"); ?>>고객ID</option>
        <option value="phone"<?php echo get_selected($_GET['sfl'], "phone"); ?>>연락처</option>
        <option value="email"<?php echo get_selected($_GET['sfl'], "email"); ?>>이메일</option>
        <option value="shop_id"<?php echo get_selected($_GET['sfl'], "shop_id"); ?>>가맹점고유번호</option>
        <option value="reason"<?php echo get_selected($_GET['sfl'], "reason"); ?>>결제사유</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed tbl_sticky_100">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr class="success">
                <th scope="col" class="td_left">결제번호</th>
                <th scope="col" class="td_center">결제일시</th>
                <th scope="col" class="td_left">주문번호</th>
                <th scope="col" class="td_left">결제자명</th>
                <th scope="col" class="td_left">고객ID</th>
                <th scope="col" class="td_left">연락처</th>
                <th scope="col" class="td_left">이메일</th>
                <th scope="col" class="td_left">가맹점명</th>
                <th scope="col" class="td_left">결제사유</th>
                <th scope="col" class="td_right">결제금액</th>
                <th scope="col" class="td_center">상태</th>
                <th scope="col" class="td_center">취소여부</th>
                <th scope="col" class="td_center">상세</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            if ($result && is_object($result) && isset($result->result)) {
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_detail = '<a href="./personalpayments_detail.php?'.$qstr.'&amp;personal_id='.$row['personal_id'].'" class="btn btn_01">상세</a>';

                    // 취소 여부에 따른 스타일
                    $cancel_class = ($row['cancel_yn'] == 'Y') ? " !bg-gray-100 text-gray-600" : "";
                    $bg = 'bg'.($i%2);

                    // 취소 금액 계산
                    $cancel_amount = $row['cancel_amount'] ?? 0;
                    $cancel_display = '';
                    if ($row['cancel_yn'] == 'Y') {
                        if ($cancel_amount >= $row['amount']) {
                            $cancel_display = '<span style="color: #d9534f; font-weight: bold;">전체취소</span>';
                        } else {
                            $cancel_display = '<span style="color: #f0ad4e;">부분취소 ('.number_format($cancel_amount).'원)</span>';
                        }
                    } else {
                        $cancel_display = '-';
                    }
            ?>
            <tr class="<?=$bg?><?=$cancel_class?>" tr_id="<?=$row['personal_id']?>">
                <td class="td_left font_size_8"><?=$row['personal_id']?></td>
                <td class="td_center font_size_8"><?=date('Y-m-d H:i:s', strtotime($row['created_at']))?></td>
                <td class="td_left font_size_8"><?=cut_str($row['order_id'], 20, '...')?></td>
                <td class="td_left"><?=get_text($row['name'])?></td>
                <td class="td_left font_size_8"><?=get_text($row['user_id'] ?: '-')?></td>
                <td class="td_left font_size_8"><?=formatPhoneNumber($row['phone'] ?? '')?></td>
                <td class="td_left font_size_8"><?=cut_str($row['email'] ?? '', 30, '...')?></td>
                <td class="td_left">
                    <?php if (!empty($row['shop_display_name'])) { ?>
                        <?=get_text($row['shop_display_name'])?>
                    <?php } elseif (!empty($row['shop_name'])) { ?>
                        <?=get_text($row['shop_name'])?>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td class="td_left font_size_8"><?=cut_str(get_text($row['reason']), 30, '...')?></td>
                <td class="td_right">
                    <strong><?=number_format($row['amount'])?>원</strong>
                </td>
                <td class="td_center"><?=$status_arr[$row['status']] ?? $row['status']?></td>
                <td class="td_center font_size_8"><?=$cancel_display?></td>
                <td class="td_center"><?=$s_detail?></td>
            </tr>
            <?php
                }
            }
            if ($i == 0)
                echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
        </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

