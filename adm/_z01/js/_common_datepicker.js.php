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

        $('#' + startInputId).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: "c-10:c+1",
            maxDate: "+1y",
            onClose: function(selectedDate) {
                if (selectedDate) {
                    $('#' + endInputId).datepicker("option", "minDate", selectedDate);
                }
            }
        });

        $('#' + endInputId).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: "c-10:c+1",
            maxDate: "+1y",
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
    };
})(jQuery);
</script>
