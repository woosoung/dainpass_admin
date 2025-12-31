<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
$(function() {
    // Enter 키로 검색 실행
    $(document).on('keypress', '#modal_search_value', function(e) {
        if (e.which === 13) {
            searchAppointments();
            return false;
        }
    });
});

function form_check(f)
{
    // shopdetail_id 필수 체크 (신규 등록 시에만)
    var w = f.w ? f.w.value : '';
    if (!w && (!f.shopdetail_id.value || f.shopdetail_id.value === '')) {
        alert("예약을 선택해 주십시오. '예약에서 선택' 버튼을 클릭하세요.");
        return false;
    }

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

// 모달 열기
function openAppointmentModal() {
    $('#appointmentSelectModal').show();
    // 초기 로드 시 최근 10건 표시
    searchAppointments();
}

// 검색 초기화 (전체목록)
function resetAppointmentSearch() {
    // 검색 필드 초기화
    $('#modal_search_value').val('');
    $('#modal_search_type').val('nickname');
    // 1페이지로 검색
    searchAppointments(1);
}

// 모달 닫기
function closeAppointmentModal() {
    $('#appointmentSelectModal').hide();
    $('#modal_search_value').val('');
    $('#appointmentListArea').html('');
    if ($('#appointmentPaginationArea').length > 0) {
        $('#appointmentPaginationArea').html('');
    }
}

// 예약 검색
function searchAppointments(page) {
    // 페이지 파라미터가 없으면 1로 초기화
    page = page || 1;

    var searchType = $('#modal_search_type').val();
    var searchValue = $('#modal_search_value').val().trim();

    // 로딩 표시 - 테이블 구조 유지하면서 높이 고정
    var loadingHtml = '<div class="tbl_wrap"><table class="tbl_head01">';
    loadingHtml += '<thead><tr>';
    loadingHtml += '<th>예약번호</th><th>예약일시</th><th>닉네임</th><th>서비스</th><th>선택</th>';
    loadingHtml += '</tr></thead><tbody>';
    // 10개 행 높이 유지를 위해 빈 행 생성
    for (var i = 0; i < 10; i++) {
        if (i === 4) {
            // 중간(5번째 행)에 로딩 메시지 표시
            loadingHtml += '<tr><td colspan="5" style="text-align:center; padding:20px; color:#666; border:none;">검색 중...</td></tr>';
        } else {
            loadingHtml += '<tr><td colspan="5" style="height:45px; border:none;">&nbsp;</td></tr>';
        }
    }
    loadingHtml += '</tbody></table></div>';
    $('#appointmentListArea').html(loadingHtml);

    $.ajax({
        url: '<?=G5_Z_URL?>/ajax/shop_appointment_search_for_personal_payment.php',
        type: 'POST',
        data: {
            search_type: searchType,
            search_value: searchValue,
            page: page  // 페이지 번호 추가
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderAppointmentList(response.appointments);
                renderPagination(response);  // 페이지네이션 렌더링
            } else {
                alert(response.message || '검색 실패');
                renderAppointmentList([]);  // 빈 테이블 렌더링 (높이 유지)
                if ($('#appointmentPaginationArea').length > 0) {
                    $('#appointmentPaginationArea').html('');
                }
            }
        },
        error: function() {
            alert('검색 중 오류가 발생했습니다.');
            renderAppointmentList([]);  // 빈 테이블 렌더링 (높이 유지)
            if ($('#appointmentPaginationArea').length > 0) {
                $('#appointmentPaginationArea').html('');
            }
        }
    });
}

// 검색 결과 렌더링
function renderAppointmentList(appointments) {
    var html = '<div class="tbl_wrap"><table class="tbl_head01">';
    html += '<thead><tr>';
    html += '<th>예약번호</th><th>예약일시</th><th>닉네임</th><th>서비스</th><th>선택</th>';
    html += '</tr></thead><tbody>';

    if (appointments.length === 0) {
        // 검색 결과 없을 때 - 중간에 메시지 표시하고 나머지는 빈 행
        for (var i = 0; i < 10; i++) {
            if (i === 4) {
                html += '<tr><td colspan="5" class="td_empty">검색 결과가 없습니다.</td></tr>';
            } else {
                html += '<tr><td colspan="5" style="height:45px; border:none;">&nbsp;</td></tr>';
            }
        }
    } else {
        // 검색 결과 표시
        $.each(appointments, function(index, item) {
            html += '<tr>';
            html += '<td>' + escapeHtml(item.appointment_no) + '</td>';
            html += '<td>' + escapeHtml(item.appointment_datetime) + '</td>';
            html += '<td>' + escapeHtml(item.nickname) + '</td>';
            html += '<td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' + escapeHtml(item.service_names || '-') + '</td>';
            html += '<td><button type="button" class="btn btn_03" onclick="selectAppointment(' +
                    item.shopdetail_id + ', \'' + escapeHtml(item.appointment_info).replace(/'/g, "\\'") + '\', \'' +
                    escapeHtml(item.nickname).replace(/'/g, "\\'") + '\')">선택</button></td>';
            html += '</tr>';
        });

        // 10개 미만일 때 빈 행 추가하여 높이 유지
        var remainingRows = 10 - appointments.length;
        for (var i = 0; i < remainingRows; i++) {
            html += '<tr><td colspan="5" style="height:45px; border:none;">&nbsp;</td></tr>';
        }
    }

    html += '</tbody></table></div>';
    $('#appointmentListArea').html(html);
}

// 예약 선택
function selectAppointment(shopdetailId, appointmentInfo, nickname) {
    // shopdetail_id만 폼에 저장 (⚠️ user_id는 프론트엔드에 전혀 존재하지 않음!)
    $('#shopdetail_id').val(shopdetailId);

    // 선택 정보 표시 (user_id 제외, 닉네임과 예약 정보만)
    $('#selected_appointment_info').html(
        '<strong>선택된 예약:</strong> ' + escapeHtml(appointmentInfo) + ' | <strong>닉네임:</strong> ' + escapeHtml(nickname)
    );

    // 모달 닫기
    closeAppointmentModal();
}

// XSS 방지 함수
function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// 페이지네이션 렌더링 함수
function renderPagination(response) {
    var currentPage = response.current_page;
    var totalPage = response.total_page;
    var totalCount = response.total_count;

    var paginationHtml = '';

    // 총 개수 표시
    paginationHtml += '<div style="text-align:center; margin-top:10px; color:#666; font-size:0.9em;">총 ' + totalCount + '건</div>';

    if (totalPage > 1) {
        paginationHtml += '<nav class="pg_wrap" style="text-align:center; margin-top:15px;"><span class="pg">';

        // 처음 버튼
        if (currentPage > 1) {
            paginationHtml += '<a href="javascript:void(0);" onclick="searchAppointments(1)" class="pg_page pg_start">처음</a> ';
        }

        // 이전 버튼
        if (currentPage > 1) {
            paginationHtml += '<a href="javascript:void(0);" onclick="searchAppointments(' + (currentPage - 1) + ')" class="pg_page pg_prev">이전</a> ';
        }

        // 페이지 번호 (현재 페이지 기준 ±5개)
        var startPage = Math.max(1, currentPage - 5);
        var endPage = Math.min(totalPage, currentPage + 5);

        for (var k = startPage; k <= endPage; k++) {
            if (currentPage !== k) {
                paginationHtml += '<a href="javascript:void(0);" onclick="searchAppointments(' + k + ')" class="pg_page">' + k + '</a> ';
            } else {
                paginationHtml += '<strong class="pg_current">' + k + '</strong> ';
            }
        }

        // 다음 버튼
        if (currentPage < totalPage) {
            paginationHtml += '<a href="javascript:void(0);" onclick="searchAppointments(' + (currentPage + 1) + ')" class="pg_page pg_next">다음</a> ';
        }

        // 끝 버튼
        if (currentPage < totalPage) {
            paginationHtml += '<a href="javascript:void(0);" onclick="searchAppointments(' + totalPage + ')" class="pg_page pg_end">맨끝</a>';
        }

        paginationHtml += '</span></nav>';
    }

    // 페이지네이션 영역 업데이트
    if ($('#appointmentPaginationArea').length === 0) {
        $('#appointmentListArea').after('<div id="appointmentPaginationArea"></div>');
    }
    $('#appointmentPaginationArea').html(paginationHtml);
}
</script>
