<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    // _XXXX_에서 XXXX는 해당 fle_db_idx로 대체되어야 함
    
    // favicon 삭제 처리
    $favicon_del_var = $_POST['set_type'].'_favicon_del';
    if(isset(${$favicon_del_var}) && is_array(${$favicon_del_var}) && count(${$favicon_del_var}) > 0){
        foreach(${$favicon_del_var} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    // plflogo 삭제 처리
    $plflogo_del_var = $_POST['set_type'].'_plflogo_del';
    if(isset(${$plflogo_del_var}) && is_array(${$plflogo_del_var}) && count(${$plflogo_del_var}) > 0){
        foreach(${$plflogo_del_var} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    // noimage 삭제 처리
    $noimage_del_var = $_POST['set_type'].'_noimage_del';
    if(isset(${$noimage_del_var}) && is_array(${$noimage_del_var}) && count(${$noimage_del_var}) > 0){
        foreach(${$noimage_del_var} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    // ogplfimg 삭제 처리
    $ogplfimg_del_var = $_POST['set_type'].'_ogplfimg_del';
    if(isset(${$ogplfimg_del_var}) && is_array(${$ogplfimg_del_var}) && count(${$ogplfimg_del_var}) > 0){
        foreach(${$ogplfimg_del_var} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
         
    if(count($merge_del) > 0){
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
    //noimage 멀티파일처리
    upload_multi_file($_FILES['file_noimage'],'set','noimage','plf');
    //ogimg 멀티파일처리
    upload_multi_file($_FILES['file_ogimg'],'set','ogimg','plf');
    //siemap 멀티파일처리
    // upload_multi_file($_FILES['file_sitemap'],'set','sitemap','plf');
}