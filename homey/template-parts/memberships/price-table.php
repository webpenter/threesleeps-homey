<?php
$pkg_listing_or_experience = get_post_meta( get_the_ID(), 'hm_settings_pkg_listing_or_experience', true );
if(is_null(trim($pkg_listing_or_experience))){ $pkg_listing_or_experience = 'both';}

$billing_period = get_post_meta( get_the_ID(), 'hm_settings_bill_period', true );
$billing_frequency = get_post_meta( get_the_ID(), 'hm_settings_billing_frequency', true );

$listings_included = get_post_meta( get_the_ID(), 'hm_settings_listings_included', true );
$listings_included = $listings_included > 0 ? $listings_included    : 0;

$unlimited_listings = get_post_meta( get_the_ID(), 'hm_settings_unlimited_listings', true );

$featured_listings = get_post_meta( get_the_ID(), 'hm_settings_featured_listings', true );

//Experiences
$experiences_included = $unlimited_experiences = $experiences_included =  $featured_experiences = '';
if($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'experiences'){
    $experiences_included = get_post_meta( get_the_ID(), 'hm_settings_experiences_included', true );
    $experiences_included = $experiences_included > 0 ? $experiences_included : 0;
    $unlimited_experiences = get_post_meta( get_the_ID(), 'hm_settings_unlimited_experiences', true );
    $experiences_included = !empty($unlimited_experiences) ? esc_html__('Unlimited Experiences', 'homey'): $experiences_included;
    $featured_experiences = get_post_meta( get_the_ID(), 'hm_settings_featured_experiences', true );
}
//Experiences

$package_price_currency = '';
$is_package_free = get_post_meta(get_the_ID(), 'hm_settings_free_package', true);

if($is_package_free == 'on') {
    $package_total_price = homey_formatted_price(0);
    $package_price_with_currency = homey_formatted_price(0, true);
    $package_price = 0;
}else{
    $package_total_price = homey_formatted_price(get_post_meta(get_the_ID(), 'hm_settings_package_price', true));
    $package_price = get_post_meta(get_the_ID(), 'hm_settings_package_price', true);
}

/*
$currency_maker = currency_maker(false, false);
$listings_currency = $currency_maker['currency'];

$package_total_price = $package_price = homey_formatted_price(get_post_meta( get_the_ID(), 'hm_settings_package_price', true ));
$package_total_price = $package_price =  explode($listings_currency, $package_price);

if(isset($package_price[1])){
    $package_total_price = $package_price = $package_price[1];
}else{
    $package_total_price = $package_price = 0;
}
*/


$stripe_id = get_post_meta( get_the_ID(), 'hm_settings_stripe_id', true );
$visibility = get_post_meta( get_the_ID(), 'hm_settings_visibility', true );
$images_per_listing = get_post_meta( get_the_ID(), 'hm_settings_images_per_listing', true );
$unlimited_images = get_post_meta( get_the_ID(), 'hm_settings_unlimited_images', true );
$taxes = get_post_meta( get_the_ID(), 'hm_settings_taxes', true );
$popular_featured = get_post_meta( get_the_ID(), 'hm_settings_popular_featured', true );
$custom_link = get_post_meta( get_the_ID(), 'hm_settings_custom_link', true );
$detail_link = get_post_permalink(get_the_ID());
$membership_settings = get_option('hm_memberships_options');
$currency = isset($membership_settings['currency'])?$membership_settings['currency']:'USD';
?>
<div class="price-table-module">
    <div class="price-table-title"><?php the_title(); ?></div><!-- price-table-title -->
    <div class="price-table-price-wrap">
        <?php echo $package_price; //membership_currency_table($currency, $package_price, ',') ?>
    </div><!-- price-table-price-wrap -->
    <div class="price-table-description">
        <ul class="list-unstyled">
            <li>
                <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Time Period', 'homey'); ?>: <strong><?php echo esc_attr($billing_frequency).' '.esc_html__(esc_attr(ucfirst($billing_period)), 'homey'); ?></strong>
            </li>
            <?php if($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'listings'){ ?>
                <li>
                    <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php echo esc_html__('Listings', 'homey'); ?>: <strong><?php echo esc_attr($unlimited_listings) == 'on' ? esc_html__('Unlimited Listings', 'homey') : esc_attr($listings_included); ?></strong>
                </li>
                <li>
                    <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Featured Listings:', 'homey'); ?> <strong><?php echo esc_attr($featured_listings) < 1 ? 0 : esc_attr($featured_listings); ?></strong>
                </li>
            <?php } ?>

            <?php if($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'experiences'){ ?>
                <!--Experiences items-->
                <li>
                    <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php echo esc_html__('Experiences', 'homey'); ?>: <strong><?php echo esc_attr($unlimited_experiences) == 'on' ? esc_html__('Unlimited Experiences', 'homey') : esc_attr($experiences_included); ?></strong>
                </li>
                <li>
                    <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Featured Experiences:', 'homey'); ?> <strong><?php echo esc_attr($featured_experiences) < 1 ? 0 : esc_attr($featured_experiences); ?></strong>
                </li>
                <!--End of Experiences items-->
            <?php } ?>
            <?php $button_title = ''; if($args['currently_subscribed_id'] > -1 ){ ?>
                <?php  $expiry_date = get_post_meta($args['currently_subscribed_id'], 'hm_subscription_detail_expiry_date',true);
                $button_title = esc_html__("Expiry Date:", 'homey').$expiry_date;
            } ?>
        </ul>
    </div><!-- price-table-description -->
    <div class="price-table-button">
        <?php $subscription_info = get_active_membership_plan();
        $is_expired_package = 0;
        if(isset($subscription_info['subscriptionExpiryDate'])){
            if(!empty($subscription_info['subscriptionExpiryDate'])){
                $expiry_date_unix = strtotime(str_replace('/', ' ', $subscription_info['subscriptionExpiryDate']));
                if($expiry_date_unix < strtotime(date('d-m-Y'))){
                    $is_expired_package = 1;
                }
            }
        }

        ?>
        <?php $plan_message = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? esc_html__('Your Active Plan', 'homey') : esc_html__('Get Started', 'homey');?>
        <?php $detail_link = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? 'javascript:void(0)': $detail_link;?>
        <?php $button_class = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? 'success': 'primary';?>
        <a class="btn btn-<?php echo esc_attr($button_class); ?>" title="<?php echo esc_attr($button_title); ?>" href="<?php echo esc_url($detail_link); ?>"><?php echo esc_attr($plan_message); ?></a>
    </div><!-- price-table-button -->
</div><!-- taxonomy-grids-module -->