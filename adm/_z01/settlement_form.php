<?php
$sub_menu = "920450";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

$form_input = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$settlement = (isset($settlement) && is_array($settlement)) ? $settlement : [];

$w = isset($w) ? (string)$w : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$ssl_id = isset($ssl_id) ? (int)$ssl_id : (isset($_REQUEST['ssl_id']) ? (int)$_REQUEST['ssl_id'] : 0);

if ($w == '') {
    $ssl_id = 0;
    $settlement['settlement_type'] = 'MANUAL';
    $html_title = '추가';
    $is_readonly = false;
    $is_edit_mode = false;
}
else if ($w == 'u') {
    // shop_settlement_log 조회
    $ssl = sql_fetch_pg(" SELECT * FROM shop_settlement_log WHERE ssl_id = '{$ssl_id}' ");
    
    if (!$ssl || !$ssl['ssl_id']) {
        alert('존재하지 않는 정산 로그 자료입니다.');
    }

    // shop_settlements 조회 (첫 번째 레코드) - 선택적 조회
    $ss = sql_fetch_pg(" SELECT * FROM shop_settlements WHERE ssl_id = '{$ssl_id}' ORDER BY settlement_id LIMIT 1 ");
    
    // shop_settlements가 없어도 shop_settlement_log가 존재하면 수정 가능
    // shop_settlements는 1:N 관계이므로 없을 수 있음
    if ($ss && isset($ss['settlement_id'])) {
        $settlement = array_merge($ssl, $ss);
    } else {
        // shop_settlements가 없는 경우 shop_settlement_log만 사용
        $settlement = $ssl;
    }

    // 자동 정산은 수정 불가
    $is_readonly = ($ssl['settlement_type'] === 'AUTO');
    // 수정 모드에서는 결제금액, 취소금액, 정산금액만 수정 가능
    $is_edit_mode = true;
    
    $html_title = '수정';
    
    // 관련 정산 목록 조회 (하단 표시용)
    $related_settlements = array();
    $related_sql = " SELECT 
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
    $related_result = sql_query_pg($related_sql);
    if ($related_result && is_object($related_result) && isset($related_result->result)) {
        while ($related_row = sql_fetch_array_pg($related_result->result)) {
            $related_settlements[] = $related_row;
        }
    }
}
else {
    alert('제대로 된 값이 넘어오지 않았습니다.');
}

// 가맹점 목록 조회
$shop_list = array();
$shop_sql = " SELECT shop_id, name, shop_name FROM {$g5['shop_table']} WHERE status != 'closed' ORDER BY name ";
$shop_result = sql_query_pg($shop_sql);
if ($shop_result && is_object($shop_result) && isset($shop_result->result)) {
    while ($shop_row = sql_fetch_array_pg($shop_result->result)) {
        $shop_list[] = $shop_row;
    }
}

$settlement_type_arr = array(
    'AUTO' => '자동',
    'MANUAL' => '수동'
);

$pay_flag_arr = array(
    'GENERAL' => '일반',
    'PERSONAL' => '개인'
);

$g5['title'] = '정산관리 '.$html_title;
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<script>
let shopList = <?=json_encode($shop_list)?>;
</script>
<form name="form01" id="form01" action="./settlement_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="ssl_id" value="<?php echo $ssl_id; ?>">
<input type="hidden" name="settlement_id" value="<?php echo $settlement['settlement_id'] ?? ''; ?>">
<?=$form_input??''?>
<div class="local_desc01 local_desc">
    <p>정산 정보를 관리해 주세요.<?php if ($is_readonly) { ?><strong class="text-red-600"> (자동 정산 데이터는 수정할 수 없습니다.)</strong><?php } ?></p>
</div>

<?php if ($is_readonly) { ?>
<div class="alert alert-warning" style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin-bottom: 20px;">
    <strong>주의:</strong> 자동 정산 데이터는 수정할 수 없습니다. 예외적인 상황이 발생한 경우에만 수동 정산을 등록해 주세요.
</div>
<?php } ?>

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
		<th scope="row">정산 유형<strong class="sound_only">필수</strong></th>
		<td>
			<?php if ($w == '') { ?>
				<input type="text" value="수동 (MANUAL)" readonly class="frm_input readonly" style="background-color:#f5f5f5;">
				<input type="hidden" name="settlement_type" value="MANUAL">
			<?php } else { ?>
				<input type="text" value="<?=$settlement_type_arr[$settlement['settlement_type']] ?? $settlement['settlement_type']?> (<?=$settlement['settlement_type']?>)" readonly class="frm_input readonly" style="background-color:#f5f5f5;">
				<input type="hidden" name="settlement_type" value="<?=$settlement['settlement_type']?>">
			<?php } ?>
		</td>
		<th scope="row">업체 선택<strong class="sound_only">필수</strong></th>
		<td>
			<?php 
			// 수정 모드이거나 자동 정산인 경우 가맹점 선택 불가
			$shop_select_disabled = ($w == 'u' || $is_readonly);
			
			if ($shop_select_disabled) { ?>
				<input type="text" id="shop_name_display" value="<?php 
					$selected_shop_name = '';
					if (isset($settlement['shop_id']) && !empty($shop_list)) {
						foreach ($shop_list as $shop_item) {
							if ($shop_item['shop_id'] == $settlement['shop_id']) {
								$selected_shop_name = !empty($shop_item['name']) ? $shop_item['name'] : $shop_item['shop_name'];
								break;
							}
						}
					}
					echo htmlspecialchars($selected_shop_name);
				?>" readonly class="frm_input readonly" style="background-color:#f5f5f5; width:200px;">
				<input type="hidden" name="shop_id" id="shop_id" value="<?=$settlement['shop_id']?>">
			<?php } else { ?>
				<button type="button" onclick="open_shop_popup();" class="btn_01 btn">가맹점 선택</button>
				<input type="text" name="shop_name_display" id="shop_name_display" value="<?php 
					$selected_shop_name = '';
					if (isset($settlement['shop_id']) && !empty($shop_list)) {
						foreach ($shop_list as $shop_item) {
							if ($shop_item['shop_id'] == $settlement['shop_id']) {
								$selected_shop_name = !empty($shop_item['name']) ? $shop_item['name'] : $shop_item['shop_name'];
								break;
							}
						}
					}
					echo htmlspecialchars($selected_shop_name);
				?>" readonly class="frm_input" style="width: 200px; margin-left: 10px;" placeholder="가맹점을 선택하세요">
				<input type="hidden" name="shop_id" id="shop_id" value="<?=$settlement['shop_id'] ?? ''?>">
			<?php } ?>
		</td>
	</tr>
	<tr>
		<th scope="row">결제 지정</th>
		<td colspan="3">
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
					<button type="button" id="btn_load_payment" class="btn_02 btn"<?php echo $is_readonly ? ' disabled' : ''; ?>>결제 정보 불러오기</button>
				<?php } ?>
				<div id="payment_info" style="margin-top:10px; padding:10px; background-color:#f9f9f9; display:none;"></div>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<th scope="row">결제 유형<strong class="sound_only">필수</strong></th>
		<td>
			<select name="pay_flag" id="pay_flag" class="frm_input"<?php echo ($is_readonly || $is_edit_mode) ? ' disabled' : ''; ?> required>
				<option value="">::선택::</option>
				<option value="GENERAL"<?php echo get_selected($settlement['pay_flag'] ?? '', "GENERAL"); ?>>일반 (GENERAL)</option>
				<option value="PERSONAL"<?php echo get_selected($settlement['pay_flag'] ?? '', "PERSONAL"); ?>>개인 (PERSONAL)</option>
			</select>
			<?php if ($is_readonly || $is_edit_mode) { ?><input type="hidden" name="pay_flag" value="<?=$settlement['pay_flag']?>"><?php } ?>
		</td>
		<th scope="row">예약 내역 ID</th>
		<td>
			<input type="text" name="shopdetail_id" id="shopdetail_id" value="<?=$settlement['shopdetail_id'] ?? ''?>" class="frm_input"<?php echo ($is_readonly || $is_edit_mode) ? ' readonly' : ''; ?>>
		</td>
	</tr>
	<tr>
		<th scope="row">개인 결제 ID</th>
		<td>
			<input type="text" name="personal_id" id="personal_id" value="<?=$settlement['personal_id'] ?? ''?>" class="frm_input"<?php echo ($is_readonly || $is_edit_mode) ? ' readonly' : ''; ?>>
			<?php echo help("결제 유형이 '개인 (PERSONAL)'일 때만 사용됩니다."); ?>
		</td>
		<th scope="row">예약일시</th>
		<td>
			<input type="datetime-local" name="appointment_datetime" id="appointment_datetime" value="<?php 
				if (!empty($settlement['appointment_datetime'])) {
					echo date('Y-m-d\TH:i', strtotime($settlement['appointment_datetime']));
				}
			?>" class="frm_input"<?php echo ($is_readonly || $is_edit_mode) ? ' readonly' : ''; ?>>
		</td>
	</tr>
	<tr>
		<th scope="row">결제금액<strong class="sound_only">필수</strong></th>
		<td>
			<input type="number" name="total_payment_amount" id="total_payment_amount" value="<?=$settlement['total_payment_amount'] ?? 0?>" step="0.01" min="0" class="frm_input text-right"<?php echo $is_readonly ? ' readonly' : ''; ?> required>
			<span>원</span>
		</td>
		<th scope="row">취소금액</th>
		<td>
			<input type="number" name="cancel_amount" id="cancel_amount" value="<?=$settlement['cancel_amount'] ?? 0?>" step="0.01" min="0" class="frm_input text-right"<?php echo $is_readonly ? ' readonly' : ''; ?>>
			<span>원</span>
		</td>
	</tr>
	<tr>
		<th scope="row">정산금액<strong class="sound_only">필수</strong></th>
		<td colspan="3">
			<input type="number" name="net_settlement_amount" id="net_settlement_amount" value="<?=$settlement['net_settlement_amount'] ?? 0?>" step="1" min="0" class="frm_input text-right" style="width:200px;"<?php echo $is_readonly ? ' readonly' : ''; ?> required>
			<span>원</span>
			<?php echo help("정산금액 = 결제금액 - 취소금액 (자동 계산됩니다)"); ?>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<?php if ($w == 'u' && !empty($related_settlements)) { ?>
<div class="tbl_head01 tbl_wrap" style="margin-top:30px;">
	<h3>관련 정산 내역</h3>
	<table class="table table-bordered table-condensed">
		<caption>해당 정산 로그에 연결된 정산 내역 목록</caption>
		<thead>
			<tr class="success">
				<th scope="col">정산ID</th>
				<th scope="col">결제유형</th>
				<th scope="col">결제ID</th>
				<th scope="col">예약일시</th>
				<th scope="col" class="td_right">결제금액</th>
				<th scope="col" class="td_right">취소금액</th>
				<th scope="col" class="td_right">정산금액</th>
				<th scope="col">생성일시</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($related_settlements as $idx => $rel_settlement) { ?>
			<tr class="bg<?=$idx%2?>">
				<td><?=$rel_settlement['settlement_id']?></td>
				<td><?=$pay_flag_arr[$rel_settlement['pay_flag']] ?? $rel_settlement['pay_flag']?></td>
				<td><?=$rel_settlement['payment_id'] ?? '-'?></td>
				<td class="font_size_8">
					<?php 
					if (!empty($rel_settlement['appointment_datetime'])) {
						echo date('Y-m-d H:i', strtotime($rel_settlement['appointment_datetime']));
					} else {
						echo '-';
					}
					?>
				</td>
				<td class="td_right"><?=number_format($rel_settlement['total_payment_amount'] ?? 0)?></td>
				<td class="td_right"><?=number_format($rel_settlement['cancel_amount'] ?? 0)?></td>
				<td class="td_right"><strong><?=number_format($rel_settlement['net_settlement_amount'] ?? 0)?></strong></td>
				<td class="font_size_8">
					<?php 
					if (!empty($rel_settlement['created_at'])) {
						echo date('Y-m-d H:i', strtotime($rel_settlement['created_at']));
					} else {
						echo '-';
					}
					?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } ?>

<div class="btn_fixed_top">
    <a href="./settlement_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <?php if (!$is_readonly) { ?>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
    <?php } ?>
</div>
</form>

<?php
include_once('./js/settlement_form.js.php');
include_once (G5_ADMIN_PATH.'/admin.tail.php');

