<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// echo $name_exists;exit;
?>
<ul class="color_ul">
	<?php if($alpha_flag) {?>
	<li class="color_li color_li1">
		<input type="color" id="<?=$aid?>" readonly value="<?=$bg16?>" style="width:50px;">
	</li>
	<li class="color_li color_li2">
		<span class="color_alpha_ttl">투명도</span>
		<?php //echo bpwg_input_range('',$bga,$w,0,1,0.05,100); ?>
		<span class="range_span bp_wdx100" style="padding-right:29px;">
			<input type="range" value="<?=$bga?>" id="<?=$bid?>" min="0" max="1" step="0.05" size="30">
			<span class="output_span">
				<output id="<?=$did?>" style="padding-right:5px;"><?=$bga?></output>
			</span>
		</span>
	</li>
	<li class="color_li color_li3">
		<input type="hidden" name="<?=$name?>" id="<?=$cid?>" value="<?=$input_color?>">
		<div class="color_result_bg"><div class="color_result"></div></div>
	</li>
	<?php }else{ ?>
	<li class="color_li color_li1"><input type="color" name="<?=$name?>" id="<?=$aid?>" readonly value="<?=$input_color?>" style="width:50px;"></li>
	<?php } ?>
</ul>
<script>
$(function(){
	//여기서 부터는 ie버전에서 input 박스의 색상을 표시해 준다.
	<?php if($g5['is_explorer']){?>
	var <?=$aid?>_val = $('#<?=$aid?>').val();
	$('#<?=$aid?>').css({'border':'1px solid #ddd','font-size':0,'background':<?=$aid?>_val});
	<?php } ?>

	<?php if($alpha_flag) {?>
		//색상과 투명도 설정
		var <?=$eid?>_rgbacolor = tms_hex2rgba($('#<?=$aid?>').val(), <?=$bga?>);
		//console.log(<?=$eid?>_rgbacolor);
		//console.log('<?=$bga?>');
		//console.log('<?=$input_color?>');
		//console.log($('#<?=$bid?>').val());
		//console.log($('#<?=$bid?>').length);
		$('#<?=$cid?>').val(<?=$eid?>_rgbacolor);
		$('#<?=$cid?>').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);

		$('#<?=$aid?>').colpick({
			layout:'hex',
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				<?=$eid?>_rgbacolor = tms_hex2rgba($(el).val(), $('#<?=$bid?>').val());
				$(el).parent().siblings('.color_li3').find('input').val(<?=$eid?>_rgbacolor);
				$(el).parent().siblings('.color_li3').find('input').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
				
				<?php if($g5['is_explorer']){?>
				$(el).css({'border':'1px solid #ddd','font-size':0,'background':$(el).val()});
				<?php } ?>
			}
		});
	<?php }else{ // 여기까지는 $alpha_flag == true ?>
		//색상만 설정
		$('#<?=$aid?>').colpick({
			layout:'hex',
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				$(el).parent().find('input').val('#'+hex);
				
				<?php if($name_exists == 'N') { ?>
				let input_colors = $(el).parent().parent().parent().parent().siblings('input');
				if(input_colors.length == 1){
					input_colors.val('');
					let lis_color_str = '';
					let lis = $('.'+$(el).parent().parent().parent().attr('class'));
					lis.each(function(index){
						lis_color_str += $(this).find('.color_ul').find('.color_li').find('input').val();
						if(index < lis.length-1) lis_color_str += ',';
					});
					// alert(lis_color_str);
					input_colors.val(lis_color_str);
				}
				<?php } ?>
			}
		});
	<?php } // 여기까지는 $alpha_flag == false ?>
});
</script>