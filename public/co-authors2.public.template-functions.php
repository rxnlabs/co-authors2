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

/**
 * Show link to co-author's pages.
 * 
 * Show link to the co-author's page where users can browse posts by author
 * 
 * @return void
 */
function coauthors2_posts_link( $post_id = '' ){
  
  if( get_coauthors2_posts_link($post_id)[0] instanceof WP_Error && ini_get('display_errors') == 1 ){
    echo get_coauthors2_posts_link($post_id)[0]->get_error_message( $coauthors[0]->get_error_code() );
  }else{
    echo get_coauthors2_posts_link($post_id); 
  }

}

/**
 * Get link to the co-author's page.
 * 
 * Get link to the author's page where users can browse posts by author
 * 
 * @return string|WP_Error HTML with links to author's page and author's display name or instance of WP_Error if something went wrong
 */
function get_coauthors2_posts_link( $post_id = '' ){
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

  $find_authors = get_coauthors2( $post_id );

  if( is_array($find_authors) ){
    foreach( $find_authors as $author ){
      $author = get_userdata($author->ID);

      if( $author != false ){
        $link = get_author_posts_url($author->ID);
        $name = $author->display_name;
        $post_type = get_post_type( $post_id );
        $post_type = get_post_type_object($post_type );

        $coauthors[] = sprintf( '<a href="%1$s" title="%2$s by %3$s">%3$s</a>', $link, ucwords($post_type->labels->name), $name );
      }
    }
  }else{
    $default_author = ( !empty($default_author)?$default_author: get_post_field( 'post_author', $post_id, 'raw' ) );
    
    if( !$default_author instanceof WP_Error ){
      $author = get_userdata($default_author);

      $link = get_author_posts_url($author->ID);
      $name = $author->display_name;
      $post_type = get_post_type( $post_id );
      $post_type = get_post_type_object($post_type );

      $coauthors[] = sprintf( '<a href="%1$s" title="%2$s by %3$s">%3$s</a>', $link, ucwords($post_type->labels->name), $name );
    }else{
      $coauthors[] = $default_author;
    }
  }

  if( count($coauthors) > 1 )
    $coauthors[count($coauthors)-1] = __('and ').end($coauthors);

  if( count($coauthors) != 0 && !$coauthors[0] instanceof WP_Error )
    return implode( ', ', $coauthors );
  elseif( $coauthors[0] instanceof WP_Error ){
    return $coauthors[0];
  }

}