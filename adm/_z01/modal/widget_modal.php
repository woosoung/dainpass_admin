<!--스킨 선택 모달창-->
<div id="skin_select_modal">
	<div id="skin_select_tbl">
		<div id="skin_select_td">
			<div id="skin_select_bg"></div>
			<div id="skin_select_box">
				<h3 id="skin_select_title"></h3>
				<img id="skin_select_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="skin_select_con"></div>
			</div>
		</div>
	</div>
</div>
<!-- 콘텐츠 개별 이미지 등록 모달창-->
<div id="confile_reg_modal">
	<div id="confile_reg_tbl">
		<div id="confile_reg_td">
			<div id="confile_reg_bg"></div>
			<div id="confile_reg_box">
				<h3 id="confile_reg_title">콘텐츠 개별 이미지 등록</h3>
				<img id="confile_reg_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="confile_reg_con">
					<form name="confileregform" id="confileregform" action="<?=G5_Z_URL?>/widget_content_file_register_update.php" onsubmit="return confilereg_check(this);" method="post" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="bwgs_idx" value="">
						<input type="hidden" name="bwgc_idx" value="">
						<input type="hidden" name="bwga_type" value="content">
						<div id="cur_img">
							<div id="file_box">
								<input type="file" name="bwcfile" id="bwcfile" multiple class="with-preview" maxlength="1" accept="png|jpg|gif|svg" data-maxfile="<?=$g5['bpwidget']['bwgf_filesize']?>">
							</div>
							<table class="ftbl">
								<tbody>
									<tr>
										<th>제목</th>
										<td colspan="5"><input type="text" name="bwga_title" class="ftxt" value=""></td>
									</tr>
									<tr>
										<th>랭크</th>
										<td><input type="text" name="bwga_rank" class="ftxt" value=""></td>
										<th>순서</th>
										<td><input type="text" name="bwga_sort" class="ftxt" value=""></td>
										<th>상태</th>
										<td>
											<select name="bwga_status" class="fselect">
												<option value="ok">사용</option>
												<option value="pending">대기</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>내용</th>
										<td colspan="5">
											<textarea name="bwga_content" class="ftxtarea"></textarea>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="modal_btn">
							<input type="submit" class="btn_submit btn" value="확인">
							<input type="button" class="confile_reg_close btn_close btn" value="창닫기">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!--개별 이미지 변경 모달창-->
<div id="img_change_modal">
	<div id="img_change_tbl">
		<div id="img_change_td">
			<div id="img_change_bg"></div>
			<div id="img_change_box">
				<h3 id="img_change_title">개별 이미지 변경</h3>
				<img id="img_change_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="img_change_con">
					<form name="filechangeform" id="filechangeform" action="<?=G5_Z_URL?>/widget_file_change_update.php" onsubmit="return filechange_check(this);" method="post" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="bwgs_idx" value="">
						<input type="hidden" name="bwgc_idx" value="">
						<input type="hidden" name="bwga_idx" value="">
						<div id="cur_img">
							<div class="cur_img_box"></div>
							<table class="ftbl">
								<tbody>
									<tr>
										<th>제목</th>
										<td colspan="5"><input type="text" name="bwga_title" class="ftxt" value=""></td>
									</tr>
									<tr>
										<th>랭크</th>
										<td><input type="text" name="bwga_rank" class="ftxt" value=""></td>
										<th>순서</th>
										<td><input type="text" name="bwga_sort" class="ftxt" value=""></td>
										<th>상태</th>
										<td>
											<select name="bwga_status" class="fselect">
												<option value="ok">사용</option>
												<option value="pending">대기</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>내용</th>
										<td colspan="5">
											<textarea name="bwga_content" class="ftxtarea"></textarea>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="file_box">
							<input type="file" name="filechange" id="filechange" multiple class="with-preview" maxlength="1" accept="png|jpg|gif|svg" data-maxfile="<?=$g5['bpwidget']['bwgf_filesize']?>">
						</div>
						<div class="modal_btn">
							<input type="submit" class="btn_submit btn" value="확인">
							<input type="button" class="img_change_close btn_close btn" value="창닫기">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>