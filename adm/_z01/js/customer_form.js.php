<script>
$('#customer_profile_img').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif|svg',
});

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

function fcustomerformcheck(f)
{
    if (!f.user_id.value.trim()) {
        alert('아이디를 입력해 주세요.');
        f.user_id.focus();
        return false;
    }

    if (!f.name.value.trim()) {
        alert('이름을 입력해 주세요.');
        f.name.focus();
        return false;
    }

    if (!f.customer_key.value.trim()) {
        alert('구매자 고유 아이디를 입력해 주세요.');
        f.customer_key.focus();
        return false;
    }

    // 이메일 형식 검증
    if (f.email.value && !/^[a-z0-9_+.-]+@([a-z0-9-]+\.)+[a-z0-9]{2,4}$/i.test(f.email.value)) {
        alert('이메일 형식이 올바르지 않습니다.');
        f.email.focus();
        return false;
    }

    return true;
}
</script>