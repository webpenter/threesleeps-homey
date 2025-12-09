<?php
/**
 * Template Name: Homey Membership Subscriptions Webhook
 */
// Get the current URL
$current_url = $_SERVER['REQUEST_URI'];

// Parse the URL and query parameters
$parsed_url = parse_url($current_url);
$params = array();
if(isset($parsed_url['query'])){
    parse_str($parsed_url['query'], $params);
}

get_header();
global $homey_local, $homey_prefix, $wpdb;

//<editor-fold desc="init variables code">
require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');
define('PAYPAL_SANDBOX', true);
$hm_options = get_option('hm_memberships_options');
$currency = isset($hm_options['currency'])?$hm_options['currency']:'USD';

//$paypal_client_id = 'AZxYj4mbzgBKtc42DgzUImYDnfVfMnVGD3eY03lSloBSbBhZ1PreAqKgF_eyGaFC14pmrAFIa3w5WUrl';//we will get from db
//$paypal_client_secret = 'EGaSettT-AqLz7C-DojJEp4GiMmKBaxAtcrMvbOzQnT5mKcfdiJSh8JynAS7NtwcpgBUxx_hNm3lkSeN';//we will get from db

$paypal_client_id = @$hm_options['paypal_client_id'];
$paypal_client_secret = @$hm_options['paypal_sk'];

define('PAYPAL_URL', (PAYPAL_SANDBOX == true) ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr");
define('PAYPAL_CLIENT_ID', $paypal_client_id);
define('PAYPAL_CLIENT_SECRET', $paypal_client_id);

$allowed_html = array();
$paymentMethod = '';

$current_user = wp_get_current_user();
$userID = $current_user->ID;
$user_email = $current_user->user_email;
$admin_email = get_bloginfo('admin_email');
$username = $current_user->user_login;

$date = date('Y-m-d G:i:s', current_time('timestamp', 0));

$payload = @file_get_contents('php://input');
$postID = -1;

if(isset($params['postId'])){
    $postID = $params['postId'];
}

if(isset($params['hm_planID'])){
    $postID = $params['hm_planID'];
}

$is_paypal_live = @$hm_options['paypal_status'];
$paypal_host = $access_token = '';
if ($is_paypal_live != 'disabled'){
    $paypal_host = get_payment_api_url('paypal', $is_paypal_live);

    $url = $paypal_host . '/v1/oauth2/token';
    $postArgs = 'grant_type=client_credentials';
    $access_token = homey_getMethodPaypalAccessToken($url, $postArgs, $paypal_client_id, $paypal_client_secret);
}

$create_listing_link = homey_get_template_link('template/dashboard-submission.php');
$create_experience_link = homey_get_template_link('template/dashboard-experience-submission.php');
//</editor-fold>

//<editor-fold desc="Webhook related code">

if (!empty($payload)) {
    if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) { //if payload is from stripe or not
        //handle here stripe webhook function
        handle_hm_stripe_webhook($payload, $current_user, $date);
        http_response_code(200);
        exit();
    } else {//check if valid paypal payload or not
        //handle here paypal webhook function
        handle_hm_paypal_webhook($payload, $current_user, $date);
        http_response_code(200);
        exit();
    }
}

function handle_hm_stripe_webhook($payload = null, $current_user='', $date='')
{
    global $wpdb;
    //for stripe webhooks
    //$payload = @file_get_contents('php://input');
    $event = null;

    try {
        $event = \Stripe\Event::constructFrom(
            json_decode($payload, true)
        );
    } catch(\UnexpectedValueException $e) {
        // Invalid payload
        http_response_code(400);
        exit();
    }

// Handle the event
    switch ($event->type) {
        case 'invoice.payment_succeeded':
            $stripeInvoiceInfo = $event->data->object; // contains a \Stripe\PaymentIntent

            $latest_invoice  = $stripeInvoiceInfo->id;
            $subscription_id = $stripeInvoiceInfo->subscription;
            // $purchase_date   = $stripeInvoiceInfo->period_start;

            $purchase_date   = isset($stripeInvoiceInfo->lines->data[0]->period->start) ? $stripeInvoiceInfo->lines->data[0]->period->start : $stripeInvoiceInfo->period_start;
            $expiry_date     = isset($stripeInvoiceInfo->lines->data[0]->period->end) ? $stripeInvoiceInfo->lines->data[0]->period->end : $stripeInvoiceInfo->period_end;

            $amount_paid     = isset( $stripeInvoiceInfo->amount_paid ) ? (float) $stripeInvoiceInfo->amount_paid / 100 : 0 ;

            $tbl = $wpdb->prefix.'postmeta';
            $prepare_guery = $wpdb->prepare( "SELECT post_id  FROM $tbl WHERE meta_key ='hm_subscription_detail_sub_id' AND meta_value = '%s'", $subscription_id );

            $posts = $wpdb->get_col( $prepare_guery );

            $totalIndex = '';

            foreach ($posts as $k => $postId){

                //$totalAllowedListings = get_post_meta($postId, 'hm_settings_listings_included', true);

                add_post_meta($postId, 'hm_subscription_detail_order_number', $latest_invoice);
                //update_post_meta($postId, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                update_post_meta($postId, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $purchase_date));
                update_post_meta($postId, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $expiry_date));
                update_post_meta($postId, 'hm_subscription_detail_purchase_date_timestamp', $purchase_date);
                update_post_meta($postId, 'hm_subscription_detail_expiry_date_timestamp', $expiry_date);
                update_post_meta($postId, 'hm_subscription_detail_amount_paid', $amount_paid);
                update_post_meta($postId, 'hm_subscription_detail_status', 'active');

                $invoiceID = homey_generate_invoice( 'package','recurring', $postId, date('d/M/Y h:i:s', $purchase_date), $current_user, 0, 0, '', 'stripe', $amount_paid );

                add_post_meta($postId, 'hm_subscription_invoice_ids', $invoiceID);

            }
        break;

        case 'invoice.payment_failed':
        case 'customer.subscription.deleted':

            $stripeInvoiceInfo = $event->data->object; // contains a \Stripe\PaymentIntent

            $latest_invoice  = $stripeInvoiceInfo->id;
            $subscription_id = $stripeInvoiceInfo->subscription;
            $purchase_date   = isset($stripeInvoiceInfo->lines->data->period->start) ? $stripeInvoiceInfo->lines->data->period->start : $stripeInvoiceInfo->period_start;
            $expiry_date     = isset($stripeInvoiceInfo->lines->data->period->end) ? $stripeInvoiceInfo->lines->data->period->end : $stripeInvoiceInfo->period_end;


        $tbl = $wpdb->prefix.'postmeta';
            $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_sub_id' and meta_value = '%s'", $subscription_id );
            $posts = $wpdb->get_col( $prepare_guery );
            clearance_membership_plan();

            $totalIndex = '';
            foreach ($posts as $k => $postId){
                //$totalAllowedListings = get_post_meta($postId, 'hm_settings_listings_included', true);

                add_post_meta($postId, 'hm_subscription_detail_order_number', $latest_invoice);
                //update_post_meta($postId, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                update_post_meta($postId, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $purchase_date));
                update_post_meta($postId, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $expiry_date));
                update_post_meta($postId, 'hm_subscription_detail_status', 'expired');
                $totalIndex .= $subscription_id.' <> '.$postId.', '. $k;
            }

            break;

        default:
            http_response_code(400);
            exit();
    }
}//end of handle_hm_stripe_webhook()

function handle_hm_paypal_webhook($payload = null, $current_user='', $date='')
{
    global $wpdb;
    $subscriptionInfo = json_decode($payload, true);

    $resource_type = $subscriptionInfo['resource_type'];
    $event_type = $subscriptionInfo['event_type'];
    $state = $subscriptionInfo['resource']['state'];

    if( $resource_type == "sale" && $event_type == "PAYMENT.SALE.COMPLETED" && $state == "completed" ) {
        // Get transaction information from URL
        $data["plan_status"]        = $subscriptionInfo['status'];
        $data["plan_id"]            = $subscriptionInfo['plan_id'];
        $data["txn_id"]             = $subscription_id = $subscriptionInfo['id'];
        $data["payer_name"]         = $subscriptionInfo['subscriber']['name']['given_name'].' '.$subscriptionInfo['subscriber']['name']['surname'];
        $data["email_address"]      = $subscriptionInfo['subscriber']['email_address'];
        $data["payer_id"]           = $subscriptionInfo['subscriber']['payer_id'];
        $data["amount_paid"]        = $subscriptionInfo['billing_info']['last_payment']['amount']['value'];
        $data["currency_code"]      = $subscriptionInfo['billing_info']['last_payment']['amount']['currency_code'];
        $data["start_time"]         = $subscriptionInfo['start_time'];
        $data["next_billing_time"]  = '';

        if(isset($subscriptionInfo['billing_info']['next_billing_time'])){
            $data["next_billing_time"]  = $subscriptionInfo['billing_info']['next_billing_time'];
        }

        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_sub_id' and meta_value = '%s'", $subscription_id );
        $posts = $wpdb->get_col( $prepare_guery );

        $totalIndex = '';
        foreach ($posts as $k => $postId){
            $totalAllowedListings = get_post_meta($postId, 'hm_settings_listings_included', true);

            add_post_meta($postId, 'hm_subscription_detail_order_number', $subscription_id);
            update_post_meta($postId, 'hm_subscription_detail_total_listings', $totalAllowedListings);
            update_post_meta($postId, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $data["start_time"]));
            update_post_meta($postId, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $data["next_billing_time"]));
            $totalIndex .= ', '. $k;

            $invoiceID = homey_generate_invoice( 'package','recurring', $postId, date('d/M/Y h:i:s', $data["start_time"]), $current_user, 0, 0, '' , 'paypal', $data["amount_paid"] );

            add_post_meta($postId, 'hm_subscription_invoice_ids', $invoiceID);
        }
        clearance_membership_plan();
    }

    if( $event_type == "BILLING.SUBSCRIPTION.EXPIRED" || $event_type == "BILLING.SUBSCRIPTION.CANCELLED" || $event_type == "PAYMENT.SALE.REFUNDED" || $event_type == "PAYMENT.SALE.REVERSED" ) {

        // Get transaction information from URL
        $subscriptionInfo           = $subscriptionInfo['resource'];

        $data["plan_status"]        = $subscriptionInfo['status'];
        $data["txn_id"]             = $subscription_id = $subscriptionInfo['id'];
        $data["start_time"]         = $subscriptionInfo['start_time'];
        $data["next_billing_time"]  = '';

        if(isset($subscriptionInfo['billing_info']['next_billing_time'])){
            $data["next_billing_time"]  = $subscriptionInfo['billing_info']['next_billing_time'];
        }

        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_sub_id' and meta_value = '%s'", $subscription_id );
        $posts = $wpdb->get_col( $prepare_guery );

        $totalIndex = '';
        if($posts){
            foreach ($posts as $k => $postId){
                update_post_meta($postId, 'hm_subscription_detail_status', $data["plan_status"]);
                update_post_meta($postId, 'hm_subscription_detail_purchase_date', date('d-m-Y h:i:s', $data["start_time"]));
                update_post_meta($postId, 'hm_subscription_detail_expiry_date', date('d-m-Y h:i:s', $data["next_billing_time"]));
                $totalIndex .= ', '. $k;
            }
            clearance_membership_plan();
        }
    }
}//end of handle_hm_paypal_webhook()

function save_membership_subscription_post($paymentMethod = null, $data=[])
{
    $current_user = wp_get_current_user();
    $all_vairables = '';
    $counter = 0;
    $postID = $params['hm_planID'];
    $hm_planID = $params['hm_planID'];
    $hm_planInfo = get_post($hm_planID);

    $memberships = array();
    $order_number = '';
    if ($paymentMethod == 'paypal') {
        $order_number = $data['txn_id'];
        if(homey_is_paypal_id_used($data['txn_id']) == 0) {
            $order_number = $paypalSubscription_ID = $data['plan_id'];//$stripeSubscriptionInfo['id'];
            $purchase_date = $data['start_time'];//$stripeSubscriptionInfo['current_period_start'];
            $expiry_date = $data['next_billing_time'];//$stripeSubscriptionInfo['current_period_end'];
            $latest_invoice = $data['txn_id'];//$stripeSubscriptionInfo['latest_invoice'];

            //get total number of allowed listings for selected plan
            $hm_settings_bill_period = get_post_meta($postID, 'hm_settings_bill_period', true);
            $totalAllowedListings    = get_post_meta($postID, 'hm_settings_listings_included', true);

            //save subscription to homey DB
            $subscriptionInfo = array(
                'post_title' => $data['email_address'],//$stripeCustomerInfo['email'],
                'post_content' => $data['email_address'].' subscribed to plan <strong>'.$hm_planInfo->post_title.'</strong>',
                'post_status' => 'publish',
                'post_author' => $current_user->ID,
                'post_type' => "hm_subscriptions"
            );

            //inserting post wp_insert_post() will return ID of inserted post
            $subscription_ID = wp_insert_post($subscriptionInfo);

            // update post meta for saved subscription in homey DB
            add_post_meta($subscription_ID, 'hm_subscription_detail_session_id', $data['txn_id']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_status', 'active');
            add_post_meta($subscription_ID, 'hm_subscription_detail_payment_gateway', $paymentMethod);
            add_post_meta($subscription_ID, 'hm_subscription_detail_order_number', $data['txn_id']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_customer_id', $data['payer_id']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_plan_id', $postID);
            add_post_meta($subscription_ID, 'hm_subscription_detail_sub_id', $data['txn_id']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_total_listings', $totalAllowedListings);
            add_post_meta($subscription_ID, 'hm_subscription_detail_remaining_listings', $totalAllowedListings);
            add_post_meta($subscription_ID, 'hm_subscription_detail_purchase_date', $data['start_time']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_expiry_date', $data['next_billing_time']);
            add_post_meta($subscription_ID, 'hm_subscription_detail_amount_paid', $data['amount_paid']);

            $start_date_time = empty(trim($data["start_time"])) ? date('d/M/Y h:i:s') : $data["start_time"];

            $invoiceID = homey_generate_invoice( 'package','recurring', $subscription_ID, strtotime($start_date_time), $current_user->ID, 0, 0, '' , $paymentMethod, $data["amount_paid"] );
            add_post_meta($subscription_ID, 'hm_subscription_invoice_ids', $invoiceID);

        }

        //end of save subscription
    }
    return $order_number;
}
//</editor-fold>

?>

<?php
if (isset($params['is_homey_membership'])) {
    $paymentMethod = isset($params['payment_gateway']) ? $params['payment_gateway'] : 'stripe';

    //<editor-fold desc=" free membership request processing">
    if ( $paymentMethod == 'free' && is_user_logged_in()) {

        $amount_paid     = 0 ;
        $customerInfo = wp_get_current_user();

        $planId = $params['planId'];
        $isAlreadySubscribed = 0; // here will create a function to check if the current user will have free plan active then user have to wait for the end.
        if ($isAlreadySubscribed == 0 && $planId > 0) {

                $totalAllowedListings = get_post_meta($planId, 'hm_settings_listings_included', true);
                $billing_frequency = get_post_meta($planId, 'hm_settings_billing_frequency', true);

                $totalRemainingListings = get_post_meta($planId, 'hm_subscription_detail_remaining_listings', true);
                $purchase_date = date('d/M/Y'); //$stripeSubscriptionInfo['current_period_start'];
                $future_timestamp = strtotime("+".$billing_frequency." month");
                $expiry_date = date('d/M/Y', $future_timestamp);

                //save subscription
                $subscriptionInfo = array(
                    'ID' => '',
                    'post_title' => $customerInfo->user_email.' ('.$customerInfo->display_name.')',
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                    'post_type' => "hm_subscriptions"
                );

                $freeInvoiceNumber = $order_number = 'free-pkg-'.rand(1111, 99999);

                clearance_membership_plan();
                // insert new package information
                $subID = wp_insert_post($subscriptionInfo);

                add_post_meta($subID, 'hm_subscription_detail_status', 'active');
                add_post_meta($subID, 'hm_subscription_detail_payment_gateway', 'free');
                add_post_meta($subID, 'hm_subscription_detail_customer_id', $customerInfo->ID);
                add_post_meta($subID, 'hm_subscription_detail_order_number', $freeInvoiceNumber);
                add_post_meta($subID, 'hm_subscription_detail_plan_id', $postID);
                add_post_meta($subID, 'hm_subscription_detail_sub_id', $freeInvoiceNumber);
                add_post_meta($subID, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                add_post_meta($subID, 'hm_subscription_detail_remaining_listings', $totalAllowedListings);
                add_post_meta($subID, 'hm_subscription_detail_purchase_date',$purchase_date);
                add_post_meta($subID, 'hm_subscription_detail_expiry_date', $expiry_date);

                //$invoiceID = homey_generate_invoice( 'package','recurring', $subID, date('d/M/Y h:i:s', $purchase_date), get_current_user_id(), 0, 0, '' , 'free', $amount_paid );
                add_post_meta($subID, 'hm_subscription_invoice_ids', $freeInvoiceNumber);
                //end of save subscription
            }
        }
    //</editor-fold>

    //<editor-fold desc=" stripe request processing after payment is paid">
    if (isset($params['is_homey_membership']) && $paymentMethod == 'stripe') {
        if (isset($params['session_id'])) {
            $stripe = new \Stripe\StripeClient(
                $hm_options['stripe_sk']
            );

            $stripeSessionInfo = $stripe->checkout->sessions->retrieve($params['session_id']);

            $isAlreadySubscribed = homey_is_stripe_id_used($params['session_id']);

            if ($isAlreadySubscribed > 0) {
                $order_number = get_post_meta($isAlreadySubscribed, 'hm_subscription_detail_order_number', true);
            }

            $amount_paid     = isset( $stripeSessionInfo->amount_total ) ? $stripeSessionInfo->amount_total / 100 : 0 ;

            $stripeCustomerInfo = $stripe->customers->retrieve($stripeSessionInfo->customer);

            if(isset($stripeSessionInfo->display_items[0])){
                $stripePlanId = $stripeSessionInfo->display_items[0]->plan->id;
            }else{
                $stripeSubscriptionInfo = $stripe->subscriptions->retrieve($stripeSessionInfo['subscription']);
//                dd($stripeSubscriptionInfo->plan->id);
                $stripePlanId = $stripeSubscriptionInfo->plan->id;
            }

            if (isset($stripeCustomerInfo->id) && $isAlreadySubscribed == 0 ) {
                if ($postID > 0) {
                    $stripePlanInfo = $stripe->plans->retrieve($stripePlanId);

                    $stripeSubscriptionInfo = $stripe->subscriptions->retrieve($stripeSessionInfo['subscription']);

                    $totalAllowedListings = get_post_meta($postID, 'hm_settings_listings_included', true);
                    $totalRemainingListings = get_post_meta($postID, 'hm_subscription_detail_remaining_listings', true);
                    $purchase_date = $stripeSubscriptionInfo['current_period_start'];
                    $expiry_date = $stripeSubscriptionInfo['current_period_end'];

                    //save subscription
                    $subscriptionInfo = array(
                        'ID' => '',
                        'post_title' => $stripeCustomerInfo['email'],
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_author' => get_current_user_id(),
                        'post_type' => "hm_subscriptions"
                    );

                    $stripeInvoiceInfo = $stripe->invoices->retrieve($stripeSubscriptionInfo['latest_invoice']);
                    $stripeInvoiceNumber = $order_number = $stripeInvoiceInfo['number'];

                    clearance_membership_plan();
                    // insert new package information
                    $subID = wp_insert_post($subscriptionInfo);

                    add_post_meta($subID, 'hm_subscription_detail_status', 'active');
                    add_post_meta($subID, 'hm_subscription_detail_payment_gateway', 'stripe');
                    add_post_meta($subID, 'hm_subscription_detail_customer_id', $stripeCustomerInfo['id']);
                    add_post_meta($subID, 'hm_subscription_detail_order_number', $stripeInvoiceNumber);
                    add_post_meta($subID, 'hm_subscription_detail_session_id', $params['session_id']);
                    add_post_meta($subID, 'hm_subscription_detail_plan_id', $postID);
                    add_post_meta($subID, 'hm_subscription_detail_sub_id', $stripeSubscriptionInfo['id']);
                    add_post_meta($subID, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                    add_post_meta($subID, 'hm_subscription_detail_remaining_listings', $totalAllowedListings);
                    add_post_meta($subID, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $purchase_date));
                    add_post_meta($subID, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $expiry_date));

                    $invoiceID = homey_generate_invoice( 'package','recurring', $subID, date('d/M/Y h:i:s', $purchase_date), get_current_user_id(), 0, 0, '' , 'stripe', $amount_paid );
                    add_post_meta($subID, 'hm_subscription_invoice_ids', $invoiceID);
                    //end of save subscription
                }
            }
        }

    }
    //</editor-fold>

    //<editor-fold desc="paypal request processing after payment is paid">
    elseif ((isset($params['is_homey_membership']) && $paymentMethod == 'paypal')) {
        $trxn_info = array();
        if (isset($params['subscriptionID'])) {

            $is_paypal_live = $hm_options['paypal_status'];
            $host = 'https://api.sandbox.paypal.com';
            // Check if paypal live
            if ($is_paypal_live == 'live') {
                $host = 'https://api.paypal.com';
            }

            $url = $host."/v1/billing/subscriptions/".$params['subscriptionID'];
            $subscriptionInfo = homey_execute_curl_request($url, null, $access_token, false);

//            echo '<pre>';
//            print_r($subscriptionInfo);
//            exit;

            // Get transaction information from URL
            $data["plan_status"]        = $subscriptionInfo['status'];
            $data["plan_id"]            = $subscriptionInfo['plan_id'];
            $data["txn_id"]             = $subscriptionInfo['id'];
            $data["payer_name"]         = $subscriptionInfo['subscriber']['name']['given_name'].' '.$subscriptionInfo['subscriber']['name']['surname'];
            $data["email_address"]      = $subscriptionInfo['subscriber']['email_address'];
            $data["payer_id"]           = $subscriptionInfo['subscriber']['payer_id'];
            $data["amount_paid"]        = $subscriptionInfo['billing_info']['last_payment']['amount']['value'];
            $data["currency_code"]      = $subscriptionInfo['billing_info']['last_payment']['amount']['currency_code'];
            $data["start_time"]         = $subscriptionInfo['start_time'];
            $data["next_billing_time"]  = '';

            if(isset($subscriptionInfo['billing_info']['next_billing_time'])){
                $data["next_billing_time"]  = $subscriptionInfo['billing_info']['next_billing_time'];
            }
            clearance_membership_plan();
            $order_number = save_membership_subscription_post('paypal', $data);
        }
    }
    //</editor-fold>
    //<editor-fold desc="init post related information">
    $pkg_listing_or_experience = 'both';
    if ($postID > -1) {
        $membershipInfo = get_post($postID);
        $billing_period = get_post_meta($postID, 'hm_settings_bill_period', true);

        $pkg_listing_or_experience = get_post_meta( $postID, 'hm_settings_pkg_listing_or_experience', true );
        if(is_null(trim($pkg_listing_or_experience))){ $pkg_listing_or_experience = 'both';}

        //Experiences
        $experiences_included = $unlimited_experiences = $experiences_included =  $featured_experiences = '';
        if($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'experiences'){
            $experiences_included = get_post_meta( $postID, 'hm_settings_experiences_included', true );
            $experiences_included = $experiences_included > 0 ? $experiences_included : 0;
            $unlimited_experiences = get_post_meta( $postID, 'hm_settings_unlimited_experiences', true );
            $experiences_included = !empty($unlimited_experiences) ? esc_html__('Unlimited Experiences', 'homey'): $experiences_included;
            $featured_experiences = get_post_meta( $postID, 'hm_settings_featured_experiences', true );
        }
        //Experiences

        $billing_frequency = get_post_meta($postID, 'hm_settings_billing_frequency', true);
        $listings_included = get_post_meta($postID, 'hm_settings_listings_included', true);
        $unlimited_listings = get_post_meta($postID, 'hm_settings_unlimited_listings', true);

        $featured_listings = get_post_meta($postID, 'hm_settings_featured_listings', true);
        $stripe_package_id = get_post_meta($postID, 'hm_settings_stripe_package_id_'.$currency, true);
        $visibility = get_post_meta($postID, 'hm_settings_visibility', true);
        $images_per_listing = get_post_meta($postID, 'hm_settings_images_per_listing', true);
        $unlimited_images = get_post_meta($postID, 'hm_settings_unlimited_images', true);
        $taxes = get_post_meta($postID, 'hm_settings_taxes', true);
        $popular_featured = get_post_meta($postID, 'hm_settings_popular_featured', true);
        $custom_link = get_post_meta($postID, 'hm_settings_custom_link', true);
        $package_price = $package_total_price = get_post_meta($postID, 'hm_settings_package_price', true);

        if($taxes > 0){
            $package_total_price = $package_price + ($package_price / $taxes);
        }
    }
    //</editor-fold>

}
?>

<section class="main-content-area">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="page-title">
                        <div class="block-top-title">
                            <?php get_template_part('template-parts/breadcrumb'); ?>
                            <h2><?php the_title(); ?></h2>
                        </div><!-- block-top-title -->
                    </div><!-- page-title -->
                </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
            </div><!-- .row -->
        </div><!-- .container -->

        <div class="container">
            <?php
            if (isset($params['limit-exceeded'])) { ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
                        <h3 class="error"><?php echo esc_html__("You have used all of your allowed listings, please subscribe from following plans.", 'homey')?></h3>
                    </div>
                </div>
            <?php } ?>
            <?php
            if (isset($params['success']) || $paymentMethod == 'free') {
                if(!homey_is_host() && !homey_is_admin()){
                    wp_update_user( array( 'ID' => $userID, 'role' => 'homey_host' ) );
                }

                ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
                        <div class="membership-package-wrap">
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Thank you for your payment!', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                </div>
                                <div class="block-body">
                                    <?php $message_w = sprintf( __('The order <strong>#%s</strong> has been completed (Payment
                                    method: %s ) and a confirmation email has been sent to <strong>%s</strong>', 'homey'), $order_number, $paymentMethod, $current_user->user_email );
                                    echo $message_w;

                                    $message = esc_html__('The order', 'homey'). '<strong>#'.$order_number.'</strong>'.esc_html__(' has been completed (Payment method', 'homey').' : '.$paymentMethod.' )';
                                    homey_send_emails( $current_user->user_email, esc_html__('Membership purchased successfully', 'homey'), $message );// email to user

                                    $message = esc_html__('The order', 'homey'). '<strong>#'.$order_number.'</strong>'.esc_html__(' has been completed (Payment method', 'homey').' : '.$paymentMethod.' ) '. esc_html__('by', 'homey').' '.$current_user->user_email;
                                    homey_send_emails( $admin_email, esc_html__('Congratulations New membership purchased successfully', 'homey'), $message );// email to admin
                                    ?>
                                </div><!-- block-body -->
                            </div><!-- block -->
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Order Summary', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                </div>
                                <div class="block-body">
                                    <ul class="list-unstyled mebership-list-info">
                                        <li>
                                            <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Time Period', 'homey'); ?>: <strong><?php echo $billing_frequency.' '.esc_html__(esc_attr(ucfirst($billing_period)), 'homey'); ?></strong>
                                        </li>
                                        <?php if($pkg_listing_or_experience == 'both' || $pkg_listing_or_experience == 'listings'){ ?>
                                            <li>
                                                <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Listings', 'homey'); ?>: <strong><?php echo $unlimited_listings == 'on' ? esc_html_e('Unlimited Listings', 'homey') : $listings_included; ?></strong>
                                            </li>
                                            <li>
                                                <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i> <?php esc_html_e('Featured Listings', 'homey'); ?>: <strong><?php echo $featured_listings < 1 ? 0 : $featured_listings; ?></strong>
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
                                    </ul>
                                </div><!-- block-body -->
                            </div><!-- block -->
                        </div><!-- membership-package-wrap -->
                        <div class="membership-nav-wrap">
                            <?php if($pkg_listing_or_experience == 'both' ||$pkg_listing_or_experience == 'listings'){ ?> <button class="btn btn-primary btn-block" onclick="window.location.href='<?php echo $create_listing_link; ?>'"><?php echo esc_html_e('Create a Listing', 'homey');?></button><?php } ?> <?php if($pkg_listing_or_experience == 'both'){ echo ' | '; } ?> <?php if($pkg_listing_or_experience == 'both' ||$pkg_listing_or_experience == 'experiences'){ ?> <button class="btn btn-primary btn-block" onclick="window.location.href='<?php echo $create_experience_link; ?>'"><?php echo esc_html_e('Create a Experience', 'homey');?></button><?php } ?>
                        </div>
                    </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
                </div><!-- .row -->
                <?php
            }

            if (isset($params['cancel'])) {
                ?>
                <h1><?php echo __("Your payment wasn't successful give it another try."); ?> </h1>

                <?php
            } else {
                ?><!--cancel message html-->
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <?php if(isset($params['listing-limit-completed'])){ ?>
                            <h3 class="error"><?php echo esc_html__("You have used all of your allowed listings, please subscribe from following plans.", 'homey')?></h3>
                        <?php } ?>
                        <div class="membership-package-wrap">
                            <div class="row no-margin">
                                <?php
                                wp_reset_postdata();
                                //set the $args
                                $args = array(
                                    'post_type' => 'hm_homey_memberships',
                                    'post_status' => 'publish',
                                    'order' => 'ASC',
                                    'orderby' => 'post__in'
                                );
                                //do the query
                                $plans_query = new WP_Query($args);
                                $users_subscriptions = homey_get_user_subscription(1, null, 'active');

                                if ($plans_query->have_posts()) {
                                    while ($plans_query->have_posts()) {
                                        $plans_query->the_post();

                                        $currently_subscribed_plan = $currently_subscribed_id = -1;
                                        foreach ($users_subscriptions as $sub){
                                            if(isset($sub['planID'])){
                                                if($sub['planID'] == get_the_ID()){
                                                    $currently_subscribed_plan = 1;
                                                    $currently_subscribed_id = $sub['subscriptionID'];
                                                }
                                            }
                                        }

                                        $is_featured = get_post_meta(get_the_ID(), 'hm_settings_popular_featured', true);
                                        $is_visible = get_post_meta(get_the_ID(), 'hm_settings_visibility', true);
                                        if ($is_visible == 'yes') { ?>
                                            <div class="col-sm-3">
                                                <?php $price_table_name = ($is_featured == 'yes') ? 'featured-price-table' : 'price-table' ?>
                                                <?php get_template_part("template-parts/memberships/$price_table_name", null, array('currently_subscribed_id' => $currently_subscribed_id, 'currently_subscribed_plan' => $currently_subscribed_plan)); ?>
                                            </div>
                                        <?php }
                                    }
                                } else {
                                    ?>
                                    <h1><?php echo __("No plans are available to subscribe."); ?> </h1>
                                <?php }
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div><!-- membership-package-wrap -->
                    </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
                </div><!-- .row -->
            <?php } ?>
        </div>   <!-- .container -->

    </section><!-- main-content-area listing-page grid-listing-page -->
<?php get_footer(); ?>