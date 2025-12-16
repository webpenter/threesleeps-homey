<?php
require_once( get_stylesheet_directory() . '/inc/register-scripts.php' );
function homey_enqueue_styles() {
    
    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');
    
    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));
    
}
add_action('wp_enqueue_scripts', 'homey_enqueue_styles');



wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
/**
 * creating a Stripe account.
 */

require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');

/**
 * Enqueue required scripts for notifications with proper dependency handling.
 */
function enqueue_notifyjs_scripts()
{
    wp_enqueue_script('jquery');

    wp_enqueue_script(
        'notifyjs',
        'https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js',
        ['jquery'],
        '0.4.2',
        [
            'in_footer' => true,
            'strategy' => 'defer'
        ]
    );
}
add_action('wp_enqueue_scripts', 'enqueue_notifyjs_scripts');

/**
 * Display dynamic notifications with configurable parameters.
 *
 * @param string  $message   Notification content (HTML escaped)
 * @param string  $type      Style type (success|error|info|warn)
 * @param bool    $auto_hide Auto-dismissal flag
 */
function homey_notify_message($message, $type = 'info', $auto_hide = false)
{
    $config = [
        'className'   => esc_js($type),
        'autoHide'    => $auto_hide,
        'clickToHide' => true,
        'position'   => 'right bottom',
        'gap'        => 20
    ];

    wp_print_inline_script_tag(
        sprintf(
            'jQuery(function($){ $.notify("%s", %s); });',
            esc_js($message),
            wp_json_encode($config, JSON_HEX_TAG)
        ),
        ['id' => 'homey-notify-message']
    );
}

/**
 * Custom notification styling for branded appearance.
 */
function homey_custom_notify_css()
{
    $colors = [
        'background' => '#fff3f3',
        'border'     => '#e1e1e1',
        'primary'    => '#333',
        'success'    => '#5cb85c',
        'danger'     => '#d9534f'
    ];
?>
    <style id="homey-notify-styles">
        .notifyjs-foo-base {
            background: <?= $colors['background'] ?>;
            border: 1px solid <?= $colors['border'] ?>;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 20px;
            max-width: min(400px, 95vw);
            font-family: system-ui, -apple-system, sans-serif;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .notifyjs-foo-base .title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 15px;
            color: <?= $colors['primary'] ?>;
        }

        .notifyjs-foo-base .buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .notifyjs-foo-base button {
            min-width: 80px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition:
                background-color 0.2s ease,
                transform 0.1s ease;
        }

        .notifyjs-foo-base button:active {
            transform: scale(0.98);
        }

        .notifyjs-foo-base button.yes {
            background: <?= $colors['success'] ?>;
            color: white;
        }

        .notifyjs-foo-base button.no {
            background: <?= $colors['danger'] ?>;
            color: white;
        }
    </style>
<?php
}
add_action('wp_head', 'homey_custom_notify_css');

/**
 * Interactive notification system with action buttons.
 *
 * @param string $title       Notification header text
 * @param string $button_text Primary button text
 * @param string $button_url  Redirect URL for primary action
 */
function homey_notify_with_button_custom($title, $button_text, $button_url)
{
    $markup = <<<HTML
    <div class="notifyjs-foo-base">
        <div class="title" data-notify-html="title"></div>
        <div class="buttons">
            <button class="no">Cancel</button>
            <button class="yes" data-notify-text="button"></button>
        </div>
    </div>
    HTML;

    $config = [
        'style' => 'foo',
        'autoHide' => false,
        'clickToHide' => false,
        'placement' => [
            'from' => 'top',
            'align' => 'center'
        ],
        'offset' => ['y' => 20]
    ];

    ob_start(); ?>
    <script>
        jQuery(function($) {
            $.notify.addStyle('foo', {
                html: `<?= preg_replace('/\s+/', ' ', $markup) ?>`
            });

            $(document)
                .on('click', '.notifyjs-foo-base .no', (e) => $(e.target).trigger('notify-hide'))
                .on('click', '.notifyjs-foo-base .yes', () => {
                    window.location.href = "<?= esc_url($button_url) ?>";
                });

            $.notify({
                    title: "<?= esc_js($title) ?>",
                    button: "<?= esc_js($button_text) ?>"
                },
                <?= wp_json_encode($config, JSON_HEX_TAG) ?>
            );
        });
    </script>
<?php
    echo ob_get_clean();
}



/**
 * Handle the custom endpoint for creating a Stripe account.
 */
function handle_create_stripe_account()
{
    // Check if the correct action is present in the URL.
    if (isset($_GET['action']) && $_GET['action'] === 'create_stripe_account') {

        // Ensure the user is logged in.
        if (! is_user_logged_in()) {
            wp_die('Please log in to set up your payout method.');
        }

        $stripe_secret_key = trim(homey_option('stripe_secret_key'));

        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($stripe_secret_key);
        } else {
            return;
        }

        $current_user_id = get_current_user_id();
        $user = get_userdata($current_user_id);

        // Restrict this feature to hosts.
        if (empty($user->roles) || ! in_array('homey_host', (array) $user->roles)) {
            wp_die('Payout method setup is only available for hosts.');
        }

        // Check if a payout account already exists.
        $stripe_connect_account_id = get_user_meta($current_user_id, 'stripe_connect_account_id', true);
        if (empty($stripe_connect_account_id)) {

            $country = 'US';

            if (isset($_GET['country'])) {
                $country = $_GET['country'];
            }

            // Set capabilities - only request transfers capability for sending commissions.
            $capabilities = array(
                'card_payments' => array('requested' => true),
                'transfers' => array('requested' => true)
            );

            // Prepare parameters for creating the account.
            $params = array(
                'type'         => 'express',
                'email'        => $user->user_email,
                'country'      => $country,
                'capabilities' => $capabilities,
            );

            //wiki
            // For accounts in KE, specify the recipient service agreement.
            // if ( strtoupper( $country ) !== 'US' ) {
            //     $params['tos_acceptance'] = array(
            //         'service_agreement' => 'recipient'
            //     );
            // }

            try {
                // Create a new payout account.
                $account = \Stripe\Account::create($params);

                $stripe_connect_account_id = $account->id;
                update_user_meta($current_user_id, 'stripe_connect_account_id', $stripe_connect_account_id);
            } catch (Exception $e) {
                wp_die('Error creating payout account: ' . $e->getMessage());
            }
        }

        // Create an account link for completing the setup.
        try {
            $account_link = \Stripe\AccountLink::create(array(
                'account'     => $stripe_connect_account_id,
                'refresh_url' => site_url('/stripe-connect-refresh'),
                'return_url'  => site_url('/stripe-connect-return'),
                'type'        => 'account_onboarding',
            ));

            // Redirect to the payout account setup URL.
            wp_redirect($account_link->url);
            exit;
        } catch (Exception $e) {
            wp_die('Error generating account link: ' . $e->getMessage());
        }
    }
}
add_action('init', 'handle_create_stripe_account');

/**
 * Add an admin menu page for managing payout account removals.
 */
function homey_admin_remove_stripe_account_page()
{
    add_menu_page(
        'Remove Payout Accounts',               // Page title
        'Remove Payout Accounts',               // Menu title
        'manage_options',                       // Capability
        'remove-payout-accounts',               // Menu slug
        'homey_remove_payout_accounts_callback', // Callback function
        'dashicons-trash',                      // Icon
        75                                      // Position
    );
}
add_action('admin_menu', 'homey_admin_remove_stripe_account_page');

/**
 * Callback to render the admin page listing users with connected payout accounts.
 * Also displays the parent (platform) account ID.
 */
function homey_remove_payout_accounts_callback()
{
    if (! current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $stripe_secret_key = trim(homey_option('stripe_secret_key'));

    if (class_exists('\Stripe\Stripe')) {
        \Stripe\Stripe::setApiKey($stripe_secret_key);
    } else {
        return;
    }

    // Retrieve the parent (platform) account information.
    try {
        $platform_account = \Stripe\Account::retrieve();
        $parent_account_id = $platform_account->id;
    } catch (Exception $e) {
        $parent_account_id = 'Error: ' . $e->getMessage();
    }

    // Process account removal if requested.
    if (isset($_GET['remove_account']) && ! empty($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $result  = homey_admin_disconnect_user_payout_account($user_id);
        if (is_wp_error($result)) {
            echo '<div class="error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="updated"><p>Payout account removed successfully for user ID ' . esc_html($user_id) . '.</p></div>';
        }
    }

    // Query users with a connected payout account.
    $args  = array(
        'meta_key'     => 'stripe_connect_account_id',
        'meta_compare' => 'EXISTS'
    );
    $users = get_users($args);
?>
    <div class="wrap">
        <h1>Remove Payout Accounts (Test Accounts Only by Default)</h1>
        <p><strong>Parent Account ID:</strong> <?php echo esc_html($parent_account_id); ?></p>
        <p>
            By default only test accounts (livemode = false) are removable.
            If you need to force removal (via API) for live accounts, add <code>&amp;force_remove=1</code> to the URL.
            Alternatively, use the <strong>Remove from DB Only</strong> option if API access isn’t available.
        </p>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Payout Account ID</th>
                    <th>Account Mode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (! empty($users)) {
                    foreach ($users as $user) :
                        $payout_account = get_user_meta($user->ID, 'stripe_connect_account_id', true);
                        if (! $payout_account) {
                            continue;
                        }
                        $account_mode = 'Unknown';
                        $can_remove   = false;

                        // Try retrieving the account details to check its mode.
                        try {
                            $account = \Stripe\Account::retrieve($payout_account);
                            //print_r($account);
                            //print_r('mode:'. $account->livemode);
                            // if ( isset( $account->livemode ) ) {
                            if ($account->livemode) {
                                $account_mode = 'Live';
                                $can_remove   = false;
                            } else {
                                $account_mode = 'Test';
                                $can_remove   = true;
                            }
                            // }
                        } catch (Exception $e) {
                            // If retrieval fails, mark as "Error" and offer DB removal.
                            $account_mode = 'Error';
                        }
                ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html($payout_account); ?></td>
                            <td><?php echo esc_html($account_mode); ?></td>
                            <td>
                                <?php if ($account_mode == 'Test') : ?>
                                    <a href="<?php echo esc_url(add_query_arg(array('remove_account' => '1', 'user_id' => $user->ID))); ?>" class="button button-secondary" onclick="return confirm('Are you sure you want to remove the payout account for this user?');">Remove Account</a>
                                <?php elseif ($account_mode == 'Live') : ?>
                                    <a href="<?php echo esc_url(add_query_arg(array('remove_account' => '1', 'user_id' => $user->ID, 'force_remove' => '1'))); ?>" class="button button-secondary" onclick="return confirm('This is a live account. Are you sure you want to force removal via API?');">Force Remove</a>
                                    <br>
                                    <a href="<?php echo esc_url(add_query_arg(array('remove_account' => '1', 'user_id' => $user->ID, 'db_only' => '1'))); ?>" class="button" onclick="return confirm('Are you sure you want to remove this live account from the database only?');" style="margin-top:5px;">Remove from DB Only</a>
                                <?php elseif ($account_mode == 'Error') : ?>
                                    <a href="<?php echo esc_url(add_query_arg(array('remove_account' => '1', 'user_id' => $user->ID, 'db_only' => '1'))); ?>" class="button" onclick="return confirm('Account retrieval failed. Remove the payout account from the database only?');">Remove from DB Only</a>
                                <?php else : ?>
                                    <span style="color: #555;">Not Removable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                } else {
                    echo '<tr><td colspan="6" style="text-align:center;">No users found with a connected payout account.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}

/**
 * Disconnects a user's payout account.
 * By default, only accounts in test mode (livemode = false) are allowed to be removed.
 * Use the 'force_remove' parameter to attempt deauthorization for live accounts.
 * Use the 'db_only' parameter to remove the account solely from the database.
 *
 * @param int $user_id The ID of the user whose account should be disconnected.
 * @return mixed The deauthorization response on success or a WP_Error on failure.
 */
function homey_admin_disconnect_user_payout_account($user_id)
{
    $user = get_userdata($user_id);
    if (! $user) {
        return new WP_Error('invalid_user', 'Invalid user ID.');
    }

    // Get the connected payout account.
    $stripe_connect_account_id = get_user_meta($user_id, 'stripe_connect_account_id', true);
    if (empty($stripe_connect_account_id)) {
        return new WP_Error('no_account', 'User does not have a connected payout account.');
    }

    // If the "db_only" flag is set, remove the meta without contacting Stripe.
    if (isset($_GET['db_only']) && $_GET['db_only'] === '1') {
        delete_user_meta($user_id, 'stripe_connect_account_id');
        return true;
    }

    // Retrieve account details.
    try {
        $account = \Stripe\Account::retrieve($stripe_connect_account_id);
    } catch (Exception $e) {
        return new WP_Error('account_retrieve_error', $e->getMessage());
    }

    // Check for force removal override (only available to admins).
    $force_remove = (isset($_GET['force_remove']) && $_GET['force_remove'] === '1');

    // Only allow removal if the account is in test mode or if forced.
    if ($account->livemode && ! $force_remove) {
        return new WP_Error('live_account', 'Cannot remove live payout accounts without force override.');
    }

    // Deauthorize the account via Stripe and remove it from the database.
    try {
        $deauth = \Stripe\OAuth::deauthorize(array(
            'client_id'      => homey_option('stripe_connect_client_id'),
            'stripe_user_id' => $stripe_connect_account_id,
        ));
        delete_user_meta($user_id, 'stripe_connect_account_id');
        return $deauth;
    } catch (Exception $e) {
        return new WP_Error('disconnect_error', $e->getMessage());
    }
}


/**
 * Add a new field for Stripe Client ID to the existing Stripe Settings section.
 */
add_filter('redux/options/homey_options/sections', 'child_add_stripe_client_id_field');
function child_add_stripe_client_id_field($sections)
{
    // Loop through each section to find the Stripe Settings section.
    foreach ($sections as &$section) {
        if (isset($section['id']) && 'mem-stripe-settings' === $section['id']) {
            // Append the new field to the section's fields.
            $section['fields'][] = array(
                'id'       => 'enable_stripe_connect',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Stripe Connect', 'homey'),
                'subtitle' => esc_html__('Turn on/off Stripe Connect functionality.', 'homey'),
                'default'  => false, // Default is off
                'on'       => esc_html__('On', 'homey'),
                'off'      => esc_html__('Off', 'homey'),
            );
            // Add Stripe Client ID field that will only appear if Stripe Connect is enabled.
            $section['fields'][] = array(
                'id'       => 'stripe_connect_client_id',
                'type'     => 'text',
                'title'    => esc_html__('Stripe Client ID', 'homey'),
                'subtitle' => esc_html__('Enter your Stripe Client ID', 'homey'),
                'desc'     => esc_html__('This is required for Stripe Connect ( https://dashboard.stripe.com/settings/connect/onboarding-options/oauth ).', 'homey'),
                'required' => array('enable_stripe_connect', '=', '1'), // Only show if Stripe Connect is enabled
                'default'  => '',
            );
        }
    }
    return $sections;
}

require_once('includes/stripe-connect.php'); 

require_once( get_stylesheet_directory() . '/framework/functions/listings.php' );
require_once( get_stylesheet_directory() . '/framework/functions/reservation.php' );
require_once( get_stylesheet_directory() . '/framework/functions/calendar.php' );
require_once( get_stylesheet_directory() . '/framework/functions/icalendar.php' );

/**
 *    ---------------------------------------------------------------------------------------
 *    Meta Boxes
 *    ---------------------------------------------------------------------------------------
 */
require_once(get_stylesheet_directory() . '/framework/metaboxes/homey-meta-boxes.php');

add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_hour'])) {
        $schedules['every_hour'] = [
            'interval' => 3600, // 1 hour in seconds
            'display'  => __('Every Hour'),
        ];
    }
    return $schedules;
});
if (!wp_next_scheduled('homey_ical_sync_multi')) {
    wp_schedule_event(time(), 'every_hour', 'homey_ical_sync_multi');
}

if(isset($_GET['homey_ical_multi'])) {

   homey_ical_sync_multi_callback();

}

add_action('save_post', function($post_id) {

	if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (!is_admin()) return;

    $booking_type = get_post_meta($post_id, 'homey_booking_type', true);
    $accomodation = get_post_meta($post_id, 'homey_accomodation', true);

    if ($booking_type === 'per_day_multi') {

		if (!empty($accomodation) && is_array($accomodation)) {
			$cleaned  = [];
			$used_ids = [];

			foreach ($accomodation as $key => $room) {
				$room_id = isset($room['room_id']) ? trim((string)$room['room_id']) : '';
				$price   = isset($room['night_price']) ? trim((string)$room['night_price']) : '';

				// Treat empty string or zero as "empty" price – adjust if you want to allow zero
				$price_is_empty = ($price === '' || (float)$price == 0);

				// 1) If BOTH room_id and price are empty -> skip (unset)
				if ($room_id === '' && $price_is_empty) {
					continue;
				}

				// 2) If price exists but room_id missing -> generate new room_id
				if ($room_id === '') {
					$room_id = 'room_' . uniqid('', true);
					$room['room_id'] = $room_id;
				}

				// 3) If room_id duplicates another, regenerate to keep unique
				if (isset($used_ids[$room_id])) {
					$room_id = 'room_' . uniqid('', true);
					$room['room_id'] = $room_id;
				}

				$used_ids[$room_id] = true;
				$cleaned[] = $room;
			}

			// Reindex keys to keep things tidy for Meta Box
			update_post_meta($post_id, 'homey_accomodation', array_values($cleaned));
		}

        update_post_meta($post_id, 'homey_multiroom_booking', 'per_day_multi');
        update_post_meta($post_id, 'homey_booking_type', 'per_day');
    } else {
        update_post_meta($post_id, 'homey_multiroom_booking', '');
    }
});


?>
