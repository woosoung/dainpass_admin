<script>
function check_all(f) {
    if (f.chkall.checked) {
        for (var i = 0; i < f.length; i++) {
            if (f[i].name == "chk[]") {
                f[i].checked = true;
            }
        }
    } else {
        for (var i = 0; i < f.length; i++) {
            if (f[i].name == "chk[]") {
                f[i].checked = false;
            }
        }
    }
}
</script>

