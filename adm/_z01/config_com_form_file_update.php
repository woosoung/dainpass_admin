<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    if(@count(${$_POST['set_type'].'_del'})){
        foreach(${$_POST['set_type'].'_del'} as $k=>$v) {
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
    upload_multi_file($_FILES['file_preparing'],'set','preparing','com');
    //favicon 멀티파일처리
    upload_multi_file($_FILES['file_favicon'],'set','favicon','com');
    //ogimg 멀티파일처리
    upload_multi_file($_FILES['file_ogimg'],'set','ogimg','com');
    //siemap 멀티파일처리
    upload_multi_file($_FILES['file_sitemap'],'set','sitemap','com');
}