<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 월 변경
function changeMonth(delta) {
    var year = parseInt($('#year-select').val());
    var month = parseInt($('#month-select').val());
    
    month += delta;
    
    if (month < 1) {
        month = 12;
        year--;
    } else if (month > 12) {
        month = 1;
        year++;
    }
    
    location.href = './staff_schedule_calendar.php?year=' + year + '&month=' + month;
}

// 년월 선택 변경
function changeYearMonth() {
    var year = $('#year-select').val();
    var month = $('#month-select').val();
    location.href = './staff_schedule_calendar.php?year=' + year + '&month=' + month;
}

// 특정 날짜에 스케줄 추가
function addScheduleForDate(date) {
    location.href = './staff_schedule_form.php?work_date=' + date;
}

// 스케줄 수정
function editSchedule(scheduleId) {
    location.href = './staff_schedule_form.php?w=u&schedule_id=' + scheduleId;
}

// 스케줄 삭제
function deleteSchedule(event, scheduleId) {
    event.stopPropagation();
    
    if (!confirm('해당 근무일정을 삭제하시겠습니까?')) {
        return false;
    }
    
    $.ajax({
        url: './ajax/staff_schedule_del.php',
        type: 'POST',
        data: {
            schedule_id: scheduleId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // 해당 라벨 삭제
                $('[data-schedule-id="' + scheduleId + '"]').fadeOut(300, function() {
                    $(this).remove();
                });
                alert('삭제되었습니다.');
            } else {
                alert(response.message || '삭제에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        }
    });
}

$(document).ready(function() {
    // 페이지 로드 완료
    console.log('Staff Schedule Calendar loaded');
});
</script>

