<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var transactionTypeChart = null;
    var pointTrendChart = null;
    var monthlyNetIncreaseChart = null;
    var balanceRangeChart = null;
    var memberBalanceRankChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatPoint(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR') + 'P';
    }

    function formatPercent(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toFixed(1) + '%';
    }

    // AJAX 데이터 로드
    function loadPointStatistics() {
        var periodType = $('#period_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/platform_statistics_point_data.php',
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
            currentChartData = data;
            
            updateSummaryCards(data.summary);
            renderTransactionTypeChart(data.transaction_type_distribution);
            renderPointTrendChart(data.point_trend);
            renderMonthlyNetIncreaseChart(data.monthly_net_increase);
            renderBalanceRangeChart(data.balance_range_distribution);
            renderMemberBalanceRankChart(data.member_balance_rank);
            renderTransactionTable(data.transaction_list);
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

        $('#total_earned').text(formatPoint(summary.total_earned || 0));
        $('#total_earned_cancelled').text(formatPoint(summary.total_earned_cancelled || 0));
        $('#total_used').text(formatPoint(summary.total_used || 0));
        $('#total_used_cancelled').text(formatPoint(summary.total_used_cancelled || 0));
        $('#total_balance').text(formatPoint(summary.total_balance || 0));
        $('#used_member_count').text(formatNumber(summary.used_member_count || 0) + '명');
        $('#avg_balance').text(formatPoint(summary.avg_balance || 0));
        $('#usage_rate').text(formatPercent(summary.usage_rate || 0));
        $('#earned_cancel_rate').text(formatPercent(summary.earned_cancel_rate || 0));
    }

    // 포인트 거래 유형별 분포 차트 (파이 차트)
    function renderTransactionTypeChart(distributionData) {
        if (!distributionData) distributionData = {};

        var labels = ['적립', '적립취소', '사용', '사용취소'];
        var data = [
            distributionData['적립'] || 0,
            distributionData['적립취소'] || 0,
            distributionData['사용'] || 0,
            distributionData['사용취소'] || 0
        ];
        var colors = ['#4BC0C0', '#FF9800', '#36A2EB', '#9C27B0']; // 적립: 청록색, 적립취소: 주황색, 사용: 파란색, 사용취소: 보라색

        var ctx = document.getElementById('transaction_type_chart');
        if (!ctx) return;

        if (transactionTypeChart) {
            transactionTypeChart.destroy();
        }

        transactionTypeChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
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
                                return label + ': ' + formatPoint(value) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 기간별 포인트 적립/적립취소/사용/사용취소 추이 차트 (선 그래프)
    function renderPointTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var earnedData = [];
        var earnedCancelledData = [];
        var usedData = [];
        var usedCancelledData = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            earnedData.push(trendData[i].earned || 0);
            earnedCancelledData.push(trendData[i].earned_cancelled || 0);
            usedData.push(trendData[i].used || 0);
            usedCancelledData.push(trendData[i].used_cancelled || 0);
        }

        var ctx = document.getElementById('point_trend_chart');
        if (!ctx) return;

        if (pointTrendChart) {
            pointTrendChart.destroy();
        }

        pointTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '적립',
                        data: earnedData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2,
                        fill: false
                    },
                    {
                        label: '적립취소',
                        data: earnedCancelledData,
                        borderColor: 'rgba(255, 152, 0, 1)',
                        backgroundColor: 'rgba(255, 152, 0, 0.1)',
                        tension: 0.2,
                        fill: false
                    },
                    {
                        label: '사용',
                        data: usedData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.2,
                        fill: false
                    },
                    {
                        label: '사용취소',
                        data: usedCancelledData,
                        borderColor: 'rgba(156, 39, 176, 1)',
                        backgroundColor: 'rgba(156, 39, 176, 0.1)',
                        tension: 0.2,
                        fill: false
                    }
                ]
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
                                return context.dataset.label + ': ' + formatPoint(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatPoint(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // 월별 포인트 순 증가량 차트 (막대 차트)
    function renderMonthlyNetIncreaseChart(netIncreaseData) {
        if (!netIncreaseData) netIncreaseData = [];

        var labels = [];
        var data = [];
        var colors = [];

        for (var i = 0; i < netIncreaseData.length; i++) {
            var month = netIncreaseData[i].month || '';
            if (month) {
                // YYYY-MM-DD 형식을 YYYY-MM으로 변환
                labels.push(month.substring(0, 7));
            } else {
                labels.push('');
            }
            var value = netIncreaseData[i].net_increase || 0;
            data.push(value);
            // 양수는 파란색, 음수는 빨간색
            colors.push(value >= 0 ? 'rgba(54, 162, 235, 0.6)' : 'rgba(255, 99, 132, 0.6)');
        }

        var ctx = document.getElementById('monthly_net_increase_chart');
        if (!ctx) return;

        if (monthlyNetIncreaseChart) {
            monthlyNetIncreaseChart.destroy();
        }

        monthlyNetIncreaseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '순 증가량',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(function(c) {
                        return c.replace('0.6', '1');
                    }),
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
                                return '순 증가량: ' + formatPoint(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return formatPoint(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // 포인트 잔액 구간별 회원 분포 차트 (가로 막대 차트)
    function renderBalanceRangeChart(rangeData) {
        if (!rangeData) rangeData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < rangeData.length; i++) {
            labels.push(rangeData[i].range_label || '미지정');
            data.push(rangeData[i].member_count || 0);
        }

        var ctx = document.getElementById('balance_range_chart');
        if (!ctx) return;

        if (balanceRangeChart) {
            balanceRangeChart.destroy();
        }

        balanceRangeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '회원 수',
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
                                return '회원 수: ' + formatNumber(context.parsed.x || 0) + '명';
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

    // 회원별 포인트 보유량 순위 차트 (가로 막대 차트)
    function renderMemberBalanceRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(rankData.length, 50); i++) {
            var memberName = rankData[i].customer_name || rankData[i].user_id || '미지정';
            if (memberName.length > 15) {
                memberName = memberName.substring(0, 15) + '...';
            }
            labels.push(memberName);
            data.push(rankData[i].balance || 0);
        }

        var ctx = document.getElementById('member_balance_rank_chart');
        if (!ctx) return;

        if (memberBalanceRankChart) {
            memberBalanceRankChart.destroy();
        }

        memberBalanceRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '보유량',
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
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
                                return '보유량: ' + formatPoint(context.parsed.x || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatPoint(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // 포인트 거래 내역 테이블 렌더링
    function renderTransactionTable(transactionList) {
        if (!transactionList || !Array.isArray(transactionList)) transactionList = [];

        var $tbody = $('#point_transaction_table tbody');
        $tbody.empty();

        if (transactionList.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < transactionList.length; i++) {
            var item = transactionList[i];
            var transactionDate = item.transaction_date || '';
            if (transactionDate) {
                transactionDate = transactionDate.substring(0, 19).replace('T', ' ');
            }
            
            var amountDisplay = item.amount || 0;
            var amountSign = '';
            if (item.transaction_type === '적립' || item.transaction_type === '사용취소') {
                amountSign = '+';
            } else if (item.transaction_type === '적립취소' || item.transaction_type === '사용') {
                amountSign = '-';
                amountDisplay = Math.abs(amountDisplay);
            }
            
            var row = '<tr>';
            row += '<td>' + (transactionDate || '-') + '</td>';
            row += '<td>' + (item.user_id || '-') + '</td>';
            row += '<td>' + (item.customer_name || '-') + '</td>';
            row += '<td>' + (item.transaction_type_kr || '-') + '</td>';
            row += '<td class="text-right">' + amountSign + formatPoint(amountDisplay) + '</td>';
            row += '<td class="text-right">' + formatPoint(item.balance_after || 0) + '</td>';
            row += '<td>' + (item.appointment_id ? item.appointment_id : '-') + '</td>';
            row += '<td>' + (item.memo || '-') + '</td>';
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
        loadPointStatistics();
    
    // 조회 버튼 클릭 이벤트
    $('#search_btn').on('click', function() {
            loadPointStatistics();
    });
});

})(jQuery);
</script>

