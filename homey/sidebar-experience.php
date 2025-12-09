<?php
global $post;
$pageID = isset($post->ID) ? $post->ID : '';
$sidebar_meta = homey_get_sidebar_meta($pageID);
if( is_singular('experience') ) {

    if( is_active_sidebar( 'single-experience' ) ) {
        dynamic_sidebar( 'single-experience' );
    }
} else {
    if( $sidebar_meta['homey_sidebar'] == 'yes' && !empty($sidebar_meta['selected_sidebar']) ) {
        if( is_active_sidebar( $sidebar_meta['selected_sidebar'] ) ) {
            dynamic_sidebar( $sidebar_meta['selected_sidebar'] );
        }
    } else {
        if( is_active_sidebar( 'experience-sidebar' ) ) {
            dynamic_sidebar( 'experience-sidebar' );
        }
    }
}

?>