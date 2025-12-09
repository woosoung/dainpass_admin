<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (is_file(G5_Z_PATH . '/js/tailwind.min.js')) add_javascript('<script src="' . G5_Z_URL . '/js/tailwind.min.js"></script>', 0);
include_once(G5_THEME_PATH . '/head.php');
?>

<!-- 로그인 시작 { -->
<div id="mb_login" class="mbskin">
    <div class="max-w-md p-4 mx-auto sm:p-6">

        <!-- 헤더: 타이틀 & 회원가입 링크 -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">로그인</h1>
        </div>

        <!-- 안내 메시지 -->
        <div class="p-4 mb-6 border-l-4 border-blue-500 rounded bg-blue-50">
            <p class="text-sm text-blue-700">
                가맹점 회원 로그인
            </p>
        </div>

        <!-- 로그인 폼 -->
        <form name="flogin" action="<?php echo $login_action_url ?>"
            onsubmit="return flogin_submit(this);" method="post">
            <input type="hidden" name="url" value="<?php echo $login_url ?>">

            <!-- 아이디 입력 -->
            <div class="mb-4">
                <label for="login_id" class="block mb-2 text-sm font-semibold text-gray-700">
                    아이디
                </label>
                <input type="text" name="mb_id" id="login_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="아이디">
            </div>

            <!-- 비밀번호 입력 -->
            <div class="mb-4">
                <label for="login_pw" class="block mb-2 text-sm font-semibold text-gray-700">
                    비밀번호
                </label>
                <input type="password" name="mb_password" id="login_pw" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="비밀번호">
            </div>

            <!-- 자동로그인 & ID/PW 찾기 -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_login" id="login_auto_login"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="login_auto_login" class="ml-2 text-sm text-gray-700">
                        자동로그인
                    </label>
                </div>
                <div class="flex gap-4">
                    <a href="<?php echo G5_BBS_URL ?>/password_lost.php"
                        class="text-sm text-blue-600 transition hover:text-blue-800 hover:underline">
                        ID/PW 찾기
                    </a>
                    <a href="<?php echo G5_BBS_URL ?>/register.php"
                        class="text-sm text-blue-600 transition hover:text-blue-800 hover:underline">
                        회원가입
                    </a>
                </div>
            </div>

            <!-- 로그인 버튼 -->
            <button type="submit"
                class="w-full px-6 py-4 mb-4 text-lg font-semibold text-white transition duration-200 bg-blue-600 rounded-lg hover:bg-blue-700">
                로그인
            </button>
        </form>

        <!-- 소셜 로그인 -->
        <?php @include_once(get_social_skin_path() . '/social_login.skin.php'); ?>

        <?php // 쇼핑몰 사용시 
        ?>
        <?php if (isset($default['de_level_sell']) && $default['de_level_sell'] == 1) { ?>

            <!-- 비회원 구매 섹션 -->
            <?php if (preg_match("/orderform.php/", $url)) { ?>
                <div class="mt-8">
                    <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b-2 border-gray-200">
                        비회원 구매
                    </h2>
                    <p class="mb-4 text-sm text-gray-600">
                        비회원으로 주문하시는 경우 포인트는 지급하지 않습니다.
                    </p>

                    <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50">
                        <?php echo conv_content($default['de_guest_privacy'], $config['cf_editor']); ?>
                    </div>

                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="agree" value="1"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="agree" class="ml-2 text-sm text-gray-700">
                            개인정보수집에 대한 내용을 읽었으며 이에 동의합니다.
                        </label>
                    </div>

                    <button type="button" onclick="guest_submit(document.flogin);"
                        class="w-full px-6 py-4 text-lg font-semibold text-white transition duration-200 bg-blue-600 rounded-lg hover:bg-blue-700">
                        비회원으로 구매하기
                    </button>
                </div>

                <script>
                    function guest_submit(f) {
                        if (document.getElementById('agree')) {
                            if (!document.getElementById('agree').checked) {
                                alert("개인정보수집에 대한 내용을 읽고 이에 동의하셔야 합니다.");
                                return;
                            }
                        }

                        f.url.value = "<?php echo $url; ?>";
                        f.action = "<?php echo $url; ?>";
                        f.submit();
                    }
                </script>
            <?php } else if (preg_match("/orderinquiry.php$/", $url)) { ?>
                <!-- 비회원 주문조회 섹션 -->
                <div class="mt-8">
                    <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b-2 border-gray-200">
                        비회원 주문조회
                    </h2>

                    <form name="forderinquiry" method="post" action="<?php echo urldecode($url); ?>" autocomplete="off">
                        <div class="mb-4">
                            <label for="od_id" class="block mb-2 text-sm font-semibold text-gray-700">
                                주문서번호
                            </label>
                            <input type="text" name="od_id" value="<?php echo get_text($od_id); ?>" id="od_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="주문서번호">
                        </div>

                        <div class="mb-4">
                            <label for="od_pwd" class="block mb-2 text-sm font-semibold text-gray-700">
                                비밀번호
                            </label>
                            <input type="password" name="od_pwd" id="od_pwd" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="비밀번호">
                        </div>

                        <button type="submit"
                            class="w-full px-6 py-4 text-lg font-semibold text-white transition duration-200 bg-blue-600 rounded-lg hover:bg-blue-700">
                            확인
                        </button>
                    </form>

                    <div class="p-4 mt-4 border-l-4 border-blue-500 rounded bg-blue-50">
                        <p class="text-sm text-blue-700">
                            메일로 발송해드린 주문서의 <strong>주문번호</strong> 및 주문 시 입력하신 <strong>비밀번호</strong>를 정확히 입력해주십시오.
                        </p>
                    </div>
                </div>
            <?php } ?>

        <?php } ?>
    </div>
</div>

<script>
    jQuery(function($) {
        $("#login_auto_login").click(function() {
            if (this.checked) {
                this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
            }
        });
    });

    function flogin_submit(f) {
        if ($(document.body).triggerHandler('login_sumit', [f, 'flogin']) !== false) {
            return true;
        }
        return false;
    }
</script>
<!-- } 로그인 끝 -->