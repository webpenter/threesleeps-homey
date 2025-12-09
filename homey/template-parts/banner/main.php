<?php
global $post, $homey_prefix;
$post_id = isset($post->ID) ? $post->ID : '';
if(empty($post_id)) {
	return;
}
$banner_type = get_post_meta($post->ID, $homey_prefix.'header_type', true);

if( $banner_type == 'parallax' ) {
	get_template_part('template-parts/banner/parallax');

} elseif( $banner_type == 'video' ) {
	get_template_part('template-parts/banner/video');

} elseif( $banner_type == 'map' ) {
    get_template_part('template-parts/banner/map');

} elseif( $banner_type == 'experiences_map' ) {
    get_template_part('template-parts/banner/map_experiences');

} elseif( $banner_type == 'slider' ) {
    get_template_part('template-parts/banner/slider');

} elseif( $banner_type == 'experiences_slider' ) {
    
    get_template_part('template-parts/banner/slider-exp');

} elseif( $banner_type == 'rev_slider' ) {
	get_template_part('template-parts/banner/revolution-slider');

} elseif( $banner_type == 'half_search' ) {
    if(homey_is_experiences_page()){
        get_template_part('template-parts/banner/half-search-exp');
    }else{
        get_template_part('template-parts/banner/half-search');
    }

} 
?>