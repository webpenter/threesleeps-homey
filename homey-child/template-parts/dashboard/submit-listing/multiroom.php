<?php
global $homey_local,$hide_fields, $homey_booking_type;
$room_aminites = array('Desktop Workspace','Private Bathroom','Lock on Door','Set of Sheets','Second Set of Sheets','Kitchenette','Air Conditioning','Balcony','Wifi','Balcony','Television','Internet Cable');

// Price label and placeholder variables
$price_label = $price_plac = '';
if($homey_booking_type == 'per_day_date') {
    $price_label = homey_option('ad_day_date_label');
    $price_plac = homey_option('ad_day_date_plac');
    $fees_label = homey_option('ad_day_date_text');
}elseif($homey_booking_type == 'per_hour') {
    $fees_label = homey_option('ad_hourly_text');
} else if($homey_booking_type == 'per_week') {
    $price_label = homey_option('ad_weekly_label');
    $price_plac = homey_option('ad_weekly_plac');
    $fees_label = homey_option('ad_weekly_text');
} else if($homey_booking_type == 'per_month') {
    $price_label = homey_option('ad_monthly_label');
    $price_plac = homey_option('ad_monthly_plac');
    $fees_label = homey_option('ad_monthly_text');
} else {
    $price_label = homey_option('ad_nightly_label');
    $price_plac = homey_option('ad_nightly_plac');
    $fees_label = homey_option('ad_daily_text');
}

if($hide_fields['price_postfix'] != 1) {
    $instance_classes = 'col-sm-12 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
} else {
    $instance_classes = 'col-sm-6 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
}

$room_id ='room_' . round(microtime(true) * 1000) . '_' . mt_rand(0, 999);
?>

<div class="form-step">
    <!--step information-->
    <div class="block">
        <input type="hidden" name="homey_accomodation[0][room_id]" value="<?php echo esc_attr($room_id); ?>">

        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('ad_bedrooms_text')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php if($hide_fields['instant_booking'] != 1) { ?>
            <div class="<?php echo esc_attr($instance_classes); ?>">
                <div class="form-group">
                    <label><?php echo esc_attr(homey_option('ad_ins_booking_label')); ?></label>
                    <label class="control control--checkbox radio-tab"><?php echo esc_attr(homey_option('ad_ins_booking_des')); ?>
                        <input type="checkbox" name="instant_booking" value="1">
                        <span class="control__indicator"></span>
                        <span class="radio-tab-inner"></span>
                    </label>
                </div>
            </div>
            <?php } ?>
			
			<div id="more_bedrooms_main">
                <div class="more_rooms_wrap">

                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="acc_bedroom_name"><?php echo esc_attr(homey_option('ad_acc_bedroom_name')); ?></label>
                                <input type="text" name="homey_accomodation[0][acc_bedroom_name]"  class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_name_plac')); ?>">
                            </div>
                        </div>
                    </div>		

					<div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="acc_bedroom_description"><?php echo esc_html__('Description'); ?></label>
                                <textarea rows="5" name="homey_accomodation[0][acc_bedroom_description]" class="form-control" placeholder="<?php echo esc_html__('Add description'); ?>"><?php if (isset($acc['acc_bedroom_description'])){ echo sanitize_text_field( $acc['acc_bedroom_description'] ); } ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_acc_no_of_beds')); ?> </label>
                                <input type="text" name="homey_accomodation[0][acc_no_of_beds]"  class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_no_of_beds_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_guests"> <?php echo esc_attr(homey_option('ad_acc_guests')); ?> </label>
                                <input type="text" name="homey_accomodation[0][acc_guests]"  class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_guests_plac')); ?>">
                            </div>
                        </div>
                        <?php if($hide_fields['listing_size'] != 1) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="listing_size"> <?php echo esc_attr(homey_option('ad_listing_size')).homey_req('listing_size'); ?> </label>
                                    <input type="text" class="form-control" name="homey_accomodation[0][listing_size]" placeholder="<?php echo esc_attr(homey_option('ad_size_placeholder'));?>">
                                </div>
                            </div>
                            <?php } ?>

                            <?php if($hide_fields['listing_size_unit'] != 1) { ?>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="listing_size_unit"> <?php echo esc_attr(homey_option('ad_listing_size_unit')).homey_req('listing_size_unit'); ?> </label>
                                    <input type="text" class="form-control" name="homey_accomodation[0][listing_size_unit]" placeholder="<?php echo esc_attr(homey_option('ad_listing_size_unit_plac'));?>">
                                </div>
                            </div>
                            <?php } ?>
                    </div>

                    <!-- Pricing Section Added to Bedroom -->
                    <hr class="row-separator">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <h3 class="sub-title"><?php echo esc_attr('Pricing'); ?></h3>
                        </div>
                        
                        <!-- Night Price Field -->
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <?php if($homey_booking_type == 'per_day_date') { ?>
                                    <label for="night-price"><?php echo esc_html__('Price Per Day', 'homey').homey_req('night_price'); ?></label>
                                    <input type="text" name="homey_accomodation[0][day_date_price]" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_html__('Enter price for 1 day', 'homey'); ?>">
                                <?php } elseif($homey_booking_type == 'per_hour') { ?>
                                    <label for="night-price"><?php echo esc_html__('Price Per Hour', 'homey').homey_req('night_price'); ?></label>
                                    <input type="text" name="homey_accomodation[0][hour_price]" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_html__('Enter price for 1 hour', 'homey'); ?>">
                                <?php } else { ?>
                                    <label for="night-price"><?php echo esc_attr($price_label).homey_req('night_price'); ?></label>
                                    <input type="text" name="homey_accomodation[0][night_price]" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_attr($price_plac); ?>">
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cleaning Fee -->
                    <?php if($hide_fields['cleaning_fee'] != 1) { ?>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <label><?php echo esc_attr(homey_option('ad_cleaning_fee')); ?></label>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <input type="text" name="homey_accomodation[0][cleaning_fee]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_cleaning_fee_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" name="homey_accomodation[0][cleaning_fee_type]" value="daily">
                                    <span class="control-text"><?php echo esc_attr($fees_label); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" name="homey_accomodation[0][cleaning_fee_type]" value="per_stay" checked="checked">
                                    <span class="control-text"><?php echo esc_attr(homey_option('ad_perstay_text')); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="listing_payout"> <?php echo esc_attr('Payment while booking', 'homey'); ?> </label>
                                <input type="text" class="form-control" name="homey_accomodation[0][listing_payout]" value="100" placeholder="<?php echo esc_attr('Check how much initial payment you want the client to deposit while booking', 'homey');?>">
                            </div>
                        </div>  
						<!-- Sadiq ical work -->
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="listing_ical"> <?php echo esc_attr('Import iCal Link'); ?> </label>
                                <input type="text" class="form-control"  name="homey_accomodation[0][listing_ical]" placeholder="<?php echo esc_attr('feed_name|iCal url , feed_name|second iCal url');?> ">
                            </div>
                        </div>   
                    </div>
                    <?php } ?>

                    <!-- City Fee -->
                    <?php if($hide_fields['city_fee'] != 1) { ?>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <label><?php echo esc_attr(homey_option('ad_city_fee')); ?></label>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <input type="text" name="homey_accomodation[0][city_fee]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_city_fee_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" name="homey_accomodation[0][city_fee_type]" value="daily">
                                    <span class="control-text"><?php echo esc_attr($fees_label); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" name="homey_accomodation[0][city_fee_type]" value="per_stay" checked="checked">
                                    <span class="control-text"><?php echo esc_attr(homey_option('ad_perstay_text')); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <hr class="row-separator">
                    <?php if($hide_fields['amenities'] != 1) { ?>
                        <hr class="row-separator">
                        <div class="listing-form-row">
                            <div class="house-features-list aminities0">
                                <label class="label-title"><?php echo esc_attr(homey_option('ad_amenities')); ?></label>

                                <?php
                                $amenities_terms_id = array();

                                if(!empty($acc['listing_amenities'])) {
                                    $amenities_terms = $acc['listing_amenities'];

                                    if ($amenities_terms && !is_wp_error($amenities_terms)) {
                                        foreach ($amenities_terms as $amenity) {
                                            $amenities_terms_id[] = $amenity;
                                        }
                                    }
                                }
                                $amenities = get_terms('listing_amenity', array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
                               
                                if (!empty($amenities)) {
                                    foreach ($amenities as $amenity) {
                                        $checkboxId = esc_attr($amenity->slug);
                                        $checkboxValue = esc_attr($amenity->slug);
                                        $isChecked = in_array($amenity->slug, $amenities_terms_id);
                                        echo '<label class="control control--checkbox">';
                                        echo '<input type="checkbox" name="homey_accomodation[0][listing_amenities][]" id="' . $checkboxId . '" value="' . $checkboxValue . '" ' . ($isChecked ? 'checked' : '') . ' />';
                                        echo '<span class="contro-text">' . esc_attr($amenity->name) . '</span>';
                                        echo '<span class="control__indicator"></span>';
                                        echo '</label>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <?php if($hide_fields['facilities'] != 1) { ?>
                        <div class="listing-form-row">
                            <div class="house-features-list facilities0">
                                <label class="label-title"><?php echo esc_attr(homey_option('ad_facilities')); ?></label>

                                <?php
                                $facilities_terms_id = array();

                                if(!empty($acc['listing_facilities'])) {
                                    $facilities_terms = $acc['listing_facilities'];

                                    if ($facilities_terms && !is_wp_error($facilities_terms)) {
                                        foreach ($facilities_terms as $facility) {
                                            $facilities_terms_id[] = $facility;
                                        }
                                    }
                                }
                                $facilities = get_terms('listing_facility', array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
                               
                                if (!empty($facilities)) {
                                    foreach ($facilities as $facility) {
                                        $checkboxId = esc_attr($facility->slug);
                                        $checkboxValue = esc_attr($facility->slug);
                                        $isChecked = in_array($facility->slug, $facilities_terms_id);
                                        echo '<label class="control control--checkbox">';
                                        echo '<input type="checkbox" name="homey_accomodation[0][listing_facilities][]" id="' . $checkboxId . '" value="' . $checkboxValue . '" ' . ($isChecked ? 'checked' : '') . ' />';
                                        echo '<span class="contro-text">' . esc_attr($facility->name) . '</span>';
                                        echo '<span class="control__indicator"></span>';
                                        echo '</label>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div id="homey_gallery_dragDrop_room" class="media-drag-drop">
                        <div class="upload-icon">
                            <i class="homey-icon homey-icon-picture-landscape-images-photography" aria-hidden="true"></i>
                        </div>
                        <h4>
                            <?php echo homey_option('ad_drag_drop_img'); ?><br>
                            <span><?php echo esc_attr(homey_option('ad_image_size_text')); ?></span>
                        </h4>
                        <button id="select_gallery_images_room_0" href="javascript:;" class="btn btn-secondary"><i class="homey-icon homey-icon-social-media-yelp"></i> <?php echo esc_attr(homey_option('ad_upload_btn')); ?></button>
                    </div>
                    <div id="homey_gallery_container_room_0" class="row">
                    </div>
                    <div id="homey_errors_0"></div>
                    <div id="upload-progress-images_room_0" class="progree_system"></div>

                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <button type="button" data-remove="0" class="btn btn-primary remove-beds"><?php echo esc_attr(homey_option('ad_acc_btn_remove_room'));?></button>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12 text-right">
                    <button type="button" id="add_multi_bedrooms" data-increment="0" class="btn btn-primary"><i class="homey-icon homey-icon-add"></i> <?php echo esc_attr(homey_option('ad_acc_btn_add_other')); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>