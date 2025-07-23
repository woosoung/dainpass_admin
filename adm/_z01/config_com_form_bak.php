<?php
$sub_menu = "930100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'com';

$thumb_wd = 200;
$thumb_ht = 150;

//준비중파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'preparing' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$cms['cms_f_arr'] = array();
$cms['cms_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$cms['cms_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    // print_r2($row2);
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($cms['cms_f_arr'],array('file'=>$file_down_del));
    @array_push($cms['cms_fidxs'],$row2['fle_idx']);
}
// exit;
//favicon파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'favicon' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$fvc['fvc_f_arr'] = array();
$fvc['fvc_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$fvc['fvc_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($fvc['fvc_f_arr'],array('file'=>$file_down_del));
    @array_push($fvc['fvc_fidxs'],$row2['fle_idx']);
}

//ogimg파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'ogimg' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$ogi['ogi_f_arr'] = array();
$ogi['ogi_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$ogi['ogi_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($ogi['ogi_f_arr'],array('file'=>$file_down_del));
    @array_push($ogi['ogi_fidxs'],$row2['fle_idx']);
}

//sitemap파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'sitemap' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql,1);
//echo $rs->num_rows;echo "<br>";
$stm['stm_f_arr'] = array();
$stm['stm_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$stm['stm_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($stm['stm_f_arr'],array('file'=>$file_down_del));
    @array_push($stm['stm_fidxs'],$row2['fle_idx']);
}


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_opengraph">오픈그래프</a></li>
    <li><a href="#anc_cf_webmaster">웹마스터</a></li>
</ul>';

$g5['title'] = '기준환경설정';
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
            <th>홈페이지제목</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : 웹솔루션전문 넷도리",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_title" class="w-[60%]" value="<?=${'set_'.$set_type}['set_title']?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_title']</p>
                    <?php } ?>
                </div>
            </td>
            <th>대표관리자이메일</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : woosoung@sample.com",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_adm_email" class="w-[60%]" value="<?=${'set_'.$set_type}['set_adm_email']?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_adm_email']</p>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>Favicon 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'Favicon'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_favicon" name="file_favicon[]" multiple class="">
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
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['favicon_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>준비중 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'준비중'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_preparing" name="file_preparing[]" multiple class="">
                        <?php
                        if(@count($cms['cms_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($cms['cms_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$cms['cms_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['preparing_str']?>
                        </div>
                    </div>
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
                <textarea name="set_possible_ip" id="set_possible_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_possible_ip'])?></textarea>
            </td>
            <th><label for="set_intercept_ip">접근차단 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_admin){ ?>
                <p>$set_<?=$set_type?>['set_intercept_ip']</p>
                <?php } ?>
                <textarea name="set_intercept_ip" id="set_intercept_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_intercept_ip'])?></textarea>
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
                <input type="text" name="set_og_title" class="w-[300px]" value="<?=${'set_'.$set_type}['set_og_title']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_title']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>설명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:description 부분에 들어갈 내용입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_desc" class="w-[500px]" value="<?=${'set_'.$set_type}['set_og_desc']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_desc']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>ogimg 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'ogimg'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_ogimg" name="file_ogimg[]" multiple class="">
                        <?php
                        if(@count($ogi['ogi_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($ogi['ogi_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$ogi['ogi_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['ogimg_str']?>
                        </div>
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
                <input type="text" name="set_google_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_google_site_verification']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_google_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>네이버 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("네이버 웹마스타 설정을 위한 <strong style='color:red'>naver-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_naver_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_naver_site_verification']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_naver_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>사이트맵</th>
            <td colspan="3" class="tms_help">
                <?php echo help("웹마스터 연동을 위한 sitemap.xml 파일을 업로드해 주세요.<br>기존 파일이 존재하면 덮어쓰기 됩니다.<br><strong>파일위치 : <span style='color:blue;'>".G5_DATA_NDR_URL."/seo/sitemap.xml</sapn></strong>"); ?>
                <p>반드시 사이트 오픈후 sitemap.xml파일(파일명 동일하게 작성)을 작성하여 업로드 해 주세요.<br>sitemap.xml을 만들어주는 사이트 여기-> [<a href="http://www.check-domains.com/sitemap/index.php" target="_blank" style="color:orange;">http://www.check-domains.com/sitemap/index.php</a>]<br>방법은 아래 순서를 참고 하세요.</p>
                <p>1. 사이트 기본URL을 Site URL입력란에 입력하세요.</p>
                <p>2. 아래 라디오버튼에서 "Server's response"를 체크하세요.</p>
                <p>3. 그 옆에 있는 "Frequency"의 드롭박스 목록에서 "Always"로 선택하세요.(Monthly가 아닙니다. 주의하세요!)</p>
                <p>4. Site URL입력란 오른쪽에 있는 노란색버튼"Create Sitemap"을 클릭합니다. </p>
                <p>5. 시간이 상당히 오래 걸리기 때문에 페이지를 절대 닫지말고 끝까지 기다리세요.(대략 30분정도 소요됨)</p>
                <p>6. 작성완료되면 다운받고, 파일명이 sitemap.xml인것을 확인후 현 사이트로 돌아와 업로드 해 주세요.</p>
                <hr class="border-b-1 border-gray-200 my-4">
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_sitemap" name="file_sitemap[]" multiple class="">
                        <?php
                        if(@count($stm['stm_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($stm['stm_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$stm['stm_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['sitemap_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
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