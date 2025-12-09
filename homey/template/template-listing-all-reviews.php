<?php
/**
 * Template Name: Listings That Have Reviews
 */
get_header();

global $post, $listing_founds, $paged, $homey_prefix, $template_args;

if (!empty($number_of_listing)) {
    $posts_per_page = $number_of_listing;
} else {
    $posts_per_page = 25;
}

the_content();
?>

<section class="main-content-area listing-page listing-page <?php echo $full_width_class; ?>">
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

        <div data-section="posts" class="container">
            <div class="row">
                <?php
                $wp_qry = new WP_Query(
                    array(
                        'post_type' => 'homey_review',
                        'posts_per_page' => $posts_per_page,
                        'paged' => $paged,
                        'ignore_sticky_posts' => 1,
                        'post_status' => 'publish'
                    )
                );
                ?>

                <div class="all-reviews-body">
                    <?php
                    if ($wp_qry->have_posts()):
                        while ($wp_qry->have_posts()): $wp_qry->the_post();
                            $listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
                            $rating = get_post_meta(get_the_ID(), 'homey_rating', true);
                            $review_author = homey_get_author('70', '70', 'img-circle');
                            ?>

                            <div class="review-block">
                                <div class="media">
                                    <div class="media-left">
                                        <a class="media-object">
                                            <?php echo $review_author['photo']; ?>
                                        </a>
                                    </div>
                                    <div class="media-body media-middle">
                                        <div class="msg-user-info">
                                            <div class="msg-user-left">
                                                <h2 class="title"><a
                                                            href="<?php echo get_permalink($listing_id); ?>/#review-<?php the_ID(); ?>"><?php echo get_the_title($listing_id); ?></a>
                                                </h2>

                                                <div class="message-date">

                                                    <i class="homey-icon homey-icon-calendar-3"></i><?php printf(__('%s ago', 'homey'), human_time_diff(get_the_time('U'), current_time('timestamp'))); ?>


                                                    <span class="rating">
			                                	<?php echo homey_get_review_stars($rating, true, true, false); ?>
			                            	</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo homey_get_content(15); ?>
                                    </div>
                                </div>
                            </div>

                        <?php
                        endwhile;
                    endif;
                    homey_pagination($wp_qry->max_num_pages, $range = 2);
                    wp_reset_postdata();
                    ?>
                </div><!-- widget-body -->
            </div><!-- .row -->
        </div>   <!-- .container -->
    </section><!-- main-content-area listing-page grid-listing-page -->

<?php get_footer();