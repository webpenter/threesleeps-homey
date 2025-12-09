<?php
/**
 * Template Name: Experiences Card View
 */
get_header();
global $post, $experience_founds, $paged, $homey_prefix, $template_args;

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
$experiences_sort = get_post_meta( $page_id, $homey_prefix.'experiences_sort', true );
$number_of_experiences = get_post_meta( $page_id, $homey_prefix.'experiences_num', true );
$types = get_post_meta( $page_id, $homey_prefix.'types_exp', false );
$countries = get_post_meta( $page_id, $homey_prefix.'countries_exp', false );
$states = get_post_meta( $page_id, $homey_prefix.'states_exp', false );
$cities = get_post_meta( $page_id, $homey_prefix.'cities_exp', false );
$areas = get_post_meta( $page_id, $homey_prefix.'areas_exp', false );

if(!empty($number_of_experiences)) {
    $posts_per_page  = $number_of_experiences;
} else {
    $posts_per_page = 9;
}
$sort_by = $experiences_sort;
if ( isset( $_GET['sortby'] ) ) {
    $sort_by = $_GET['sortby'];
}

$experience_args = array(
    'post_type' => 'experience',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish'
);

$experience_args = apply_filters( 'homey_experience_filter', $experience_args );

$experience_args = homey_experience_sort ( $experience_args );

$experience_qry = new WP_Query( $experience_args );

$template_args = array( 'listing-item-view' => 'item-card-view' );
?>

<section class="main-content-area experiences-listing-page listing-page homey-matchHeight-needed <?php echo $full_width_class; ?>">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="experience-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php
                if ( $experience_qry->have_posts() ) : $experience_founds = $experience_qry->found_posts; ?>

                    <?php get_template_part('template-parts/experience/sort-tool'); ?>

                    <div id="experiences_module_section" class="listing-wrap item-card-view">
                        <div id="module_experiences" class="row">
                            <?php
                            while ( $experience_qry->have_posts() ) : $experience_qry->the_post();

                                get_template_part('template-parts/experience/experience', 'card', $template_args);

                            endwhile;
                            ?>
                        </div>

                        <!--start Pagination-->
                        <?php
                        if($pagination_type == 'number') {
                            homey_pagination( $experience_qry->max_num_pages, $range = 2 );

                        } else if($pagination_type == 'loadmore' && $experience_qry->max_num_pages > 1) { ?>

                            <div class="homey-loadmore loadmore text-center">
                                <a
                                        data-paged="2"
                                        data-limit="<?php echo esc_attr($posts_per_page); ?>"
                                        data-style="card"
                                        data-type="<?php homey_array_to_comma_string($types); ?>"
                                        data-country="<?php homey_array_to_comma_string($countries); ?>"
                                        data-state="<?php homey_array_to_comma_string($states); ?>"
                                        data-city="<?php homey_array_to_comma_string($cities); ?>"
                                        data-area="<?php homey_array_to_comma_string($areas); ?>"
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

                    </div><!-- experience-wrap -->

                <?php
                else:
                    get_template_part('template-parts/experience/experience-none');
                endif;
                ?>

            </div>

            <?php if($sidebar_meta['homey_sidebar'] == 'yes') { ?>
                <div class="<?php echo esc_attr($sidebar_classes); if( isset($sticky_sidebar['experience_sidebar']) && $sticky_sidebar['experience_sidebar'] > 0 ){ echo ' homey_sticky'; }?>">
                    <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                        <?php get_sidebar('experience'); ?>
                    </div>
                </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->


</section><!-- main-content-area experience-page grid-experience-page -->

<?php get_footer(); ?>
