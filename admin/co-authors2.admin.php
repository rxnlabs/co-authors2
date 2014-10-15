<?php
if( !class_exists('CoAuthors2Admin') ){

  /**
   * Functionality for plugin to be executed while logged into the WordPress dashboard.
   */
  class CoAuthors2Admin extends CoAuthors2{

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
    public $debug = false;

    /**
     * Post types where we won't show the authors metabox.
     * 
     * @var array
     */
    public $filtered_cpt = array( 'acf' );

    public function __construct(){
      if( $this->debug  && !class_exists('Kint') ){
        require plugin_dir_path(__DIR__).'lib/vendor/autoload.php';
      }
      $this->user_roles = maybe_unserialize( get_option( '_'.$this->prefix.'_role_filter', array( 'author', 'administrator', 'editor', 'contributor' ) ) );
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
      add_filter( 'plugin_action_links_co-authors2/co-authors2.php', array( &$this, 'add_settings_link') );
      add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
      add_action( 'admin_init', array( &$this, 'save_settings' ) );
      add_action( 'add_meta_boxes', array( &$this, 'create_metaboxes' ) );
      add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) );
      add_action( 'save_post', array( &$this, 'save_coauthors' ) );
      add_filter( 'manage_posts_columns', array( &$this, 'custom_post_columns') );
      add_action( 'manage_posts_custom_column', array( &$this, 'custom_post_columns_content'), 10, 2 );
    }

    /**
     * Enqueue scripts for the dashboard
     * 
     * @return void
     */
    public function admin_enqueue(){
      global $pagenow;
      global $wp_scripts;
      wp_register_script( 'typeahead.js', plugin_dir_url(__FILE__).'js/vendor/typeahead.js/dist/typeahead.bundle.min.js', array('jquery'), '0.10.5' );
       wp_register_script( $this->prefix.'-admin', plugin_dir_url(__FILE__).'js/co-authors2.admin.js', array('jquery'), $this->version );
       wp_register_style( 'typeahead.js',  plugin_dir_url(__FILE__).'css/typeahead.css', '', '1.0a' );
       wp_register_style( $this->prefix.'-admin', plugin_dir_url(__FILE__).'css/co-authors2.admin.css', '', $this->version );
       wp_enqueue_style( $this->prefix.'-admin' );

      if( in_array($pagenow,array('post.php','post-new.php')) ){
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'typeahead.js' );
        wp_localize_script( $this->prefix.'-admin', $this->prefix, array( 
          'users' => json_encode($this->get_matched_users())
          ));
        wp_enqueue_script( $this->prefix.'-admin' );
        wp_enqueue_style( 'typeahead.js' );
      }
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
        if( !in_array($type,$this->filtered_cpt) && post_type_supports( $type,'author') )
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
      $settings_link = array( '<a href="'.admin_url('options-general.php?page='.$this->prefix.'-settings.php').'">'.__('Settings').'</a>' );
      
      return array_merge($links, $settings_link);
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
        !wp_verify_nonce( $_POST[$this->prefix.'_save_settings'], $co_authors2_admin->prefix.'_settings' ) && 
        isset($_POST[$this->prefix.'_save']) ){

          // if user can't manage options
          if( !current_user_can( 'manage_options' ) ) return;

          // set the filtered roles
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

      if( isset($_POST) &&
        !empty($_POST) &&
        array_filter($_POST) != false &&
        is_admin() && 
        !wp_verify_nonce( $_POST[$this->prefix.'_save_import'], $co_authors2_admin->prefix.'_import' ) && 
        isset($_POST[$this->prefix.'_import_co_authors_plus']) ){
        $this->import_co_authors_plus();
      }
    }
      

    /**
     * Import authors from the Co-Authors Plus plugin.
     * 
     * @return void
     */
    public function import_co_authors_plus( $echo = false ){

      // flag to set whether we've already imported authors from the Co-Authors Plus plugin
      $already_imported_coauthors_plus = (int)get_option( '_'.$this->prefix.'_imported_coauthrsplus', 0 );

      if( $already_imported_coauthors_plus != 1 ){  
        define('WP_MEMORY_LIMIT','512');

        // look for all posts that have the coauthors-plus term
        $post_types = get_post_types( array(
          'public'=>true,
          'publicly_queryable'=>true
          ), 'names' );

        foreach( $post_types as $post_type ){
          // check if the post type supports an author
          if( !post_type_supports( $post_type,'author') || in_array($post_type, $this->filtered_cpt) )
            continue;

          $posts = get_posts(array(
            'posts_per_page'=>-1,
            'fields'=>'ids'
            ));

          $co_authors_plus = is_plugin_active( 'co-authors-plus/co-authors-plus.php' );

          $count = 0;
          foreach( $posts as $single_post ){
            $coauthors = array();
            // import from the co-authors-plus plugin
            if( $co_authors_plus ){
              global $coauthors_plus;

              $authors = get_coauthors( $single_post );

              foreach( $authors as $author ){
                $coauthors[] = $author->ID;
              }

              if( count($coauthors) == 0 )
                $coauthors[] = $single_post->post_author;

            }else{
              $coauthors[] = $single_post->post_author;
            }

            // make sure this post doesn't already have the co-authors2 meta data before overwriting it
            $already_has_co2_authors = get_post_meta( $single_post, '_'.$this->prefix.'_post_authors', true );

            if( empty($already_has_co2_authors) ){
              delete_post_meta( $single_post, '_'.$this->prefix.'_post_authors' );
              update_post_meta( $single_post, '_'.$this->prefix.'_post_authors', $coauthors, true );
              if( $echo ) echo "Imported authors for post $single_post\n";
            }else{
              if( $echo ) echo "Co-Authors already set for post $single_post\n";
            }

            $count++;
          }
        }

        update_option( '_'.$this->prefix.'_imported_coauthrsplus', 1 );
        if( $echo ) echo "Finished importing post authors to Co-Authors2 plugin\n";
        if( $echo ) echo "Imported post authors for $count posts\n";
      }else{
        if( $echo ) echo "Already imported post authors to Co-Authors2 plugin\n";

      }

    }

    /**
     * Show admin notice after updating plugin settings.
     *
     * @return void
     */
    public function admin_notices(){
      if( $_GET['page'] === $this->prefix.'-settings.php' && !empty($_POST) ){
        if( isset($_POST[$this->prefix.'_import_co_authors_plus']) ){
          echo sprintf( '<div class="updated"><p>%s</p></div>', __( 'Imported Authors From Co-Authurs Plus plugin') );
        }

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

      return (!empty($roles)?$roles:'');
    }

    /**
     * Custom metabox to select the post author.
     * 
     * Show the custom metabox used to select multiple authors for the post
     * 
     * @return void
     */
    public function custom_metabox(){
      global $wp_scripts;
      wp_nonce_field( basename( __FILE__ ), $this->prefix.'_select_author' );
      if( get_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors', true ) ){
        $ca2_authors = get_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors', true );
      }else{
        $ca2_authors = '';
      }

      echo '<p>'.__( 'Select post authors by typing an author\'s name in the textbox below' ).'</p>'; 
      echo '<div id="'.$this->prefix.'_search_authors"><input class="typeahead" type="text" placeholder="'.__('Add Author').'">';

      // if there are already authors assigned to the post, list them in the metabox
      if( !empty($ca2_authors) ){
        foreach( $ca2_authors as $author ){
          $data = get_userdata( $author );
          echo '<p>'.$data->display_name.'<input type="hidden" value="'.$author.'" name="'.$this->prefix.'_post_authors[]"> <a class="ca2_remove_author">Remove</a></p>';
        }
      }else{
        $data = get_userdata( wp_get_current_user()->ID );
        echo '<p>'.$data->display_name.'<input type="hidden" value="'.$author.'" name="'.$this->prefix.'_post_authors[]"> <a class="ca2_remove_author">Remove</a></p>';
      }

      echo '</div>';
    }

    /**
     * Save the coauthors for the post.
     * 
     * @return void
     */
    public function save_coauthors(){
      global $post;
      if( defined( 'DOING_AJAX' ) && DOING_AJAX )
        return false;

      if( !isset( $_POST[$this->prefix.'_post_authors'] ) && !isset( $_POST[$this->prefix.'_select_author'] ) || !wp_verify_nonce( $_POST[$this->prefix.'_select_author'], basename( __FILE__ ) ) )
        return false;

      $authors = array();
      if( !empty($_POST[$this->prefix.'_post_authors']) ){
        foreach( $_POST[$this->prefix.'_post_authors'] as $author )
          $authors[] = esc_attr($author);
      }else{
        if( empty( $_POST['post_author_override'] ) )
          $authors[] = wp_get_current_user()->ID;
        else
          $authors[] = esc_attr( $_POST['post_author_override'] );
      }

      do_action('ca2_save_post_authors', get_the_ID(), $authors );

      $authors = array_unique($authors);

      // if the user who wrote the post is NOT one of the co-authors, make the post's actual author the first author selected. This way the wrong author won't show up in the post if plugin is removed
      if( !in_array(wp_get_current_user()->ID,$authors) ){
        $post->post_author = $authors[0];
        wp_update_post( $post );
      }

      delete_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors' );
      update_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors', $authors, true );

      $find_publication = get_posts(array(
          'post_type'=>'af_product',
          'meta_query'=>array(
              array(
                'key'=>'pubcode',
                'value'=>esc_attr( $_POST['_pubcode'] )
              )
            ),
          'posts_per_page'=>-1,
          'post_status'=>'any'
          ));

      if( !empty($find_publication) ){
        foreach($find_publication as $pub){
          
          $publication_contributors = get_post_meta($pub->ID,'_pub_contributors', true);

          if( empty($publication_contributors) ){
            $publication_contributors = $authors;
          }else{
            $publication_contributors = array_merge($publication_contributors,$authors);
          }

          $publication_contributors = array_unique($publication_contributors);

          update_post_meta( $pub->ID, '_pub_contributors', $publication_contributors );
        }
      }
    }

    /**
     * Get the users that match the roles selected.
     * 
     * Get all users that match the roles that are allowed to be co-authors. These are the users displayed in the dropdown when selecting an author.
     * 
     * @return array An associative array of matched users with the user ID as the key and the display name as the value
     */
    public function get_matched_users(){
      $matched_users = array();
      // get all users who match the role(s) selected
      foreach( $this->user_roles as $role ){
        $users = get_users(array(
          'role'=>$role,
          'blog_id'=>$GLOBALS['blog_id'],
          'fields'=>array( 'ID', 'display_name' )
          ));

        if( !empty($users) ){
          foreach( $users as $user ){
            $matched_users[] = array('user_id'=>$user->ID,'user_name'=>$user->display_name);
          }
        }
      }

      return (!empty($matched_users)?$matched_users:'');
    }

    /**
     * Add the co-authors2 column to the posts page.
     * 
     * @return array Column names to display
     */
    public function custom_post_columns($defaults){
      $defaults[$this->prefix.'_column'] = __('Authors');
      unset($defaults['author']);
      return $defaults;
    }

    /**
     * Show the content to be used when displaying the co-authors column.
     * 
     * Show the co-authors on the WordPress posts page.
     * 
     * @return void
     */
    public function custom_post_columns_content( $column_name, $post_id ){

      if( $column_name == $this->prefix.'_column' ){
      
        $ca2_authors = get_post_meta( $post_id, '_'.$this->prefix.'_post_authors', true );
        $authors = array();
        // if there are already authors assigned to the post, list them in the metabox
        if( !empty($ca2_authors) ){
          foreach( $ca2_authors as $author ){
            $data = get_userdata( $author );
            $authors[] = '<a href="edit.php?author_name='.$data->user_login.'">'.$data->display_name.'</a>';
          }
        }

        if( !empty($authors[0]) )
          echo implode(', ',$authors);
      }

    }

  }

}