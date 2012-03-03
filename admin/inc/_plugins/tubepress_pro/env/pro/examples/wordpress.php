<?php
/**
 * This example shows how to use TubePress Pro in a WordPress environment, but outside of a post
 * or a page. The example below shows how to use TubePress in a WordPress template. It's content
 * is copied from the "World's Simplest Index Page": http://codex.wordpress.org/The_Loop_in_Action
 * 
 * In this example, we're adding a TubePress gallery just after the call to "get_header"
 */
?>

<?php

    /* STEP 1: Include the TubePress pro library file (tubepress-pro.php) */
    require_once dirname(__FILE__) . "/../tubepress-pro.php";

    get_header();

    /* STEP 2: Include this statement anywhere you want your gallery */
    print tubepressGallery("resultsPerPage='3'");
    
    if (have_posts()) :
       while (have_posts()) :
          the_post();
          the_content();
       endwhile;
    endif;
    get_sidebar();
    get_footer(); 
?>
