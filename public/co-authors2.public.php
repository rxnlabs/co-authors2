<?php
if( !class_exists('CoAuthors2Public') ){

  /**
   * Functionality for plugin to be executed on frontend of WordPress site.
   */
  class CoAuthors2Public extends CoAuthors2{
    public function __construct(){
      $this->hooks();
    }

    /**
     * Functions to execute when WordPress hooks are called.
     * 
     * @return void
     */
    public function hooks(){
      add_filter( 'the_author', array( &$this, 'the_coauthors2' ) );
    }

    /**
     * Show the name the co-authors of the current post.
     * 
     * Filter hook for "the_author" function. Show the display names of the co-authors when the "the_author" is called.
     * 
     * @return string Display name of the post author(s)
     */
    public function the_coauthors2($author){

      $find_co_authors = get_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors', true );

      $coauthors = array();

      if( !empty($find_co_authors) ){
        foreach( $find_co_authors as $author_id ){
          // check if the author ID still exists in the database
          $is_still_active = get_userdata($author_id);

          if( $is_still_active instanceof WP_User ){
            $coauthors[] = $is_still_active->display_name;
          }
        }

        // if we have more than one author, add "and" as separator between authors names.
        if( count($coauthors) > 1 ){
          $coauthors[count($coauthors)-1] = __('and ').end($coauthors);

          $author = implode( ', ', $coauthors );

        }elseif( count($coauthors) == 1 ){
          $author = $coauthors[0];
        }
            
      }

      return $author;
    }

  }
}
