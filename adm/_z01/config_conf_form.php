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
    <li><a href="#anc_cf_widget">위젯환경설정</a></li>
</ul>';

$g5['title'] = '환경설정';
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
    <h2 class="h2_frm">기본환경설정</h2>
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
            <th scope="row">
                사이트기본 배경색상
            </th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("PC버전에서 사이트 전체 기본 배경 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>              <div class="flex">
                    <ul class="flex [&>li]:mr-4">
                        <li>
                        
                        밝은 배경 색상<br>
                        <?=tms_input_color('set_bright_bg',${'set_'.$set_type}['set_bright_bg'],$w)?>
                        </li>
                        <li>
                        보통 배경 색상<br>
                        <?=tms_input_color('set_normal_bg',${'set_'.$set_type}['set_normal_bg'],$w)?>
                        </li>
                        <li>
                        메인 배경 색상<br>
                        <?=tms_input_color('set_main_bg',${'set_'.$set_type}['set_main_bg'],$w)?>
                        </li>
                        <li>
                        다크 배경 색상<br>
                        <?=tms_input_color('set_dark_bg',${'set_'.$set_type}['set_dark_bg'],$w)?>
                        </li>
                    </ul>
                    <div>
                        <p>$set_<?=$set_type?>['set_bright_bg']</p>
                        <p>$set_<?=$set_type?>['set_normal_bg']</p>
                        <p>$set_<?=$set_type?>['set_main_bg']</p>
                        <p>$set_<?=$set_type?>['set_dark_bg']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                사이트기본 폰트색상
            </th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("PC버전에서 사이트 전체 기본 폰트 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>              <div class="flex">
                    <ul class="flex [&>li]:mr-4">
                        <li>
                        밝은 배경 폰트<br>
                        <?=tms_input_color('set_bright_font',${'set_'.$set_type}['set_bright_font'],$w)?>
                        </li>
                        <li>
                        보통 배경 폰트<br>
                        <?=tms_input_color('set_normal_font',${'set_'.$set_type}['set_normal_font'],$w)?>
                        </li>
                        <li>
                        메인 배경 폰트<br>
                        <?=tms_input_color('set_main_font',${'set_'.$set_type}['set_main_font'],$w)?>
                        </li>
                        <li>
                        다크 배경 폰트<br>
                        <?=tms_input_color('set_dark_font',${'set_'.$set_type}['set_dark_font'],$w)?>
                        </li>
                    </ul>
                    <div>
                        <p>$set_<?=$set_type?>['set_bright_font']</p>
                        <p>$set_<?=$set_type?>['set_normal_font']</p>
                        <p>$set_<?=$set_type?>['set_main_font']</p>
                        <p>$set_<?=$set_type?>['set_dark_font']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>삭제처리방법</th>
            <td colspan="3">
                <div class="flex gap-6">
                    <div>
                        <?php
                        $chk_del_yn_0 = (!${'set_'.$set_type}['set_del_yn']) ? 'checked' : '';
                        $chk_del_yn_1 = (${'set_'.$set_type}['set_del_yn']) ? 'checked' : '';
                        ?>
                        <label for="set_del_yn_0" class="label_radio">
                            <input type="radio" id="set_del_yn_0" name="set_del_yn" value="0" <?=$chk_del_yn_0?>>
                            <strong></strong>
                            <span>상태값처리</span>
                        </label>
                        <label for="set_del_yn_1" class="label_radio">
                            <input type="radio" id="set_del_yn_1" name="set_del_yn" value="1" <?=$chk_del_yn_1?>>
                            <strong></strong>
                            <span>삭제처리</span>
                        </label>
                    </div>
                    <div>
                        <p>$set_<?=$set_type?>['set_del_yn']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>사이트 작업중 여부</th>
            <td>
                <div class="flex gap-6">
                    <div>
                        <?php
                        $chk_preparing_yn_0 = (!${'set_'.$set_type}['set_preparing_yn']) ? 'checked' : '';
                        $chk_preparing_yn_1 = (${'set_'.$set_type}['set_preparing_yn']) ? 'checked' : '';
                        ?>
                        <label for="set_preparing_yn_0" class="label_radio">
                            <input type="radio" id="set_preparing_yn_0" name="set_preparing_yn" value="0" <?=$chk_preparing_yn_0?>>
                            <strong></strong>
                            <span>공개중</span>
                        </label>
                        <label for="set_preparing_yn_1" class="label_radio">
                            <input type="radio" id="set_preparing_yn_1" name="set_preparing_yn" value="1" <?=$chk_preparing_yn_1?>>
                            <strong></strong>
                            <span>작업중</span>
                        </label>
                    </div>
                    <div>
                        <p>$set_<?=$set_type?>['set_preparing_yn']</p>
                    </div>
                </div>
            </td>
            <th>기본상태</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : pending=대기,ok=정상",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_status" class="w-[50%]" value="<?=${'set_'.$set_type}['set_status']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_status_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>링크타겟</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : _self=현재창,_blank=새창",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_target" class="w-[50%]" value="<?=${'set_'.$set_type}['set_target']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_target_str']?>
                        </div>
                    </div>
                </div>
            </td>
            <th>표시여부</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : show=표시,hide=비표시",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_show_hide" class="w-[50%]" value="<?=${'set_'.$set_type}['set_show_hide']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_show_hide_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>검색유형</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : shop=상품검색,bbs=게시판검색",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_sch_type" class="w-[220px]" value="<?=${'set_'.$set_type}['set_sch_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_sch_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
            <th>업체유형</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : purchase=매입처,sale=매출처,both=매입매출처,etc=기타",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_com_type" class="w-[50%]" value="<?=${'set_'.$set_type}['set_com_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_com_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>업체상태</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : ok=정상,close=폐업,stop=거래중지,prohibit=거래금지",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_com_status" class="w-[50%]" value="<?=${'set_'.$set_type}['set_com_status']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_com_status_str']?>
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
    <h2 class="h2_frm">위젯설정</h2>
    <?php echo $pg_anchor ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>위젯설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>캐시시간</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : 0=0초,0.00139=5초,0.0028=10초,0.0056=20초,0.0084=30초,0.012=40초,0.0139=50초,0.0167=60초,1=1시간",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_cachetimes" class="w-[50%]" value="<?=${'set_'.$set_type}['set_cachetimes']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_cachetimes_str']?>
                        </div>
                </div>
            </td>
            <th>BP위젯캐시 저장시간</th>
            <td class="tms_help">
                <!--
                $cache_time은 시간단위 
                1시간=1, 5초=0.00139, 10초=0.0028, 20초=0.0056, 30초=0.0084, 40초=0.012, 50초=0.0139, 60초=0.0167, 3600초=1시간
                -->
                <?php echo tms_help("캐시 저장시간의 값이 작을수록 위젯 수정후 반영되는 시간이 짧아집니다.",1,'#f9fac6','#333333'); ?>
                <?php echo tms_select_selected(${'set_'.$set_type}['set_cachetimes'], 'set_cachetime', ${'set_'.$set_type}['set_cachetime'], 0,0,0);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
                <span class="ml-4">$set_<?=$set_type?>['set_cachetime']</span>
            </td>
        </tr>
        <tr>
            <th>위젯분류</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : banner=배너,content=콘텐츠,board=게시판,shop=쇼핑몰,item=상품,section=섹션스킨,etc=기타",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_purpose" class="w-[60%]" value="<?=${'set_'.$set_type}['set_purpose']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_purpose_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>텍스트애니메이션</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : flash=플래시,flip=플립,flipInX=플립인X,flipInY=플립인Y,fadeIn=패이드인,fadeInUp=패이드인위쪽,fadeInDown=패이드인아래쪽,fadeInLeft=패이드인왼쪽,fadeInRight=패이드인오른쪽,fadeInUpBig=페이드인위쪽크게,fadeInDownBig=페이드인아래쪽크게,rollIn=롤인,rotateInUpRight=회전위쪽오른쪽,bounceInLeft=바운스인왼쪽,bounceInRight=바운스인오른쪽",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_text_ani" class="w-[80%]" value="<?=${'set_'.$set_type}['set_text_ani']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_text_ani_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품노출분류</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : 1=히트,2=추천,3=최신,4=인기,5=할인,6=분류,7=전체",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_item_type" class="w-[60%]" value="<?=${'set_'.$set_type}['set_item_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_item_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_widget -->
<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');