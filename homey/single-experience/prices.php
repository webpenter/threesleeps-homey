<?php
global $post, $homey_local, $homey_prefix, $hide_labels;
$exp_prefix = 'experience_';

$night_price = homey_get_experience_data('night_price');
$security_deposit = homey_get_experience_data('security_deposit');
?>

<div id="price-section" class="price-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('experience_sn_prices_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if(!empty($night_price) && @$hide_labels[$exp_prefix.'sn_nightly_label'] != 1) { ?>
                        <li>
                            <i class="homey-icon homey-icon-check-circle-1" aria-hidden="true"></i>
                            <?php echo homey_exp_get_price_label();?>: 
                            <strong><?php echo homey_formatted_price($night_price, false); ?></strong>
                        </li>
                        <?php } ?>
                    </ul>
                </div><!-- block-right -->
            </div><!-- block-body -->

            <?php get_template_part('single-experience/extra-prices'); ?>
        </div><!-- block-section -->
    </div><!-- block -->
</div>
