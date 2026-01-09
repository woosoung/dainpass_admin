<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 날짜 계산 함수
    window.calculateQuickPeriodDates = function(daysBack) {
        var today = new Date();
        var startDate = new Date(today.getTime() - (daysBack * 24 * 60 * 60 * 1000));
        return {
            start: formatDate(startDate),
            end: formatDate(today)
        };
    };

    function formatDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    // Datepicker 초기화
    window.initializeStatisticsDatePicker = function(options) {
        options = options || {};
        var startInputId = options.startInputId || 'start_date';
        var endInputId = options.endInputId || 'end_date';

        // 절대 최소 날짜: 2년 전 1월 1일
        var today = new Date();
        var currentYear = today.getFullYear();
        var minYear = currentYear - 2;
        var absoluteMinDate = new Date(minYear, 0, 1); // 1월 1일

        $('#' + startInputId).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: minYear + ":" + currentYear,
            minDate: absoluteMinDate,
            maxDate: "+0d",
            onClose: function(selectedDate) {
                if (selectedDate) {
                    var selected = new Date(selectedDate);
                    // 선택한 날짜와 절대 최소 날짜 중 큰 값을 사용
                    var minDateForEnd = selected > absoluteMinDate ? selected : absoluteMinDate;
                    $('#' + endInputId).datepicker("option", "minDate", minDateForEnd);
                }
            }
        });

        $('#' + endInputId).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: minYear + ":" + currentYear,
            minDate: absoluteMinDate,
            maxDate: "+0d",
            onClose: function(selectedDate) {
                if (selectedDate) {
                    $('#' + startInputId).datepicker("option", "maxDate", selectedDate);
                }
            }
        });
    };

    // 빠른 선택 버튼 초기화
    window.initializeQuickSelectButtons = function(options) {
        options = options || {};
        var startInputId = options.startInputId || 'start_date';
        var endInputId = options.endInputId || 'end_date';
        var searchBtnId = options.searchBtnId || 'search_btn';

        $(document).on('click', '.quick-period-btn', function() {
            var daysBack = $(this).data('days');
            var dates = window.calculateQuickPeriodDates(daysBack);

            $('#' + startInputId).val(dates.start);
            $('#' + endInputId).val(dates.end);

            if (options.autoSearch !== false) {
                $('#' + searchBtnId).click();
            }
        });

        // period_type 변경 시 자동 조회
        $('#period_type').on('change', function() {
            if (options.autoSearch !== false) {
                $('#' + searchBtnId).click();
            }
        });
    };
})(jQuery);
</script>
