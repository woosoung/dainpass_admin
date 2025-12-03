// 비밀번호 검증 함수
let reg_mb_password_check = function () {
  let result = "";
  $.ajax({
    type: "POST",
    url: g5_bbs_url + "/ajax.mb_password.php",
    data: {
      reg_mb_password: encodeURIComponent($("#reg_mb_password").val()),
    },
    cache: false,
    async: false,
    success: function (data) {
      result = data;
    },
  });
  return result;
};

// 사업자등록번호 검증 함수
let reg_business_no_check = function () {
  let result = "";
  $.ajax({
    type: "POST",
    url: g5_bbs_url + "/ajax.mb_business_no.php",
    data: {
      reg_business_no: $("#reg_business_no").val(),
    },
    cache: false,
    async: false,
    success: function (data) {
      result = data;
    },
  });
  return result;
};
