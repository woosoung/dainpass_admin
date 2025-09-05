<!-- Utility Class (외부파일로 인쿠르드 불가능하다, 이렇게 동일한 파일내에서 정의해야 한다.) -->
<!-- include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php'); -->
<!-- tailwindcss custom정의 -->
<script>
const brightbg = '#f6f6f6';
const brightbgfont = '#555555';
const normalbg = '#efefef';
const normalbgfont = '#333333';
const mainbg = '#92a2ee';
const mainbgfont = '#ffffff';
const darkbg = '#555555';
const darkbgfont = '#aaaaaa';
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                
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
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base{

}

@layer components{
    .ca-ul {
        @apply [&>li]:border [&>li]:border-gray-200 [&>li]:w-[400px] [&>li]:p-2 [&>li]:my-1;
    }
    .ca-ul > li {
        @apply relative flex justify-start items-center gap-3 cursor-move bg-white [&>.fa-times]:ml-auto [&>.fa-times]:my-auto [&>.fa-times]:cursor-pointer [&>.fa-times]:text-red-700;
    }
    .ca-ul > li > .sp_sort{
        @apply flex justify-center items-center inline-block w-[18px] h-[18px] pt-[1px] text-white font-bold border border-mainbg bg-mainbg rounded-full;
    }
}

@layer utilities {
    .mm-btn { /* 관리자단 컨텐츠영역에서 사용하는 메인버튼 */
        @apply relative inline-block bg-mainbg leading-[35px] h-[35px] px-4 rounded-sm;
    }
    .mm-blue-btn {
        @apply mm-btn bg-blue-500 !text-white;
    }
    
}
</style>