<?php

if( php_sapi_name() != 'cli' )
  die( 'Only run commands from the PHP CLI' );

class CoAuthors2Import{

  public static function import(){
    echo $GLOBALS['co_authors2_admin']->import_co_authors_plus(true);
  }

  public static function relate_authors_to_pubs(){

    $all_publications = get_posts(array(
      'post_type'=>'af_product',
      'posts_per_page'=>-1,
      'post_status'=>'any'
      ));

    foreach( $all_publications as $publication ){
      $has_editors = get_post_meta($publication->ID,'_pub_contributors',true);

      if( !empty($has_editors) ){
        delete_post_meta( $publication->ID, '_pub_contributors' );
        echo "Deleted contributors for {$publication->post_title}\n";
      }
    }

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
      }

      // loop through each co-authors to make sure they belong to any of the user roles allowed to be a co-author
      $verified_authors = array();
      foreach( $has_coauthors as $author ){
        if( user_can( $author, 'edit_posts' ) )
          $verified_authors[] = $author;
      }

      delete_post_meta( $post->ID,'_'.$GLOBALS['co_authors2']->prefix.'_post_authors' );
      update_post_meta( $post->ID,'_'.$GLOBALS['co_authors2']->prefix.'_post_authors', $verified_authors );

      $test = implode(', ', $verified_authors);
      echo "Added and verified co-authors for post {$post->ID}. Co-Authors are $test\n";

      if( !empty($publication) ){
        $test = implode(', ', $verified_authors);
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
            $active_authors = get_post_meta($pub->ID,'_emeritus', true);

            if( empty($publication_contributors) ){
              $publication_contributors = $verified_authors;
            }else{
              $publication_contributors = array_merge($publication_contributors,$verified_authors);
            }

            $publication_contributors = array_merge($publication_contributors,$active_authors);

            $publication_contributors = array_unique($publication_contributors);

            update_post_meta( $pub->ID, '_pub_contributors', $publication_contributors );

            echo "Added authors $test to publication {$pub->post_title}\n";
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