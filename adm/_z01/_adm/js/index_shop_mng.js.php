<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var charts = {
        salesTrendChart: null,
        appointmentTrendChart: null,
        hourlyAppointmentChart: null,
        weeklyAppointmentChart: null,
        customerTypeChart: null,
        servicePopularityChart: null,
        serviceSalesChart: null,
        customerAmountChart: null,
        couponIssueUseTrendChart: null,
        discountAmountTrendChart: null
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
            url: '../ajax/index_shop_mng_data.php',
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

            // 통계 카드 업데이트
            updateSummaryCards(res);
            
            // 차트 렌더링 (period_type 전달)
            var periodType = res.period_type || 'daily';
            renderSalesTrendChart(res.daily_sales, periodType);
            renderAppointmentTrendChart(res.daily_appointments, periodType);
            renderHourlyAppointmentChart(res.hourly_appointments);
            renderWeeklyAppointmentChart(res.weekly_appointments);
            renderCustomerTypeChart(res.customer_type_distribution);
            renderServicePopularityChart(res.service_popularity);
            renderServiceSalesChart(res.service_sales);
            renderCustomerAmountChart(res.top_customers);
            renderCouponIssueUseTrendChart(res.coupon_issue_use_trend, periodType);
            renderDiscountAmountTrendChart(res.discount_amount_trend, periodType);
            
            // 정산 처리 내역 테이블 업데이트
            renderSettlementTable(res.settlement_logs);
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

    function updateSummaryCards(res) {
        // 매출 요약은 우선 대시보드용 통합 응답인 sales_summary를 사용하고,
        // 혹시 없으면 각 통계 ajax의 기본 키인 summary를 폴백으로 사용한다.
        var sales = res.sales_summary || res.summary || {};
        var reservation = res.reservation_summary || {};
        var customer = res.customer_summary || {};
        var service = res.service_summary || {};
        var coupon = res.coupon_summary || {};
        var settlementDeduction = res.settlement_deduction || {};

        // 매출/정산 통계
        $('#today_sales_amount').text(formatCurrency(sales.today_sales_amount || 0));
        $('#today_appointment_count').text(formatNumber(sales.today_appointment_count || 0));
        // month_sales_amount: 매출/정산통계 페이지와 동일하게 표시
        // sales.month_sales_amount가 문자열로 전달될 수 있으므로 Number로 변환
        var monthSalesAmount = sales.month_sales_amount;
        console.log('monthSalesAmount (before processing):', monthSalesAmount, typeof monthSalesAmount);
        if (monthSalesAmount === undefined || monthSalesAmount === null) {
            console.warn('monthSalesAmount is undefined or null, setting to 0');
            monthSalesAmount = 0;
        } else {
            monthSalesAmount = Number(monthSalesAmount);
            if (isNaN(monthSalesAmount)) {
                console.warn('monthSalesAmount is NaN, setting to 0');
                monthSalesAmount = 0;
            }
        }
        console.log('monthSalesAmount (after processing):', monthSalesAmount);
        $('#month_sales_amount').text(formatCurrency(monthSalesAmount));
        $('#month_appointment_count').text(formatNumber(sales.month_appointment_count || 0));
        $('#range_cancel_amount').text(formatCurrency(sales.range_cancel_amount || 0));
        $('#range_cancel_count').text(formatNumber(sales.range_cancel_count || 0));
        var cancelRate = typeof sales.range_cancel_rate !== 'undefined' ? sales.range_cancel_rate : 0;
        $('#range_cancel_rate').text(cancelRate.toFixed(1) + '%');
        $('#settlement_net_amount').text(formatCurrency(settlementDeduction.total_settlement_amount || 0));
        $('#settlement_total_sales').text(formatCurrency(settlementDeduction.total_sales_amount || 0));

        // 예약/운영 통계
        $('#total_appointment_count').text(formatNumber(reservation.total_appointment_count || 0) + ' 건');
        $('#active_appointment_count').text(formatNumber(reservation.active_appointment_count || 0));
        $('#repeat_visit_rate').text((reservation.repeat_visit_rate || 0).toFixed(1) + ' %');
        $('#repeat_customer_count').text(formatNumber(reservation.repeat_customer_count || 0));
        $('#avg_appointment_per_customer').text((reservation.avg_appointment_per_customer || 0).toFixed(1) + ' 회');

        // 고객 통계
        $('#new_customer_count').text(formatNumber(customer.new_customer_count || 0) + ' 명');
        $('#existing_customer_count').text(formatNumber(customer.existing_customer_count || 0));
        // 평균 예약 금액: 소수점 이하 제거
        var avgAmount = customer.avg_amount_per_customer || 0;
        $('#avg_amount_per_customer').text(formatCurrency(Math.floor(avgAmount)));
        $('#avg_appointment_frequency').text((customer.avg_appointment_frequency || 0).toFixed(1) + ' 회');

        // 서비스/리뷰 통계
        $('#total_services').text(formatNumber(service.total_services || 0) + ' 개');
        $('#total_service_sales').text(formatCurrency(service.total_service_sales || 0));
        $('#avg_rating').text((service.avg_rating || 0).toFixed(1) + ' 점');
        $('#review_count').text(formatNumber(service.review_count || 0));
        $('#avg_appointment_per_service').text((service.avg_appointment_per_service || 0).toFixed(1) + ' 건');

        // 쿠폰 통계
        $('#total_coupon_issued').text(formatNumber(coupon.total_coupon_issued || 0) + ' 개');
        $('#total_coupon_used').text(formatNumber(coupon.total_coupon_used || 0));
        var couponUsageRate = typeof coupon.coupon_usage_rate !== 'undefined' ? coupon.coupon_usage_rate : 0;
        $('#coupon_usage_rate').text(couponUsageRate.toFixed(1) + ' %');
    }

    function formatDateLabel(dateStr, periodType) {
        if (!dateStr) return '';
        // PostgreSQL 날짜 형식 (YYYY-MM-DD)을 파싱
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var day = parseInt(parts[2], 10);
        
        if (isNaN(year) || isNaN(month) || isNaN(day)) return dateStr;
        
        if (periodType === 'monthly') {
            return year + '년 ' + month + '월';
        } else if (periodType === 'weekly') {
            return month + '/' + day;
        } else {
            // 일별: M/D 형식
            return month + '/' + day;
        }
    }

    function renderSalesTrendChart(dailySales, periodType) {
        var labels = [];
        var totalData = [];
        var netData = [];

        if (dailySales && dailySales.length) {
            for (var i = 0; i < dailySales.length; i++) {
                var row = dailySales[i];
                labels.push(formatDateLabel(row.date, periodType));
                totalData.push(row.total_sales || 0);
                netData.push(row.net_sales || 0);
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
                datasets: [
                    {
                        label: '총 매출',
                        data: totalData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2
                    },
                    {
                        label: '순 매출',
                        data: netData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y || 0);
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

    function renderAppointmentTrendChart(dailyAppointments, periodType) {
        var labels = [];
        var data = [];

        if (dailyAppointments && dailyAppointments.length) {
            for (var i = 0; i < dailyAppointments.length; i++) {
                var row = dailyAppointments[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.total_count || 0);
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
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.2
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
        if (charts.hourlyAppointmentChart) {
            charts.hourlyAppointmentChart.destroy();
        }

        charts.hourlyAppointmentChart = new Chart(ctx, {
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

    function renderWeeklyAppointmentChart(weeklyAppointments) {
        var labels = [];
        var data = [];

        if (weeklyAppointments && weeklyAppointments.length) {
            for (var i = 0; i < weeklyAppointments.length; i++) {
                var row = weeklyAppointments[i];
                labels.push(row.weekday_name);
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('weekly_appointment_chart').getContext('2d');
        if (charts.weeklyAppointmentChart) {
            charts.weeklyAppointmentChart.destroy();
        }

        charts.weeklyAppointmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
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

    function renderCustomerTypeChart(customerTypeDistribution) {
        var labels = [];
        var data = [];

        if (customerTypeDistribution && customerTypeDistribution.length) {
            for (var i = 0; i < customerTypeDistribution.length; i++) {
                var row = customerTypeDistribution[i];
                labels.push(row.type);
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('customer_type_chart').getContext('2d');
        if (charts.customerTypeChart) {
            charts.customerTypeChart.destroy();
        }

        charts.customerTypeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatNumber(context.parsed || 0) + '명';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderServicePopularityChart(servicePopularity) {
        var labels = [];
        var data = [];

        if (servicePopularity && servicePopularity.length) {
            for (var i = 0; i < servicePopularity.length; i++) {
                var row = servicePopularity[i];
                labels.push(row.service_name || '서비스 ' + row.service_id);
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('service_popularity_chart').getContext('2d');
        if (charts.servicePopularityChart) {
            charts.servicePopularityChart.destroy();
        }

        charts.servicePopularityChart = new Chart(ctx, {
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

    function renderServiceSalesChart(serviceSales) {
        var labels = [];
        var data = [];

        if (serviceSales && serviceSales.length) {
            for (var i = 0; i < serviceSales.length; i++) {
                var row = serviceSales[i];
                labels.push(row.service_name || '서비스 ' + row.service_id);
                data.push(row.total_sales || 0);
            }
        }

        var ctx = document.getElementById('service_sales_chart').getContext('2d');
        if (charts.serviceSalesChart) {
            charts.serviceSalesChart.destroy();
        }

        charts.serviceSalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
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

    function renderCustomerAmountChart(topCustomers) {
        var labels = [];
        var data = [];

        if (topCustomers && topCustomers.length) {
            for (var i = 0; i < topCustomers.length; i++) {
                var row = topCustomers[i];
                labels.push(row.customer_name || '고객 ' + row.customer_id);
                data.push(row.total_amount || 0);
            }
        }

        var ctx = document.getElementById('customer_amount_chart').getContext('2d');
        if (charts.customerAmountChart) {
            charts.customerAmountChart.destroy();
        }

        charts.customerAmountChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 금액',
                    data: data,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 금액: ' + formatCurrency(context.parsed.y || 0);
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

    function renderCouponIssueUseTrendChart(couponIssueUseTrend, periodType) {
        var labels = [];
        var issuedData = [];
        var usedData = [];

        if (couponIssueUseTrend && couponIssueUseTrend.length) {
            for (var i = 0; i < couponIssueUseTrend.length; i++) {
                var row = couponIssueUseTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                issuedData.push(row.issued_count || 0);
                usedData.push(row.used_count || 0);
            }
        }

        var ctx = document.getElementById('coupon_issue_use_trend_chart').getContext('2d');
        if (charts.couponIssueUseTrendChart) {
            charts.couponIssueUseTrendChart.destroy();
        }

        charts.couponIssueUseTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '발급 수',
                        data: issuedData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.2
                    },
                    {
                        label: '사용 수',
                        data: usedData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y || 0) + '개';
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

    function renderDiscountAmountTrendChart(discountAmountTrend, periodType) {
        var labels = [];
        var data = [];

        if (discountAmountTrend && discountAmountTrend.length) {
            for (var i = 0; i < discountAmountTrend.length; i++) {
                var row = discountAmountTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.discount_amount || 0);
            }
        }

        var ctx = document.getElementById('discount_amount_trend_chart').getContext('2d');
        if (charts.discountAmountTrendChart) {
            charts.discountAmountTrendChart.destroy();
        }

        charts.discountAmountTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '할인 금액',
                    data: data,
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.1)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '할인 금액: ' + formatCurrency(context.parsed.y || 0);
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

    function getSettlementStatusText(status) {
        if (!status) return '-';
        var statusMap = {
            'done': '완료',
            'pending': '대기',
            'failed': '실패'
        };
        return statusMap[status.toLowerCase()] || status;
    }

    function renderSettlementTable(settlementLogs) {
        var $tbody = $('#settlement_table tbody');
        $tbody.empty();

        if (!settlementLogs || !settlementLogs.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td></tr>');
            return;
        }

        settlementLogs.forEach(function(row) {
            var periodText = '';
            if (row.settlement_start_at || row.settlement_end_at) {
                periodText = (row.settlement_start_at || '-') + ' ~ ' + (row.settlement_end_at || '-');
            }

            var tr = '<tr>' +
                '<td class="text-center">' + (row.settlement_date || row.settlement_at || '-') + '</td>' +
                '<td class="text-center">' + (periodText || '-') + '</td>' +
                '<td class="text-right pr-2">' + formatCurrency(row.settlement_amount || 0) + '</td>' +
                '<td class="text-center">' + getSettlementStatusText(row.status) + '</td>' +
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

