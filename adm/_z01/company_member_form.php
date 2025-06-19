<?php
$sub_menu = "930600";
include_once("./_common.php");
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

auth_check($auth[$sub_menu], 'w');

if ($w == 'u') {
    $cmm = get_table_meta('company_member','cmm_idx',$cmm_idx);
    $com_idx = $cmm['cmm_com_idx'];
}
//print_r2($cmm);
//exit;

$com = get_table_meta('company','com_idx',$com_idx);
//print_r2($com);
//exit;
if(!$com['com_idx'])
    alert('업체 정보가 존재하지 않습니다.');
//	print_r2($com);


if ($w == '') {
    $html_title = '추가';

    $mb['mb_id'] = time();
    $mb['mb_nick'] = time();
    $mb['cmm_status'] = 'ok';
}
else if ($w == 'u') {
    $mb = get_table_meta('member','mb_id',$cmm['cmm_mb_id']);
//	print_r2($mb);

    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = '담당자 '.$html_title;
include_once(G5_PATH.'/head.sub.php');
add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js?ver='.G5_JS_VER.'"></script>',0);
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>
	<?php if(!G5_IS_MOBILE){ ?>
    <div class="local_desc01 local_desc">
        <p>본 페이지는 담당자를 간단하게 관리하는 페이지입니다.(아이디, 비번 임의생성)</p>
        <p>휴대폰 번호 중복 불가! (중복인 경우 이전 회원정보에 추가됩니다.)</p>
        <p>회원가입을 시키시고 관리자 승인 후 사용하게 하는 것이 더 좋습니다.</p>
    </div>
	<?php } ?>

    <form name="form01" id="form01" action="./company_member_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?=$w?>">
	<input type="hidden" name="com_idx" value="<?=$com_idx?>">
	<input type="hidden" name="cmm_idx" value="<?=$cmm_idx?>">
	<input type="hidden" name="token" value="">
	<input type="hidden" name="ex_page" value="<?=$ex_page?>">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">업체명</th>
				<td>
                    <div><?php echo $com['com_name'];?></div>
                    <div class="font_size_9">대표: <?php echo $com['com_president'];?></div>
				</td>
			</tr>
			<tr>
				<th scope="row">담당자명</th>
				<td>
                    <input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id" required class="frm_input required">
                    <input type="hidden" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" required class="frm_input required">
                    <input type="text" name="mb_name" value="<?=$mb['mb_name']?>" required class="frm_input required" style="width:50% !important;">
				</td>
			</tr>
			<tr>
                <th scope="row">직급/직책</th>
				<td>
                    <select name="cmm_rank">
                        <option value="">직급</option>
                        <?=$rank_opt?>
                    </select>
                    <script>$('select[name=cmm_rank]').val('<?=$cmm['cmm_rank']?>');</script>
					<select name="cmm_role">
						<option value="">직책</option>
                        <?=$role_opt?>
					</select>
					<script>$('select[name=cmm_role]').val('<?=$cmm['cmm_role']?>');</script>
				</td>
			</tr>
			<tr>
				<th scope="row">휴대폰</th>
				<td>
                    <input type="text" name="mb_hp" value="<?=$mb['mb_hp']?>" class="frm_input">
				</td>
			</tr>
			<tr>
				<th scope="row">이메일</th>
				<td>
                    <input type="text" name="mb_email" value="<?=$mb['mb_email']?>" class="frm_input" style="width:100%;">
				</td>
			</tr>
			<tr>
				<th scope="row">메모</th>
				<td><textarea name="mb_memo" id="mb_memo"><?=$mb['mb_memo']?></textarea></td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="btn_fixed_top" style="top:0;">
        <input type="button" class="btn_close btn btn_03" value="창닫기" onclick="javascript:opener.location.reload();window.close();">
        <input type="button" class="btn btn_02 btn_list" value="목록" onClick="self.location='./company_member_list.php?com_idx=<?=$com_idx?>'">
        <input type="button" class="btn_delete btn btn_02" value="삭제" style="display:<?=(!$cmm_idx)?'none':'';?>;">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
	</div>
    </form>

</div>

<script>
var g5_admin_csrf_token_key = "<?php echo (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";

$(function() {
    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./company_member_form_update.php?token="+token+"&w=d&com_idx=<?=$com_idx?>&cmm_idx=<?=$cmm_idx?>";
		}
	});
});

function form01_check(f) {
    if (f.mb_name.value=='') {
		alert("담당자명을 입력하세요.");
		f.mb_name.select();
		return false;
	}
    
	if (f.mb_hp.value=='') {
		alert("휴대폰을 입력하세요.");
		f.mb_hp.select();
		return false;
	}

    if (f.mb_email.value=='') {
		alert("이메일을 입력하세요.");
		f.mb_email.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');