<?php
$update_file = (is_file($g5['file_name'].'_update.php')) ? './'.$g5['file_name'].'_update.php' : './config_form_update.php';
?>
<script>
let update_file = '<?=$update_file?>';
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = update_file;
    return true;
}
</script>