<?php

/**
 * Common Taxonomy - Used by property taxonomies
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/01/16
 * Time: 6:09 PM
 */
global $wp_query, $post, $taxonomy_title, $taxonomy_name, $listing_founds, $listing_view, $template_args, $homey_prefix, $homey_local;
$sticky_sidebar = '';
$page_id = $post->ID;

$sticky_sidebar = homey_option('sticky_sidebar');
$pagination_type = homey_option('pagination_type');
$listings_sort = get_post_meta($page_id, $homey_prefix . 'listings_sort', true);

// Title
$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
$taxonomy_title = $current_term->name;
$description = $current_term->description;
$taxonomy_name = get_query_var('taxonomy');

$taxonomy_sidebar = homey_option('taxonomy_layout');
$taxonomy_layout = homey_option('taxonomy_posts_layout');

$template_args = array('listing-item-view' => 'item-grid-view');

if ($taxonomy_layout == 'list' || $taxonomy_layout == 'list-v2') {
    $template_args = array('listing-item-view' => 'item-list-view');
} elseif ($taxonomy_layout == 'card') {
    $template_args = array('listing-item-view' => 'item-card-view');
}

$taxonomy_num_posts = homey_option('taxonomy_num_posts');
$full_width_page = '';
if ($taxonomy_sidebar == 'no-sidebar') {
    $full_width_page = "listing-page-full-width";
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
} elseif ($taxonomy_sidebar == 'right-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';
    $sec_class = "right-sidebar";
} elseif ($taxonomy_sidebar == 'left-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
    $sec_class = "left-sidebar";
}

$number_of_listings = 9;

$number_of_prop = $taxonomy_num_posts;
if (!$number_of_prop) {
    $number_of_prop = 9;
}

$sort_args = array('post_status' => 'publish');
$sort_args = homey_listing_sort($sort_args);
$sort_args['post_status'] = 'publish';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$sort_args['paged'] = $paged;
$sort_args['posts_per_page'] = $number_of_prop;

if (!empty($number_of_listings)) {
    $posts_per_page  = $number_of_listings;
} else {
    $posts_per_page = 9;
}

$sort_by = $listings_sort;

$types = get_post_meta($page_id, $homey_prefix . 'types', false);

$room_types = get_post_meta($page_id, $homey_prefix . 'room_types', false);
$countries = get_post_meta($page_id, $homey_prefix . 'countries', false);
$states = get_post_meta($page_id, $homey_prefix . 'states', false);
$cities = get_post_meta($page_id, $homey_prefix . 'cities', false);
$areas = get_post_meta($page_id, $homey_prefix . 'areas', false);
$booking_type = get_post_meta($page_id, $homey_prefix . 'listings_booking_type', true);

$args = array_merge($wp_query->query_vars, $sort_args);

// if radius is enabled zahid.k
$lat = isset($_REQUEST['lat']) ? $_REQUEST['lat'] : '';
$lng = isset($_REQUEST['lng']) ? $_REQUEST['lng'] : '';
$search_radius = isset($_REQUEST['radius']) ? $_REQUEST['radius'] : 50;

if (homey_option('enable_radius') && homey_option('show_radius') != 0) {
    $radius_ids = apply_filters('homey_radius_filter', $args, $lat, $lng, $search_radius);

    if (!empty($radius_ids)) {
        $args['post__in'] = $radius_ids;
    }
}
// if radius is enabled zahid.k

$tax_query = [];

if (isset($_REQUEST['city']) && !empty($_REQUEST['city'])) {
    $tax_query[] = array(
        'taxonomy' => 'listing_city',
        'field' => 'slug',
        'terms' => $_REQUEST['city']
    );
}

$tax_count = count($tax_query);

if ($tax_count > 1) {
    $args['relation'] = 'AND';
}
if ($tax_count > 0) {
    $args['tax_query'] = $tax_query;
}

$wp_query = new WP_Query($args);
$types = isset($args['listing_type']) ? $args['listing_type'] : $types;
?>

<section class="main-content-area listing-page <?php echo $full_width_page; ?>">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <?php get_template_part('template-parts/page-title'); ?>
                <p><?php echo $current_term->name; ?></p>
            </div>

            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 hidden-xs">
                <?php get_template_part('template-parts/listing/layout-tool'); ?>
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php if ($wp_query->have_posts()) : $listing_founds = $wp_query->found_posts; ?>

                    <?php get_template_part('template-parts/listing/sort-tool'); ?>

                    <div id="listings_module_section_taxonomy" class="listing-wrap which-layout-<?php echo $taxonomy_layout; ?> <?php if (str_contains($taxonomy_layout, '2')) {
                                                                                                                                    echo 'item-' . grid_list_or_card($taxonomy_layout, 1) . '-view';
                                                                                                                                    echo ' item-' . grid_list_or_card($taxonomy_layout, 1) . '-view-v2';
                                                                                                                                } ?> item-<?php echo esc_attr($taxonomy_layout); ?>-view">
                        <div id="module_listings_div" class="row">
                            <?php
                            while ($wp_query->have_posts()) : $wp_query->the_post();

                                if ($taxonomy_layout == 'card') {
                                    get_template_part('template-parts/listing/listing-card', '', $template_args);
                                } elseif ($taxonomy_layout == 'grid-v2' || $taxonomy_layout == 'list-v2') {
                                    get_template_part('template-parts/listing/listing-item-v2', '', $template_args);
                                } else {
                                    get_template_part('template-parts/listing/listing-item', '', $template_args);
                                }

                            endwhile;
                            ?>
                        </div>

                        <!--start Pagination-->
                        <?php
                        if ($pagination_type == 'number') {
                            homey_pagination($wp_query->max_num_pages, $range = 2);
                        } else if ($pagination_type == 'loadmore' && $wp_query->max_num_pages > 1) { ?>

                            <div class="homey-loadmore loadmore text-center">
                                <a
                                    data-paged="2"
                                    data-limit="<?php echo esc_attr($posts_per_page); ?>"
                                    data-style="<?php echo esc_attr($taxonomy_layout); ?>"
                                    data-type="<?php if (is_array($types)) {
                                                    homey_array_to_comma_string($types);
                                                } else {
                                                    echo $types;
                                                }; ?>"
                                    data-roomtype="<?php homey_array_to_comma_string($room_types); ?>"
                                    data-country="<?php homey_array_to_comma_string($countries); ?>"
                                    data-state="<?php homey_array_to_comma_string($states); ?>"
                                    data-city="<?php homey_array_to_comma_string($cities); ?>"
                                    data-area="<?php homey_array_to_comma_string($areas); ?>"
                                    data-booking_type="<?php echo esc_attr($booking_type); ?>"
                                    data-featured=""
                                    data-offset="<?php echo esc_attr($posts_per_page * $paged); ?>"
                                    data-sortby="<?php echo esc_attr($sort_by); ?>"
                                    href="javascript:void(0)"
                                    class="btn btn-primary btn-long">
                                    <i id="spinner-icon" class="homey-icon homey-icon-loading-half fa-pulse fa-spin fa-fw" style="display: none;"></i>
                                    <?php echo esc_attr($homey_local['loadmore_btn']); ?>
                                </a>
                            </div>
                        <?php } ?>
                        <?php wp_reset_postdata(); ?>
                        <!--start Pagination-->

                    </div><!-- listing-wrap -->

                <?php
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>
                <p><?php echo $description; ?></p>
            </div>

            <?php if ($taxonomy_sidebar != 'no-sidebar') { ?>
                <div class="<?php echo esc_attr($sidebar_classes);
                            if (isset($sticky_sidebar['listing_sidebar']) && $sticky_sidebar['listing_sidebar'] > 0) {
                                echo ' homey_sticky';
                            } ?>">
                    <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                        <?php get_sidebar('listing'); ?>
                    </div>
                </div>
            <?php } ?>

        </div><!-- .row -->
    </div> <!-- .container -->


</section><!-- main-content-area listing-page grid-listing-page -->