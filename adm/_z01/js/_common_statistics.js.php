<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
/**
 * 통계 페이지 공통 유틸리티 함수들
 */
var StatisticsCommon = (function() {
    'use strict';

    /**
     * 숫자 포맷팅 (천 단위 콤마)
     */
    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    /**
     * 통화 포맷팅 (원화)
     */
    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '0원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    /**
     * 날짜 라벨 포맷팅 (차트용)
     * @param {string} dateStr - YYYY-MM-DD 형식 날짜 문자열
     * @param {string} periodType - 'daily', 'weekly', 'monthly'
     */
    function formatDateLabel(dateStr, periodType) {
        if (!dateStr) return '';
        var date = new Date(dateStr + 'T00:00:00');
        if (isNaN(date.getTime())) return dateStr;

        switch(periodType) {
            case 'weekly':
                var weekStart = new Date(date);
                weekStart.setDate(date.getDate() - date.getDay());
                var weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                return (weekStart.getMonth() + 1) + '/' + weekStart.getDate() + '~' + (weekEnd.getMonth() + 1) + '/' + weekEnd.getDate();
            case 'monthly':
                return (date.getMonth() + 1) + '월';
            case 'daily':
            default:
                return (date.getMonth() + 1) + '/' + date.getDate();
        }
    }

    /**
     * 날짜 유효성 검증
     * @param {string} dateStr - YYYY-MM-DD 형식 날짜 문자열
     * @returns {boolean} 유효 여부
     */
    function validateDate(dateStr) {
        // 날짜 형식 검증 (YYYY-MM-DD)
        var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(dateStr)) {
            return false;
        }

        // 날짜 유효성 검증
        var date = new Date(dateStr + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return false;
        }

        // 날짜 범위 검증 (2년 전 연도의 1월 1일부터 오늘까지)
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var currentYear = today.getFullYear();
        var minYear = currentYear - 2;
        var minDate = new Date(minYear + '-01-01T00:00:00');

        if (date < minDate || date > today) {
            return false;
        }

        return true;
    }

    /**
     * 통계 조회 파라미터 검증
     * @param {object} params - {periodType, startDate, endDate}
     * @returns {object} {valid: boolean, message: string}
     */
    function validateStatisticsParams(params) {
        var periodType = params.periodType;
        var startDate = params.startDate;
        var endDate = params.endDate;

        // 기본 입력값 검증
        if (!startDate || !endDate) {
            return {valid: false, message: '조회 기간을 선택해 주세요.'};
        }

        // period_type 검증 (화이트리스트)
        var allowedPeriodTypes = ['daily', 'weekly', 'monthly'];
        if (allowedPeriodTypes.indexOf(periodType) === -1) {
            return {valid: false, message: '올바르지 않은 기간 타입입니다.'};
        }

        // 날짜 형식 및 유효성 검증
        if (!validateDate(startDate)) {
            return {valid: false, message: '시작일이 올바르지 않습니다.'};
        }

        if (!validateDate(endDate)) {
            return {valid: false, message: '종료일이 올바르지 않습니다.'};
        }

        // 날짜 순서 검증
        if (new Date(startDate) > new Date(endDate)) {
            return {valid: false, message: '시작일은 종료일보다 이전이어야 합니다.'};
        }

        // 조회 기간 제한 (최대 3년, 윤년 자동 고려)
        var start = new Date(startDate + 'T00:00:00');
        var end = new Date(endDate + 'T00:00:00');
        var maxEndDate = new Date(start);
        maxEndDate.setFullYear(start.getFullYear() + 3);

        if (end > maxEndDate) {
            return {valid: false, message: '조회 기간은 최대 3년까지 가능합니다.'};
        }

        return {valid: true, message: ''};
    }

    /**
     * AJAX 에러 메시지 파싱
     * @param {object} xhr - jQuery XMLHttpRequest 객체
     * @param {string} status - 에러 상태
     * @param {string} error - 에러 메시지
     * @returns {string} 파싱된 에러 메시지
     */
    function parseAjaxError(xhr, status, error) {
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
        return errorMsg + '\n상태: ' + status + '\n오류: ' + error;
    }

    /**
     * AJAX 에러 핸들러
     * @param {object} xhr - jQuery XMLHttpRequest 객체
     * @param {string} status - 에러 상태
     * @param {string} error - 에러 메시지
     */
    function handleAjaxError(xhr, status, error) {
        var errorMsg = parseAjaxError(xhr, status, error);
        alert(errorMsg);
        console.error('AJAX Error:', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
    }

    // Public API
    return {
        formatNumber: formatNumber,
        formatCurrency: formatCurrency,
        formatDateLabel: formatDateLabel,
        validateDate: validateDate,
        validateStatisticsParams: validateStatisticsParams,
        parseAjaxError: parseAjaxError,
        handleAjaxError: handleAjaxError
    };
})();
</script>
