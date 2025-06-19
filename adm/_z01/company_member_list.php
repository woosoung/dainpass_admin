<?php
$sub_menu = "930600";
include_once("./_common.php");
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');
auth_check($auth[$sub_menu], 'w');

if(!$com_idx)
    alert_close('업체 정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

$sql_common = " FROM {$g5['company_member_table']} AS cmm
                 LEFT JOIN {$g5['member_table']} AS mb ON cmm.cmm_mb_id = mb.mb_id AND mb_leave_date = '' AND mb_intercept_date = '' ";

$where = array();
$where[] = " cmm_status != 'trash' AND cmm.cmm_com_idx = '".$com_idx."' ";   // 디폴트 검색조건

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
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
    $sst = "cmm_reg_dt";
    $sod = "DESC";
}

$sql_order = " order by {$sst} {$sod} ";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '업체담당자';
include_once(G5_PATH.'/head.sub.php');

$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$result = sql_query($sql);
add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js?ver='.G5_JS_VER.'"></script>',0);
?>
<style>
    .btn_fixed_top {top: 9px;}
    .member_company_brief {margin:10px 0;}
    .member_company_brief span {font-size:1.3em;}
</style>

<div class="new_win">
    
    <h1><?php echo $g5['title']; ?></h1>
    <div class=" new_win_con">
        <div class="member_company_brief">
        <span><?=$com['com_name']?></span> (대표: <?=$com['com_president']?>)
        </div>
        
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
            <tr>
                <th scope="col" id="mb_list_chk" style="display:none;">
                    <label for="chkall" class="sound_only">담당자 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col">이름</th>
                <th scope="col">ID</th>
                <th scope="col">직급</th>
                <th scope="col">직책</th>
                <th scope="col">휴대폰</th>
				<?php if(!G5_IS_MOBILE){ ?>
                <th scope="col">이메일</th>
				<?php } ?>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                //print_r2($row);
                $s_mod = '<a href="./company_member_form.php?'.$qstr.'&amp;w=u&amp;cmm_idx='.$row['cmm_idx'].'" class="btn btn_03">수정</a>';

                $bg = 'bg'.($i%2);
            ?>

            <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['cmm_idx'] ?>" >
                <td headers="mb_list_chk" class="td_chk" style="display:none;">
                    <input type="hidden" name="cmm_idx[<?php echo $i ?>]" value="<?php echo $row['cmm_idx'] ?>" id="cmm_idx_<?php echo $i ?>">
                    <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_name']); ?>님</label>
                    <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                </td>
                <td class="td_mb_name"><?php echo get_text($row['mb_name']); ?></td>
                <td class="td_mb_id"><?php echo get_text($row['mb_id']); ?></td>
                <td class="td_mb_rank"><?=$rank_arr[$row['cmm_rank']]?></td>
                <td class="td_mb_role"><?=$role_arr[$row['cmm_role']]?></td>
                <td class="td_mb_hp"><?=$row['mb_hp']?></td>
                <td class="td_mb_email"><?=$row['mb_email']?></td>
                <td headers="mb_list_mng" class="td_mng td_mng_s">
                    <?php echo $s_mod ?><!-- 수정 -->
                </td>
            </tr>
            <?php
            }
            if ($i == 0)
                echo "<tr><td colspan='8' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
            </table>
        </div>

        <div class="btn_fixed_top">
            <a href="javascript:opener.location.reload();window.close();" id="member_add" class="btn btn_02">창닫기</a>
            <a href="./company_member_form.php?com_idx=<?=$com_idx?>" id="btn_add" class="btn btn_01">담당자추가</a>
        </div>        
        
    </div>
</div>

<script>
$(function() {

    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=700,scrollbars=1");
        memberwin.focus();
        return false;
    });
});

function form01_check(f) {

    // 팀개별분배는 아이디 제거해야 함
	if (f.sra_type.value=='team'&&f.cmm_idx_saler.value!='') {
		alert("팀개별분배인 경우 직원아이디값이 공백이어야 합니다.");
		f.cmm_idx_saler.select();
		return false;
	}
	// 개인분배는 아이디값이 반드시 있어야 함
	if (f.sra_type.value=='member'&&f.cmm_idx_saler.value=='') {
		alert("개인분배인 경우 직원아이디값이 존재해야 합니다.");
		f.cmm_idx_saler.select();
		return false;
	}
	if (isNaN(f.sra_price.value)==true) {
		alert("금액은 숫자만 가능합니다.");
		f.sra_price.focus();
		return false;
	}

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');