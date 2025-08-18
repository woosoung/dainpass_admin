<?php
$update_file = (is_file($g5['file_name'].'_update.php')) ? './'.$g5['file_name'].'_update.php' : './config_form_update.php';
?>
<script>
const FAVICON_MAX_SIZE_KB = 100; // 최대 100KB
const FAVICON_MAX_SIZE_BYTES = FAVICON_MAX_SIZE_KB * 1024; // 바이트 단위로 변환
let update_file = '<?=$update_file?>';
// $('#file_preparing').MultiFile({
//     max:1,
//     accept:'jpg|jpeg|png|gif',
// });
$('#file_favicon').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif|ico|svg',
    afterFileAppend: function(element, value, master_element) {
        const files = element.files;
        for(let i = 0; i < files.length; i++){
            const file = files[i];
            // 파일크기 제한
            if(file.size > FAVICON_MAX_SIZE_BYTES){
                alert(`"${file.name}" 파일은 ${FAVICON_MAX_SIZE_KB}KB를 초과할 수 없습니다.`);
                $(element).val('');
                return false; // 파일 추가 중단
            }

            // MIME 타입 확인
            const validTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'image/png', 'image/jpeg', 'image/gif', 'image/jpg'];
            if(!validTypes.includes(file.type)){
                alert(`"${file.name}" 파일은 유효한 아이콘 형식이 아닙니다.`);
                $(element).val('');
                return false; // 파일 추가 중단
            }
        }
    },
});

$('#file_plflogo').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});

$('#file_ogimg').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
// $('#file_sitemap').MultiFile({
//     max:1,
//     accept:'xml',
// });
// alert(update_file);
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = update_file;
    return true;
}


document.addEventListener("DOMContentLoaded", function () {
  const copyIcons = document.querySelectorAll(".copy_url");

  copyIcons.forEach(icon => {
    icon.addEventListener("click", function () {
      const targetSpan = this.nextElementSibling;
      if (targetSpan && targetSpan.classList.contains("copied_url")) {
        const text = targetSpan.textContent;
        navigator.clipboard.writeText(text)
          .then(() => alert("텍스트가 복사되었습니다!"))
          .catch(err => {
            alert("복사에 실패했습니다.");
            console.error("Clipboard copy failed:", err);
          });
      }
    });
  });
});
</script>