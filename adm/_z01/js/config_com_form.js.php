<script>
$('#file_preparing').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_favicon').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_ogimg').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_sitemap').MultiFile({
    max:1,
    accept:'xml',
});
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_com_form_update.php";
    return true;
}
</script>