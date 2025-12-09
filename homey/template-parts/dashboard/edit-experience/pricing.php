<?php
global $homey_local, $homey_booking_type, $experience_meta_data;
$hide_fields_for_experience = $hide_fields = homey_option('experience_add_hide_fields');

$instant_booking = isset($experience_meta_data['homey_instant_booking'][0]) ? $experience_meta_data['homey_instant_booking'][0] : 0;// homey_get_field_meta('instant_booking');

$day_date_price = isset($experience_meta_data['homey_day_date_price'][0]) ? $experience_meta_data['homey_day_date_price'][0] : 0; //homey_get_field_meta('day_date_price');
$night_price = isset($experience_meta_data['homey_night_price'][0]) ? $experience_meta_data['homey_night_price'][0] : 0; //homey_get_field_meta('night_price');

if($homey_booking_type == 'per_day_date') {
    $fees_label = homey_option('ad_day_date_text');
}elseif($homey_booking_type == 'per_hour') {
    $fees_label = homey_option('ad_hourly_text');
} else if($homey_booking_type == 'per_week') {
    $fees_label = homey_option('ad_weekly_text');
} else if($homey_booking_type == 'per_month') {
    $fees_label = homey_option('ad_monthly_text');
} else {
    $fees_label = homey_option('ad_daily_text');
}

$price_label = $price_plac = '';

$price_label = homey_option('experience_ad_nightly_label');
$price_plac = homey_option('experience_ad_nightly_plac');


if(@$hide_fields['price_postfix'] != 1) {
    $instance_classes = 'col-sm-12 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
} else {
    $instance_classes = 'col-sm-6 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
}

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'pricing') {
    $class = 'in active';
}
?>
<div id="pricing-tab" class="tab-pane fade <?php echo esc_attr($class);?>">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('experience_ad_pricing_label')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">

                <?php if($hide_fields['experience_instant_booking'] != 1) { ?>
                    <div class="<?php echo esc_attr($instance_classes); ?>">
                        <div class="form-group">
                            <label><?php echo esc_attr(homey_option('experience_ad_ins_booking_label')); ?></label>
                            <label class="control control--checkbox radio-tab"><?php echo esc_attr(homey_option('experience_ad_ins_booking_des')); ?>
                                <input type="checkbox" <?php checked( $instant_booking, 1 ); ?> name="instant_booking">
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                <?php } ?>

                <?php if($hide_fields['experience_night_price'] != 1) { ?>
                    <?php if($homey_booking_type == 'per_day_date') { ?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="night-price"><?php echo esc_html__('Price Per Day', 'homey').homey_req('experience_night_price'); ?></label>
                                <input type="text" value="<?php echo $day_date_price; ?>" name="day_date_price" class="form-control" <?php homey_required('experience_night_price'); ?> id="day_date_price" placeholder="<?php echo esc_html__('Enter price for 1 day', 'homey'); ?>">
                            </div>
                        </div>
                    <?php } elseif($homey_booking_type == 'per_hour') { ?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="night-price"><?php echo esc_html__('Price Per Hour', 'homey').homey_req('experience_night_price'); ?></label>
                                <input type="text"  value="<?php echo $night_price; ?>"  data-input-title="<?php echo esc_html__('Price Per Hour', 'homey'); ?>" name="hour_price" class="form-control" <?php homey_required('experience_night_price'); ?> id="hour_price" placeholder="<?php echo esc_html__('Enter price for 1 hour', 'homey'); ?>">
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="night-price"><?php echo esc_attr($price_label).homey_req('experience_night_price'); ?></label>
                                <input type="text"  value="<?php echo $night_price; ?>"  data-input-title="<?php echo esc_html__('Price Per Night', 'homey'); ?>" name="night_price" class="form-control" <?php homey_required('experience_night_price'); ?> id="night_price" placeholder="<?php echo esc_attr($price_plac); ?>">
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if($hide_fields['experience_price_postfix'] != 1) { ?>
                    <div class="<?php echo esc_attr($postfix_classes); ?>">
                        <div class="form-group">
                            <label for="price_postfix"><?php echo esc_attr(homey_option('experience_ad_price_postfix_label')); ?></label>
                            <input type="text" value="<?php echo isset($experience_meta_data['homey_price_postfix'][0]) ? $experience_meta_data['homey_price_postfix'][0] : 0;?>" name="price_postfix" class="form-control" id="price_postfix" placeholder="<?php echo esc_attr(homey_option('experience_ad_price_postfix_plac')); ?>">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
