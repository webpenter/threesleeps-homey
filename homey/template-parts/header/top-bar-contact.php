<?php
global $homey_local;
$top_bar_phone = homey_option('top_bar_phone');
$top_bar_email = homey_option('top_bar_email');
?>

<ul class="top-contact-address hidden-sm hidden-xs list-unstyled">
	<?php if( !empty( $top_bar_phone ) ) { ?>
    	<li><a class="phone-number" href="tel:<?php echo str_replace(' ', '', $top_bar_phone); ?>"><i class="homey-icon homey-icon-phone-circle"></i> <?php echo esc_html($top_bar_phone); ?></a></li>
	<?php } ?>

	<?php if( !empty( $top_bar_email ) ) { ?>
    	<li><a class="email-contact" href="mailto:<?php echo esc_html($top_bar_email); ?>"><i class="homey-icon homey-icon-unread-emails"></i> <?php echo esc_attr($homey_local['topbar_contact_text']); ?></a></li>
    <?php } ?>
</ul>
<ul class="top-contact-address top-contact-address-mobile hidden-md hidden-lg list-unstyled">
    <?php if( !empty( $top_bar_phone ) ) { ?>
    	<li><a class="phone-number" href="tel:<?php echo str_replace(' ', '', $top_bar_phone); ?>"><i class="homey-icon homey-icon-phone-circle"></i> </a></li>
	<?php } ?>
    <?php if( !empty( $top_bar_email ) ) { ?>
    	<li><a class="email-contact" href="mailto:<?php echo esc_html($top_bar_email); ?>"><i class="homey-icon homey-icon-unread-emails"></i></a></li>
    <?php } ?>
</ul>