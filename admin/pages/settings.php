<?php
global $co_authors2_admin;
?>
<div class="wrap" id="<?php _e($co_authors2_admin->prefix);?>settings_page">
  <h2><?php _e( 'Co-Authors2' );?></h2>

  <form method="post" action="options-general.php?page=co-authors2-settings.php" id="<?php _e($co_authors2_admin->prefix);?>_settings_form">
    <h3><?php _e( 'Filter authors by role(s)' );?></h3>
    <p><?php _e( 'When selecting a post author, only show users with these roles');?>.
    <p><input name="<?php _e($co_authors2_admin->prefix);?>_dirble_access_token" value="<?php _e($co_authors2_admin->dirble_access_token);?>" class="regular-text" type="text">
    <?php wp_nonce_field( $co_authors2_admin->prefix.'_save_settings', $co_authors2_admin->prefix.'_settings' );?>
    <p><input name="<?php _e($co_authors2_admin->prefix);?>_save" class="button button-primary button-large" accesskey="p" value="Update Settings" type="submit">
  </form>
</div>