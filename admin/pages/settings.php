<?php
global $co_authors2_admin;
global $co_authors2;
?>
<div class="wrap" id="<?php _e($co_authors2->prefix);?>_settings_page">
  <h2><span class="dashicons dashicons-admin-users"></span> <?php _e( 'Co-Authors2 (version '.$co_authors2->version.')' );?></h2>
  <h3><?php _e('Assign multiple authors to posts, pages, and custom post types. Add 2, 4 or even more authors to a post.');?></h3>
  <form method="post" action="options-general.php?page=<?php echo $co_authors2->prefix;?>-settings.php" id="<?php _e($co_authors2->prefix);?>_settings_form">
    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="<?php echo $co_authors2->prefix;?>_roles"><?php _e( 'Filter authors by role(s)' );?></label>
        </th>
        <td>
          <p><?php _e( 'When selecting a post author, only show users who have <strong>any</strong> these roles.');?>
          <?php 
          if( is_array($co_authors2_admin->get_roles('all')) ){
            foreach( $co_authors2_admin->get_roles('all') as $key=>$role ){
              if( in_array($key,$co_authors2_admin->user_roles) )
                $filtered = 'checked';
              else
                $filtered = '';

              echo '<p><input value="'.$key.'" type="checkbox" name="'.$co_authors2->prefix.'_role_filter[]" id="'.$key.'_role_filter" '.$filtered.'><label for="'.$key.'_role_filter">'.$role.'</label></p>';
            }
          }
          ?>
        </td>
      </tr>
    </table>
    <?php wp_nonce_field( $co_authors2->prefix.'_save_settings', $co_authors2->prefix.'_settings' );?>
    <p><input name="<?php _e($co_authors2->prefix);?>_save" class="button button-primary button-large" accesskey="p" value="Save Settings &raquo;" type="submit">
  </form>

  <?php if( get_option( '_'.$co_authors2->prefix.'_imported_coauthorsplus', 0 ) == '0' ):?>
    <h3><?php _e('Do you want to import all authors to Co-Authors2?');?></h3>
    <p><?php _e('This also imports authors from the Co-Authors Plus plugin');?></p>
    <p><strong><?php _e('It could take a long time to import all authors if you have a lot of posts. This command can also be run from the PHP command line if you have ssh access to the site\'s server (this could prevent timeout errors or fatal memory errors). Proceed with caution.');?></strong></p>
    <form method="post" action="options-general.php?page=<?php echo $co_authors2->prefix;?>-settings.php" id="<?php _e($co_authors2->prefix);?>_import_form">
      <?php wp_nonce_field( $co_authors2->prefix.'_save_import', $co_authors2->prefix.'_import' );?>
      <p><input name="<?php _e($co_authors2->prefix);?>_import_co_authors_plus" class="button button-primary button-large" accesskey="p" value="Import Co-Authors &raquo;" type="submit"></p>
    </form>
  <?php endif;?>
</div>