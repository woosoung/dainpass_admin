<script>
$(document).ready(function() {
    // 현재 URL의 검색 파라미터 가져오기
    var urlParams = new URLSearchParams(window.location.search);
    var currentSca = urlParams.get('sca') || '';
    
    // 페이지 리로드 함수
    function reloadWithSearchParam() {
        var url = window.location.pathname;
        if (currentSca) {
            url += '?sca=' + encodeURIComponent(currentSca);
        }
        window.location.href = url;
    }
    
    // 모달 열기
    $('#btn_new_registration').on('click', function() {
        $('#registration_modal').addClass('active');
        $('#registration_form')[0].reset();
    });

    // 모달 닫기
    $('.modal-close').on('click', function() {
        $('#registration_modal').removeClass('active');
    });

    // 모달 배경 클릭 시 닫기
    $('.modal-overlay').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });

    // 검색은 폼 제출 방식으로 처리됨 (fsearch form)

    // 신규 등록 폼 제출
    $('#registration_form').on('submit', function(e) {
        e.preventDefault();

        var categoryId = $('#modal_category').val();
        var prepPeriod = $('#modal_prep_period').val();

        if (categoryId === '' || categoryId === null || categoryId === undefined) {
            alert('업종을 선택해 주세요.');
            return false;
        }

        if (prepPeriod === '' || prepPeriod < 0) {
            alert('예약 준비시간을 올바르게 입력해 주세요.');
            return false;
        }

        // AJAX로 데이터 저장
        $.ajax({
            url: './category_preparation_list_update.php',
            type: 'POST',
            data: {
                mode: 'save',
                category_id: categoryId,
                prep_period_for_reservation: prepPeriod
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || '저장되었습니다.');
                    reloadWithSearchParam();
                } else {
                    var errorMsg = response.message || '저장에 실패했습니다.';
                    if (response.debug_sql) {
                        console.error('[신규등록] SQL:', response.debug_sql);
                        console.error('[신규등록] category_id:', response.debug_category_id);
                        console.error('[신규등록] prep_period:', response.debug_prep_period);
                    }
                    alert(errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('[신규등록] Error:', error);
                console.error('[신규등록] Response:', xhr.responseText);
                alert('서버 오류가 발생했습니다.');
            }
        });
    });

    // 저장 버튼 클릭
    $(document).on('click', '.btn-save', function(e) {
        e.preventDefault();

        var categoryId = $(this).data('category-id');
        var $form = $('.inline-form[data-category-id="' + categoryId + '"]');
        var prepPeriod = $form.find('input[name="prep_period"]').val();

        if (prepPeriod === '' || prepPeriod < 0) {
            alert('예약 준비시간을 올바르게 입력해 주세요.');
            return false;
        }

        if (!confirm('저장하시겠습니까?')) {
            return false;
        }

        // AJAX로 데이터 저장
        $.ajax({
            url: './category_preparation_list_update.php',
            type: 'POST',
            data: {
                mode: 'save',
                category_id: categoryId,
                prep_period_for_reservation: prepPeriod
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || '저장되었습니다.');
                    reloadWithSearchParam();
                } else {
                    alert(response.message || '저장에 실패했습니다.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('서버 오류가 발생했습니다.');
            }
        });
    });

    // 삭제 버튼 클릭
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        var categoryId = $(this).data('category-id');

        if (!confirm('정말 삭제하시겠습니까?')) {
            return false;
        }

        // AJAX로 데이터 삭제
        $.ajax({
            url: './category_preparation_list_update.php',
            type: 'POST',
            data: {
                mode: 'delete',
                category_id: categoryId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || '삭제되었습니다.');
                    reloadWithSearchParam();
                } else {
                    alert(response.message || '삭제에 실패했습니다.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('서버 오류가 발생했습니다.');
            }
        });
    });

    // ESC 키로 모달 닫기
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal-overlay').removeClass('active');
        }
    });
});
</script>

