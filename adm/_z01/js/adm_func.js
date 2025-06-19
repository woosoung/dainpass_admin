//hex to rgba
if(typeof(tms_hex2rgba) != 'function'){	
function tms_hex2rgba(hex, alpha) {
    var r = parseInt(hex.slice(1, 3), 16),
        g = parseInt(hex.slice(3, 5), 16),
        b = parseInt(hex.slice(5, 7), 16);

    return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
    
    //else {
    //    return "rgb(" + r + ", " + g + ", " + b + ")";
    //}
}
}
//rgba to hex
if(typeof(tms_rgba2hex) != 'function'){
function tms_rgba2hex(rgba){
    var backtxt = rgba.substring(rgba.indexOf('(')+1);//rgba(까지 잘라낸 나머지 문자열 대입
    var oktxt = backtxt.substr(0,backtxt.length-1); //마지막 )를 잘라낸 나머지 문자열 대입
    var okarr = oktxt.split(',');//(,)로 분할해서 배열변수에 대입
    var okr = $.trim(okarr[0]);
    var okg = $.trim(okarr[1]);
    var okb = $.trim(okarr[2]);
    var oka = $.trim(okarr[3]);
    var result = '';
    
    result = "#"+
        ("0"+parseInt(okr,10).toString(16)).slice(-2) +
        ("0"+parseInt(okg,10).toString(16)).slice(-2) +
        ("0"+parseInt(okb,10).toString(16)).slice(-2);
    
    if(oka){
        return {"color":result,"opacity":parseFloat(oka)};
    }else{
        return {"color":result,"opacity":0};
    }
}
}


//첨부파일 한 개씩 삭제처리하는 함수
if(typeof(file_single_del) != 'function'){
function file_single_del(fle_db_tbl,fle_idx){
    if(confirm("선택한 파일을 정말 삭제 하시겠습니까?")){
        var single_file_url = g5_url+'/adm/_z01/ajax/wgt_file_single_del.php';
        $.ajax({
            type:"POST",
            url:single_file_url,
            dataType:"text",
            data:{'fle_db_tbl':fle_db_tbl,'fle_idx':fle_idx},
            success:function(res){
                if(res){
                    alert(res);
                }else{
                    location.reload();
                }
            },
            error:function(e){
                alert(e.responseText);
            }
        });
    }
}
}

//한 줄의 첨부파일들을 일괄 삭제처리하는 함수
if(typeof(files_row_del) != 'function'){
function files_row_del(fle_db_tbl,fle_idxs){
    if(confirm("선택한 파일을 전부 삭제 하시겠습니까?")){
        var row_files_url = g5_url+'/adm/_z01/ajax/wgt_files_row_del.php';
        $.ajax({
            type:"POST",
            url:row_files_url,
            dataType:"text",
            data:{'fle_db_tbl':fle_db_tbl,'fle_idxs':fle_idxs},
            success:function(res){
                if(res){
                    alert(res);
                }else{
                    location.reload();
                }
            },
            error:function(e){
                alert(e.responseText);
            }
        });
    }
}
}

//
if(typeof(check_all_tms) != 'function'){
function check_all_tms(f)
{
    //alert(f.chkall.checked);return false;
    var chk = $('input[name^="chk["]');
    
    chk.each(function(){
        $(this).attr('checked',f.chkall.checked);
    });
}
}

//목록페이지 checkbox 체크되어 있는항목이 한 개라도 존재하는 확인하는 함수
if(typeof(is_checked_tms) != 'function'){
function is_checked_tms(hidden_chk_list_name){
    var checked = false;
    var chk = $('input[name^="'+hidden_chk_list_name+'["]');
    
    chk.each(function(){
        if($(this).attr('checked'))
            checked = true;
    });
    
    return checked;
}
}    