<?php
$sub_menu = "920700";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$inq_id = isset($_GET['inq_id']) ? (int)$_GET['inq_id'] : 0;

if ($inq_id > 0) {
    // 최초 질문 조회
    $sql = " SELECT iq.*, s.name AS shop_name, s.shop_id AS shop_id_display
             FROM {$g5['shop_admin_inquiry_table']} iq
             LEFT JOIN {$g5['shop_table']} s ON iq.shop_id = s.shop_id
             WHERE iq.inq_id = '$inq_id' 
             AND (iq.inq_parent_id IS NULL OR iq.inq_parent_id = 0) ";
    
    $inq = sql_fetch_pg($sql);
    
    if (!isset($inq['inq_id']) || !$inq['inq_id']) {
        alert("문의자료가 없습니다.");
    }
    
    // 답변 목록 조회 (최신순) - PostgreSQL에서만 조회
    $sql_replies = " SELECT iqr.*
                     FROM {$g5['shop_admin_inquiry_table']} iqr
                     WHERE iqr.inq_parent_id = '$inq_id'
                     ORDER BY iqr.inq_created_at DESC ";
    $result_replies = sql_query_pg($sql_replies);
    
    $replies = array();
    $admin_ids = array(); // 관리자 ID 수집용
    
    if ($result_replies && isset($result_replies->result)) {
        while ($reply = sql_fetch_array_pg($result_replies->result)) {
            // 관리자 ID 수집 (MySQL에서 조회하기 위해)
            if (!empty($reply['reply_mb_id'])) {
                $admin_ids[] = addslashes($reply['reply_mb_id']);
            }
            $replies[] = $reply;
        }
    }
    
    // MySQL에서 관리자 정보 조회
    $admin_info = array();
    if (!empty($admin_ids)) {
        $admin_ids = array_unique($admin_ids);
        $admin_ids_str = "'" . implode("','", $admin_ids) . "'";
        $sql_admin = " SELECT mb_id, mb_name FROM {$g5['member_table']} WHERE mb_id IN ({$admin_ids_str}) ";
        $result_admin = sql_query($sql_admin);
        
        if ($result_admin) {
            while ($admin = sql_fetch_array($result_admin)) {
                $admin_info[$admin['mb_id']] = $admin['mb_name'];
            }
        }
    }
    
    // 답변 배열에 관리자 이름 추가
    foreach ($replies as $key => $reply) {
        if (!empty($reply['reply_mb_id']) && isset($admin_info[$reply['reply_mb_id']])) {
            $replies[$key]['admin_name'] = $admin_info[$reply['reply_mb_id']];
        }
    }
    
    $html_title = "가맹점문의 - 보기";
} else {
    alert("문의번호가 없습니다.");
}
// exit;
$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$qstr = '';
if (isset($_GET['inq_id'])) {
    $qstr = 'inq_id='.$_GET['inq_id'];
}

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_qa_question">가맹점 문의</a></li>
<li><a href="#anc_qa_reply_form">답변 작성</a></li>
<li><a href="#anc_qa_replies">답변 목록</a></li>
</ul>';
?>

<form name="fshopqaform" id="fshopqaform" action="./shop_qa_form_update.php" method="post" enctype="multipart/form-data" onsubmit="return fshopqaform_submit(this);">
<input type="hidden" name="inq_id" value="<?php echo $inq_id; ?>">
<input type="hidden" name="w" value="r">
<input type="hidden" name="token" value="<?php echo get_token(); ?>">
<input type="hidden" name="original_secret_yn" id="original_secret_yn" value="<?php echo $inq['inq_secret_yn']; ?>">

<section id="anc_qa_question">
    <h2 class="h2_frm"><?php echo $g5['title']; ?></h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>가맹점 문의 내용 (읽기 전용)</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">문의번호</th>
            <td><?=$inq['inq_id']?></td>
        </tr>
        <tr>
            <th scope="row">가맹점</th>
            <td>
                <div class="text-sm">
                    <strong><?=get_text($inq['shop_name'])?></strong>
                    <span class="text-gray-500 text-xs ml-2">(ID: <?=$inq['shop_id']?>)</span>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">가맹점 관리자</th>
            <td>
                <?php if(!empty($inq['shop_mb_id'])) { ?>
                    <span class="text-sm"><?=get_text($inq['shop_mb_id'])?></span>
                <?php } else { ?>
                    <span class="text-gray-500 text-sm">정보 없음</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">제목</th>
            <td>
                <a href="javascript:void(0);" class="text-blue-600">
                    <?php if($inq['inq_secret_yn'] == 'Y') { ?>
                        <i class="fa fa-lock text-gray-500"></i>
                    <?php } ?>
                    <?=get_text($inq['inq_subject'])?>
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row">작성일시</th>
            <td>
                <div class="text-sm">
                    <span class="text-gray-500"><?=substr($inq['inq_created_at'],0,16)?></span>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">내용</th>
            <td>
                <div class="qna_content_view" style="padding: 15px; border: 1px solid #ddd; background: #f9f9f9; min-height: 100px; line-height: 1.6;">
                    <?=nl2br(get_text($inq['inq_content']))?>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">비밀글</th>
            <td>
                <?php if($inq['inq_secret_yn'] == 'Y') { ?>
                    <span class="text-red-600"><i class="fa fa-lock"></i> 비밀글</span>
                <?php } else { ?>
                    <span class="text-green-600">공개글</span>
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<hr style="margin: 20px 0; border: 1px solid #ddd;">

<section id="anc_qa_reply_form">
    <h2 class="h2_frm">답변 작성</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>답변 작성</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="inq_reply_secret_yn">비밀글</label></th>
            <td>
                <label for="inq_reply_secret_yn_y">
                    <input type="radio" name="inq_reply_secret_yn" id="inq_reply_secret_yn_y" value="Y" <?php echo ($inq['inq_secret_yn'] == 'Y') ? 'checked' : ''; ?>>
                    비밀글
                </label>
                <label for="inq_reply_secret_yn_n" style="margin-left: 20px;">
                    <input type="radio" name="inq_reply_secret_yn" id="inq_reply_secret_yn_n" value="N" <?php echo ($inq['inq_secret_yn'] == 'N') ? 'checked' : ''; ?>>
                    공개글
                </label>
                <?php if($inq['inq_secret_yn'] == 'N') { ?>
                    <div id="secret_warning" style="display: none; margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; color: #856404; border-radius: 4px;">
                        <i class="fa fa-exclamation-triangle"></i> 댓글에 비밀글 설정을 하시면 본 대표 문의글도 비밀글로 전환됩니다.
                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="inq_reply_content">답변 내용<strong class="sound_only">필수</strong></label></th>
            <td>
                <textarea name="inq_reply_content" id="inq_reply_content" rows="10" class="frm_input" style="width: 100%;" required></textarea>
                <?php echo help("가맹점 문의에 대한 답변을 작성해주세요."); ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="입력" class="btn_submit btn" accesskey="s">
    <a href="./shop_qa_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<hr style="margin: 20px 0; border: 1px solid #ddd;">

<section id="anc_qa_replies">
    <h2 class="h2_frm">답변 목록</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <?php
        if (!empty($replies) && count($replies) > 0) {
            foreach ($replies as $idx => $reply) {
                $writer_name = '';
                $is_admin = false;
                
                // 관리자/가맹점 관리자 구분
                if (!empty($reply['reply_mb_id'])) {
                    // 관리자 답변
                    $writer_name = !empty($reply['admin_name']) ? get_text($reply['admin_name']) : get_text($reply['reply_mb_id']);
                    $is_admin = true;
                    // 관리자 답변 배경색 (연한 파란색)
                    $bg_class = 'bg-blue-50';
                    $border_color = '#3b82f6';
                } else {
                    // 가맹점 관리자 추가 질문
                    $writer_name = !empty($reply['shop_mb_id']) ? get_text($reply['shop_mb_id']) : '가맹점 관리자';
                    $is_admin = false;
                    // 가맹점 관리자 답변 배경색 (연한 초록색)
                    $bg_class = 'bg-green-50';
                    $border_color = '#10b981';
                }
                
                // 비밀글 처리: 본 문의글이 비밀글이면 댓글도 비밀글로 표시하되 lock 아이콘 없이 내용 표시
                $show_lock = false;
                $show_content = true;
                if ($inq['inq_secret_yn'] == 'N' && !empty($reply['inq_secret_yn']) && $reply['inq_secret_yn'] == 'Y') {
                    // 본 문의글이 공개인데 댓글이 비밀글인 경우에만 lock 표시
                    $show_lock = true;
                } elseif ($inq['inq_secret_yn'] == 'Y') {
                    // 본 문의글이 비밀글이면 댓글도 비밀글로 처리하되 lock 아이콘 없이 내용 표시
                    $show_lock = false;
                    $show_content = true;
                }
        ?>
        <div class="qna_reply_item <?=$bg_class?>" style="padding: 15px; margin-bottom: 10px; border: 2px solid <?=$border_color?>; border-radius: 4px;" data-reply-id="<?=$reply['inq_id']?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <strong><?=$writer_name?></strong>
                    <?php if($is_admin) { ?>
                        <span class="text-blue-700 text-sm font-weight-bold">(다인패스 관리자)</span>
                        <?php 
                        // 관리자 답변이고 가맹점 관리자가 확인하지 않은 경우 (inq_status = 'pending')
                        $can_edit = ($is_admin && !empty($reply['inq_status']) && $reply['inq_status'] == 'pending');
                        $is_confirmed = ($is_admin && !empty($reply['inq_status']) && $reply['inq_status'] == 'ok');
                        
                        // 가맹점 관리자 확인 여부 표시
                        if ($is_confirmed) {
                        ?>
                        <span class="text-green-600 text-xs ml-2"><i class="fa fa-check-circle"></i> 가맹점 확인완료</span>
                        <?php } else { ?>
                        <span class="text-orange-600 text-xs ml-2"><i class="fa fa-clock-o"></i> 가맹점 미확인</span>
                        <?php } ?>
                        
                        <?php if ($can_edit) { ?>
                        <span class="text-orange-600 text-xs ml-2">(수정/삭제 가능)</span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="text-green-700 text-sm font-weight-bold">(가맹점 관리자)</span>
                    <?php } ?>
                    <?php if($show_lock) { ?>
                        <i class="fa fa-lock text-gray-500 ml-2"></i>
                    <?php } ?>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="text-gray-500 text-sm"><?=!empty($reply['inq_created_at']) ? substr($reply['inq_created_at'],0,16) : ''?></span>
                    <?php if($is_admin && $can_edit) { ?>
                        <button type="button" class="btn_edit_reply btn_02 btn" data-reply-id="<?=$reply['inq_id']?>" style="padding: 4px 8px; font-size: 12px;">수정</button>
                        <button type="button" class="btn_delete_reply btn_02 btn" data-reply-id="<?=$reply['inq_id']?>" style="padding: 4px 8px; font-size: 12px; background: #dc3545; color: #fff;">삭제</button>
                    <?php } ?>
                </div>
            </div>
            <div class="qna_reply_content" id="reply_content_<?=$reply['inq_id']?>" style="padding: 10px; background: #fff; border: 1px solid #eee; min-height: 50px; line-height: 1.6; border-radius: 4px;">
                <?php if($show_lock && !$show_content) { ?>
                    <span class="text-gray-500">비밀글입니다.</span>
                <?php } else { ?>
                    <?=!empty($reply['inq_content']) ? nl2br(get_text($reply['inq_content'])) : ''?>
                <?php } ?>
            </div>
            <?php if($is_admin && $can_edit) { ?>
            <div id="reply_edit_form_<?=$reply['inq_id']?>" style="display: none; margin-top: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                <form class="frm_edit_reply" data-reply-id="<?=$reply['inq_id']?>">
                    <input type="hidden" name="reply_id" value="<?=$reply['inq_id']?>">
                    <input type="hidden" name="inq_id" value="<?=$inq_id?>">
                    <div style="margin-bottom: 10px;">
                        <label><strong>답변 내용 수정:</strong></label>
                        <textarea name="inq_reply_content" rows="5" class="frm_input" style="width: 100%;" required><?=!empty($reply['inq_content']) ? htmlspecialchars($reply['inq_content']) : ''?></textarea>
                    </div>
                    <div style="text-align: right;">
                        <button type="button" class="btn_cancel_edit btn_02 btn" data-reply-id="<?=$reply['inq_id']?>" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;">취소</button>
                        <button type="submit" class="btn_submit btn" style="padding: 4px 8px; font-size: 12px;">수정 저장</button>
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>
        <?php
            }
        } else {
        ?>
        <div class="empty_table" style="padding: 20px; text-align: center; color: #999;">
            아직 답변이 없습니다.
        </div>
        <?php
        }
        ?>
    </div>
</section>

<?php include_once('./js/shop_qa_form.js.php'); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');

