<?php
$sub_menu = '920700';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '업종관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";

$sfl = in_array($sfl, array('name', 'category_id')) ? $sfl : '';

if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if (isset($save_stx) && $save_stx && ($save_stx != $stx))
        $page = 1;
}

$sql_common = " FROM {$g5['shop_categories_table']} ";
// if ($is_admin != 'super')
//     $sql_search .= " $where ca_mb_id = '{$member['mb_id']}' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " SELECT COUNT(*) AS cnt " . $sql_common;

$row = sql_fetch_pg($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "category_id";
    $sod = "ASC";
}
$sql_order = "ORDER BY $sst $sod";

// 출력할 레코드를 얻음
$sql  = " SELECT *
             $sql_common
             $sql_order
             LIMIT $rows OFFSET $from_record ";

$result = sql_query_pg($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">생성된  분류 수</span><span class="ov_num">  <?php echo number_format($total_count); ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="name"<?php echo get_selected($sfl, "name", true); ?>>업종명</option>
    <option value="category_id"<?php echo get_selected($sfl, "category_id", true); ?>>업종코드</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div id="sct" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="2"><?php echo subject_sort_link("category_id"); ?>업종코드</a></th>
        <th scope="col" id="sct_cate" rowspan="2">업종명</th>
        <th scope="col" id="sct_amount">가맹점갯수</th>
        <th scope="col" id="sct_hpcert">본인인증여부</th>
        <th scope="col" id="sct_hpcert">성인인증여부</th>
        <th scope="col" id="sct_sell"><?php echo subject_sort_link("use_yn"); ?>활성화여부</a></th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    $s_add = $s_vie = $s_upd = $s_del = '';
    for ($i=0; $row=sql_fetch_array_pg($result); $i++)
    {
        $level = strlen($row['category_id']) / 2 - 1;
        $p_ca_name = '';

        if ($level > 0) {
            $class = 'class="name_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 분류명
            $p_ca_id = substr($row['category_id'], 0, $level*2);
            $sql = " SELECT name FROM {$g5['shop_categories_table']} where category_id = '$p_ca_id' ";
            $temp = sql_fetch_pg($sql);
            $p_ca_name = $temp['name'].'의하위';
        } else {
            $class = '';
        }

        $s_level = '<div><label for="ca_name_'.$i.'" '.$class.'><span class="sound_only">'.$p_ca_name.''.($level+1).'단 분류</span></label></div>';
        $s_level_input_size = 25 - $level *2; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./categoryform.php?category_id='.$row['category_id'].'&amp;'.$qstr.'" class="btn btn_03">추가</a> '; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./categoryform.php?w=u&amp;category_id='.$row['category_id'].'&amp;'.$qstr.'" class="btn btn_02"><span class="sound_only">'.get_text($row['name']).' </span>수정</a> ';

        if ($is_admin == 'super' || $member['mb_level'] >= 9) {
            $s_del = '<a href="./categoryformupdate.php?w=d&amp;category_id='.$row['category_id'].'&amp;'.$qstr.'" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only">'.get_text($row['name']).' </span>삭제</a> ';
        }
        // 해당 분류에 속한 가맹점의 수(한 개의 가맹점이 여러 개의 업종에 속할 수 있으므로, 가맹점 수가 아닌 업종에 속한 가맹점 수를 구함)
        $sql1 = " SELECT COUNT(*) AS cnt FROM {$g5['shop_category_relation_table']}
                      WHERE category_id = '{$row['category_id']}' ";
        // echo $sql1."<br>";continue;
        $row1 = sql_fetch_pg($sql1);

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_code">
            <input type="hidden" name="category_id[<?php echo $i; ?>]" value="<?php echo $row['category_id']; ?>">
            <a href="<?php echo 'javascript:void(0);';//shop_category_url($row['category_id']); ?>"><?php echo $row['category_id']; ?></a>
        </td>
        <td headers="sct_cate" class="sct_name<?php echo $level; ?>"><?php echo $s_level; ?> <input type="text" name="name[<?php echo $i; ?>]" value="<?php echo get_text($row['name']); ?>" id="name_<?php echo $i; ?>" required class="tbl_input full_input required"></td>
        <td headers="sct_amount" class="td_amount"><?php echo $row1['cnt']; ?></td>
        <td headers="sct_hpcert" class="td_possible">
            <input type="checkbox" name="cert_use_yn[<?php echo $i; ?>]" value="1" id="cert_use_yes<?php echo $i; ?>" <?php if($row['cert_use_yn'] == 'Y') echo 'checked="checked"'; ?>>
            <label for="cert_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_adultcert" class="td_possible">
            <input type="checkbox" name="adult_use_yn[<?php echo $i; ?>]" value="1" id="adult_use_yes<?php echo $i; ?>" <?php if($row['adult_use_yn'] == 'Y') echo 'checked="checked"'; ?>>
            <label for="adult_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_sell" class="td_possible">
            <input type="checkbox" name="use_yn[<?php echo $i; ?>]" value="1" id="use_yn<?php echo $i; ?>" <?php echo ($row['use_yn'] == 'Y' ? "checked" : ""); ?>>
            <label for="use_yn<?php echo $i; ?>">활성화</label>
        </td>
        <td class="td_mng td_mng_s">
            <?php echo $s_add; ?>
            <?php echo $s_vie; ?>
            <?php echo $s_upd; ?>
            <?php echo $s_del; ?>
        </td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan=\"7\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="일괄수정" class="btn_02 btn">

    <?php if ($is_admin == 'super' || $member['mb_level'] >= 9) {?>
    <a href="./categoryform.php" id="cate_add" class="btn btn_01">분류 추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $("select.skin_dir").on("change", function() {
        var type = "";
        var dir = $(this).val();
        if(!dir)
            return false;

        var id = $(this).attr("id");
        var $sel = $(this).siblings("select");
        var sval = $sel.find("option:selected").val();

        if(id.search("mobile") > -1)
            type = "mobile";

        $sel.load(
            "./ajax.skinfile.php",
            { dir : dir, type : type, sval: sval }
        );
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');