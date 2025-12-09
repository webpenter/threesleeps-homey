<?php
/**
 * Template Name: Experience Half Map
 */
global $post, $wp_query, $homey_prefix, $paged, $booking_type;

$page_id = $post->ID;
$zoom_level = homey_option('halfmap_zoom_level');
$halfmap_layout = homey_option('experience_halfmap_posts_layout');
$halfmap_default_order = get_post_meta( $post->ID, 'homey_experiences_sort', true );
$number_of_experiences = get_post_meta( $post->ID, 'homey_experiences_num', true );
$types = get_post_meta( $page_id, 'homey_types_exp', false );
$booking_type = get_post_meta( $page_id, 'homey_halfmap_booking_type', true );

if(!empty($number_of_experiences)) {
    $halfmap_num_posts  = $number_of_experiences;
} else {
    $halfmap_num_posts = 9;
}

get_header(); 
?>

<section class="half-map-wrap map-on-left clearfix">
        
        <div class="half-map-right-wrap">
            <div id="homey-halfmap" 
                data-zoom="<?php echo intval($zoom_level); ?>"
                data-layout="<?php echo esc_attr($halfmap_layout); ?>"
                data-num-posts="<?php echo esc_attr($halfmap_num_posts); ?>"
                data-order="<?php echo esc_attr($halfmap_default_order); ?>"
                data-type="<?php homey_array_to_comma_string($types); ?>"
                data-booking_type="<?php echo esc_attr($booking_type); ?>"
            >
            </div>
            <?php get_template_part('template-parts/map-controls-exp'); ?>
        </div><!-- .half-map-right-wrap -->

        <div class="half-map-left-wrap homey-matchHeight-needed">
            <div class="half-map-left-inner-wrap">
                <?php get_template_part('template-parts/search/search-half-map-exp'); ?>
                <?php get_template_part('template-parts/experience/sort-tool_2'); ?>

                <div id="homey_halfmap_experiences_container" class="listing-wrap which-layout-<?php echo $halfmap_layout;?> <?php if(str_contains($halfmap_layout, '2')){ echo 'item-'.grid_list_or_card($halfmap_layout, 1).'-view'; } ?> item-<?php echo esc_attr($halfmap_layout); ?>-view">
                </div><!-- grid-experience-page -->
            </div><!-- .half-map-left-inner-wrap -->
        </div><!-- .half-map-left-wrap -->
        
    </section><!-- .half-map-wrap -->


<?php get_footer(); ?>