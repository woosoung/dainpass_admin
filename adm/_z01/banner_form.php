<?php
$sub_menu = '920650';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$bng_id = isset($_GET['bng_id']) ? (int)$_GET['bng_id'] : 0;
$bg = array(
    'bng_code'=>'',
    'bng_start_dt'=>'',
    'bng_end_dt'=>'',
    'bng_name'=>'',
    'bng_desc'=>'',
    'bng_status'=>'ok',
);

// 배너그룹 이미지 초기화 (PC용)
$bng_img = array(
    'bng_img_f_arr' => array(),
    'bng_img_fidxs' => array(),
    'bng_img_lst_idx' => 0,
    'fle_db_idx' => 0
);

// 배너그룹 이미지 초기화 (모바일용)
$bng_mo_img = array(
    'bng_mo_img_f_arr' => array(),
    'bng_mo_img_fidxs' => array(),
    'bng_mo_img_lst_idx' => 0,
    'fle_db_idx' => 0
);

if ($w == "")
{
    $html_title = '배너그룹 추가';
}
else if ($w == "u")
{
    // 배너그룹의 PC용 이미지
    $sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'banner_group' AND fle_type = 'bng_img' AND fle_dir = 'plt/banner' AND fle_db_idx = '{$bng_id}' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    $bng_img_wd = 150;
    $bng_img_ht = 150;
    $bng_img['bng_img_f_arr'] = array();
    $bng_img['bng_img_fidxs'] = array();
    $bng_img['bng_img_lst_idx'] = 0;
    $bng_img['fle_db_idx'] = $bng_id;
    for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
        $is_s3file_yn = is_s3file($row2['fle_path']);
        $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$bng_img_wd.':'.$bng_img_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
        $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$bng_img_wd.'px;height:'.$bng_img_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
        $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_pc_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="bng_img_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_pc_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
        $bng_img['fle_db_idx'] = $row2['fle_db_idx'];
        @array_push($bng_img['bng_img_f_arr'], array('file'=>$row2['down_del']));
        @array_push($bng_img['bng_img_fidxs'], $row2['fle_idx']);
    }

    // 배너그룹의 모바일용 이미지
    $sql_mo = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'banner_group' AND fle_type = 'bng_mo_img' AND fle_dir = 'plt/banner' AND fle_db_idx = '{$bng_id}' ORDER BY fle_reg_dt DESC ";
    $rs_mo = sql_query_pg($sql_mo);
    $bng_mo_img['bng_mo_img_f_arr'] = array();
    $bng_mo_img['bng_mo_img_fidxs'] = array();
    $bng_mo_img['bng_mo_img_lst_idx'] = 0;
    $bng_mo_img['fle_db_idx'] = $bng_id;
    for($i=0;$row2_mo=sql_fetch_array_pg($rs_mo->result);$i++) {
        $is_s3file_yn_mo = is_s3file($row2_mo['fle_path']);
        $row2_mo['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$bng_img_wd.':'.$bng_img_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2_mo['fle_path'];
        $row2_mo['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2_mo['thumb_url'].'" alt="'.$row2_mo['fle_name_orig'].'" style="width:'.$bng_img_wd.'px;height:'.$bng_img_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2_mo['fle_width'].' X '.$row2_mo['fle_height'].'</span>'.PHP_EOL;
        $row2_mo['down_del'] = ($is_s3file_yn_mo) ? $row2_mo['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2_mo['fle_path'].'&file_name_orig='.$row2_mo['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2_mo['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_mo_'.$row2_mo['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="bng_mo_img_'.$row2_mo['fle_db_idx'].'_del['.$row2_mo['fle_idx'].']" id="del_mo_'.$row2_mo['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
        $row2_mo['down_del'] .= ($is_dev_manager && $is_s3file_yn_mo) ? 
        '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql_mo).' LIMIT 1;</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2_mo['fle_path'].'</span></span>
        <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2_mo['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
        $row2_mo['down_del'] .= ($is_s3file_yn_mo) ? '<br>'.$row2_mo['thumb'].PHP_EOL : ''.PHP_EOL;
        $bng_mo_img['fle_db_idx'] = $row2_mo['fle_db_idx'];
        @array_push($bng_mo_img['bng_mo_img_f_arr'], array('file'=>$row2_mo['down_del']));
        @array_push($bng_mo_img['bng_mo_img_fidxs'], $row2_mo['fle_idx']);
    }

    $sql = " SELECT * FROM banner_group WHERE bng_id = '$bng_id' ";
    
    $bg = sql_fetch_pg($sql);
    if (! (isset($bg['bng_id']) && $bg['bng_id']))
        alert("자료가 없습니다.");

    $html_title = $bg['bng_name'] . " 수정";
    $bg['bng_code'] = get_text($bg['bng_code']);
    $bg['bng_name'] = get_text($bg['bng_name']);
    $bg['bng_desc'] = get_text($bg['bng_desc']);
    $bg['bng_status'] = isset($bg['bng_status']) ? $bg['bng_status'] : 'ok';
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_banner_group">배너그룹 정보</a></li>
<li><a href="#anc_banner_list">배너 목록</a></li>
</ul>';

add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<form name="fbannerform" action="./banner_form_update.php" onsubmit="return fbannerformcheck(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="bng_id" value="<?php echo $bng_id; ?>">

<section id="anc_banner_group">
    <h2 class="h2_frm">배너그룹 정보<?php if ($w == "") { ?> <span style="font-size: 12px; color: #f00;">(우선 배너그룹 정보를 등록하고 난 후, 배너 정보를 등록할 수 있습니다.)</span> <?php } ?></h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>배너그룹 정보</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($w == "u") { ?>
        <tr>
            <th scope="row"><label for="bng_id">배너그룹 ID</label></th>
            <td colspan="3">
                <span class="frm_bng_id"><?php echo $bg['bng_id']; ?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label for="bng_code">배너그룹 코드</label><strong class="sound_only">필수</strong></th>
            <td colspan="3">
                <?php echo help("영문으로 시작하고 영문, 숫자, 언더스코어(_)만 입력 가능합니다."); ?>
                <input type="text" name="bng_code" value="<?php echo $bg['bng_code']; ?>" id="bng_code" size="38" required class="required frm_input" <?php echo ($w == "u") ? "readonly" : ""; ?>>
                <span id="bng_code_check_result" style="margin-left: 10px;"></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bng_name">배너그룹명</label><strong class="sound_only">필수</strong></th>
            <td colspan="3"><input type="text" name="bng_name" value="<?php echo $bg['bng_name']; ?>" id="bng_name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="bng_desc">배너그룹 설명</label></th>
            <td colspan="3"><textarea name="bng_desc" id="bng_desc" rows="5" class="w-full frm_input"><?php echo $bg['bng_desc']; ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="bng_start_dt">시작일시</label></th>
            <td>
                <input type="datetime-local" name="bng_start_dt" value="<?php echo !empty($bg['bng_start_dt']) ? date('Y-m-d\TH:i', strtotime($bg['bng_start_dt'])) : ''; ?>" id="bng_start_dt" class="frm_input">
                <?php echo help("값이 없으면 바로 표시됩니다."); ?>
            </td>
            <th scope="row"><label for="bng_end_dt">종료일시</label></th>
            <td>
                <input type="datetime-local" name="bng_end_dt" value="<?php echo !empty($bg['bng_end_dt']) ? date('Y-m-d\TH:i', strtotime($bg['bng_end_dt'])) : ''; ?>" id="bng_end_dt" class="frm_input">
                <?php echo help("값이 없으면 무제한 표시됩니다."); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bng_status">상태</label><strong class="sound_only">필수</strong></th>
            <td colspan="3">
                <select name="bng_status" id="bng_status" class="frm_input">
                    <option value="ok" <?php echo (!isset($bg['bng_status']) || $bg['bng_status'] == '' || $bg['bng_status'] == 'ok') ? 'selected' : ''; ?>>정상</option>
                    <option value="pending" <?php echo (isset($bg['bng_status']) && $bg['bng_status'] == 'pending') ? 'selected' : ''; ?>>대기</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="banner_group_img">배너그룹 이미지 (PC)</label></th>
            <td colspan="3">
                <?php echo help("웹사이트(PC)에서 배너 그룹의 위치를 파악할 수 있는 이미지를 관리합니다. (최대 1개까지 업로드 가능합니다.)"); ?>
                <div>
                    <input type="file" id="banner_group_img" name="banner_group_img[]" multiple class="multifile">
                    <?php
                    if(isset($bng_img['bng_img_f_arr']) && is_array($bng_img['bng_img_f_arr']) && count($bng_img['bng_img_f_arr'])){
                        echo '<ul>'.PHP_EOL;
                        for($i=0;$i<count($bng_img['bng_img_f_arr']);$i++) {
                            echo "<li>[".($i+1).']'.$bng_img['bng_img_f_arr'][$i]['file']."</li>".PHP_EOL;
                        }
                        echo '</ul>'.PHP_EOL;
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="banner_group_mo_img">배너그룹 이미지 (Mobile)</label></th>
            <td colspan="3">
                <?php echo help("모바일 앱에서 배너 그룹의 위치를 파악할 수 있는 이미지를 관리합니다. (최대 1개까지 업로드 가능합니다.)"); ?>
                <div>
                    <input type="file" id="banner_group_mo_img" name="banner_group_mo_img[]" multiple class="multifile">
                    <?php
                    if(isset($bng_mo_img['bng_mo_img_f_arr']) && is_array($bng_mo_img['bng_mo_img_f_arr']) && count($bng_mo_img['bng_mo_img_f_arr'])){
                        echo '<ul>'.PHP_EOL;
                        for($i=0;$i<count($bng_mo_img['bng_mo_img_f_arr']);$i++) {
                            echo "<li>[".($i+1).']'.$bng_mo_img['bng_mo_img_f_arr'][$i]['file']."</li>".PHP_EOL;
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

<?php if ($w == "u" && $bng_id) { ?>
<section id="anc_banner_list">
    <h2 class="h2_frm">배너 목록</h2>
    <?php echo $pg_anchor; ?>
    
    <div class="tbl_frm01 tbl_wrap" style="margin-bottom: 30px;">
        <h3 id="banner_form_title">배너 추가</h3>
        <input type="hidden" id="editing_bnr_id" value="">
        
        <!-- 섬네일 표시 영역 -->
        <div id="banner_preview_area" style="display: none; margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <strong style="display: block; margin-bottom: 10px;">현재 수정 중인 배너</strong>
            <div id="banner_preview_content" style="display: flex; gap: 15px; align-items: flex-start;">
                <!-- 기존 이미지 섬네일 (PC용) -->
                <div id="banner_img_preview" style="display: none;">
                    <div style="margin-bottom: 5px; font-size: 12px; color: #666; font-weight: bold;">PC 이미지</div>
                    <img id="banner_img_thumb" src="" alt="배너 이미지 (PC)" style="width: 120px; height: 80px; border: 1px solid #ddd; object-fit: cover;">
                </div>
                <!-- 기존 이미지 섬네일 (모바일용) -->
                <div id="banner_mo_img_preview" style="display: none;">
                    <div style="margin-bottom: 5px; font-size: 12px; color: #666; font-weight: bold;">Mobile 이미지</div>
                    <img id="banner_mo_img_thumb" src="" alt="배너 이미지 (Mobile)" style="width: 120px; height: 80px; border: 1px solid #ddd; object-fit: cover;">
                </div>
                <!-- 유튜브 썸네일 -->
                <div id="banner_youtube_preview" style="display: none;">
                    <div style="margin-bottom: 5px; font-size: 12px; color: #666;">유튜브 영상</div>
                    <img id="banner_youtube_thumb" src="" alt="유튜브 썸네일" style="width: 120px; height: 80px; border: 1px solid #ddd; object-fit: cover;">
                </div>
                <!-- 섬네일이 없을 때 -->
                <div id="banner_no_preview" style="display: none;">
                    <div style="width: 120px; height: 80px; border: 1px solid #ddd; background-color: #eee; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">
                        이미지 없음
                    </div>
                </div>
            </div>
        </div>
        
        <table>
        <caption>배너 정보 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bnr_shop_id">가맹점</label></th>
            <td colspan="3">
                <?php echo help("가맹점을 선택하지 않으면 플랫폼 배너입니다. 특정 가맹점을 선택하면 해당 가맹점의 배너입니다."); ?>
                <input type="hidden" name="bnr_shop_id" id="bnr_shop_id" value="0">
                <input type="text" name="bnr_shop_name" id="bnr_shop_name" value="" readonly class="frm_input" style="width: 200px;" placeholder="가맹점을 선택하세요">
                <button type="button" onclick="open_shop_popup();" class="btn_01 btn">가맹점선택</button>
                <button type="button" onclick="clear_shop_selection();" class="btn_02 btn">초기화</button>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_name">배너명</label></th>
            <td colspan="3"><input type="text" name="bnr_name" id="bnr_name" size="38" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_desc">배너 설명</label></th>
            <td colspan="3"><textarea name="bnr_desc" id="bnr_desc" rows="3" class="w-full frm_input"></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_link">링크 주소 (PC)</label></th>
            <td>
                <input type="text" name="bnr_link" id="bnr_link" size="50" class="frm_input" placeholder="https://example.com">
                <?php echo help("웹(PC)에서 사용할 일반 URL 주소입니다."); ?>
            </td>
            <th scope="row"><label for="bnr_target">링크 타겟</label></th>
            <td>
                <select name="bnr_target" id="bnr_target" class="frm_input">
                    <option value="_self">현재창</option>
                    <option value="_blank">새창</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_mo_link">링크 주소 (Mobile)</label></th>
            <td colspan="3">
                <input type="text" name="bnr_mo_link" id="bnr_mo_link" size="50" class="frm_input" placeholder="dainpass://shop/detail?id=123">
                <?php echo help("모바일 앱에서 사용할 DeepLink 형식의 주소입니다. (예: dainpass://shop/detail?id=123)"); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_youtube">유튜브 영상 URL</label></th>
            <td colspan="3">
                <input type="text" name="bnr_youtube" id="bnr_youtube" size="50" class="frm_input" placeholder="https://www.youtube.com/watch?v=...">
                <?php echo help("이미지 또는 유튜브 영상 중 하나는 필수입니다."); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="banner_img">배너 이미지 (PC)</label></th>
            <td colspan="3">
                <input type="file" id="banner_img" name="banner_img[]" accept="image/*" class="multifile">
                <?php echo help("PC에서 사용할 배너 이미지를 업로드합니다. 이미지 또는 유튜브 영상 중 하나는 필수입니다."); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="banner_mo_img">배너 이미지 (Mobile)</label></th>
            <td colspan="3">
                <input type="file" id="banner_mo_img" name="banner_mo_img[]" accept="image/*" class="multifile">
                <?php echo help("모바일 앱에서 사용할 배너 이미지를 업로드합니다. (선택사항)"); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_start_dt">시작일시</label></th>
            <td>
                <input type="datetime-local" name="bnr_start_dt" id="bnr_start_dt" class="frm_input">
            </td>
            <th scope="row"><label for="bnr_end_dt">종료일시</label></th>
            <td>
                <input type="datetime-local" name="bnr_end_dt" id="bnr_end_dt" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bnr_status">상태</label></th>
            <td colspan="3">
                <select name="bnr_status" id="bnr_status" class="frm_input">
                    <option value="ok" selected>정상</option>
                    <option value="pending">대기</option>
                </select>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
    <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
        <button type="button" id="btn_banner_cancel" class="btn_02 btn" style="display: none; margin-left: 10px;">수정 취소</button>
        <button type="button" id="btn_banner_add" class="btn_01 btn">배너 추가</button>
    </div>

    <div class="tbl_head01 tbl_wrap">
        <h3>등록된 배너 목록 <span style="font-size: 12px; color: #f00;">(배너를 항목을 상하방향으로 드래그&드롭해서 순서를 조정할 수 있습니다.)</span></h3>
        <style>
        /* Sortable placeholder 스타일 */
        .tr-placeholder {
            height: 50px;
            background-color: #f0f0f0;
            border: 2px dashed #ccc;
        }
        .tr-placeholder td {
            border: none;
        }
        /* 드래그 중인 요소의 너비 및 배경색 유지 */
        #banner_list_table.ui-sortable-helper {
            display: table;
            opacity: 1 !important;
        }
        #banner_list_table.ui-sortable-helper tr {
            display: table-row;
            opacity: 1 !important;
            background-color: inherit !important;
        }
        #banner_list_table.ui-sortable-helper td {
            display: table-cell;
            opacity: 1 !important;
            background-color: inherit !important;
        }
        /* 드래그 중인 행의 배경색 유지 */
        #banner_list tr.ui-sortable-helper {
            opacity: 1 !important;
        }
        #banner_list tr.ui-sortable-helper td {
            background-color: inherit !important;
            opacity: 1 !important;
        }
        </style>
        <table id="banner_list_table" class="table table-bordered table-condensed">
            <caption>배너 목록</caption>
            <thead>
                <tr class="success">
                    <th scope="col" style="width: 50px;">순서</th>
                    <th scope="col" style="width: 120px;">PC 이미지</th>
                    <th scope="col" style="width: 120px;">Mobile 이미지</th>
                    <th scope="col" style="width: 120px;">유튜브</th>
                    <th scope="col">배너명</th>
                    <th scope="col">웹링크</th>
                    <th scope="col">모바일링크</th>
                    <th scope="col">상태</th>
                    <th scope="col">시작일시</th>
                    <th scope="col">종료일시</th>
                    <th scope="col" style="width: 100px;">관리</th>
                </tr>
            </thead>
            <tbody id="banner_list">
                <?php
                // 배너 목록 조회 (삭제된 배너 제외)
                $banner_sql = " SELECT * FROM banner WHERE bng_id = '{$bng_id}' AND (bnr_status IS NULL OR bnr_status != 'del') ORDER BY bnr_sort ASC, bnr_id ASC ";
                $banner_result = sql_query_pg($banner_sql);
                $thumb_wd = 120;
                $thumb_ht = 80;
                
                $banner_count = 0;
                while($banner_row = sql_fetch_array_pg($banner_result->result)){
                    $banner_count++;
                    
                    // 배너 이미지 조회 (PC용)
                    $banner_img_sql = " SELECT * FROM {$g5['dain_file_table']} 
                                        WHERE fle_db_tbl = 'banner' 
                                          AND fle_type = 'banner_img' 
                                          AND fle_dir = 'plt/banner' 
                                          AND fle_db_idx = '{$banner_row['bnr_id']}' 
                                        ORDER BY fle_reg_dt DESC LIMIT 1 ";
                    $banner_img_res = sql_fetch_pg($banner_img_sql);
                    
                    // 배너 이미지 조회 (모바일용)
                    $banner_mo_img_sql = " SELECT * FROM {$g5['dain_file_table']} 
                                           WHERE fle_db_tbl = 'banner' 
                                             AND fle_type = 'banner_mo_img' 
                                             AND fle_dir = 'plt/banner' 
                                             AND fle_db_idx = '{$banner_row['bnr_id']}' 
                                           ORDER BY fle_reg_dt DESC LIMIT 1 ";
                    $banner_mo_img_res = sql_fetch_pg($banner_mo_img_sql);
                    
                    // PC용 이미지 HTML 생성
                    $pc_thumbnail_html = '';
                    if(!empty($banner_img_res['fle_path'])){
                        $thumb_url = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$banner_img_res['fle_path'];
                        $pc_thumbnail_html = '<div style="position:relative;display:inline-block;"><img src="'.$thumb_url.'" alt="배너 이미지 (PC)" style="width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;border:1px solid #ddd;display:block;"><i class="fa fa-image" style="position:absolute;top:2px;right:2px;background-color:rgba(0,102,204,0.8);color:#fff;padding:2px 4px;border-radius:3px;font-size:10px;" title="PC 이미지"></i><span style="position:absolute;bottom:2px;left:2px;background-color:rgba(0,102,204,0.8);color:#fff;padding:1px 3px;font-size:9px;border-radius:2px;">PC</span></div>';
                    } else {
                        $pc_thumbnail_html = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                    }
                    
                    // 모바일용 이미지 HTML 생성
                    $mo_thumbnail_html = '';
                    if(!empty($banner_mo_img_res['fle_path'])){
                        $thumb_url_mo = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$banner_mo_img_res['fle_path'];
                        $mo_thumbnail_html = '<div style="position:relative;display:inline-block;"><img src="'.$thumb_url_mo.'" alt="배너 이미지 (Mobile)" style="width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;border:1px solid #ddd;display:block;"><i class="fa fa-image" style="position:absolute;top:2px;right:2px;background-color:rgba(204,102,0,0.8);color:#fff;padding:2px 4px;border-radius:3px;font-size:10px;" title="모바일 이미지"></i><span style="position:absolute;bottom:2px;left:2px;background-color:rgba(204,102,0,0.8);color:#fff;padding:1px 3px;font-size:9px;border-radius:2px;">MO</span></div>';
                    } else {
                        $mo_thumbnail_html = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                    }
                    
                    // 유튜브 썸네일 HTML 생성
                    $youtube_thumbnail_html = '';
                    if(!empty($banner_row['bnr_youtube'])){
                        $youtube_id = '';
                        if(preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $banner_row['bnr_youtube'], $matches)){
                            $youtube_id = $matches[1];
                            $youtube_thumb = 'https://img.youtube.com/vi/'.$youtube_id.'/maxresdefault.jpg';
                            $youtube_thumbnail_html = '<div style="position:relative;display:inline-block;"><img src="'.$youtube_thumb.'" alt="유튜브 썸네일" style="width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;border:1px solid #ddd;display:block;"><i class="fa fa-youtube" style="position:absolute;top:2px;right:2px;background-color:rgba(255,0,0,0.8);color:#fff;padding:2px 4px;border-radius:3px;font-size:10px;" title="유튜브"></i></div>';
                        } else {
                            $youtube_thumbnail_html = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                        }
                    } else {
                        $youtube_thumbnail_html = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                    }
                ?>
                <tr data-bnr-id="<?=$banner_row['bnr_id']?>">
                    <td class="td_sort" style="position:relative;text-align:center;"><?=$banner_row['bnr_sort']?><i class="fa fa-arrows" style="position:absolute;top:3px;right:4px;color:#999;font-size:15px;cursor:move;" title="드래그하여 순서 변경"></i></td>
                    <td class="td_thumb" style="text-align:center;"><?=$pc_thumbnail_html?></td>
                    <td class="td_thumb" style="text-align:center;"><?=$mo_thumbnail_html?></td>
                    <td class="td_thumb" style="text-align:center;"><?=$youtube_thumbnail_html?></td>
                    <td class="td_name"><?=get_text($banner_row['bnr_name'])?></td>
                    <td class="td_link">
                        <?php 
                        echo !empty($banner_row['bnr_link']) ? cut_str($banner_row['bnr_link'], 30) : '-';
                        ?>
                    </td>
                    <td class="td_mo_link">
                        <?php 
                        echo !empty($banner_row['bnr_mo_link']) ? cut_str($banner_row['bnr_mo_link'], 30) : '-';
                        ?>
                    </td>
                    <td class="td_status"><?=$banner_row['bnr_status'] == 'ok' ? '정상' : '대기'?></td>
                    <td class="td_start_dt font_size_8"><?=!empty($banner_row['bnr_start_dt']) ? substr($banner_row['bnr_start_dt'],0,16) : '-'?></td>
                    <td class="td_end_dt font_size_8"><?=!empty($banner_row['bnr_end_dt']) ? substr($banner_row['bnr_end_dt'],0,16) : '-'?></td>
                    <td class="td_mng">
                        <button type="button" class="btn_banner_edit btn_02 btn" data-bnr-id="<?=$banner_row['bnr_id']?>">수정</button>
                        <button type="button" class="btn_banner_del btn_02 btn" data-bnr-id="<?=$banner_row['bnr_id']?>">삭제</button>
                    </td>
                </tr>
                <?php
                }
                if ($banner_count == 0)
                    echo "<tr class=\"no-data\"><td colspan=\"11\" class=\"empty_table\">등록된 배너가 없습니다.</td></tr>";
                ?>
            </tbody>
        </table>
    </div>
<?php } ?>

<div class="btn_fixed_top">
    <a href="./banner_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php include_once('./js/banner_form.js.php'); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

