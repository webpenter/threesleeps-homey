<?php
global $post, $paged, $experience_founds, $number_of_experiences, $template_args;

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$search_layout = homey_option('search_posts_layout_exp');
$search_num_posts = homey_option('search_num_posts_exp');
$search_default_order = homey_option('search_default_order_exp');
$sticky_sidebar = homey_option('sticky_sidebar');

if($sidebar_meta['homey_sidebar'] != 'yes') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

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


$number_of_experiences = 9;

$number_of_prop = $search_num_posts;
if(!$number_of_prop){
    $number_of_prop = 9;
}

if ( is_front_page() ) {
    $paged = (get_query_var('page')) ? get_query_var('page') : 1;
}

$search_qry = array(
    'post_type' => 'experience',
    'posts_per_page' => $number_of_prop,
    'paged' => $paged,
    'post_status' => 'publish'
);

$template_args = array( 'listing-item-view' => 'item-grid-view' );

if ( $search_layout == 'list' || $search_layout == 'list-v2' ) {
    $template_args = array( 'listing-item-view' => 'item-list-view' );
} elseif ( $search_layout == 'card' ) {
    $template_args = array( 'listing-item-view' => 'item-card-view' );
}

?>

<section class="main-content-area experience-page listing-page listing-page-full-width <?php echo esc_attr($match_height_class); ?>">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="experience-title"><?php the_title(); ?> </h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php 
                $search_qry = apply_filters( 'homey_search_filter_exp', $search_qry );

                $search_qry = homey_experience_sort ( $search_qry );

                $search_qry = new WP_Query( $search_qry );
                if ( $search_qry->have_posts() ) : $experience_founds = $search_qry->found_posts; ?>

                <?php get_template_part('template-parts/experience/sort-tool'); ?>
                
                <div class="listing-wrap which-layout-<?php echo $search_layout;?> <?php if(str_contains($search_layout, '2')){ echo 'item-'.grid_list_or_card($search_layout, 1).'-view'; echo ' item-'.grid_list_or_card($search_layout, 1).'-view-v2';} ?> item-<?php echo esc_attr($search_layout);?>-view">
                    <div class="row">
                        <?php
                        while ( $search_qry->have_posts() ) : $search_qry->the_post();

                            if($search_layout == 'card') {
                                get_template_part('template-parts/experience/experience', 'card', $template_args);
                            }elseif($search_layout == 'grid-v2' || $search_layout == 'list-v2') {
                                get_template_part('template-parts/experience/experience', 'item-v2', $template_args);
                            } else {
                                get_template_part('template-parts/experience/experience', 'item', $template_args);
                            }

                        endwhile;
                        ?>
                    </div>
                    
                    <!--start Pagination-->
                    <?php homey_pagination( $search_qry->max_num_pages, $range = 2 ); wp_reset_postdata(); ?>
                    <!--start Pagination-->
                <?php wp_reset_query(); ?>
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