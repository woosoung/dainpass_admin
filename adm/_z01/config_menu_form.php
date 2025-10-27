<?php
$sub_menu = "910100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'menu';


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본메뉴설정</a></li>
</ul>';

$g5['title'] = '관리메뉴설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본메뉴설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본메뉴설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">
                기본메뉴<br>
                <span id="all_hide_clear" class="cmf_all_hide_clear_btn">전체비활성해제</span>
            </th>
            <td colspan="3" class="cmf_menu_h3_class cmf_menu_ul_class cmf_menu_li_class">
                <?php
                // print_r2($set_menu);
                // 비활성 메뉴 가져오기
                $main_arr = isset(${'set_'.$set_type}['set_hide_mainmenus']) ? explode(',',${'set_'.$set_type}['set_hide_mainmenus']) : array();
                $sub_arr = isset(${'set_'.$set_type}['set_hide_submenus']) ? explode(',',${'set_'.$set_type}['set_hide_submenus']) : array();
                ?>
                
                <input type="hidden" name="set_hide_mainmenus" id="set_hide_mainmenus" value="<?=${'set_'.$set_type}['set_hide_mainmenus']?>" class="border w-full">
                <input type="hidden" name="set_hide_submenus" id="set_hide_submenus" value="<?=${'set_'.$set_type}['set_hide_submenus']?>" class="border w-full">
                <?php
                //전체 메뉴구조를 확인하려면 변수($menu_list_tag_)맨끝에 '_'를 제거하세요.
                // echo $menu_list_tag 일때만 메뉴구조 확인 가능
                $menu_list_tag_ = '';
                if($member['mb_level'] == 10){ echo $menu_list_tag_; }
                $auth_list_tag = '<div class="auth_box">'.PHP_EOL;
                foreach($menu2 as $k => $v){
                    if(count($v)){
                        foreach($v as $i => $s){
                            if($i == 0) {
                                $auth_list_tag .= '<div class="auth_div"><h3 class="auth_h3'.((in_array($k,$main_arr)?' unact':'')).'" data-code="'.$k.'" title="'.substr($k,4,3).'000" style="font-size:0.9rem;">'.$s[1];
                                $auth_list_tag .= '</h3><ul class="auth_ul">'.PHP_EOL;
                            }
                            if($i >= 1) $auth_list_tag .= '<li data-code="'.$s[0].'" title="'.$s[0].'" class="auths'.((in_array($s[0],$sub_arr)?' unact':'')).'">'.$s[1].'</li>'.PHP_EOL;
                            if($i == count($v)-1) $auth_list_tag .= '</ul></div>'.PHP_EOL;  
                        }
                    }
                }
                $auth_list_tag .= '</div>'.PHP_EOL;
                echo $auth_list_tag;
                ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<script>
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');