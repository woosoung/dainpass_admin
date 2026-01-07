<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스들
    var salesTrendChart = null;
    var appointmentTrendChart = null;
    var shopStatusChart = null;
    var memberSignupTrendChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
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
            return month + '/' + day;
        } else {
            // 일별: M/D 형식
            return month + '/' + day;
        }
    }

    function initDefaultDates() {
        var today = new Date();
        var defaultStart = new Date();
        defaultStart.setDate(today.getDate() - 30);
        
        $('#start_date').val(defaultStart.toISOString().split('T')[0]);
        $('#end_date').val(today.toISOString().split('T')[0]);
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
            var summary = data.summary || {};

            // KPI 카드 업데이트
            updateSummaryCards(summary);
            
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
                } catch (e) {
                    // JSON 파싱 실패 시 기본 메시지 사용
                }
            }
            alert(errorMsg);
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

    function renderSalesTrendChart(data, periodType) {
        var ctx = document.getElementById('sales_trend_chart');
        if (!ctx) return;

        if (salesTrendChart) {
            salesTrendChart.destroy();
        }

        var labels = data.map(function(item) {
            return formatDateLabel(item.date, periodType);
        });
        var amounts = data.map(function(item) {
            return item.amount || 0;
        });

        salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: amounts,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '매출: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderAppointmentTrendChart(data, periodType) {
        var ctx = document.getElementById('appointment_trend_chart');
        if (!ctx) return;

        if (appointmentTrendChart) {
            appointmentTrendChart.destroy();
        }

        var labels = data.map(function(item) {
            return formatDateLabel(item.date, periodType);
        });
        var counts = data.map(function(item) {
            return item.count || 0;
        });

        appointmentTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: counts,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.y) + '건';
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

    function renderShopStatusChart(data) {
        var ctx = document.getElementById('shop_status_chart');
        if (!ctx) return;

        if (shopStatusChart) {
            shopStatusChart.destroy();
        }

        var labels = [];
        var values = [];
        var colors = [];

        if (data.active > 0) {
            labels.push('정상');
            values.push(data.active);
            colors.push('rgb(34, 197, 94)');
        }
        if (data.pending > 0) {
            labels.push('대기');
            values.push(data.pending);
            colors.push('rgb(234, 179, 8)');
        }
        if (data.closed > 0) {
            labels.push('폐업');
            values.push(data.closed);
            colors.push('rgb(156, 163, 175)');
        }
        if (data.shutdown > 0) {
            labels.push('중지');
            values.push(data.shutdown);
            colors.push('rgb(239, 68, 68)');
        }

        if (labels.length === 0) {
            labels = ['데이터 없음'];
            values = [1];
            colors = ['rgb(229, 231, 235)'];
        }

        shopStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
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

    function renderMemberSignupTrendChart(data, periodType) {
        var ctx = document.getElementById('member_signup_trend_chart');
        if (!ctx) return;

        if (memberSignupTrendChart) {
            memberSignupTrendChart.destroy();
        }

        var labels = data.map(function(item) {
            return formatDateLabel(item.date, periodType);
        });
        var counts = data.map(function(item) {
            return item.count || 0;
        });

        memberSignupTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '회원 가입 수',
                    data: counts,
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '가입 수: ' + formatNumber(context.parsed.y) + '명';
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

    function renderRecentSettlementTable(data) {
        var $tbody = $('#recent_settlement_table tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td></tr>');
            return;
        }

        data.forEach(function(item) {
            var dateStr = item.settlement_date ? item.settlement_date.split(' ')[0] : '-';
            var row = '<tr>' +
                '<td class="text-center">' + dateStr + '</td>' +
                '<td class="text-center">' + (item.shop_name || '-') + '</td>' +
                '<td class="text-center">' + formatCurrency(item.settlement_amount || 0) + '</td>' +
                '<td class="text-center">' + (item.settlement_status_kr || item.settlement_status || '-') + '</td>' +
                '</tr>';
            $tbody.append(row);
        });
    }

    function renderRecentShopRegistrationTable(data) {
        var $tbody = $('#recent_shop_registration_table tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 가맹점이 없습니다.</td></tr>');
            return;
        }

        data.forEach(function(item) {
            var dateStr = item.created_at ? item.created_at.split(' ')[0] : '-';
            var row = '<tr>' +
                '<td class="text-center">' + dateStr + '</td>' +
                '<td class="text-center">' + (item.shop_name || '-') + '</td>' +
                '<td class="text-center">' + (item.category_name || '-') + '</td>' +
                '<td class="text-center">' + (item.status_kr || item.status || '-') + '</td>' +
                '</tr>';
            $tbody.append(row);
        });
    }

    function renderRecentMemberSignupTable(data) {
        var $tbody = $('#recent_member_signup_table tbody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 회원이 없습니다.</td></tr>');
            return;
        }

        data.forEach(function(item) {
            var dateStr = item.created_at ? item.created_at.split(' ')[0] : '-';
            var name = item.name || item.nickname || '-';
            var row = '<tr>' +
                '<td class="text-center">' + dateStr + '</td>' +
                '<td class="text-center">' + (item.user_id || '-') + '</td>' +
                '<td class="text-center">' + name + '</td>' +
                '<td class="text-center">' + (item.email || '-') + '</td>' +
                '</tr>';
            $tbody.append(row);
        });
    }

    // 페이지 로드 시 초기화
    $(document).ready(function() {
        initDefaultDates();
        loadDashboardData();

        // 조회 버튼 클릭 이벤트
        $('#search_btn').on('click', function() {
            loadDashboardData();
        });
    });
})(jQuery);
</script>

