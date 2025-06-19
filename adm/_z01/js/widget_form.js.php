<script>
const w = '<?=$w?>';
const bwgs_idx = '<?=$wgt_idx?>';
let skin = '<?=$wgt_skin?>';

timePicker($('.wgt_start_time'),12);
timePicker($('.wgt_end_time'),12);

$(function(){
	$('.dt_cancel').on('click',function(){
		$(this).siblings('input').val('0000-00-00');
		$(this).siblings('select').val('00:00');
	});
	
	// eventOn();
	$(document).on('change','.wgt_start_date, .wgt_start_time, .wgt_end_date, .wgt_end_time',function(e){
		e.stopPropagation();
		e.preventDefault();
		var inputdate = null;
		var inputtime = null;
		var inputdatetime = $(this).parent().siblings('.datetime');
		if($(this).hasClass('bwg_dt')){//현재 날짜입력일때
			inputdate = $(this);
			inputtime = $(this).siblings('.bwg_time');
			if(inputdate.val()){
				if(tms_dt_valid(inputdate.val())){//날짜 입력값이 올바르면
					inputdatetime_val = inputdate.val()+' '+inputtime.val()+':00';
				}else{ //날짜입력이잘못되었으면
					alert('올바른 날짜입력이 아닙니다.');
					inputdatetime_val = '';
					inputtime.find('option').attr('selected',false);
					inputtime.find('option[value="00:00"]').attr('selected',true);
					inputdate.val('').focus();
				}
			}else{
				inputdatetime_val = '';
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
			}
			inputdatetime.val(inputdatetime_val);
		}else if($(this).hasClass('bwg_time')){ //현재 시간입력일때
			inputdate = $(this).siblings('.bwg_dt');
			inputtime = $(this);
			//만약 날짜에 입력이 없으면 날짜부터 입력하라는 경고창을 표시한다.
			if(!inputdate.val()){//시간 앞 날짜입력에 값이 없으면
				alert('날짜부터 입력하세요');
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
				inputdate.focus();
				return false;
			}else{ //시간 앞 날짜입력에 값이 있으면
				inputdatetime.val(inputdate.val()+' '+inputtime.val()+':00');
			}
		}
	});
	if(w == 'u'){
		widget_skin_select(device,skin,bwgs_idx);
	}else{
		widget_skin_select(device);
	}
});

//위젯 위치코드 입력시 중복여부를 체크하는 함수
function widget_code_repetition_check(wgt_code){
	if(wgt_code == ''){
		$('#wgt_code_chk').text('');
		return false;
	}
	$.ajax({
		type:"POST",
		url:"<?=G5_Z_URL?>/ajax/widget_code_repetition_check.php",
		dataType:"text",
		data:{'wgt_code':wgt_code},
		success:function(response){
			if(response){
				response = Number(response);
				if(response != 0){
					$('#wgt_code_chk').text('입력불가').attr('state',0).css('color','red');
				}
				else
					$('#wgt_code_chk').text('입력가능').attr('state',1).css('color','blue');
			}
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}


//위젯을 표시하는 스킨 선택박스를 호출하는 함수
function widget_skin_select(checkskin,wgt_idx){
	var category = $('#wgt_category').val();
	
	$.ajax({
		type:"POST",
		url:"<?=G5_Z_URL?>/ajax/widget_skin_select.php",
		dataType:"html",
		data:{'w':w, 'category':category, 'skin':checkskin, 'wgt_idx':wgt_idx},
		success:function(response){
			$('#td_widget_skin').html(response);
			//console.log($('select[name=bwgs_skin]').val() != null);
			if(w != '') call_skin_config(devc,checkskin,w,bwgs_idx);

			//안에 스킨 선택 요소가 존재하면 스킨선택 모달창에 목록을 셋팅한다.
			if($('#td_widget_skin').find('select').find('option').length > 0){
				skin_select_modal_setting($('#td_widget_skin').find('select'));
			}
			// eventOn();
			//alert($('select[name=bwgs_skin]').length);
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}

//모달창에 스킨선탠 목록을 셋팅하는 함수 
function skin_select_modal_setting(selectObj){
	$('#skin_select_title').text('');
	$('#skin_select_con').empty();
	$('#skin_select_title').text($('#wgt_skin').attr('device').toUpperCase()+'위젯 스킨선택');
	selectObj.find('option').each(function(){
		var selected = ($(this).is(':selected')) ? ' selected' : '';
		$('<div class="skin_lst'+selected+'" value="'+($(this).text() != '사용안함' ? $(this).text() : '')+'"><img src="'+$(this).attr('thumb')+'"><div class="skin_name">'+$(this).text()+'</div></div>').appendTo('#skin_select_con');
	});
}
</script>