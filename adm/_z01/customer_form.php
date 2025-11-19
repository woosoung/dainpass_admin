<?php
$sub_menu = '920500'; // 적절한 메뉴 번호로 변경 필요
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$cu = array(
    'user_id'=>'',
    'name'=>'',
    'phone'=>'',
    'email'=>'',
    'identify_key'=>'',
    'customer_key'=>'',
    'encrypted_pwd'=>'',
    'birth_date'=>'',
    'gender'=>'',
    'status'=>'',
    'nickname'=>'',
    'zipcode'=>'',
    'addr1'=>'',
    'addr2'=>'',
    'addr3'=>'',
    'agreed_marketing_email'=>'',
    'is_real_name_verified'=>'',
    'withdraw'=>'N',
    'agreed_push'=>'N',
    'apple_id'=>'',
    'google_id'=>'',
    'naver_id'=>'',
    'kakao_id'=>'',
);

// 신규등록 모드일 때도 배열 초기화
$profile_img = array(
    'profile_f_arr' => array(),
    'profile_fidxs' => array(),
    'profile_lst_idx' => 0,
    'fle_db_idx' => 0
);

if ($w == "")
{
    $html_title = '고객 추가';
}
else if ($w == "u")
{
    // 고객의 프로필 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'customers' AND fle_type = 'profile_img' AND fle_dir = 'user/profile' AND fle_db_idx = '{$customer_id}' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    $profile_wd = 150;
    $profile_ht = 150;
    $profile_img['profile_f_arr'] = array();
    $profile_img['profile_fidxs'] = array();
    $profile_img['profile_lst_idx'] = 0;
    $profile_img['fle_db_idx'] = $customer_id;
    for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$profile_wd.':'.$profile_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$profile_wd.'px;height:'.$profile_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="profile_img_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $profile_img['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($profile_img['profile_f_arr'], array('file'=>$row2['down_del']));
        @array_push($profile_img['profile_fidxs'], $row2['fle_idx']);
    }

    $sql = " SELECT * FROM customers WHERE customer_id = '$customer_id' ";
    
    $cu = sql_fetch_pg($sql);
    if (! (isset($cu['customer_id']) && $cu['customer_id']))
        alert("자료가 없습니다.");

    $html_title = $cu['name'] . " 수정";
    $cu['user_id'] = get_text($cu['user_id']);
    $cu['name'] = get_text($cu['name']);
    $cu['phone'] = get_text($cu['phone']);
    $cu['email'] = get_text($cu['email']);
    $cu['nickname'] = get_text($cu['nickname']);
    $cu['addr1'] = get_text($cu['addr1']);
    $cu['addr2'] = get_text($cu['addr2']);
    $cu['addr3'] = get_text($cu['addr3']);
    // 라디오버튼 필드들 안전하게 처리
    $cu['agreed_marketing_email'] = isset($cu['agreed_marketing_email']) ? $cu['agreed_marketing_email'] : '';
    $cu['is_real_name_verified'] = isset($cu['is_real_name_verified']) ? $cu['is_real_name_verified'] : '';
    $cu['withdraw'] = isset($cu['withdraw']) ? $cu['withdraw'] : 'N';
    $cu['agreed_push'] = isset($cu['agreed_push']) ? $cu['agreed_push'] : 'N';
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_customerfrm_basic">기본정보</a></li>
<li><a href="#anc_customerfrm_address">주소정보</a></li>
<li><a href="#anc_customerfrm_profile">프로필이미지</a></li>
<li><a href="#anc_customerfrm_etc">기타정보</a></li>
</ul>';

add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fcustomerform" action="./customer_form_update.php" onsubmit="return fcustomerformcheck(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

<?php if($is_dev_manager) { ?>
<div class="local_desc02 local_desc">
    <p>
        <span class="text-red-800">여기는 customers 테이블관련 데이터입니다.</span>
    </p>
</div>
<?php } ?>

<section id="anc_customerfrm_basic">
    <h2 class="h2_frm">기본정보</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>고객 기본정보</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($w == "u") { ?>
        <tr>
            <th scope="row"><label for="customer_id">고객 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">customer_id</span><?php } ?></th>
            <td>
                <span class="frm_customer_id"><?php echo $cu['customer_id']; ?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label for="user_id">아이디</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">user_id</span><?php } ?></th>
            <td><input type="text" name="user_id" value="<?php echo $cu['user_id']; ?>" id="user_id" size="38" required class="required frm_input" <?php echo ($w == "u") ? "readonly" : ""; ?>></td>
        </tr>
        <tr>
            <th scope="row"><label for="name">이름</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">name</span><?php } ?></th>
            <td><input type="text" name="name" value="<?php echo $cu['name']; ?>" id="name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="phone">연락처</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">phone</span><?php } ?></th>
            <td><input type="text" name="phone" value="<?php echo $cu['phone']; ?>" id="phone" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="email">이메일</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">email</span><?php } ?></th>
            <td><input type="email" name="email" value="<?php echo $cu['email']; ?>" id="email" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="customer_key">구매자 고유 아이디</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">customer_key</span><?php } ?></th>
            <td><input type="text" name="customer_key" value="<?php echo $cu['customer_key']; ?>" id="customer_key" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="encrypted_pwd">패스워드</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">encrypted_pwd</span><?php } ?></th>
            <td><input type="password" name="encrypted_pwd" value="" id="encrypted_pwd" size="38" class="frm_input" placeholder="<?php echo ($w == "u") ? "변경하지 않으려면 비워두세요" : ""; ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="birth_date">생년월일</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">birth_date</span><?php } ?></th>
            <td><input type="date" name="birth_date" value="<?php echo $cu['birth_date']; ?>" id="birth_date" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="gender">성별</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">gender</span><?php } ?></th>
            <td>
                <select name="gender" id="gender" class="frm_input">
                    <option value="">선택안함</option>
                    <option value="MALE" <?php echo ($cu['gender'] == 'MALE') ? 'selected' : ''; ?>>남성</option>
                    <option value="FEMALE" <?php echo ($cu['gender'] == 'FEMALE') ? 'selected' : ''; ?>>여성</option>
                    <option value="OTHER" <?php echo ($cu['gender'] == 'OTHER') ? 'selected' : ''; ?>>기타</option>
                    <option value="UNKNOWN" <?php echo ($cu['gender'] == 'UNKNOWN') ? 'selected' : ''; ?>>알 수 없음</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="nickname">닉네임</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">nickname</span><?php } ?></th>
            <td><input type="text" name="nickname" value="<?php echo $cu['nickname']; ?>" id="nickname" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="status">상태</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">status</span><?php } ?></th>
            <td>
                <select name="status" id="status" class="frm_input">
                    <option value="active" <?php echo (!isset($cu['status']) || $cu['status'] == '' || $cu['status'] == 'active') ? 'selected' : ''; ?>>활성</option>
                    <option value="inactive" <?php echo (isset($cu['status']) && $cu['status'] == 'inactive') ? 'selected' : ''; ?>>비활성</option>
                    <option value="suspended" <?php echo (isset($cu['status']) && $cu['status'] == 'suspended') ? 'selected' : ''; ?>>정지</option>
                </select>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_customerfrm_address">
    <h2 class="h2_frm">주소정보</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>고객 주소정보</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">주소<strong class="sound_only">필수</strong></th>
            <td class="td_addr_line" style="line-height:280%;">
                <?php echo help("주소가 명확하지 않은 경우 [주소검색]을 통해 정확히 입력해 주세요."); ?>
                <label for="zipcode" class="sound_only">우편번호</label>
                <input type="text" name="zipcode" value="<?php echo $cu['zipcode']; ?>" id="zipcode" readonly class="frm_input readonly" maxlength="6" style="width:65px;">
                <button type="button" class="btn_frmline" onclick="win_zip('fcustomerform', 'zipcode', 'addr1', 'addr2', 'addr3');">주소 검색</button>
                <br>
                <input type="text" name="addr1" value="<?php echo $cu['addr1']; ?>" id="addr1" readonly class="w-[400px] frm_input readonly">
                <label for="addr1">기본주소</label><br>
                <input type="text" name="addr2" value="<?php echo $cu['addr2']; ?>" id="addr2" class="w-[400px] frm_input">
                <label for="addr2">상세주소</label>
                <br>
                <input type="text" name="addr3" value="<?php echo $cu['addr3']; ?>" id="addr3" class="w-[400px] frm_input">
                <label for="addr3">참고항목</label>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_customerfrm_profile">
    <h2 class="h2_frm">프로필이미지</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>프로필이미지 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row"><label for="customer_profile_img">프로필 이미지</label></th>
                <td colspan="3">
                    <?php echo help("고객의 프로필 이미지를 관리합니다. (최대 1개까지 업로드 가능합니다.)"); ?>
                    <div>
                        <input type="file" id="customer_profile_img" name="customer_profile_img[]" multiple class="multifile">
                        <?php
                        if(isset($profile_img['profile_f_arr']) && is_array($profile_img['profile_f_arr']) && count($profile_img['profile_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($profile_img['profile_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$profile_img['profile_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_customerfrm_etc">
    <h2 class="h2_frm">기타정보</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>고객 기타정보</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="identify_key">본인인증 키</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">identify_key</span><?php } ?></th>
            <td><input type="text" name="identify_key" value="<?php echo $cu['identify_key']; ?>" id="identify_key" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="agreed_marketing_email">정보메일수신동의</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">agreed_marketing_email</span><?php } ?></th>
            <td>
                <label for="agreed_marketing_email_y"><input type="radio" name="agreed_marketing_email" id="agreed_marketing_email_y" value="Y" <?php echo (isset($cu['agreed_marketing_email']) && $cu['agreed_marketing_email'] == 'Y') ? 'checked' : ''; ?>> 동의</label>
                <label for="agreed_marketing_email_n"><input type="radio" name="agreed_marketing_email" id="agreed_marketing_email_n" value="N" <?php echo (isset($cu['agreed_marketing_email']) && $cu['agreed_marketing_email'] == 'N') || $cu['agreed_marketing_email'] == '' ? 'checked' : ''; ?>> 비동의</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="is_real_name_verified">실명 인증 여부</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">is_real_name_verified</span><?php } ?></th>
            <td>
                <label for="is_real_name_verified_y"><input type="radio" name="is_real_name_verified" id="is_real_name_verified_y" value="Y" <?php echo (isset($cu['is_real_name_verified']) && $cu['is_real_name_verified'] == 'Y') ? 'checked' : ''; ?>> 인증됨</label>
                <label for="is_real_name_verified_n"><input type="radio" name="is_real_name_verified" id="is_real_name_verified_n" value="N" <?php echo (isset($cu['is_real_name_verified']) && $cu['is_real_name_verified'] == 'N') || $cu['is_real_name_verified'] == '' ? 'checked' : ''; ?>> 미인증</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="withdraw">탈퇴여부</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">withdraw</span><?php } ?></th>
            <td>
                <label for="withdraw_y"><input type="radio" name="withdraw" id="withdraw_y" value="Y" <?php echo (isset($cu['withdraw']) && $cu['withdraw'] == 'Y') ? 'checked' : ''; ?>> 탈퇴</label>
                <label for="withdraw_n"><input type="radio" name="withdraw" id="withdraw_n" value="N" <?php echo (!isset($cu['withdraw']) || $cu['withdraw'] == 'N' || $cu['withdraw'] == '') ? 'checked' : ''; ?>> 정상</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="agreed_push">알림동의</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">agreed_push</span><?php } ?></th>
            <td>
                <label for="agreed_push_y"><input type="radio" name="agreed_push" id="agreed_push_y" value="Y" <?php echo (isset($cu['agreed_push']) && $cu['agreed_push'] == 'Y') ? 'checked' : ''; ?>> 동의</label>
                <label for="agreed_push_n"><input type="radio" name="agreed_push" id="agreed_push_n" value="N" <?php echo (!isset($cu['agreed_push']) || $cu['agreed_push'] == 'N' || $cu['agreed_push'] == '') ? 'checked' : ''; ?>> 비동의</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="apple_id">애플 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">apple_id</span><?php } ?></th>
            <td><input type="text" name="apple_id" value="<?php echo $cu['apple_id']; ?>" id="apple_id" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="google_id">구글 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">google_id</span><?php } ?></th>
            <td><input type="text" name="google_id" value="<?php echo $cu['google_id']; ?>" id="google_id" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="naver_id">네이버 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">naver_id</span><?php } ?></th>
            <td><input type="text" name="naver_id" value="<?php echo $cu['naver_id']; ?>" id="naver_id" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="kakao_id">카카오톡 ID</label><?php if($is_dev_manager) { ?><br><span class="text-red-800">kakao_id</span><?php } ?></th>
            <td><input type="text" name="kakao_id" value="<?php echo $cu['kakao_id']; ?>" id="kakao_id" size="38" class="frm_input"></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./customer_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<?php include_once('./js/customer_form.js.php'); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');