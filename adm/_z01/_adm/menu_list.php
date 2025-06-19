<?php
$sub_menu = "100290";
include_once('./_common.php');

if (!$is_manager)
    alert('접근권한이 없습니다.');

// 메뉴테이블 생성
if( !isset($g5['menu_table']) ){
    die('<meta charset="utf-8">dbconfig.php 파일에 <strong>$g5[\'menu_table\'] = G5_TABLE_PREFIX.\'menu\';</strong> 를 추가해 주세요.');
}

if(!sql_query(" DESCRIBE {$g5['menu_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['menu_table']}` (
                  `me_id` int(11) NOT NULL AUTO_INCREMENT,
                  `me_code` varchar(255) NOT NULL DEFAULT '',
                  `me_name` varchar(255) NOT NULL DEFAULT '',
                  `me_link` varchar(255) NOT NULL DEFAULT '',
                  `me_target` varchar(255) NOT NULL DEFAULT '0',
                  `me_order` int(11) NOT NULL DEFAULT '0',
                  `me_use` tinyint(4) NOT NULL DEFAULT '0',
                  `me_mobile_use` tinyint(4) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`me_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
}
$sql = " select * from {$g5['menu_table']} order by me_code ";
$result = sql_query($sql);

$g5['title'] = "메뉴설정";
include(G5_ADMIN_PATH.'/admin.head.php');

$colspan = 9;
?>
<div class="local_desc01 local_desc">
    <p>각 메뉴의 라인을 <strong>상하방향</strong>으로 <strong>드래그&드롭</strong>하여 <strong>나열순서</strong>를 변경할 수 있습니다.</p>
    <p><strong>◀ | ▶</strong> 아이콘을 클릭하여 메뉴의 <strong>트리구조</strong>를 변경할 수 있습니다.</p>
    <p><strong>주의!</strong> 메뉴설정 작업 후 반드시 <strong>확인</strong>을 누르셔야 저장됩니다.</p>
</div>

<form name="fmenulist" id="fmenulist" method="post" action="./menu_list_update.php" onsubmit="return fmenulist_submit(this);">
<input type="hidden" name="token" value="">
<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">트리설정</th>
        <th scope="col">메뉴</th>
        <th scope="col">순서코드</th>
        <th scope="col">링크</th>
        <th scope="col">새창</th>
        <th scope="col">PC사용</th>
        <th scope="col">모바일사용</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $bg = 'bg'.($i%2);
        $sub_menu_class = '';
        if(strlen($row['me_code']) == 4) {
            $sub_menu_class = ' sub_menu_class';
            $sub_menu_info = '<span class="sound_only">'.$row['me_name'].'의 서브</span>';
            $sub_menu_ico = '<span class="sub_menu_ico"></span>';
        }

        $search  = array('"', "'");
        $replace = array('&#034;', '&#039;');
        $me_name = str_replace($search, $replace, $row['me_name']);

		// 들여쓰기 padding-left
		$row['me_padding_left'] = 5+(strlen($row['me_code'])/2-1)*15 .'px';
        // depth 
        $row['me_depth'] = strlen($row['me_code'])/2 - 1;
		
		// 링크 수정
		$row['me_link'] = (substr($row['me_link'],0,1)=='/' && !preg_match("/http/i",$row['me_link'])) ? G5_URL.$row['me_link'] : bpwg_set_http($row['me_link']);
		$slen = 2;
        if(strlen($row['me_code']) == 4) {
            $slen = 2;
        }
        else if(strlen($row['me_code']) == 6) {
            $slen = 4;
        }
    ?>
    <tr class="menu_list menu_group_<?=substr($row['me_code'], 0, $slen)?>" me_code="<?=$row['me_code']?>" data-id="<?=$row['me_id']?>" data-depth="<?=$row['me_depth']?>" data-code="<?=$row['me_code']?>">
        <td class="td_idx"><?=$row['me_id']?></td>
        <td class="td_depth w-[60px] text-center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
        <td class="td_category depth_<?=$row['me_depth']?>">
            <input type="hidden" name="code[]" value="<?php echo substr($row['me_code'], 0, 2) ?>">
            <input type="hidden" name="depth[]" value="<?php echo strlen($row['me_code'])/2 ?>">
            <label for="me_name_<?php echo $i; ?>" class="sound_only2"><?php echo $sub_menu_info; ?> 메뉴<strong class="sound_only2"> 필수</strong></label>
            <input type="text" name="me_name[]" value="<?php echo $me_name; ?>" id="me_name_<?php echo $i; ?>" required class="required tbl_input full_input">
        </td>
        <td class="td_code w-[80px]">
            <label for="me_code_<?php echo $i; ?>" class="sound_only">순서코드</label>
            <input type="text" name="me_code[]" readonly value="<?php echo $row['me_code'] ?>" id="me_code_<?php echo $i; ?>" class="tbl_input readonly">
        </td>
        <td class="w-[400px]">
            <label for="me_link_<?php echo $i; ?>" class="sound_only2">링크<strong class="sound_only2"> 필수</strong></label>
            <input type="text" name="me_link[]" value="<?php echo $row['me_link'] ?>" id="me_link_<?php echo $i; ?>" required class="required tbl_input full_input">
        </td>
        <td class="w-[100px]">
            <label for="me_target_<?php echo $i; ?>" class="sound_only">새창</label>
            <select name="me_target[]" id="me_target_<?php echo $i; ?>">
                <option value="self"<?php echo get_selected($row['me_target'], 'self', true); ?>>사용안함</option>
                <option value="blank"<?php echo get_selected($row['me_target'], 'blank', true); ?>>사용함</option>
            </select>
        </td>
        <td class="w-[100px]">
            <label for="me_use_<?php echo $i; ?>" class="sound_only">PC사용</label>
            <select name="me_use[]" id="me_use_<?php echo $i; ?>">
                <option value="1"<?php echo get_selected($row['me_use'], '1', true); ?>>사용함</option>
                <option value="0"<?php echo get_selected($row['me_use'], '0', true); ?>>사용안함</option>
            </select>
        </td>
        <td class="w-[100px]">
            <label for="me_mobile_use_<?php echo $i; ?>" class="sound_only">모바일사용</label>
            <select name="me_mobile_use[]" id="me_mobile_use_<?php echo $i; ?>">
                <option value="1"<?php echo get_selected($row['me_mobile_use'], '1', true); ?>>사용함</option>
                <option value="0"<?php echo get_selected($row['me_mobile_use'], '0', true); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_mng w-[100px]">
            <div class="flex gap-1 justify-center">
                <?php if(strlen($row['me_code']) < 6) { ?>
                <button type="button" class="btn_add_submenu btn_03">추가</button>
                <?php } ?>
                <button type="button" class="btn_del_menu btn_02">삭제</button>
            </div>
        </td>
    </tr>
    <?php
    }

    if ($i==0)
        echo '<tr id="empty_menu_list"><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <button type="button" onclick="return add_menu();" class="btn btn_02">메뉴추가<span class="sound_only"> 새창</span></button>
    <input type="submit" name="act_button" value="확인" class="btn_submit btn ">
</div>

</form>



<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');