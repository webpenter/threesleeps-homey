<?php
global $homey_prefix, $homey_local;

hm_exp_validity_check();
$is_membership_info = hm_exp_featured_limit_check();

$price_featured_experience = homey_option('price_featured_experience');

$upgrade_id = isset( $_GET['upgrade_id'] ) ? $_GET['upgrade_id'] : '';

$terms_conditions = homey_option('payment_terms_condition');
$allowed_html_array = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    )
);
$enable_paypal = homey_option('enable_paypal');
$enable_stripe = homey_option('enable_stripe');
$enable_wireTransfer = '';

$is_upgrade = 0; $experience_id = '';
if( !empty( $upgrade_id ) ) {
    $is_upgrade = 1;
    $experience_id = $upgrade_id;
}

$checked_paypal = $checked_stripe = $checked_bank = $woo_commerce_gateway_class = '';
if($enable_paypal != 0 ) {
    $checked_paypal = 'checked';
} elseif( $enable_paypal != 1 && $enable_stripe != 0 ) {
    $checked_stripe = 'checked';
} elseif( $enable_paypal != 1 && $enable_stripe != 1 && $enable_wireTransfer != 0 ) {
    $checked_bank = 'checked';
} else {
    $woo_commerce_gateway_class = "homey-woocommerce-featured-pay";
}
$stripe_processor_link = homey_get_template_link('template/template-stripe-charge-exp.php');

if(isset($is_membership_info['is_allowed_membership'])){

    $remaining_featured_experience = $is_membership_info['total_allowed_featured_experience'] - $is_membership_info['current_number_experience'];
    $remaining_featured_experience = $remaining_featured_experience > 0 ? $remaining_featured_experience : 0;


    $is_experience_featured = 0;
    if ($remaining_featured_experience > 0 && isset($_GET['membership-featured'])) {
        $featured_value = $_GET['membership-featured'] > 0 ? 1 : 0;
        update_post_meta($experience_id, 'homey_featured', $featured_value);
        $is_experience_featured = get_post_meta($experience_id, 'homey_featured', true);

        $is_membership_info = hm_exp_featured_limit_check(); // again to avoid current featured experience.
        $remaining_featured_experience = $is_membership_info['total_allowed_featured_experience'] - $is_membership_info['current_number_experience'];
        $remaining_featured_experience = $remaining_featured_experience > 0 ? $remaining_featured_experience : 0;
    }

?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <form name="homey_checkout" method="post" class="homey_payment_form" action="<?php echo esc_url($stripe_processor_link); ?>">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_html__('Make This Experience Featured', 'homey'); ?></h2>
                                </div><!-- block-left -->
                            </div><!-- block-head -->

                            <div class="block-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h3><?php esc_html_e('This will use your membership package credits.', 'homey'); ?></h3>
                                    </div>
                                </div>
                            </div>

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_html__('Make Featured', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul>
                                                <li>
                                                    <?php esc_html_e('Upgrade to featured', 'homey'); ?>
                                                    <span><?php echo esc_html__('You are allowed to make total', 'homey'); ?> <?php echo $is_membership_info['total_allowed_featured_experience']; ?> <?php esc_html_e('Featured experiences.', 'homey'); ?></span>
                                                </li>

                                                <li class="total">
                                                    <div class="payment-list-price-detail clearfix">
                                                        <div class="pull-left">
                                                            <div class="payment-list-price-detail-total-price"><?php $remaining_featured_experience = $is_membership_info['total_allowed_featured_experience'] - $is_membership_info['current_number_experience'];  esc_html_e('Remaining Featured Limit:', 'homey'); echo ' '; esc_html_e($remaining_featured_experience, 'homey'); ?></div>
                                                        </div>
                                                        <div class="pull-right text-right">
                                                            <div class="payment-list-price-detail-total-price"><?php esc_html_e('Total Consumed', 'homey'); ?> <?php echo $is_membership_info['current_number_experience']; ?></div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div><!-- payment-list -->
                                        <div class="row">
                                            <?php if($is_experience_featured > 0 ) { ?>
                                                <a href="?dpage=upgrade_featured_exp&upgrade_id=<?php echo $experience_id; ?>&membership-featured=0" <?php echo $remaining_featured_experience == 0 ? 'disabled=""disabled' : ''; ?> class="btn btn-danger btn-large center" title="<?php echo esc_html__('Remove From Featured', 'homey'); ?>"><?php echo esc_html__('Remove From Featured', 'homey'); ?></a>

                                            <?php }else{ ?>
                                                <a href="?dpage=upgrade_featured_exp&upgrade_id=<?php echo $experience_id; ?>&membership-featured=1" <?php echo $remaining_featured_experience == 0 ? 'disabled=""disabled' : ''; ?> class="btn btn-success btn-large center" title="<?php echo esc_html__('Make Featured', 'homey'); ?>"><?php echo esc_html__('Make Featured', 'homey'); ?></a>
                                            <?php } ?>

                                        </div>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->
                        </div><!-- .block -->
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
        </form>
    </div><!-- .dashboard-content-area -->

    <aside class="dashboard-sidebar">
        <div class="item-grid-view">
            <?php get_template_part('template-parts/dashboard/sidebar-experience'); ?>
        </div>
    </aside><!-- .dashboard-sidebar -->

</div><!-- .user-dashboard-right -->
<?php }else{ ?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <form name="homey_checkout" method="post" class="homey_payment_form" action="<?php echo esc_url($stripe_processor_link); ?>">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_html__('Select the payment method', 'homey'); ?></h2>
                                </div><!-- block-left -->
                            </div><!-- block-head -->

                            <div class="block-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?php if($price_featured_experience < 1 && homey_is_admin()){
                                            echo esc_html__('Please set the featured price in homey options > payment gateways > Make Experience Featured, currently it is less then 1.', 'homey');
                                        } ?>
                                        <div class="payment-method">
                                            <?php
                                                if( $enable_paypal != 0 && $price_featured_experience < 1) {
                                                    echo esc_html__('Error, price should be greater than zero.', 'homey');
                                                }
                                            if( $enable_paypal != 0 && $price_featured_experience > 0) { ?>
                                            <div class="payment-method-block paypal-method">
                                                <div class="form-group">
                                                    <label class="control control--radio radio-tab">
                                                        <input type="radio" class="payment-paypal homey_check_gateway" name="homey_payment_type" value="paypal" <?php echo esc_html($checked_paypal);?>>
                                                        <span class="control-text"><?php echo esc_html__('Paypal', 'homey'); ?></span>
                                                        <span class="control__indicator"></span>
                                                        <span class="radio-tab-inner"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <?php if( $enable_stripe != 0 ) { ?>
                                            <div class="payment-method-block stripe-method">
                                                <div class="form-group">
                                                    <label class="control control--radio radio-tab">
                                                        <input type="radio" class="payment-stripe homey_check_gateway" name="homey_payment_type" value="stripe" <?php echo esc_html($checked_stripe);?>>
                                                        <span class="control-text"><?php echo esc_html__('Stripe', 'homey'); ?></span>
                                                        <span class="control__indicator"></span>
                                                        <span class="radio-tab-inner"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_html__('Payment', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul>
                                                <li>
                                                    <?php esc_html_e('Upgrade to featured', 'homey'); ?>
                                                    <span><?php echo homey_formatted_price($price_featured_experience); ?></span>
                                                </li>
                                                
                                                <li class="total">
                                                    <div class="payment-list-price-detail clearfix">
                                                        <div class="pull-left">
                                                            <div class="payment-list-price-detail-total-price"><?php esc_html_e('Total', 'homey'); ?></div>
                                                        </div>
                                                        <div class="pull-right text-right">
                                                            <div class="payment-list-price-detail-total-price"><?php echo homey_formatted_price($price_featured_experience); ?></div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div><!-- payment-list -->
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->
                        </div><!-- .block -->

                        <?php
                        if( $enable_stripe != 0 && $price_featured_experience < 1) {
                            echo esc_html__('Error, price should be greater than zero.', 'homey');
                        }

                        if( $enable_stripe != 0 && $price_featured_experience > 0) {
                            homey_exp_stripe_payment_for_featured();
                        }
                        ?>

                        <div id="without_stripe" class="payment-buttons">
                            <input type="hidden" id="experience_id" name="experience_id" value="<?php echo intval( $experience_id ); ?>">
                            <input type="hidden" id="is_upgrade" name="is_upgrade" value="<?php echo intval($is_upgrade); ?>">
                            <button id="homey_complete_order_exp" data-listid="<?php echo intval( $experience_id ); ?>" class="<?php echo $woo_commerce_gateway_class; ?> btn btn-success btn-full-width"><?php echo esc_html__('Process Payment', 'homey'); ?></button>

                        </div>
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
        </form>
    </div><!-- .dashboard-content-area -->

    <aside class="dashboard-sidebar">
        <div class="item-grid-view">

            <?php get_template_part('template-parts/dashboard/sidebar-experience'); ?>

        </div>
    </aside><!-- .dashboard-sidebar -->
    
</div><!-- .user-dashboard-right -->
<?php } ?>