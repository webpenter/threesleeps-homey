<?php
global $homey_local, $homey_booking_type, $experience_meta_data;
$hide_fields_for_experience = $hide_fields = homey_option('experience_add_hide_fields');

$additional_rules = isset($experience_meta_data['homey_additional_rules'][0]) ? $experience_meta_data['homey_additional_rules'][0] : '';
$cancellation_policy = isset($experience_meta_data['homey_cancellation_policy'][0]) ? $experience_meta_data['homey_cancellation_policy'][0] : '';
$checkin_after_before = homey_option('experience_checkin_after_before');
$checkin_after_before_array = explode(',', $checkin_after_before);

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');

$smoke = homey_get_exp_field_meta('experience_smoke');
$pets = homey_get_exp_field_meta('experience_pets');
$party = homey_get_exp_field_meta('experience_party');
$children = homey_get_exp_field_meta('experience_children');

$start_and_end_hours = '';
for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour + 30 * 60) {
    $start_and_end_hours .= '<option value="' . date('H:i', $halfhour) . '">' . date(homey_time_format(), $halfhour) . '</option>';
}

$checkinout_hours = '';

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'pricing') {
    $class = 'in active';
}
?>
<div id="price_terms-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('experience_ad_terms_rules')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php if (@$hide_fields_for_experience['experience_cancel_policy'] != 1) { ?>
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="cancel"><?php echo esc_attr(homey_option('experience_ad_cancel_policy')) . homey_req('experience_cancellation_policy'); ?></label>
                            <select name="cancellation_policy" class="selectpicker" data-live-search="false"
                                    data-live-search-style="begins"
                                    title="<?php echo esc_attr(homey_option('experience_ad_cancel_policy')); ?>">
                                <option value=""><?php echo esc_html__("Select Cancellation Policy", "homey"); ?></option>
                                <?php

                                $args = array(
                                    'post_type' => 'homey_cancel_policy',
                                    'posts_per_page' => 100
                                );

                                $policies_data = '';

                                $policies_qry = new WP_Query($args);
                                if ($policies_qry->have_posts()):
                                    while ($policies_qry->have_posts()): $policies_qry->the_post();
                                        $is_selected = trim($cancellation_policy) == get_the_ID() ? "selected='selected'" : '';
                                        echo '<option ' . $is_selected . ' value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                                    endwhile;
                                endif;
                                ?>
                            </select>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row">

                <!--Smoking-->
                <?php if(@$hide_fields['experience_smoking_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_smoking_allowed')); ?>?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input <?php checked($smoke, '1'); ?> name="smoke" value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input <?php checked($smoke, '0'); ?> name="smoke" value="0" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_no')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!--Pets-->
                <?php if(@$hide_fields['experience_pets_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_pets_allowed')); ?>?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="pets" <?php checked($pets, '1'); ?> value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="pets" <?php checked($pets, '0'); ?> value="0" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_no')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!--Party-->
                <?php if(@$hide_fields['experience_party_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_party_allowed')); ?>?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="party" <?php checked($party, '1'); ?> value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="party" <?php checked($party, '0'); ?> value="0" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_no')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!--Children-->
                <?php if(@$hide_fields['experience_children_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_children_allowed')); ?>?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="children" <?php checked($children, '1'); ?> value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="children" <?php checked($children, '0'); ?> value="0" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_no')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <?php if (@$hide_fields['experience_additional_rules'] != 1) { ?>
                <div class="row">
                    <div class="col-sm-12 col-sm-12">
                        <div class="form-group">
                            <label for="additional_rules"><?php echo esc_attr(homey_option('experience_ad_add_rules_info_optional')); ?></label>
                            <textarea name="additional_rules" class="form-control" id="rules"
                                      rows="3"><?php echo $additional_rules; ?></textarea>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
