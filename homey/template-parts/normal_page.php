<?php
global $post, $paged, $listing_founds, $number_of_listings, $template_args;
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$search_layout = homey_option('search_posts_layout');
$search_num_posts = homey_option('search_num_posts');
$sticky_sidebar = homey_option('sticky_sidebar');

$beds_baths_rooms_search = homey_option('beds_baths_rooms_search');
$search_criteria = '=';
if( $beds_baths_rooms_search == 'greater') {
    $search_criteria = '>=';
} elseif ($beds_baths_rooms_search == 'lessthen') {
    $search_criteria = '<=';
}

$search_criteria = '=';
if( $beds_baths_rooms_search == 'greater') {
    $search_criteria = '>=';
} elseif ($beds_baths_rooms_search == 'lessthen') {
    $search_criteria = '<=';
}

$full_width_class = '';
if($sidebar_meta['homey_sidebar'] != 'yes') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
    $full_width_class = 'listing-page-full-width';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'right') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';
    $sec_class = 'right-sidebar';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'left') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
    $sec_class = 'left-sidebar';
}

$match_height_class = '';
if($search_layout == 'grid') {
    $match_height_class = 'homey-matchHeight-needed';
}

$number_of_listings = 9;

$number_of_prop = $search_num_posts;
if(!$number_of_prop){
    $number_of_prop = 9;
}

if ( is_front_page()  ) {
    $paged = (get_query_var('page')) ? get_query_var('page') : 1;
}
$search_qry = array(
    'post_type' => 'listing',
    'posts_per_page' => $number_of_prop,
    'paged' => $paged,
    'post_status' => 'publish'
);

$meta_query = array();
// min and max price logic
if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any' && isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
    $min_price = doubleval(homey_clean($_GET['min-price']));
    $max_price = doubleval(homey_clean($_GET['max-price']));

    if ($min_price > 0 && $max_price > $min_price) {
        $meta_query[] = array(
            'key' => array('homey_day_date_price', 'homey_night_price'),
            'value' => array($min_price, $max_price),
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN',
        );
    }
} else if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any') {
    $min_price = doubleval(homey_clean($_GET['min-price']));
    if ($min_price > 0) {
        $meta_query[] = array(
            'key' => array('homey_day_date_price', 'homey_night_price'),
            'value' => $min_price,
            'type' => 'NUMERIC',
            'compare' => '>=',
        );
    }
} else if (isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
    $max_price = doubleval(homey_clean($_GET['max-price']));
    if ($max_price > 0) {
        $meta_query[] = array(
            'key' => array('homey_day_date_price', 'homey_night_price'),
            'value' => $max_price,
            'type' => 'NUMERIC',
            'compare' => '<=',
        );
    }
}

if(!empty($guests)) {
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key' => 'homey_total_guests_plus_additional_guests',
            'value' => intval($guests),
            'type' => 'NUMERIC',
            'compare' => $search_criteria,
        ),
        array(
            'key' => 'homey_guests',
            'value' => intval($guests),
            'type' => 'NUMERIC',
            'compare' => $search_criteria
        )
    );
}

//because this is boolean, no other option other than yes or no
//$pets = $pets == '' ? 1 : $pets;
//if(!empty($pets) && $pets != '0') {
if(!empty($pets) && $pets != -1) {
    $meta_query[] = array(
        'key' => 'homey_pets',
        'value' => $pets,
        'type' => 'NUMERIC',
        'compare' => '=',
    );
}
//print_r($meta_query);exit;
if (!empty($bedrooms)) {
    $bedrooms = sanitize_text_field($bedrooms);
    $meta_query[] = array(
        'key' => 'homey_listing_bedrooms',
        'value' => $bedrooms,
        'type' => 'NUMERIC',
        'compare' => $search_criteria,
    );
}

if (!empty($rooms)) {
    $rooms = sanitize_text_field($rooms);
    $meta_query[] = array(
        'key' => 'homey_listing_rooms',
        'value' => $rooms,
        'type' => 'NUMERIC',
        'compare' => $search_criteria,
    );
}

if( !empty($booking_type) ) {
    $meta_query[] = array(
        'key'     => 'homey_booking_type',
        'value'   => $booking_type,
        'compare' => '=',
        'type'    => 'CHAR'
    );
}

$template_args = array( 'listing-item-view' => 'item-grid-view' );

if ( $search_layout == 'list' || $search_layout == 'list-v2' ) {
    $template_args = array( 'listing-item-view' => 'item-list-view' );
} elseif ( $search_layout == 'card' ) {
    $template_args = array( 'listing-item-view' => 'item-card-view' );
}
?>

<section class="main-content-area listing-page  <?php echo esc_attr($full_width_class); ?> <?php echo esc_attr($match_height_class); ?>">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php the_title(); ?> </h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php
                //if(empty($guests)) {
                    $search_qry = apply_filters( 'homey_search_filter', $search_qry );
                    $search_qry = homey_listing_sort ( $search_qry );
               // }

                if(is_array($meta_query)){
                    if(isset($search_qry['meta_query'])){
                        $search_qry['meta_query'] = array_merge($search_qry['meta_query'], $meta_query);
                    }else{
                        $search_qry['meta_query'] = $meta_query;
                    }
                }

                $search_qry = new WP_Query( $search_qry );

                if ( $search_qry->have_posts() ) : $listing_founds = $search_qry->found_posts; ?>

                    <?php get_template_part('template-parts/listing/sort-tool'); ?>

                    <div class="listing-wrap which-layout-<?php echo $search_layout;?> <?php if(str_contains($search_layout, '2')){ echo 'item-'.grid_list_or_card($search_layout, 1).'-view'; echo ' item-'.grid_list_or_card($search_layout, 1).'-view-v2'; } ?> item-<?php echo $search_layout; ?>-view">
                        <div class="row">
                            <?php
                            while ( $search_qry->have_posts() ) : $search_qry->the_post();
                             if($search_layout == 'card') {
                                    get_template_part('template-parts/listing/listing-card', '', $template_args);
                                }elseif($search_layout == 'grid-v2' || $search_layout == 'list-v2') {
                                    get_template_part('template-parts/listing/listing-item-v2', '', $template_args);
                                } else {
                                    get_template_part('template-parts/listing/listing-item', '', $template_args);
                                }

                            endwhile;
                            ?>
                        </div>

                        <!--start Pagination-->
                        <?php homey_pagination( $search_qry->max_num_pages, $range = 2 ); wp_reset_postdata(); ?>
                        <!--start Pagination-->
                        <?php wp_reset_query(); ?>
                    </div><!-- listing-wrap -->

                <?php
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>
            </div>

            <?php if($sidebar_meta['homey_sidebar'] == 'yes') { ?>
                <div class="<?php echo esc_attr($sidebar_classes); if( isset($sticky_sidebar['listing_sidebar']) && $sticky_sidebar['listing_sidebar'] > 0 ){ echo ' homey_sticky'; }?>">
                    <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                        <?php get_sidebar('listing'); ?>
                    </div>
                </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->


</section><!-- main-content-area listing-page grid-listing-page -->