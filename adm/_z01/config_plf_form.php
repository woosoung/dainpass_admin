<?php
$sub_menu = "920250";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'plf';

$thumb_wd = 200;
$thumb_ht = 150;



$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본환경설정</a></li>
    <li><a href="#anc_cf_opengraph">오픈그래프</a></li>
    <li><a href="#anc_cf_webmaster">웹마스터</a></li>
</ul>';

$g5['title'] = '플랫폼기본환경설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
@include_once('./css/'.$g5['file_name'].'.css.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/_form.css">',0);
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/js/colpick/colpick.css">', 0);
add_javascript('<script src="'.G5_Z_URL.'/js/colpick/colpick.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본환경설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본환경설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>사이트제목</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : 우성다인패스",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_title" class="w-[60%]" value="<?=${'set_'.$set_type}['set_title']??''?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_title']</p>
                    <?php } ?>
                </div>
            </td>
            <th>대표관리자이메일</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : woosoung@sample.com",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_adm_email" class="w-[60%]" value="<?=${'set_'.$set_type}['set_adm_email']??''?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_adm_email']</p>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="set_possible_ip">접근가능 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_admin){ ?>
                <p>$set_<?=$set_type?>['set_possible_ip']</p>
                <?php } ?>
                <textarea name="set_possible_ip" id="set_possible_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_possible_ip']??'')?></textarea>
            </td>
            <th><label for="set_intercept_ip">접근차단 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_admin){ ?>
                <p>$set_<?=$set_type?>['set_intercept_ip']</p>
                <?php } ?>
                <textarea name="set_intercept_ip" id="set_intercept_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_intercept_ip']??'')?></textarea>
            </td>
        </tr>
        <tr>
            <th>Favicon 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'Favicon'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_favicon" name="file_favicon[]" multiple class="multifile">
                        <?php
                        if(@count($fvc['fvc_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($fvc['fvc_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$fvc['fvc_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <?php if(isset(${'set_'.$set_type}['favicon_str'])){ ?>
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['favicon_str']?>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->

<section id="anc_cf_opengraph">
    <h2 class="h2_frm">오픈그래프</h2>
    <?=$pg_anchor?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>오픈그래프설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>타이틀</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:title 부분에 들어갈 타이틀입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_title" class="w-[300px]" value="<?=${'set_'.$set_type}['set_og_title']??''?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_title']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>설명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:description 부분에 들어갈 내용입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_desc" class="w-[500px]" value="<?=${'set_'.$set_type}['set_og_desc']??''?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_desc']</p>
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_opengraph -->

<section id="anc_cf_webmaster">
    <h2 class="h2_frm">웹마스터</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>웹마스터설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>구글 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("구글 웹마스타 설정을 위한 <strong style='color:red'>google-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_google_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_google_site_verification']??''?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_google_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>네이버 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("네이버 웹마스타 설정을 위한 <strong style='color:red'>naver-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_naver_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_naver_site_verification']??''?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_naver_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_webmaster -->

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');