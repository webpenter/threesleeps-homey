<?php
global $post, $homey_prefix, $homey_local, $template_args;
$listing_images = get_post_meta( get_the_ID(), 'homey_listing_images', false );
$bedrooms       = get_post_meta( get_the_ID(), 'homey_listing_bedrooms', true );
$guests         = get_post_meta( get_the_ID(), 'homey_guests', true );

$allow_additional_guests = get_post_meta( get_the_ID(), 'homey_allow_additional_guests', true );
$num_additional_guests = get_post_meta( get_the_ID(), 'homey_num_additional_guests', true );

if( $allow_additional_guests == 'yes' && ! empty( $num_additional_guests ) ) {
    $guests = (int) $guests + (int) $num_additional_guests;
}

$beds           = get_post_meta( get_the_ID(), 'homey_beds', true );
$baths          = get_post_meta( get_the_ID(), 'homey_baths', true );
$night_price    = get_post_meta( get_the_ID(), 'homey_night_price', true );
$listing_author = homey_get_author();
$enable_host = homey_option('enable_host');
$compare_favorite = homey_option('compare_favorite');

if(isset($template_args['listing_price_from_search_results'])){
    $listing_price = $template_args['listing_price_from_search_results'];
}else{
    if(isset($_REQUEST['arrive'])){
        $listing_id = get_the_ID();
        $listing_price = homey_get_price_by_id($listing_id);
    }else{
        $listing_price = homey_get_price();

    }
}

$cgl_meta = homey_option('cgl_meta');
$cgl_beds = homey_option('cgl_beds');
$cgl_baths = homey_option('cgl_baths');
$cgl_guests = homey_option('cgl_guests');
$cgl_types = homey_option('cgl_types');
$rating = homey_option('rating');
$total_rating = get_post_meta( get_the_ID(), 'listing_total_rating', true );

$lgc_listing_icons = [ 'bedrooms', 'bathrooms', 'guests' ];
if(function_exists('homey_get_lgc_listing_icon_html')) {
    $lgc_listing_icons = homey_get_lgc_listing_icon_html();
}

$bedrooms_icon = $lgc_listing_icons['bedrooms'];
$bathroom_icon = $lgc_listing_icons['bathrooms'];
$guests_icon = $lgc_listing_icons['guests'];

$price_separator = homey_option('currency_separator');

$homey_permalink = homey_listing_permalink();
$homey_permalink_review_link = $homey_permalink.'#reviews-section';
?>

<div class="item-wrap infobox_trigger homey-matchHeight <?php echo is_null($template_args) ? '' : __( $template_args['listing-item-view'] ); ?> infobox_trigger homey-matchHeight" data-dir="listing-item" data-id="<?php echo $post->ID; ?>">
    <div class="media property-item">
        <div class="media-left">
            <div class="item-media item-media-thumb">
                <?php homey_listing_featured(get_the_ID()); ?>
                <a class="hover-effect" href="<?php echo esc_url($homey_permalink); ?>">
                    <?php
                    if( has_post_thumbnail( $post->ID ) ) {
                        the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                    }else{
                        homey_image_placeholder( 'homey-listing-thumb' );
                    }
                    ?>
                </a>

                <?php if(!empty($listing_price)) { ?>
                    <div class="item-media-price">
                        <span class="item-price">
                            <?php echo homey_formatted_price($listing_price, false, true); ?><sub><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></sub>
                        </span>
                    </div>
                <?php } ?>

                <?php if($enable_host) { ?>
                    <div class="item-user-image">
                        <?php echo ''.$listing_author['photo']; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="media-body item-body clearfix">

            <div class="item-title-head table-block">
                <div class="title-head-left">
                    <h2 class="title"><a href="<?php echo esc_url($homey_permalink); ?>">
                            <?php the_title(); ?></a></h2>

                    <?php get_template_part('single-listing/item-address'); ?>
        
                </div>
            </div>

            <?php if($cgl_meta != 0) { ?>
            <ul class="item-amenities">

                <?php if($cgl_beds != 0 && $bedrooms != '') { ?>
                <li>
                    <?php echo ''.$bedrooms_icon; ?> 
                    <span class="total-beds"><?php echo esc_attr($bedrooms); ?></span> <span class="item-label"><?php echo esc_attr(homey_option('glc_bedrooms_label'));?></span>
                </li>
                <?php } ?>

                <?php if($cgl_baths != 0 && $baths != '') { ?>
                <li>
                    <?php echo ''.$bathroom_icon; ?> 
                    <span class="total-baths"><?php echo esc_attr($baths); ?></span> <span class="item-label"><?php echo esc_attr(homey_option('glc_baths_label'));?></span>
                </li>
                <?php } ?>

                <?php if($cgl_guests!= 0 && $guests != '') { ?>
                <li>
                    <?php echo ''.$guests_icon; ?> 
                    <span class="total-guests"><?php echo esc_attr($guests); ?></span> <span class="item-label"><?php echo esc_attr(homey_option('glc_guests_label'));?></span>
                </li>
                <?php } ?>

                <?php if($cgl_types != 0) { ?>
                <li class="item-type"><?php echo homey_taxonomy_simple('listing_type'); ?></li>
                <?php } ?>
            </ul>
            <?php } ?>

            <?php if($enable_host) { ?>
                <div class="item-user-image list-item-hidden">
                    <?php echo ''.$listing_author['photo']; ?>
                    <span class="item-user-info"><?php echo esc_attr($homey_local['hosted_by']);?><br>
                    <?php echo esc_attr($listing_author['name']); ?></span>
                </div>
            <?php } ?>

            <div class="item-footer">

                <?php if($compare_favorite) { ?>
                    <div class="footer-right">
                        <div class="item-tools">
                            <div class="btn-group dropup">
                                <?php get_template_part('template-parts/listing/compare-fav'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php
                if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                    <div class="footer-left">
                        <div class="stars">
                            <div class="list-inline rating">
                                <?php echo homey_get_review_v2($total_rating, get_the_ID(), $homey_permalink_review_link); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div><!-- .item-wrap -->