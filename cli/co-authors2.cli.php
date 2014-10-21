<?php

if( php_sapi_name() != 'cli' )
  die( 'The commands only run on the PHP command line interface' );

class CoAuthors2CLI{

  public static function import_coauthors(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus(true);
  }

}

//pull in wordpress
ini_set('memory_limit','1500M');
if( file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-blog-header.php') ){
  require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-blog-header.php';
}else{
  die( "Could not load WordPress. Could not find the 'wp-blog-header.php' file in WordPress's root directory. This file is needed for the import to work.\n" );
}
require '../co-authors2.php';
CoAuthors2CLI::import_coauthors();