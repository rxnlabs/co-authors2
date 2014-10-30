<?php
if( !class_exists('CoAuthors2Public') ){

  /**
   * Functionality for plugin to be executed on frontend of WordPress site.
   */
  class CoAuthors2Public extends CoAuthors2{
    public function __construct(){
      $this->hooks();
    }

    public function hooks(){
      add_filter( 'the_author', array( &$this, 'the_coauthors2' ) );
    }

    /**
     * Show the name the co-authors of the current post.
     * 
     * @return void
     */
    public function the_coauthors2($author){

      $find_co_authors = get_post_meta( get_the_ID(), '_'.$this->prefix.'_post_authors', true );

      if( !empty($find_co_authors) ){
        foreach( $find_co_authors as $author_id ){
          $is_still_active = get_userdata($author_id);

          if( $is_still_active != false ){
            $coauthors[] = $is_still_active->display_name;
          }
        }

        if( count($coauthors) > 1 )
          $coauthors[count($coauthors)-1] = __('and ').end($coauthors);

        if( count($coauthors) != 0 && !$coauthors[0] instanceof WP_Error )
          $author = implode( ', ', $coauthors );
      }

      return $author;
    }
  }
}
