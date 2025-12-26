<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var charts = {
        salesTrendChart: null,
        appointmentTrendChart: null,
        shopStatusChart: null,
        memberSignupTrendChart: null
    };

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    function loadDashboardData() {
        var periodType = $('#period_type').val();
        var startDate  = $('#start_date').val();
        var endDate    = $('#end_date').val();

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: '../ajax/index_plt_mng_data.php',
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

            var data = res.data || {};
            
            // 주요 KPI 카드 업데이트
            updateSummaryCards(data.summary || {});
            
            // 차트 렌더링 (period_type 전달)
            var periodType = res.period_type || 'daily';
            renderSalesTrendChart(data.sales_trend || [], periodType);
            renderAppointmentTrendChart(data.appointment_trend || [], periodType);
            renderShopStatusChart(data.shop_status_distribution || {});
            renderMemberSignupTrendChart(data.member_signup_trend || [], periodType);
            
            // 최근 활동 내역 테이블 업데이트
            renderRecentSettlementTable(data.recent_settlements || []);
            renderRecentShopRegistrationTable(data.recent_shop_registrations || []);
            renderRecentMemberSignupTable(data.recent_member_signups || []);
        }).fail(function(xhr, status, error) {
            var errorMsg = '서버 통신 중 오류가 발생했습니다.';
            if (xhr.responseText) {
                try {
                    var errorRes = JSON.parse(xhr.responseText);
                    if (errorRes && errorRes.message) {
                        errorMsg = errorRes.message;
                    }
                } catch(e) {
                    // JSON 파싱 실패 시 기본 메시지 사용
                }
            }
            alert(errorMsg + '\n상태: ' + status + '\n오류: ' + error);
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        $('#today_platform_sales').text(formatCurrency(summary.today_platform_sales || 0));
        $('#total_shop_count').text(formatNumber(summary.total_shop_count || 0) + ' 개');
        $('#active_shop_count').text(formatNumber(summary.active_shop_count || 0));
        $('#total_member_count').text(formatNumber(summary.total_member_count || 0) + ' 명');
        $('#active_member_count').text(formatNumber(summary.active_member_count || 0));
        $('#today_appointment_count').text(formatNumber(summary.today_appointment_count || 0) + ' 건');
        $('#platform_avg_rating').text((summary.platform_avg_rating || 0).toFixed(1) + ' 점');
        $('#pending_settlement_amount').text(formatCurrency(summary.pending_settlement_amount || 0));
    }

    function formatDateLabel(dateStr, periodType) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var day = parseInt(parts[2], 10);
        
        if (isNaN(year) || isNaN(month) || isNaN(day)) return dateStr;
        
        if (periodType === 'monthly') {
            return year + '년 ' + month + '월';
        } else if (periodType === 'weekly') {
            return month + '/' + day; // 주차 정보가 없으므로 월/일로 표시
        } else {
            return month + '/' + day;
        }
    }

    function renderSalesTrendChart(salesTrend, periodType) {
        var labels = [];
        var data = [];

        if (salesTrend && salesTrend.length) {
            for (var i = 0; i < salesTrend.length; i++) {
                var row = salesTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.amount || 0);
            }
        }

        var ctx = document.getElementById('sales_trend_chart').getContext('2d');
        if (charts.salesTrendChart) {
            charts.salesTrendChart.destroy();
        }

        charts.salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '매출: ' + formatCurrency(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderAppointmentTrendChart(appointmentTrend, periodType) {
        var labels = [];
        var data = [];

        if (appointmentTrend && appointmentTrend.length) {
            for (var i = 0; i < appointmentTrend.length; i++) {
                var row = appointmentTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('appointment_trend_chart').getContext('2d');
        if (charts.appointmentTrendChart) {
            charts.appointmentTrendChart.destroy();
        }

        charts.appointmentTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
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
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderShopStatusChart(shopStatusDistribution) {
        var labels = [];
        var data = [];
        var colors = [];

        if (shopStatusDistribution) {
            if (shopStatusDistribution.active > 0) {
                labels.push('정상');
                data.push(shopStatusDistribution.active);
                colors.push('#4CAF50');
            }
            if (shopStatusDistribution.pending > 0) {
                labels.push('대기');
                data.push(shopStatusDistribution.pending);
                colors.push('#FFC107');
            }
            if (shopStatusDistribution.closed > 0) {
                labels.push('폐업');
                data.push(shopStatusDistribution.closed);
                colors.push('#9E9E9E');
            }
            if (shopStatusDistribution.shutdown > 0) {
                labels.push('금지');
                data.push(shopStatusDistribution.shutdown);
                colors.push('#F44336');
            }
        }

        var ctx = document.getElementById('shop_status_chart').getContext('2d');
        if (charts.shopStatusChart) {
            charts.shopStatusChart.destroy();
        }

        charts.shopStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderMemberSignupTrendChart(memberSignupTrend, periodType) {
        var labels = [];
        var data = [];

        if (memberSignupTrend && memberSignupTrend.length) {
            for (var i = 0; i < memberSignupTrend.length; i++) {
                var row = memberSignupTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('member_signup_trend_chart').getContext('2d');
        if (charts.memberSignupTrendChart) {
            charts.memberSignupTrendChart.destroy();
        }

        charts.memberSignupTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '가입 건수',
                    data: data,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '가입 건수: ' + formatNumber(context.parsed.y || 0) + '명';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderRecentSettlementTable(settlements) {
        var $tbody = $('#recent_settlement_table tbody');
        $tbody.empty();

        if (!settlements || !settlements.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td></tr>');
            return;
        }

        settlements.forEach(function(row) {
            var tr = '<tr>' +
                '<td class="text-center">' + (row.settlement_date || '-') + '</td>' +
                '<td class="text-center">' + (row.shop_name || '-') + '</td>' +
                '<td class="text-right pr-2">' + formatCurrency(row.settlement_amount || 0) + '</td>' +
                '<td class="text-center">' + (row.settlement_status_kr || row.settlement_status || '-') + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function renderRecentShopRegistrationTable(shops) {
        var $tbody = $('#recent_shop_registration_table tbody');
        $tbody.empty();

        if (!shops || !shops.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 가맹점이 없습니다.</td></tr>');
            return;
        }

        shops.forEach(function(row) {
            var dateStr = row.created_at ? row.created_at.split(' ')[0] : '-';
            var tr = '<tr>' +
                '<td class="text-center">' + dateStr + '</td>' +
                '<td class="text-center">' + (row.shop_name || '-') + '</td>' +
                '<td class="text-center">' + (row.category_name || '-') + '</td>' +
                '<td class="text-center">' + (row.status_kr || row.status || '-') + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function renderRecentMemberSignupTable(members) {
        var $tbody = $('#recent_member_signup_table tbody');
        $tbody.empty();

        if (!members || !members.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 회원이 없습니다.</td></tr>');
            return;
        }

        members.forEach(function(row) {
            var dateStr = row.created_at ? row.created_at.split(' ')[0] : '-';
            var tr = '<tr>' +
                '<td class="text-center">' + dateStr + '</td>' +
                '<td class="text-center">' + (row.user_id || '-') + '</td>' +
                '<td class="text-center">' + (row.name || row.nickname || '-') + '</td>' +
                '<td class="text-center">' + (row.email || '-') + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function initDefaultDates() {
        var today = new Date();
        var endDate = today.toISOString().slice(0, 10);
        var start = new Date();
        start.setMonth(start.getMonth() - 1); // 한 달 전
        var startDate = start.toISOString().slice(0, 10);

        if (!$('#start_date').val()) {
            $('#start_date').val(startDate);
        }
        if (!$('#end_date').val()) {
            $('#end_date').val(endDate);
        }
    }

    $(function() {
        initDefaultDates();

        $('#search_btn').on('click', function() {
            loadDashboardData();
        });

        // 최초 진입 시 자동 조회
        loadDashboardData();
    });
})(jQuery);
</script>

