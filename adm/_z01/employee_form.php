<?php
$sub_menu = "910600";
include_once('./_common.php');

include_once(G5_ZSQL_PATH.'/term_department.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu],"r");

$form_input = '';
// 추가 변수 생성
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
    //    print_r3($key.'='.$value);
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
//                print_r3($key.$k2.'='.$v2);
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

$mb_id = ($w == '') ? '' : $mb_id;

// 사용관련
$sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'member' AND fle_dir = 'admin/member' AND fle_db_idx = '{$mb_id}' ORDER BY fle_reg_dt DESC ";
// echo $sql;exit;
$rs = sql_query_pg($sql);

$mbf['mbf_f_arr'] = array();
$mbf['mbf_fidxs'] = array();
$mbf['mbf_lst_idx'] = 0;
$mbf['fle_db_idx'] = $mb_id;
for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
    $is_s3file_yn = is_s3file($row2['fle_path']);
    $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="mbf_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
    '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
    $mbf['fle_db_idx'] = $row2['fle_db_idx'];
    @array_push($mbf['mbf_f_arr'], array('file'=>$row2['down_del']));
    @array_push($mbf['mbf_fidxs'], $row2['fle_idx']);
}


//회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
if(@count($mbf['mbf_fidxs'])) $mbf['mbf_lst_idx'] = $mbf['mbf_fidxs'][0];

// exit;







if ($w == '') {
    $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = 6;
    $html_title = '추가';
}
else if ($w == 'u')
{
    // $mb = get_member($mb_id);
    $mb = get_table_meta('member','mb_id',$mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] > $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 사원은 수정할 수 없습니다.');

    // print_r2($mb);exit;
    $required_mb_id = 'readonly';
    $required_mb_password = '';
    $html_title = '수정';
    
    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);

    // 사원 권한정보 가져오기
    $auth_sql = " SELECT * FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ORDER BY CAST(au_menu AS SIGNED) ";
    $auth_result = sql_query($auth_sql,1);
    $auth_arr = array();
    $auth_list = array();
    for($i=0;$row=sql_fetch_array($auth_result);$i++) {
        $rw_arr = explode(',',$row['au_auth']);
        $auth_bar = preg_replace('/,/','_',$row['au_auth']);
        $auth_arr[$row['au_menu']] = $rw_arr;
        array_push($auth_list,$row['au_menu'].'_'.$auth_bar);
    }

    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 본인확인방법
if($w == 'u'){
    switch($mb['mb_certify']) {
        case 'hp':
            $mb_certify_case = '휴대폰';
            $mb_certify_val = 'hp';
            break;
        case 'ipin':
            $mb_certify_case = '아이핀';
            $mb_certify_val = 'ipin';
            break;
        case 'admin':
            $mb_certify_case = '관리자 수정';
            $mb_certify_val = 'admin';
            break;
        default:
            $mb_certify_case = '';
            $mb_certify_val = 'admin';
            break;
    }
}

// 본인확인
$mb_certify_yes  =  isset($mb['mb_certify']) ? 'checked="checked"' : '';
$mb_certify_no   = !isset($mb['mb_certify']) ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  isset($mb['mb_adult'])      ? 'checked="checked"' : '';
$mb_adult_no        = !isset($mb['mb_adult'])      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  isset($mb['mb_mailling'])   ? 'checked="checked"' : '';
$mb_mailling_no     = !isset($mb['mb_mailling'])   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  isset($mb['mb_sms'])        ? 'checked="checked"' : '';
$mb_sms_no          = !isset($mb['mb_sms'])        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';


$g5['title'] = $g5['title'] ?? ''; // 초기화

if (isset($mb['mb_intercept_date'])) {
    $g5['title'] = "차단된 ";
}


$g5['title'] = '사원 '.$html_title;
require_once G5_ADMIN_PATH.'/admin.head.php';
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
add_javascript(G5_POSTCODE_JS, 0);//다음 주소 js
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fmember" id="fmember" action="./<?=$g5['file_name']?>_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>사원정보를 관리하는 페이지입니다.(사원의 회원등급은 기본 lv.6 이상입니다.)</p>
</div>
<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:10%;">
        <col style="width:40%">
        <col class="grid_4" style="width:10%;">
        <col style="width:40%">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?=$sound_only??''?></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_id"<?=(($w!='')?' readonly':'')?> value="<?=$mb['mb_id']??''?>" id="reg_mb_id" class="frm_input<?=(($w!='')?' readonly':'')?>" size="15"  maxlength="20" autocomplete="off">
                <?php if($w=='') { ?>
                <span class="s_id_info"></span>
                <?php } ?>
            </div>
        </td>
        <th scope="row"><label for="mb_password">비밀번호<?=$sound_only??''?></label></th>
        <td>
            <div class="flex gap-3">
                <?php if($w==''|| !auth_check($auth[$sub_menu]??'','r,w',1) || $member['mb_level'] == $mb['mb_level']) { ?>
                <input type="password" name="mb_password" id="mb_password" <?php //echo $required_mb_password ?> class="frm_input <?php //echo $required_mb_password ?>" size="15" maxlength="20" autocomplete="new-password">
                <?php } else { ?>
                <span style="color:#aaa;" class="inline-block w-[180px]">비밀번호 수정 불가</span>
                <?php } ?>
                <?php echo help('비밀번호는 반드시 영문으로 시작해야하고 이 후 영문숫자 조합으로 6글자이상 입력해 주세요.') ?>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_name">이름(실명)<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_name" value="<?=$mb['mb_name']??''?>" id="mb_name" class="frm_input" size="15"  maxlength="20" <?php if(@auth_check($auth[$sub_menu],'r,w',1)) echo 'readonly';?>></td>
        <th scope="row"><label for="mb_nick">닉네임<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="mb_nick" value="<?=$mb['mb_nick']??''?>" id="reg_mb_nick" class="frm_input" size="15"  maxlength="20" <?php if(@auth_check($auth[$sub_menu],'r,w',1)) echo 'readonly';?>>
            <?php if(@!auth_check($auth[$sub_menu],'r,w',1)) { ?>
                <span class="s_nick_info"></span>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_department">부서<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_department" id="mb_department" class="frm_input">
                <option value="">::부서선택::</option>
                <?=$department_opt?>
            </select>
            <script>
            const mb_department = document.querySelector('#mb_department');
            if (mb_department) mb_department.value = <?= json_encode($mb['mb_department'] ?? "") ?>;
            </script>
        </td>
        <th scope="row"><label for="mb_department">직급<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_rank" id="mb_rank" class="frm_input">
                <option value="">::직급선택::</option>
                <?=$rank_opt?>
            </select>
            <script>
            const mb_rank = document.querySelector('#mb_rank');
            if (mb_rank) mb_rank.value = <?= json_encode($mb['mb_rank'] ?? "") ?>;
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_role">직책<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_role" id="mb_role" class="frm_input">
                <option value="">::직책선택::</option>
                <?=$role_opt?>
            </select>
            <script>
            const mb_role = document.querySelector('#mb_role');
            if (mb_role) mb_role.value = <?= json_encode($mb['mb_role'] ?? "") ?>;
            </script>
        </td>
        <th scope="row"><label for="mb_email">이메일<strong class="sound_only">필수</strong></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_email" value="<?=$mb['mb_email']??''?>" id="reg_mb_email" class="frm_input w-[200px]" size="15"  maxlength="100">
                <span class="s_email_info"></span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_hp">휴대폰번호<strong class="sound_only">필수</strong></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_hp" value="<?=formatPhoneNumber($mb['mb_hp']??'')?>" id="reg_mb_hp" class="frm_input w-[200px]" size="15"  maxlength="20">
                <span class="s_hp_info"></span>
            </div>
        </td>
        <th scope="row"><label for="mb_level">권한등급</label></th>
        <td>
            <select name="mb_level" id="mb_lv" class="frm_input">
                <?php for($j=6; $j<=$member['mb_level']; $j++) { ?>
                <option value="<?=$j?>">lv.<?=$j?></option>
                <?php } ?>
            </select>
            <script>
            const mb_lv = document.querySelector('#mb_lv');
            if (mb_lv) mb_lv.value = <?= json_encode($mb['mb_level'] ?? "") ?>;
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row">주소</th>
        <td class="td_addr_line">
            <label for="mb_zip" class="sound_only">우편번호</label>
            <input type="text" name="mb_zip" value="<?=(($mb['mb_zip1']??'').($mb['mb_zip2']??''))?>" id="mb_zip" class="frm_input readonly w-[60px]" size="5" maxlength="6">
            <button type="button" class="btn_frmline" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button><br>
            <input type="text" name="mb_addr1" value="<?=$mb['mb_addr1']??''?>" id="mb_addr1" class="frm_input readonly" size="60">
            <label for="mb_addr1">기본주소</label><br>
            <input type="text" name="mb_addr2" value="<?=$mb['mb_addr2']??''?>" id="mb_addr2" class="frm_input" size="60">
            <label for="mb_addr2">상세주소</label>
            <br>
            <input type="text" name="mb_addr3" value="<?=$mb['mb_addr3']??''?>" id="mb_addr3" class="frm_input" size="60">
            <label for="mb_addr3">참고항목</label>
            <input type="hidden" name="mb_addr_jibeon" value="<?=$mb['mb_addr_jibeon']??''?>"><br>
        </td>
        <th scope="row">사원관련파일</th>
        <td>
            <?php echo help("사원관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file_mbf" name="mbf_datas[]" multiple class="">
            <?php
            if(@count($mbf['mbf_f_arr'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($mbf['mbf_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$mbf['mbf_f_arr'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_memo">메모</label></th>
        <td colspan="3">
            <textarea name="mb_memo" id="mb_memo"><?=$mb['mb_memo']??''?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_datetime">입사일<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="mb_datetime" value="<?=substr($mb['mb_datetime']??'',0,10)?>" id="mb_datetime" maxlength="100" readonly class="tms_date readonly frm_input datetime w-[100px]" size="30">
        </td>
        <th scope="row"><label for="mb_leave_date">퇴사일자</label></th>
        <td>
            <input type="text" name="mb_leave_date" value="<?=formatDate($mb['mb_leave_date']??'')?>" id="mb_leave_date" class="readonly tms_date frm_input w-[100px]" maxlength="8">
        </td>
    </tr>
    <?php
    if($w == 'u') {
        // 나는 나의 권한을 수정할 수 없다.나보다 등급이 낮은 사람의 권한은 편집할 수 있다.
        if($is_ultra || ($member['mb_id'] != $mb['mb_id'] && $member['mb_level'] > $mb['mb_level'])){
    ?>
    <tr>
        <th scope="row">
            관리페이지(권한설정)<br>
            <span id="all_auth_del" class="epf_all_auth_del_btn">전체권한삭제</span>
        </th>
        <td colspan="3" class="epf_menu_h3_class epf_menu_hs_class epf_menu_ul_class epf_menu_li_class epf_menu_sp_class">
            <?php
            // print_r2($member_auth_menus);
            // print_r2($menu);
            // echo $menu_list_tag;
            // print_r2($auth_arr);//[100300]= array(r,w);
            // print_r2($auth_list);//[0] = 100300_r_w
            $auths_str = (count($auth_list)) ? implode(',',$auth_list) : '';
            ?>
            <label for="auth_renewal" class="epf_auth_renewal_label">
                <input type="checkbox" name="auth_renewal" id="auth_renewal" value="1" class="border"> 메뉴권한재설정
            </label>
            <input type="hidden" name="auths" value="<?=$auths_str?>" class="border w-full">
            <?php
            // print_r2($menu_main_titles);
            //전체 메뉴구조를 확인하려면 변수($menu_list_tag_)맨끝에 '_'를 제거하세요.
            // echo $menu_list_tag 일때만 메뉴구조 확인 가능
            if($member['mb_level'] == 10){ echo $menu_list_tag_??''; }
            $auth_list_tag = '<div class="auth_box">'.PHP_EOL;
            foreach($menu2 as $k => $v){
                if(in_array($k,$set_menu['set_hide_mainmenus_arr'])) continue;
                if(count($v)){
                    $auth_list_tag .= '<div><h3 class="auth_h3" style="font-size:0.9rem;">'.$menu_main_titles[$k];
                    $auth_list_tag .= '<span class="group_y">그룹권한부여</span>';
                    $auth_list_tag .= '<span class="group_n">그룹권한삭제</span>';
                    $auth_list_tag .= '</h3><ul class="auth_ul">'.PHP_EOL;
                    foreach($v as $i => $s){
                        if($i == 0) continue;
                        if(in_array($s[0],$set_menu['set_hide_submenus_arr'])) continue;
                        $auths = $auth_arr[$s[0]] ?? [];
                        $auth_list_tag .= '<li data-code="'.$s[0].'" class="auths'.(array_key_exists($s[0], $auth_arr) ? ' act' : '').'">'.$s[1];
                        $auth_list_tag .= '<span class="auth_r'.(in_array('r', $auths) ? ' act' : '').'">읽기</span>';
                        $auth_list_tag .= '<span class="auth_w'.(in_array('w', $auths) ? ' act' : '').'">쓰기</span>';
                        $auth_list_tag .= '<span class="auth_d'.(in_array('d', $auths) ? ' act' : '').'">삭제</span>';
                        $auth_list_tag .= '</li>' . PHP_EOL;
                    }
                    $auth_list_tag .= '</ul></div>'.PHP_EOL;
                }
            }
            $auth_list_tag .= '</div>'.PHP_EOL;
            echo $auth_list_tag;
            ?>
        </td>
    </tr>
    <?php 
        }
    } 
    ?>
    </tbody>
	</table>
</div>
<div class="btn_fixed_top">
    <a href="./employee_list.php?<?=$qstr?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');