<?php
$sub_menu = "100290";
include_once('./_common.php');

if (!$is_manager)
    alert('접근권한이 없습니다.');

$g5['title'] = '메뉴 추가';
include_once(G5_PATH.'/head.sub.php');

// [메뉴추가] 버튼 클릭 시 1차 최상위 코드 생성
if($new == 'new' || !$code) {
	$depth = 1;
	$code = base_convert(substr($code,0, 2), 36, 10);
	$code += 36;
	$code = base_convert($code, 10, 36);
	$me_code = $code;

}
// [추가] 버튼 클릭 시 해당 메뉴의 맨 하단 코드 생성
else {
	$depth = strlen($code)/2+1;
	$code_last = substr($me_code_last, $depth*2-2,2);
	$me_code = base_convert(substr($code_last,0, 2), 36, 10);
	$me_code += 36;
	$me_code = $code.base_convert($me_code, 10, 36);
}
//echo $depth.'<br>';
// echo $code.'<br>';
// echo substr($code,0, 2).'<br>';
//echo $me_code.' 해당 그룹 마지막 me_code<br>';

// 들여쓰기 padding-left
$me_padding_left = 5+($depth-1)*15 .'px';
?>

<style>
#menu_frm .tbl_frm01 td{border-top:1px solid #e6e6e6 !important;}
#menu_frm h1{font-weight:600;}
#menu_frm .btn_win02.btn_win{text-align:center;}
#menu_frm .btn_win02.btn_win .btn_submit{float:none;height:30px;line-height:30px;position:relative;top:5px;}
#menu_frm .btn_win02.btn_win .btn_02{position:relative;height:30px;line-height:30px;top:5px;}
#menu_frm .add_select{height:30px;line-height:30px;}
</style>
<div id="menu_frm" class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fmenuform" id="fmenuform" class="new_win_con">

    <div class="new_win_desc">
        <label for="me_type">대상선택</label>
        <select name="me_type" id="me_type">
            <option value="">직접입력</option>
            <option value="board">게시판</option>
			<?php if(defined('G5_COMMUNITY_USE')&&!G5_COMMUNITY_USE) { ?>
            <option value="shop">쇼핑카테고리</option>
            <option value="it_type">쇼핑상품유형</option>
			<?php } ?>
            <option value="group">게시판그룹</option>
            <option value="content">내용관리</option>
        </select>
    </div>

    <div id="menu_result"></div>

    </form>

</div>

<script>
$(function() {
    $("#menu_result").load(
        "./menu_form_search.php"
    );

    function link_checks_all_chage(){

        var $links = $(opener.document).find("#menulist input[name='me_link[]']"),
            $o_link = $(".td_mng input[name='link[]']"),
            hrefs = [],
            menu_exist = false;
           
        if( $links.length ){
            $links.each(function( index ) {
                hrefs.push( $(this).val() );
            });

            $o_link.each(function( index ) {
                if( $.inArray( $(this).val(), hrefs ) != -1 ){
                    $(this).closest("tr").find("td:eq( 0 )").addClass("exist_menu_link");
                    menu_exist = true;
                }
            });
        }

        if( menu_exist ){
            $(".menu_exists_tip").show();
        } else {
            $(".menu_exists_tip").hide();
        }
    }

    function menu_result_change( type ){
        
        var dfd = new $.Deferred();

        $("#menu_result").empty().load(
            "./menu_form_search.php",
            { type : type },
            function(){
                dfd.resolve('Finished');
            }
        );

        return dfd.promise();
    }

    $("#me_type").on("change", function() {
        var type = $(this).val();

        var promise = menu_result_change( type );

        promise.done(function(message) {
            link_checks_all_chage(type);
        });

    });

    $(document).on("click", "#add_manual", function() {
        var me_name = $.trim($("#me_name").val());
        var me_link = $.trim($("#me_link").val());

        add_menu_list(me_name, me_link, "<?=$code?>", "<?=$me_code_last?>");
    });

    $(document).on("click", ".add_select", function() {
        var me_name = $.trim($(this).siblings("input[name='subject[]']").val());
        var me_link = $.trim($(this).siblings("input[name='link[]']").val());

        add_menu_list(me_name, me_link, "<?=$code?>", "<?=$me_code_last?>");
    });
});

function add_menu_list(name, link, code, me_code_last)
{
    var $menulist = $(".tbl_head01", opener.document);
    var ms = new Date().getTime();
    var me_code = "<?=$me_code?>";
    var sub_menu_class;
    var me_depth = me_code.length/2 - 1;
    <?php if($new == 'new') { ?>
    sub_menu_class = " class=\"td_category depth_"+me_depth+"\"";
    <?php } else { ?>
    sub_menu_class = " class=\"td_category depth_"+me_depth+"\"";
    <?php } ?>

    var list = "<tr class=\"menu_list menu_group_<?=$code?> ui-sortable-handle\" me_code=\"<?=$me_code?>\" data-id=\"\" data-depth=\""+me_depth+"\" data-code=\"<?=$me_code?>\">";
    list += "<td class=\"td_idx\"></td>";
    list += "<td class=\"td_depth w-[60px] text-center\">";
    list += "<a href=\"#\" alt=\"상위단계로\">◀</a> | <a href=\"#\" alt=\"하위단계로\">▶</a>";
    list += "</td>";
    list += "<td"+sub_menu_class+">";
    list += "<label for=\"me_name_"+ms+"\"  class=\"sound_only2\">메뉴<strong class=\"sound_only2\"> 필수</strong></label>";
    list += "<input type=\"hidden\" name=\"code[]\" value=\"<?php echo $code; ?>\">";
    list += "<input type=\"hidden\" name=\"depth[]\" value=\"<?php echo $depth; ?>\">";
    list += "<input type=\"text\" name=\"me_name[]\" value=\""+name+"\" id=\"me_name_"+ms+"\" required class=\"required tbl_input full_input\">";
    list += "</td>";
    list += "<td class=\"td_code w-[80px]\">";
    list += "<label for=\"me_code_"+me_code+"\"  class=\"sound_only\">순서코드</label>";
    list += "<input type=\"text\" name=\"me_code[]\" readonly value=\""+me_code+"\" id=\"me_code_"+ms+"\" class=\"tbl_input readonly\">";
    list += "</td>";
    list += "<td class=\"w-[400px]\">";
    list += "<label for=\"me_link_"+ms+"\"  class=\"sound_only\">링크<strong class=\"sound_only\"> 필수</strong></label>";
    list += "<input type=\"text\" name=\"me_link[]\" value=\""+link+"\" id=\"me_link_"+ms+"\" required class=\"required tbl_input full_input\">";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_target_"+ms+"\"  class=\"sound_only\">새창</label>";
    list += "<select name=\"me_target[]\" id=\"me_target_"+ms+"\">";
    list += "<option value=\"self\">사용안함</option>";
    list += "<option value=\"blank\">사용함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_use_"+ms+"\"  class=\"sound_only\">PC사용</label>";
    list += "<select name=\"me_use[]\" id=\"me_use_"+ms+"\">";
    list += "<option value=\"1\">사용함</option>";
    list += "<option value=\"0\">사용안함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_mobile_use_"+ms+"\"  class=\"sound_only\">모바일사용</label>";
    list += "<select name=\"me_mobile_use[]\" id=\"me_mobile_use_"+ms+"\">";
    list += "<option value=\"1\">사용함</option>";
    list += "<option value=\"0\">사용안함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"td_mng w-[100px]\">";
    list += "<div class=\"flex gap-1 justify-center\">";
    <?php if(strlen($me_code) < 6) { ?>
    list += "<button type=\"button\" class=\"btn_add_submenu btn_03\">추가</button>";
    <?php } ?>
    list += "<button type=\"button\" class=\"btn_del_menu btn_02\">삭제</button>";
    list += "</div>";
    list += "</td>";
    list += "</tr>";

    // 메뉴 삽입 위치
	var $menu_last = null;
    
    if(me_code_last) {
        $menu_last = $menulist.find("tr[me_code="+me_code_last+"]");
	}
    else {
        $menu_last = $menulist.find("tr.menu_list:last");
	}

	// 리스트 항목이 한개라도 있으면 그룹의 마지막 부분에 삽입
	if($menu_last.size() > 0) {
        $menu_last.after(list);
    }
	// 리스트 항목이 없으면 새로운 항목 한개를 생성
	else {
        if($menulist.find("#empty_menu_list").size() > 0)
            $menulist.find("#empty_menu_list").remove();

        $menulist.find("table tbody").append(list);
    }

    // 테이블 리스트 라인 색상 전체 변경
	// $menulist.find("tr.menu_list").each(function(index) {
    //     $(this).removeClass("bg0 bg1")
    //         .addClass("bg"+(index % 2));
    // });

    window.close();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');