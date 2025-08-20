<?php
$sub_menu = '920050';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "r");



$g5['title'] = '플랫폼기본환경설정';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_scf_info">사업자정보</a></li>
<li><a href="#anc_scf_payment">결제설정</a></li>
<li><a href="#anc_scf_sms">SMS설정</a></li>
<li><a href="#anc_scf_etc">기타설정</a></li>
</ul>';


if( function_exists('pg_setting_check2') ){
    pg_setting_check2(true);
}



// if(!$default2['de_kakaopay_cancelpwd']){
//     $default2['de_kakaopay_cancelpwd'] = '1111';
// }
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/js/colpick/colpick.css">', 0);
add_javascript('<script src="'.G5_Z_URL.'/js/colpick/colpick.js"></script>',0);
?>
<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="token" value="">
<section id="anc_scf_info">
    <h2 class="h2_frm">사업자정보</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            사업자정보는 tail.php 와 content.php 에서 표시합니다.<br>
            대표전화번호는 SMS 발송번호로 사용되므로 사전등록된 발신번호와 일치해야 합니다.
        </p>
        <?php if($is_dev_manager) { ?>
        <p>
            <span class="text-red-800">여기의 데이터는 dain_default 테이블에 해당합니다.</span>
        </p>
        <?php } ?>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>사업자정보 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_admin_company_name">회사명<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_name</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_name" value="<?php echo get_sanitize_input($default2['de_admin_company_name']??''); ?>" id="de_admin_company_name" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_company_saupja_no">사업자등록번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_saupja_no</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_saupja_no"  value="<?php echo get_sanitize_input($default2['de_admin_company_saupja_no']??''); ?>" id="de_admin_company_saupja_no" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_owner">대표자명<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_owner</span><?php } ?></label></th>
            <td colspan="3">
                <input type="text" name="de_admin_company_owner" value="<?php echo get_sanitize_input($default2['de_admin_company_owner']??''); ?>" id="de_admin_company_owner" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_tel">대표전화번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_tel</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_tel" value="<?php echo get_sanitize_input($default2['de_admin_company_tel']??''); ?>" id="de_admin_company_tel" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_company_fax">팩스번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_fax</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_fax" value="<?php echo get_sanitize_input($default2['de_admin_company_fax']??''); ?>" id="de_admin_company_fax" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_tongsin_no">통신판매업 신고번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_tongsin_no</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_tongsin_no" value="<?php echo get_sanitize_input($default2['de_admin_tongsin_no']??''); ?>" id="de_admin_tongsin_no" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_buga_no">부가통신 사업자번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_buga_no</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_buga_no" value="<?php echo get_sanitize_input($default2['de_admin_buga_no']??''); ?>" id="de_admin_buga_no" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_zip">사업장우편번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_zip</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_zip" value="<?php echo get_sanitize_input($default2['de_admin_company_zip']??''); ?>" id="de_admin_company_zip" class="frm_input" size="10">
            </td>
            <th scope="row"><label for="de_admin_company_addr">사업장주소<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_company_addr</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_company_addr" value="<?php echo get_sanitize_input($default2['de_admin_company_addr']??''); ?>" id="de_admin_company_addr" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_info_name">정보관리책임자명<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_info_name</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_info_name" value="<?php echo get_sanitize_input($default2['de_admin_info_name']??''); ?>" id="de_admin_info_name" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_info_email">정보책임자 e-mail<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_admin_info_email</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_admin_info_email" value="<?php echo get_sanitize_input($default2['de_admin_info_email']??''); ?>" id="de_admin_info_email" class="frm_input" size="30">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id ="anc_scf_payment">
    <h2 class="h2_frm">결제설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>결제설정 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_settle_min_point">결제 최소포인트<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_settle_min_point</span><?php } ?></label></th>
            <td>
                <?php echo help("회원의 포인트가 설정값 이상일 경우만 주문시 결제에 사용할 수 있습니다.\n포인트 사용을 하지 않는 경우에는 의미가 없습니다."); ?>
                <input type="text" name="de_settle_min_point" value="<?php echo get_sanitize_input($default2['de_settle_min_point']); ?>" id="de_settle_min_point" class="frm_input" size="10"> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_settle_max_point">최대 결제포인트<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_settle_max_point</span><?php } ?></label></th>
            <td>
                <?php echo help("주문 결제시 최대로 사용할 수 있는 포인트를 설정합니다.\n포인트 사용을 하지 않는 경우에는 의미가 없습니다."); ?>
                <input type="text" name="de_settle_max_point" value="<?php echo get_sanitize_input($default2['de_settle_max_point']); ?>" id="de_settle_max_point" class="frm_input" size="10"> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_settle_point_unit">결제 포인트단위<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_settle_point_unit</span><?php } ?></label></th>
            <td>
                <?php echo help("주문 결제시 사용되는 포인트의 절사 단위를 설정합니다."); ?>
                <select id="de_settle_point_unit" name="de_settle_point_unit">
                    <option value="100" <?php echo get_selected($default2['de_settle_point_unit'], 100); ?>>100</option>
                    <option value="10"  <?php echo get_selected($default2['de_settle_point_unit'],  10); ?>>10</option>
                    <option value="1"   <?php echo get_selected($default2['de_settle_point_unit'],   1); ?>>1</option>
                </select> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_card_point">포인트부여<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_card_point [Y/N]</span><?php } ?></label></th>
            <td>
                <?php echo help("신용카드, 계좌이체, 휴대폰 결제시 포인트를 부여할지를 설정합니다. (기본값은 '아니오')"); ?>
                <select id="de_card_point" name="de_card_point">
                    <option value="N" <?php echo get_selected($default2['de_card_point'], 'N'); ?>>아니오</option>
                    <option value="Y" <?php echo get_selected($default2['de_card_point'], 'Y'); ?>>예</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_point_days">예약완료 포인트<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_point_days</span><?php } ?></label></th>
            <td>
                <?php echo help("예약자가 회원일 경우에만 예약완료시 포인트를 지급합니다. 예약취소 등을 고려하여 포인트를 지급할 적당한 기간을 입력하십시오. (기본값은 7일)\n0일로 설정하는 경우에는 주문완료와 동시에 포인트를 지급합니다."); ?>
                예약 완료 <input type="text" name="de_point_days" value="<?php echo get_sanitize_input($default2['de_point_days']); ?>" id="de_point_days" class="frm_input" size="2"> 일 이후에 포인트를 지급
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_pg_service">결제대행사<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_pg_service [토스=lg]</span><?php } ?></label></th>
            <td>
                <input type="hidden" name="de_pg_service" id="de_pg_service" value="<?php echo $default2['de_pg_service']; ?>" >
                <?php echo help('쇼핑몰에서 사용할 결제대행사를 선택합니다.'); ?>
                <ul class="de_pg_tab">
                    <li class="<?php if($default2['de_pg_service'] == 'lg') echo 'tab-current'; ?>"><a href="#lg_info_anchor" data-value="lg" title="토스페이먼츠 선택하기">토스페이먼츠</a></li>
                </ul>
            </td>
        </tr>
        <tr class="pg_info_fld lg_info_fld" id="lg_info_anchor">
            <th scope="row">
                <label for="de_lg_mid">토스페이먼츠 상점아이디<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_lg_mid</span><?php } ?></label><br>
                <a href="http://sir.kr/main/service/lg_pg.php" target="_blank" id="scf_lgreg" class="lg_btn">토스페이먼츠 신청하기</a>
            </th>
            <td>
                <span class="sitecode hidden">si_</span> <input type="text" name="de_lg_mid" value="<?php echo get_sanitize_input($default2['de_lg_mid']); ?>" id="de_lg_mid" class="frm_input code_input w-[120px]" size="10" maxlength="20"> 영문자, 숫자 혼용
            </td>
        </tr>
        <tr class="pg_info_fld lg_info_fld">
            <th scope="row"><label for="de_lg_mert_key">토스페이먼츠 MERT KEY<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_lg_mert_key</span><?php } ?></label></th>
            <td>
                <?php echo help("토스페이먼츠 상점MertKey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실 수 있습니다.\n예) 95160cce09854ef44d2edb2bfb05f9f3"); ?>
                <input type="text" name="de_lg_mert_key" value="<?php echo get_sanitize_input($default2['de_lg_mert_key']); ?>" id="de_lg_mert_key" class="frm_input " size="36" maxlength="50">
            </td>
        </tr>
        <tr>
            <th scope="row">에스크로 사용<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_escrow_use [Y/N]</span><?php } ?></th>
            <td>
                <?php echo help("에스크로 결제를 사용하시려면, 반드시 결제대행사 상점 관리자 페이지에서 에스크로 서비스를 신청하신 후 사용하셔야 합니다.\n에스크로 사용시 배송과의 연동은 되지 않으며 에스크로 결제만 지원됩니다."); ?>
                    <input type="radio" name="de_escrow_use" value="N" <?php echo $default2['de_escrow_use']=='N'?"checked":""; ?> id="de_escrow_use1">
                    <label for="de_escrow_use1">일반결제 사용</label>
                    <input type="radio" name="de_escrow_use" value="Y" <?php echo $default2['de_escrow_use']=='Y'?"checked":""; ?> id="de_escrow_use2">
                    <label for="de_escrow_use2"> 에스크로결제 사용</label>
            </td>
        </tr>
        <tr>
            <th scope="row">결제 테스트<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_card_test [Y/N]</span><?php } ?></th>
            <td>
                <?php echo help("PG사의 결제 테스트를 하실 경우에 체크하세요. 결제단위 최소 1,000원"); ?>
                <input type="radio" name="de_card_test" value="N" <?php echo $default2['de_card_test']=='N'?"checked":""; ?> id="de_card_test1">
                <label for="de_card_test1">실결제 </label>
                <input type="radio" name="de_card_test" value="Y" <?php echo $default2['de_card_test']=='Y'?"checked":""; ?> id="de_card_test2">
                <label for="de_card_test2">테스트결제</label>
                <div class="scf_cardtest lg_cardtest">
                    <a href="https://app.tosspayments.com/" target="_blank" class="btn_frmline">실결제 관리자</a>
                    <a href="https://pgweb.tosspayments.com/tmert" target="_blank" class="btn_frmline">테스트 관리자</a>
                </div>
                <div id="scf_cardtest_tip">
                    <strong>일반결제 사용시 테스트 결제</strong>
                    <dl>
                        <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                        <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                        <dt>가상계좌</dt><dd>1원 이상, 모든 은행이 테스트 되는 것은 아니며 "해당 은행 계좌 없음" 자주 발생함.<br>(광주은행, 하나은행)</dd>
                        <dt>휴대폰</dt><dd>1004원, 실결제가 되며 다음날 새벽에 일괄 취소됨</dd> 
                    </dl>
                    <strong>에스크로 사용시 테스트 결제</strong><br>
                    <dl>
                        <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                        <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                        <dt>가상계좌</dt><dd>1원 이상, 입금통보는 제대로 되지 않음.</dd>
                        <dt>휴대폰</dt><dd>테스트 지원되지 않음.</dd>
                    </dl>
                    <ul id="lg_cardtest_tip" class="scf_cardtest_tip_adm scf_cardtest_tip_adm_hide">
                        <li>테스트결제의 <a href="https://pgweb.tosspayments.com/tmert" target="_blank">상점관리자</a> 로그인 정보는 토스페이먼츠 상점아이디 첫 글자에 t를 추가해서 로그인하시기 바랍니다. 예) tsi_lguplus</li>
                    </ul>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_tax_flag_use">복합과세 결제<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_tax_flag_use [Y/N]</span><?php } ?></label></th>
            <td>
                 <?php echo help("복합과세(과세, 비과세) 결제를 사용하려면 체크하십시오.\n복합과세 결제를 사용하기 전 PG사에 별도로 결제 신청을 해주셔야 합니다. 사용시 PG사로 문의하여 주시기 바랍니다."); ?>
                <input type="checkbox" name="de_tax_flag_use" value="1" id="de_tax_flag_use"<?php echo $default2['de_tax_flag_use'] == 'Y'?' checked':''; ?>> 사용
            </td>
        </tr>
        </tbody>
        </table>
        <script>
        $('#scf_cardtest_tip').addClass('scf_cardtest_tip');
        $('<button type="button" class="scf_cardtest_btn btn_frmline">테스트결제 팁 더보기</button>').appendTo('.scf_cardtest');

        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $(".<?php echo $default2['de_pg_service']; ?>_cardtest").removeClass("scf_cardtest_hide");
        $("#<?php echo $default2['de_pg_service']; ?>_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
        </script>
    </div>
</section>


<script>
function byte_check(el_cont, el_byte)
{
    var cont = document.getElementById(el_cont);
    var bytes = document.getElementById(el_byte);
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';
    var limit_num = <?=$default2['de_sms_max_bytes']?>; //(jQuery("#cf_sms_type").val() == "LMS") ? 1500 : 80;

    if( $("input[name='cf_aligo_key']").length && $("input[name='cf_aligo_key']").val() ){
        limit_num = <?=$default2['de_sms_max_bytes']?>;
    }

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    //byte.value = cnt + ' / 80 bytes';
    bytes.innerHTML = cnt + ' / ' + limit_num +' bytes';

    if (cnt > limit_num) {
        exceed = cnt - limit_num;
        // alert('메시지 내용은 ' + limit_num +' 바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = cont.value;
        for (i=0; i<tmp.length; i++) {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > limit_num) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        cont.value = tmp;
        //byte.value = xcnt + ' / 80 bytes';
        bytes.innerHTML = xcnt + ' / ' + limit_num +' bytes';
        return;
    }
}
</script>

<section id="anc_scf_sms" >
    <h2 class="h2_frm">SMS 설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>SMS 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_sms_use">SMS 사용<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_sms_use ['' | 'aligo']</span><?php } ?></label></th>
            <td>
                <?php echo help("SMS  서비스 회사를 선택하십시오. 서비스 회사를 선택하지 않으면, SMS 발송 기능이 동작하지 않습니다."); ?>
                <select id="de_sms_use" name="de_sms_use">
                    <option value="" <?php echo get_selected($default2['de_sms_use'], ''); ?>>사용안함</option>
                    <option value="aligo" <?php echo get_selected($default2['de_sms_use'], 'aligo'); ?>>알리고</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_sms_hp">관리자 휴대폰번호<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_sms_hp</span><?php } ?></label></th>
            <td>
                <?php echo help("예약결제시 플랫폼관리자가 문자메세지를 받아볼 번호를 숫자만으로 입력하세요. 예) 0101234567"); ?>
                <input type="text" name="de_sms_hp" value="<?php echo get_sanitize_input($default2['de_sms_hp']); ?>" id="de_sms_hp" class="frm_input" size="20">
            </td>
        </tr>
        <tr class="aligo_version">
            <th scope="row"><label for="de_aligo_user_id">알리고 사용자아이디<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_aligo_user_id</span><?php } ?></label></th>
            <td>
                <?php echo help("알리고에서 사용하시는 회원아이디를 입력합니다."); ?>
                <input type="text" name="de_aligo_user_id" value="<?php echo get_sanitize_input($default2['de_aligo_user_id']??''); ?>" id="de_aligo_user_id" class="frm_input" size="20">
            </td>
        </tr>
        <tr class="aligo_json_version">
            <th scope="row"><label for="de_aligo_key">알리고 Key값<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_aligo_key</span><?php } ?></label></th>
            <td>
                <input type="text" name="de_aligo_key" value="<?php echo get_sanitize_input($default2['de_aligo_key']); ?>" id="de_aligo_key" class="frm_input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row">알리고 SMS 신청<br>회원가입</th>
            <td>
                <?php echo help("알리고 회원가입은 아래 필크를 클릭하세요."); ?>
                <a href="https://smartsms.aligo.in/join.html" target="_blank" class="btn_frmline">알리고 회원가입</a>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_sms_hp">문자 최대입력 사이즈<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_sms_max_bytes</span><?php } ?></label></th>
            <td>
                <p><?php echo help("문자전송시 입력 가능한 최대 문자용량을 입력하세요. 예) 3000"); ?></p>
                <p><?php echo help('주의! '.(isset($default2['de_sms_max_bytes'])?$default2['de_sms_max_bytes']:0).' bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)'); ?></p>
                <input type="text" name="de_sms_max_bytes" value="<?php echo get_sanitize_input($default2['de_sms_max_bytes']); ?>" id="de_sms_max_bytes" class="frm_input" size="20"> bytes
            </td>
        </tr>
         </tbody>
        </table>
    </div>

    <section id="scf_sms_pre">
        <h3>사전에 정의된 SMS프리셋</h3>
        <div class="local_desc01 local_desc">
            <dl>
                <dt>1. 일반회원가입시 등록회원에게 발송</dt>
                <dd>{이름} {플랫폼명} {회원아이디}</dd>
                <dt>2. 예약결제시 예약자에게 발송</dt>
                <dd>{이름} {예약결제번호} {예약결제금액} {플랫폼명}</dd>
                <dt>3. 예약결제시 가맹점관리자에게 발송</dt>
                <dd>{가맹점명} {이름} {예약내용} {플랫폼명}</dd>
                <dt>4. 입금확인시 예약자에게 발송</dt>
                <dd>{이름} {예약결제번호} {입금액} {플랫폼명}</dd>
                <dt>5. 예약취소시 예약자에게 발송</dt>
                <dd>{이름} {취소내용} {플랫폼명}</dd>
                <dt>6. 예약취소시 가맹점관리자에게 발송</dt>
                <dd>{가맹점명} {이름} {취소내용} {플랫폼명}</dd>
                <dt>7. 예약자에게 출발권고 발송</dt>
                <dd>{이름} {가맹점명} {가맹점별예약일시} {출발권고내용} {플랫폼명}</dd>
                <dt>8. 인증코드를 회원에게 발송</dt>
                <dd>{플랫폼명} {인증코드}</dd>
                <dt>9. 개인결제청구를 예약자에게 발송</dt>
                <dd>{가맹점명} {이름} {예약일시} {개인결제청구아이디} {개인결제청구내용} {플랫폼명}</dd>
                <dt>10. 테스트 문자 발송</dt>
                <dd>{이름} {테스트내용} {플랫폼명}</dd>
            </dl>
            <p class="text-blue-900">{OOO} 부분을 적절한 데이터로 치환해서 내용을 발송합니다.</p>
            <p><?php echo help('주의! 3000 bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)'); ?></p>
        </div>

        <div id="scf_sms">
            <?php
            $scf_sms_title = array (
                1=>"일반회원가입시 회원에게 발송", 
                2=>"예약결제시 예약자에게 발송", 
                3=>"예약결제시 가맹점관리자에게 발송", 
                4=>"입금확인시 예약자에게 발송", 
                5=>"예약취소시 예약자에게 발송",
                6=>"예약취소시 가맹점관리자에게 발송",
                7=>"예약자에게 출발권고 발송",
                8=>"인증코드를 회원에게 발송",
                9=>"개인결제청구를 회원에게 발송",
                10=>"테스트 문자 발송"
            );
            for ($i=1; $i<=10; $i++) {
            ?>
            <section class="scf_sms_box mt-7">
                <h4><?php echo $scf_sms_title[$i]; ?></h4>
                <?php if($is_dev_manager) { ?><span class="text-red-800">de_sms_use<?=$i?> [Y/N]</span><br><?php } ?>
                <input type="checkbox" name="de_sms_use<?php echo $i; ?>" value="1" id="de_sms_use<?php echo $i; ?>" <?php echo ($default2["de_sms_use".$i] == 'Y' ? " checked" : ""); ?>>
                <label for="de_sms_use<?php echo $i; ?>"><span class="sound_only"><?php echo $scf_sms_title[$i]; ?></span>사용</label>
                <?php if($is_dev_manager) { ?><br><span class="text-red-800">de_sms_cont<?=$i?></span><?php } ?>
                <div class="scf_sms_img">
                    <textarea id="de_sms_cont<?php echo $i; ?>" name="de_sms_cont<?php echo $i; ?>" ONKEYUP="byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');"><?php echo html_purifier($default2['de_sms_cont'.$i]); ?></textarea>
                </div>
                <span id="byte<?php echo $i; ?>" class="scf_sms_cnt">0 / <?=$default2['de_sms_max_bytes']?> 바이트</span>
            </section>

            <script>
            byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');
            </script>
            <?php } ?>
        </div>
    </section>

</section>

<section id="anc_scf_etc" >
    <h2 class="h2_frm">기타설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기타설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_cart_keep_term">장바구니 보관기간<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_cart_keep_term</span><?php } ?></label></th>
            <td>
                 <?php echo help("장바구니 일정의 보관 기간을 설정하십시오."); ?>
                <input type="text" name="de_cart_keep_term" value="<?php echo get_sanitize_input($default2['de_cart_keep_term']); ?>" id="de_cart_keep_term" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_guest_cart_use">비회원 장바구니<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_guest_cart_use [Y/N]</span><?php } ?></label></th>
            <td>
                 <?php echo help("비회원 장바구니 기능을 사용하려면 체크하십시오."); ?>
                <input type="checkbox" name="de_guest_cart_use" value="1" id="de_guest_cart_use"<?php echo $default2['de_guest_cart_use'] == 'Y'?' checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_schedule_max_day">최대일정날짜수<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_schedule_max_date</span><?php } ?></label></th>
            <td>
                 <?php echo help("일정을 구성하는 최대 날짜수(예를들어 5 로 지정하면:5일치의 일정까지만 등록이 가능합니다."); ?>
                <?php echo tms_input_range('de_schedule_max_day',$default2['de_schedule_max_day']??'5',$w,1,10,1,'40',48,'일'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">일정날짜별 코스색상<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_selected_course1 ~ 5 [#333333]</span><?php } ?></th>
            <td>
                <?php echo $default2['de_schedule_max_day']; ?>
                <?php echo tms_input_color('de_selected_course1',$default2['de_selected_course1']??'#cccccc',$w); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">일정선택업체별 색상<?php if($is_dev_manager) { ?><br><span class="text-red-800">de_selected_com1 ~ 100 [#333333]</span><?php } ?></th>
            <td>
                
            </td>
        </tr>
         </tbody>
        </table>
    </div>

</section>


<div class="btn_fixed_top">
    <!-- <a href=" <?php //echo G5_SHOP_URL; ?>" class="btn btn_02">쇼핑몰</a> -->
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
function fconfig_check(f)
{
    <?php echo get_editor_js('de_baesong_content'); ?>
    <?php echo get_editor_js('de_change_content'); ?>
    <?php echo get_editor_js('de_guest_privacy'); ?>
    
    var msg = "",
        pg_msg = "";

    if ( f.de_pg_service.value == "lg" ) {
        if( f.de_lg_mid.value && f.de_lg_mert_key.value && parseInt(f.de_card_test.value) > 0 ){
            pg_msg = "토스페이먼츠";
        }
    }

    if( pg_msg ){
        msg += "(주의!) "+pg_msg+" 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.\n쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.\n실결제로 변경하려면 결제설정 탭 -> 결제 테스트에서 실결제를 선택해 주세요.\n정말로 테스트결제로 설정하시겠습니까?";
    }

    if( msg ){
        if (confirm(msg)){
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

$(function() {

    $(document).ready(function () {
        
        $("#de_global_nhnkcp_naverpay").on("click", function(e){
            if ( $(this).prop('checked') ) {
                $("#de_easy_nhnkcp_naverpay").prop('checked', true);
            }
        });

        function hash_goto_scroll(hash){
            var $elem = hash ? $("#"+hash) : $('#' + window.location.hash.replace('#', ''));
            if($elem.length) {

                var admin_head_height = $("#hd_top").height() + $("#container_title").height() + 30;

                $('html, body').animate({
                    scrollTop: ($elem.offset().top - admin_head_height) + 'px'
                }, 500, 'swing');
            }
        }

        hash_goto_scroll();
        
        $(document).on("click", ".pg_test_conf_link", function(e){
            e.preventDefault();

            var str_hash = this.href.split("#")[1];

            if( str_hash ){
                hash_goto_scroll(str_hash);
            }
        });
    });

    $(document).on("click", ".de_pg_tab a", function(e){

        var pg = $(this).attr("data-value"),
            class_name = "tab-current";

        $("#de_pg_service").val(pg);
        $(this).parent("li").addClass(class_name).siblings().removeClass(class_name);

        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
    });

    $("#de_pg_service").on("change", function() {
        var pg = $(this).val();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
    });

    $(".scf_cardtest_btn").bind("click", function() {
        var $cf_cardtest_tip = $("#scf_cardtest_tip");
        var $cf_cardtest_btn = $(".scf_cardtest_btn");

        $cf_cardtest_tip.toggle();

        if($cf_cardtest_tip.is(":visible")) {
            $cf_cardtest_btn.text("테스트결제 팁 닫기");
        } else {
            $cf_cardtest_btn.text("테스트결제 팁 더보기");
        }
    });
});
</script>

<?php

// LG의 경우 log 디렉토리 체크
if($default2['de_pg_service'] == 'lg') {
    $log_path = G5_LGXPAY_PATH.'/lgdacom/log';

    try {
        if( ! is_dir($log_path) && is_writable(G5_LGXPAY_PATH.'/lgdacom/') ){
            @mkdir($log_path, G5_DIR_PERMISSION);
            @chmod($log_path, G5_DIR_PERMISSION);
        }
    } catch(Exception $e) {
    }

    if(!is_dir($log_path)) {

        if( is_writable(G5_LGXPAY_PATH.'/lgdacom/') ){
            // 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
            @mkdir($log_path, G5_DIR_PERMISSION);
            @chmod($log_path, G5_DIR_PERMISSION);
        }

        if(!is_dir($log_path)){
            echo '<script>'.PHP_EOL;
            echo 'alert("'.str_replace(G5_PATH.'/', '', G5_LGXPAY_PATH).'/lgdacom 폴더 안에 log 폴더를 생성하신 후 쓰기권한을 부여해 주십시오.\n> mkdir log\n> chmod 707 log");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }
    }

    if(is_writable($log_path)) {
        if( function_exists('check_log_folder') ){
            check_log_folder($log_path);
        }
    } else {
        echo '<script>'.PHP_EOL;
        echo 'alert("'.str_replace(G5_PATH.'/', '',$log_path).' 폴더에 쓰기권한을 부여해 주십시오.\n> chmod 707 log");'.PHP_EOL;
        echo '</script>'.PHP_EOL;
    }
}

include_once (G5_ADMIN_PATH.'/admin.tail.php');