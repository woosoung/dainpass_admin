<?php
$sub_menu = "930550";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// w: write mode (등록/수정)
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$group_id = isset($_REQUEST['group_id']) ? (int)$_REQUEST['group_id'] : 0;

// qstr 생성
$qstr = '';
foreach ($_GET as $key => $value) {
    if (in_array($key, ['sst', 'sod', 'sfl', 'stx', 'page'])) {
        $qstr .= '&'.$key.'='.$value;
    }
}

$group = array();
$group_files = array();

if ($w == 'u' && $group_id) {
    // 수정 모드
    $sql = " SELECT * FROM {$g5['shop_space_group_table']} 
             WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
    $group = sql_fetch_pg($sql);
    
    if (!$group || !isset($group['group_id'])) {
        alert('존재하지 않는 공간 그룹입니다.');
    }
    
    // 도면 이미지 파일 조회
    $file_sql = " SELECT * FROM {$g5['dain_file_table']} 
                  WHERE fle_db_tbl = 'shop_space_group' 
                  AND fle_db_idx = '{$group_id}' 
                  AND fle_type = 'ssg' 
                  AND fle_dir = 'shop/shop_img' 
                  ORDER BY fle_reg_dt DESC ";
    $file_result = sql_query_pg($file_sql);
    
    if ($file_result && is_object($file_result) && isset($file_result->result)) {
        while ($file_row = sql_fetch_array_pg($file_result->result)) {
            $is_s3file_yn = is_s3file($file_row['fle_path']);
            if ($is_s3file_yn) {
                $thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:200:150:1/plain/'.$set_conf['set_s3_basicurl'].'/'.$file_row['fle_path'];
                $file_row['thumb_html'] = '<img src="'.$thumb_url.'" alt="'.$file_row['fle_name_orig'].'" style="width:200px;height:150px;border:1px solid #ddd;">';
                $file_row['download_html'] = '<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$file_row['fle_path'].'&file_name_orig='.$file_row['fle_name_orig'].'">[다운로드]</a>';
            }
            $group_files[] = $file_row;
        }
    }
    
    $html_title = '수정';
} else {
    // 등록 모드
    $w = '';
    $group['group_type'] = 'FLOOR';
    $group['is_active'] = 't';
    $group['sort_order'] = 0;
    $html_title = '등록';
}

$g5['title'] = '공간그룹 '.$html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<div class="local_desc01 local_desc">
    <p>공간 그룹(층/홀/존)을 <?php echo $html_title ?>합니다. 도면 이미지를 업로드하면 해당 그룹의 공간 유닛을 배치할 수 있습니다.</p>
</div>

<form name="fgroup" id="fgroup" action="./shop_space_group_form_update.php" method="post" enctype="multipart/form-data" onsubmit="return fgroup_submit(this);">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id ?>">
<input type="hidden" name="qstr" value="<?php echo htmlspecialchars($qstr) ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?></caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
        <col style="width:85%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="group_type">그룹 타입<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="group_type" id="group_type" class="frm_input">
                <option value="FLOOR"<?php echo $group['group_type'] == 'FLOOR' ? ' selected' : '' ?>>층 (FLOOR)</option>
                <option value="HALL"<?php echo $group['group_type'] == 'HALL' ? ' selected' : '' ?>>홀 (HALL)</option>
                <option value="ZONE"<?php echo $group['group_type'] == 'ZONE' ? ' selected' : '' ?>>존 (ZONE)</option>
            </select>
            <?php echo help("공간 그룹의 타입을 선택합니다. (층/홀/존)"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="name">그룹명<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($group['name'] ?? '') ?>" class="frm_input w-[400px]" maxlength="100" required>
            <?php echo help("예: 1층, A홀, VIP존 등"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="level_no">층 번호</label></th>
        <td>
            <input type="number" name="level_no" id="level_no" value="<?php echo htmlspecialchars($group['level_no'] ?? '') ?>" class="frm_input text-center w-[100px]">
            <?php echo help("층 번호가 있는 경우 입력합니다. (선택사항)"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="canvas_width">캔버스 크기</label></th>
        <td>
            <input type="number" name="canvas_width" id="canvas_width" value="<?php echo htmlspecialchars($group['canvas_width'] ?? '') ?>" class="frm_input text-center w-[100px]" min="1">
            <span> × </span>
            <input type="number" name="canvas_height" id="canvas_height" value="<?php echo htmlspecialchars($group['canvas_height'] ?? '') ?>" class="frm_input text-center w-[100px]" min="1">
            <?php echo help("도면 기준 캔버스 크기(px)를 입력합니다. 공간 유닛의 좌표는 이 크기를 기준으로 계산됩니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="sort_order">정렬순서</label></th>
        <td>
            <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($group['sort_order'] ?? 0) ?>" class="frm_input text-center w-[100px]">
            <?php echo help("낮은 숫자가 먼저 표시됩니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="is_active">활성화</label></th>
        <td>
            <input type="radio" name="is_active" id="is_active_y" value="t"<?php echo (!isset($group['is_active']) || $group['is_active'] == 't') ? ' checked' : '' ?>>
            <label for="is_active_y">활성</label>
            &nbsp;&nbsp;
            <input type="radio" name="is_active" id="is_active_n" value="f"<?php echo (isset($group['is_active']) && $group['is_active'] == 'f') ? ' checked' : '' ?>>
            <label for="is_active_n">비활성</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="description">설명</label></th>
        <td>
            <textarea name="description" id="description" rows="4" class="frm_input w-[100%]"><?php echo htmlspecialchars($group['description'] ?? '') ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">도면 이미지</th>
        <td>
            <?php echo help("도면 이미지를 업로드합니다. 공간 유닛 배치 시 이 이미지가 배경으로 사용됩니다."); ?>
            <input type="file" name="group_images[]" id="group_images" multiple class="frm_input" accept="image/*">
            
            <?php if ($w == 'u' && count($group_files) > 0): ?>
            <div class="mt-3">
                <strong>기존 파일</strong>
                <ul class="mt-2">
                <?php foreach ($group_files as $i => $file): ?>
                    <li class="mb-2">
                        <?php echo $file['thumb_html'] ?? '' ?>
                        <br>
                        <?php echo htmlspecialchars($file['fle_name_orig']) ?>
                        <?php echo $file['download_html'] ?? '' ?>
                        <?php if (isset($file['fle_width']) && isset($file['fle_height'])): ?>
                        (<?php echo $file['fle_width'] ?> × <?php echo $file['fle_height'] ?>)
                        <?php endif; ?>
                        <label for="file_del_<?php echo $file['fle_idx'] ?>" class="ml-2">
                            <input type="checkbox" name="file_del[]" id="file_del_<?php echo $file['fle_idx'] ?>" value="<?php echo $file['fle_idx'] ?>">
                            삭제
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./shop_space_group_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once('./js/shop_space_group_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

