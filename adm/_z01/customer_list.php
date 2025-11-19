<?php
$sub_menu = '920500';
include_once('./_common.php');

@auth_check($auth[$sub_menu],"r");

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

$sql_common = " FROM {$g5['customers_table']} ";

$where = array();
$where[] = " withdraw != 'Y' ";

$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';

// 검색 조건 처리
if ($stx) {
    switch ($sfl) {
        case 'user_id' :
            $where[] = " user_id = '".addslashes($stx)."' ";
            break;
        case 'customer_id' :
            $where[] = " customer_id = '".addslashes($stx)."' ";
            break;
        case 'name' :
            $where[] = " name LIKE '%".addslashes($stx)."%' ";
            break;
        case 'phone' :
            $where[] = " phone LIKE '%".addslashes($stx)."%' ";
            break;
        case 'email' :
            $where[] = " email LIKE '%".addslashes($stx)."%' ";
            break;
        case 'customer_key' :
            $where[] = " customer_key = '".addslashes($stx)."' ";
            break;
        case 'status' :
            $where[] = " status = '".addslashes($stx)."' ";
            break;
        default :
            $where[] = " ( user_id LIKE '%".addslashes($stx)."%' OR name LIKE '%".addslashes($stx)."%' OR email LIKE '%".addslashes($stx)."%' OR phone LIKE '%".addslashes($stx)."%' ) ";
            break;
    }
}

// 탈퇴 여부 필터
$ser_withdraw = isset($_GET['ser_withdraw']) ? trim($_GET['ser_withdraw']) : '';
if ($ser_withdraw !== '') {
    $where[] = " withdraw = '".addslashes($ser_withdraw)."' ";
}

// 상태 필터
$ser_status = isset($_GET['ser_status']) ? trim($_GET['ser_status']) : '';
if ($ser_status !== '') {
    $where[] = " status = '".addslashes($ser_status)."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);
else
    $sql_search = '';

if (!$sst) {
    $sst = "customer_id";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$rows = 20;
if (!$page) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT *
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

// 탈퇴 회원 수
$sql = " SELECT COUNT(*) AS cnt FROM {$g5['customers_table']} WHERE withdraw = 'Y' ";
$row = sql_fetch_pg($sql);
$withdraw_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 11;

// 프로필 이미지 썸네일 크기
$profile_wd = 80;
$profile_ht = 80;

$status_arr = array(
    'active' => '활성',
    'inactive' => '비활성',
    'suspended' => '정지',
    '' => '미설정'
);

$gender_arr = array(
    'MALE' => '남성',
    'FEMALE' => '여성',
    'OTHER' => '기타',
    'UNKNOWN' => '알 수 없음'
);

$g5['title'] = '고객회원관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">탈퇴회원</span><span class="ov_num"> <?php echo number_format($withdraw_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="ser_withdraw" class="sound_only">탈퇴여부</label>
<select name="ser_withdraw" id="ser_withdraw" class="cp_field" title="탈퇴여부">
    <option value="">전체</option>
    <option value="N"<?php echo get_selected($_GET['ser_withdraw']??'', "N"); ?>>정상</option>
    <option value="Y"<?php echo get_selected($_GET['ser_withdraw']??'', "Y"); ?>>탈퇴</option>
</select>

<label for="ser_status" class="sound_only">상태</label>
<select name="ser_status" id="ser_status" class="cp_field" title="상태">
    <option value="">전체상태</option>
    <option value="active"<?php echo get_selected($_GET['ser_status']??'', "active"); ?>>활성</option>
    <option value="inactive"<?php echo get_selected($_GET['ser_status']??'', "inactive"); ?>>비활성</option>
    <option value="suspended"<?php echo get_selected($_GET['ser_status']??'', "suspended"); ?>>정지</option>
</select>

<select name="sfl" id="sfl">
    <option value=""<?php echo get_selected($_GET['sfl']??'', ""); ?>>전체</option>
    <option value="user_id"<?php echo get_selected($_GET['sfl']??'', "user_id"); ?>>아이디</option>
    <option value="customer_id"<?php echo get_selected($_GET['sfl']??'', "customer_id"); ?>>고객ID</option>
    <option value="name"<?php echo get_selected($_GET['sfl']??'', "name"); ?>>이름</option>
    <option value="phone"<?php echo get_selected($_GET['sfl']??'', "phone"); ?>>연락처</option>
    <option value="email"<?php echo get_selected($_GET['sfl']??'', "email"); ?>>이메일</option>
    <option value="customer_key"<?php echo get_selected($_GET['sfl']??'', "customer_key"); ?>>구매자고유ID</option>
    <option value="status"<?php echo get_selected($_GET['sfl']??'', "status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<form name="form01" id="form01" action="./customer_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
                    <th scope="col">
                        <label for="chkall" class="sound_only">고객 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" class="td_left">번호</th>
                    <th scope="col" class="td_center !w-[<?=$profile_wd?>px]">프로필</th>
                    <th scope="col" class="td_left">아이디</th>
                    <th scope="col" class="td_left">이름</th>
                    <th scope="col" class="td_left">닉네임</th>
                    <th scope="col">연락처</th>
                    <th scope="col" class="w-[200px]">이메일</th>
                    <th scope="col">성별</th>
                    <th scope="col">상태</th>
                    <th scope="col">탈퇴여부</th>
                    <th scope="col">가입일</th>
                    <th scope="col" id="mb_list_mng">수정</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_mod = '<a href="./customer_form.php?'.$qstr.'&amp;w=u&amp;customer_id='.$row['customer_id'].'">수정</a>';

                    // 해당 고객의 프로필 이미지 가져오기
                    $fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'customers'
                                    AND fle_type = 'profile_img'
                                    AND fle_dir = 'user/profile'
                                    AND fle_db_idx = '{$row['customer_id']}'
                                ORDER BY fle_reg_dt DESC LIMIT 1 ";
                    $fres = sql_fetch_pg($fsql);
                    // 이미지파일이 존재하면 썸네일 경로 생성
                    $row['thumb_tag'] = '';
                    if(!empty($fres['fle_path'])){
                        $row['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$profile_wd.':'.$profile_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
                        $row['thumb_tag'] = '<img src="'.$row['thumb_url'].'" alt="'.get_text($row['name']).'" width="'.$profile_wd.'" class="inline-block" height="'.$profile_ht.'" style="border:1px solid #ddd;width:'.$profile_wd.'px;height:'.$profile_ht.'px;">';
                    }
                    else {
                        $row['thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$profile_wd.'" class="inline-block" height="'.$profile_ht.'" style="border:1px solid #ddd;width:'.$profile_wd.'px;height:'.$profile_ht.'px;">';
                    }

                    // 탈퇴인 경우 그레이 표현
                    $row['withdraw_class'] = ($row['withdraw'] == 'Y') ? " tr_withdraw" : "";
                    $bg = 'bg'.($i%2);

                ?>
                <tr class="<?=$bg?><?=$row['withdraw_class']?>" tr_id="<?=$row['customer_id']?>">
                    <td class="td_chk">
                        <input type="hidden" name="customer_id[<?=$i?>]" value="<?=$row['customer_id']?>" id="customer_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['name'])?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
                    </td>
                    <td class="td_customer_idx td_left font_size_8"><?=$row['customer_id']?></td><!-- 번호 -->
                    <td class="td_thumb font_size_8"><?=$row['thumb_tag']?></td><!-- 프로필이미지 -->
                    <td class="td_user_id td_left"><?=get_text($row['user_id'])?></td><!-- 아이디 -->
                    <td class="td_name td_left"><b><?=get_text($row['name'])?></b></td><!-- 이름 -->
                    <td class="td_nickname td_left"><?=get_text($row['nickname'])?></td><!-- 닉네임 -->
                    <td class="td_phone"><?=get_text($row['phone'])?></td><!-- 연락처 -->
                    <td class="td_email"><?=cut_str($row['email'],30,'...')?></td><!-- 이메일 -->
                    <td class="td_gender"><?=isset($gender_arr[$row['gender']]) ? $gender_arr[$row['gender']] : ''?></td><!-- 성별 -->
                    <td headers="list_status" class="td_status"><?=isset($status_arr[$row['status']]) ? $status_arr[$row['status']] : '미설정'?></td><!-- 상태 -->
                    <td class="td_withdraw"><?=($row['withdraw'] == 'Y') ? '탈퇴' : '정상'?></td><!-- 탈퇴여부 -->
                    <td class="td_created_at font_size_8"><?=substr($row['created_at'],0,10)?></td><!-- 가입일 -->
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
        <?php if(!@auth_check($auth[$sub_menu],"d",1)) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <?php } ?>
        <a href="./customer_form.php" id="bo_add" class="btn_01 btn">고객추가</a>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php include_once('./js/customer_list.js.php'); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');