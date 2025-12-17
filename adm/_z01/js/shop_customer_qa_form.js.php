<script>
$(document).ready(function() {
    // 본 문의글이 공개글인 경우, 댓글에 비밀글 설정 시 경고
    var originalSecretYn = $('#original_secret_yn').val();
    
    if (originalSecretYn == 'N') {
        $('input[name="qna_reply_secret_yn"]').on('change', function() {
            if ($(this).val() == 'Y') {
                $('#secret_warning').slideDown();
            } else {
                $('#secret_warning').slideUp();
            }
        });
        
        // 초기 상태 확인
        if ($('input[name="qna_reply_secret_yn"]:checked').val() == 'Y') {
            $('#secret_warning').show();
        }
    }
});

function fcustomerqaform_submit(f)
{
    if (!f.qna_reply_content.value || f.qna_reply_content.value.trim() == '') {
        alert("답변 내용을 입력해주세요.");
        f.qna_reply_content.focus();
        return false;
    }
    
    // 본 문의글이 공개글인데 댓글에 비밀글 설정하는 경우 확인
    var originalSecretYn = document.getElementById('original_secret_yn').value;
    var replySecretYn = f.qna_reply_secret_yn.value;
    
    if (originalSecretYn == 'N' && replySecretYn == 'Y') {
        if (!confirm('댓글에 비밀글 설정을 하시면 본 대표 문의글도 비밀글로 전환됩니다.\n계속하시겠습니까?')) {
            return false;
        }
    }
    
    return true;
}

// 답변 수정 버튼 클릭
$(document).on('click', '.btn_edit_reply', function() {
    var reply_id = $(this).data('reply-id');
    $('#reply_content_' + reply_id).hide();
    $('#reply_edit_form_' + reply_id).show();
});

// 답변 수정 취소 버튼 클릭
$(document).on('click', '.btn_cancel_edit', function() {
    var reply_id = $(this).data('reply-id');
    $('#reply_content_' + reply_id).show();
    $('#reply_edit_form_' + reply_id).hide();
});

// 답변 수정 폼 제출
$(document).on('submit', '.frm_edit_reply', function(e) {
    e.preventDefault();
    var form = $(this);
    var reply_id = form.data('reply-id');
    var formData = form.serialize();
    
    if (!confirm('답변을 수정하시겠습니까?')) {
        return false;
    }
    
    $.ajax({
        url: './shop_customer_qa_form_update.php',
        type: 'POST',
        data: formData + '&w=u&token=' + $('input[name="token"]').val(),
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                alert(response.message);
            } else {
                alert('답변이 수정되었습니다.');
                location.reload();
            }
        },
        error: function() {
            alert('수정 중 오류가 발생했습니다.');
        }
    });
    
    return false;
});

// 답변 삭제 버튼 클릭
$(document).on('click', '.btn_delete_reply', function() {
    var reply_id = $(this).data('reply-id');
    
    if (!confirm('답변을 삭제하시겠습니까?\n삭제된 답변은 복구할 수 없습니다.')) {
        return false;
    }
    
    $.ajax({
        url: './shop_customer_qa_form_update.php',
        type: 'POST',
        data: {
            reply_id: reply_id,
            qna_id: $('input[name="qna_id"]').val(),
            shop_id: $('input[name="shop_id"]').val(),
            w: 'd',
            token: $('input[name="token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                alert(response.message);
            } else {
                alert('답변이 삭제되었습니다.');
                location.reload();
            }
        },
        error: function() {
            alert('삭제 중 오류가 발생했습니다.');
        }
    });
    
    return false;
});
</script>
