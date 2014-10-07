<?php
if( !class_exists('CoAuthors2Admin') ){

  /**
   * Functionality for plugin to be executed while logged into the WordPress dashboard.
   */
  class CoAuthors2Admin{

    /**
     * Default user role to filter when adding an author to the post.
     * 
     * @var string
     */
    public $user_role;

    /**
     * Debug flag for plugin
     * 
     * @var bool
     */
    public $debug;

    /**
     * Prefix used by plugin to save.
     * 
     * @var string
     */
    public $prefix;

    public function __construct(){
      $this->debug = true;
      if( $this->debug  && !class_exists('Kint') ){
        require plugin_dir_path(__DIR__).'lib/vendor/autoload.php';
      }
      $this->prefix = '_ca2_';
      $this->user_role = 'edit_posts';
      $this->hooks();
    }

    public function hooks(){
      register_activation_hook( __FILE__, array( &$this, 'activate' ) );
    }

    /**
     * Actions to perform when the plugin is activated
     * 
     * @return void
     */
    public function activate(){
      $this->set_options();
    }

    /**
     * Options used by plugin.
     * 
     * Options saved to WordPress' options table.
     */
    public function set_options(){
      add_option( $this->prefix.'user_filter', $this->user_role );
    }

    /**
     * Get all user roles based on their capability.
     * 
     * @link http://codex.wordpress.org/Roles_and_Capabilities
     * @param array|string $capabilities An array of capabilities you want to look for or a string of one capability you want to search for.
     * @return array An array of user roles or an empty string if no user roles found that match the capabilities specified.
     */
    public function get_roles( $capabilities = array() ){
      global $wp_roles;

      $all_roles = get_editable_roles();
      $roles = array();

      foreach( $all_roles as $role_name=>$role ){
        // by default only return user roles who can edit posts. Else, loop through the capabilities parameter to make sure user has all of the capabilities specified
        if( empty($capabilities) ){
          if( in_array('edit_posts', $role['capabilities']) ){
            $roles[] = $role_name;
          }
        }elseif( is_array($capabilities) ){
          $has_capability = false;
          foreach( $capabilities as $capability_name ){
            if( in_array($capability_name, $role['capabilities']) )
              $has_capability = true;
            else
              $has_capability = false; break;
          }

          if( $has_capability )
            $roles[] = $role_name;
        }elseif( is_string($capabilities) ){
          if( in_array($capabilities, $role['capabilities']) ){
            $roles[] = $role_name;
          }
        }
      }
      
      if( !empty($roles) )
        return $roles;
      else
        return '';
    }

  }
}