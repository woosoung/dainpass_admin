<?php
$sub_menu = "920450";
include_once('./_common.php');

@auth_check($auth[$sub_menu],"r");

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
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):urlencode($value));
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):htmlspecialchars($value)).'" class="frm_input">'.PHP_EOL;
        }
    }
}

// 검색 조건 qstr에 추가
if (isset($_GET['sfl']) && $_GET['sfl']) $qstr .= '&sfl='.urlencode($_GET['sfl']);
if (isset($_GET['stx']) && $_GET['stx']) $qstr .= '&stx='.urlencode($_GET['stx']);

$sql_common = " FROM shop_settlement_log ssl
                LEFT JOIN shop s ON s.shop_id = ssl.shop_id
            ";

$where = array();

// 검색 조건 처리
$_GET['sfl'] = !empty($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$ser_shop_id = isset($_GET['ser_shop_id']) ? (int)$_GET['ser_shop_id'] : 0;
$ser_settlement_type = isset($_GET['ser_settlement_type']) ? trim(clean_xss_tags($_GET['ser_settlement_type'])) : '';
$fr_date = isset($_GET['fr_date']) ? clean_xss_tags($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? clean_xss_tags($_GET['to_date']) : '';

// 가맹점 검색
if ($ser_shop_id > 0) {
    $where[] = " ssl.shop_id = '{$ser_shop_id}' ";
}

// 정산 유형 검색
if (!empty($ser_settlement_type)) {
    $ser_settlement_type = addslashes($ser_settlement_type);
    $where[] = " ssl.settlement_type = '{$ser_settlement_type}' ";
}

// 기간 검색
if ($fr_date) {
    $where[] = " DATE(ssl.settlement_at) >= '{$fr_date}' ";
}
if ($to_date) {
    $where[] = " DATE(ssl.settlement_at) <= '{$to_date}' ";
}

// 일반 검색
if ($stx) {
    switch ($_GET['sfl']) {
        case 'ssl_id':
            $where[] = " ssl.ssl_id = '".addslashes($stx)."' ";
            break;
        case 'settlement_id':
            $where[] = " EXISTS (SELECT 1 FROM shop_settlements ss WHERE ss.ssl_id = ssl.ssl_id AND ss.settlement_id = '".addslashes($stx)."') ";
            break;
        default:
            if ($_GET['sfl']) {
                $where[] = " ssl.{$_GET['sfl']} LIKE '%".addslashes($stx)."%' ";
            }
            break;
    }
}

// 최종 WHERE 생성
$sql_search = '';
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "ssl.settlement_at";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

// 목록 조회 쿼리 (관련 정산 개수 포함)
$sql = " SELECT 
            ssl.ssl_id,
            ssl.shop_id,
            ssl.settlement_type,
            ssl.settlement_at,
            ssl.settlement_amount,
            ssl.status,
            ssl.settlement_start_at,
            ssl.settlement_end_at,
            s.name AS shop_name,
            s.shop_name AS shop_name_display,
            (SELECT COUNT(*)::integer 
             FROM shop_settlements ss 
             WHERE ss.ssl_id = ssl.ssl_id) AS settlement_count,
            (SELECT ss.settlement_id 
             FROM shop_settlements ss 
             WHERE ss.ssl_id = ssl.ssl_id 
             ORDER BY ss.settlement_id 
             LIMIT 1) AS first_settlement_id
            {$sql_common}
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";

$result = sql_query_pg($sql);

// 전체 개수 조회
$sql = " SELECT COUNT(*) AS total {$sql_common} {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 11;

$status_arr = array(
    'done' => '완료',
    'pending' => '대기',
    'failed' => '실패'
);

$settlement_type_arr = array(
    'AUTO' => '자동',
    'MANUAL' => '수동'
);

$g5['title'] = '정산관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<script src="<?php echo G5_Z_URL ?>/js/settlement_list.js.php"></script>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <div class="mb-2 sch_last">
        <label for="ser_shop_id" class="sound_only">가맹점</label>
        <input type="hidden" name="ser_shop_id" id="ser_shop_id" value="<?php echo $ser_shop_id ?>">
        <input type="text" name="ser_shop_name" id="ser_shop_name" value="" readonly class="frm_input" style="width: 150px;" placeholder="가맹점을 선택하세요">
        <button type="button" onclick="open_shop_popup();" class="btn_01 btn">가맹점선택</button>
        <button type="button" onclick="clear_shop_selection();" class="btn_02 btn">초기화</button>

        <label for="ser_settlement_type" class="sound_only">정산유형</label>
        <select name="ser_settlement_type" id="ser_settlement_type" class="frm_input">
            <option value="">전체</option>
            <option value="AUTO"<?php echo get_selected($ser_settlement_type, "AUTO"); ?>>자동</option>
            <option value="MANUAL"<?php echo get_selected($ser_settlement_type, "MANUAL"); ?>>수동</option>
        </select>

        <label for="fr_date" class="sound_only">시작일</label>
        <input type="text" name="fr_date" value="<?php echo htmlspecialchars($fr_date) ?>" id="fr_date" class="frm_input" size="10" placeholder="시작일">

        <label for="to_date" class="sound_only">종료일</label>
        <input type="text" name="to_date" value="<?php echo htmlspecialchars($to_date) ?>" id="to_date" class="frm_input" size="10" placeholder="종료일">

        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input">
            <option value="">선택</option>
            <option value="ssl_id"<?php echo get_selected($_GET['sfl'], "ssl_id"); ?>>정산로그ID</option>
            <option value="settlement_id"<?php echo get_selected($_GET['sfl'], "settlement_id"); ?>>정산ID</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo htmlspecialchars($stx) ?>" id="stx" class="frm_input">
        
        <input type="submit" class="btn_submit" value="검색">
    </div>
</form>

<form name="form01" id="form01" method="post" action="./settlement_list_update.php" onsubmit="return form01_submit(this);">
    <?php echo $form_input ?>
    <input type="hidden" name="w" value="">
    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">
                        <label for="chkall" class="sound_only">전체선택</label>
                        <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col">정산로그ID</th>
                    <th scope="col">가맹점명</th>
                    <th scope="col">정산유형</th>
                    <th scope="col">정산기간</th>
                    <th scope="col">정산금액</th>
                    <th scope="col">상태</th>
                    <th scope="col">관련정산개수</th>
                    <th scope="col">정산일시</th>
                    <th scope="col">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if ($result && is_object($result) && isset($result->result)) {
                    for($i=0; $row = sql_fetch_array_pg($result->result); $i++) {
                        $bg = 'bg'.($i%2);
                        $s_mod = '<a href="./settlement_form.php?w=u&ssl_id='.$row['ssl_id'].'" class="btn btn_03">수정</a>';
                        
                        $settlement_period = '';
                        if ($row['settlement_start_at'] && $row['settlement_end_at']) {
                            $settlement_period = date('Y-m-d', strtotime($row['settlement_start_at'])).' ~ '.date('Y-m-d', strtotime($row['settlement_end_at']));
                        }
                        
                        $settlement_count = isset($row['settlement_count']) ? (int)$row['settlement_count'] : 0;
                ?>
                <tr class="<?=$bg?>" tr_id="<?=$row['ssl_id']?>">
                    <td class="td_chk">
                        <input type="hidden" name="ssl_id[<?=$i?>]" value="<?=$row['ssl_id']?>" id="ssl_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=$row['ssl_id']?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
                    </td>
                    <td class="td_num"><?=$row['ssl_id']?></td>
                    <td class="td_left"><?=get_text($row['shop_name'] ?: $row['shop_name_display'] ?: '')?></td>
                    <td class="td_left"><?=$settlement_type_arr[$row['settlement_type']] ?? $row['settlement_type']?></td>
                    <td class="td_left"><?=$settlement_period?></td>
                    <td class="td_num !text-right"><?=number_format($row['settlement_amount'])?></td>
                    <td class="td_status"><?=$status_arr[$row['status']] ?? $row['status']?></td>
                    <td class="td_num"><?=$settlement_count?></td>
                    <td class="td_datetime w-[200px]"><?=$row['settlement_at'] ? date('Y-m-d H:i', strtotime($row['settlement_at'])) : ''?></td>
                    <td class="td_mngsmall"><?=$s_mod?></td>
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
    <div class="btn_fixed_top">
        <a href="./settlement_form.php" id="bo_add" class="btn_01 btn">정산추가</a>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

