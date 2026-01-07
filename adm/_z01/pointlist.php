<?php
$sub_menu = "920400";
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
if (isset($_GET['ser_type']) && $_GET['ser_type']) $qstr .= '&ser_type='.urlencode($_GET['ser_type']);
if (isset($_GET['ser_date_field']) && $_GET['ser_date_field']) $qstr .= '&ser_date_field='.urlencode($_GET['ser_date_field']);
if (isset($_GET['fr_date']) && $_GET['fr_date']) $qstr .= '&fr_date='.urlencode($_GET['fr_date']);
if (isset($_GET['to_date']) && $_GET['to_date']) $qstr .= '&to_date='.urlencode($_GET['to_date']);

$sql_common = " FROM point_transactions pt
                LEFT JOIN customers c ON pt.customer_id = c.customer_id
                LEFT JOIN payments p ON pt.payment_id = p.payment_id
            ";

$where = array();

// 검색 조건 처리
$_GET['sfl'] = !empty($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$ser_type = isset($_GET['ser_type']) ? trim(clean_xss_tags($_GET['ser_type'])) : '';
$ser_date_field = isset($_GET['ser_date_field']) ? clean_xss_tags($_GET['ser_date_field']) : 'created_at';
$fr_date = isset($_GET['fr_date']) ? clean_xss_tags($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? clean_xss_tags($_GET['to_date']) : '';

// 포인트 유형 필터
// ser_type 원본 값 저장 (비교용)
$ser_type_original = $ser_type;

// ser_type이 선택되면 해당 타입만 조회, 없으면 적립만 조회 (그룹화 구조 유지)
if (!empty($ser_type) && in_array($ser_type, array('적립', '사용', '사용취소', '적립취소'))) {
    $ser_type_escaped = addslashes($ser_type);
    $where[] = " pt.type = '{$ser_type_escaped}' ";
} else {
    // 기본값: 적립 포인트만 조회 (그룹화 구조 유지)
    $where[] = " pt.type = '적립' ";
    $ser_type_original = ''; // 기본값으로 설정
}

// 통계용 WHERE 조건 (타입 필터 제외)
$where_stats = array();

// 검색어 처리
if ($stx) {
    switch ($_GET['sfl']) {
        case 'point_id':
            $where[] = " pt.point_id = '".addslashes($stx)."' ";
            $where_stats[] = " pt.point_id = '".addslashes($stx)."' ";
            break;
        case 'customer_id':
            $where[] = " pt.customer_id = '".addslashes($stx)."' ";
            $where_stats[] = " pt.customer_id = '".addslashes($stx)."' ";
            break;
        case 'customer_name':
            $where[] = " c.name LIKE '%".addslashes($stx)."%' ";
            $where_stats[] = " c.name LIKE '%".addslashes($stx)."%' ";
            break;
        case 'user_id':
            $where[] = " c.user_id LIKE '%".addslashes($stx)."%' ";
            $where_stats[] = " c.user_id LIKE '%".addslashes($stx)."%' ";
            break;
        case 'type':
            $where[] = " pt.type = '".addslashes($stx)."' ";
            $where_stats[] = " pt.type = '".addslashes($stx)."' ";
            break;
        case 'payment_id':
            $where[] = " pt.payment_id = '".addslashes($stx)."' ";
            $where_stats[] = " pt.payment_id = '".addslashes($stx)."' ";
            break;
        default:
            if ($_GET['sfl']) {
                $where[] = " pt.{$_GET['sfl']} LIKE '%".addslashes($stx)."%' ";
                $where_stats[] = " pt.{$_GET['sfl']} LIKE '%".addslashes($stx)."%' ";
            }
            break;
    }
}

// 날짜 범위 검색
if ($fr_date && $to_date) {
    $where[] = " pt.{$ser_date_field} >= '{$fr_date} 00:00:00' AND pt.{$ser_date_field} <= '{$to_date} 23:59:59' ";
    $where_stats[] = " pt.{$ser_date_field} >= '{$fr_date} 00:00:00' AND pt.{$ser_date_field} <= '{$to_date} 23:59:59' ";
} else if ($fr_date) {
    $where[] = " pt.{$ser_date_field} >= '{$fr_date} 00:00:00' ";
    $where_stats[] = " pt.{$ser_date_field} >= '{$fr_date} 00:00:00' ";
} else if ($to_date) {
    $where[] = " pt.{$ser_date_field} <= '{$to_date} 23:59:59' ";
    $where_stats[] = " pt.{$ser_date_field} <= '{$to_date} 23:59:59' ";
}

// 최종 WHERE 생성
$sql_search = '';
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// 통계용 WHERE 생성 (타입 필터 제외)
$sql_search_stats = '';
if ($where_stats)
    $sql_search_stats = ' WHERE '.implode(' AND ', $where_stats);

// 정렬 - 적립 포인트의 created_at 기준으로 변경
if (!$sst) {
    $sst = "pt.created_at";
    $sod = "DESC";
}

$rows = 30;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

// 적립 포인트만 먼저 조회 (created_at 최신순)
$sql_earn = " SELECT 
                pt.point_id,
                pt.customer_id,
                pt.type,
                pt.amount,
                pt.earned_at,
                pt.expired_at,
                pt.used_at,
                pt.related_id,
                pt.payment_id,
                pt.memo,
                pt.created_at,
                pt.updated_at,
                c.name AS customer_name,
                c.user_id,
                p.payment_id AS payment_info
             FROM point_transactions pt
             LEFT JOIN customers c ON pt.customer_id = c.customer_id
             LEFT JOIN payments p ON pt.payment_id = p.payment_id
             {$sql_search}
             ORDER BY pt.created_at DESC
             LIMIT {$rows} OFFSET {$from_record} ";

$result_earn = sql_query_pg($sql_earn);

// 포인트별로 관련 거래 조회
$earn_points = array();
if ($result_earn && is_object($result_earn) && isset($result_earn->result)) {
    while ($earn_row = sql_fetch_array_pg($result_earn->result)) {
        $earn_id = $earn_row['point_id'];
        $earn_points[$earn_id] = $earn_row;
        
        // ser_type이 '적립'이거나 선택되지 않은 경우에만 관련 거래 조회 (그룹화 구조)
        // 다른 타입('사용', '사용취소')을 선택한 경우에는 관련 거래 조회 안 함
        if (empty($ser_type_original) || $ser_type_original == '적립') {
            // 해당 적립 포인트에 연결된 사용/취소 내역 조회
            $related_sql = " SELECT 
                                pt.point_id,
                                pt.customer_id,
                                pt.type,
                                pt.amount,
                                pt.earned_at,
                                pt.expired_at,
                                pt.used_at,
                                pt.related_id,
                                pt.payment_id,
                                pt.memo,
                                pt.created_at,
                                pt.updated_at,
                                c.name AS customer_name,
                                c.user_id,
                                p.payment_id AS payment_info
                             FROM point_transactions pt
                             LEFT JOIN customers c ON pt.customer_id = c.customer_id
                             LEFT JOIN payments p ON pt.payment_id = p.payment_id
                             WHERE pt.related_id = {$earn_id}
                             ORDER BY pt.created_at ASC ";
            
            $related_result = sql_query_pg($related_sql);
            $earn_points[$earn_id]['related_transactions'] = array();
            if ($related_result && is_object($related_result) && isset($related_result->result)) {
                while ($related_row = sql_fetch_array_pg($related_result->result)) {
                    $earn_points[$earn_id]['related_transactions'][] = $related_row;
                }
            }
        } else {
            // 다른 타입 선택 시 관련 거래 없음
            $earn_points[$earn_id]['related_transactions'] = array();
        }
    }
}

// 전체 개수 계산 (ser_type이 선택되면 해당 타입 기준, 없으면 적립 기준)
$sql = " SELECT COUNT(*) AS total 
         FROM point_transactions pt
         LEFT JOIN customers c ON pt.customer_id = c.customer_id
         LEFT JOIN payments p ON pt.payment_id = p.payment_id
         {$sql_search} ";
$count = sql_fetch_pg($sql);
$total_count = isset($count['total']) ? $count['total'] : 0;
$total_page = ceil($total_count / $rows);

// 통계 정보 (모든 타입 대상)
$sql = " SELECT 
            COUNT(*) FILTER (WHERE pt.type = '적립') AS earn_count,
            COUNT(*) FILTER (WHERE pt.type = '사용') AS use_count,
            COUNT(*) FILTER (WHERE pt.type = '사용취소') AS cancel_count,
            COUNT(*) FILTER (WHERE pt.type = '적립취소') AS earn_cancel_count,
            COALESCE(SUM(pt.amount) FILTER (WHERE pt.type = '적립'), 0) AS total_earn,
            COALESCE(SUM(pt.amount) FILTER (WHERE pt.type = '사용'), 0) AS total_use,
            COALESCE(SUM(pt.amount) FILTER (WHERE pt.type = '사용취소'), 0) AS total_cancel,
            COALESCE(SUM(pt.amount) FILTER (WHERE pt.type = '적립취소'), 0) AS total_earn_cancel
         FROM point_transactions pt
         LEFT JOIN customers c ON pt.customer_id = c.customer_id
         LEFT JOIN payments p ON pt.payment_id = p.payment_id
         {$sql_search_stats} ";
$stats = sql_fetch_pg($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 14;

$type_arr = array(
    '적립' => '<span style="color:blue;">적립</span>',
    '사용' => '<span style="color:red;">사용</span>',
    '사용취소' => '<span style="color:green;">사용취소</span>',
    '적립취소' => '<span style="color:orange;">적립취소</span>'
);

$g5['title'] = '포인트관리';
// exit;
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <?php if ($stats) { ?>
    <span class="btn_ov01"><span class="ov_txt">적립</span><span class="ov_num"> <?php echo number_format($stats['earn_count']) ?>건</span></span>
    <span class="btn_ov01"><span class="ov_txt">사용</span><span class="ov_num"> <?php echo number_format($stats['use_count']) ?>건</span></span>
    <span class="btn_ov01"><span class="ov_txt">사용취소</span><span class="ov_num"> <?php echo number_format($stats['cancel_count']) ?>건</span></span>
    <span class="btn_ov01"><span class="ov_txt">적립취소</span><span class="ov_num"> <?php echo number_format($stats['earn_cancel_count']) ?>건</span></span>
    <?php } ?>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <div class="mb-2 sch_last">
        <label for="ser_type" class="sound_only">포인트유형</label>
        <select name="ser_type" id="ser_type" class="frm_input">
            <option value="">전체</option>
            <option value="적립"<?php echo $ser_type == '적립' ? ' selected' : '' ?>>적립</option>
            <option value="사용"<?php echo $ser_type == '사용' ? ' selected' : '' ?>>사용</option>
            <option value="사용취소"<?php echo $ser_type == '사용취소' ? ' selected' : '' ?>>사용취소</option>
            <option value="적립취소"<?php echo $ser_type == '적립취소' ? ' selected' : '' ?>>적립취소</option>
        </select>

        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input">
            <option value="">선택</option>
            <option value="point_id"<?php echo $_GET['sfl'] == 'point_id' ? ' selected' : '' ?>>포인트ID</option>
            <option value="customer_id"<?php echo $_GET['sfl'] == 'customer_id' ? ' selected' : '' ?>>고객ID</option>
            <option value="customer_name"<?php echo $_GET['sfl'] == 'customer_name' ? ' selected' : '' ?>>고객명</option>
            <option value="user_id"<?php echo $_GET['sfl'] == 'user_id' ? ' selected' : '' ?>>회원ID</option>
            <option value="payment_id"<?php echo $_GET['sfl'] == 'payment_id' ? ' selected' : '' ?>>결제ID</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo htmlspecialchars($stx) ?>" id="stx" class="frm_input">
        
        <label for="ser_date_field" class="sound_only">날짜필드</label>
        <select name="ser_date_field" id="ser_date_field" class="frm_input">
            <option value="created_at"<?php echo $ser_date_field == 'created_at' ? ' selected' : '' ?>>생성일시</option>
            <option value="earned_at"<?php echo $ser_date_field == 'earned_at' ? ' selected' : '' ?>>적립일시</option>
            <option value="used_at"<?php echo $ser_date_field == 'used_at' ? ' selected' : '' ?>>사용일시</option>
        </select>
        <label for="fr_date" class="sound_only">시작일</label>
        <input type="text" name="fr_date" value="<?php echo htmlspecialchars($fr_date) ?>" id="fr_date" class="frm_input" size="10" placeholder="시작일">
        <label for="to_date" class="sound_only">종료일</label>
        <input type="text" name="to_date" value="<?php echo htmlspecialchars($to_date) ?>" id="to_date" class="frm_input" size="10" placeholder="종료일">
        
        <input type="submit" class="btn_submit" value="검색">
    </div>
</form>

<!-- 포인트 수동 부여 영역 -->
<div class="local_desc01 local_desc" style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd;">
    <h3 style="margin-top: 0;">포인트 수동 부여</h3>
    <form name="fgrant" id="fgrant" method="post" action="./pointlist_update.php" onsubmit="return grant_point_submit(this);">
        <input type="hidden" name="act" value="grant">
        <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
        <input type="hidden" name="selected_customer_id" id="selected_customer_id" value="">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="open_customer_popup();" class="btn_01 btn">회원선택</button>
            <label for="selected_customer_name" class="sound_only">회원명</label>
            <input type="text" name="selected_customer_name" id="selected_customer_name" value="" readonly class="frm_input" style="width: 150px;" placeholder="회원을 선택하세요">
            <label for="grant_point_amount" class="sound_only">포인트 금액</label>
            <input type="number" name="grant_point_amount" id="grant_point_amount" value="" class="frm_input" style="width: 120px;" placeholder="포인트 금액" min="1" required>
            <label for="grant_point_memo" class="sound_only">메모</label>
            <input type="text" name="grant_point_memo" id="grant_point_memo" value="" class="frm_input" style="width: 200px;" placeholder="메모 (선택사항)">
            <button type="submit" class="btn_01 btn">포인트부여</button>
        </div>
    </form>
</div>

<form name="form01" id="form01" action="./pointlist_update.php" onsubmit="return form01_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $_GET['sfl'] ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="ser_type" value="<?php echo $ser_type ?>">
    <input type="hidden" name="ser_date_field" value="<?php echo $ser_date_field ?>">
    <input type="hidden" name="fr_date" value="<?php echo $fr_date ?>">
    <input type="hidden" name="to_date" value="<?php echo $to_date ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <input type="hidden" name="w" value="">
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed tbl_sticky_100">
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr class="success">
                    <th scope="col">
                        <label for="chkall" class="sound_only">전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" class="td_left">포인트ID</th>
                    <th scope="col" class="td_left">고객ID</th>
                    <th scope="col" class="td_left">고객명</th>
                    <th scope="col" class="td_left">회원ID</th>
                    <th scope="col">유형</th>
                    <th scope="col" class="td_num">금액</th>
                    <th scope="col" class="td_left">관련ID</th>
                    <th scope="col" class="td_left">결제ID</th>
                    <th scope="col" class="td_left">적립일시</th>
                    <th scope="col" class="td_left">사용일시</th>
                    <th scope="col" class="td_left">생성일시</th>
                    <th scope="col" class="td_left">메모</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                foreach ($earn_points as $earn_id => $earn_row) {
                    $point_id = $earn_row['point_id'];
                    $bg = 'bg'.($i%2);
                    $row_type = $earn_row['type']; // 실제 타입 가져오기
                    
                    // 날짜 포맷
                    $earned_at_text = $earn_row['earned_at'] ? date('Y-m-d H:i', strtotime($earn_row['earned_at'])) : '-';
                    $used_at_text = $earn_row['used_at'] ? date('Y-m-d H:i', strtotime($earn_row['used_at'])) : '-';
                    $created_at_text = $earn_row['created_at'] ? date('Y-m-d H:i', strtotime($earn_row['created_at'])) : '-';
                    
                    // 금액 표시 (타입에 따라)
                    if ($row_type == '적립') {
                        $amount_display = '<span style="color:blue; font-weight:bold;">+'.number_format($earn_row['amount']).'</span>';
                    } else if ($row_type == '사용') {
                        $amount_display = '<span style="color:red; font-weight:bold;">-'.number_format($earn_row['amount']).'</span>';
                    } else if ($row_type == '사용취소') {
                        $amount_display = '<span style="color:green; font-weight:bold;">+'.number_format($earn_row['amount']).'</span>';
                    } else if ($row_type == '적립취소') {
                        $amount_display = '<span style="color:orange; font-weight:bold;">-'.number_format($earn_row['amount']).'</span>';
                    } else {
                        $amount_display = number_format($earn_row['amount']);
                    }
                    
                    // 삭제 가능 여부 확인 (옵션 1: 적립 포인트만 삭제 가능, 관련 거래가 없을 때만)
                    $can_delete_earn = false;
                    if ($row_type == '적립') {
                        $can_delete_earn = empty($earn_row['related_transactions']) || count($earn_row['related_transactions']) == 0;
                    }
                    
                    // 배경색 (적립만 파란색, 다른 타입은 기본)
                    $row_bg_color = ($row_type == '적립') ? '#f0f8ff' : '#fff';
                ?>
                <!-- 포인트 거래 (메인 행) -->
                <tr class="<?=$bg?>" style="background-color: <?=$row_bg_color?>;" tr_id="<?=$point_id?>">
                    <td class="td_chk">
                        <input type="hidden" name="point_id[<?=$i?>]" value="<?=$point_id?>" id="point_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=$point_id?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>" <?php if (!$can_delete_earn) echo 'disabled title="관련 거래가 있어 삭제할 수 없습니다."'; ?>>
                    </td>
                    <td class="td_left font_size_8">
                        <strong><a href="?sfl=point_id&stx=<?=$point_id?>" style="color:blue; text-decoration:underline;"><?=$point_id?></a></strong>
                    </td>
                    <td class="td_left font_size_8"><?=$earn_row['customer_id']?></td>
                    <td class="td_left"><strong><?=get_text($earn_row['customer_name'])?:'-'?></strong></td>
                    <td class="td_left"><?=get_text($earn_row['user_id'])?:'-'?></td>
                    <td class="td_center"><strong><?=$type_arr[$row_type]?:$row_type?></strong></td>
                    <td class="td_num"><strong><?=$amount_display?></strong></td>
                    <td class="td_left">
                        <?php 
                        if ($earn_row['related_id']) {
                            echo '<a href="?sfl=point_id&stx='.$earn_row['related_id'].'" style="color:blue; text-decoration:underline;">'.$earn_row['related_id'].'</a>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="td_left font_size_8"><?=$earn_row['payment_id']?></td>
                    <td class="td_left font_size_8"><?=$earned_at_text?></td>
                    <td class="td_left font_size_8"><?=$used_at_text?></td>
                    <td class="td_left font_size_8"><strong><?=$created_at_text?></strong></td>
                    <td class="td_left"><?=cut_str(get_text($earn_row['memo']), 30, '...')?:'-'?></td>
                    <td class="td_mngsmall">
                        <?php if ($can_delete_earn) { ?>
                            <a href="javascript:void(0);" onclick="delete_single_point(<?=$i?>, <?=$point_id?>);">삭제</a>
                        <?php } else { ?>
                            <span style="color:#999; cursor:not-allowed;" title="<?php echo $row_type == '적립' ? '관련 거래가 있어 삭제할 수 없습니다.' : '적립 포인트만 삭제할 수 있습니다.'; ?>">삭제불가</span>
                        <?php } ?>
                    </td>
                </tr>
                
                <?php
                // 관련 거래 내역 (하위 행)
                if (!empty($earn_row['related_transactions'])) {
                    foreach ($earn_row['related_transactions'] as $related_row) {
                        $i++;
                        $related_point_id = $related_row['point_id'];
                        $related_bg = 'bg'.($i%2);
                        
                        $used_at_text = $related_row['used_at'] ? date('Y-m-d H:i', strtotime($related_row['used_at'])) : '-';
                        $related_created_at_text = $related_row['created_at'] ? date('Y-m-d H:i', strtotime($related_row['created_at'])) : '-';
                        
                        // 금액 표시
                        if ($related_row['type'] == '사용') {
                            $related_amount_display = '<span style="color:red;">-'.number_format($related_row['amount']).'</span>';
                        } else if ($related_row['type'] == '사용취소') {
                            $related_amount_display = '<span style="color:green;">+'.number_format($related_row['amount']).'</span>';
                        } else if ($related_row['type'] == '적립취소') {
                            $related_amount_display = '<span style="color:orange;">-'.number_format($related_row['amount']).'</span>';
                        } else {
                            $related_amount_display = number_format($related_row['amount']);
                        }
                        
                        // 타입 배열에 적립취소 추가
                        if (!isset($type_arr[$related_row['type']])) {
                            $type_arr[$related_row['type']] = '<span style="color:orange;">'.$related_row['type'].'</span>';
                        }
                ?>
                <!-- 관련 거래 (하위 행, 들여쓰기) -->
                <tr class="<?=$related_bg?>" style="background-color: #fafafa;" tr_id="<?=$related_point_id?>">
                    <td class="td_chk">
                        <input type="hidden" name="point_id[<?=$i?>]" value="<?=$related_point_id?>" id="point_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=$related_point_id?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>" disabled title="관련 거래는 삭제할 수 없습니다. 적립 포인트만 삭제 가능합니다.">
                    </td>
                    <td class="td_left font_size_8" style="padding-left: 30px;">
                        <span style="color: #999;">└─</span>
                        <a href="?sfl=point_id&stx=<?=$related_point_id?>" style="color:blue; text-decoration:underline;"><?=$related_point_id?></a>
                    </td>
                    <td class="td_left font_size_8"><?=$related_row['customer_id']?></td>
                    <td class="td_left"><?=get_text($related_row['customer_name'])?:'-'?></td>
                    <td class="td_left"><?=get_text($related_row['user_id'])?:'-'?></td>
                    <td class="td_center"><?=$type_arr[$related_row['type']]?:$related_row['type']?></td>
                    <td class="td_num"><?=$related_amount_display?></td>
                    <td class="td_left">
                        <a href="?sfl=point_id&stx=<?=$earn_id?>" style="color:blue; text-decoration:underline;"><?=$earn_id?></a>
                    </td>
                    <td class="td_left font_size_8"><?=$related_row['payment_id']?></td>
                    <td class="td_left font_size_8">-</td>
                    <td class="td_left font_size_8"><?=$used_at_text?></td>
                    <td class="td_left font_size_8"><?=$related_created_at_text?></td>
                    <td class="td_left"><?=cut_str(get_text($related_row['memo']), 30, '...')?:'-'?></td>
                    <td class="td_mngsmall">
                        <span style="color:#999; cursor:not-allowed;" title="관련 거래는 삭제할 수 없습니다. 적립 포인트만 삭제 가능합니다.">삭제불가</span>
                    </td>
                </tr>
                <?php
                    }
                }
                $i++;
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
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once('./js/pointlist.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
});
</script>

