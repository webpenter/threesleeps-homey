<?php
global $post, $homey_prefix, $homey_local, $template_args;
$experience_images = get_post_meta( get_the_ID(), $homey_prefix.'experience_images', false );
$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
$experience_type    = homey_taxonomy_simple_by_ID( 'experience_type', $post->ID );
$allow_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'allow_additional_guests', true );
$num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );

if( $allow_additional_guests == 'yes' && ! empty( $num_additional_guests ) ) {
    $guests = $guests + $num_additional_guests;
}

$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$experience_author = homey_get_author();
$enable_host = homey_option('experience_enable_host');
$compare_favorite = homey_option('experience_compare_favorite');

$experience_price = homey_exp_get_price();

$cgl_types = homey_option('experience_cgl_types');
$rating = homey_option('experience_rating');
$total_rating = get_post_meta( get_the_ID(), 'experience_total_rating', true );

$guests_icon = homey_option('experience_lgc_guests_icon');
$price_separator = homey_option('currency_separator');

if(!empty($guests_icon)) {
    $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
}
$homey_permalink = homey_experience_permalink();
$homey_permalink_review_link = $homey_permalink.'#reviews-section';
?>

<div class="item-wrap <?php echo __( $template_args['listing-item-view'] ) ?> experience-wrap infobox_trigger homey-matchHeight"  data-id="<?php echo $post->ID; ?>">
    <div class="media property-item experience-item">
        <div class="media-left">
            <div class="item-media item-media-thumb">
                
                <?php homey_experience_featured(get_the_ID()); ?>

                <a class="hover-effect" href="<?php echo esc_url($homey_permalink); ?>">
                <?php
                if( has_post_thumbnail( $post->ID ) ) {
                    the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                }else{
                    homey_image_placeholder( 'homey-listing-thumb' );
                }
                ?>
                </a>

                <?php if(!empty($experience_price)) { ?>
                <div class="item-media-price">
                    <span class="item-price">
                        <?php echo homey_formatted_price($experience_price, false, true); ?><sub><?php echo homey_exp_get_price_label();?></sub>
                    </span>
                </div>
                <?php } ?>

                <?php if($enable_host) { ?>
                <div class="item-user-image">
                    <?php echo ''.$experience_author['photo']; ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="media-body item-body clearfix">
            <div class="item-title-head table-block">
                <div class="title-head-left">
                    <h2 class="title"><a href="<?php echo esc_url($homey_permalink); ?>">
                    <?php the_title(); ?></a></h2>
                    <?php get_template_part('single-experience/item-address'); ?>
                    <?php 
                    if(!empty($address)) {
                        echo '<address class="item-address">'.esc_attr(substr($address, 0, 50)).'</address>';
                    }
                    ?>
                    <?php if($cgl_types == 1 && ! empty( $experience_type) ){ ?>
                        <ul class="item-amenities">
                                <li>
                                    <span class="item-type"><?php echo esc_attr($experience_type); ?></span>
                                </li>
                        </ul>
                    <?php } ?>

                </div>
            </div>

            <?php if($enable_host) { ?>
            <div class="item-user-image list-item-hidden">
                    <?php echo ''.$experience_author['photo']; ?>
                    <span class="item-user-info"><?php echo esc_attr($homey_local['hosted_by']);?><br>
                    <?php echo esc_attr($experience_author['name']); ?></span>
            </div>
            <?php } ?>

            <div class="item-footer">

                <?php if($compare_favorite) { ?>
                <div class="footer-right">
                    <div class="item-tools">
                        <div class="btn-group dropup">
                            <?php get_template_part('template-parts/experience/compare-fav'); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php 
                if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                <div class="footer-left">
                    <div class="stars">
                        <div class="rating">
                            <?php echo homey_get_review_v2($total_rating, get_the_ID(), $homey_permalink_review_link, 'experience'); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div><!-- .item-wrap -->