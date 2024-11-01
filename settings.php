<div class="wrap">
<h1><?php _e(getUILanguageStringValue('connectionDialog.configureSettings')); ?></h1>
<?php settings_errors();?>
<form id="tansaSettingsForm" method="post" action="options.php">
    <?php
        global $settingsMenuSlugId;
        settings_fields($settingsMenuSlugId);
        do_settings_sections( $settingsMenuSlugId );
        submit_button();
    ?>
</form>
</div>
<!-- <style type="text/css">
    #tansaSettingsForm th {
        text-align: right !important;
    }
</style> -->