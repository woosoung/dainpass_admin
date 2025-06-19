<?php
require_once './_common.php';

if (!$is_manager)
    alert('접근권한이 없습니다.');

$type = isset($_REQUEST['type']) ? preg_replace('/[^0-9a-z_]/i', '', $_REQUEST['type']) : '';

switch ($type) {
    case 'group':
        $sql = " select gr_id as id, gr_subject as subject
                    from {$g5['group_table']}
                    order by gr_order, gr_id ";
        break;
    case 'board':
        $sql = " select bo_table as id, bo_subject as subject, gr_id
                    from {$g5['board_table']}
                    order by bo_order, bo_table ";
        break;
    case 'content':
        $sql = " select co_id as id, co_subject as subject
                    from {$g5['content_table']}
                    order by co_id ";
        break;
    case 'shop':
        $sql = " select ca_id as id, ca_name as subject
                    from {$g5['g5_shop_category_table']}
                    order by ca_order, ca_id ";
        break;
    default:
        $sql = '';
        break;
}

$msql = " SELECT me_link FROM {$g5['menu_table']} ";
$mresult = sql_query($msql);
$sch_me_link = array();
for($i=0;$row=sql_fetch_array($mresult);$i++){
	array_push($sch_me_link,$row['me_link']);
}
//print_r2($sch_me_link);
?>
<?php
if($sql) {
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) {

    $bbs_subject_title = ($type == 'board') ? '게시판제목' : '제목';
?>
<div class="tbl_head01 tbl_wrap">
	<div style="padding:10px;">
		<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
	</div>
    <table>
    <thead>
    <tr>
        <th scope="col"><?php echo $bbs_subject_title; ?></th>
        <?php if($type == 'board'){ ?>
            <th scope="col">게시판 그룹</th>
        <?php } ?>
        <th scope="col">선택</th>
    </tr>
    </thead>
    <tbody>

<?php }
        switch($type) {
            case 'group':
                $link = G5_BBS_URL.'/group.php?gr_id='.$row['id'];
                break;
            case 'board':
                $link = G5_BBS_URL.'/board.php?bo_table='.$row['id'];
                break;
            case 'content':
                $link = G5_BBS_URL.'/content.php?co_id='.$row['id'];
                break;
            case 'shop':
                $link = G5_SHOP_URL.'/list.php?ca_id='.$row['id'];
                break;
            default:
                $link = '';
                break;
        }
		
		$chk_link = str_replace(G5_URL,"",$link);
		$has_link = (in_array($chk_link,$sch_me_link)) ? 'style="color:red;"' : '';
?>

    <tr>
        <td <?=$has_link?>><?php echo $row['subject']; ?></td>
        <?php
        if($type == 'board'){
        $group = get_call_func_cache('get_group', array($row['gr_id']));
        ?>
        <td><?php echo $group['gr_subject']; ?></td>
        <?php } ?>
        <td class="td_mngsmall">
            <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
            <input type="hidden" name="link[]" value="<?php echo $link; ?>">
            <button type="button" class="add_select btn btn_03"><span class="sound_only"><?php echo $row['subject']; ?> </span>선택</button>
        </td>
    </tr>

<?php }//for($i=0; $row=sql_fetch_array($result); $i++) ?>

    </tbody>
    </table>
	<div style="padding:10px;">
		<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
	</div>
</div>

<div class="btn_win02 btn_win">
    <button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
</div>

<?php } else { ?>
	<?php
	if($type == 'it_type'){
	$it_type_arr = array('히트상품','추천상품','신상품','인기상품','할인상품');	
	?>
	
	
	<div class="tbl_head01 tbl_wrap">
		<table>
		<thead>
		<tr>
			<th scope="col">상품유형</th>
			<th scope="col">선택</th>
		</tr>
		</thead>
		<tbody>
	<?php 
		for($i=1;$i<=5;$i++){
			$type_subj = $it_type_arr[$i-1];
			$type_link = G5_SHOP_URL.'/listtype.php?type='.$i;
			
			$chk_link = str_replace(G5_URL,"",$type_link);
			$has_link = (in_array($chk_link,$sch_me_link)) ? 'style="color:red;"' : '';
	?>
		<tr>
			<td <?=$has_link?>><?php echo $type_subj; ?></td>
			<td class="td_mngsmall">
				<input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $type_subj); ?>">
				<input type="hidden" name="link[]" value="<?php echo $type_link; ?>">
				<button type="button" class="add_select btn btn_03"><span class="sound_only"><?php echo $type_subj; ?> </span>선택</button>
			</td>
		</tr>

	<?php }//for($i=1;$i<=5;$i++) ?>

		</tbody>
		</table>
		<div style="padding:10px;">
			<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
		</div>
	</div>

	<div class="btn_win02 btn_win">
		<button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
	</div>
	
	<?php }else{ ?>
		<div class="tbl_frm01 tbl_wrap">
			<table>
			<colgroup>
				<col class="grid_2">
				<col>
			</colgroup>
			<tbody>
			<tr>
				<th scope="row" style="width:50px;text-align:center;"><label for="me_name">메뉴<strong class="sound_only"> 필수</strong></label></th>
				<td><input type="text" name="me_name" id="me_name" required class="frm_input required"></td>
			</tr>
			<tr>
				<th scope="row" style="width:50px;text-align:center;"><label for="me_link">링크<strong class="sound_only"> 필수</strong></label></th>
				<td>
					<?php echo help('링크는 http(s)://를 포함해서 입력해 주세요.'); ?>
					<input type="text" name="me_link" id="me_link" size="50" required class="frm_input full_input required">
				</td>
			</tr>
			</tbody>
			</table>
		</div>

		<div class="btn_win02 btn_win">
			<button type="button" id="add_manual" class="btn_submit btn">추가</button>
			<button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
		</div>
	<?php } ?>
<?php } ?>