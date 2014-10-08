<?php
if( !class_exists('CoAuthors2Admin') ){

  /**
   * Functionality for plugin to be executed while logged into the WordPress dashboard.
   */
  class CoAuthors2Admin{

    /**
     * Default user roles to filter when adding an author to the post.
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

    /**
     * Post types where we won't show the authors metabox.
     * 
     * @var array
     */
    public $filtered_cpt;

    public function __construct(){
      $this->debug = true;
      if( $this->debug  && !class_exists('Kint') ){
        require plugin_dir_path(__DIR__).'lib/vendor/autoload.php';
      }
      $this->prefix = 'ca2';
      $this->user_roles = maybe_unserialize( get_option( '_'.$this->prefix.'_role_filter', array( 'author', 'administrator', 'editor' ) ) );
      $this->filtered_cpt = array( 'acf' );
      $this->hooks();
    }

    /**
     * WordPress action hooks to attach plugin methods to.
     * 
     * @return void
     */
    public function hooks(){
      register_activation_hook( __FILE__, array( &$this, 'activate' ) );
      add_action( 'pre_user_query', array( &$this, 'get_roles' ) );
      add_action( 'admin_menu', array( &$this, 'settings_page' ) );
      add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array(&$this,'add_settings_link') );
      add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
      add_action( 'admin_init', array( &$this, 'save_settings' ) );
      add_action( 'add_meta_boxes', array( $this, 'create_metaboxes') );
      
    }

    /**
     * Create custom metaboxes used by plugin.
     * 
     * @return void
     */
    public function create_metaboxes(){
      $post_types = get_post_types( array(
        'public'=>true,
        'publicly_queryable'=>true
        ), 'names' );
      foreach( $post_types as $type ){
        if( !in_array($type,$this->filtered_cpt) )
          add_meta_box( $this->prefix.'_select_author', 'Post Authors', array( $this, 'custom_metabox' ), $type, 'normal', 'core' );
      }
    }

    /**
     * Actions to perform when the plugin is activated.
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
      update_option( '_'.$this->prefix.'_role_filter', $this->user_roles );
    }

    /**
     * Add pages to the settings menu
     * 
     * @return void
     */
    public function settings_page(){
      add_options_page( 'Co-Authors2', 'Co-Authors2', 'manage_options', $this->prefix.'-settings.php', array( &$this, 'load_settings_page' ) );
    }

    /**
     * Content for the plugin settings page
     * 
     * @return void
     */
    public function load_settings_page(){
      include plugin_dir_path( __FILE__ ).'pages/settings.php';
    }

    /**
    * Add plugin settings link to plugin page.
    *
    * @return array Array of links to include on plugin page.
    */
    public function add_settings_link($links){
      $settings_link = '<a href="options-general.php?page='.$this->prefix.'-settings.php">'.__('Settings').'</a>';
      array_unshift($links, $settings_link);
      return $links;
    }

    /**
    * Save settings in options table
    *
    * Save plugin settings in options table
    *
    * @return void
    */
    public function save_settings(){
    if( $_GET['page'] === $this->prefix.'-settings.php' ){
      if( isset($_POST) &&
        !empty($_POST) &&
        array_filter($_POST) != false &&
        is_admin() &&
        check_admin_referer($this->prefix.'_save_settings',$this->prefix.'_settings') ){
          // if user can't manage options
          if( !current_user_can( 'manage_options' ) ) return;

          $filter_roles = array();
          if( !empty($_POST[$this->prefix.'_role_filter']) ){
            foreach( $_POST[$this->prefix.'_role_filter'] as $role ){
              $filter_roles[] = esc_attr($role);
            }
          }
          $this->user_roles = $filter_roles;
          $result = update_option( '_'.$this->prefix.'_role_filter', maybe_serialize($filter_roles) );
        }
      }
    }

    /**
     * Show admin notice after updating plugin settings.
     *
     * @return void
     */
    public function admin_notices(){
      if( $_GET['page'] === $this->prefix.'-settings.php' && !empty($_POST) ){
        echo sprintf( '<div class="updated"><p>%s</p></div>', __( 'Updated Plugin Settings') );
      }
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
         
          if( array_key_exists('edit_posts', $role['capabilities']) &&  $role['capabilities']['edit_posts'] ){
            $roles[$role_name] = $role['name'];
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
            $roles[$role_name] = $role['name'];
        }elseif( is_string($capabilities) ){
          
          if( $capabilities === 'all' )
            $roles[$role_name] = $role['name'];
          elseif( in_array($capabilities, $role['capabilities']) )
            $roles[$role_name] = $role['name'];

        }
      }

      if( !empty($roles) )
        return $roles;
      else
        return '';
    }

    /**
     * Custom metabox to select the post author.
     * 
     * Show the custom metabox used to select multiple authors for teh post
     */
    public function custom_metabox(){
      wp_nonce_field( basename( __FILE__ ), $this->prefix.'_select_author' );
      if( get_post_meta( get_the_ID(), $this->prefix.'_post_author', true ) ){

      }
      echo "ahahahghaga";
    }

    
  }

}