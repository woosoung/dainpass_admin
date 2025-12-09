<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// Tailwind CSS 로드
if (is_file(G5_Z_PATH . '/js/tailwind.min.js')) {
    add_javascript('<script src="' . G5_Z_URL . '/js/tailwind.min.js"></script>', 0);
}

include_once(G5_THEME_PATH . '/head.sub.php');
?>

<!-- 상단 시작 { -->
<header class="bg-white border-b border-gray-200 shadow-sm">
    <h1 id="hd_h1" class="sr-only"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container" class="sr-only"><a href="#container">본문 바로가기</a></div>

    <?php
    if (defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH . '/newwin.inc.php'; // 팝업레이어
    }
    ?>

    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- 로고 -->
            <div class="flex-shrink-0">
                <a href="<?php echo G5_URL ?>" class="flex items-center">
                    <img src="<?php echo $set_mng['mnglogo_url'] ?>"
                        alt="<?php echo $config['cf_title']; ?>"
                        class="h-8 sm:h-10">
                </a>
            </div>

            <!-- 네비게이션 링크 -->
            <nav class="flex items-center space-x-4 sm:space-x-6">
                <?php if ($is_member) { ?>
                    <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php"
                        class="text-xs text-gray-700 transition sm:text-sm hover:text-blue-600">
                        정보수정
                    </a>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php"
                        class="text-xs text-gray-700 transition sm:text-sm hover:text-blue-600">
                        로그아웃
                    </a>
                    <?php if ($is_admin) { ?>
                        <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>"
                            class="px-3 py-1.5 text-xs font-semibold text-white transition duration-200 bg-blue-600 rounded-lg sm:px-4 sm:py-2 sm:text-sm hover:bg-blue-700">
                            관리자
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/login.php"
                        class="text-xs text-gray-700 transition sm:text-sm hover:text-blue-600">
                        로그인
                    </a>
                    <a href="<?php echo G5_BBS_URL ?>/register.php"
                        class="px-3 py-1.5 text-xs font-semibold text-white transition duration-200 bg-blue-600 rounded-lg sm:px-4 sm:py-2 sm:text-sm hover:bg-blue-700">
                        회원가입
                    </a>
                <?php } ?>
            </nav>
        </div>
    </div>
</header>
<!-- } 상단 끝 -->

<!-- 콘텐츠 시작 { -->
<div class="min-h-screen bg-gray-50">
    <main id="container" class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">