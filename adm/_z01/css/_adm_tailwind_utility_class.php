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
    /* 카테고리 생성리스트 관련 스타일 */
    .ca-ul {
        @apply [&>li]:border [&>li]:border-gray-200 [&>li]:w-[400px] [&>li]:p-2 [&>li]:my-1;
    }
    .ca-ul > li {
        @apply relative flex justify-start items-center gap-3 cursor-move bg-white [&>.fa-times]:ml-auto [&>.fa-times]:my-auto [&>.fa-times]:cursor-pointer [&>.fa-times]:text-red-700;
    }
    .ca-ul > li > .sp_sort{
        @apply flex justify-center items-center inline-block w-[18px] h-[18px] pt-[1px] text-white font-bold border border-mainbg bg-mainbg rounded-full;
    }
    /* 가맹점관리메뉴 선택박스 스타일 */
    .chk_list_box {
        @apply flex gap-2;
    }
    .chk_list_box > label {
        @apply mr-4 cursor-pointer;
    }

    /* config_menu_form.php에서 사용할 메뉴 컴포넌트스타일 */
    .cmf_menu_h3_class {
        @apply [&>div>div]:mb-4 [&>div>div:last-child]:mt-0 [&>div>div>h3]:py-1 [&>div>div>h3]:px-2 [&>div>div>h3]:rounded-md [&>div>div>h3]:text-blue-500;
    }
    .cmf_menu_ul_class {
        @apply [&>div>div>ul]:mt-2 [&>div>div>ul]:flex [&>div>div>ul]:gap-2 [&>div>div>ul]:flex-wrap [&>div>div>ul]:leading-[0.9rem];
    }
    .cmf_menu_li_class {
        @apply [&>div>div>ul>li]:text-nowrap [&>div>div>ul>li]:border-2 [&>div>div>ul>li]:border-orange-500 [&>div>div>ul>li]:px-2 [&>div>div>ul>li]:pt-2 [&>div>div>ul>li]:pb-1 [&>div>div>ul>li]:rounded-md [&>div>div>ul>li]:flex [&>div>div>ul>li]:items-center;
    }
    .cmf_all_hide_clear_btn {
        @apply inline-block border py-1 px-2 rounded-md bg-gray-500 text-white cursor-pointer hover:bg-gray-600;
    }
    
    /* employee_form.php에서 사용할 메뉴 컴포넌트스타일 */
    .epf_auth_renewal_label {
        @apply inline-block mb-4 cursor-pointer text-blue-800;
    }
    .epf_menu_h3_class {
        @apply [&>div>div]:mb-3 [&>div>div:last-child]:mt-0 [&>div>div>h3]:py-1 [&>div>div>h3]:text-orange-500 [&>div>div>h3]:flex [&>div>div>h3]:items-center [&>div>div>h3]:gap-2;
    }
    .epf_menu_hs_class {
        @apply [&>div>div>h3>span]:cursor-pointer [&>div>div>h3>span]:border [&>div>div>h3>span]:border-gray-500 [&>div>div>h3>span]:pt-[2px] [&>div>div>h3>span]:px-[5px] [&>div>div>h3>span]:text-[0.8rem] [&>div>div>h3>span]:relative [&>div>div>h3>span]:top-[-2px] [&>div>div>h3>span]:rounded-md [&>div>div>h3>span]:bg-gray-500 [&>div>div>h3>span:hover]:bg-gray-600 [&>div>div>h3>span]:text-white [&>div>div>h3>span:last-child]:border-red-500 [&>div>div>h3>span:last-child]:bg-red-500 [&>div>div>h3>span:last-child:hover]:bg-red-600;
    }
    .epf_menu_ul_class {
        @apply [&>div>div>ul]:flex [&>div>div>ul]:gap-2 [&>div>div>ul]:flex-wrap [&>div>div>ul]:leading-[0.9rem];
    }
    .epf_menu_li_class {
        @apply [&>div>div>ul>li]:text-nowrap [&>div>div>ul>li]:border-2 [&>div>div>ul>li]:px-2 [&>div>div>ul>li]:pt-2 [&>div>div>ul>li]:pb-1 [&>div>div>ul>li]:rounded-md [&>div>div>ul>li]:flex [&>div>div>ul>li]:items-center;
    }
    .epf_menu_sp_class {
        @apply [&>div>div>ul>li>span]:ml-[3px] [&>div>div>ul>li>span]:border [&>div>div>ul>li>span]:border-gray-300 [&>div>div>ul>li>span]:px-2 [&>div>div>ul>li>span]:pt-1 [&>div>div>ul>li>span]:relative [&>div>div>ul>li>span]:top-[-2px] [&>div>div>ul>li>span]:rounded-md;
    }
    .epf_all_auth_del_btn {
        @apply inline-block border py-1 px-2 rounded-md bg-gray-500 text-white cursor-pointer hover:bg-gray-600;
    }
    /* 가맹점관리 가맹점이미지리스트 */
    #branch_imgs {
        @apply [&>li]:border [&>li]:border-gray-200 [&>li]:p-2 [&>li]:relative [&>li]:pl-[140px] [&>li]:bg-white;
    }
    #branch_imgs > li > .sp_thumb {
        @apply absolute left-2 top-2 border border-gray-300;
    }
}

@layer utilities {
    .border_test { /* 테두리 테스트 */
        @apply border border-red-500;
    }
    .mm-btn { /* 관리자단 컨텐츠영역에서 사용하는 메인버튼 */
        @apply relative inline-block bg-mainbg leading-[35px] h-[35px] px-4 rounded-sm;
    }
    .mm-blue-btn {
        @apply mm-btn bg-blue-500 !text-white;
    }
}
</style>