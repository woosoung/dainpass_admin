<?php
include_once('./_common.php');
//반려동물(10), 운동(20), 음식(30), 미용(40), 여행·레저(50), 문화(60), 교육(70), 업무지원(80), 의료·건강(90), 가정·생활(a0)
//포크수푼(음식), 역기(운동), 화장품(미용), 연필(교육), 비행기지도(여행/레저), 홈(홈/생활), 십자가(의료/건강), 면도날(문화), 발바닥(반려동물), 가방(업무지원)
$img_urls1 = [
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/bd8c1270-a182-4958-a29f-7374a1649fcc.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/86e15948-bd2f-4d0c-9c29-c31edb2cc530.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/6be079fd-33a6-450c-a71f-6d6e85592ed0.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/858bac78-40c1-404c-8fca-54477dd65471.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/a64f923c-1641-4322-833f-9a55f61f44f5.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/54771962-2604-4812-bc50-4e5231e197c9.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/b5c0c072-93f1-4a45-9981-ddf19602312e.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/60dcc4cb-d5ab-4a4e-a2e2-12a98cc9b910.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/90fc7e0e-a370-420b-8fb9-1d3a550fb38b.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/0e33ba86-4be3-4fd4-a774-91b13ed88754.svg',
];
$img_urls2 = [
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/8f0e6cc8-4541-455e-b0c6-d6144516e81d.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/2e3467ce-f339-4b7a-8a82-2144e2e72812.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/bfef0792-7f03-48e9-ba58-3a31001e92ff.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/c6a75120-e218-4fa8-83f4-e942d31e7c65.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/4128baf0-3855-459f-abe5-c0adf34b4cf9.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/5bc73823-c980-43a9-8d8e-74065eab9dfd.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/93d094c6-ee97-49ed-8eff-0eb0e495f216.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/97f5640f-ba7d-42a8-85f0-336eb16f1b51.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/8e8fde90-209c-45a8-9c79-8946a0c53fd7.svg',
    'https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/admin/category/f15f9d55-a8cb-4b50-8c9a-9bb2fb498fa2.svg',
];
/*
INSERT INTO dain_file 
(fle_mb_id, fle_db_tbl, fle_db_idx, fle_width, fle_height, fle_desc, fle_mime_type, fle_type, fle_size, fle_path, fle_name, fle_name_orig, fle_sort, fle_status, fle_reg_dt, fle_update_dt, fle_db_id)
    VALUES
('admin','shop_category','10',53,41,'','image/svg+xml','cat_off',1700,'data/admin/category/bd8c1270-a182-4958-a29f-7374a1649fcc.svg','bd8c1270-a182-4958-a29f-7374a1649fcc.svg','foots.svg',0,'ok', NOW(), NOW(), '10')
,('admin','shop_category','20',48,48,'','image/svg+xml','cat_off',1700,'data/admin/category/86e15948-bd2f-4d0c-9c29-c31edb2cc530.svg','86e15948-bd2f-4d0c-9c29-c31edb2cc530.svg','exercise.svg',0,'ok', NOW(), NOW(), '20')
,('admin','shop_category','30',36,50,'','image/svg+xml','cat_off',1100,'data/admin/category/6be079fd-33a6-450c-a71f-6d6e85592ed0.svg','6be079fd-33a6-450c-a71f-6d6e85592ed0.svg','food.svg',0,'ok', NOW(), NOW(), '30')
,('admin','shop_category','40',53,42,'','image/svg+xml','cat_off',1900,'data/admin/category/858bac78-40c1-404c-8fca-54477dd65471.svg','858bac78-40c1-404c-8fca-54477dd65471.svg','beauty.svg',0,'ok', NOW(), NOW(), '40')
,('admin','shop_category','50',67,44,'','image/svg+xml','cat_off',1400,'data/admin/category/a64f923c-1641-4322-833f-9a55f61f44f5.svg','a64f923c-1641-4322-833f-9a55f61f44f5.svg','travel.svg',0,'ok', NOW(), NOW(), '50')
,('admin','shop_category','60',50,50,'','image/svg+xml','cat_off',1000,'data/admin/category/54771962-2604-4812-bc50-4e5231e197c9.svg','54771962-2604-4812-bc50-4e5231e197c9.svg','culture.svg',0,'ok', NOW(), NOW(), '60')
,('admin','shop_category','70',46,50,'','image/svg+xml','cat_off',1200,'data/admin/category/b5c0c072-93f1-4a45-9981-ddf19602312e.svg','b5c0c072-93f1-4a45-9981-ddf19602312e.svg','education.svg',0,'ok', NOW(), NOW(), '70')
,('admin','shop_category','80',45,38,'','image/svg+xml','cat_off',1500,'data/admin/category/60dcc4cb-d5ab-4a4e-a2e2-12a98cc9b910.svg','60dcc4cb-d5ab-4a4e-a2e2-12a98cc9b910.svg','work.svg',0,'ok', NOW(), NOW(), '80')
,('admin','shop_category','90',42,42,'','image/svg+xml','cat_off',238,'data/admin/category/90fc7e0e-a370-420b-8fb9-1d3a550fb38b.svg','90fc7e0e-a370-420b-8fb9-1d3a550fb38b.svg','health.svg',0,'ok', NOW(), NOW(), '90')
,('admin','shop_category','a0',34,38,'','image/svg+xml','cat_off',699,'data/admin/category/0e33ba86-4be3-4fd4-a774-91b13ed88754.svg','0e33ba86-4be3-4fd4-a774-91b13ed88754.svg','pet.svg',0,'ok', NOW(), NOW(), 'a0')
,('admin','shop_category','b0',53,41,'','image/svg+xml','cat_on',1700,'data/admin/category/8f0e6cc8-4541-455e-b0c6-d6144516e81d.svg','8f0e6cc8-4541-455e-b0c6-d6144516e81d.svg','foots_on.svg',0,'ok', NOW(), NOW(), '10')
,('admin','shop_category','c0',48,48,'','image/svg+xml','cat_on',1700,'data/admin/category/2e3467ce-f339-4b7a-8a82-2144e2e72812.svg','2e3467ce-f339-4b7a-8a82-2144e2e72812.svg','exercise_on.svg',0,'ok', NOW(), NOW(), '20')
,('admin','shop_category','d0',36,50,'','image/svg+xml','cat_on',1100,'data/admin/category/bfef0792-7f03-48e9-ba58-3a31001e92ff.svg','bfef0792-7f03-48e9-ba58-3a31001e92ff.svg','food_on.svg',0,'ok', NOW(), NOW(), '30')
,('admin','shop_category','e0',53,42,'','image/svg+xml','cat_on',1900,'data/admin/category/c6a75120-e218-4fa8-83f4-e942d31e7c65.svg','c6a75120-e218-4fa8-83f4-e942d31e7c65.svg','beauty_on.svg',0,'ok', NOW(), NOW(), '40')
,('admin','shop_category','f0',67,44,'','image/svg+xml','cat_on',1400,'data/admin/category/4128baf0-3855-459f-abe5-c0adf34b4cf9.svg','4128baf0-3855-459f-abe5-c0adf34b4cf9.svg','travel_on.svg',0,'ok', NOW(), NOW(), '50')
,('admin','shop_category','g0',50,50,'','image/svg+xml','cat_on',1000,'data/admin/category/5bc73823-c980-43a9-8d8e-74065eab9dfd.svg','5bc73823-c980-43a9-8d8e-74065eab9dfd.svg','culture_on.svg',0,'ok', NOW(), NOW(), '60')
,('admin','shop_category','h0',46,50,'','image/svg+xml','cat_on',1200,'data/admin/category/93d094c6-ee97-49ed-8eff-0eb0e495f216.svg','93d094c6-ee97-49ed-8eff-0eb0e495f216.svg','education_on.svg',0,'ok', NOW(), NOW(), '70')
,('admin','shop_category','i0',45,38,'','image/svg+xml','cat_on',1500,'data/admin/category/97f5640f-ba7d-42a8-85f0-336eb16f1b51.svg','97f5640f-ba7d-42a8-85f0-336eb16f1b51.svg','work_on.svg',0,'ok', NOW(), NOW(), '80')
,('admin','shop_category','j0',42,42,'','image/svg+xml','cat_on',238,'data/admin/category/8e8fde90-209c-45a8-9c79-8946a0c53fd7.svg','8e8fde90-209c-45a8-9c79-8946a0c53fd7.svg','health_on.svg',0,'ok', NOW(), NOW(), '90')
,('admin','shop_category','k0',34,38,'','image/svg+xml','cat_on',699,'data/admin/category/f15f9d55-a8cb-4b50-8c9a-9bb2fb498fa2.svg','f15f9d55-a8cb-4b50-8c9a-9bb2fb498fa2.svg','pet_on.svg',0,'ok', NOW(), NOW(), 'a0')
;
*/

?>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body{background:gray;}
        .img_container{
            margin-top:20px;
        }
         .sp_img {
            display: inline-block;
            border:1px solid red;
        }
    </style>
</head>
<body>
    <div class="img_container">
        <?php foreach ($img_urls1 as $url): ?>
        <span class="sp_img"><img src="<?php echo $url; ?>" alt="Image"></span>
        <?php endforeach; ?>
    </div>
    <div class="img_container">
        <?php foreach ($img_urls2 as $url): ?>
        <span class="sp_img"><img src="<?php echo $url; ?>" alt="Image"></span>
        <?php endforeach; ?>
    </div>
</body>
</html>