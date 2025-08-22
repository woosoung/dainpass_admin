<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    // _XXXX_에서 XXXX는 해당 fle_db_idx로 대체되어야 함
    
    // 기존 코드에서 @count 대신 안전한 체크로 변경
    if(isset(${$_POST['set_type'].'_favicon_del'}) && @count(${$_POST['set_type'].'_favicon_del'})){
        foreach(${$_POST['set_type'].'_favicon_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    if(isset(${$_POST['set_type'].'_plflogo_del'}) && @count(${$_POST['set_type'].'_plflogo_del'})){
        foreach(${$_POST['set_type'].'_plflogo_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    if(isset(${$_POST['set_type'].'_ogplfimg_del'}) && @count(${$_POST['set_type'].'_ogplfimg_del'})){
        foreach(${$_POST['set_type'].'_ogplfimg_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
         
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //준비중 멀티파일처리
    // upload_multi_file($_FILES['file_preparing'],'set','preparing','plf');
    //favicon 멀티파일처리
    upload_multi_file($_FILES['file_favicon'],'set','favicon','plf');
    //로고 멀티파일처리
    upload_multi_file($_FILES['file_plflogo'],'set','plflogo','plf');
    //ogimg 멀티파일처리
    upload_multi_file($_FILES['file_ogimg'],'set','ogimg','plf');
    //siemap 멀티파일처리
    // upload_multi_file($_FILES['file_sitemap'],'set','sitemap','plf');
}