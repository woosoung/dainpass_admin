<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
$(function() {
    var user_id_timer;

    $('#user_id').on('blur', function() {
        // 수정 모드일 때는 조회 기능 비활성화
        if ($(this).prop('readonly')) {
            return;
        }

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
                        // 닉네임만 표시
                        var user_info = '';
                        if (response.customer && response.customer.nickname) {
                            user_info = '닉네임: ' + response.customer.nickname;
                        }

                        // 세부예약가맹점 ID 선택박스 구성
                        var shopdetail_select = $('#shopdetail_id');
                        shopdetail_select.html('<option value="">::세부예약ID없음::</option>');

                        if (response.shopdetails && response.shopdetails.length > 0) {
                            $.each(response.shopdetails, function(index, item) {
                                shopdetail_select.append('<option value="' + item.shopdetail_id + '">' + item.display_text + '</option>');
                            });
                        }

                        var check_msg = '조회 완료';
                        if (user_info) {
                            check_msg += ' (' + user_info + ')';
                        }
                        $('#user_id_check').html(check_msg).addClass('valid').removeClass('invalid');
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
