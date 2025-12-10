<?php

require_once get_stylesheet_directory() . '/framework/functions/reservation.php';


function homey_enqueue_styles() {
    
    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');
    
    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));
}

add_action('wp_enqueue_scripts', 'homey_enqueue_styles');


