<?php
/**
 * Template Name: Search Results for Experiences
 */
get_header();
global $post, $wp_query, $paged, $listing_founds, $number_of_listings;

$search_result_page_exp = homey_option('search_result_page_exp');

if($search_result_page_exp == 'half_map') {
    get_template_part('template-parts/half_map_for_exp');

} elseif ($search_result_page_exp == 'half_map_right') {
    get_template_part('template-parts/half_map_for_exp_right');

} else {
    get_template_part('template-parts/normal_page_for_exp');
}

get_footer(); 
?>