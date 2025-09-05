<?php
$sub_menu = "910600";
include_once('./_common.php');

include_once(G5_ZSQL_PATH.'/term_department.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu], 'r');

$form_input = '';
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


$sql_common = " FROM {$g5['member_table']} "; 

$where = array();
$where[] = " mb_level >= 6 ";   // 디폴트 검색조건
$where[] = " mb_level < 10 ";   // 디폴트 검색조건
$where[] = " mb_level <= {$member['mb_level']} ";   // 디폴트 검색조건(본인보다 높은 레벨은 검색하지 않음)
// if($member['mb_level'] == 6) {
//     $where[] = " mb_id = {$member['mb_id']} ";
// }


// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_level' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "mb_level";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", mb_name";
    $sod2 = "";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";
// echo $sql_order.BR;exit;
$sql = " SELECT COUNT(*) AS cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $sql.BR;

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} LIMIT {$from_record}, {$rows} ";
// echo $sql.BR;exit;
$result = sql_query($sql);

$colspan = 12;

$g5['title'] = '사원관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($_GET['sfl']??'', "mb_name"); ?>>이름</option>
    <option value="mb.mb_id"<?php echo get_selected($_GET['sfl']??'', "mb.mb_id"); ?>>아이디</option>
    <option value="mb_email"<?php echo get_selected($_GET['sfl']??'', "mb_email"); ?>>E-MAIL</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl']??'', "mb_hp"); ?>>휴대폰번호</option>
</select>
<label for="stx" class="sound_only">검색어</label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>사원의 회원등급은 기본 lv.6 이상입니다.</p>
</div>

<form name="fmemberlist" id="fmemberlist" action="./employee_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<?=$form_input?>
<div class="tbl_head01 tbl_wrap">
<table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="mb_list_chk">
            <label for="chkall" class="sound_only">사원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
        <th scope="col">직급</th>
        <th scope="col">직책</th>
        <th scope="col">부서</th>
        <th scope="col">휴대폰</th>
        <th scope="col">이메일</th>
        <th scope="col">권한등급</th>
        <th scope="col"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>입사일</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_leave_date', '', 'desc') ?>퇴사일</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) { 
        $mta_mb_arr = get_meta('member',$row['mb_id']);
        if(count($mta_mb_arr)){
            $row = array_merge($row,$mta_mb_arr);
        }
        unset($mta_mb_arr);
        $s_mod = '<a href="./employee_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="btn btn_03">수정</a>';
    // Alternate row background class and ensure $bg is defined
    $bg = ($i % 2) ? 'bg1' : 'bg0';
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?=$row['mb_id']?>">
        <td class="td_chk">
            <input type="hidden" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>" id="mb_id_<?=$i?>">
            <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['mb_name'])?> <?=get_text($row['mb_nick'])?>님</label>
            <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
        </td><!--선택-->
        <td class="td_mb_name w-[140px]"><?php echo get_text($row['mb_name']); ?></td><!--이름-->
        <td class="td_mb_id"><?php echo $row['mb_id']; ?></td><!--아이디-->
        <td class="td_mb_rank w-[100px]">
            <select name="mb_rank[<?=$i?>]" id="mb_rank_<?=$i?>">
                <option value="">::직급선택::</option>
                <?=$rank_opt?>
            </select>
            <script>$('#mb_rank_<?=$i?>').val('<?=$row['mb_rank']?>');</script>
        </td><!--직급-->
        <td class="td_mb_role w-[100px]">
            <select name="mb_role[<?=$i?>]" id="mb_role_<?=$i?>">
                <option value="">::직책선택::</option>
                <?=$role_opt?>
            </select>
            <script>$('#mb_role_<?=$i?>').val('<?=$row['mb_role']?>');</script>
        </td><!--직책-->
        <td class="td_mb_department w-[200px]">
            <select name="mb_department[<?=$i?>]" id="mb_department_<?=$i?>">
                <option value="">::부서선택::</option>
                <?=$department_opt?>
            </select>
            <script>$('#mb_department_<?=$i?>').val('<?=$row['mb_department']?>');</script>
        </td><!--부서-->
        <td class="td_hp w-[150px]"><?=formatPhoneNumber($row['mb_hp'])?></td><!--휴대폰-->
        <td class="td_mb_email w-[250px]"><?php echo $row['mb_email']; ?></td><!--이메일-->
        <td class="td_level w-[100px]">
            <select name="mb_level[<?=$i?>]" id="mb_level_<?=$i?>">
                <?php for($j=6; $j<=$member['mb_level']; $j++) { ?>
                <option value="<?=$j?>">lv.<?=$j?></option>
                <?php } ?>
            </select>
            <script>$('#mb_level_<?=$i?>').val('<?=$row['mb_level']?>');</script>
        </td><!--권한등급-->
        <td class="td_date w-[100px]">
            <input type="text" class="required_date w-[90px] border" name="mb_datetime[<?=$i?>]" id="mb_datetime_<?=$i?>" value="<?=substr($row['mb_datetime'],0,10)?>">
        </td><!--입사일-->
        <td class="td_date w-[100px]">
            <input type="text" class="tms_date w-[90px] border" name="mb_leave_date[<?=$i?>]" id="mb_leave_date_<?=$i?>" value="<?=formatDate($row['mb_leave_date'])?>">
        </td><!--퇴사일-->
        <td class="td_mng td_mng_s"><?php echo $s_mod ?></td><!--관리-->
        <script>
            single_date('#mb_leave_date_<?=$i?>');
            single_date('#mb_intercept_date_<?=$i?>');
        </script>
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
    <?php if ($is_manager){ // (!auth_check($auth[$sub_menu],'w',1)) { //($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02" style="display:no ne;">
    <input type="submit" name="act_button" value="선택퇴사" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./employee_form.php" id="member_add" class="btn btn_01">사원추가</a>
    <?php } ?>
</div>
</form>
<script>
function fmemberlist_submit(f){
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택퇴사") {
        if(!confirm("선택한 사원을 정말 퇴사처리 하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');