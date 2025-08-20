<?php
$sub_menu = "920800";
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu],"r");


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

$sql_common = " FROM {$g5['shop_table']} AS com
            ";


$where = array();
$where[] = " com.status != 'trash' ";

$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';

if ($stx) {
    switch ($sfl) {
		case 'name' :
            $where[] = " ( name LIKE '%{$stx}%' OR names LIKE '%{$stx}%' ) ";
            break;
		case ( $sfl == 'com.shop_id' || $sfl == 'shop_name' || $sfl == 'business_no' ) : //case ( $sfl == 'mb_id' || $sfl == 'com.shop_id' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'owner_name' ) :
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
    $sst = "com.shop_id";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            LIMIT {$rows} OFFSET {$from_record} ";


$result = sql_query_pg($sql);

// 부하율 고려한 전체갯수 쿼리에서는 조건문 불가
// $sql = " SELECT n_live_tup AS total
//         FROM pg_stat_user_tables
//         WHERE relname = '{$g5['shop_table']}' 
// ";
$sql = " SELECT COUNT(*) AS total FROM {$g5['shop_table']} WHERE status != 'trash' ";
$count = sql_fetch_pg($sql);
$total_count = $count['total'];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산

$sql = " SELECT COUNT(*) AS cnt FROM {$g5['shop_table']} WHERE status = 'pending' ";
$row = sql_fetch_pg($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 11;

$g5['title'] = '가맹점관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_options_value']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET['ser_com_type']?>').attr('selected','selected');</script>
<select name="sfl" id="sfl">
	<option value="com.name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>담당자휴대폰</option>
    <option value="owner_name"<?php echo get_selected($_GET['sfl'], "owner_name"); ?>>대표자</option>
	<option value="com.shop_id"<?php echo get_selected($_GET['sfl'], "com.shop_id"); ?>>업체고유번호</option>
	<option value="cmm.mb_id"<?php echo get_selected($_GET['sfl'], "cmm.mb_is"); ?>>담당자아이디</option>
    <option value="com.status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>업체측 담당자를 관리하시려면 업체담당자 항목의 <i class="fa fa-edit"></i> 편집아이콘을 클릭하세요. 담당자는 여러명일 수 있고 이직을 하는 경우 다른 업체에 소속될 수도 있습니다. </p>
</div>
<form name="form01" id="form01" action="./company_list_update.php" onsubmit="return form01_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="w" value="">
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed tbl_sticky_100">
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr class="success">
                    <th scope="col">
                        <label for="chkall" class="sound_only">업체 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" class="td_left">번호</th>
                    <th scope="col" class="td_left">업종</th>
                    <th scope="col" class="td_left">업체명</th>
                    <th scope="col" class="td_left">가맹점명</th>
                    <th scope="col">대표자명</th>
                    <th scope="col">이메일</th>
                    <th scope="col">업체담당자</th>
                    <th scope="col" style="width:120px;">연락처</th>
                    <th scope="col">상태</th>
                    <th scope="col" id="mb_list_mng">수정</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_mod = '<a href="./company_form.php?'.$qstr.'&amp;w=u&amp;shop_id='.$row['shop_id'].'">수정</a>';


                    // default company class name
                    // $row['default_com_class'] = ($_SESSION['ss_shop_id']==$row['shop_id']&&$member['mb_manager_yn']) ? 'b_default_company' : '';
                    // 삭제인 경우 그레이 표현
                    $row['com_status_trash_class']	= ($row['status'] == 'trash') ? " tr_trash" : "";
                    $bg = 'bg'.($i%2);

                ?>
                <tr class="<?=$bg?><?=$row['com_status_trash_class']?>" tr_id="<?=$row['shop_id']?>">
                    <td class="td_chk">
                        <input type="hidden" name="shop_id[<?=$i?>]" value="<?=$row['shop_id']?>" id="shop_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['name'])?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
                    </td>
                    <td class="td_com_idx td_left font_size_8"><?=$row['shop_id']?></td><!-- 번호 -->
                    <td class="td_shop_categories td_left font_size_8"></td><!-- 업종 -->
                    <td class="td_com_name td_left"><!-- 업체명 -->
                        <b class="<?=$row['default_com_class']?>"><?=get_text($row['name'])?></b>
                        <a style="display:none;" href="javascript:company_popup('./company_order_list.popup.php?shop_id=<?=$row['shop_id']?>','<?=$row['shop_id']?>')" style="float:right;"><i class="fa fa-window-restore"></i></a>
                    </td>
                    <td class="td_shop_name td_left"><?=get_text($row['shop_name'])?></td>
                    <td class="td_owner_name"><?=get_text($row['owner_name'])?></td><!-- 대표자명 -->
                    <td class="td_contact_email font_size_8"><?=cut_str($row['contact_email'],30,'...')?></td><!-- 이메일 -->
                    <td class="td_shop_manager td_left"></td><!-- 업체담당자 -->
                    <td class="td_contact_phone"><span class="font_size_8"><?=$row['contact_phone']?></span></td><!-- 대표전화 -->
                    <td headers="list_com_status" class="td_status"><?=$row['status']?></td><!-- 상태 -->
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
        <input type="submit" name="act_button" value="디폴트업체변경" onclick="document.pressed=this.value" class="btn_03 btn" style="margin-right:50px;display:none;">

        <?php if(!@auth_check($auth[$sub_menu],"d",1)) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <?php } ?>
        <a href="./company_form.php" id="bo_add" class="btn_01 btn">업체추가</a>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');