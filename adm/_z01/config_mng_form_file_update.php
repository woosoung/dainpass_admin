<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    // _XXXX_에서 XXXX는 해당 fle_db_idx로 대체되어야 함
    if(@count(${$_POST['set_type'].'_afavicon_del'})){
        foreach(${$_POST['set_type'].'_afavicon_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(@count(${$_POST['set_type'].'_mnglogo_del'})){
        foreach(${$_POST['set_type'].'_mnglogo_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(count($del_arr)) delete_idx_s3_file($del_arr);
    
    //준비중 멀티파일처리
    // upload_multi_file($_FILES['file_preparing'],'set','preparing','admin/conf');
    //afavicon 멀티파일처리
    upload_multi_file($_FILES['file_afavicon'],'set','afavicon','admin/'.$_POST['set_type']);
    upload_multi_file($_FILES['file_mnglogo'],'set','mnglogo','admin/'.$_POST['set_type']);
    //ogimg 멀티파일처리
    // upload_multi_file($_FILES['file_ogimg'],'set','ogimg','admin/conf');
    //siemap 멀티파일처리
    // upload_multi_file($_FILES['file_sitemap'],'set','sitemap','admin/conf');
}