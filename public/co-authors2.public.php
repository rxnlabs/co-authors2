<?php
if( !class_exists('CoAuthors2Public') ){

  /**
   * Functionality for plugin to be executed on frontend of WordPress site.
   */
  class CoAuthors2Public extends CoAuthors2{
    public function __construct(){
      require 'co-authors2.public.template-functions.php';
    }

    public function hooks(){

    }
  }
}
