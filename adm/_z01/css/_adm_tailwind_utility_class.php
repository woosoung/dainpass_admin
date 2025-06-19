<!-- Utility Class (외부파일로 인쿠르드 불가능하다, 이렇게 동일한 파일내에서 정의해야 한다.) -->
<!-- include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php'); -->
<!-- tailwindcss custom정의 -->
<script>
const brightbg = <?= json_encode($set_conf['set_bright_bg'] ?? '') ?>;
const brightbgfont = <?= json_encode($set_conf['set_bright_font'] ?? '') ?>;
const normalbg = <?= json_encode($set_conf['set_normal_bg'] ?? '') ?>;
const normalbgfont = <?= json_encode($set_conf['set_normal_font'] ?? '') ?>;
const mainbg = <?= json_encode($set_conf['set_main_bg'] ?? '') ?>;
const mainbgfont = <?= json_encode($set_conf['set_main_font'] ?? '') ?>;
const darkbg = <?= json_encode($set_conf['set_dark_bg'] ?? '') ?>;
const darkbgfont = <?= json_encode($set_conf['set_dark_font'] ?? '') ?>;
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                /* 키는 '-'하이픈을 사용할 수 없고, '_'언더바를 사용할 수 있다.
                sans: ['Noto Sans KR', 'Arial', 'sans-serif'],
                notosans: ['Noto Sans KR', 'sans-serif'],
                single: ["Single Day", 'cursive'],
                montserrat: ['Montserrat', 'sans-serif'],
                blackhansans: ['Black Han Sans', 'sans-serif'],
                nanumpen: ['Nanum Pen Script', 'cursive'],
                pretendard: ['Pretendard-Regular'],
                yclover: ['YClover-Bold'],
                */
            },
            colors: {
                /* 키는 '-'하이픈을 사용할 수 없고, '_'언더바를 사용할 수 있다.
                mygreen: {
                    100: '#1abc9c',
                    200: '#2ecc71',
                    300: '#16a085',
                    400: '#27ae60',
                },
                */
                brightbg: brightbg,
                brightbgfont: brightbgfont,
                normalbg: normalbg,
                normalbgfont: normalbgfont,
                mainbg: mainbg,
                mainbgfont: mainbgfont,
                darkbg: darkbg,
                darkbgfont: darkbgfont,
            },
        }
    }
}
</script>
<style type="text/tailwindcss">
@layer utilities {
    /*
    .shadow-box {
        @apply border-2 border-gray-200 shadow-lg rounded-3xl p-5 text-center text-xl;
    }
    .centering{
        @apply flex justify-center items-center;
    }
    */

    .mm-btn { /* 관리자단 컨텐츠영역에서 사용하는 메인버튼 */
        @apply inline-block bg-mainbg !text-mainbgfont leading-[35px] h-[35px] px-4 rounded-md;
    }
}
</style>