<?php
$sub_menu = "930600";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (mb_level >= 6 OR (mb_level < 6 AND mb_2 = 'Y')) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        if (!empty($mb_1_value) && $mb_1_value !== '0') {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    alert('접근 권한이 없습니다.');
}

// w: write mode (등록/수정)
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$unit_id = isset($_REQUEST['unit_id']) ? (int)$_REQUEST['unit_id'] : 0;
$group_id = isset($_REQUEST['group_id']) ? (int)$_REQUEST['group_id'] : 0;

// qstr 생성
$qstr = '';
foreach ($_GET as $key => $value) {
    if (in_array($key, ['sst', 'sod', 'sfl', 'stx', 'sfl2', 'sfl3', 'group_id', 'page'])) {
        $qstr .= '&'.$key.'='.$value;
    }
}

$unit = array();
$unit_files = array();

if ($w == 'u' && $unit_id) {
    // 수정 모드
    $sql = " SELECT * FROM {$g5['shop_space_unit_table']} 
             WHERE unit_id = {$unit_id} AND shop_id = {$shop_id} ";
    $unit = sql_fetch_pg($sql);
    
    if (!$unit || !isset($unit['unit_id'])) {
        alert('존재하지 않는 공간 유닛입니다.');
    }
    
    // 파일 조회
    $file_sql = " SELECT * FROM {$g5['dain_file_table']} 
                  WHERE fle_db_tbl = 'shop_space_unit' 
                  AND fle_db_idx = '{$unit_id}' 
                  AND fle_type = 'ssu' 
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
            $unit_files[] = $file_row;
        }
    }
    
    $html_title = '수정';
} else {
    // 등록 모드
    $w = '';
    $unit['unit_type'] = 'ROOM';
    $unit['is_active'] = 't';
    $unit['sort_order'] = 0;
    $unit['capacity'] = 1;
    if ($group_id > 0) {
        $unit['group_id'] = $group_id;
    }
    $html_title = '등록';
}

// 공간 그룹 목록
$group_list_sql = " SELECT group_id, name, group_type FROM {$g5['shop_space_group_table']} 
                    WHERE shop_id = {$shop_id} AND is_active = 't'
                    ORDER BY sort_order, name ";
$group_list_result = sql_query_pg($group_list_sql);
$group_list = array();
if ($group_list_result && is_object($group_list_result) && isset($group_list_result->result)) {
    while ($g_row = sql_fetch_array_pg($group_list_result->result)) {
        $group_list[] = $g_row;
    }
}

// 서비스 목록 (shop_services)
$service_list_sql = " SELECT service_id, service_name, service_duration, service_price 
                     FROM {$g5['shop_services_table']} 
                     WHERE shop_id = {$shop_id} AND is_active = 't'
                     ORDER BY sort_order, service_name ";
$service_list_result = sql_query_pg($service_list_sql);
$service_list = array();
if ($service_list_result && is_object($service_list_result) && isset($service_list_result->result)) {
    while ($s_row = sql_fetch_array_pg($service_list_result->result)) {
        $service_list[] = $s_row;
    }
}

$g5['title'] = '공간유닛 '.$html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<div class="local_desc01 local_desc">
    <p>공간 유닛(룸/테이블/좌석/가상공간)을 <?php echo $html_title ?>합니다.</p>
</div>

<form name="funit" id="funit" action="./shop_space_unit_form_update.php" method="post" enctype="multipart/form-data" onsubmit="return funit_submit(this);">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id ?>">
<input type="hidden" name="unit_id" value="<?php echo $unit_id ?>">
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
        <th scope="row"><label for="group_id">공간 그룹<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="group_id" id="group_id" class="frm_input" required>
                <option value="">선택</option>
                <?php foreach ($group_list as $g): ?>
                <option value="<?php echo $g['group_id'] ?>"<?php echo (isset($unit['group_id']) && $unit['group_id'] == $g['group_id']) ? ' selected' : '' ?>>
                    <?php echo htmlspecialchars($g['name']) ?> (<?php echo $g['group_type'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <?php echo help("이 유닛이 속할 공간 그룹을 선택합니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="unit_type">유닛 타입<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="unit_type" id="unit_type" class="frm_input">
                <option value="ROOM"<?php echo $unit['unit_type'] == 'ROOM' ? ' selected' : '' ?>>룸 (ROOM)</option>
                <option value="TABLE"<?php echo $unit['unit_type'] == 'TABLE' ? ' selected' : '' ?>>테이블 (TABLE)</option>
                <option value="SEAT"<?php echo $unit['unit_type'] == 'SEAT' ? ' selected' : '' ?>>좌석 (SEAT)</option>
                <option value="VIRTUAL"<?php echo $unit['unit_type'] == 'VIRTUAL' ? ' selected' : '' ?>>가상 (VIRTUAL)</option>
            </select>
            <?php echo help("공간 유닛의 타입을 선택합니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="name">유닛명<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($unit['name'] ?? '') ?>" class="frm_input w-[400px]" maxlength="100" required>
            <?php echo help("예: VIP룸, A-1 테이블, 1-A 좌석 등"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="code">유닛 코드</label></th>
        <td>
            <input type="text" name="code" id="code" value="<?php echo htmlspecialchars($unit['code'] ?? '') ?>" class="frm_input w-[200px]" maxlength="50">
            <?php echo help("내부 관리용 코드 (예: R101, T-03 등)"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="capacity">수용 인원<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="number" name="capacity" id="capacity" value="<?php echo htmlspecialchars($unit['capacity'] ?? 1) ?>" class="frm_input text-center w-[100px]" min="1" required>
            <?php echo help("이 공간의 최대 수용 인원"); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="service_id">연결 서비스</label></th>
        <td>
            <select name="service_id" id="service_id" class="frm_input">
                <option value="">없음</option>
                <?php foreach ($service_list as $s): ?>
                <option value="<?php echo $s['service_id'] ?>"<?php echo (isset($unit['service_id']) && $unit['service_id'] == $s['service_id']) ? ' selected' : '' ?>>
                    <?php echo htmlspecialchars($s['service_name']) ?> (<?php echo number_format($s['service_price']) ?>원 / <?php echo $s['service_duration'] ?>분)
                </option>
                <?php endforeach; ?>
            </select>
            <?php echo help("이 공간에 특정 서비스를 연결할 경우 선택합니다. (선택사항)"); ?>
        </td>
    </tr>
    <tr id="tr_seat_info" style="display:none;">
        <th scope="row">좌석 정보</th>
        <td>
            <label for="seat_row">열</label>
            <input type="text" name="seat_row" id="seat_row" value="<?php echo htmlspecialchars($unit['seat_row'] ?? '') ?>" class="frm_input text-center w-[80px]" maxlength="10">
            
            <label for="seat_number" class="ml-3">번호</label>
            <input type="text" name="seat_number" id="seat_number" value="<?php echo htmlspecialchars($unit['seat_number'] ?? '') ?>" class="frm_input text-center w-[80px]" maxlength="10">
            
            <?php echo help("극장형 좌석의 경우 행(A, B, C...)과 번호를 입력합니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="description">설명</label></th>
        <td>
            <textarea name="description" id="description" rows="4" class="frm_input w-[100%]"><?php echo htmlspecialchars($unit['description'] ?? '') ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">좌표 정보</th>
        <td>
            <div class="mb-2">
                <?php echo help("x좌표, y좌표에 대한 정보는 '도면편집'에서 언제든 정밀하게 수정할 수 있으니 적당한 값으로 입력하거나, 기본값이 0.00 설정 그대로 두셔도 됩니다."); ?>
                <label for="pos_x">X 좌표</label>
                <input type="number" name="pos_x" id="pos_x" value="<?php echo htmlspecialchars($unit['pos_x'] ?? '0.00') ?>" class="frm_input text-center w-[100px]" step="0.01">
                
                <label for="pos_y" class="ml-3">Y 좌표</label>
                <input type="number" name="pos_y" id="pos_y" value="<?php echo htmlspecialchars($unit['pos_y'] ?? '0.00') ?>" class="frm_input text-center w-[100px]" step="0.01">
            </div>
            <div class="mb-2">
                <?php echo help("가로/세로(너비/높이)에 대한 정보는 '도면편집'에서 언제든 정밀하게 수정할 수 있으니 적당한 값으로 입력하거나, 기본값이 50.00 설정 그대로 두셔도 됩니다."); ?>
                <label for="width">가로</label>
                <input type="number" name="width" id="width" value="<?php echo htmlspecialchars($unit['width'] ?? '50.00') ?>" class="frm_input text-center w-[100px]" step="0.01">
                
                <label for="height" class="ml-3">세로</label>
                <input type="number" name="height" id="height" value="<?php echo htmlspecialchars($unit['height'] ?? '50.00') ?>" class="frm_input text-center w-[100px]" step="0.01">
            </div>
            <div>
                <label for="rotation_deg">회전 각도 (degree)</label>
                <input type="number" name="rotation_deg" id="rotation_deg" value="<?php echo htmlspecialchars($unit['rotation_deg'] ?? '') ?>" class="frm_input text-center w-[100px]" step="0.01">
            </div>
            <?php echo help("도면 상의 위치와 크기를 수동으로 입력하거나, 도면 에디터를 통해 자동으로 저장됩니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="sort_order">정렬순서</label></th>
        <td>
            <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($unit['sort_order'] ?? 0) ?>" class="frm_input text-center w-[100px]">
            <?php echo help("낮은 숫자가 먼저 표시됩니다."); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="is_active">활성화</label></th>
        <td>
            <input type="radio" name="is_active" id="is_active_y" value="t"<?php echo (!isset($unit['is_active']) || $unit['is_active'] == 't') ? ' checked' : '' ?>>
            <label for="is_active_y">활성</label>
            &nbsp;&nbsp;
            <input type="radio" name="is_active" id="is_active_n" value="f"<?php echo (isset($unit['is_active']) && $unit['is_active'] == 'f') ? ' checked' : '' ?>>
            <label for="is_active_n">비활성</label>
        </td>
    </tr>
    <tr>
        <th scope="row">유닛 이미지</th>
        <td>
            <?php echo help("공간 유닛의 이미지를 업로드합니다. (룸 전경, 테이블 아이콘 등)"); ?>
            <input type="file" name="unit_images[]" id="unit_images" multiple class="frm_input" accept="image/*">
            
            <?php if ($w == 'u' && count($unit_files) > 0): ?>
            <div class="mt-3">
                <strong>기존 파일</strong>
                <ul class="mt-2">
                <?php foreach ($unit_files as $i => $file): ?>
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
    <a href="./shop_space_unit_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once('./js/shop_space_unit_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

