<?php
$sub_menu = '920650';
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
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value ?? '', 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value ?? '', 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$sql_common = " FROM banner_group ";

$where = array();
$where[] = " bng_status NOT IN ('del', 'delete', 'trash') ";

$_GET['sfl'] = !empty($_GET['sfl']) ? $_GET['sfl'] : '';

// 검색 조건 처리
if ($stx) {
    switch ($sfl) {
        case 'bng_code' :
            $where[] = " bng_code = '".addslashes($stx)."' ";
            break;
        case 'bng_name' :
            $where[] = " bng_name LIKE '%".addslashes($stx)."%' ";
            break;
        default :
            $where[] = " ( bng_code LIKE '%".addslashes($stx)."%' OR bng_name LIKE '%".addslashes($stx)."%' OR bng_desc LIKE '%".addslashes($stx)."%' ) ";
            break;
    }
}

// 상태 필터
$ser_status = isset($_GET['ser_status']) ? trim($_GET['ser_status']) : '';
if ($ser_status !== '') {
    $where[] = " bng_status = '".addslashes($ser_status)."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);
else
    $sql_search = '';

if (!$sst) {
    $sst = "bng_id";
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

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$colspan = 11;

// 섬네일 이미지 크기
$thumb_wd = 120;
$thumb_ht = 80;

$status_arr = array(
    'ok' => '정상',
    'pending' => '대기',
    'del' => '삭제',
    'delete' => '삭제',
    'trash' => '삭제'
);

$g5['title'] = '배너 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="ser_status" class="sound_only">상태</label>
<select name="ser_status" id="ser_status" class="cp_field" title="상태">
    <option value="">전체상태</option>
    <option value="ok"<?php echo get_selected($_GET['ser_status']??'', "ok"); ?>>정상</option>
    <option value="pending"<?php echo get_selected($_GET['ser_status']??'', "pending"); ?>>대기</option>
</select>

<select name="sfl" id="sfl">
    <option value=""<?php echo get_selected($_GET['sfl']??'', ""); ?>>전체</option>
    <option value="bng_code"<?php echo get_selected($_GET['sfl']??'', "bng_code"); ?>>배너그룹 코드</option>
    <option value="bng_name"<?php echo get_selected($_GET['sfl']??'', "bng_name"); ?>>배너그룹명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<form name="form01" id="form01" action="./banner_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
                        <label for="chkall" class="sound_only">배너그룹 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" class="td_left">번호</th>
                    <th scope="col" class="td_center !w-[<?=$thumb_wd?>px]">PC 섬네일</th>
                    <th scope="col" class="td_center !w-[<?=$thumb_wd?>px]">모바일 섬네일</th>
                    <th scope="col" class="td_left"><?php echo subject_sort_link('bng_code', $qstr) ?>배너그룹 코드</a></th>
                    <th scope="col" class="td_left"><?php echo subject_sort_link('bng_name', $qstr) ?>배너그룹명</a></th>
                    <th scope="col"><?php echo subject_sort_link('bng_start_dt', $qstr) ?>시작일시</a></th>
                    <th scope="col"><?php echo subject_sort_link('bng_end_dt', $qstr) ?>종료일시</a></th>
                    <th scope="col"><?php echo subject_sort_link('bng_status', $qstr) ?>상태</a></th>
                    <th scope="col"><?php echo subject_sort_link('bng_created_at', $qstr) ?>등록일시</a></th>
                    <th scope="col" id="banner_list_mng">수정</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
                    $s_mod = '<a href="./banner_form.php?'.$qstr.'&amp;w=u&amp;bng_id='.$row['bng_id'].'">수정</a>';

                    // 배너 그룹 이미지 가져오기 (PC용)
                    $fsql = " SELECT fle_path FROM {$g5['dain_file_table']}
                                WHERE fle_db_tbl = 'banner_group'
                                    AND fle_type = 'bng_img'
                                    AND fle_dir = 'plt/banner'
                                    AND fle_db_idx = '{$row['bng_id']}'
                                ORDER BY fle_reg_dt DESC LIMIT 1 ";
                    $fres = sql_fetch_pg($fsql);
                    
                    // 배너 그룹 이미지 가져오기 (모바일용)
                    $fsql_mo = " SELECT fle_path FROM {$g5['dain_file_table']}
                                  WHERE fle_db_tbl = 'banner_group'
                                      AND fle_type = 'bng_mo_img'
                                      AND fle_dir = 'plt/banner'
                                      AND fle_db_idx = '{$row['bng_id']}'
                                  ORDER BY fle_reg_dt DESC LIMIT 1 ";
                    $fres_mo = sql_fetch_pg($fsql_mo);
                    
                    // PC용 이미지 HTML 생성
                    $row['pc_thumb_tag'] = '';
                    if(!empty($fres['fle_path'])){
                        $row['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres['fle_path'];
                        $row['pc_thumb_tag'] = '<div style="position:relative;display:inline-block;"><img src="'.$row['thumb_url'].'" alt="'.get_text($row['bng_name']).' (PC)" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;"><span style="position:absolute;bottom:2px;left:2px;background-color:rgba(0,102,204,0.8);color:#fff;padding:1px 4px;font-size:10px;border-radius:2px;">PC</span></div>';
                    } else {
                        $row['pc_thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                    }
                    
                    // 모바일용 이미지 HTML 생성
                    $row['mo_thumb_tag'] = '';
                    if(!empty($fres_mo['fle_path'])){
                        $row['thumb_url_mo'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$thumb_wd.':'.$thumb_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$fres_mo['fle_path'];
                        $row['mo_thumb_tag'] = '<div style="position:relative;display:inline-block;"><img src="'.$row['thumb_url_mo'].'" alt="'.get_text($row['bng_name']).' (Mobile)" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;"><span style="position:absolute;bottom:2px;left:2px;background-color:rgba(204,102,0,0.8);color:#fff;padding:1px 4px;font-size:10px;border-radius:2px;">MO</span></div>';
                    } else {
                        $row['mo_thumb_tag'] = '<img src="'.G5_Z_URL.'/img/no_thumb.png" alt="no image" width="'.$thumb_wd.'" class="inline-block" height="'.$thumb_ht.'" style="border:1px solid #ddd;width:'.$thumb_wd.'px;height:'.$thumb_ht.'px;">';
                    }

                    $bg = 'bg'.($i%2);

                ?>
                <tr class="<?=$bg?>" tr_id="<?=$row['bng_id']?>">
                    <td class="td_chk">
                        <input type="hidden" name="bng_id[<?=$i?>]" value="<?=$row['bng_id']?>" id="bng_id_<?=$i?>">
                        <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['bng_name'])?></label>
                        <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
                    </td>
                    <td class="td_left font_size_8"><?=$row['bng_id']?></td>
                    <td class="td_thumb font_size_8" style="text-align:center;"><?=$row['pc_thumb_tag']?></td>
                    <td class="td_thumb font_size_8" style="text-align:center;"><?=$row['mo_thumb_tag']?></td>
                    <td class="td_left"><a href="./banner_form.php?<?=$qstr?>&amp;w=u&amp;bng_id=<?=$row['bng_id']?>"><?=get_text($row['bng_code'])?></a></td>
                    <td class="td_left"><b><?=get_text($row['bng_name'])?></b></td>
                    <td class="font_size_8"><?=!empty($row['bng_start_dt']) ? substr($row['bng_start_dt'],0,16) : '-'?></td>
                    <td class="font_size_8"><?=!empty($row['bng_end_dt']) ? substr($row['bng_end_dt'],0,16) : '-'?></td>
                    <td><?=isset($status_arr[$row['bng_status']]) ? $status_arr[$row['bng_status']] : $row['bng_status']?></td>
                    <td class="font_size_8"><?=substr($row['bng_created_at'],0,16)?></td>
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
        <!-- <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn"> -->
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <?php } ?>
        <a href="./banner_form.php" id="bo_add" class="btn_01 btn">배너그룹 추가</a>
    </div>
</form>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<?php include_once('./js/banner_list.js.php'); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

