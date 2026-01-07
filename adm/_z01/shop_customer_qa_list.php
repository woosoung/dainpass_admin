<?php
$sub_menu = "960300";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 페이징 검증
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0 && $page <= 10000) ? $page : 1;

// 정렬 필드 화이트리스트
$allowed_sst = array('q.qna_id', 'q.qna_created_at', 'c.nickname');
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : '';
$sst = in_array($sst, $allowed_sst) ? $sst : '';

// 정렬 방향 화이트리스트
$allowed_sod = array('asc', 'desc', 'ASC', 'DESC');
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : '';
$sod = in_array($sod, $allowed_sod) ? $sod : '';

// 검색 필드 화이트리스트 (닉네임만 허용)
$allowed_sfl = array('', 'nickname', 'qna_subject', 'qna_content');
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$sfl = in_array($sfl, $allowed_sfl) ? $sfl : '';

// 검색어 검증
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$stx = substr($stx, 0, 100); // 최대 길이 제한
$stx = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $stx); // SQL 특수문자 이스케이프

$form_input = '';
$qstr = '';
// 검색 조건을 qstr에 추가
if (!empty($stx)) {
    $qstr .= '&stx='.urlencode($stx);
}
if (!empty($sfl)) {
    $qstr .= '&sfl='.urlencode($sfl);
}
if (!empty($sst)) {
    $qstr .= '&sst='.urlencode($sst);
}
if (!empty($sod)) {
    $qstr .= '&sod='.urlencode($sod);
}
if (!empty($ser_status)) {
    $qstr .= '&ser_status='.urlencode($ser_status);
}
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
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value ?? '', 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value ?? '', 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$sql_common = " FROM shop_qna q
                LEFT JOIN customers c ON q.customer_id = c.customer_id ";

$where = array();
// 가맹점별 문의만 조회 (shop_id = 해당 가맹점)
$where[] = " q.shop_id = {$shop_id} ";
// 최초 질문만 조회 (qna_parent_id IS NULL)
$where[] = " q.qna_parent_id IS NULL ";

// 검색 조건 처리 (닉네임만 허용)
if ($stx !== '') {
    // PostgreSQL 이스케이프 (이미 SQL 특수문자는 이스케이프 처리됨)
    $stx_escaped = pg_escape_string($g5['connect_pg'], $stx);

    switch ($sfl) {
        case 'nickname' :
            $where[] = " c.nickname ILIKE '%{$stx_escaped}%' ";
            break;
        case 'qna_subject' :
            $where[] = " q.qna_subject ILIKE '%{$stx_escaped}%' ";
            break;
        case 'qna_content' :
            $where[] = " q.qna_content ILIKE '%{$stx_escaped}%' ";
            break;
        default :
            // 전체 검색: 제목, 내용, 닉네임
            $where[] = " ( q.qna_subject ILIKE '%{$stx_escaped}%' OR q.qna_content ILIKE '%{$stx_escaped}%' OR c.nickname ILIKE '%{$stx_escaped}%' ) ";
            break;
    }
}

// 답변 상태 필터 화이트리스트
$allowed_ser_status = array('', 'pending', 'answered');
$ser_status = isset($_GET['ser_status']) ? trim(clean_xss_tags($_GET['ser_status'])) : '';
$ser_status = in_array($ser_status, $allowed_ser_status) ? $ser_status : '';

if ($ser_status !== '') {
    // 답변이 있는 문의만 조회
    if ($ser_status == 'answered') {
        $where[] = " EXISTS (SELECT 1 FROM shop_qna sq WHERE sq.qna_parent_id = q.qna_id AND sq.reply_mb_id IS NOT NULL) ";
    }
    // 답변이 없는 문의만 조회
    elseif ($ser_status == 'pending') {
        $where[] = " NOT EXISTS (SELECT 1 FROM shop_qna sq WHERE sq.qna_parent_id = q.qna_id AND sq.reply_mb_id IS NOT NULL) ";
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);
else
    $sql_search = '';

// 정렬 기본값 설정
if (!$sst) {
    $sst = "q.qna_id";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;
$from_record = ($page - 1) * $rows;

$sql = " SELECT q.*, c.user_id, c.name, c.nickname, c.phone, c.email,
                (SELECT COUNT(*) FROM shop_qna sq WHERE sq.qna_parent_id = q.qna_id) AS reply_count
                -- (SELECT COUNT(*) FROM shop_qna sq WHERE sq.qna_parent_id = q.qna_id AND sq.reply_mb_id IS NOT NULL) AS admin_reply_count
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

// 답변 대기 문의 수
$sql = " SELECT COUNT(*) AS cnt FROM shop_qna q 
         WHERE q.shop_id = {$shop_id} 
         AND q.qna_parent_id IS NULL 
         AND NOT EXISTS (SELECT 1 FROM shop_qna sq WHERE sq.qna_parent_id = q.qna_id AND sq.reply_mb_id IS NOT NULL) ";
$row = sql_fetch_pg($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = ($member['mb_level'] >= 8) ? 10 : 9;

$g5['title'] = '고객문의관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">답변대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="ser_status" class="sound_only">답변상태</label>
<select name="ser_status" id="ser_status" class="cp_field" title="답변상태">
    <option value="">전체</option>
    <option value="pending"<?php echo get_selected($ser_status, "pending"); ?>>답변대기</option>
    <option value="answered"<?php echo get_selected($ser_status, "answered"); ?>>답변완료</option>
</select>

<select name="sfl" id="sfl">
    <option value=""<?php echo get_selected($sfl, ""); ?>>전체</option>
    <option value="qna_subject"<?php echo get_selected($sfl, "qna_subject"); ?>>제목</option>
    <option value="qna_content"<?php echo get_selected($sfl, "qna_content"); ?>>내용</option>
    <option value="nickname"<?php echo get_selected($sfl, "nickname"); ?>>닉네임</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8') ?>" id="stx" class="frm_input" maxlength="100">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>
        고객 문의를 관리하고 답변할 수 있습니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<form name="form01" id="form01" action="./shop_customer_qa_list_update.php" onsubmit="return form01_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="w" value="">
    <?php echo $form_input; ?>
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed tbl_sticky_100">
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr class="success">
                    <?php if($member['mb_level'] >= 8) { ?>
                    <th scope="col">
                        <label for="chkall" class="sound_only">문의 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <?php } ?>
                    <th scope="col" class="">번호</th>
                    <th scope="col" class="td_left">회원 닉네임</th>
                    <th scope="col" class="td_left">제목</th>
                    <th scope="col" class="td_left">내용</th>
                    <th scope="col">비밀글</th>
                    <th scope="col">답변수</th>
                    <th scope="col">상태</th>
                    <th scope="col">등록일</th>
                    <th scope="col" id="mb_list_mng" class="w-[100px]">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_mod = '<a href="./shop_customer_qa_form.php?'.$qstr.'&amp;w=u&amp;qna_id='.$row['qna_id'].'" class="btn btn_03">조회</a>';

                    // 답변 상태
                    $has_reply = ($row['reply_count'] > 0);
                    $status_text = $has_reply ? '답변완료' : '답변대기';
                    $status_class = $has_reply ? 'text-green-600' : 'text-red-600';
                    
                    // 내용 미리보기
                    $content_preview = cut_str(strip_tags($row['qna_content']), 50, '...');
                    
                    $bg = 'bg'.($i%2);
                ?>
                <tr class="<?=$bg?>" tr_id="<?=$row['qna_id']?>">
                    <?php if($member['mb_level'] >= 8) { ?>
                    <td class="td_chk">
                        <input type="hidden" name="qna_id[<?=$i?>]" value="<?=$row['qna_id']?>" id="qna_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['qna_subject'])?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
                    </td>
                    <?php } ?>
                    <td class="td_qna_idx font_size_8"><?=$row['qna_id']?></td>
                    <td class="td_customer_info td_left">
                        <div class="text-sm">
                            <div><?=htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8')?></div>
                            <?php
                            // 개발관리자 이상에게만 노출
                            if ($member['mb_level'] >= 8) { ?>
                            <div class="text-xs text-gray-500">ID: <?=(int)$row['customer_id']?></div>
                            <?php } ?>
                        </div>
                    </td>
                    <td class="td_qna_subject td_left">
                        <?php if($row['qna_secret_yn'] == 'Y') { ?>
                            <i class="text-gray-500 fa fa-lock"></i>
                        <?php } ?>
                        <?=get_text($row['qna_subject'])?>
                    </td>
                    <td class="td_qna_content td_left"><?=$content_preview?></td>
                    <td class="td_secret"><?=($row['qna_secret_yn'] == 'Y') ? '비밀' : '공개'?></td>
                    <td class="td_reply_count">
                        <span class="text-blue-600"><?=$row['reply_count']?></span>
                    </td>
                    <td class="td_status">
                        <span class="<?=$status_class?>"><?=$status_text?></span>
                    </td>
                    <td class="td_created_at font_size_8"><?=substr($row['qna_created_at'],0,19)?></td>
                    <td class="td_mngsmall"><?=$s_mod?></td>
                </tr>
                <?php
                }
                if ($i == 0)
                    echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                ?>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <?php if($member['mb_level'] >= 8 && !@auth_check($auth[$sub_menu],"d",1)) { ?>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <?php } ?>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php include_once('./js/shop_customer_qa_list.js.php'); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
