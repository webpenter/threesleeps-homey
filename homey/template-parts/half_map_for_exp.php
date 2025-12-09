<?php
global $post, $wp_query, $paged;
$zoom_level = homey_option('singlemap_zoom_level');
$search_layout = homey_option('search_posts_layout_exp');
$search_num_posts = homey_option('search_num_posts_exp');
$search_default_order = homey_option('search_default_order_exp');
$booking_type = isset($_GET['booking_type']) ? $_GET['booking_type'] : '';

$match_height_class = '';
if($search_layout == 'grid') {
    $match_height_class = 'homey-matchHeight-needed-ignore';
}
?>

<section class="half-map-wrap map-on-left clearfix">
        
    <div class="half-map-right-wrap">
        <div id="homey-halfmap"
            data-zoom="<?php echo intval($zoom_level); ?>"
            data-layout="<?php echo esc_attr($search_layout); ?>"
            data-num-posts="<?php echo esc_attr($search_num_posts); ?>"
            data-order="<?php echo esc_attr($search_default_order); ?>"
            data-booking_type=""
        >
        </div>
        <?php get_template_part('template-parts/map-controls-exp'); ?>
    </div><!-- .half-map-right-wrap -->

    <div class="half-map-left-wrap">
        <div class="half-map-left-inner-wrap">
            <?php get_template_part('template-parts/search/search-half-map-exp'); ?>
            <?php get_template_part('template-parts/experience/sort-tool_2'); ?>

            <div id="homey_halfmap_experiences_container" class="listing-wrap which-layout-<?php echo $search_layout;?> <?php if(str_contains($search_layout, '2')){ echo 'item-'.grid_list_or_card($search_layout, 1).'-view'; echo ' item-'.grid_list_or_card($search_layout, 1).'-view-v2';} ?> item-<?php echo esc_attr($search_layout);?>-view <?php echo esc_attr($match_height_class); ?>">
            </div><!-- grid-experience-page -->
        </div><!-- .half-map-left-inner-wrap -->
    </div><!-- .half-map-left-wrap -->
    
</section><!-- .half-map-wrap -->