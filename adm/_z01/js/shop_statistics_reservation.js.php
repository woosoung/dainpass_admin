<?php
if (!defined('_GNUBOARD_')) exit;

// jQuery UI datepicker 플러그인
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 공통 datepicker 함수 include
include_once(G5_Z_PATH . '/js/_common_datepicker.js.php');

// 공통 통계 유틸리티 함수 include
include_once(G5_Z_PATH . '/js/_common_statistics.js.php');
?>
<script>
(function($) {
    var appointmentTrendChart = null;
    var statusDistributionChart = null;
    var hourlyAppointmentChart = null;
    var weeklyAppointmentChart = null;
    var cancelTrendChart = null;

    // 공통 함수 별칭
    var formatNumber = StatisticsCommon.formatNumber;
    var formatCurrency = StatisticsCommon.formatCurrency;
    var formatDateLabel = StatisticsCommon.formatDateLabel;

    function getStatusLabel(status) {
        if (!status) return '기타';
        var statusMap = {
            'CANCELLED': '취소됨',
            'COMPLETED': '완료',
            'CONFIRMED': '확인됨',
            'PENDING': '대기중',
            'BOOKED': '예약됨'  // 통계에서 제외되지만 혹시 모르니 포함
        };
        return statusMap[status] || status;
    }

    function loadStatistics() {
        var periodType = $('#period_type').val();
        var startDate  = $('#start_date').val();
        var endDate    = $('#end_date').val();

        // 통계 파라미터 검증
        var validation = StatisticsCommon.validateStatisticsParams({
            periodType: periodType,
            startDate: startDate,
            endDate: endDate
        });

        if (!validation.valid) {
            alert(validation.message);
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/shop_statistics_reservation_data.php',
            type: 'POST',
            dataType: 'json',
            data: {
                period_type: periodType,
                start_date: startDate,
                end_date: endDate
            }
        }).done(function(res) {
            if (!res || !res.success) {
                alert(res && res.message ? res.message : '데이터 조회에 실패했습니다.');
                return;
            }

            updateSummaryCards(res.summary);
            updateDetailSummary(res.summary, res.range_start, res.range_end);
            renderAppointmentTrendChart(res.daily_appointments, periodType);
            renderStatusDistributionChart(res.status_distribution);
            renderHourlyAppointmentChart(res.hourly_appointments);
            renderWeeklyAppointmentChart(res.weekly_appointments);
            renderCancelTrendChart(res.cancel_trend, periodType);
            renderWeekdayHourPatternTable(res.weekday_hour_pattern);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_appointment_count').text(formatNumber(summary.total_appointment_count || 0) + ' 건');
        $('#range_total_count').text(formatNumber(summary.total_appointment_count || 0));
        $('#active_appointment_count').text(formatNumber(summary.active_appointment_count || 0) + ' 건');
        $('#cancel_rate').text((summary.cancel_rate || 0).toFixed(2) + ' %');
        $('#cancel_count').text(formatNumber(summary.cancel_count || 0));
        $('#repeat_visit_rate').text((summary.repeat_visit_rate || 0).toFixed(2) + ' %');
        $('#repeat_customer_count').text(formatNumber(summary.repeat_customer_count || 0));
        $('#avg_appointment_per_customer').text((summary.avg_appointment_per_customer || 0).toFixed(2) + ' 회');
    }

    function updateDetailSummary(summary, rangeStart, rangeEnd) {
        if (!summary) summary = {};

        var html = '';
        html += '<p>조회 기간: <strong>' + (rangeStart || '-') + '</strong> ~ <strong>' + (rangeEnd || '-') + '</strong></p>';
        html += '<p class="mt-2">';
        html += '전체 예약: <strong>' + formatNumber(summary.total_appointment_count || 0) + '건</strong> · ';
        html += '활성 예약: <strong>' + formatNumber(summary.active_appointment_count || 0) + '건</strong><br>';
        html += '고유 고객: <strong>' + formatNumber(summary.unique_customer_count || 0) + '명</strong> · ';
        html += '재방문 고객: <strong>' + formatNumber(summary.repeat_customer_count || 0) + '명</strong> · ';
        html += '재방문율: <strong>' + (summary.repeat_visit_rate || 0).toFixed(2) + '%</strong><br>';
        html += '취소 건수: <strong>' + formatNumber(summary.cancel_count || 0) + '건</strong> · ';
        html += '취소율: <strong>' + (summary.cancel_rate || 0).toFixed(2) + '%</strong>';
        html += '</p>';

        $('#reservation_detail_summary').html(html);
    }

    function renderAppointmentTrendChart(dailyAppointments, periodType) {
        var labels = [];
        var totalData = [];
        var cancelData = [];

        if (dailyAppointments && dailyAppointments.length) {
            for (var i = 0; i < dailyAppointments.length; i++) {
                var row = dailyAppointments[i];
                labels.push(formatDateLabel(row.date, periodType));
                totalData.push(row.total_count || 0);
                cancelData.push(row.cancel_count || 0);
            }
        }

        var ctx = document.getElementById('appointment_trend_chart').getContext('2d');
        if (appointmentTrendChart) {
            appointmentTrendChart.destroy();
        }

        appointmentTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '전체 예약',
                        data: totalData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    },
                    {
                        label: '취소 예약',
                        data: cancelData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '건';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderStatusDistributionChart(statusDistribution) {
        var labels = [];
        var data = [];

        if (statusDistribution && statusDistribution.length) {
            for (var i = 0; i < statusDistribution.length; i++) {
                var row = statusDistribution[i];
                // 상태값을 한국어로 변환 (BOOKED는 이미 제외되어 있음)
                labels.push(getStatusLabel(row.status));
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('status_distribution_chart').getContext('2d');
        if (statusDistributionChart) {
            statusDistributionChart.destroy();
        }

        var backgroundColors = [
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)'
        ];

        statusDistributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '건 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderHourlyAppointmentChart(hourlyAppointments) {
        var labels = [];
        var data = [];

        if (hourlyAppointments && hourlyAppointments.length) {
            for (var i = 0; i < hourlyAppointments.length; i++) {
                var row = hourlyAppointments[i];
                labels.push(row.hour + '시');
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('hourly_appointment_chart').getContext('2d');
        if (hourlyAppointmentChart) {
            hourlyAppointmentChart.destroy();
        }

        hourlyAppointmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.y || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '건';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderWeeklyAppointmentChart(weeklyAppointments) {
        var labels = [];
        var data = [];

        if (weeklyAppointments && weeklyAppointments.length) {
            for (var i = 0; i < weeklyAppointments.length; i++) {
                var row = weeklyAppointments[i];
                labels.push(row.weekday_name || '');
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('weekly_appointment_chart').getContext('2d');
        if (weeklyAppointmentChart) {
            weeklyAppointmentChart.destroy();
        }

        weeklyAppointmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.y || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '건';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCancelTrendChart(cancelTrend, periodType) {
        var labels = [];
        var cancelRateData = [];

        if (cancelTrend && cancelTrend.length) {
            for (var i = 0; i < cancelTrend.length; i++) {
                var row = cancelTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                cancelRateData.push(row.cancel_rate || 0);
            }
        }

        var ctx = document.getElementById('cancel_trend_chart').getContext('2d');
        if (cancelTrendChart) {
            cancelTrendChart.destroy();
        }

        cancelTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '취소율',
                        data: cancelRateData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '취소율: ' + (context.parsed.y || 0).toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderWeekdayHourPatternTable(weekdayHourPattern) {
        var $tbody = $('#weekday_hour_pattern_table tbody');
        $tbody.empty();

        if (!weekdayHourPattern || !weekdayHourPattern.length) {
            $tbody.append('<tr><td colspan="3" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        weekdayHourPattern.forEach(function(row) {
            var tr = '<tr>' +
                '<td class="text-center">' + (row.weekday_name || '-') + '</td>' +
                '<td class="text-center">' + (row.hour !== undefined ? row.hour + '시' : '-') + '</td>' +
                '<td class="text-center">' + formatNumber(row.appointment_count || 0) + '건</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }


    $(function() {
        // Datepicker 초기화
        initializeStatisticsDatePicker({
            startInputId: 'start_date',
            endInputId: 'end_date'
        });

        // 빠른 선택 버튼 초기화
        initializeQuickSelectButtons({
            startInputId: 'start_date',
            endInputId: 'end_date',
            searchBtnId: 'search_btn',
            autoSearch: true
        });

        $('#search_btn').on('click', function() {
            loadStatistics();
        });

        // 최초 진입 시 자동 조회
        loadStatistics();
    });
})(jQuery);
</script>

