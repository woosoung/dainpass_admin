<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;
$shop_info = null;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        if ($mb_1_value === '0' || $mb_1_value === '') {
            $g5['title'] = '개인결제관리';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, shop_name, name, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_info = $shop_row;
            } else {
                $g5['title'] = '개인결제관리';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

if (!$has_access) {
    $g5['title'] = '개인결제관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'w');

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;
$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';

$pp = array(
    'personal_id' => 0,
    'order_id' => '',
    'shop_id' => $shop_id,
    'shopdetail_id' => null,
    'user_id' => '',
    'name' => '',
    'reason' => '',
    'amount' => 0,
    'status' => 'CHARGE',
    'phone' => '',
    'email' => '',
    'is_settlement_target' => true
);

if ($w == 'u') {
    $html_title = '개인결제 수정';
    
    $sql = " SELECT * FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
    $pp_row = sql_fetch_pg($sql);
    
    if (!$pp_row || !$pp_row['personal_id']) {
        alert('등록된 자료가 없습니다.');
    }
    
    $pp = array(
        'personal_id' => $pp_row['personal_id'],
        'order_id' => $pp_row['order_id'],
        'shop_id' => $pp_row['shop_id'],
        'shopdetail_id' => $pp_row['shopdetail_id'],
        'user_id' => $pp_row['user_id'],
        'name' => $pp_row['name'],
        'reason' => $pp_row['reason'],
        'amount' => $pp_row['amount'],
        'status' => $pp_row['status'],
        'phone' => $pp_row['phone'],
        'email' => $pp_row['email'],
        'is_settlement_target' => $pp_row['is_settlement_target']
    );
    
    // 결제 정보 조회
    $payment_sql = " SELECT * FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' ";
    $payment_row = sql_fetch_pg($payment_sql);
} else {
    $html_title = '개인결제 입력';
}

$g5['title'] = $html_title;
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_desc01 local_desc">
    <p>
        개인결제 청구 정보를 <?php echo $w == 'u' ? '수정' : '등록'; ?>합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="fpersonalpayform" action="./shop_personalpayformupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="personal_id" value="<?php echo $pp['personal_id']; ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="sst" value="<?php echo isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : ''; ?>">
<input type="hidden" name="sod" value="<?php echo isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : ''; ?>">
<input type="hidden" name="sfl" value="<?php echo isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : ''; ?>">
<input type="hidden" name="stx" value="<?php echo isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : ''; ?>">
<input type="hidden" name="sfl2" value="<?php echo isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; ?>">
<input type="hidden" name="page" value="<?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>">

<section id="anc_spp_info">
    <h2 class="h2_frm">청구 정보</h2>
    <div class="local_desc">
        <p>개인결제 청구 관련 기본 정보입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>청구 정보 목록</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($w == 'u') { ?>
        <tr>
            <th scope="row">주문번호</th>
            <td>
                <?php echo htmlspecialchars($pp['order_id']); ?>
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($pp['order_id']); ?>">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label for="user_id">회원ID</label></th>
            <td>
                <input type="text" name="user_id" value="<?php echo get_text($pp['user_id']); ?>" id="user_id" class="frm_input" size="30">
                <small style="color: #666;">회원ID를 입력하면 고객 정보와 결제 내역이 자동으로 불러와집니다.</small>
                <div id="user_id_check" class="user_id_check" style="margin-top: 5px;"></div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="name">이름 <strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="name" value="<?php echo get_text($pp['name']); ?>" id="name" required class="required frm_input" size="30"></td>
        </tr>
        <tr>
            <th scope="row"><label for="phone">휴대폰</label></th>
            <td><input type="text" name="phone" value="<?php echo get_text($pp['phone']); ?>" id="phone" class="frm_input" size="30"></td>
        </tr>
        <tr>
            <th scope="row"><label for="email">이메일</label></th>
            <td><input type="text" name="email" value="<?php echo get_text($pp['email']); ?>" id="email" class="frm_input" size="30"></td>
        </tr>
        <tr>
            <th scope="row"><label for="reason">청구사유 <strong class="sound_only">필수</strong></label></th>
            <td><textarea name="reason" id="reason" rows="5" required class="required"><?php echo html_purifier($pp['reason']); ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="amount">청구금액 <strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="amount" value="<?php echo $pp['amount']; ?>" id="amount" required class="required frm_input" size="15"> 원</td>
        </tr>
        <tr>
            <th scope="row"><label for="shopdetail_id">세부예약가맹점 ID</label></th>
            <td>
                <select name="shopdetail_id" id="shopdetail_id" class="frm_input">
                    <option value="">::세부예약ID없음::</option>
                    <?php if ($w == 'u' && $pp['shopdetail_id']) { ?>
                    <?php
                    // 수정 모드일 때 기존 shopdetail_id 정보 조회
                    $existing_sql = " SELECT asd.shopdetail_id, asd.appointment_id, asd.appointment_datetime, sa.appointment_no
                                      FROM appointment_shop_detail AS asd
                                      LEFT JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
                                      WHERE asd.shopdetail_id = " . (int)$pp['shopdetail_id'] . " ";
                    $existing_row = sql_fetch_pg($existing_sql);
                    if ($existing_row) {
                        $existing_datetime = $existing_row['appointment_datetime'] ? date('Y-m-d H:i', strtotime($existing_row['appointment_datetime'])) : '';
                        $existing_appointment_no = $existing_row['appointment_no'] ? $existing_row['appointment_no'] : '';
                        $existing_text = '예약번호: ' . $existing_appointment_no . '(세부예약ID: ' . $existing_row['shopdetail_id'] . ')-예약일시: ' . $existing_datetime;
                    ?>
                    <option value="<?php echo $existing_row['shopdetail_id']; ?>" selected><?php echo htmlspecialchars($existing_text); ?></option>
                    <?php } ?>
                    <?php } ?>
                </select>
                <small style="color: #666;">회원ID를 입력하면 결제한 예약 내역이 자동으로 불러와집니다.</small>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php if ($w == 'u' && isset($payment_row) && $payment_row) { ?>
<section id="anc_spp_pay" class="cbox">
    <h2 class="h2_frm">결제 정보</h2>
    <div class="local_desc02 local_desc">
        <p>결제 관련 정보입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>결제 정보 목록</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결제ID</th>
            <td><?php echo $payment_row['payment_id']; ?></td>
        </tr>
        <tr>
            <th scope="row">결제키</th>
            <td><?php echo htmlspecialchars($payment_row['payment_key']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제방법</th>
            <td><?php echo htmlspecialchars($payment_row['payment_method']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제금액</th>
            <td><?php echo number_format($payment_row['amount']); ?>원</td>
        </tr>
        <tr>
            <th scope="row">결제상태</th>
            <td><?php echo htmlspecialchars($payment_row['status']); ?></td>
        </tr>
        <tr>
            <th scope="row">결제일시</th>
            <td><?php echo $payment_row['paid_at'] ? date('Y-m-d H:i:s', strtotime($payment_row['paid_at'])) : '-'; ?></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>
<?php } ?>

<div class="btn_fixed_top">
    <a href="./shop_personalpaylist.php?<?php echo isset($_GET['sst']) ? 'sst='.clean_xss_tags($_GET['sst']).'&' : ''; ?><?php echo isset($_GET['sod']) ? 'sod='.clean_xss_tags($_GET['sod']).'&' : ''; ?><?php echo isset($_GET['sfl']) ? 'sfl='.clean_xss_tags($_GET['sfl']).'&' : ''; ?><?php echo isset($_GET['stx']) ? 'stx='.urlencode($_GET['stx']).'&' : ''; ?><?php echo isset($_GET['sfl2']) ? 'sfl2='.clean_xss_tags($_GET['sfl2']).'&' : ''; ?><?php echo isset($_GET['page']) ? 'page='.(int)$_GET['page'] : ''; ?>" class="btn btn_02">목록</a>
    <?php if($w == 'u') { ?>
        <a href="./shop_personalpayformupdate.php?w=d&amp;personal_id=<?php echo $pp['personal_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
    <?php } ?>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
var shop_id = <?php echo $shop_id; ?>;

$(function() {
    var user_id_timer;
    
    $('#user_id').on('blur', function() {
        var user_id = $(this).val().trim();
        
        if (!user_id) {
            $('#user_id_check').html('').removeClass('valid invalid');
            $('#shopdetail_id').html('<option value="">::세부예약ID없음::</option>');
            return;
        }
        
        $('#user_id_check').html('조회 중...').removeClass('valid invalid');
        
        clearTimeout(user_id_timer);
        user_id_timer = setTimeout(function() {
            $.ajax({
                url: './ajax/shop_personalpay_shopdetail.php',
                type: 'POST',
                data: {
                    user_id: user_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // 고객 정보 설정
                        if (response.customer) {
                            if (response.customer.name) {
                                $('#name').val(response.customer.name);
                            }
                            if (response.customer.phone) {
                                $('#phone').val(response.customer.phone);
                            }
                            if (response.customer.email) {
                                $('#email').val(response.customer.email);
                            }
                        }
                        
                        // 세부예약가맹점 ID 선택박스 구성
                        var shopdetail_select = $('#shopdetail_id');
                        shopdetail_select.html('<option value="">::세부예약ID없음::</option>');
                        
                        if (response.shopdetails && response.shopdetails.length > 0) {
                            $.each(response.shopdetails, function(index, item) {
                                shopdetail_select.append('<option value="' + item.shopdetail_id + '">' + item.display_text + '</option>');
                            });
                        }
                        
                        $('#user_id_check').html('조회 완료').addClass('valid').removeClass('invalid');
                    } else {
                        $('#user_id_check').html(response.message || '조회 실패').addClass('invalid').removeClass('valid');
                        $('#shopdetail_id').html('<option value="">::세부예약ID::</option>');
                    }
                },
                error: function() {
                    $('#user_id_check').html('조회 중 오류가 발생했습니다.').addClass('invalid').removeClass('valid');
                    $('#shopdetail_id').html('<option value="">::세부예약ID::</option>');
                }
            });
        }, 500);
    });
});

function form_check(f)
{
    if(f.amount.value.replace(/[0-9]/g, "").length > 0) {
        alert("청구금액은 숫자만 입력해 주십시오");
        f.amount.focus();
        return false;
    }
    
    if(parseInt(f.amount.value) <= 0) {
        alert("청구금액은 0보다 큰 값이어야 합니다");
        f.amount.focus();
        return false;
    }

    return true;
}
</script>

<style>
.user_id_check {
    font-size: 0.9em;
    margin-top: 5px;
}
.user_id_check.valid {
    color: green;
}
.user_id_check.invalid {
    color: red;
}
</style>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

