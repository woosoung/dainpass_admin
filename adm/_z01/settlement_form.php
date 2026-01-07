<?php
$sub_menu = "920450";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

$w = isset($w) ? (string)$w : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$ssl_id = isset($ssl_id) ? (int)$ssl_id : (isset($_REQUEST['ssl_id']) ? (int)$_REQUEST['ssl_id'] : 0);

$settlement = array();
$settlement_list = array();
$is_readonly = false;
$is_edit_mode = false;

if ($w == 'u') {
    $is_edit_mode = true;
    
    // shop_settlement_log 조회
    $log_sql = " SELECT * FROM shop_settlement_log WHERE ssl_id = '{$ssl_id}' LIMIT 1 ";
    $log_result = sql_fetch_pg($log_sql);
    
    if (!$log_result || !$log_result['ssl_id']) {
        alert('존재하지 않는 정산 자료입니다.');
    }
    
    $settlement['ssl_id'] = $log_result['ssl_id'];
    $settlement['shop_id'] = $log_result['shop_id'];
    $settlement['settlement_type'] = $log_result['settlement_type'];
    $settlement['settlement_amount'] = $log_result['settlement_amount'];
    $settlement['settlement_start_at'] = $log_result['settlement_start_at'];
    $settlement['settlement_end_at'] = $log_result['settlement_end_at'];
    
    // 자동 정산은 읽기 전용
    if ($log_result['settlement_type'] == 'AUTO') {
        $is_readonly = true;
    }
    
    // 첫 번째 shop_settlements 레코드 조회
    $first_sql = " SELECT * FROM shop_settlements WHERE ssl_id = '{$ssl_id}' ORDER BY settlement_id LIMIT 1 ";
    $first_result = sql_fetch_pg($first_sql);
    
    if ($first_result) {
        $settlement['settlement_id'] = $first_result['settlement_id'];
        $settlement['pay_flag'] = $first_result['pay_flag'];
        $settlement['shopdetail_id'] = $first_result['shopdetail_id'];
        $settlement['personal_id'] = $first_result['personal_id'];
        $settlement['payment_id'] = $first_result['payment_id'];
        $settlement['appointment_datetime'] = $first_result['appointment_datetime'];
        $settlement['total_payment_amount'] = $first_result['total_payment_amount'];
        $settlement['cancel_amount'] = $first_result['cancel_amount'];
        $settlement['net_settlement_amount'] = $first_result['net_settlement_amount'];
        $settlement['settlement_status'] = $first_result['settlement_status'];
    }
    
    // 관련 shop_settlements 목록 조회
    $list_sql = " SELECT 
                    ss.settlement_id,
                    ss.pay_flag,
                    ss.payment_id,
                    ss.appointment_datetime,
                    ss.total_payment_amount,
                    ss.cancel_amount,
                    ss.net_settlement_amount,
                    ss.created_at
                 FROM shop_settlements ss
                 WHERE ss.ssl_id = '{$ssl_id}'
                 ORDER BY ss.appointment_datetime DESC, ss.created_at DESC ";
    $list_result = sql_query_pg($list_sql);
    
    if ($list_result && is_object($list_result) && isset($list_result->result)) {
        for($i=0; $row = sql_fetch_array_pg($list_result->result); $i++) {
            $settlement_list[] = $row;
        }
    }
    
    // 가맹점 정보 조회
    if ($settlement['shop_id']) {
        $shop_sql = " SELECT name, shop_name FROM shop WHERE shop_id = '{$settlement['shop_id']}' LIMIT 1 ";
        $shop_result = sql_fetch_pg($shop_sql);
        if ($shop_result) {
            $settlement['shop_name'] = $shop_result['shop_name'] ?: $shop_result['name'];
        }
    }
}

$html_title = ($w == '') ? '추가' : '수정';
$g5['title'] = '정산관리 '.$html_title;

// 결제 유형 한글 변환 배열
$pay_flag_arr = array(
    'GENERAL' => '일반결제',
    'PERSONAL' => '개인결제'
);

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<script src="<?php echo G5_Z_URL ?>/js/settlement_form.js.php"></script>
<div class="local_desc01 local_desc">
    <p>정산 정보를 관리해 주세요.<?php if ($is_readonly) echo ' (자동 정산 데이터는 수정할 수 없습니다.)'; ?></p>
</div>

<form name="form01" id="form01" action="./settlement_form_update.php" onsubmit="return form01_submit(this);" method="post">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="ssl_id" value="<?php echo $settlement['ssl_id'] ?? '' ?>">
    <input type="hidden" name="settlement_id" value="<?php echo $settlement['settlement_id'] ?? '' ?>">
    <input type="hidden" name="settlement_type" id="settlement_type" value="<?php echo $settlement['settlement_type'] ?? 'MANUAL' ?>">
    
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4" style="width:10%;">
                <col style="width:40%;">
                <col class="grid_4" style="width:10%;">
                <col style="width:40%;">
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">가맹점<strong class="sound_only">필수</strong></th>
                <td>
                    <?php if ($w == '' && !$is_readonly) { ?>
                        <input type="hidden" name="shop_id" id="shop_id" value="<?=$settlement['shop_id'] ?? ''?>">
                        <input type="text" name="shop_name_display" id="shop_name_display" value="" readonly class="frm_input" style="width:200px;" placeholder="가맹점을 선택하세요">
                        <button type="button" onclick="open_shop_popup();" class="btn_01 btn">가맹점선택</button>
                    <?php } else { ?>
                        <input type="hidden" name="shop_id" id="shop_id" value="<?=$settlement['shop_id'] ?? ''?>">
                        <input type="text" name="shop_name_display" id="shop_name_display" value="<?=get_text($settlement['shop_name'] ?? '')?>" readonly class="frm_input" style="width:300px;">
                    <?php } ?>
                </td>
                <th scope="row">정산 유형</th>
                <td>
                    <input type="text" value="<?php echo ($settlement['settlement_type'] ?? 'MANUAL') == 'AUTO' ? '자동' : '수동' ?>" readonly class="frm_input" style="width:200px;">
                </td>
            </tr>
            <tr>
                <th scope="row">결제 지정</th>
                <td>
                    <?php if ($w == '' && !$is_readonly) { ?>
                        <?php echo help("가맹점을 선택한 후, 결제 정보를 선택하면 관련 정보가 자동으로 채워집니다."); ?>
                        <select name="payment_id" id="payment_id" class="frm_input" style="width:300px;" disabled>
                            <option value="">가맹점을 먼저 선택해주세요</option>
                        </select>
                        <div id="payment_info" style="margin-top:10px; padding:10px; background-color:#f9f9f9; display:none;"></div>
                    <?php } else { ?>
                        <?php if ($is_edit_mode) { ?>
                            <input type="text" name="payment_id" id="payment_id" value="<?=$settlement['payment_id'] ?? ''?>" placeholder="결제 ID" class="frm_input" style="width:200px;" readonly>
                        <?php } else { ?>
                            <?php echo help("결제 ID를 입력하면 관련 정보가 자동으로 채워집니다."); ?>
                            <input type="text" name="payment_id" id="payment_id" value="<?=$settlement['payment_id'] ?? ''?>" placeholder="결제 ID (선택사항)" class="frm_input" style="width:200px;"<?php echo $is_readonly ? ' readonly' : ''; ?>>
                        <?php } ?>
                        <div id="payment_info" style="margin-top:10px; padding:10px; background-color:#f9f9f9; display:none;"></div>
                    <?php } ?>
                </td>
                <th scope="row">결제 유형<strong class="sound_only">필수</strong></th>
                <td>
                    <select name="pay_flag" id="pay_flag" class="frm_input"<?php echo ($is_readonly || $is_edit_mode) ? ' disabled' : ''; ?>>
                        <option value="">선택</option>
                        <option value="GENERAL"<?php echo get_selected($settlement['pay_flag'] ?? '', "GENERAL"); ?>>일반</option>
                        <option value="PERSONAL"<?php echo get_selected($settlement['pay_flag'] ?? '', "PERSONAL"); ?>>개인</option>
                    </select>
                    <?php if ($is_edit_mode) { ?>
                        <input type="hidden" name="pay_flag" value="<?=$settlement['pay_flag'] ?? ''?>">
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row">예약 내역 ID</th>
                <td>
                    <input type="text" name="shopdetail_id" id="shopdetail_id" value="<?=$settlement['shopdetail_id'] ?? ''?>" class="frm_input"<?php echo ($is_readonly || ($is_edit_mode && isset($settlement['shopdetail_id']) && $settlement['shopdetail_id'])) ? ' readonly' : ''; ?>>
                </td>
                <th scope="row">개인 결제 ID</th>
                <td>
                    <input type="text" name="personal_id" id="personal_id" value="<?=$settlement['personal_id'] ?? ''?>" class="frm_input"<?php echo ($is_readonly || ($is_edit_mode && isset($settlement['personal_id']) && $settlement['personal_id'])) ? ' readonly' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th scope="row">예약일시</th>
                <td>
                    <input type="text" name="appointment_datetime" id="appointment_datetime" value="<?=$settlement['appointment_datetime'] ?? ''?>" class="frm_input" style="width:200px;"<?php echo $is_readonly ? ' readonly' : ''; ?>>
                </td>
                <th scope="row">결제금액<strong class="sound_only">필수</strong></th>
                <td>
                    <input type="number" name="total_payment_amount" id="total_payment_amount" value="<?=$settlement['total_payment_amount'] ?? ''?>" class="frm_input" step="1" min="0"<?php echo $is_readonly ? ' readonly' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th scope="row">취소금액</th>
                <td>
                    <input type="number" name="cancel_amount" id="cancel_amount" value="<?=$settlement['cancel_amount'] ?? 0?>" class="frm_input" step="1" min="0"<?php echo $is_readonly ? ' readonly' : ''; ?>>
                </td>
                <th scope="row">정산금액<strong class="sound_only">필수</strong></th>
                <td>
                    <input type="number" name="net_settlement_amount" id="net_settlement_amount" value="<?=$settlement['net_settlement_amount'] ?? ''?>" class="frm_input" step="1" min="0"<?php echo $is_readonly ? ' readonly' : ''; ?>>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    
    <?php if ($w == 'u' && !empty($settlement_list)) { ?>
    <div class="tbl_head01 tbl_wrap" style="margin-top:30px;">
        <h2>관련 정산 내역</h2>
        <table>
            <caption>관련 정산 내역 목록</caption>
            <thead>
                <tr>
                    <th scope="col">정산ID</th>
                    <th scope="col">결제유형</th>
                    <th scope="col">결제ID</th>
                    <th scope="col">예약일시</th>
                    <th scope="col">결제금액</th>
                    <th scope="col">취소금액</th>
                    <th scope="col">정산금액</th>
                    <th scope="col">생성일시</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settlement_list as $item) { ?>
                <tr>
                    <td class="td_num"><?=$item['settlement_id']?></td>
                    <td class="td_left"><?=$pay_flag_arr[$item['pay_flag']] ?? $item['pay_flag']?></td>
                    <td class="td_num"><?=$item['payment_id']?></td>
                    <td class="td_datetime w-[200px]"><?=$item['appointment_datetime'] ? date('Y-m-d H:i', strtotime($item['appointment_datetime'])) : ''?></td>
                    <td class="td_num"><?=number_format($item['total_payment_amount'])?></td>
                    <td class="td_num"><?=number_format($item['cancel_amount'])?></td>
                    <td class="td_num"><?=number_format($item['net_settlement_amount'])?></td>
                    <td class="td_datetime w-[200px]"><?=$item['created_at'] ? date('Y-m-d H:i', strtotime($item['created_at'])) : ''?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    
    <div class="btn_fixed_top">
        <a href="./settlement_list.php" class="btn_02 btn">목록</a>
        <?php if (!$is_readonly) { ?>
        <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
        <?php } ?>
    </div>
</form>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>

