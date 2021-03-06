<?php
/*
Plugin Name: Co-Authors2
Plugin URI: https://github.com/AgoraFinancial/co-authors2
Description: Assign multiple authors to posts,pages, and custom post types.
Version: 1.0a
Author: De'Yonte W.
License: GNU GENERAL PUBLIC LICENSE
Copyright 2014 De'Yonte W.
 
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
  die ( 'You do not have sufficient permissions to access this page!' );
}

// Only create an instance of the plugin if it doesn't already exists in GLOBALS
if( ! array_key_exists( 'co_authors2', $GLOBALS ) ) {
  define('CO_AUTHORS2_VERSION','1.0b');

  class CoAuthors2{

    /**
     * Prefix used by plugin to save.
     * 
     * @var string
     */
    public $prefix = 'ca2';

    /**
     * Plugin version
     * 
     * @var string
     */
    public $version = CO_AUTHORS2_VERSION;

    /**
     * Plugin constructor.
     * 
     * @return void
     */
    public function __construct(){
      if( php_sapi_name() === 'cli' ){
        require 'admin/co-authors2.admin.php';
        require 'public/co-authors2.public.php';
        $GLOBALS['co_authors2_admin'] = new CoAuthors2Admin;
        $GLOBALS['co_authors2_public'] = new CoAuthors2Public;
      }else{

        if( is_admin() ){
          require 'admin/co-authors2.admin.php';
          $GLOBALS['co_authors2_admin'] = new CoAuthors2Admin;
        }else{
          require 'public/co-authors2.public.php';
          $GLOBALS['co_authors2_public'] = new CoAuthors2Public;
        }

      }

      require 'co-authors2.template-functions.php';
    }

    /**
     * Verify that user is currently in a user role.
     * 
     * Check to see if the user is in one of the roles that are allowed to be a coauthor.
     * 
     * @param int|WP_User $user_id A user ID or a WP_User instance.
     * @return bool True if the user is not part of the selected role or false if not.
     */
    /*public function is_user_in_roles( $user_id = '' ){
      require 'admin/co-authors2.admin.php';
      $co2_admin = new CoAuthors2Admin;

      if( empty($user_id) )
        wp_get_current_user()->ID;

      if( $user_id instanceof WP_User )
        $user_id = $user_id->ID;

      if( in_array($user_id, $co2_admin->get_matched_users()) )
        return true;
      else
        return false;
    }*/

  }
}

$GLOBALS['co_authors2'] = $co_authors2 = new CoAuthors2;