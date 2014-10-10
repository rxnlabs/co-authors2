<?php
/**
 * Get the co-authors of a post.
 * 
 * Get the co-authors of a post. If there are no co-authors, grab the post author.
 * 
 * @return array|string An array of author IDs or empty string if no co-authors are found.
 */
function get_coauthors2( $post_id = '' ){
  global $post;
  global $co_authors2;

  $coauthors = array();
  $default_author = '';

  if( empty($post_id ) )
    $post_id = $post->ID;

  if( is_string($post_id) || is_numeric($post_id) ){
    $post_id = $post_id;
  }elseif( $post_id instanceof WP_Post ){
    $default_author = $post->post_author;
    $post_id = $post_id->ID;
  }

  $find_authors = get_post_meta( $post_id, '_'.$co_authors2->prefix.'_post_authors', true );

  if( is_array($find_authors) ){
    foreach( $find_authors as $author_id ){
      $author = get_userdata($author_id);

      if( $author != false )
        $coauthors[] = $author;
    }
  }else{
    $coauthors[] = ( !empty($default_author)?$default_author: get_post_field( 'post_author', $post_id, 'raw' ) );
  }

  if( count($coauthors) != 0 && !$coauthors[0] instanceof WP_Error )
    return $coauthors;
  elseif( $coauthors[0] instanceof WP_Error )
    return $coauthors[0];
  else
    return;
}