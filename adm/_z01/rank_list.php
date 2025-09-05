<?php
$sub_menu = "910500";
include_once('./_common.php');

@auth_check($auth[$sub_menu],"r");

// 용어 설정
$category = !empty($category) ? $category : 'rank';


// include_once(G5_ZSQL_PATH.'/term_rank.php');

//아래 AND 조건절에 비어있는 줄에는 원래 아래와 같은 내용은 있었다 (2군데)
//AND term.trm_status = 'ok' AND parent.trm_status = 'ok'
//-- 카테고리 구조 추출 --//
$sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor member: 루트 노드를 가져옴
    SELECT
        trm_idx,
        trm_name,
    	trm_name2,
        CAST(trm_name AS CHAR(255)) AS path,
        trm_desc,
        trm_left,
        trm_right,
		trm_status,
        0 AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = {$g5['term_table']}.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0  -- 루트 노드
        AND trm_category = 'rank'
        AND trm_status != 'trash'

    UNION ALL

    -- Recursive member: 하위 노드들을 경로와 함께 가져옴
    SELECT
        t.trm_idx,
        t.trm_name,
    	t.trm_name2,
        CONCAT(tp.path, ' > ', t.trm_name) AS path,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
		t.trm_status,
        (SELECT COUNT(*)
        FROM {$g5['term_table']} AS parent
        WHERE parent.trm_left < t.trm_left
        AND parent.trm_right > t.trm_right) AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = t.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'rank'
        AND t.trm_status != 'trash'
)
SELECT trm_idx, trm_name, trm_name2, path, trm_desc, trm_left, trm_right, trm_status, depth, has_children FROM TermPaths ORDER BY trm_left;
";

$result = sql_query($sql);
$total_count = sql_num_rows($result);
$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$g5['title'] = '직급관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>


<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총분류수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>개 </span></span>
</div>

<form name="fcarlist" method="post" action="./department_list_update.php" autocomplete="off">
<input type="hidden" name="category" value="<?php echo $category; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 목록</caption>
<thead>
<tr>
    <th scope="col" style="width:6%">트리구조</th>
    <th scope="col" style="width:15%">직급명</th>
    <th scope="col" style="width:10%">직급명2</th>
    <th scope="col" style="width:7%"><a href="javascript:" id="sub_toggle">닫기</a></th>
    <th scope="col" style="width:20%">설명</th>
    <th scope="col" style="width:10%">위치이동</th>
    <th scope="col" style="width:5%;white-space:nowrap;">고유코드</th>
	<th scope="col" style="width:6%">숨김</th>
    <th scope="col" style="width:6%">관리</th>
</tr>
</thead>
<tbody>
	<!-- 항목 추가를 위한 DOM (복제후 제거됨) -->
	<tr class="" style="display:none">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="hidden" name="trm_depth[]" value="0">
			<input type="hidden" name="trm_idx[]" value="">
			<input type="text" name="trm_name[]" value="직급명을 입력하세요" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="text" name="trm_name2[]" value="" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><a href="#">열기</a></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center">
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
		<td class="td_idx" style="text-align:center"></td>
		<td class="td_use" style="text-align:center">
			<input type="hidden" name="trm_status[]" value="ok">
			<input type="checkbox" name="trm_use[]">
	    </td>
	    <td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
	<!-- //항목 추가를 위한 DOM (복제후 제거됨) -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	// print_r2($row);
	//-- 들여쓰기
	$row['indent'] = ($row['depth']) ? $row['depth']*50:10;
	
	//-- 하위 열기 닫기
	//$row['sub_toggle'] = ($row['depth']==0) ? '<a href="#">닫기</a>':'-';
	$row['sub_toggle'] = ($row['has_children']==1) ? '<a href="#">닫기</a>':'-';
	
    // 추가 부분 unserialize
    // $unser = unserialize(stripslashes($row['trm_more']));
    // if( is_array($unser) ) {
    //     foreach ($unser as $key=>$value) {
    //         //print_r3($key.'/'.$value);
    //         $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
    //     }    
    // }
	
	$usechecked = ($row['trm_status'] == 'ok') ? '':'checked';
	$status_txt = ($row['trm_status'] == 'ok') ? 'ok':'hide';
    $bg = 'bg'.($i%2);
?>
	<tr class="<?php echo $bg; ?>">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:<?=$row['indent']?>px;text-align:left;">
			<input type="hidden" name="trm_depth[]" value="<?=$row['depth']?>">
			<input type="hidden" name="trm_idx[]" value="<?=$row['trm_idx']?>">
			<input type="text" name="trm_name[]" value="<?php echo get_text(trim($row['trm_name'])); ?>" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name2">
			<input type="text" name="trm_name2[]" value="<?php echo get_text(trim($row['trm_name2'])); ?>" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><?=$row['sub_toggle']?></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="<?php echo get_text(trim($row['trm_desc'])); ?>" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center"><!-- 위치이동 -->
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
	    <td class="td_idx" style="text-align:center"><!-- 코유코드 -->
			<?=$row['trm_idx']?>
	    </td>
	    <td class="td_use" style="text-align:center"><!-- 숨김 -->
			<input type="hidden" name="trm_status[]" value="<?=$status_txt?>">
	        <input type="checkbox" name="trm_use[]" <?=$usechecked?>>
	    </td>
		<td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
<?php }
if ($i == 0) echo "<tr class=\"no-data\"><td colspan=\"9\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
?>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:insert_item()" id="btn_add_car" class="btn btn_02">항목추가</a>
	<input type="submit" name="act_button" value="확인" class="btn_submit btn">
</div>
</form>

<script>
//----------------------------------------------
$(function() {
    
    // 엑셀업로드 창 열기
    $(document).on('click','#btn_excel_upload',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        win_excel_upload = window.open(href, "win_excel_upload", "left=100,top=100,width=520,height=600,scrollbars=1");
        win_excel_upload.focus();
    });
    
	
	//-- DOM 복제 & 생성 & 초기화 --//
	list_dom01=$("#table01_list tbody");
	orig_dom01=list_dom01.find("tr").eq(0).clone();
	list_dom01.find("tr:eq(0)").remove();	// 복제한 후에 제거
	list01_nothing_display();

	//-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};

	$("#table01_list tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: "#table01_list tr:not(.no-data)"
		, stop: function(event, ui) {
			//alert(ui.item.html());
			//-- 정렬 후 처리 / 맨 처음 항목이면 최상위 레벨이어야 함
			if($(this).find('tr').index(ui.item) == 0 && ui.item.find('input[name^=trm_depth]').val() > 0) {
				ui.item.find('input[name^=trm_depth]').val(0);
				ui.item.find('.td_trm_name').css('padding-left','0px');
			}
			
			setTimeout(function(){ ui.item.removeAttr('style'); }, 10);
		}
	});
	
	//=====================================================카테고리 사용여부=========================== 
	$('input[type="checkbox"]').click(function(){
		if($(this).is(":checked")){
			$(this).siblings('input[type="hidden"]').val('hide');
			//alert($(this).siblings('input[type="hidden"]').val());
		}else{
			$(this).siblings('input[type="hidden"]').val('ok');
			//alert($(this).siblings('input[type="hidden"]').val());
		}
	});

	//-- 차종추가 경고창 초기 설정
	alert_flag = true; 


	//-- 단계이동 버튼 클릭 --//
//	$('.td_depth a').live('click',function(e) {
	$(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
		//-- 맨 처음 항목은 무조건 최상위 단계이어야 함
		if($(this).parents('tbody:first').find('tr').index($(this).parents('tr:first')) == 0 && $(this).parent().find('a').index($(this)) == 1) {
			alert('맨 처음 항목은 최상위 레벨이어야 합니다. \n\n단계 2로 이동할 수 없습니다.');
			return false;
		}
		
		//-- depth 값 업데이트
		var indent_sign_value = ($(this).parent().find('a').index($(this)) == 0)? -1:1;
		var new_depth = parseInt($(this).parents('tr:first').find('input[name^=trm_depth]').val()) + indent_sign_value;
		if(new_depth < 0) new_depth = 0;
		$(this).parents('tr:first').find('input[name^=trm_depth]').val(new_depth);
		
		//-- 들여쓰기 적용
		var indent_value = (new_depth) ? new_depth * 50:10;
		$(this).parents('tr:first').find('.td_trm_name').css('padding-left',indent_value+'px');
		
		//update_notice();	//-- [일괄수정] 버튼 활성화
	});


	//-- 위치이동 버튼 클릭 --//
//	$('.td_sort a').live('click',function(e) {
	$(document).on('click','.td_sort a',function(e) {
		e.preventDefault();

		var target_tr = $(this).parents('tr:first').clone().hide();
		var flag_up_down = ($(this).parent().find('a').index($(this)) == 0)? 'up':'down';
		var tr_loc = $(this).parents('tbody:first').find('tr').index($(this).parents('tr:first'));
		

		if(flag_up_down == "up" && tr_loc == 0) {
			alert('맨 처음 항목입니다. 더 이상 올라갈 때가 없지 않나요?');
			return false;
		}
		else if(flag_up_down == "down" && tr_loc == $(this).parents('tbody:first').find('tr').length - 1) {
			alert('마지막 항목입니다. 보면 알잖아요~');
			return false;
		}

		$(this).parents('tr:first').stop(true,true).fadeOut('fast',function(){
			$(this).remove();

			if(flag_up_down == "up") {
				target_tr.insertBefore($('#table01_list tbody tr').eq(parseInt(tr_loc)-1)).stop(true,true).fadeIn('fast').removeAttr('style');
			}
			else {
				target_tr.insertAfter($('#table01_list tbody tr').eq(tr_loc)).stop(true,true).fadeIn('fast').removeAttr('style');
			}

		});

		//update_notice();	//-- Submit 버튼 활성화
	});


	//-- 삭제 버튼 클릭시 --//
//	$('.td_del a').live('click',function(e) {
	$(document).on('click','.td_del a',function(e) {
		e.preventDefault();
		
		//-- 추가된 항목은 바로 삭제, 기 등록된 조직은 관련 작업 진행
		if(confirm('하위 카테고리 전체 및 소속 항목들이 전부 삭제됩니다. \n\n후회할 수도 있을 텐데~~ 정말 삭제하시겠습니까?')) {
			if($(this).parents('tr:first').find('input[name^=trm_idx]').val()) {

				//-- 삭제 함수 호출(마지막인수는 trash변경할지 아예 삭제할지의 영부인데 0이면 trash, 1이면 완전삭제)
				trm_delete($(this).parents('tr:first').find('input[name^=trm_idx]').val(),1);

			}
			else {
				$(this).parents('tr:first').remove();
			}
			
			//update_notice();	//-- Submit 버튼 활성화
		}
	});


	//-- 닫기 열기
	$('#sub_toggle').click(function() {
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		$('#table01_list tbody tr').find('input[name^=trm_depth]').each(function() {
			if($(this).val() > 0) {
				if(this_text == "닫기")
					$(this).closest('tr').hide();
				else 
					$(this).closest('tr').show();
			}
			else {
				if(this_text == "닫기") {
					$(this).closest('tr').find('.td_sub_category a').text('열기');
				}
				else 
					$(this).closest('tr').find('.td_sub_category a').text('닫기');
			}
		});
	});


	//-- 서브 부분만 열고 닫기
//	$('.td_sub_category a').live('click',function(e) {
	$(document).on('click','.td_sub_category a',function(e) {
		e.preventDefault();
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		
		var this_depth = $(this).closest('tr').find('input[name^=trm_depth]').val();
		var this_sub_flag = false;
		
		$(this).closest('tr').nextAll('tr').each(function() {
			if($(this).find('input[name^=trm_depth]').val() > this_depth && this_sub_flag == false) {

				if(this_text == "닫기")
					$(this).hide();
				else
					$(this).show();
			}
			else 
				this_sub_flag = true;
		});
	});



});
//----------------------------------------------


//-- 01 No data 처리 --//
function list01_nothing_display() {
	if(list_dom01.find("tr:not(.no-data)").length == 0)
		list_dom01.find('.no-data').show();
	else 
		list_dom01.find('.no-data').hide();
}
//-- //01 No data 처리 --//


//-- 테이블 항목 추가
function insert_item() {
	//-- DOM 복제
	sDom = orig_dom01.clone();

	//-- DOM 입력
	//sDom.insertBefore($('#table01_list tbody tr').eq(0)).show();
	//$('#table01_list tbody tr').eq(0).find('input[name^=trm_name]').select().focus();
	$('#table01_list tbody').append(sDom.show());
	$('#table01_list tbody tr:last').find('input[name^=trm_name]').select().focus();

	list01_nothing_display();
	
	if(alert_flag == true) {
		alert('입력항목을 작성한 후 하단의 [일괄수정] 버튼을 클릭하여 적용해 주시면 됩니다.');
		alert_flag = false;
	}
}


//-- 항목 삭제 함수 --//
function trm_delete(this_trm_idx, fn_delte) {
	//-- 디버깅 Ajax --//
	$.ajax({
		url:'./ajax/term_delete.php',
		type:'get',
		data:{"category":"<?=$category?>", "trm_idx":this_trm_idx,"delete":fn_delte},
		dataType:'json',
		timeout:3000, 
		beforeSend:function(){},
		success:function(data){
			self.location.reload();
		},
		error:function(xmlRequest) {
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
		} 
	//-- 디버깅 Ajax --//

	});	
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');