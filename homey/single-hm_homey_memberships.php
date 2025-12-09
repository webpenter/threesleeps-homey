<?php
/**
 * The Template for displaying all single posts for hm membership
 * @since Homey 1.6.3
 */
get_header();
global $post, $homey_local;

$hm_options = get_option('hm_memberships_options');
$postID = isset($post->ID) ? $post->ID : get_the_ID();

$is_package_free = get_post_meta($postID, 'hm_settings_free_package', true);

if (!isset($hm_options['paypal_client_id'])) {
    echo '<h1 id="plugin_setting_error_msg" class="plugin_setting_error_msg" style="text-align: center; margin-top: 20px;color:red;">' . esc_html__('Membership plugin is missing the main settings, please contact to the admin for this issue.', 'homey') . '</h1>';
    dd(esc_html__('Not able to process further', 'homey'));
}

#region initvariables
//<editor-fold desc="init all variables">
$homeyPlugin = PLUGINDIR . "/homey-membership";

$uri = explode('/', $_SERVER['REQUEST_URI']);

define('PAYPAL_CLIENT_ID', $hm_options['paypal_client_id']);

// get access token
$is_paypal_live = $hm_options['paypal_status'];
$host = 'https://api-m.sandbox.paypal.com';
$access_token = '';
if ($is_package_free != 'on' && $is_paypal_live != 'disabled' && false != $hm_options) {
    // Check if paypal live
    if ($is_paypal_live == 'live') {
        $host = 'https://api-m.paypal.com';
    }

    $url = $host . '/v1/oauth2/token';
    $postArgs = 'grant_type=client_credentials';
    $access_token = homey_getMethodPaypalAccessToken($url, $postArgs, $hm_options['paypal_client_id'], $hm_options['paypal_sk']);
}
// end of getting access token

$baseUrl = sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],
    '/' . $uri[1]
);
$baseUrl .= '/';
$membership_settings = get_option('hm_memberships_options');
$currency = isset($membership_settings['currency']) ? $membership_settings['currency'] : 'USD';
$terms_conditions = homey_option('login_terms_condition');
$enable_password = homey_option('enable_password');
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');

$post_title = $post->post_title;

$billing_period = get_post_meta($postID, 'hm_settings_bill_period', true);
$billing_frequency = get_post_meta($postID, 'hm_settings_billing_frequency', true);

$listings_included = get_post_meta($postID, 'hm_settings_listings_included', true);
$listings_included = $listings_included > 0 ? $listings_included : 0;
$unlimited_listings = get_post_meta($postID, 'hm_settings_unlimited_listings', true);
$featured_listings = get_post_meta($postID, 'hm_settings_featured_listings', true);

$experiences_included = get_post_meta($postID, 'hm_settings_experiences_included', true);
$experiences_included = $experiences_included > 0 ? $experiences_included : 0;

$unlimited_experiences = get_post_meta($postID, 'hm_settings_unlimited_experiences', true);
$featured_experiences = get_post_meta($postID, 'hm_settings_featured_experiences', true);

$stripe_package_id = get_post_meta($postID, 'hm_settings_stripe_package_id_' . $currency, true);

$visibility = get_post_meta($postID, 'hm_settings_visibility', true);
$images_per_listing = get_post_meta($postID, 'hm_settings_images_per_listing', true);
$unlimited_images = get_post_meta($postID, 'hm_settings_unlimited_images', true);

$tax_id_stripe = get_post_meta($postID, 'hm_settings_tax_id_stripe', true);
$tax_id_paypal = get_post_meta($postID, 'hm_settings_tax_id_paypal', true);

$popular_featured = get_post_meta($postID, 'hm_settings_popular_featured', true);
$custom_link = get_post_meta($postID, 'hm_settings_custom_link', true);

$package_total_price = homey_formatted_price(get_post_meta($postID, 'hm_settings_package_price', true));

if ($is_package_free == 'on') {
    $package_total_price = homey_formatted_price(0);
    $package_price = 0;
} else {
    $package_total_price = homey_formatted_price(get_post_meta($postID, 'hm_settings_package_price', true));
    $package_price = get_post_meta($postID, 'hm_settings_package_price', true);
}
$pkg_listing_or_experience = get_post_meta( get_the_ID(), 'hm_settings_pkg_listing_or_experience', true );
if(is_null(trim($pkg_listing_or_experience))){ $pkg_listing_or_experience = 'both';}

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
/*
$package_price_currency = '';

$currency_maker = currency_maker(false, false);
$listings_currency = $currency_maker['currency'];

$package_total_price = $package_price = homey_formatted_price(get_post_meta($postID, 'hm_settings_package_price', true));
$package_total_price = $package_price =  explode($listings_currency, $package_price);

if(isset($package_price[1])){
    $package_total_price = $package_price = $package_price[1];
}else{
    $package_total_price = $package_price = 0;
}
*/
$hm_stripe_plan_id = '';
$enable_paypal = $hm_options['paypal_status'];
$enable_stripe = $hm_options['stripe_status'];
$stripe_processor_link = homey_get_template_link('template/template-membership-webhook.php');
$paypal_processor_link = $stripe_processor_link . '?is_homey_membership=1&payment_gateway=paypal';
//</editor-fold>
#endregion initvariables

#region stripe related code
//<editor-fold desc="Stripe Related Code">
function createStripeProductAndPlan($hm_options, $tax_id_stripe = '')
{
    $stripe_pk = $hm_options['stripe_pk'];
    //to check if already stripe product is created?
    $hm_stripe_product_id = get_option('hmStripePid_' . $stripe_pk);

    //We have to create stripe product if already not created
    if (empty($hm_stripe_product_id)) {
        $data = array(
            "name" => esc_html__(get_bloginfo('name'), 'homey'),
            "type" => "service"
        );

        if (!empty($hm_options['stripe_sk'])) {
            $stripe = new \Stripe\StripeClient(
                $hm_options['stripe_sk']
            );

            $stripe_product_info = $stripe->products->create($data);

            $hm_stripe_product_id = isset($stripe_product_info->id) ? $stripe_product_info->id : -1;
            if ($hm_stripe_product_id != -1) {
                update_option('hmStripePid_' . $stripe_pk, $hm_stripe_product_id);
            }
        }
    }

    return $hm_stripe_product_id;
}

if ($is_package_free != 'on') {
    $hm_stripe_product_id = createStripeProductAndPlan($hm_options);

//check for valid product's plan from stripe gateway, if not then try to create them.
    $hm_stripe_plan_id = get_option($postID . '_' . $hm_options['stripe_pk'] . '_' . $currency);

    if (!empty(trim($hm_stripe_product_id)) && empty(trim($hm_stripe_plan_id))) {
        //create plan on product
        $interval_unit = "month";
        if ($billing_period == "weekly") {
            $interval_unit = "week";
        }
        if ($billing_period == "daily") {
            $interval_unit = "day";
        } elseif ($billing_period == "monthly") {
            $interval_unit = "month";
        } elseif ($billing_period == "yearly") {
            $interval_unit = "year";
        }

        $stripeData = array(
            'amount' => (float)$package_price * 100,
            'currency' => $currency,
            'interval' => $interval_unit,
            'interval_count' => (int)$billing_frequency,
            'product' => $hm_stripe_product_id,
        );

        $stripe = new \Stripe\StripeClient(
            $hm_options['stripe_sk']
        );

        $productInfo = $stripe->plans->create($stripeData);

        update_option($postID . '_' . $hm_options['stripe_pk'] . '_' . $currency, $productInfo->id);
        update_post_meta($postID, 'hm_settings_stripe_package_id' . '_' . $currency, $productInfo->id);

        $hm_stripe_plan_id = $productInfo->id;
        $productInfo = '';
    }
//end for -> check for valid product's plan from stripe gateway, if not then try to create them.

}
//</editor-fold>
#endregion stripe related code

#region paypal related code
//<editor-fold desc="Paypal related code">
function createProductAndPlan($access_token, $host)
{
    //to check if already product is created?
    $hm_product_id = get_option('hm_prod_id_' . PAYPAL_CLIENT_ID);

    //We have to create paypal product if already not created
    if (empty($hm_product_id)) {
        $jsonData = json_encode(array(
            "name" => esc_html__(get_bloginfo('name'), 'homey'),
            "description" => esc_html__("For Host to buy different plans to post listings.", 'homey'),
            "type" => "SERVICE",
            "category" => "SOFTWARE",
            "image_url" => esc_html__('http://gethomey.io/wp-content/themes/homey/images/logo.png', 'homey'),
            //this could be the issue if the URL is set for localhost.
            "home_url" => homey_get_template_link('template/template-membership-webhook.php')

        ));

        $productInfo = homey_execute_curl_request($host . "/v1/catalogs/products", $jsonData, $access_token, 1);

        if (isset($productInfo['id'])) {
            update_option('hm_prod_id_' . PAYPAL_CLIENT_ID, $productInfo['id']);
            $hm_product_id = $productInfo['id'];
        }
    }
    return $hm_product_id;
}

//to check if already product is created?
if (!empty($access_token)) {
    $hm_product_id = createProductAndPlan($access_token, $host);

    //check for valid product's plan from gateways, if not then try to create them.
    $hm_plan_id = get_option($postID . '_' . PAYPAL_CLIENT_ID . '_' . strtoupper($currency));

    if (!empty(trim($hm_product_id)) && empty(trim($hm_plan_id))) {
        //create plan on product
        $interval_unit = "MONTH";
        if ($billing_period == "daily") {
            $interval_unit = "DAY";
        }
        if ($billing_period == "weekly") {
            $interval_unit = "WEEK";
        } elseif ($billing_period == "monthly") {
            $interval_unit = "MONTH";
        } elseif ($billing_period == "yearly") {
            $interval_unit = "YEAR";
        }

        $payPalData = array(
            'product_id' => $hm_product_id,
            'name' => $post_title,
            'description' => $post_title . ' package plan will allow you to create ' . $listings_included . ' numbers of listings.',
            'status' => 'ACTIVE',
            'billing_cycles' => array(
                array(
                    'frequency' => array(
                        'interval_unit' => $interval_unit,
                        'interval_count' => (int)$billing_frequency,
                    ),
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' =>
                        array(
                            'fixed_price' =>
                                array(
                                    'value' => (float)$package_price,
                                    'currency_code' => strtoupper($currency),
                                ),
                        ),
                ),
            ),
            'payment_preferences' => array(
                'auto_bill_outstanding' => true,
                'setup_fee' =>
                    array(
                        'value' => 0,
                        'currency_code' => strtoupper($currency),
                    ),
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ),
            'taxes' =>
                array(
                    'percentage' => (float)$tax_id_paypal,
                    'inclusive' => false,
                ),
        );

//        echo '<pre>'; print_r($payPalData);exit;

        $jsonData = json_encode($payPalData);

        $productInfo = homey_execute_curl_request($host . "/v1/billing/plans", $jsonData, $access_token);
        update_option($postID . '_' . PAYPAL_CLIENT_ID, $productInfo['id'] . '_' . strtoupper($currency));
        update_post_meta($postID, 'hm_settings_paypal_package_id', $productInfo['id']);
        $hm_plan_id = $productInfo['id'];
    }
}
//end for -> check for valid product's plan from gateways, if not then try to create them.
//</editor-fold>
#endregion paypal related code
?>
    <!--<editor-fold desc="html section">-->
    <section class="main-content-area">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="page-title">
                        <div class="block-top-title">
                            <?php get_template_part('template-parts/breadcrumb'); ?>
                        </div><!-- block-top-title -->
                    </div><!-- page-title -->
                </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
            </div><!-- .row -->
        </div><!-- .container -->
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="membership-package-order-detail">
                        <div class="block">
                            <div class="block-body">
                                <h3><?php echo esc_html__("Membership Package", 'homey'); ?></h3>
                                <ul class="list-unstyled mebership-list-info">
                                    <li><i class="homey-icon homey-icon-check-circle-1"
                                           aria-hidden="true"></i> <?php echo esc_html__("Package", 'homey'); ?>
                                        <strong><?php the_title(); ?></strong></li>
                                    <li><i class="homey-icon homey-icon-check-circle-1"
                                           aria-hidden="true"></i> <?php echo esc_html__("Price", 'homey'); ?>
                                        <strong><?php if (function_exists('membership_currency')) {
                                                echo membership_currency($currency, $package_price, ',');
                                            } else {
                                                echo homey_formatted_price($package_price);
                                            } ?></strong></li>
                                    <li><i class="homey-icon homey-icon-check-circle-1"
                                           aria-hidden="true"></i> <?php echo esc_html__("Time Period", 'homey'); ?>
                                        <strong><?php echo $billing_frequency . ' ' . homey_custom_invoice_translate_title_do($billing_period); ?></strong>
                                    </li>

                                    <?php if ($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'listings') { ?>
                                        <li><i class="homey-icon homey-icon-check-circle-1"
                                               aria-hidden="true"></i> <?php echo esc_html__("Number of Listings", "homey"); ?>
                                            <strong><?php echo $unlimited_listings == 'on' ? esc_html__('Unlimited Listings', 'homey') : $listings_included; ?></strong>
                                        </li>
                                        <li><i class="homey-icon homey-icon-check-circle-1"
                                               aria-hidden="true"></i> <?php echo esc_html__("Number of Featured Listings", "homey"); ?>
                                            <strong><?php echo $featured_listings < 1 ? 0 : $featured_listings; ?></strong>
                                        </li>
                                    <?php } ?>

                                    <?php if ($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'experiences') { ?>
                                        <!--Experiences-->
                                        <li><i class="homey-icon homey-icon-check-circle-1"
                                               aria-hidden="true"></i> <?php echo esc_html__("Number of Experiences", "homey"); ?>
                                            <strong><?php echo $unlimited_experiences == 'on' ? esc_html__('Unlimited Experiences', 'homey') : $experiences_included; ?></strong>
                                        </li>
                                        <li><i class="homey-icon homey-icon-check-circle-1"
                                               aria-hidden="true"></i> <?php echo esc_html__("Number of Featured Experiences", "homey"); ?>
                                            <strong><?php echo $featured_experiences < 1 ? 0 : $featured_experiences; ?></strong>
                                        </li>
                                        <!--End of Experiences items-->
                                    <?php } ?>
                                    <!--                                    <li><i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> -->
                                    <?php //echo esc_html__("Taxes", 'homey'); ?><!-- <strong>-->
                                    <?php //echo $taxes; ?><!--%</strong></li>-->
                                    <li class="total-price"><?php echo esc_html__("Total Price", 'homey'); ?>
                                        <strong><?php echo $package_total_price; ?></strong></li>
                                </ul>

                            </div>
                        </div>
                    </div><!-- membership-package-order-detail -->
                    <div class="text-center">
                        <a href="<?php echo homey_get_template_link('template/template-membership-webhook.php'); ?>"><?php echo esc_html__("Change Package", 'homey'); ?></a>
                    </div>
                </div>


                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                    <div class="membership-package-wrap">
                        <?php if (!is_user_logged_in()): ?>
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_html__("Account Information", 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <?php echo esc_html__("Already have an account?", 'homey'); ?> <a href="#"
                                                                                                          id="hm_membership_login_btn"
                                                                                                          data-toggle="modal"
                                                                                                          data-target="#modal-login"><?php echo esc_html__(esc_attr($homey_local['login_text'], 'homey')); ?></a>
                                    </div><!-- block-right -->
                                </div>
                                <div class="block-body">
                                    <div class="row">
                                        <div class="error-message error" id="hm_register_msgs"></div>
                                    </div>
                                    <div class="row">
                                        <!--<div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input class="form-control is-valid" placeholder="Enter your first name" type="text">
                                            </div>
                                        </div>--><!-- col-md-6 col-sm-12 -->
                                        <!--<div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input class="form-control is-invalid" placeholder="Enter your last name" type="text">
                                            </div>
                                        </div>--><!-- col-md-6 col-sm-12 -->
                                        <form id="memberships_register_form">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label><?php echo esc_html__("Username", 'homey'); ?></label>
                                                    <input name="username" type="text"
                                                           class="form-control email-input-1"
                                                           placeholder="<?php esc_html_e('Username', 'homey'); ?>"/>
                                                </div>
                                            </div><!-- col-md-6 col-sm-12 -->
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label><?php echo esc_html__("Email", 'homey'); ?></label>
                                                    <input type="useremail" name="useremail"
                                                           class="form-control email-input-1"
                                                           placeholder="<?php echo esc_html__('Email', 'homey'); ?>">
                                                </div>
                                            </div><!-- col-md-6 col-sm-12 -->
                                            <?php if ($enable_password == 'yes') { ?>
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="form-group">
                                                        <label><?php echo esc_html__("Password", 'homey'); ?></label>
                                                        <input name="register_pass" type="password"
                                                               class="form-control password-input-1"
                                                               placeholder="<?php echo esc_html__('Password', 'homey'); ?>">
                                                    </div>
                                                </div><!-- col-md-6 col-sm-12 -->
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="form-group">
                                                        <label><?php echo esc_html__("Confirm Password", "homey"); ?></label>
                                                        <input name="register_pass_retype" type="password"
                                                               class="form-control password-input-2"
                                                               placeholder="<?php echo esc_html__('Repeat Password', 'homey'); ?>">
                                                    </div>
                                                </div><!-- col-md-6 col-sm-12 -->
                                            <?php } ?>

                                            <?php wp_nonce_field('homey_register_nonce', 'homey_register_security'); ?>
                                            <input type="hidden" id="action" name="action" value="homey_register">
                                            <input type="hidden" id="role" name="role" value="homey_host">

                                            <div class="col-md-6 col-sm-12 checkbox pull-left">
                                                <?php if ($enable_forms_gdpr != 0) { ?>
                                                    <label>
                                                        <input name="privacy_policy" type="checkbox">
                                                        <?php echo wp_kses($forms_gdpr_text, homey_allowed_html()); ?>
                                                    </label>
                                                <?php } ?>

                                                <label>
                                                    <input required name="term_condition"
                                                           type="checkbox"> <?php echo sprintf(wp_kses(__('I agree with your <a href="%s">Terms & Conditions</a>', 'homey'), homey_allowed_html()), get_permalink($terms_conditions)); ?>
                                                    &nbsp;
                                                </label>
                                            </div>

                                            <?php get_template_part('template-parts/google', 'reCaptcha'); ?>

                                            <div class="col-md-6 col-sm-12 pull-left">
                                                <button id="plugin_register_btn" type="button"
                                                        class="hm_membership_register btn btn-primary"><?php echo esc_html__("Register", "homey"); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- block-body -->
                            </div><!-- block -->
                        <?php else: ?>
                            <div class="block">
                                <div class="block-head table-block">
                                    <h2 class="title"><?php echo esc_attr($homey_local['payment_label']); ?></h2>
                                </div>
                                <div class="block-body">
                                    <!--                                <form name="homey_checkout" method="post" class="homey_payment_form" action="-->
                                    <?php //echo esc_url($stripe_processor_link); ?><!--">-->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h3><?php echo esc_attr($homey_local['select_payment']); ?></h3>
                                            <div class="payment-method">
                                                <?php if ($is_package_free != 'on' && $enable_paypal != 'disabled' && false != $hm_options) { ?>
                                                    <div class="payment-method-block paypal-method">
                                                        <!-- Buy button -->
                                                        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&vault=true&disable-funding=credit,card"></script>
                                                        <script>
                                                            paypal.Buttons({
                                                                createSubscription: function (data, actions) {
                                                                    return actions.subscription.create({
                                                                        'plan_id': '<?php echo $hm_plan_id; ?>'
                                                                    });
                                                                },
                                                                onApprove: function (data, actions) {
                                                                    //console.log(data);
                                                                    jQuery("#paypal-button-container").html("<p class='btn btn-primary btn-block'><?php esc_html_e('Redirecting to Invoice Page.', 'homey'); ?></p>");
                                                                    window.location.href = '<?php echo $paypal_processor_link . '&hm_planID=' . $postID;?>&success=1&subscriptionID=' + data.subscriptionID
                                                                },
                                                                style: {
                                                                    size: 'rect',
                                                                    color: 'blue'
                                                                }
                                                            }).render('#paypal-button-container');
                                                        </script>

                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway"
                                                                       name="payment_gateway" value="paypal"
                                                                       type="radio">
                                                                <span class="control-text"><?php esc_html_e('Paypal', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php if ($is_package_free != 'on' && $enable_stripe != 'disabled' && false != $hm_options) { ?>
                                                    <div class="payment-method-block stripe-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway"
                                                                       name="payment_gateway" value="stripe"
                                                                       type="radio">
                                                                <span class="control-text"><?php esc_html_e('Stripe', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php if ($is_package_free == 'on') { ?>
                                                    <div class="payment-method-block free-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway"
                                                                       name="payment_gateway" value="free"
                                                                       type="radio">
                                                                <span class="control-text"><?php esc_html_e('Free', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <form action="<?php echo $stripe_processor_link; ?>"
                                                          id="free-package" name="free-package" method="get">
                                                        <input type="hidden" name="is_homey_membership" value="1">
                                                        <input type="hidden" name="payment_gateway" value="free">
                                                        <input type="hidden" name="postId"
                                                               value="<?php echo $postID; ?>">
                                                        <input type="hidden" name="planId"
                                                               value="<?php echo $postID; ?>">
                                                    </form>
                                                <?php } ?>

                                                <?php if ($is_package_free != 'on' && $hm_options['wcomm_status'] != 'disabled' && false != $hm_options) { ?>
                                                    <div class="payment-method-block woo-commerce-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway"
                                                                       name="payment_gateway" value="woocom"
                                                                       type="radio">
                                                                <span class="control-text"><?php esc_html_e('Woo Commerce', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if ($enable_stripe != 'disabled' && false != $hm_options) {
                                        ?>
                                        <!--    <link href="--><?php //echo $baseUrl.$homeyPlugin;?><!--/assets/css/style.css" type="text/css" rel="stylesheet" />-->
                                        <script src="https://js.stripe.com/v3/"></script>
                                        <script src="<?php echo get_site_url(null, $homeyPlugin . '/assets/js/homey_membership_stripe.js') ?>"></script>

                                        <div id="error-message"></div>
                                        <input name="basePluginUrl" id="basePluginUrl"
                                               value="<?php echo get_site_url('', $homeyPlugin); ?>" type="hidden">


                                    <?php } ?>
                                    <div style="display: none;" id="paypal-button-container"></div>
                                    <button id="homey_membership_payment" type="button"
                                            class="btn btn-primary btn-block"><?php echo esc_attr($homey_local['btn_process_pay']); ?></button>
                                    <!--                                </form>-->
                                    <p class="error text-danger" id="response_statusText"></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div><!-- membership-package-wrap -->
                </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->

            </div><!-- .row -->
        </div>   <!-- .container -->
        <?php
        #region not logged in user html
        if (is_user_logged_in()): ?>
            <script>
                jQuery(document).ready(function () {
                    var stripe = '';
                    <?php  if( $enable_stripe != 'disabled' && false != $hm_options) { ?>
                    stripe = Stripe('<?php echo $hm_options['stripe_pk']; ?>');
                    <?php } ?>
                    //Setup event handler to create a Checkout Session when button is clicked

                    jQuery('input[name="payment_gateway"]').click(function () {
                        jQuery("#paypal-button-container").hide();
                        jQuery("#homey_membership_payment").show();

                        var selectedGateway = document.querySelector('input[name="payment_gateway"]:checked').value;
                        //alert(selectedGateway);
                        if (selectedGateway == 'paypal') {
                            jQuery("#homey_membership_payment").hide();
                            jQuery("#paypal-button-container").show();
                        }
                    });

                    document.getElementById("homey_membership_payment").addEventListener("click", function (evt) {
                        var selectedGateway = document.querySelector('input[name="payment_gateway"]:checked').value;

                        jQuery(this).text('please wait..');
                        jQuery(this).attr('disabled', 'disabled');

                        if (selectedGateway == 'paypal') {
                            // document.getElementById("paypal-form").submit();
                        }//paypal
                        else if (selectedGateway == 'free') {
                            document.getElementById("free-package").submit();
                        }//paypal

                        else if (selectedGateway == 'stripe') {
                            jQuery.ajax({
                                url: '<?php echo get_admin_url() . 'admin-ajax.php'; ?>',
                                dataType: 'JSON',
                                method: 'POST',
                                data: {
                                    'is_homey_membership': 1,
                                    'stripe_processor_link': '<?php echo $stripe_processor_link; ?>',
                                    'planId': '<?php echo @$hm_stripe_plan_id; ?>',
                                    'currency': '<?php echo $currency; ?>',
                                    'postID': '<?php echo $postID; ?>',
                                    'tax_id_stripe': '<?php echo $tax_id_stripe; ?>',
                                    'action': 'stripe_membership_sessions',
                                },
                                success: function (result) {
                                    stripe.redirectToCheckout({
                                        sessionId: result.id
                                    }).then(handleResult)
                                        .error(function (e) {
                                            alert(e.statusText);
                                            jQuery("#response_statusText").text(e.statusText);
                                            console.log(e);
                                        });
                                },
                                error: function (xhr, status, error) {
                                    var err = eval("(" + xhr.responseText + ")");
                                    console.log(err.Message);
                                }
                            });
                        } else if (selectedGateway == 'woocom') {
                            // e.preventDefault();
                            //homey_processing_modal( processing_text );
                            var jQuerythis = jQuery(this);
                            var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
                            var package_id = <?php echo $postID; ?>;

                            jQuery.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {
                                    'action': 'homey_membership_woo_pay',
                                    'package_id': package_id
                                },
                                success: function (data) {
                                    if (data.success != false) {
                                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url + '?package_id=' + package_id;
                                        window.location.href = urlWithGetVars;
                                    } else {
                                        jQuery('#homey_modal').modal('hide');
                                    }
                                },
                                error: function (errorThrown) {
                                }
                            }); // jQuery.ajax
                        } else {
                            alert('please select payment gateway methods');
                        }

                    });
                });
            </script>
        <?php endif;
        #endregion not logged in user
        ?>

    </section><!-- main-content-area listing-page grid-listing-page -->
    <!--</editor-fold>-->

<?php
get_footer();
