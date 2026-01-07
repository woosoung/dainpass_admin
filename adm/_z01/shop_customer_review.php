<?php
$sub_menu = "960400";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// 개발자 권한 체크 (mb_level 8 이상)
$is_developer = isset($member['mb_level']) && $member['mb_level'] >= 8;

// review_id 검증
$review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;

if ($review_id <= 0) {
    alert("리뷰번호가 없습니다.");
}

if ($review_id > 0) {

    // 리뷰 상세 조회
    $sql = " SELECT sr.*,
                    c.user_id,
                    c.name as customer_name,
                    c.nickname,
                    s.shop_name,
                    s.name as shop_display_name
             FROM shop_review AS sr
             LEFT JOIN customers AS c ON sr.customer_id = c.customer_id
             LEFT JOIN shop AS s ON sr.shop_id = s.shop_id
             WHERE sr.review_id = {$review_id}
             AND sr.shop_id = {$shop_id}
             AND sr.sr_deleted = 'N' ";
    
    $review = sql_fetch_pg($sql);
    
    if (!isset($review['review_id']) || !$review['review_id']) {
        alert("리뷰자료가 없습니다.");
    }

    $html_title = "고객리뷰 - 상세보기";
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<div class="local_desc01 local_desc">
    <p>
        고객이 작성한 리뷰의 상세 내용을 확인할 수 있습니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">리뷰ID</th>
        <td><?php echo $review['review_id']; ?></td>
    </tr>
    <tr>
        <th scope="row">회원 닉네임</th>
        <td>
            <?php echo $review['nickname'] ? htmlspecialchars($review['nickname']) : '-'; ?>
        </td>
    </tr>
    <tr>
        <th scope="row">평점</th>
        <td>
            <?php
            $score_text = '';
            switch ($review['sr_score']) {
                case 5:
                    $score_text = '<span style="color:green; font-weight:bold;">5점 (매우만족)</span>';
                    break;
                case 4:
                    $score_text = '<span style="color:blue; font-weight:bold;">4점 (만족)</span>';
                    break;
                case 3:
                    $score_text = '<span style="color:orange; font-weight:bold;">3점 (보통)</span>';
                    break;
                case 2:
                    $score_text = '<span style="color:#ff6b6b; font-weight:bold;">2점 (불만)</span>';
                    break;
                case 1:
                    $score_text = '<span style="color:red; font-weight:bold;">1점 (매우불만)</span>';
                    break;
                default:
                    $score_text = htmlspecialchars($review['sr_score']) . '점';
            }
            echo $score_text;
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">내용</th>
        <td>
            <div class="review_content_view" style="padding: 15px; border: 1px solid #ddd; background: #f9f9f9; min-height: 100px; line-height: 1.6;">
                <?php echo nl2br(get_text($review['sr_content'])); ?>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">등록일시</th>
        <td><?php echo $review['sr_created_at'] ? date('Y-m-d H:i:s', strtotime($review['sr_created_at'])) : '-'; ?></td>
    </tr>
    <tr>
        <th scope="row">수정일시</th>
        <td><?php echo $review['sr_updated_at'] ? date('Y-m-d H:i:s', strtotime($review['sr_updated_at'])) : '-'; ?></td>
    </tr>
    <tr>
        <th scope="row">고객IP</th>
        <td>
            <?php
            if ($review['sr_ip']) {
                if ($is_developer) {
                    // 개발자는 전체 IP 표시
                    echo htmlspecialchars($review['sr_ip']);
                } else {
                    // 가맹점 관리자는 뒤 두 영역 마스킹
                    $ip_parts = explode('.', $review['sr_ip']);
                    if (count($ip_parts) == 4) {
                        echo htmlspecialchars($ip_parts[0] . '.' . $ip_parts[1] . '.***.***');
                    } else {
                        // IPv6나 다른 형식
                        echo '***';
                    }
                }
            } else {
                echo '-';
            }
            ?>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./shop_customer_review_list.php" class="btn_02 btn">목록</a>
    <?php if ($is_developer) { ?>
    <a href="./shop_customer_review_form.php?w=u&review_id=<?php echo $review_id; ?>" class="btn btn_03">수정</a>
    <?php } ?>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
