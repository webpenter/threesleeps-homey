<?php
global $post, $homey_local, $homey_prefix;
$rating = homey_option('rating');
?>
<section class="top-banner-wrap property-slider-wrap <?php homey_banner_fullscreen(); ?>">
        <div class="header-slider">
            <?php
            $args = array(
                'post_type' => 'experience',
                'meta_key' => $homey_prefix.'homeslider_exp',
                'meta_value' => 'yes',
                'posts_per_page' => '-1'
            );
            $slider = new WP_Query( $args );

            if( $slider->have_posts() ): while( $slider->have_posts() ): $slider->the_post();
                $slider_img = get_post_meta( $post->ID, $homey_prefix. 'slider_image_exp', true );
                $imag_url = wp_get_attachment_image_src( $slider_img, 'full', true );

                $address        = ''; //get_post_meta( get_the_ID(), $homey_prefix.'experience_address', true );
                //guests info
                $guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
                $guests         = $guests > 0 ? $guests : 0;

                $num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );
                $num_additional_guests = $num_additional_guests > 0 ? $num_additional_guests : 0;

                $total_guests   = (int) $num_additional_guests + (int) $guests;

                $night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
                $experience_author = homey_get_author();

                $total_rating = get_post_meta( get_the_ID(), 'experience_total_rating', true );
                $experience_total_number_of_reviews = get_post_meta( $post->ID, 'experience_total_number_of_reviews', true );
                ?>
                    <div class="header-slider-item" style="background-image: url(<?php echo esc_url($imag_url[0]); ?>);">
                    <a class="banner-link" href="<?php the_permalink(); ?>"></a>
                    <div class="item-wrap item-list-view">
                        <div class="property-item">
                            <div class="item-body clearfix">
                                <div class="item-title-head table-block">
                                    <div class="title-head-left">
                                        <h2 class="title"><a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?></a></h2>
                                        <?php get_template_part('single-experience/item-address'); ?>
                                        <?php if(!empty($address)) { ?>
                                        <address class="item-address"><?php echo esc_attr($address); ?></address>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="item-media-price">
                                    <span class="item-price">
                                        <?php echo homey_formatted_price($night_price, false, true); ?><sub>/<?php echo homey_exp_get_price_label_by_id(get_the_ID()); ?></sub>
                                    </span>
                                </div>
                                <ul class="item-amenities">
                                    <li>
                                        <i class="homey-icon homey-icon-multiple-man-woman-2"></i>
                                        <span class="total-guests"><?php echo esc_attr($total_guests); ?></span> <span class="item-label"><?php echo esc_attr(homey_option('experience_glc_guests_label'));?></span>
                                    </li>
                                    <li class="item-type"><?php echo homey_taxonomy_simple('experience_type'); ?></li>
                                </ul>

                                <div class="item-user-image list-item-hidden">
                                    <?php echo ''.$experience_author['photo'];?>
                                    <span class="item-user-info"><?php echo esc_attr($homey_local['hosted_by']);?><br>
                                    <?php echo esc_attr($experience_author['name']); ?></span>
                                </div>

                                <div class="item-footer">
                                    <?php 
                                    if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                                    <div class="footer-left">
                                        <div class="stars">
                                            <ul class="list-inline rating">
                                                <?php echo homey_get_review_exp_stars_v2($total_rating, false, true, false); ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div><!-- .item-wrap -->
                </div><!-- header-slider-item -->
            <?php endwhile; endif; wp_reset_postdata(); ?>
        </div><!-- top-gallery-section -->
    </section><!-- header-parallax -->