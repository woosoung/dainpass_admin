<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 근무시간 계산
function calculateWorkDuration() {
    var startTime = $('#start_time').val();
    var endTime = $('#end_time').val();
    
    if (startTime && endTime) {
        var start = new Date('2000-01-01 ' + startTime);
        var end = new Date('2000-01-01 ' + endTime);
        
        // 시간 차이 계산 (밀리초)
        var diff = end - start;
        
        if (diff <= 0) {
            $('#work_duration').text('종료 시간이 시작 시간보다 늦어야 합니다.').css('color', 'red');
            return false;
        } else {
            // 시간과 분으로 변환
            var hours = Math.floor(diff / 1000 / 60 / 60);
            var minutes = Math.floor((diff / 1000 / 60) % 60);
            
            var durationText = hours + '시간';
            if (minutes > 0) {
                durationText += ' ' + minutes + '분';
            }
            
            $('#work_duration').text(durationText).css('color', '#2563eb');
            return true;
        }
    }
    
    return true;
}

// 요일 표시
function displayDayOfWeek() {
    var workDate = $('#work_date').val();
    
    if (workDate) {
        var date = new Date(workDate);
        var days = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
        var dayOfWeek = days[date.getDay()];
        
        var color = '#000';
        if (date.getDay() === 0) { // 일요일
            color = '#ef4444';
        } else if (date.getDay() === 6) { // 토요일
            color = '#3b82f6';
        }
        
        $('#work_day_of_week').text(dayOfWeek).css('color', color);
    }
}

// 폼 제출 검증
function form01_submit(f) {
    if (!f.staff_id.value) {
        alert('직원을 선택해주세요.');
        f.staff_id.focus();
        return false;
    }
    
    if (!f.work_date.value) {
        alert('근무 날짜를 입력해주세요.');
        f.work_date.focus();
        return false;
    }
    
    if (!f.start_time.value) {
        alert('근무 시작 시간을 입력해주세요.');
        f.start_time.focus();
        return false;
    }
    
    if (!f.end_time.value) {
        alert('근무 종료 시간을 입력해주세요.');
        f.end_time.focus();
        return false;
    }
    
    // 시간 검증
    var start = new Date('2000-01-01 ' + f.start_time.value);
    var end = new Date('2000-01-01 ' + f.end_time.value);
    
    if (end <= start) {
        alert('근무 종료 시간이 시작 시간보다 늦어야 합니다.');
        f.end_time.focus();
        return false;
    }
    
    return true;
}

// 스케줄 삭제 (폼에서)
function deleteScheduleForm(scheduleId) {
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
                alert('삭제되었습니다.');
                location.href = './staff_schedule_list.php';
            } else {
                alert(response.message || '삭제에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            console.error('XHR Status:', xhr.status);
            console.error('XHR Response:', xhr.responseText);
            console.error('Error:', error);
            console.error('Status:', status);
            alert('삭제 중 오류가 발생했습니다.\n상태: ' + xhr.status + '\n에러: ' + error + '\n\n콘솔을 확인하세요.');
        }
    });
}

$(document).ready(function() {
    // 초기 요일 및 근무시간 표시
    displayDayOfWeek();
    calculateWorkDuration();
    
    // 날짜 변경시 요일 표시
    $('#work_date').on('change', function() {
        displayDayOfWeek();
    });
    
    // 시간 변경시 근무시간 계산
    $('#start_time, #end_time').on('change', function() {
        calculateWorkDuration();
    });
    
    // 페이지 로드 완료
    console.log('Staff Schedule Form loaded');
});
</script>

