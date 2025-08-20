<?php
$sub_menu = "920250";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'plf';

// 플랫폼사이트 Favicon 이미지
$sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'set' AND fle_dir = '{$set_type}' AND fle_db_idx = 'favicon' ORDER BY fle_reg_dt DESC ";
// echo $sql;exit;
$rs = sql_query_pg($sql);
$fc_wd = 80;
$fc_ht = 80;
$fc['fvc_f_arr'] = array();
$fc['fvc_fidxs'] = array();
$fc['fvc_lst_idx'] = 0;
$fc['fle_db_idx'] = 'favicon';
for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
    $is_s3file_yn = is_s3file($row2['fle_path']);
    $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$fc_wd.':'.$fc_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
    $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$fc_wd.'px;height:'.$fc_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
    $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$set_type.'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
    '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
    $fc['fle_db_idx'] = $row2['fle_db_idx'];
    @array_push($fc['fvc_f_arr'], array('file'=>$row2['down_del']));
    @array_push($fc['fvc_fidxs'], $row2['fle_idx']);
}

// 플랫폼사이트 로고 이미지
$sql2 = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'set' AND fle_dir = '{$set_type}' AND fle_db_idx = 'plflogo' ORDER BY fle_reg_dt DESC ";
// echo $sql2;exit;
$rs2 = sql_query_pg($sql2);
$plflogo_wd = 172;
$plflogo_ht = 105;
$plflogo['plflogo_f_arr'] = array();
$plflogo['plflogo_fidxs'] = array();
$plflogo['plflogo_lst_idx'] = 0;
$plflogo['fle_db_idx'] = 'plflogo';
for($i=0;$row2=sql_fetch_array_pg($rs2->result);$i++) {
    // print_r2($row2);
    $is_s3file_yn = is_s3file($row2['fle_path']);
    $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$plflogo_wd.':'.$plflogo_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
    $row2['thumb'] = '<img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$plflogo_wd.'px;height:'.$plflogo_ht.'px;margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
    $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$set_type.'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
    '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql2).' LIMIT 1;</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
    $plflogo['fle_db_idx'] = $row2['fle_db_idx'];
    @array_push($plflogo['plflogo_f_arr'], array('file'=>$row2['down_del']));
    @array_push($plflogo['plflogo_fidxs'], $row2['fle_idx']);
}

// exit;
// 플랫폼사이트 오픈그래프 대표이미지
$sql = " SELECT * FROM {$g5['dain_file_table']} WHERE fle_db_tbl = 'set' AND fle_dir = '{$set_type}' AND fle_db_idx = 'ogimg' ORDER BY fle_reg_dt DESC ";
// echo $sql;exit;
$rs = sql_query_pg($sql);
$og_wd = 160;
$og_ht = 100;
$og['og_f_arr'] = array();
$og['og_fidxs'] = array();
$og['og_lst_idx'] = 0;
$og['fle_db_idx'] = 'ogimg';
for($i=0;$row2=sql_fetch_array_pg($rs->result);$i++) {
    // print_r2($row2);
    $is_s3file_yn = is_s3file($row2['fle_path']);
    $row2['thumb_url'] = $set_conf['set_imgproxy_url'].'/rs:fill:'.$og_wd.':'.$og_ht.':1/plain/'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'];
    $row2['thumb'] = '<span class="inline-block bg_transparent ml-[20px]"><img src="'.$row2['thumb_url'].'" alt="'.$row2['fle_name_orig'].'" style="width:'.$og_wd.'px;height:'.$og_ht.'px;border:1px solid #ddd;"></span><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
    $row2['down_del'] = ($is_s3file_yn) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_path='.$row2['fle_path'].'&file_name_orig='.$row2['fle_name_orig'].'">[파일다운로드]</a>&nbsp;&nbsp;'.substr($row2['fle_reg_dt'],0,19).'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$set_type.'_'.$row2['fle_db_idx'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_dev_manager && $is_s3file_yn) ? 
    '<br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.trim($sql).' LIMIT 1;</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$set_conf['set_s3_basicurl'].'/'.$row2['fle_path'].'</span></span>
    <br><span><i class="copy_url fa fa-clone cursor-pointer text-blue-500" aria-hidden="true"></i>&nbsp;<span class="copied_url">'.$row2['thumb_url'].'</span></span>'.PHP_EOL : ''.PHP_EOL;
    $row2['down_del'] .= ($is_s3file_yn) ? '<br>'.$row2['thumb'].PHP_EOL : ''.PHP_EOL;
    $og['fle_db_idx'] = $row2['fle_db_idx'];
    @array_push($og['og_f_arr'], array('file'=>$row2['down_del']));
    @array_push($og['og_fidxs'], $row2['fle_idx']);
}
// exit;


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">사이트기본설정</a></li>
    <li><a href="#anc_cf_opengraph">오픈그래프</a></li>
    <li><a href="#anc_cf_webmaster">웹마스터</a></li>
</ul>';

$g5['title'] = '플랫폼사이트설정';
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
<?php if($is_dev_manager) { ?>
<div class="local_desc02 local_desc">
    <p>
        <span class="text-red-800">여기는 setting 테이블관련 데이터입니다.</span>
    </p>
    <p>
        <h3>사이트제목 추출쿼리</h3>
        <span class="text-red-800">SELECT set_value FROM setting WHERE set_key = 'dain' AND set_type = 'plf' AND set_name = 'set_title'</span><br>
        <h3>대표관리자이메일 추출쿼리</h3>
        <span class="text-red-800">SELECT set_value FROM setting WHERE set_key = 'dain' AND set_type = 'plf' AND set_name = 'set_adm_email'</span>
    </p>
</div>
<?php } ?>

<section id="anc_cf_default">
    <h2 class="h2_frm">사이트기본설정</h2>
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
                    <?php if($is_dev_manager){ ?>
                    <p>$set_<?=$set_type?>['set_title']</p>
                    <?php } ?>
                </div>
            </td>
            <th>대표관리자이메일</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : woosoung@sample.com",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_adm_email" class="w-[60%]" value="<?=${'set_'.$set_type}['set_adm_email']??''?>">
                    <?php if($is_dev_manager){ ?>
                    <p>$set_<?=$set_type?>['set_adm_email']</p>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="set_possible_ip">접근가능 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_dev_manager){ ?>
                <p>$set_<?=$set_type?>['set_possible_ip']</p>
                <?php } ?>
                <textarea name="set_possible_ip" id="set_possible_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_possible_ip']??'')?></textarea>
            </td>
            <th><label for="set_intercept_ip">접근차단 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_dev_manager){ ?>
                <p>$set_<?=$set_type?>['set_intercept_ip']</p>
                <?php } ?>
                <textarea name="set_intercept_ip" id="set_intercept_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_intercept_ip']??'')?></textarea>
            </td>
        </tr>
        <tr>
            <th>플랫폼사이트 Favicon 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("플랫폼사이트의 'Favicon'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_favicon" name="file_favicon[]" multiple class="multifile">
                        <?php
                        if(@count($fc['fvc_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($fc['fvc_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$fc['fvc_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_dev_manager){ ?>
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
        <tr>
            <th>플랫폼사이트 로고 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("플랫폼사이트의 '로고'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_plflogo" name="file_plflogo[]" multiple class="multifile">
                        <?php
                        if(@count($plflogo['plflogo_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($plflogo['plflogo_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$plflogo['plflogo_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_dev_manager){ ?>
                    <div class="tms_hbox">
                        <?php if(isset(${'set_'.$set_type}['plflogo_str'])){ ?>
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['plflogo_str']?>
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
            <th>오픈그래프 타이틀</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:title 부분에 들어갈 타이틀입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_title" class="w-[300px]" value="<?=${'set_'.$set_type}['set_og_title']??''?>">
                <?php if($is_dev_manager){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_title']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>오픈그래프 설명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:description 부분에 들어갈 내용입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_desc" class="w-[500px]" value="<?=${'set_'.$set_type}['set_og_desc']??''?>">
                <?php if($is_dev_manager){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_desc']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>오픈그래프 대표이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("오픈그래프의 대표이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_ogimg" name="file_ogimg[]" multiple class="multifile">
                        <?php
                        if(@count($og['og_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($og['og_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$og['og_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_dev_manager){ ?>
                    <div class="tms_hbox">
                        <?php if(isset(${'set_'.$set_type}['ogimg_str'])){ ?>
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['ogimg_str']?>
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
                <?php if($is_dev_manager){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_google_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>네이버 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("네이버 웹마스타 설정을 위한 <strong style='color:red'>naver-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_naver_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_naver_site_verification']??''?>">
                <?php if($is_dev_manager){ ?>
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