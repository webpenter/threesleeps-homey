<?php
/**
 * Template Name: Payout Setup Refresh
 * Description: A stylish page to handle refresh scenarios in the payout method setup flow.
 */
get_header();
?>
<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
<div class="payout-setup-container" style="max-width:600px; margin:40px auto; padding:30px; background:#fff; border-radius:10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); font-family: 'Roboto', sans-serif;">
    <h2 style="text-align:center; color:#d9534f; margin-bottom:20px;"><?php esc_html_e( 'Setup Incomplete', 'homey-core' ); ?></h2>
    <p style="text-align:center; font-size:16px; color:#555;">
        <?php esc_html_e( 'It looks like your payout setup process was interrupted. Please click the button below to restart and finish setting up your account.', 'homey-core' ); ?>
    </p>
    <?php
    $current_user_id   = get_current_user_id();
    $stripe_account_id = get_user_meta( $current_user_id, 'stripe_connect_account_id', true );

    $stripe_secret_key = trim( homey_option( 'stripe_secret_key' ) );

    if ( class_exists( '\Stripe\Stripe' ) ) {
        \Stripe\Stripe::setApiKey( $stripe_secret_key );
    } else {
        return;
    }
    
    if ( empty( $stripe_account_id ) ) {
        echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'No payout method is linked to your account.', 'homey-core' ) . '</p>';
    } else {
        try {
            $account_link = \Stripe\AccountLink::create( array(
                'account'     => $stripe_account_id,
                'refresh_url' => site_url( '/stripe-connect-refresh' ),
                'return_url'  => site_url( '/stripe-connect-return' ),
                'type'        => 'account_onboarding',
            ) );
            if ( ! empty( $account_link->url ) ) {
                echo '<div style="text-align:center; margin-top:20px;">';
                echo '<a href="' . esc_url( $account_link->url ) . '" class="btn" style="display:inline-block; padding:12px 20px; background:#0275d8; color:#fff; text-decoration:none; border-radius:5px; font-weight:500;">' 
                     . esc_html__( 'Restart Setup', 'homey-core' ) . '</a>';
                echo '</div>';
            }
        } catch ( Exception $e ) {
            echo '<p style="text-align:center; color:#d9534f;">' . esc_html__( 'Error generating refresh link: ', 'homey-core' ) . esc_html( $e->getMessage() ) . '</p>';
        }
    }
    ?>
    <div style="margin-top:30px; text-align:center;">
        <p style="font-size:14px; color:#555;">
            <?php esc_html_e( 'If you continue to have issues, please contact support.', 'homey-core' ); ?>
        </p>
    </div>
</div>
<?php
get_footer();
