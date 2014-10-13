<?php

if( php_sapi_name() != 'cli' )
  die( 'Only run commands from the PHP CLI' );

class CoAuthors2Import{

  public static function import(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus(true);
  }
}

//pull in wordpress
ini_set('memory_limit','1500M');
define('DB_HOST','127.0.0.1');
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-load.php';
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-admin/includes/plugin.php';
require '../co-authors2.php';
CoAuthors2Import::import();