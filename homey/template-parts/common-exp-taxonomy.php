<?php
/**
 * Common Taxonomy - Used by property taxonomies
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/01/16
 * Time: 6:09 PM
 */
global $post, $taxonomy_title, $taxonomy_name, $experience_founds, $experience_view, $template_args;
$sticky_sidebar = '';

$sticky_sidebar = homey_option('sticky_sidebar');

// Title
$current_term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
$taxonomy_title = $current_term->name;
$taxonomy_name = get_query_var( 'taxonomy' );

$taxonomy_sidebar = homey_option('taxonomy_exp_layout');
$taxonomy_layout = homey_option('taxonomy_exp_posts_layout');
$taxonomy_num_posts = homey_option('taxonomy_exp_num_posts');

$template_args = array( 'listing-item-view' => 'item-grid-view' );

if ( $taxonomy_layout == 'list' || $taxonomy_layout == 'list-v2' ) {
    $template_args = array( 'listing-item-view' => 'item-list-view' );
} elseif ( $taxonomy_layout == 'card' ) {
    $template_args = array( 'listing-item-view' => 'item-card-view' );
}

$full_width_page_class = '';

if($taxonomy_sidebar == 'no-sidebar') {
    $full_width_page_class = 'listing-page-full-width';
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

} elseif($taxonomy_sidebar == 'right-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';
    $sec_class = "right-sidebar";

} elseif($taxonomy_sidebar == 'left-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
    $sec_class = "left-sidebar";
}

$number_of_experiences = 9;

$number_of_prop = $taxonomy_num_posts;
if(!$number_of_prop){
    $number_of_prop = 9;
}

$sort_args = array( 'post_status' => 'publish');
$sort_args = homey_experience_sort($sort_args);
$sort_args['post_status'] = 'publish';

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$sort_args['paged'] = $paged;
$sort_args['posts_per_page'] = $number_of_prop;

global $wp_query;
$args = array_merge( $wp_query->query_vars, $sort_args );

// if radius is enabled zahid.k
$lat = isset($_REQUEST['lat']) ? $_REQUEST['lat'] : '';
$lng = isset($_REQUEST['lng']) ? $_REQUEST['lng'] : '';
$search_radius = isset($_REQUEST['radius']) ? $_REQUEST['radius'] : 50;

if( homey_option('enable_radius') && homey_option('show_radius') != 0 ) {
    $radius_ids = apply_filters('homey_radius_filter', $args, $lat, $lng, $search_radius);

    if(!empty($radius_ids)) {
        $args['post__in'] = $radius_ids;
    }
}
// if radius is enabled zahid.k

$tax_query = [];

if(isset($_REQUEST['city']) && !empty($_REQUEST['city'])) {
    $tax_query[] = array(
        'taxonomy' => 'experience_city',
        'field' => 'slug',
        'terms' => $_REQUEST['city']
    );
}

if(isset($_REQUEST['language']) && !empty($_REQUEST['language'])) {
    $tax_query[] = array(
        'taxonomy' => 'experience_language',
        'field' => 'slug',
        'terms' => $_REQUEST['language']
    );
}

$tax_count = count( $tax_query );

if( $tax_count > 1 ) {
    $args['relation'] = 'AND';
}
if( $tax_count > 0 ){
    $args['tax_query'] = $tax_query;
}

$wp_query = new WP_Query( $args );
?>

<section class="main-content-area listing-page experiences-listing-page <?php echo $full_width_page_class; ?>">
<!--<section class="main-content-area experience-page experience-page-full-width">-->
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <?php get_template_part('template-parts/page-title'); ?>
                <p><?php echo $current_term->name; ?></p>
            </div>

            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 hidden-xs">
                <?php get_template_part('template-parts/experience/layout-tool'); ?>
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php if ( $wp_query->have_posts() ) : $experience_founds = $wp_query->found_posts; ?>

                    <?php get_template_part('template-parts/experience/sort-tool'); ?>

                    <div class="listing-wrap item-<?php echo esc_attr($taxonomy_layout);?>-view">
<!--                    <div class="experience-wrap item---><?php //echo esc_attr($taxonomy_layout);?><!---view">-->
                        <div class="row">
                            <?php
                            while ( $wp_query->have_posts() ) : $wp_query->the_post();

                                if($taxonomy_layout == 'card') {
                                    get_template_part('template-parts/experience/experience', 'card', $template_args);
                                }elseif($taxonomy_layout == 'grid-v2' || $taxonomy_layout == 'list-v2') {
                                    get_template_part('template-parts/experience/experience', 'item-v2', $template_args);
                                } else {
                                    get_template_part('template-parts/experience/experience', 'item', $template_args);
                                }

                            endwhile;
                            ?>
                        </div>

                        <!--start Pagination-->
                        <?php homey_pagination( $wp_query->max_num_pages, $range = 2 ); wp_reset_postdata(); ?>
                        <!--start Pagination-->

                    </div><!-- experience-wrap -->

                <?php
                else:
                    get_template_part('template-parts/experience/experience-none');
                endif;
                ?>

            </div>

            <?php if($taxonomy_sidebar != 'no-sidebar') { ?>
                <div class="<?php echo esc_attr($sidebar_classes); if( isset($sticky_sidebar['experience_sidebar'])){ if( @$sticky_sidebar['experience_sidebar'] > 0 ){ echo ' homey_sticky'; } } ?>">
                    <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                        <?php get_sidebar('experience'); ?>
                    </div>
                </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->


</section><!-- main-content-area experience-page grid-experience-page -->
