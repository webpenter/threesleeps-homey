<?php
global $homey_local, $hide_fields, $homey_booking_type;
$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode(',', $checkin_after_before);

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');
$start_and_end_hours = '';
for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour + 30 * 60) {
    $start_and_end_hours .= '<option value="' . date('H:i', $halfhour) . '">' . date(homey_time_format(), $halfhour) . '</option>';
}

$checkinout_hours = '';
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('experience_ad_terms_rules')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php if ($hide_fields['cancel_policy'] != 1) { ?>
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="cancel"><?php echo esc_attr(homey_option('experience_ad_cancel_policy')) . homey_req('cancellation_policy'); ?></label>
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
                                        echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
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

                <?php if ($hide_fields['smoking_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_smoking_allowed')); ?>
                            ?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_smoke" value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_smoke" value="0" checked="checked" type="radio">
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
                <?php if ($hide_fields['pets_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_pets_allowed')); ?>?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_pets" value="1" checked="checked" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_pets" value="0" type="radio">
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
                <?php if ($hide_fields['party_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_party_allowed')); ?>
                            ?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_party" value="1" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_party" value="0" checked="checked" type="radio">
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
                <?php if ($hide_fields['children_allowed'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <label class="label-condition"><?php echo esc_attr(homey_option('experience_ad_children_allowed')); ?>
                            ?</label>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_children" value="1" checked="checked" type="radio">
                                        <span class="control-text"><?php echo esc_attr(homey_option('experience_ad_text_yes')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input name="experience_children" value="0" type="radio">
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

            <?php if ($hide_fields['additional_rules'] != 1) { ?>
                <div class="row">
                    <div class="col-sm-12 col-sm-12">
                        <div class="form-group">
                            <label for="additional_rules"><?php echo esc_attr(homey_option('experience_ad_add_rules_info_optional')); ?></label>
                            <textarea name="additional_rules" class="form-control" id="rules" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
