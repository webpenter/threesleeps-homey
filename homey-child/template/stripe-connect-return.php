<?php
/**
 * Template Name: Payout Setup Return
 * Description: A polished page to handle the return from payout method onboarding.
 */
get_header();

// Enqueue a Google Font for better typography.
?>
<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">

<div class="payout-setup-container" style="max-width:600px; margin:40px auto; padding:30px; background:#fff; border-radius:10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); font-family: 'Roboto', sans-serif;">
    <h2 style="text-align:center; color:#0275d8; margin-bottom:20px;"><?php esc_html_e( 'Payout Setup Status', 'homey-core' ); ?></h2>
    <?php
    $current_user_id   = get_current_user_id();
    $payout_account_id = get_user_meta( $current_user_id, 'stripe_connect_account_id', true );

    $stripe_secret_key = trim( homey_option( 'stripe_secret_key' ) );

    if ( class_exists( '\Stripe\Stripe' ) ) {
        \Stripe\Stripe::setApiKey( $stripe_secret_key );
    } else {
        return;
    }

    require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');
    $stripe = new Homey_Stripe();

    if ( empty( $payout_account_id ) ) {
        echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'No payout method linked. Please add one to start receiving payments.', 'homey-core' ) . '</p>';
    } else {
        try {
            $account = \Stripe\Account::retrieve( $payout_account_id );
            if ( isset( $account->capabilities->transfers ) && $account->capabilities->transfers === 'active' ) {
                echo '<p style="text-align:center; color:#5cb85c;">' . esc_html__( 'Congratulations! Your payout method is active and ready for use!', 'homey-core' ) . '</p>';
            } else {
                echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'Your payout setup is incomplete.', 'homey-core' ) . '</p>';
                if ( isset( $account->requirements->currently_due ) && is_array( $account->requirements->currently_due ) && !empty( $account->requirements->currently_due ) ) {
                    echo '<p style="text-align:center; color:#d9534f; font-size:14px;">';
                    $friendly_missing = $stripe->getFriendlyMissingRequirements($account->requirements->currently_due);
                    echo '<strong>' . esc_html__( 'Missing details:', 'homey-core' ) . '</strong> ' . esc_html( $friendly_missing[0] );
                    echo '</p>';
                }
                // Provide a button to resume the setup process.
                try {
                    $account_link = \Stripe\AccountLink::create( array(
                        'account'     => $payout_account_id,
                        'refresh_url' => site_url( '/stripe-connect-refresh' ),
                        'return_url'  => site_url( '/stripe-connect-return' ),
                        'type'        => 'account_onboarding',
                    ) );
                    if ( ! empty( $account_link->url ) ) {
                        echo '<div style="text-align:center; margin-top:20px;">';
                        echo '<a href="' . esc_url( $account_link->url ) . '" class="btn" style="display:inline-block; padding:12px 20px; background:#f0ad4e; color:#fff; text-decoration:none; border-radius:5px; font-weight:500;">' 
                             . esc_html__( 'Complete Setup', 'homey-core' ) . '</a>';
                        echo '</div>';
                    }
                } catch ( Exception $e ) {
                    echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'Error generating setup link: ', 'homey-core' ) . esc_html( $e->getMessage() ) . '</p>';
                }
            }
        } catch ( Exception $e ) {
            echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'Error retrieving account details: ', 'homey-core' ) . esc_html( $e->getMessage() ) . '</p>';
        }
    }
    ?>
    <div style="margin-top:30px; text-align:center;">
        <p style="font-size:14px; color:#555;">
            <?php esc_html_e( 'Need assistance? Visit your', 'homey-core' ); ?> 
            <a href="<?php echo site_url( '/dashboard' ) ?>" target="_blank" style="color:#0275d8; text-decoration:underline;"><?php esc_html_e( 'Dashboard', 'homey-core' ); ?></a> 
            <?php esc_html_e( 'or', 'homey-core' ); ?>
            <?php 
                $stripe->display_stripe_dashboard_link(); 
            ?>
        </p>
    </div>
</div>

<?php
get_footer();
