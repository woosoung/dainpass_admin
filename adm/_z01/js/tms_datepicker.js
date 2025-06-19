$(function(){
    $('.from_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
        onClose:function(dateText, inst){
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
                $('.to_date').val('');
            }
        },
		onSelect:function(selectedDate){
			$('.to_date').datepicker('option','minDate',selectedDate);
			$('.to_date').val('');
		}
	});
	
    $('.to_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		},
		onSelect:function(selectedDate){
			if($('.from_date').val() == ''){
				$(this).val('');
			}
		}
	});
	
    $('.tms_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		}
	});
	
    $('.required_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'닫기',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
	});
});

// 시작날짜/종료날짜함수 인수로 ID값으로 넘기자 '#from_date', '#to_date'
if (typeof (range_date) != 'function') { 
function range_date(f_id, t_id) {
    $(f_id).datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
        yearRange: '1910:2100',
        onClose:function(dateText, inst){
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
                $(t_id).val('');
            }
		},
		onSelect:function(selectedDate){
			$(t_id).datepicker('option','minDate',selectedDate);
			$(t_id).val('');
		}
    });

    $(t_id).datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		},
		onSelect:function(selectedDate){
			if($(f_id).val() == ''){
				$(this).val('');
			}
		}
	});
}
}

// 날짜선택함수 인수로 ID값으로 넘기자 '#tms_date'
if (typeof (single_date) != 'function') {
function single_date(tms_id) {
    $(tms_id).datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        // 아래 함수의 기능은 
        onClose: function (dateText, inst) {
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
        }
	});
}
}
//날짜형식에 맞는지 날짜유효성 검사함수
if(typeof(tms_dt_valid) != 'function'){
function tms_dt_valid(dt){
	var dt_ptrn = /[0-9]{4}-[0-9]{2}-[0-9]{2}/;
	var y = 0;
	var m = 0;
	var d = 0;
	var m_day = [31,28,31,30,31,30,31,31,30,31,30,31];
	//일자척으로 10자리가 아니면 날짜 형식 아니므로 실패
	if(dt.length != 10)
		return false;
	//일차적으로 10자리의 날짜 형식이 아니면 실패
	if(!dt_ptrn.test(dt))
		return false;
	
	var dt_arr = dt.split("-");
	y = parseInt(dt_arr[0],10);
	m = parseInt(dt_arr[1],10);
	d = parseInt(dt_arr[2],10);
	
	//1910년도 보다 작으면 실패
	if(y < 1910)
		return false;
	
	//월이 0이하 이거나 12보다 크면 실패
	if(m <= 0 || m > 12)
		return false;
	
	//일이 0이하 이거나 31보다 크면 실패
	if(d <= 0 || d > 31)
		return false;
	
	//윤년일때
	if(tms_is_leaf(y)){
		//윤달일때
		if(m == 2){
			if(d > m_day[m - 1] + 1)//29일보다 크면 실패
				return false;
		}else{
			if(d > m_day[m - 1]){
				return false;
			}
		}
	}else{
		if(d > m_day[m - 1]){
			return false;
		}
	}
	
	return true;
}
}
//윤년 여부 검사함수
if(typeof(tms_is_leaf) != 'function'){
function tms_is_leaf(year){
	var leaf = false;
	if(year % 4 == 0){
		leaf = true;
		
		if(year % 100 == 0){
			leaf = false;
		}
		
		if(year % 400 == 0){
			leaf = false;
		}
	}
	
	return leaf;
}
}