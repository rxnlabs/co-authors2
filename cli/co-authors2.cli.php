<?php

if( php_sapi_name() != 'cli' )
  die( 'The co-authors2 plugin commands only run on the PHP command line interface' );

class CoAuthors2CLI{

  public function __construct(){
    $is_this_plugin_active = is_plugin_active( 'co-authors-plus/co-authors-plus.php' );
    if( !$is_this_plugin_active ){
      $activated = activate_plugin( 'co-authors2/co-authors2.php' );

      if( is_wp_error( $activated ) )
        die("Could not activate the co-authors2 plugin because ".$activated->get_error_message( $activated->get_error_code() ) ."\n");
      else
        echo "Successfully activated the co-authors2 plugin\n";
    }
  }

  public function import_coauthors(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus(true);
  }

}

// setup PHP CLI environment
ini_set('memory_limit','1500M');
// check if PHP is running on a Mac. If so, change the database host from localhost to 127.0.0.1 to prevent a MySQL connection error.
$is_mac = stripos( php_uname(), 'mac' );
if( $is_mac !== false )
  define('DB_HOST','127.0.0.1');
//pull in wordpress
if( file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-blog-header.php') ){
  require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-blog-header.php';
}else{
  die( "Could not load WordPress. Could not find the 'wp-blog-header.php' file in WordPress's root directory. This file is needed for the import to work.\n" );
}
require '../co-authors2.php';
$co_authors2_cli = new CoAuthors2CLI();
$co_authors2_cli->import_coauthors();