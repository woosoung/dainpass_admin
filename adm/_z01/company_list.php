<?php
$sub_menu = "930600";
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

auth_check($auth[$sub_menu],"r");


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


$com_types = array('purchase','sales','both','etc');
$com_types_string = implode("','", $com_types);
$com_types_string = "'" . $com_types_string . "'";


$sql_common = " FROM {$g5['company_table']} AS com
                LEFT JOIN {$g5['company_member_table']} AS cmm ON com.com_idx = cmm.cmm_com_idx AND cmm_status = 'ok'
                LEFT JOIN {$g5['member_table']} AS mb ON cmm.cmm_mb_id = mb.mb_id AND mb_leave_date = '' AND mb_intercept_date = '' ";

//-- 업종 검색
$sql_com_type = ($com_types_string) ? " AND com_type IN (".$com_types_string.") " : "";

$where = array();
$where[] = " com_status != 'trash' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
            break;
		case ( $sfl == 'mb_id' || $sfl == 'com.com_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_name' || $sfl == 'mb_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "com_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT com.com_idx, com_name, com_names, com_type, com_reg_dt, com_status
            ,com_tel, com_president, com_email, com_fax
            ,GROUP_CONCAT( CONCAT(
                'mb_id=', cmm.cmm_mb_id, '^'
                ,'cmm_rank=', cmm.cmm_rank, '^'
                ,'mb_name=', mb_name, '^'
                ,'mb_hp=', mb_hp
            ) ORDER BY cmm_reg_dt DESC ) AS com_namagers_info
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        GROUP BY com_idx
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 9;


$g5['title'] = '거래처관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only2">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_options_value']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET['ser_com_type']?>').attr('selected','selected');</script>
<select name="sfl" id="sfl">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>담당자휴대폰</option>
    <option value="com_president"<?php echo get_selected($_GET['sfl'], "com_president"); ?>>대표자</option>
	<option value="com.com_idx"<?php echo get_selected($_GET['sfl'], "com.com_idx"); ?>>업체고유번호</option>
	<option value="cmm.cmm_mb_id"<?php echo get_selected($_GET['sfl'], "cmm.cmm_mb_id"); ?>>담당자아이디</option>
    <option value="com_status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only2">검색어<strong class="sound_only2"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>업체측 담당자를 관리하시려면 업체담당자 항목의 <i class="bi bi-pencil-square text-blue-800"></i> 편집아이콘을 클릭하세요. 담당자는 여러명일 수 있고 이직을 하는 경우 다른 업체에 소속될 수도 있습니다. </p>
</div>

<form name="form01" id="form01" action="./company_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?=$form_input?>

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <tr class="success">
		<th scope="col">
			<label for="chkall" class="sound_only">업체 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">업체번호</th>
		<th scope="col">업체명</th>
		<th scope="col">대표자명</th>
		<th scope="col">이메일</th>
		<th scope="col" style="width:120px;">대표전화</th>
		<th scope="col">업체담당자</th>
		<th scope="col">업체구분</th>
		<th scope="col" id="mb_list_mng">수정</th>
	</tr>
    </thead>
    <tbody>
    <?php
    for($i=0; $row=sql_fetch_array($result); $i++) { 
        // 메타 분리
        if($row['com_namagers_info']) {
            $pieces = explode(',', $row['com_namagers_info']);
            for ($j1=0; $j1<sizeof($pieces); $j1++) {
                $sub_item = explode('^', $pieces[$j1]);
                for ($j2=0; $j2<sizeof($sub_item); $j2++) {
                    list($key, $value) = explode('=', $sub_item[$j2]);
//                    echo $key.'='.$value.'<br>';
                    $row['com_managers'][$j1][$key] = $value;
                }
            }
            unset($pieces);unset($sub_item);
        }
        // 담당자(들)
        if( is_array($row['com_managers']) ) {
            for ($j=0; $j<sizeof($row['com_managers']); $j++) {
//                echo $key.'='.$value.'<br>';
                $row['com_managers_text'] .= $row['com_managers'][$j]['mb_name'].' ['.$row['com_managers'][$j]['mb_id'].']';
                $row['com_managers_text'] .= $row['com_managers'][$j]['mb_hp'] ? ' <span class="font_size_8">(<i class="bi bi-telephone-fill"></i> '.$row['com_managers'][$j]['mb_hp'].')</span><br>' : '<br>' ;
            }
        }
        //수정버튼
        $s_mod = '<a href="./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$row['com_idx'].'">수정</a>';
    ?>
    <tr class="<?=$bg?>" tr_id="<?=$row['com_idx']?>">
        <td class="td_chk" >
			<input type="hidden" name="com_idx[<?=$i?>]" value="<?=$row['com_idx']?>" id="com_idx_<?=$i?>">
			<label for="chk_<?=$i?>" class="sound_only2"><?=get_text($row['com_name'])?></label>
			<input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_com_idx font_size_8"><!-- 번호 -->
			<?=$row['com_idx']?>
		</td>
        <td class="td_com_name td_left"><!-- 업체명 -->
			<b><?=get_text($row['com_name'])?></b>
		</td>
        <td class="td_com_president"><!-- 대표자명 -->
			<?=get_text($row['com_president'])?>
		</td>
        <td class="td_com_email font_size_8"><!-- 이메일 -->
			<?=cut_str($row['com_email'],21,'..')?>
		</td>
        <td class="td_com_tel"><!-- 대표전화 -->
			<span class="font_size_8"><?=formatPhoneNumber($row['com_tel'])?></span>
		</td>
        <td class="td_com_manager td_left" style="position:relative;padding-left:25px;font-size:1em;vertical-align:top;"><!-- 업체담당자 -->
			<?php echo $row['com_managers_text']; ?>
            <div style="display:<?=($is_admin=='super')?:'no ne'?>">
                <a href="javascript:" com_idx="<?=$row['com_idx']?>" class="btn_manager" style="position:absolute;top:5px;left:5px;font-size:1.1rem;">
                    <i class="bi bi-pencil-square text-blue-800"></i>
                </a>
            </div>
		</td>
        <td class="td_mmg font_size_8"><!-- 업체구분 -->
            <?=$set_conf['set_com_type_karr'][$row['com_type']]?>
		</td>
        <td class="td_mngsmall">
			<?=$s_mod?>
		</td>
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
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="./company_form.php" id="bo_add" class="btn_01 btn">업체추가</a>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');