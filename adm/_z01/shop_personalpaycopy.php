<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'w');

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;

$g5['title'] = '개인결제 복사';
include_once(G5_PATH.'/head.sub.php');

$sql = " SELECT * FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
$row = sql_fetch_pg($sql);

if(!$row || !$row['personal_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');
?>

<div class="new_win">
    <h1>개인결제 복사</h1>

    <form name="fpersonalpaycopy" method="post" action="./shop_personalpaycopyupdate.php" onsubmit="return form_check(this);">
    <input type="hidden" name="personal_id" value="<?php echo $personal_id; ?>">
    <input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">

     <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <tbody>
        <tr>
            <th scope="row"><label for="name">이름</label></th>
            <td><input type="text" name="name" value="<?php echo get_text($row['name']); ?>" id="name" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="amount">청구금액</label></th>
            <td><input type="text" name="amount" value="" id="amount" required class="required frm_input" size="20"> 원</td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="복사하기" class="btn_submit">
        <button type="button" onclick="self.close();">창닫기</button>
    </div>

    </form>

</div>

<script>
// <![CDATA[
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
// ]]>
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');

