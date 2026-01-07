<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var settlementStatusChart = null;
    var settlementTrendChart = null;
    var settlementCycleChart = null;
    var shopSettlementRankChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    // AJAX 데이터 로드
    function loadSettlementStatistics() {
        var periodType = $('#period_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var statusFilter = $('#status_filter').val() || '';
        var cycleFilter = $('#cycle_filter').val() || '';

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/platform_statistics_settlement_data.php',
            type: 'POST',
            dataType: 'json',
            data: {
                period_type: periodType,
                start_date: startDate,
                end_date: endDate,
                status: statusFilter,
                cycle: cycleFilter
            }
        }).done(function(res) {
            if (!res || !res.success) {
                alert(res && res.message ? res.message : '데이터 조회에 실패했습니다.');
                return;
            }

            var data = res.data || {};
            // 현재 데이터 저장
            currentChartData = data;
            
            updateSummaryCards(data.summary);
            renderStatusDistributionChart(data.status_distribution);
            renderSettlementTrendChart(data.settlement_trend);
            renderCycleDistributionChart(data.cycle_distribution);
            renderShopSettlementRankChart(data.shop_settlement_rank);
            renderSettlementDetailTable(data.settlement_detail_list);
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

        $('#total_settlement_amount').text(formatCurrency(summary.total_settlement_amount || 0));
        $('#pending_settlement_amount').text(formatCurrency(summary.pending_settlement_amount || 0));
        $('#period_settlement_amount').text(formatCurrency(summary.period_settlement_amount || 0));
        $('#completed_count').text(formatNumber(summary.completed_count || 0));
        $('#pending_count').text(formatNumber(summary.pending_count || 0));
        
        var avgAmount = summary.avg_settlement_amount || 0;
        $('#avg_settlement_amount').text(formatCurrency(avgAmount));
    }

    // 정산 상태별 분포 차트 (파이)
    function renderStatusDistributionChart(statusData) {
        if (!statusData) statusData = {};

        // shop_settlements 테이블은 대문자 상태값 사용
        var labels = [];
        var data = [];
        var colors = [];
        
        // COMPLETED (완료)
        if (statusData.COMPLETED && (statusData.COMPLETED.count || 0) > 0) {
            labels.push('완료');
            data.push(statusData.COMPLETED.count || 0);
            colors.push('#4CAF50');
        }
        
        // PENDING (대기)
        if (statusData.PENDING && (statusData.PENDING.count || 0) > 0) {
            labels.push('대기');
            data.push(statusData.PENDING.count || 0);
            colors.push('#FFC107');
        }
        
        // 기타 상태값들
        for (var key in statusData) {
            if (key !== 'COMPLETED' && key !== 'PENDING' && statusData[key] && (statusData[key].count || 0) > 0) {
                labels.push(key);
                data.push(statusData[key].count || 0);
                colors.push('#9E9E9E');
            }
        }
        
        // 데이터가 없을 경우 기본값
        if (labels.length === 0) {
            labels = ['완료', '대기'];
            data = [0, 0];
            colors = ['#4CAF50', '#FFC107'];
        }

        var ctx = document.getElementById('settlement_status_chart');
        if (!ctx) return;

        if (settlementStatusChart) {
            settlementStatusChart.destroy();
        }

        settlementStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors
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

    // 기간별 정산 금액 추이 차트 (선 그래프)
    function renderSettlementTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            var dateStr = trendData[i].date || '';
            // 날짜 포맷팅 (YYYY-MM-DD -> MM/DD)
            if (dateStr) {
                var dateParts = dateStr.split('-');
                if (dateParts.length === 3) {
                    labels.push(dateParts[1] + '/' + dateParts[2]);
                } else {
                    labels.push(dateStr);
                }
            } else {
                labels.push('');
            }
            data.push(trendData[i].amount || 0);
        }

        var ctx = document.getElementById('settlement_trend_chart');
        if (!ctx) return;

        if (settlementTrendChart) {
            settlementTrendChart.destroy();
        }

        settlementTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '정산 금액',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
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
                                return '정산 금액: ' + formatCurrency(context.parsed.y || 0);
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

    // 정산 주기별 분포 차트 (파이)
    function renderCycleDistributionChart(cycleData) {
        if (!cycleData) cycleData = [];

        var labels = [];
        var data = [];
        var cycleLabels = {
            'daily': '일일',
            'weekly': '주간',
            'monthly': '월간'
        };

        for (var i = 0; i < cycleData.length; i++) {
            var cycle = cycleData[i].cycle || '';
            var cycleLabel = cycleLabels[cycle] || cycle || '미지정';
            labels.push(cycleLabel);
            data.push(cycleData[i].count || 0);
        }

        var ctx = document.getElementById('settlement_cycle_chart');
        if (!ctx) return;

        if (settlementCycleChart) {
            settlementCycleChart.destroy();
        }

        settlementCycleChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
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

    // 가맹점별 정산 금액 순위 차트 (가로 막대)
    function renderShopSettlementRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        // 최대 20개, 가맹점명이 길면 축약
        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var shopName = rankData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            data.push(rankData[i].total_settlement || 0);
        }

        var ctx = document.getElementById('shop_settlement_rank_chart');
        if (!ctx) return;

        if (shopSettlementRankChart) {
            shopSettlementRankChart.destroy();
        }

        shopSettlementRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '정산 금액',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
                                return '정산 금액: ' + formatCurrency(context.parsed.x || 0);
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

    // 정산 처리 내역 테이블 렌더링
    function renderSettlementDetailTable(detailList) {
        if (!detailList) detailList = [];

        var $tbody = $('#settlement_detail_table tbody');
        $tbody.empty();

        if (detailList.length === 0) {
            $tbody.append('<tr><td colspan="6" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < detailList.length; i++) {
            var item = detailList[i];
            var row = '<tr>';
            row += '<td>' + (item.settlement_id || '-') + '</td>';
            row += '<td>' + (item.shop_name || '-') + '</td>';
            row += '<td class="text-right">' + formatCurrency(item.settlement_amount || 0) + '</td>';
            row += '<td>' + (item.settlement_status_kr || '-') + '</td>';
            var dateStr = item.settlement_date || '';
            if (dateStr) {
                var dateParts = dateStr.split(' ');
                row += '<td>' + (dateParts[0] || dateStr) + '</td>';
            } else {
                row += '<td>-</td>';
            }
            row += '<td>' + (item.settlement_cycle_kr || '-') + '</td>';
            row += '</tr>';
            $tbody.append(row);
        }
    }

    // 현재 차트 데이터 저장
    var currentChartData = {};

    // 데이터 내보내기 버튼 (TODO: 실제 구현 필요)
    $('#export_btn').on('click', function() {
        alert('데이터 내보내기 기능은 추후 구현 예정입니다.');
    });

    // 초기화
$(document).ready(function() {
    // 초기 데이터 로드
        loadSettlementStatistics();
    
    // 조회 버튼 클릭 이벤트
    $('#search_btn').on('click', function() {
            loadSettlementStatistics();
    });
});

})(jQuery);
</script>

