<?php
include_once('./_common.php');

if(!$is_manager) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.close();</script>";
}


$sql_common = " FROM {$g5['shop_table']} AS com";

$where = array();
// $where[] = " com_type = '1' ";
$where[] = " status NOT IN ('trash','closed','shutdown') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'shop_id' : 
			$where[] = " shop_id = '{$stx}' ";
			break;
		case 'shop_name' :
            $where[] = " ( shop_name LIKE '%{$stx}%' OR shop_names LIKE '%{$stx}%' ) ";
            break;
		case 'name' :
            $where[] = " ( name LIKE '%{$stx}%' OR names LIKE '%{$stx}%' ) ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "created_at";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

// 테이블의 전체 레코드수만 얻음
$sql = " SELECT COUNT(*) as cnt " . $sql_common.$sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 6;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT 
			shop_id,
			shop_name,
			name,
            branch,
			owner_name
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
$result = sql_query($sql,1);
$rcnt = $result->num_rows;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '업체목록';

include_once(G5_PATH.'/head.sub.php');
?>
<style>
html,body{overflow:hidden;}
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_02 btn_close" onclick="window.close()">닫기</a>
	<?php } ?>
	<h1><?php echo $g5['title']; ?></h1>
	<div id="com_sch_list" class="new_win">
		<div class="local_ov01 local_ov">
			<?php echo $listall ?>
			<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch py-2" method="get">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl" style="">
			<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
			<option value="com_idx"<?php echo get_selected($_GET['sfl'], "com_idx"); ?>>업체번호</option>
		</select>
		<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:130px;">
		<input type="submit" class="btn_submit" value="검색">
		
		</form>
		<div class="tbl_head01 tbl_wrap">
			<table class="table table-bordered table-condensed">
			<caption><?php echo $g5['title']; ?> 목록</caption>
			<thead>
				<th scope="col">업체번호</th>
				<th scope="col">업체명</th>
				<th scope="col">대표</th>
				<th scope="col">관리</th>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++){
				//print_r2($row);
				$choice = '<a href="javascript:" class="a_mag btn btn_02" shop_id="'.$row['shop_id'].'" name="'.$row['name'].'">선택</a>';
			?>
				<tr>
				<td class="td_shop_id"><?=$row['shop_id']?></td>
				<td class="td_shop_name"><!-- 업체명 -->
					<b><?php echo get_text($row['shop_name']); ?></b>
                    <?php if($row['branch']){ ?>
                    <br>(<?=$row['branch']?>)
                    <?php } ?>
				</td>
				<td class="td_com_mgn"><!-- 마진 -->
					<b><?php echo get_text($row['owner_name']); ?></b>
				</td>
				<td class="td_mng" style="text-align:center;"><!-- 관리 -->
					<?=$choice?>
				</td>
				</tr>
			<?php
			}
			if ($rcnt == 0){
				echo "<tr><td class='td_empty' colspan='4'>".PHP_EOL;
				echo "자료가 없습니다.<br>".PHP_EOL;
				echo '<a href="'.G5_Z_URL.'/company_form.php" target="_blank" class="ov_listall" style="margin-top:5px;">업체등록</a>'.PHP_EOL;
				echo "</td></tr>".PHP_EOL;
			}
			?>
			</tbody>
			</table>
		</div>
		<?php
		echo get_paging($config['cf_mobile_pages'], $page, $total_page, '?'.$qstr);
		?>
	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(500,640)','onload':'parent.resizeTo(500,640)'});
$('.a_mag').on('click',function(){
    <?php if($file_name == 'company_form'){ ?>
    opener.document.getElementById('com_idx_parent').value = $(this).attr('com_idx');
    opener.document.getElementById('com_name_parent').value = $(this).attr('com_name');
    <?php }else{ ?>
	opener.document.getElementById('com_idx').value = $(this).attr('com_idx');
	opener.document.getElementById('com_name').value = $(this).attr('com_name');
    <?php } ?>
	window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');