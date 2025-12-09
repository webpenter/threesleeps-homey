<?php
/**
 * Template Name: Listing Grid View v2
 */
get_header();

global $post, $listing_founds, $paged, $homey_prefix, $template_args;

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$pagination_type = homey_option('pagination_type');
$sticky_sidebar = homey_option('sticky_sidebar');

$full_width_class =''; 

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

$page_id = $post->ID;
$listing_sort = get_post_meta( $page_id, $homey_prefix.'listing_sort', true );
$number_of_listing = get_post_meta( $page_id, $homey_prefix.'listing_num', true );
$types = get_post_meta( $page_id, $homey_prefix.'types', false );
$room_types = get_post_meta( $page_id, $homey_prefix.'room_types', false );
$countries = get_post_meta( $page_id, $homey_prefix.'countries', false );
$states = get_post_meta( $page_id, $homey_prefix.'states', false );
$cities = get_post_meta( $page_id, $homey_prefix.'cities', false );
$areas = get_post_meta( $page_id, $homey_prefix.'areas', false );
$booking_type = get_post_meta( $page_id, $homey_prefix.'listing_booking_type', true );

if(!empty($number_of_listing)) {
    $posts_per_page  = $number_of_listing;
} else {
    $posts_per_page = 9;
}

$sort_by = $listing_sort;
if ( isset( $_GET['sortby'] ) ) {
    $sort_by = $_GET['sortby'];
}

$listing_args = array(
    'post_type' => 'listing',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish'
);

$listing_args = apply_filters( 'homey_listing_filter', $listing_args );
$listing_args = homey_listing_sort ( $listing_args );
$listing_qry = new WP_Query( $listing_args );

$listing_layout = homey_option('detail_layout');
if( isset( $_GET['detail_layout'] ) ) {
    $listing_layout = $_GET['detail_layout'];
}
$list_page_class = 'listing-page';
if($listing_layout == 'v5' || $listing_layout == 'v6' ){
    $list_page_class .= '-'.$listing_layout;
}

$template_args = array( 'listing-item-view' => 'item-grid-view' );
the_content();
?>

<section class="main-content-area listing-page <?php echo $list_page_class. ' ' .$full_width_class; ?>">
    <div data-section="breadcrumb" class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div data-section="posts"  class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php
                if ( $listing_qry->have_posts() ) : $listing_founds = $listing_qry->found_posts; ?>

                    <?php get_template_part('template-parts/listing/sort-tool'); ?>

                    <div id="listing_module_section" class="listing-wrap item-grid-view item-grid-v2-view">
                        <div id="module_listing" class="row">
                            <?php
                            while ( $listing_qry->have_posts() ) : $listing_qry->the_post();

                                get_template_part('template-parts/listing/listing-item-v2', '', $template_args );

                            endwhile;
                            ?>
                        </div>

                        <!--start Pagination-->
                        <?php
                        if($pagination_type == 'number') {
                            homey_pagination( $listing_qry->max_num_pages, $range = 2 );

                        } else if($pagination_type == 'loadmore') { ?>

                            <div class="homey-loadmore loadmore text-center">
                                <a
                                        data-paged="2"
                                        data-limit="<?php echo esc_attr($posts_per_page); ?>"
                                        data-style="grid"
                                        data-type="<?php homey_array_to_comma_string($types); ?>"
                                        data-roomtype="<?php homey_array_to_comma_string($room_types); ?>"
                                        data-country="<?php homey_array_to_comma_string($countries); ?>"
                                        data-state="<?php homey_array_to_comma_string($states); ?>"
                                        data-city="<?php homey_array_to_comma_string($cities); ?>"
                                        data-area="<?php homey_array_to_comma_string($areas); ?>"
                                        data-booking_type="<?php echo esc_attr($booking_type); ?>"
                                        data-featured=""
                                        data-offset="<?php echo esc_attr($posts_per_page); ?>"
                                        data-sortby="<?php echo esc_attr($sort_by); ?>"
                                        href="#"
                                        class="btn btn-primary btn-long">
                                    <i id="spinner-icon" class="homey-icon homey-icon-loading-half fa-pulse fa-spin fa-fw" style="display: none;"></i>
                                    <?php echo esc_attr($homey_local['loadmore_btn']); ?>
                                </a>
                            </div>
                        <?php } ?>
                        <!--start Pagination-->

                        <?php wp_reset_postdata(); ?>

                    </div><!-- listing-wrap -->

                <?php
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>

            </div>

            <?php if($sidebar_meta['homey_sidebar'] == 'yes') { ?>
                <div class="<?php echo esc_attr($sidebar_classes); if( isset($sticky_sidebar['listing_sidebar']) && $sticky_sidebar['listing_sidebar'] > 0 ){ echo ' homey_sticky'; } ?>">
                <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                    <?php get_sidebar('listing'); ?>
                </div>
            </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->
</section><!-- main-content-area listing-page grid-listing-page -->

<?php get_footer(); ?>
