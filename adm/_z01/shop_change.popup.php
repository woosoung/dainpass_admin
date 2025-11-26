<?php
include_once('./_common.php');

if(!$is_manager) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.close();</script>";
}

// 변수 초기화
$stx = isset($stx) ? trim($stx) : '';
$sfl = isset($sfl) ? trim($sfl) : 'shop_id';
$page = isset($page) ? (int)$page : 1;
$sst = isset($sst) ? trim($sst) : '';
$sod = isset($sod) ? trim($sod) : '';

// 페이징을 위한 쿼리스트링 구성
$qstr = '';
if($stx) {
    $qstr .= '&stx='.urlencode($stx);
}
if($sfl) {
    $qstr .= '&sfl='.urlencode($sfl);
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
		case 'owner_name' :
            $where[] = " owner_name LIKE '%{$stx}%' ";
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
$row = sql_fetch_pg($sql);
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
		LIMIT {$rows} OFFSET {$from_record} 
";
$result = sql_query_pg($sql);
$rcnt = ($result && is_object($result) && isset($result->num_rows)) ? $result->num_rows : 0;

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
			<?php if($is_manager){ ?>
			<button type="button" id="btn_reset_mb1" class="btn btn_02" style="margin-left:10px;">플랫폼 모드로 되돌리기</button>
			<?php } ?>
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch py-2" method="get">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl" style="">
			<option value="shop_id"<?php echo get_selected(isset($_GET['sfl']) ? $_GET['sfl'] : '', "shop_id"); ?>>업체번호</option>
			<option value="shop_name"<?php echo get_selected(isset($_GET['sfl']) ? $_GET['sfl'] : '', "shop_name"); ?>>가맹점명</option>
			<option value="name"<?php echo get_selected(isset($_GET['sfl']) ? $_GET['sfl'] : '', "name"); ?>>업체명</option>
			<option value="name"<?php echo get_selected(isset($_GET['sfl']) ? $_GET['sfl'] : '', "name"); ?>>대표명</option>
			<option value="owner_name"<?php echo get_selected(isset($_GET['sfl']) ? $_GET['sfl'] : '', "owner_name"); ?>>대표자명</option>
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
			if ($result && is_pg_wrapper($result)) {
				for ($i=0; $row=sql_fetch_array_pg($result->result); $i++){
					//print_r2($row);
					$choice = '<a href="javascript:" class="a_mag btn btn_02" shop_id="'.$row['shop_id'].'" name="'.$row['name'].'">선택</a>';
				?>
					<tr>
					<td class="td_shop_id"><?=$row['shop_id']?></td>
					<td class="td_shop_name"><!-- 업체명 -->
						<b><?php echo get_text($row['shop_name']); ?></b>
	                    <?php if($row['branch']){ ?>
	                    (<?=$row['branch']?>)
	                    <?php } ?>
						<br><small class="text-gray-500">업체명: <?=$row['name']?></small>
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
			}
			if ($rcnt == 0){
				echo "<tr><td class='td_empty' colspan='4'>".PHP_EOL;
				echo "자료가 없습니다.".PHP_EOL;
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

// 가맹점 선택 버튼 클릭 이벤트
$('.a_mag').on('click',function(){
    var shop_id = $(this).attr('shop_id');
    var shop_name = $(this).closest('tr').find('.td_shop_name b').text();
    
    if(!shop_id) {
        alert('가맹점 정보가 올바르지 않습니다.');
        return false;
    }
    
    // AJAX로 mb_1 업데이트
    $.ajax({
        url: g5_z_url + '/ajax/mb1_update.php',
        type: 'POST',
        data: {
            shop_id: shop_id
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                opener.location.reload(); // 부모창 새로고침
                window.close(); // 팝업창 닫기
            } else {
                alert(response.message || '가맹점 변경에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            alert('서버 오류가 발생했습니다. 다시 시도해주세요.');
            console.error(error);
        }
    });
    
    return false;
});

// 플랫폼 모드로 되돌리기 버튼 클릭 이벤트
$('#btn_reset_mb1').on('click', function(){
    if(!confirm('플랫폼 모드로 되돌리시겠습니까?')) {
        return false;
    }
    
    // AJAX로 mb_1을 0으로 업데이트
    $.ajax({
        url: g5_z_url + '/ajax/mb1_update.php',
        type: 'POST',
        data: {
            shop_id: 0
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                opener.location.reload(); // 부모창 새로고침
                window.close(); // 팝업창 닫기
            } else {
                alert(response.message || '변경에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            alert('서버 오류가 발생했습니다. 다시 시도해주세요.');
            console.error(error);
        }
    });
    
    return false;
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');