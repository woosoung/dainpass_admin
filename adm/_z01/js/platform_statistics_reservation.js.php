<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var reservationTrendChart = null;
    var statusDistributionChart = null;
    var hourlyReservationChart = null;
    var weeklyReservationChart = null;
    var categoryReservationChart = null;
    var regionReservationChart = null;
    var shopReservationRankChart = null;
    var regionReservationChartType = 'pie';

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    function formatPercent(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toFixed(1) + '%';
    }

    // AJAX 데이터 로드
    function loadReservationStatistics() {
        var periodType = $('#period_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var categoryId = $('#category_filter').val() || '';
        var region = $('#region_filter').val() || '';
        var status = $('#status_filter').val() || '';

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/platform_statistics_reservation_data.php',
            type: 'POST',
            dataType: 'json',
            data: {
                period_type: periodType,
                start_date: startDate,
                end_date: endDate,
                category_id: categoryId,
                region: region,
                status: status
            }
        }).done(function(res) {
            if (!res || !res.success) {
                alert(res && res.message ? res.message : '데이터 조회에 실패했습니다.');
                return;
            }

            var data = res.data || {};
            // 현재 데이터 저장 (차트 타입 전환용)
            currentChartData = data;
            
            updateSummaryCards(data.summary);
            renderReservationTrendChart(data.reservation_trend);
            renderStatusDistributionChart(data.status_distribution);
            renderHourlyReservationChart(data.hourly_reservation);
            renderWeeklyReservationChart(data.weekly_reservation);
            renderCategoryReservationChart(data.category_reservation);
            renderRegionReservationChart(data.region_reservation, regionReservationChartType);
            renderShopReservationRankChart(data.shop_reservation_rank);
            renderReservationTable(data.reservation_list);
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
            alert(errorMsg);
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    // 주요 지표 카드 업데이트
    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_reservation_count').text(formatNumber(summary.total_reservation_count || 0) + '건');
        $('#completed_count').text(formatNumber(summary.completed_count || 0) + '건');
        $('#cancelled_count').text(formatNumber(summary.cancelled_count || 0) + '건');
        $('#completion_rate').text(formatPercent(summary.completion_rate || 0));
        $('#cancellation_rate').text(formatPercent(summary.cancellation_rate || 0));
        $('#avg_daily_reservation').text(formatNumber(summary.avg_daily_reservation || 0) + '건');
    }

    // 기간별 예약 건수 추이 차트 (선 그래프)
    function renderReservationTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].count || 0);
        }

        var ctx = document.getElementById('reservation_trend_chart');
        if (!ctx) return;

        if (reservationTrendChart) {
            reservationTrendChart.destroy();
        }

        reservationTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
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

    // 예약 상태별 분포 차트 (파이)
    function renderStatusDistributionChart(statusData) {
        if (!statusData) statusData = [];

        var labels = [];
        var data = [];
        var colors = ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#9E9E9E', '#FF9800'];

        for (var i = 0; i < statusData.length; i++) {
            var statusName = statusData[i].status || '미지정';
            // 상태명 한글 변환
            switch(statusName) {
                case 'COMPLETED': statusName = '완료'; break;
                case 'PENDING': statusName = '대기'; break;
                case 'CANCELLED':
                case 'CANCELED':
                case 'CANCEL': statusName = '취소'; break;
                case 'CONFIRMED': statusName = '확정'; break;
                case 'NO_SHOW': statusName = '노쇼'; break;
                case 'BOOKED': statusName = '예약됨'; break;
            }
            labels.push(statusName);
            data.push(statusData[i].count || 0);
        }

        var ctx = document.getElementById('status_distribution_chart');
        if (!ctx) return;

        if (statusDistributionChart) {
            statusDistributionChart.destroy();
        }

        statusDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '건 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 시간대별 예약 건수 차트 (막대)
    function renderHourlyReservationChart(hourlyData) {
        if (!hourlyData) hourlyData = [];

        // 0~23시 모든 시간대 초기화
        var labels = [];
        var data = [];
        for (var h = 0; h < 24; h++) {
            labels.push(h + '시');
            data.push(0);
        }

        // 실제 데이터로 채우기
        for (var i = 0; i < hourlyData.length; i++) {
            var hour = hourlyData[i].hour || 0;
            if (hour >= 0 && hour < 24) {
                data[hour] = hourlyData[i].count || 0;
            }
        }

        var ctx = document.getElementById('hourly_reservation_chart');
        if (!ctx) return;

        if (hourlyReservationChart) {
            hourlyReservationChart.destroy();
        }

        hourlyReservationChart = new Chart(ctx, {
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
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

    // 요일별 예약 건수 차트 (막대)
    function renderWeeklyReservationChart(weeklyData) {
        if (!weeklyData) weeklyData = [];

        // 요일 순서대로 초기화 (일요일=0부터 토요일=6까지)
        var dayOrder = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
        var labels = [];
        var data = [];
        for (var d = 0; d < 7; d++) {
            labels.push(dayOrder[d]);
            data.push(0);
        }

        // 실제 데이터로 채우기
        for (var i = 0; i < weeklyData.length; i++) {
            var dayNum = weeklyData[i].day_num || 0;
            if (dayNum >= 0 && dayNum < 7) {
                data[dayNum] = weeklyData[i].count || 0;
            }
        }

        var ctx = document.getElementById('weekly_reservation_chart');
        if (!ctx) return;

        if (weeklyReservationChart) {
            weeklyReservationChart.destroy();
        }

        weeklyReservationChart = new Chart(ctx, {
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
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

    // 업종별 예약 건수 분포 차트 (가로 막대)
    function renderCategoryReservationChart(categoryData) {
        if (!categoryData) categoryData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < categoryData.length; i++) {
            labels.push(categoryData[i].category_name || '미지정');
            data.push(categoryData[i].count || 0);
        }

        var ctx = document.getElementById('category_reservation_chart');
        if (!ctx) return;

        if (categoryReservationChart) {
            categoryReservationChart.destroy();
        }

        categoryReservationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.x || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    x: {
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

    // 지역별 예약 건수 분포 차트 (파이/막대)
    function renderRegionReservationChart(regionData, chartType) {
        if (!regionData) regionData = [];
        if (!chartType) chartType = regionReservationChartType;

        var labels = [];
        var data = [];

        for (var i = 0; i < regionData.length; i++) {
            labels.push(regionData[i].region || '미지정');
            data.push(regionData[i].count || 0);
        }

        var ctx = document.getElementById('region_reservation_chart');
        if (!ctx) return;

        if (regionReservationChart) {
            regionReservationChart.destroy();
        }

        var chartConfig = {
            type: chartType || 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: chartType === 'pie' ?
                        ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'] :
                        'rgba(54, 162, 235, 0.6)',
                    borderColor: chartType === 'pie' ?
                        ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'] :
                        'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: chartType === 'pie' ? 'right' : 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || (chartType === 'pie' ? context.parsed : context.parsed.y);
                                if (chartType === 'pie') {
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + formatNumber(value) + '건 (' + percentage + '%)';
                                } else {
                                    return label + ': ' + formatNumber(value) + '건';
                                }
                            }
                        }
                    }
                }
            }
        };

        if (chartType === 'bar') {
            chartConfig.options.indexAxis = 'y';
            chartConfig.options.scales = {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatNumber(value);
                        }
                    }
                }
            };
        }

        regionReservationChart = new Chart(ctx, chartConfig);
    }

    // 가맹점별 예약 건수 순위 차트 (가로 막대)
    function renderShopReservationRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var shopName = rankData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            data.push(rankData[i].count || 0);
        }

        var ctx = document.getElementById('shop_reservation_rank_chart');
        if (!ctx) return;

        if (shopReservationRankChart) {
            shopReservationRankChart.destroy();
        }

        shopReservationRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.x || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    x: {
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

    // 예약 상세 내역 테이블 렌더링
    function renderReservationTable(reservationList) {
        if (!reservationList) reservationList = [];

        var $tbody = $('#reservation_detail_table tbody');
        $tbody.empty();

        if (reservationList.length === 0) {
            $tbody.append('<tr><td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < reservationList.length; i++) {
            var item = reservationList[i];
            
            // 상태명 한글 변환
            var statusName = item.status || '-';
            switch(statusName) {
                case 'COMPLETED': statusName = '완료'; break;
                case 'PENDING': statusName = '대기'; break;
                case 'CANCELLED':
                case 'CANCELED':
                case 'CANCEL': statusName = '취소'; break;
                case 'CONFIRMED': statusName = '확정'; break;
                case 'NO_SHOW': statusName = '노쇼'; break;
                case 'BOOKED': statusName = '예약됨'; break;
            }
            
            // 날짜 포맷팅 (PostgreSQL timestamp 형식 처리)
            var appointmentDate = item.appointment_datetime || '';
            var createdDate = item.created_at || '';
            
            function formatDateTime(dateStr) {
                if (!dateStr) return '-';
                // ISO 형식 (YYYY-MM-DDTHH:mm:ss) 또는 PostgreSQL 형식 (YYYY-MM-DD HH:mm:ss) 처리
                var formatted = dateStr.toString();
                // T를 공백으로 변환
                formatted = formatted.replace('T', ' ');
                // .000000 같은 마이크로초 제거
                formatted = formatted.replace(/\.\d+/g, '');
                // 초 제거 (YYYY-MM-DD HH:mm 형식으로)
                if (formatted.length > 16) {
                    formatted = formatted.substring(0, 16);
                }
                return formatted;
            }
            
            appointmentDate = formatDateTime(appointmentDate);
            createdDate = formatDateTime(createdDate);
            
            var row = '<tr>';
            row += '<td>' + appointmentDate + '</td>';
            row += '<td>' + (item.shop_name || '-') + '</td>';
            row += '<td>' + statusName + '</td>';
            row += '<td class="text-right">' + formatCurrency(item.balance_amount || 0) + '</td>';
            row += '<td>' + createdDate + '</td>';
            row += '</tr>';
            $tbody.append(row);
        }
    }

    // 현재 차트 데이터 저장
    var currentChartData = {};

    // 차트 타입 전환 버튼 이벤트
    $(document).on('click', '.chart-type-btn', function() {
        var $btn = $(this);
        var chartId = $btn.data('chart');
        var chartType = $btn.data('type');
        var $btnGroup = $btn.siblings('.chart-type-btn').addBack();

        $btnGroup.removeClass('active');
        $btn.addClass('active');

        // 차트 타입 저장 및 재렌더링
        if (chartId === 'region_reservation_chart') {
            regionReservationChartType = chartType;
            if (currentChartData.region_reservation) {
                renderRegionReservationChart(currentChartData.region_reservation, chartType);
            }
        }
    });

    // 데이터 내보내기 버튼 (TODO: 실제 구현 필요)
    $('#export_btn').on('click', function() {
        alert('데이터 내보내기 기능은 추후 구현 예정입니다.');
    });

    // 초기화
    $(document).ready(function() {
        // 초기 데이터 로드
        loadReservationStatistics();

        // 조회 버튼 클릭 이벤트
        $('#search_btn').on('click', function() {
            loadReservationStatistics();
        });
    });

})(jQuery);
</script>

