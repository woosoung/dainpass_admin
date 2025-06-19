<script>
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>