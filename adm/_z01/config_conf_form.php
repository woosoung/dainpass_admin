<?php
$sub_menu = "920200";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'conf';


//afavicon파일 추출 ###########################################################
/*
[fle_idx] => 11
[fle_mb_id] => super
[fle_db_tbl] => set
[fle_db_idx] => afavicon
[fle_width] => 80
[fle_height] => 80
[fle_desc] => 
[fle_mime_type] => image/png
[fle_dir] => admin/conf
[fle_size] => 1531
[fle_path] => data/admin/conf/Favicondainpass_688c50b5644d1.png
[fle_name] => Favicondainpass_688c50b5644d1.png
[fle_name_orig] => Favicon-dainpass.png
[fle_sort] => 0
[fle_status] => ok
[fle_reg_dt] => 2025-08-01 14:29:25
[fle_update_dt] => 2025-08-01 05:29:25.514486
*/
$sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'set' AND fle_dir = 'admin/{$set_type}' AND fle_db_idx = 'afavicon' ORDER BY fle_reg_dt DESC ";
// echo $sql;exit;
$rs = sql_query_pg($sql);
$fvc['afvc_f_arr'] = array();
$fvc['afvc_fidxs'] = array();
$fvc['afvc_lst_idx'] = 0;
$fvc['fle_db_idx'] = 'XXXX';
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    $is_s3file_yn = is_s3file($row2['fle_path']);
    $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:80:80:1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
    $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:80px;height:80px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
    $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$set_type.'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
    '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
    $fvc['fle_db_idx'] = $row2['fle_db_idx'];
    @array_push($fvc['afvc_f_arr'], array('file'=>$row2['down_del']));
    @array_push($fvc['afvc_fidxs'], $row2['fle_idx']);
}


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
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<input type="hidden" name="fle_db_idx" value="<?=$fvc['fle_db_idx']?>">
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
            <th>이미지 프록시서버 URL</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예)이미지 프록시서버 URL",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_imgproxy_url" class="w-[300px]" value="<?=${'set_'.$set_type}['set_imgproxy_url']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_imgproxy_url_str']??''?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>비회원 접근가능한 페이지</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예)login,logout,register,register_form,register_form,password_lost",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_nomb_accessiblepages" class="w-[300px]" value="<?=${'set_'.$set_type}['set_nomb_accessiblepages']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_nomb_accessiblepages_str']??''?>
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
        <tr>
            <th>S3 파일 기본URL</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("S3 파일 기본URL",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_s3_basicurl" class="w-[400px]" value="<?=${'set_'.$set_type}['set_s3_basicurl']??''?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_s3_basicurl_str']??''?>
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