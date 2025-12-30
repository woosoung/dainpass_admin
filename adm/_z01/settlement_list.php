<?php
$sub_menu = "920450";
include_once('./_common.php');

@auth_check($auth[$sub_menu],"r");

$form_input = '';
$qstr = ''; // 초기화
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$where = array();

$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';
$ser_shop_id = isset($_GET['ser_shop_id']) ? trim($_GET['ser_shop_id']) : '';
$ser_settlement_type = isset($_GET['ser_settlement_type']) ? trim($_GET['ser_settlement_type']) : '';
$ser_date_from = isset($_GET['ser_date_from']) ? trim($_GET['ser_date_from']) : '';
$ser_date_to = isset($_GET['ser_date_to']) ? trim($_GET['ser_date_to']) : '';
$ser_payment_id = isset($_GET['ser_payment_id']) ? trim($_GET['ser_payment_id']) : '';

// 가맹점 필터링
if (!empty($ser_shop_id)) {
    $ser_shop_id = addslashes($ser_shop_id);
    $where[] = " ssl.shop_id = '{$ser_shop_id}' ";
}

// 정산 유형 필터링
if (!empty($ser_settlement_type)) {
    $ser_settlement_type = addslashes($ser_settlement_type);
    $where[] = " ssl.settlement_type = '{$ser_settlement_type}' ";
}

// 기간 검색
if (!empty($ser_date_from)) {
    $ser_date_from = addslashes($ser_date_from);
    $where[] = " DATE(ssl.settlement_at) >= '{$ser_date_from}' ";
}
if (!empty($ser_date_to)) {
    $ser_date_to = addslashes($ser_date_to);
    $where[] = " DATE(ssl.settlement_at) <= '{$ser_date_to}' ";
}

// 결제 ID 검색 (shop_settlement_log에는 payment_id가 없으므로 제거하거나 주석 처리)
// if (!empty($ser_payment_id)) {
//     $ser_payment_id = addslashes($ser_payment_id);
//     $where[] = " ss.payment_id = '{$ser_payment_id}' ";
// }

// 일반 검색
if ($stx) {
    switch ($sfl) {
        case 'ssl_id':
            $where[] = " ssl.ssl_id = '{$stx}' ";
            break;
        case 'shop_id':
            $where[] = " ssl.shop_id = '{$stx}' ";
            break;
        default:
            $where[] = " ssl.ssl_id::text LIKE '%{$stx}%' ";
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

// shop_settlement_log를 기준으로 첫 번째 shop_settlements만 가져오기
// 서브쿼리를 사용하여 각 ssl_id의 첫 번째 settlement_id를 찾고 JOIN
$sql = " SELECT 
            ssl.ssl_id
            , ssl.shop_id
            , sh.name
            , sh.shop_name
            , ssl.settlement_at
            , ssl.settlement_amount
            , ssl.status
            , ssl.memo
            , ssl.settlement_start_at
            , ssl.settlement_end_at
            , ssl.settlement_type
            , (SELECT COUNT(settlement_id)::integer FROM shop_settlements ss WHERE ss.ssl_id = ssl.ssl_id) AS settlement_count
            FROM shop_settlement_log ssl
            LEFT JOIN shop sh ON ssl.shop_id = sh.shop_id
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";

// 디버깅용 (주석 해제하여 쿼리 확인 가능)
// echo "<pre>".htmlspecialchars($sql)."</pre>"; exit;
// echo $sql;exit;
$result = sql_query_pg($sql);

// 쿼리 오류 확인 및 처리
if (!$result || !is_object($result) || !isset($result->result) || !$result->result) {
    // 쿼리 오류 발생 시 빈 결과 객체 생성
    $result = (object)array('result' => null);
}

// 전체 개수
$sql_count = " SELECT COUNT(*) AS total
                FROM shop_settlement_log ssl
                LEFT JOIN shop sh ON ssl.shop_id = sh.shop_id
                {$sql_search} ";
$count = sql_fetch_pg($sql_count);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

// 통계 정보
$sql = " SELECT COUNT(DISTINCT ssl.ssl_id) AS cnt FROM shop_settlement_log ssl WHERE ssl.settlement_type = 'AUTO' ";
$row = sql_fetch_pg($sql);
$auto_count = $row['cnt'] ?? 0;

$sql = " SELECT COUNT(DISTINCT ssl.ssl_id) AS cnt FROM shop_settlement_log ssl WHERE ssl.settlement_type = 'MANUAL' ";
$row = sql_fetch_pg($sql);
$manual_count = $row['cnt'] ?? 0;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 10;

$settlement_type_arr = array(
    'AUTO' => '자동',
    'MANUAL' => '수동'
);

$pay_flag_arr = array(
    'GENERAL' => '일반',
    'PERSONAL' => '개인'
);

$status_arr = array(
    'done' => '완료',
    'pending' => '대기',
    'failed' => '실패'
);

// 가맹점 목록 조회 (검색용)
$shop_list = array();
$shop_sql = " SELECT shop_id, name, shop_name FROM {$g5['shop_table']} WHERE status != 'closed' ORDER BY name ";
$shop_result = sql_query_pg($shop_sql);
if ($shop_result && is_object($shop_result) && isset($shop_result->result)) {
    while ($shop_row = sql_fetch_array_pg($shop_result->result)) {
        $shop_list[] = $shop_row;
    }
}

$g5['title'] = '정산관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">자동정산</span><span class="ov_num"> <?php echo number_format($auto_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">수동정산</span><span class="ov_num"> <?php echo number_format($manual_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <?php
    // 선택된 가맹점 정보 조회
    $selected_shop_id = isset($_GET['ser_shop_id']) ? (int)$_GET['ser_shop_id'] : 0;
    $selected_shop_name = '';
    if ($selected_shop_id > 0 && !empty($shop_list)) {
        foreach ($shop_list as $shop_item) {
            if ($shop_item['shop_id'] == $selected_shop_id) {
                $selected_shop_name = !empty($shop_item['name']) ? $shop_item['name'] : $shop_item['shop_name'];
                break;
            }
        }
    }
    ?>
    <input type="hidden" name="ser_shop_id" id="ser_shop_id" value="<?=$selected_shop_id?>">
    <button type="button" onclick="open_shop_popup();" class="btn_01 btn">가맹점선택</button>
    <input type="text" name="ser_shop_name_display" id="ser_shop_name_display" value="<?=htmlspecialchars($selected_shop_name)?>" readonly class="frm_input" style="width: 200px; margin-left: 10px;" placeholder="가맹점을 선택하세요">
    <button type="button" onclick="clear_shop_selection();" class="btn_02 btn" style="margin-left: 5px;">초기화</button>

    <label for="ser_settlement_type" class="sound_only">정산유형</label>
    <select name="ser_settlement_type" id="ser_settlement_type" class="cp_field" title="정산유형선택">
        <option value="">전체</option>
        <option value="AUTO"<?php echo get_selected($ser_settlement_type, "AUTO"); ?>>자동</option>
        <option value="MANUAL"<?php echo get_selected($ser_settlement_type, "MANUAL"); ?>>수동</option>
    </select>

    <label for="ser_date_from" class="sound_only">시작일</label>
    <input type="date" name="ser_date_from" id="ser_date_from" value="<?php echo $ser_date_from ?>" class="frm_input" style="width:120px;">

    <label for="ser_date_to" class="sound_only">종료일</label>
    <input type="date" name="ser_date_to" id="ser_date_to" value="<?php echo $ser_date_to ?>" class="frm_input" style="width:120px;">

    <select name="sfl" id="sfl">
        <option value="ssl_id"<?php echo get_selected($_GET['sfl'], "ssl_id"); ?>>정산로그ID</option>
        <!-- <option value="settlement_id"<?php echo get_selected($_GET['sfl'], "settlement_id"); ?>>정산ID</option> -->
        <!-- <option value="shop_id"<?php echo get_selected($_GET['sfl'], "shop_id"); ?>>가맹점ID</option> -->
        <!-- <option value="payment_id"<?php echo get_selected($_GET['sfl'], "payment_id"); ?>>결제ID</option> -->
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
</form>

<form name="form01" id="form01" action="./settlement_list_update.php" onsubmit="return form01_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="w" value="">
    <?php echo $form_input ?>
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed tbl_sticky_100">
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr class="success">
                    <th scope="col">
                        <label for="chkall" class="sound_only">전체선택</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" class="td_left">정산로그ID</th>
                    <th scope="col" class="td_left">가맹점명</th>
                    <th scope="col" class="td_center">정산유형</th>
                    <th scope="col" class="td_right">정산금액</th>
                    <th scope="col" class="td_center">정산일시</th>
                    <th scope="col" class="td_center">정산기간</th>
                    <th scope="col" class="td_center">상태</th>
                    <th scope="col" class="td_center">관련정산개수</th>
                    <th scope="col" id="settlement_list_mng">수정</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if ($result && is_object($result) && isset($result->result) && $result->result) {
                    
                    for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_mod = '<a href="./settlement_form.php?'.$qstr.'&amp;w=u&amp;ssl_id='.$row['ssl_id'].'">수정</a>';
                    
                    $bg = 'bg'.($i%2);
                    $settlement_type_class = ($row['settlement_type'] == 'AUTO') ? 'text-blue-600' : 'text-green-600';
                    
                    // 가맹점명 표시
                    $shop_display = !empty($row['name']) ? $row['name'] : ($row['shop_name'] ?? '');
                    
                    // 정산일시 표시
                    $settlement_at_display = '';
                    if (!empty($row['settlement_at'])) {
                        $settlement_at_display = date('Y-m-d H:i', strtotime($row['settlement_at']));
                    } else {
                        $settlement_at_display = '-';
                    }
                    
                    // 정산기간 표시
                    $settlement_period = '';
                    if (!empty($row['settlement_start_at']) && !empty($row['settlement_end_at'])) {
                        $start_date = date('Y-m-d', strtotime($row['settlement_start_at']));
                        $end_date = date('Y-m-d', strtotime($row['settlement_end_at']));
                        $settlement_period = $start_date . ' ~ ' . $end_date;
                    } else if (!empty($row['settlement_start_at'])) {
                        $settlement_period = date('Y-m-d', strtotime($row['settlement_start_at'])) . ' ~';
                    } else if (!empty($row['settlement_end_at'])) {
                        $settlement_period = '~ ' . date('Y-m-d', strtotime($row['settlement_end_at']));
                    } else {
                        $settlement_period = '-';
                    }
                    
                    // 상태 표시
                    $status_display = '';
                    if (!empty($row['status'])) {
                        $status_display = isset($status_arr[$row['status']]) ? $status_arr[$row['status']] : htmlspecialchars($row['status']);
                    } else {
                        $status_display = '-';
                    }
                    
                    // 정산내역수 표시
                    // 필드명이 대소문자로 반환될 수 있으므로 확인
                    $settlement_count = 0;
                    if (isset($row['settlement_count'])) {
                        $settlement_count = (int)$row['settlement_count'];
                    } else if (isset($row['SETTLEMENT_COUNT'])) {
                        $settlement_count = (int)$row['SETTLEMENT_COUNT'];
                    }
                ?>
                <tr class="<?=$bg?>" tr_id="<?=$row['ssl_id']?>">
                    <td class="td_chk">
                        <input type="hidden" name="ssl_id[<?=$i?>]" value="<?=$row['ssl_id']?>" id="ssl_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=$row['ssl_id']?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>"<?php echo ($row['settlement_type'] == 'AUTO') ? ' disabled' : ''; ?>>
                    </td>
                    <td class="td_left font_size_8"><?=$row['ssl_id']?></td>
                    <td class="td_left">
                        <?php echo htmlspecialchars($shop_display); ?>
                    </td>
                    <td class="td_center">
                        <span class="<?=$settlement_type_class?>">
                            <?=$settlement_type_arr[$row['settlement_type']] ?? $row['settlement_type']?>
                        </span>
                    </td>
                    <td class="td_right">
                        <strong><?=number_format($row['settlement_amount'] ?? 0)?></strong>
                    </td>
                    <td class="td_center font_size_8">
                        <?=$settlement_at_display?>
                    </td>
                    <td class="td_center font_size_8">
                        <?=$settlement_period?>
                    </td>
                    <td class="td_center font_size_8">
                        <?=$status_display?>
                    </td>
                    <td class="td_center">
                        <strong><?=number_format($settlement_count)?></strong>
                    </td>
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
        <?php if(!@auth_check($auth[$sub_menu],"d",1)) { ?>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <?php } ?>
        <a href="./settlement_form.php" id="bo_add" class="btn_01 btn">정산추가</a>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once('./js/settlement_list.js.php');
include_once (G5_ADMIN_PATH.'/admin.tail.php');

