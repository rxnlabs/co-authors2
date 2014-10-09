<?php
global $co_authors2_admin;
?>
<div class="wrap" id="<?php _e($co_authors2_admin->prefix);?>_settings_page">
  <h2><?php _e( 'Co-Authors2' );?></h2>

  <form method="post" action="options-general.php?page=<?php echo $co_authors2_admin->prefix;?>-settings.php" id="<?php _e($co_authors2_admin->prefix);?>_settings_form">
    <h3><?php _e( 'Filter authors by role(s)' );?></h3>
    <p><?php _e( 'When selecting a post author, only show users with these roles');?>.
    <?php 
    if( is_array($co_authors2_admin->get_roles('all')) ){
      foreach( $co_authors2_admin->get_roles('all') as $key=>$role ){
        if( in_array($key,$co_authors2_admin->user_roles) )
          $filtered = 'checked';
        else
          $filtered = '';

        echo '<p><input value="'.$key.'" type="checkbox" name="'.$co_authors2_admin->prefix.'_role_filter[]" id="'.$key.'_role_filter" '.$filtered.'><label for="'.$key.'_role_filter">'.$role.'</label></p>';
      }
    }
    wp_nonce_field( $co_authors2_admin->prefix.'_save_settings', $co_authors2_admin->prefix.'_settings' );
    ?>
    <p><input name="<?php _e($co_authors2_admin->prefix);?>_save" class="button button-primary button-large" accesskey="p" value="Update Settings &raquo;" type="submit">
  </form>

  <?php if( is_plugin_active( 'co-authors-plus/co-authors-plus.php' ) ):?>
  <h3><?php _e('Looks like you have the Co-Authors Plus plugin installed. Do you want to import all authors to Co-Authors2?');?></h3>
    <form method="post" action="options-general.php?page=<?php echo $co_authors2_admin->prefix;?>-settings.php" id="<?php _e($co_authors2_admin->prefix);?>_import_form">
      <?php wp_nonce_field( $co_authors2_admin->prefix.'_save_import', $co_authors2_admin->prefix.'_import' );?>
      <p><input name="<?php _e($co_authors2_admin->prefix);?>_import_co_authors_plus" class="button button-primary button-large" accesskey="p" value="Import Co-Authors &raquo;" type="submit"></p>
    </form>
  <?php endif;?>
</div>