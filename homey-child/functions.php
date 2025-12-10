<?php
function homey_enqueue_styles() {
    
    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');
    
    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));
}

add_action('wp_enqueue_scripts', 'homey_enqueue_styles');




/**
 * Handle the custom endpoint for creating a Stripe account.
 */
function handle_create_stripe_account() {
    // Check if the correct action is present in the URL.
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'create_stripe_account' ) {

        // Ensure the user is logged in.
        if ( ! is_user_logged_in() ) {
            wp_die( 'Please log in to set up your payout method.' );
        }

        $stripe_secret_key = trim( homey_option( 'stripe_secret_key' ) );

        if ( class_exists( '\Stripe\Stripe' ) ) {
            \Stripe\Stripe::setApiKey( $stripe_secret_key );
        } else {
            return;
        }

        $current_user_id = get_current_user_id();
        $user = get_userdata( $current_user_id );

        // Restrict this feature to hosts.
        if ( empty( $user->roles ) || ! in_array( 'homey_host', (array) $user->roles ) ) {
            wp_die( 'Payout method setup is only available for hosts.' );
        }

        // Check if a payout account already exists.
        $stripe_connect_account_id = get_user_meta( $current_user_id, 'stripe_connect_account_id', true );
        if ( empty( $stripe_connect_account_id ) ) {

            $country = 'AU';
            // Set capabilities - only request transfers capability for sending commissions.
            $capabilities = array(
                'transfers' => array( 'requested' => true )
            );

            // Prepare parameters for creating the account.
            $params = array(
                'type' => 'custom',
                'country' => "$country",
                'business_type' => 'individual',
                'individual' => [
                    'first_name' => "$user->homey_first_name",
                    'last_name' => "$user->homey_last_name",
                    'dob' => [
                        'day' => 01,
                        'month' => 01,
                        'year' => 2000,
                    ],
                    'email' => $user->user_email,
                    'address' => [
                        'line1' => "$user->homey_street_address",
                        'city' => "$user->homey_city",
                        'state' => "$user->homey_state",
                        'postal_code' => "$user->homey_zipcode",
                        'country'      => $country,
                    ],
                ],
                'capabilities' => $capabilities,
            );

            // For accounts in KE, specify the recipient service agreement.
//            if ( strtoupper( $country ) === 'AU' ) {
//                $params['tos_acceptance'] = array(
//                    'service_agreement' => 'recipient'
//                );
//            }

            try {
                // Create a new payout account.
                $account = \Stripe\Account::create( $params );

                // $account = \Stripe\Account::create( array(
                //     'type'         => 'express',
                //     'email'        => $user->user_email,
                //     'country'      => 'KE',
                //     'capabilities' => array(
                //         // 'card_payments' => array( 'requested' => true ),
                //         'transfers'     => array( 'requested' => true ),
                //     ),
                // ) );
                $stripe_connect_account_id = $account->id;
                update_user_meta( $current_user_id, 'stripe_connect_account_id', $stripe_connect_account_id );
            } catch ( Exception $e ) {
                wp_die( 'Error creating payout account: ' . $e->getMessage() );
            }
        }

        // Create an account link for completing the setup.
        try {
            $account_link = \Stripe\AccountLink::create( array(
                'account'     => $stripe_connect_account_id,
                'refresh_url' => site_url( '/stripe-connect-refresh' ),
                'return_url'  => site_url( '/stripe-connect-return' ),
                'type'        => 'account_onboarding',
            ) );

            // Redirect to the payout account setup URL.
            wp_redirect( $account_link->url );
            exit;
        } catch ( Exception $e ) {
            wp_die( 'Error generating account link: ' . $e->getMessage() );
        }
    }
}
add_action( 'init', 'handle_create_stripe_account' );

/**
 * Disconnects a user's payout account.
 * By default, only accounts in test mode (livemode = false) are allowed to be removed.
 * Use the 'force_remove' parameter to attempt deauthorization for live accounts.
 * Use the 'db_only' parameter to remove the account solely from the database.
 *
 * @param int $user_id The ID of the user whose account should be disconnected.
 * @return mixed The deauthorization response on success or a WP_Error on failure.
 */
function homey_admin_disconnect_user_payout_account( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return new WP_Error( 'invalid_user', 'Invalid user ID.' );
    }

    // Get the connected payout account.
    $stripe_connect_account_id = get_user_meta( $user_id, 'stripe_connect_account_id', true );
    if ( empty( $stripe_connect_account_id ) ) {
        return new WP_Error( 'no_account', 'User does not have a connected payout account.' );
    }

    // If the "db_only" flag is set, remove the meta without contacting Stripe.
    if ( isset( $_GET['db_only'] ) && $_GET['db_only'] === '1' ) {
        delete_user_meta( $user_id, 'stripe_connect_account_id' );
        return true;
    }

    // Retrieve account details.
    try {
        $account = \Stripe\Account::retrieve( $stripe_connect_account_id );
    } catch ( Exception $e ) {
        return new WP_Error( 'account_retrieve_error', $e->getMessage() );
    }

    // Check for force removal override (only available to admins).
    $force_remove = ( isset( $_GET['force_remove'] ) && $_GET['force_remove'] === '1' );

    // Only allow removal if the account is in test mode or if forced.
    if ( $account->livemode && ! $force_remove ) {
        return new WP_Error( 'live_account', 'Cannot remove live payout accounts without force override.' );
    }

    // Deauthorize the account via Stripe and remove it from the database.
    try {
        $deauth = \Stripe\OAuth::deauthorize( array(
            'client_id'      => homey_option( 'stripe_connect_client_id' ),
            'stripe_user_id' => $stripe_connect_account_id,
        ) );
        delete_user_meta( $user_id, 'stripe_connect_account_id' );
        return $deauth;
    } catch ( Exception $e ) {
        return new WP_Error( 'disconnect_error', $e->getMessage() );
    }
}
