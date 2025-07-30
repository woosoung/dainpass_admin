<?php
$sub_menu = "920200";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'conf';


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본환경설정</a></li>
    <li><a href="#anc_cf_amazon">Amazon</a></li>
</ul>';

$g5['title'] = '관리환경설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
@include_once('./css/'.$g5['file_name'].'.css.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/_form.css">',0);
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/js/colpick/colpick.css">', 0);
add_javascript('<script src="'.G5_Z_URL.'/js/colpick/colpick.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본설정</h2>
    <?php echo $pg_anchor ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>기본</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예)기본",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="" class="w-[300px]" value="">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->
<section id="anc_cf_widget">
    <h2 class="h2_frm">Amazon설정</h2>
    <?php echo $pg_anchor ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>아마존설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>AWS 리전명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("ap-northeast-2",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_aws_region" class="w-[200px]" value="<?=${'set_'.$set_type}['set_aws_region']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_aws_region_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>S3 버킷명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("dainpass-bucket-file",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_aws_bucket" class="w-[200px]" value="<?=${'set_'.$set_type}['set_aws_bucket']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_aws_bucket_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>S3 IAM 사용자명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("S3사용을 위한 사용자명(dainpass-s3-admin)",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_s3iam_user" class="w-[200px]" value="<?=${'set_'.$set_type}['set_s3iam_user']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_s3iam_user_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>S3 엑세스키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("S3사용을 위한 엑세스키",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_s3_accesskey" class="w-[200px]" value="<?=${'set_'.$set_type}['set_s3_accesskey']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_s3_accesskey_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>S3 비밀엑세스키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("S3사용을 위한 비밀엑세스키",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_s3_secretaccesskey" class="w-[300px]" value="<?=${'set_'.$set_type}['set_s3_secretaccesskey']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_s3_secretaccesskey_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_amazon -->
<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');