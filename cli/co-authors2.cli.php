<?php

if( php_sapi_name() != 'cli' )
  die( 'Only run commands from the PHP CLI' );

class CoAuthors2Import{

  public function __construct(){
    require '../co-authors2.php';
  }

  public static function import(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus();
  }
}

//pull in wordpress
define('DB_HOST','127.0.0.1');
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-blog-header.php';
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-admin/includes/plugin.php';
CoAuthors2Import::import();