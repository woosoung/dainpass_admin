<?php
$sub_menu = "920200";
include_once("./_common.php");
include_once(G5_ZSQL_PATH.'/term_rank.php');

// mb_level에 따라 $sub_menu 재정의
$is_store_owner = false; // 가맹점 오너 여부
$is_store_manager = false; // 가맹점 관리자 여부

if ($is_member && $member['mb_id']) {
    if ($member['mb_level'] >= 4 && $member['mb_level'] <= 5) {
        // 가맹점 오너/관리자는 930100 권한으로 체크
        $sub_menu = "930100";

        // 오너/관리자 플래그 설정
        $is_store_owner = ($member['mb_level'] == 5);
        $is_store_manager = ($member['mb_level'] == 4);

        // shop_id 소유권 검증 (필수!)
        $user_shop_id = (int)trim($member['mb_1']);
        $requested_shop_id = (int)$shop_id;

        if ($requested_shop_id > 0 && $user_shop_id > 0 && $requested_shop_id != $user_shop_id) {
            alert_close('자신의 가맹점만 관리할 수 있습니다.');
        }
    }
}

@auth_check($auth[$sub_menu], 'w');

// 가맹점 관리자는 담당자 추가 불가
if ($w == '' && $is_store_manager) {
    alert_close('담당자 추가 권한이 없습니다.');
}

if ($w == 'u') {
    $mb = get_table_meta('member','mb_id',$mb_id);
    $mbt = get_gmeta('member',$mb_id);
    if(count($mbt)){
        $mb = array_merge($mb,$mbt);
    }

    // 가맹점 관리자(mb_level=4)는 본인 계정만 수정 가능
    if ($is_store_manager && $mb_id != $member['mb_id']) {
        alert_close('본인 계정만 수정할 수 있습니다.');
    }
}


$shop = get_table_meta_pg('shop','shop_id',$shop_id);

//print_r2($cst);
//exit;
if(!$shop['shop_id'])
    alert('가맹점 정보가 존재하지 않습니다.');
//	print_r2($cst);


if ($w == '') {
    $html_title = '추가';
}
else if ($w == 'u') {
    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = '가맹점관리자 '.$html_title;
include_once(G5_PATH.'/head.sub.php');
?>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>
<?php if(G5_IS_MOBILE){ ?>
<style>
.new_win .btn{width:30px;border:0;}
.btn_close{background-image:url(https://icongr.am/fontawesome/times.svg?size=20&color=7a7a7a);background-repeat:no-repeat;background-position:center;font-size:0;background-color:#ddd;}
.btn_delete{background-image:url(https://icongr.am/fontawesome/trash-o.svg?size=20&color=7a7a7a);background-repeat:no-repeat;background-position:center;font-size:0;background-color:#ddd;}
.btn_list{background-image:url(https://icongr.am/fontawesome/list.svg?size=20&color=7a7a7a);background-repeat:no-repeat;background-position:center;font-size:0;margin:0;}
</style>
<?php } ?>
<div class="new_win pt-[60px]">
    <h1 class="fixed w-full top-[0px]"><?php echo $g5['title']; ?></h1>
    <div class="local_desc01 local_desc">
        <p>- 본 페이지는 가맹점관리자를 간단하게 관리하는 페이지입니다.</p>
        <p>- 아이디, 비번은 임의로 생성됩니다.</p>
        <!-- <p>휴대폰 번호 중복 불가! (중복인 경우 이전 회원정보에 추가됩니다.)</p> -->
        <!-- <p>회원가입을 시키시고 관리자 승인 후 사용하게 하는 것이 더 좋습니다.</p> -->
    </div>
    <form name="form01" id="form01" action="./shop_manager_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="shop_id" value="<?=$shop_id??''?>">
	<input type="hidden" name="token" value="">
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
                    <div><?php echo $shop['name'];?><?php if(isset($shop['branch'])) echo ' / '.$shop['branch']; ?></div>
                    <div class="font_size_9">대표: <?php echo $shop['owner_name'];?></div>
				</td>
			</tr>
			<tr>
				<th scope="row">담당자명</th>
				<td>
                    <input type="hidden" name="mb_id" value="<?=$mb['mb_id']??''?>" id="mb_id" class="frm_input">
                    <input type="hidden" name="mb_nick" value="<?=$mb['mb_nick']??''?>" id="mb_nick" class="frm_input">
                    <input type="hidden" name="mb_1" value="<?=$mb['mb_1']??''?>" id="mb_1" class="frm_input"><!--//가맹점아이디-->
                    <input type="hidden" name="mb_2" value="<?=$mb['mb_2']??''?>" id="mb_2" class="frm_input"><!--//가맹점관리자여부(Y/N)-->
                    <input type="text" name="mb_name" value="<?=$mb['mb_name']??''?>" required class="frm_input required" style="width:50% !important;">
					<select name="mb_rank">
						<option value="">직함</option>
                        <?=$rank_opt?>
					</select>
					<script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const selectEl = document.querySelector('select[name="mb_rank"]');
                        if (selectEl) {
                            const valueToSelect = '<?=$mb['mb_rank']??''?>';
                            selectEl.value = valueToSelect; // select의 값 설정
                        }
                    });
                    </script>
				</td>
			</tr>
            <?php if($w == 'u') { ?>
            <tr>
                <th scope="row">회원ID</th>
                <td><?=$mb['mb_id']??''?></td>
            </tr>
            <tr>
                <th scope="row">비밀번호</th>
                <td>
                    <input type="password" name="mb_password" value="" class="frm_input" style="width:50% !important;">
                </td>
            </tr>
            <?php  } ?>
			<tr>
				<th scope="row">휴대폰</th>
				<td>
                    <?php echo help('숫자만 입력하세요.') ?>
                    <input type="hidden" value="ok" id="mb_hp_flag" class="frm_input">
                    <input type="text" name="mb_hp" id="mb_hp" value="<?=$mb['mb_hp']??''?>" required class="frm_input required">
                    <span id="hp_verfiy_msg" class="text-xs text-red-500"></span>
				</td>
			</tr>
			<tr>
				<th scope="row">이메일</th>
				<td>
                    <input type="text" name="mb_email" value="<?=$mb['mb_email']??''?>" class="frm_input" style="width:100%;">
				</td>
			</tr>
			<tr>
				<th scope="row">메모</th>
				<td colspan="3"><textarea name="mb_memo" id="mb_memo"><?php echo $mb['mb_memo']??''; ?></textarea></td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="btn_fixed_top top-[11px]">
        <input type="button" class="btn_close btn btn_02" value="창닫기" onclick="javascript:opener.location.reload();window.close();">
        <!-- <input type="button" class="btn_close btn btn_02" value="창닫기" onclick="javascript:window.close();"> -->
        <input type="button" class="btn btn_01" value="목록" onClick="self.location='./shop_manager_list.php?shop_id=<?=$shop['shop_id']?>'">
    </div>
	<div class="win_btn ">
        <?php if($w == 'u') { ?>
        <?php
        // 삭제 버튼: 가맹점 오너 또는 플랫폼 관리자만 표시
        $show_delete = ($is_store_owner || $member['mb_level'] >= 6);
        ?>
        <input type="button" class="btn_delete btn btn_02" value="삭제" style="display:<?=(!$mb['mb_id'] || !$show_delete)?'none':'';?>;">
        <?php } ?>
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
    </div>

    </form>

</div>

<script>
var g5_admin_csrf_token_key = "<?php echo (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";

$(function() {

    // 휴대폰 중복 체크 (중복 회원이 있으면 이메일 주소 자동 입력)
    // $(document).on('click','#btn_member',function(e){

    // });

    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
            var del_url = "./shop_manager_form_update.php?token="+token+"&w=d&shop_id=<?=(isset($shop['shop_id'])?$shop['shop_id']:'')?>&mb_id=<?=$mb['mb_id']??''?>";
            // alert(del_url);
			self.location = del_url;
		}
	});

    
});

document.addEventListener("DOMContentLoaded", function () {
    const hpInput = document.querySelector('input[name="mb_hp"]');

    if (hpInput) {
        hpInput.addEventListener("input", function (e) {
            let value = e.target.value;

            // 1숫자 이외의 모든 문자 제거
            value = value.replace(/[^0-9]/g, '');

            // 0으로 시작하는 것은 허용하면서 11자리까지만 입력
            if (value.length > 11) {
                value = value.slice(0, 11); // 11자리로 자르기
            }


            // 0으로 시작하는 것은 허용 (따라서 별도 제약 불필요)
            // 단, 길이 제한을 두고 싶다면 다음 코드 추가 가능:
            // value = value.slice(0, 11);  // 예: 최대 11자리로 제한

            e.target.value = value;
        });

        hpInput.addEventListener("blur", function (e) {
            const value = e.target.value;
            if (value.length < 10) {
                document.getElementById('hp_verfiy_msg').textContent = "최소 10자리 이상 입력";
                e.target.focus();
                document.getElementById('mb_hp_flag').value = 'no';
                // e.target.value = ''; // 원하면 자동 초기화도 가능
            }
            else {
                document.getElementById('hp_verfiy_msg').textContent = "";
                document.getElementById('mb_hp_flag').value = 'ok';
            }
        });
    }
});



function form01_check(f) {
    
    if (f.mb_name.value=='') {
		alert("담당자를 입력하세요.");
		f.mb_name.select();
		return false;
	}
    
	if (f.mb_hp.value=='') {
		alert("휴대폰을 입력하세요.");
		f.mb_hp.select();
		return false;
	}

    if (f.mb_hp_flag.value=='no') {
        alert("올바른 휴대폰 번호를 입력하세요.");
        f.mb_hp.select();
        return false;
    }


    // if (f.mb_email.value=='') {
	// 	alert("이메일을 입력하세요.");
	// 	f.mb_email.select();
	// 	return false;
	// }

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');
