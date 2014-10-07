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
  if( is_admin() ){
    require 'admin/co-authors2.admin.php';
    $GLOBALS['co_authors2_admin'] = new CoAuthors2Admin();
  }
}