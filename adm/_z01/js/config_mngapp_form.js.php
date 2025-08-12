<?php
$update_file = (is_file($g5['file_name'].'_update.php')) ? './'.$g5['file_name'].'_update.php' : './config_form_update.php';
?>
<script>
let update_file = '<?=$update_file?>';
// $('#file_afavicon').MultiFile({
//     max:1,
//     accept:'jpg|jpeg|png|gif',
// });
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