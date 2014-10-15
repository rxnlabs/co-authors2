<?php

if( php_sapi_name() != 'cli' )
  die( 'Only run commands from the PHP CLI' );

class CoAuthors2Import{

  public static function import(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus(true);
  }

  public static function relate_authors_to_pubs(){
    $relate = get_posts( array(
      'post_type'=>'post',
      'posts_per_page'=>-1,
      'post_status'=>'any'
      ) );

    foreach( $relate as $post ){
      $has_coauthors = get_post_meta( $post->ID, '_'.$GLOBALS['co_authors2']->prefix.'_post_authors', true );
      $publication = get_post_meta( $post->ID, '_pubcode', true );

      if( empty($has_coauthors) ){
        $has_coauthors = array();
        $has_coauthors[] = $post->post_author;
        delete_post_meta( $post->ID,'_'.$GLOBALS['co_authors2']->prefix.'_post_authors' );
        update_post_meta( $post->ID,'_'.$GLOBALS['co_authors2']->prefix.'_post_authors', $has_coauthors );
        echo "Added post authors to {$post->ID}\n";
      }

      if( !empty($publication) ){
        $test = implode(', ', $has_coauthors);
        echo "Post {$post->ID} part of pub $publication with co-authors $test\n";
        $find_publication = get_posts(array(
          'post_type'=>'af_product',
          'meta_query'=>array(
              array(
                'key'=>'pubcode',
                'value'=>$publication
              )
            ),
          'posts_per_page'=>-1,
          'post_status'=>'any'
          ));

        if( !empty($find_publication) ){
          foreach($find_publication as $pub){
            
            $publication_contributors = get_post_meta($pub->ID,'_pub_contributors', true);

            if( empty($publication_contributors) ){
              $publication_contributors = $has_coauthors;
            }else{
              $publication_contributors = array_merge($publication_contributors,$has_coauthors);
            }

            $publication_contributors = array_unique($publication_contributors);

            update_post_meta( $pub->ID, '_pub_contributors', $publication_contributors );

            echo "Added authors to publication {$pub->post_title}\n";
          }
        }
      }
    }

  }

}

//pull in wordpress
ini_set('memory_limit','1500M');
define('DB_HOST','127.0.0.1');
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-load.php';
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wp-admin/includes/plugin.php';
require '../co-authors2.php';
CoAuthors2Import::relate_authors_to_pubs();