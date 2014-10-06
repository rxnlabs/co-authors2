<?<?php
if( !class_exists('Co-Authors2-Admin') ){

  /**
   * Functionality for plugin to be executed while logged into the WordPress dashboard.
   */
  class Co-Authors2-Admin{

    /**
     * Default user role to filter when adding an author to the post.
     * 
     * @var string
     */
    public $user_role;

    /**
     * Prefix used by plugin to save.
     * 
     * @var string
     */
    public $prefix;

    public function __construct(){
      $this->prefix = '_ca2_';
      $this->user_role = 'edit_posts';
    }

    public function hooks(){

    }

    /**
     * Options used by plugin.
     * 
     * Options saved to WordPress' options table.
     */
    public function create_options(){
      add_option( $this->prefix.'user_filter', $this->user_role, 'yes' );
    }


  }
}