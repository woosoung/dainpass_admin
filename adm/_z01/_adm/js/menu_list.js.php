<script>
$(function() {
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
    
    $(".tbl_head01 tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: ".tbl_head01 tr:not(.no-data)"
		, stop: function(event, ui) {
            min_depth = 2;
			//alert(ui.item.html());
            const po = (ui.item.prev().attr('data-id') !== undefined)?ui.item.prev():null;//prev object
            const co = ui.item; //current object
            const no = (ui.item.next().attr('data-id') !== undefined)?ui.item.next():null; //next object
            let cdepth = 0; //새로 갱신된 depth
            // 우선 현재객체의 depth_X 클래스를 제거한다.
            co.find('.td_category').removeClass((idx,cls) => {
                return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
            });
			if(!po){ //맨 위로 이동 했을 경우 ==============================================
                co.attr('data-depth', 0);
                cdepth = 0;
                co.find('.td_category').addClass('depth_0');
            }
            else if(po && no){ //사이범위에서 이동 했을 경우 =================================
                let po_depth = Number(po.attr('data-depth'));
                let no_depth = Number(no.attr('data-depth'));
                
                if(po_depth == no_depth){ //이전과 다음이 같은 depth일때 - 이전과 같은 depth
                    co.attr('data-depth', po_depth);
                    cdepth = po_depth;
                    co.find('.td_category').addClass('depth_'+po_depth);
                }
                else if(po_depth < no_depth){ // 이전보다 다음 depth가 작을때 - 다음과 같은 depth
                    co.attr('data-depth', no_depth);
                    cdepth = no_depth;
                    co.find('.td_category').addClass('depth_'+no_depth);
                }
                else if(po_depth > no_depth){ // 이전보다 다음 depth가 클때 - 이전과 같은 depth
                    co.attr('data-depth', po_depth);
                    cdepth = po_depth;
                    co.find('.td_category').addClass('depth_'+po_depth);
                }
            }
            else{ //맨 아래로 이동 했을 경우 ===============================================
                const po_depth = Number(po.attr('data-depth'));
                co.attr('data-depth', po_depth);
                cdepth = po_depth;
                co.find('.td_category').addClass('depth_'+po_depth);
            }

            if(cdepth < 2){ // 계층이 0 또는 1 일경우 '추가'버튼이 필요하다.
                if(!co.find('.btn_add_submenu').length){
                    $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(co.find('.td_mng').find('div'));
                }
            }
            else{ // 계층이 2 이상일경우 '추가'버튼이 필요없다.
                if(co.find('.btn_add_submenu').length){
                    co.find('.btn_add_submenu').remove();
                }
            }

            resortCode();
		}
	});

    $(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
        let cur_tr = $(this).closest('tr');
        const prev_depth = $(this).closest('tr').prev().attr('data-id') == undefined?null:Number($(this).closest('tr').prev().attr('data-depth'));
        const next_depth = $(this).closest('tr').next().attr('data-id') == undefined?null:Number($(this).closest('tr').next().attr('data-depth'));
        let cur_depth = Number($(this).closest('tr').attr('data-depth'));
        const direct = ($(this).index() == 0)?'left':'right';
        if(prev_depth == null){
            alert('첫번째 메뉴는 좌우로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth == 0 && direct == 'left'){
            alert('1차메뉴(최상위메뉴)는 좌측로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth == 2 && direct == 'right'){
            alert('3차메뉴(하위메뉴)는 우측으로 이동할 수 없습니다.');
            return false;
        }
        else if(prev_depth < cur_depth && cur_depth < next_depth){
            alert('상위메뉴와 하위메뉴가 존재하면 이동할 수 없습니다.');
            return false;
        }
        else if(prev_depth < cur_depth && direct == 'right'){
            alert('상위메뉴 보다 2단계 하위로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth < next_depth && direct == 'left'){
            alert('하위메뉴 보다 2단계 상위로 이동할 수 없습니다.');
            return false;
        }
        
        cur_depth = (direct == 'left')?cur_depth - 1:cur_depth + 1;

        if(cur_depth < 2){
            if(!cur_tr.find('.btn_add_submenu').length){
                $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(cur_tr.find('.td_mng').find('div'));
            }
        }else{
            if(cur_tr.find('.btn_add_submenu').length){
                cur_tr.find('.btn_add_submenu').remove();
            }
        }

        // 우선 현재객체의 depth_X 클래스를 제거한다.
        cur_tr.find('.td_category').removeClass((idx,cls) => {
            return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
        });
        cur_tr.attr('data-depth', cur_depth);
        cur_tr.find('.td_category').addClass('depth_'+cur_depth);
        resortCode();
    });

	$(document).on("click", ".btn_add_submenu", function() {
        var code = $(this).closest("tr").find("input[name='me_code[]']").val();
		
		// 해당 메뉴 그룹의 맨 마지막 me_code를 같이 던져줘야 한다. (생성 dom 추가 위치 & 코드)
		var me_code_last = code;
		$('.tbl_head01 tbody tr').each(function(i,v){
			// console.log( code +'='+ $(this).attr('me_code') + ' 길이=' + code.length + ' 잘린코드=' + $(this).attr('me_code').substring(0,code.length) );
			if( code == $(this).attr('me_code').substring(0,code.length) ) {
				me_code_last = $(this).attr('me_code');
			}
		});
		
        add_submenu(code, me_code_last);
    });

	$(document).on("click", ".btn_del_menu", function() {
        if(!confirm("메뉴를 삭제하시겠습니까?"))
            return false;
		
		var tr_me_code = $(this).closest("tr").attr('me_code');

		$('.tbl_head01 tr[me_code^='+tr_me_code+']').remove();

        if($(".tbl_head01 tr.menu_list").size() < 1) {
            var list = "<tr id=\"empty_menu_list\"><td colspan=\"<?php echo $colspan; ?>\" class=\"empty_table\">자료가 없습니다.</td></tr>\n";
            $(".tbl_head01 table tbody").append(list);
        }
    });
});
// console.log(makeCode());
// console.log(nextCode('Z0ZZ','n'));
// console.log(str2.slice(0,-2));
// console.log(str2);
// console.log(str);
// console.log(nextCode('10','n'));
// 메뉴 코드 재설정
function resortCode(){
    $(".tbl_head01 tr.menu_list").each(function(idx) {
        const prev_depth = ($(this).prev().attr('data-depth') !== undefined)?Number($(this).prev().attr('data-depth')):null;
        const prev_code = ($(this).prev().attr('me_code') !== undefined)?$(this).prev().attr('me_code'):null;
        let cur_depth = Number($(this).attr('data-depth'));
        let cur_tr = $(this);
        let cur_code = '';
        
        if(idx == 0){
            cur_code = nextMenuCode(); // '10'으로 셋팅
            cur_tr.find('.td_category').removeClass((idx,cls) => {
                return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
            });
            cur_depth = 0;
            cur_tr.attr('data-depth', cur_depth);
            cur_tr.find('.td_category').addClass('depth_'+cur_depth);
        }
        else{
            // 만약 상위메뉴가 현재메뉴의 2단계 상위일경우 현재메뉴를 1단계 상위로 변경
            if(prev_depth + 2 == cur_depth) cur_depth = cur_depth - 1; 
            
            if(prev_depth == cur_depth){ // 이전과 같은 계층일때
                // console.log('prev_code='+prev_code);
                cur_code = nextMenuCode(prev_code, 'n');
            }
            else if(prev_depth < cur_depth){ // 이전보다 하위계층일때
                cur_code = nextMenuCode(prev_code, 'c');
            }
            else if(prev_depth > cur_depth){ // 이전보다 상위계층일때
                if(cur_depth == 0){
                    cur_code = nextMenuCode(prev_code.substring(0,2), 'n');
                } else if(cur_depth == 1){
                    cur_code = nextMenuCode(prev_code.substring(0,4), 'n');
                }
            }
        }
        
        const code = (cur_depth == 0 || cur_depth == 1)?cur_code.substring(0,2):cur_code.substring(0,4);

        cur_tr.removeClass((id,cls) => { // menu_group_로 시작하는 클래스를 제거한다.
            return (cls.match(/(^|\s)menu_group_\S+/g || []).join(' '));
        });
        cur_tr.addClass('menu_group_'+code); // 새로운 코드로 클래스를 추가한다.
        cur_tr.attr('me_code', cur_code); // 새로운 코드를 me_code 속성 변경한다.
        cur_tr.attr('data-depth', cur_depth); // 새로운 depth를 data-depth 속성 변경한다.
        cur_tr.find('.td_category').removeClass((id,cls) => { // depth_로 시작하는 클래스를 제거한다.
            return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
        });
        cur_tr.find('.td_category').addClass('depth_' + cur_depth); // 새로운 depth로 클래스를 추가한다.
        cur_tr.find("input[name='code[]']").val(code); // 새로운 코드를 input[name='code[]'] 속성 변경한다.
        cur_tr.find("input[name='me_code[]']").val(cur_code); // 새로운 코드를 input[name='me_code[]'] 속성 변경한다.
        cur_tr.find("input[name='depth[]']").val(cur_depth); // 새로운 depth를 input[name='depth[]'] 속성 변경한다.
        cur_tr.find("label[for^='me_name_']").attr('for','me_name_' + cur_depth); // 라벨명을 변경한다.

        if(cur_depth < 2){
            if(!cur_tr.find('.btn_add_submenu').length){
                $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(cur_tr.find('.td_mng').find('div'));
            }
        }else{
            if(cur_tr.find('.btn_add_submenu').length){
                cur_tr.find('.btn_add_submenu').remove();
            }
        }
    });
}

// console.log(nextMenuCode('10','c'));

function nextMenuCode(code = '', type = '') { // type = c:자식코드, n:다음코드
    let cd = (code != '')?code:'10';
    let result = '';
    if (type == '') {
        result = cd;
        return result;
    }
    
    const carr = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    let fixcode = cd.slice(0,-2); // 끝에서 2번째 문자까지 자르고 나머지 문자열 저장
    // fixcode = fixcode.slice(0,-2);
    // carr.indexOf('Z')
    // cd.charAt(code.length - 2) // 끝에서 2번째 문자
    // cd.slice(0,-2) // 끝에서 2번째 문자까지 자르고 나머지 문자열
    let cur_front = '';
    let cur_rear = '';
    let next_front = '';
    let next_rear = '';

    if (type == 'c') { // 자식코드
        result = cd+'10';
    }
    else if(type == 'n') { // 다음코드
        cur_front = cd.charAt(cd.length - 2); // 코드의 끝에서 2번째 문자
        cur_rear = cd.charAt(cd.length - 1); // 코드의 끝에서 1번째 문자
        
        if(cur_front == 'Z'){
            next_front = 'Z';
            if(cur_rear == 'Z'){
                return false;
            }
            else{
                next_rear = carr[carr.indexOf(cur_rear) + 1];
            }
        }
        else{
            next_front = carr[carr.indexOf(cur_front) + 1];
            next_rear = '0';
        }
        result = fixcode + next_front + next_rear;
    }
    return result;
}

function add_menu()
{
    var max_code = base_convert(0, 10, 36);
    $(".tbl_head01 tr.menu_list").each(function() {
        var me_code = $(this).find("input[name='code[]']").val().substr(0, 2);
        if(max_code < me_code)
            max_code = me_code;
    });

    var url = "./menu_form.php?code="+max_code+"&new=new";
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function add_submenu(code, me_code_last){
    var url = "./menu_form.php?code="+code+'&me_code_last='+me_code_last;
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function base_convert(number, frombase, tobase) {
  //  discuss at: http://phpjs.org/functions/base_convert/
  // original by: Philippe Baumann
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  //   example 1: base_convert('A37334', 16, 2);
  //   returns 1: '101000110111001100110100'

  return parseInt(number + '', frombase | 0)
    .toString(tobase | 0);
}

function fmenulist_submit(f){

    var me_links = document.getElementsByName('me_link[]');
    var reg = /^javascript/; 

	for (i=0; i<me_links.length; i++){
        
	    if( reg.test(me_links[i].value) ){ 
        
            alert('링크에 자바스크립트문을 입력할수 없습니다.');
            me_links[i].focus();
            return false;
        }
    }

    return true;
}
</script>