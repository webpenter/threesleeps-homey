<?php 
global $homey_prefix, $homey_local, $listing_data, $hide_fields, $homey_booking_type;
$accomodation = get_post_meta($listing_data->ID, $homey_prefix.'accomodation', true);
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'bedrooms') {
    $class = 'in active';
}

if($hide_fields['price_postfix'] != 1) {
    $instance_classes = 'col-sm-12 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
} else {
    $instance_classes = 'col-sm-6 col-xs-12';
    $postfix_classes = 'col-sm-6 col-xs-12';
}

$instant_booking = homey_get_field_meta('instant_booking');
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
$number_of_accomodation = 0;
$number_of_accomodation = count($accomodation);
?>

<div id="bedrooms-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_html(homey_option('ad_bedrooms_text')); ?></h3>
    </div>
    <div class="block-body">
        <?php if($hide_fields['instant_booking'] != 1) { ?>
            <div class="<?php echo esc_attr($instance_classes); ?>">
                <div class="form-group">
                    <label><?php echo esc_attr(homey_option('ad_ins_booking_label')); ?></label>
                    <label class="control control--checkbox radio-tab"><?php echo esc_attr(homey_option('ad_ins_booking_des')); ?>
                        <input type="checkbox" <?php checked( $instant_booking, 1 ); ?> name="instant_booking" value="1">
                        <span class="control__indicator"></span>
                        <span class="radio-tab-inner"></span>
                    </label>
                </div>
            </div>
        <?php } ?>
        <div id="more_bedrooms_main">
            <?php 
            $count = 0;
            printf('<input type="hidden" id="homey_accomodation_count" value="%d">', $number_of_accomodation);
            if(!empty($accomodation)) {
                foreach($accomodation as $acc):

                    $room_id = isset($acc['room_id']) 
                    ? $acc['room_id'] 
                    : 'room_' . round(microtime(true) * 1000) . '_' . mt_rand(0, 999);
                ?>
                    <div class="more_rooms_wrap">
                        <input type="hidden" name="homey_accomodation[<?php echo intval($count); ?>][room_id]" value="<?php echo esc_attr($room_id); ?>">

                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_bedroom_name"><?php echo esc_attr(homey_option('ad_acc_bedroom_name')); ?></label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_bedroom_name]" value="<?php if (isset($acc['acc_bedroom_name'])){ echo sanitize_text_field( $acc['acc_bedroom_name'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_name_plac')); ?>">
                                </div>
                            </div>
                        </div>
						
						<div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_bedroom_description"><?php echo esc_html__('Description'); ?></label>
                                    <textarea rows="5" name="homey_accomodation[<?php echo intval($count); ?>][acc_bedroom_description]" class="form-control" placeholder="<?php echo esc_html__('Add description'); ?>"><?php if (isset($acc['acc_bedroom_description'])){ echo sanitize_text_field( $acc['acc_bedroom_description'] ); } ?></textarea>
                                </div>
                            </div>
                        </div>
						
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_acc_no_of_beds')); ?> </label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_no_of_beds]" value="<?php if (isset($acc['acc_no_of_beds'])){ echo sanitize_text_field( $acc['acc_no_of_beds'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_no_of_beds_plac')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_guests"> <?php echo esc_attr(homey_option('ad_acc_guests')); ?> </label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_guests]" value="<?php if (isset($acc['acc_guests'])){ echo sanitize_text_field( $acc['acc_guests'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_guests_plac')); ?>">
                                </div>
                            </div>
                            <?php if($hide_fields['listing_size'] != 1) { ?>
                                <div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="listing_size"> <?php echo esc_attr(homey_option('ad_listing_size')).homey_req('listing_size'); ?> </label>
                                        <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][listing_size]" value="<?php if (isset($acc['listing_size'])){ echo sanitize_text_field( $acc['listing_size'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_size_placeholder'));?>">
                                    </div>
                                </div>
                                <?php } ?>

                                <?php if($hide_fields['listing_size_unit'] != 1) { ?>
                                <div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="listing_size_unit"> <?php echo esc_attr(homey_option('ad_listing_size_unit')).homey_req('listing_size_unit'); ?> </label>
                                        <input type="text" class="form-control" name="homey_accomodation[<?php echo intval($count); ?>][listing_size_unit]" value="<?php if (isset($acc['listing_size_unit'])){ echo sanitize_text_field( $acc['listing_size_unit'] ); } ?>" <?php homey_required('listing_size_unit'); ?> id="listing_size_unit" placeholder="<?php echo esc_attr(homey_option('ad_listing_size_unit_plac'));?>">
                                    </div>
                                </div>
                                <?php } ?>
                        </div>

                        <!-- Pricing Section -->
                        <hr class="row-separator">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <h3 class="sub-title"><?php echo esc_html__('Pricing', 'homey'); ?></h3>
                            </div>
                            
                            <!-- Night Price Field -->
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <?php if($homey_booking_type == 'per_day_date') { ?>
                                        <label for="night-price"><?php echo esc_html__('Price Per Day', 'homey').homey_req('night_price'); ?></label>
                                        <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][day_date_price]" value="<?php if (isset($acc['day_date_price'])){ echo sanitize_text_field( $acc['day_date_price'] ); } ?>" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_html__('Enter price for 1 day', 'homey'); ?>">
                                    <?php } elseif($homey_booking_type == 'per_hour') { ?>
                                        <label for="night-price"><?php echo esc_html__('Price Per Hour', 'homey').homey_req('night_price'); ?></label>
                                        <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][hour_price]" value="<?php if (isset($acc['hour_price'])){ echo sanitize_text_field( $acc['hour_price'] ); } ?>" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_html__('Enter price for 1 hour', 'homey'); ?>">
                                    <?php } else { ?>
                                        <label for="night-price"><?php echo esc_attr($price_label).homey_req('night_price'); ?></label>
                                        <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][night_price]" value="<?php if (isset($acc['night_price'])){ echo sanitize_text_field( $acc['night_price'] ); } ?>" class="form-control" <?php homey_required('night_price'); ?> placeholder="<?php echo esc_attr($price_plac); ?>">
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
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][cleaning_fee]" value="<?php if (isset($acc['cleaning_fee'])){ echo sanitize_text_field( $acc['cleaning_fee'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_cleaning_fee_plac')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input type="radio" <?php if (isset($acc['cleaning_fee_type'])){ checked( sanitize_text_field( $acc['cleaning_fee_type']), 'daily' ); } ?> name="homey_accomodation[<?php echo intval($count); ?>][cleaning_fee_type]" value="daily">
                                        <span class="control-text"><?php echo esc_attr($fees_label); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input type="radio" <?php if (isset($acc['cleaning_fee_type'])){ checked( sanitize_text_field( $acc['cleaning_fee_type']), 'per_stay' ); } else { echo 'checked="checked"'; } ?> name="homey_accomodation[<?php echo intval($count); ?>][cleaning_fee_type]" value="per_stay">
                                        <span class="control-text"><?php echo esc_attr(homey_option('ad_perstay_text')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
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
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][city_fee]" value="<?php if (isset($acc['city_fee'])){ echo sanitize_text_field( $acc['city_fee'] ); } ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_city_fee_plac')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input type="radio" <?php if (isset($acc['city_fee_type'])){ checked( sanitize_text_field( $acc['city_fee_type']), 'daily' ); } ?> name="homey_accomodation[<?php echo intval($count); ?>][city_fee_type]" value="daily">
                                        <span class="control-text"><?php echo esc_attr($fees_label); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="form-group">
                                    <label class="control control--radio radio-tab">
                                        <input type="radio" <?php if (isset($acc['city_fee_type'])){ checked( sanitize_text_field( $acc['city_fee_type']), 'per_stay' ); } else { echo 'checked="checked"'; } ?> name="homey_accomodation[<?php echo intval($count); ?>][city_fee_type]" value="per_stay">
                                        <span class="control-text"><?php echo esc_attr(homey_option('ad_perstay_text')); ?></span>
                                        <span class="control__indicator"></span>
                                        <span class="radio-tab-inner"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="listing_payout"> <?php echo esc_html__('Payment while booking', 'homey'); ?> </label>
                                    <input type="text" class="form-control" value="<?php if (isset($acc['listing_payout'])){ echo sanitize_text_field( $acc['listing_payout'] ); } ?>" name="homey_accomodation[<?php echo intval($count); ?>][listing_payout]" placeholder="<?php echo esc_html__('Check how much initial payment you want the client to deposit while booking', 'homey');?>">
                                </div>
                            </div>
							<!-- Sadiq ical work -->
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="listing_ical"> <?php echo esc_attr('Import iCal Link'); ?> </label>
                                    <input type="text" class="form-control" value="<?php if (isset($acc['listing_ical'])){ echo sanitize_text_field( $acc['listing_ical'] ); } ?>" name="homey_accomodation[<?php echo intval($count); ?>][listing_ical]" placeholder="<?php echo esc_attr('feed_name|iCal url , feed_name|second iCal url');?> ">
                                </div>
                            </div>
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="listing_ical"> <?php echo esc_attr('Export Link:'); ?> </label>
                                    <?php 
                                        $iCal_export_link = homey_generate_ical_multi_export_link($listing_data->ID, $room_id); 
                                        homey_generate_ical_multi_dot_ics_url($listing_data->ID, $room_id);
                                        $ics_file_url = get_post_meta($listing_data->ID, "icalendar_file_url_room_".$room_id, true);
                                        echo esc_url($iCal_export_link);
                                        // echo esc_url($ics_file_url);

                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if($hide_fields['amenities'] != 1) { ?>
                            <div class="listing-form-row">
                                <div class="house-features-list amenities0">
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
                                            $checkboxId = 'amenity-' . esc_attr($amenity->slug);
                                            $checkboxValue = esc_attr($amenity->name);
                                            $isChecked = in_array($amenity->slug, $amenities_terms_id, true) || in_array($amenity->name, $amenities_terms_id, true);
                                            echo '<label class="control control--checkbox">';
                                            echo '<input type="checkbox" name="homey_accomodation[' . $count . '][listing_amenities][]" id="' . $checkboxId . '" value="' . $checkboxValue . '" ' . ($isChecked ? 'checked' : '') . ' />';
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
                                            $checkboxId = 'facility-' . esc_attr($facility->slug);
                                            $checkboxValue = esc_attr($facility->name);
                                            $isChecked =  in_array($facility->slug, $facilities_terms_id, true) || in_array($facility->name, $facilities_terms_id, true);
                                            echo '<label class="control control--checkbox">';
                                            echo '<input type="checkbox" name="homey_accomodation[' . $count . '][listing_facilities][]" id="' . $checkboxId . '" value="' . $checkboxValue . '" ' . ($isChecked ? 'checked' : '') . ' />';
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
                            <button id="select_gallery_images_room_<?php echo $count; ?>" href="javascript:;" class="btn btn-secondary"><i class="homey-icon homey-icon-social-media-yelp"></i> <?php echo esc_attr(homey_option('ad_upload_btn')); ?></button>
                        </div>
                        <div id="homey_gallery_container_room_<?php echo $count; ?>" class="row">
                            <?php
                            if(isset($acc['select_gallery_images_room'])) {
                                $listing_images = $acc['select_gallery_images_room'];
                            } else {
                                $listing_images =array();
                            }
                            $listing_images = array_unique($listing_images);

                            if( !empty($listing_images[0])) {
                                foreach ($listing_images as $listing_image_id) {

                                    $listing_thumb = wp_get_attachment_image_src( $listing_image_id, 'homey-listing-thumb' );

                                    $img_available = wp_get_attachment_image($listing_image_id, 'thumbnail');

                                    if( !empty($img_available)) {

                                        echo '<div class="col-sm-2 col-xs-4 listing-thumb">';
                                        echo '<figure class="upload-gallery-thumb">';
                                        echo wp_get_attachment_image($listing_image_id, 'thumbnail');
                                        echo '</figure>';
                                        echo '<div class="upload-gallery-thumb-buttons">';
                                        echo '<button class="icon-delete" data-listing-id="' . intval($listing_data->ID) . '"  data-attachment-id="' . intval($listing_image_id) . '"><i class="homey-icon homey-icon-bin-1-interface-essential"></i></button>';
                                        echo '<input type="hidden" class="listing-image-id" name="homey_accomodation['.$count.'][select_gallery_images_room][]" value="' . intval($listing_image_id) . '"/>';
                                        echo '</div>';
                                        echo '<span style="display: none;" class="icon icon-loader"><i class="homey-icon homey-icon-loading-half fa-spin"></i></span>';
                                        echo '</div>';

                                    }
                                }
                            }
                            ?>
                        </div>
                        <div id="homey_errors_<?php echo $count; ?>"></div>
                        <div id="upload-progress-images_room_<?php echo $count; ?>" class="progree_system"></div>

                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <button type="button" data-remove="<?php echo esc_attr( $count-1 ); ?>" class="btn btn-primary remove-beds"><?php echo esc_attr(homey_option('ad_acc_btn_remove_room'));?></button>
                            </div>
                        </div>
                        <hr>
                    </div>
             <?php  $count++;
                endforeach; 
            } ?>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 text-right">
                <button type="button" id="add_multi_bedrooms" data-increment="<?php echo esc_attr( $count-1 ); ?>" class="btn btn-primary"><i class="homey-icon homey-icon-add"></i> <?php echo esc_attr(homey_option('ad_acc_btn_add_other')); ?></button>
            </div>
        </div>
    </div>
</div>