<?php
global $homey_local, $hide_fields, $current_user, $experience_data, $experience_meta_data, $edit_experience_id, $homey_booking_type;

wp_get_current_user();
$userID = $current_user->ID;
$make_featured = homey_option('make_featured');

$layout_order = homey_option('experience_form_sections');
$layout_order = $layout_order['enabled'];

$edit_experience_id = intval( trim( $_GET['edit_experience'] ) );
$experience_data    = get_post( $edit_experience_id );

$experience_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$homey_booking_type = homey_booking_type_by_id($edit_experience_id);

if ( ! empty( $experience_data ) && ( $experience_data->post_type == 'experience' ) ) {
    $experience_meta_data = get_post_custom( $experience_data->ID );

    if ( ($experience_data->post_author == $current_user->ID) || homey_is_admin() ) {

        $post_status = $experience_data->post_status;
        $address = ''; //get_post_meta( $edit_experience_id, 'homey_experience_address', true );
        $featured = get_post_meta( $edit_experience_id, 'homey_featured', true );

        $cal_class = $price_terms = $time_class = $what_provided_class = $info_class = $pricing_class = $media_class = $features_class = $location_class = $bedrooms_class = $services_class = $rules_class = '';
        if(isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
            $cal_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'price_terms') {
            $price_terms = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'time') {
            $time_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'what_provided') {
            $what_provided_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'pricing') {
            $pricing_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'media') {
            $media_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'features') {
            $features_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'location') {
            $location_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'bedrooms') {
            $bedrooms_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'services') {
            $services_class = 'active';

        } elseif(isset($_GET['tab']) && $_GET['tab'] == 'rules') {
            $rules_class = 'active';

        } else {
            $info_class = 'active';
        }

        $dashboard = homey_get_template_link('template/dashboard.php');

        $upgrade_link  = add_query_arg( array(
            'dpage' => 'upgrade_featured_exp',
            'upgrade_id' => $edit_experience_id,
        ), $dashboard );
        ?>

        <form autocomplete="off" id="submit_experience_form" name="new_post" method="post" action="#" enctype="multipart/form-data" class="edit-frontend-property">
            <input type="hidden" name="draft_experience_id" value="<?php echo intval($edit_experience_id); ?>">

            <div class="block">
                <div class="block-head table-block">
                    <div class="block-left">
                        <h2 class="title"><?php echo get_the_title($edit_experience_id); ?></h2>
                        <?php get_template_part('single-experience/item-address', null, array('edit_experience_id' => $edit_experience_id,'address_tag_class' => 'item-address', 'prefix_address' => '<i class="homey-icon homey-icon-style-two-pin-marker v-middle"></i>', 'postfix_address' => '')); ?>
                        <?php if(!empty($address)) { ?>
                            <address class="title-address"><i class="homey-icon homey-icon-style-two-pin-marker v-middle"></i>
                                <?php echo esc_attr($address); ?>
                            </address>
                        <?php } ?>
                    </div>

                    <?php if($featured != 1 && $make_featured != 0) { ?>
                        <div class="block-right">
                            <?php if( homey_is_woocommerce() ) { ?>
                                <a data-listid="<?php echo intval($edit_experience_id); ?>" data-featured="1" class="homey-woocommerce-featured-pay btn btn-secondary btn-slim" href="<?php echo esc_url($upgrade_link); ?>"><?php echo esc_attr($homey_local['upgrade_btn']); ?></a>
                            <?php }else{ ?>
                                <a class="btn btn-secondary btn-slim " href="<?php echo esc_url($upgrade_link); ?>"><?php echo esc_attr($homey_local['upgrade_btn']); ?></a>
                            <?php }?>
                        </div>
                    <?php } ?>
                </div>
                <div class="listing-submit-wrap">
                    <a href="<?php echo get_permalink($edit_experience_id); ?>" class="btn btn-dark-grey btn-preview-experience"><?php echo esc_attr($homey_local['view_btn']); ?></a>

                    <?php
                    if($post_status != 'pending' || homey_is_admin()) {
                        if($post_status == 'draft') { ?>
                            <button type="button" class="btn btn-dark-grey draft-experience-validation"><?php esc_html_e('Publish', 'homey'); ?></button>
                        <?php } else { ?>
                            <button class="btn btn-dark-grey btn-save-experience"><?php echo esc_attr($homey_local['update_btn']); ?></button>
                        <?php }
                    }else{ ?>
<!--                        <button title="--><?php //echo esc_attr(ucwords($post_status)); ?><!--" disabled="disabled" class="btn btn-dark-grey disabled">--><?php //echo esc_attr($homey_local['update_btn']); ?><!--</button>-->
                        <button title="<?php echo esc_attr(ucwords($post_status)); ?>" class="btn btn-dark-grey"><?php echo esc_attr($homey_local['update_btn']); ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="homy-taber-module">
                <ul id="form_tabs" class="taber-nav taber-nav-fixed" role="tablist">
                    <?php
                    if ($layout_order) {
                        foreach ($layout_order as $key=>$value) {

                            switch($key) {
                                case 'price_terms':
                                    ?>
                                    <li role="presentation" data-tab="price_terms" class="<?php echo esc_attr($price_terms); ?>">
                                        <a href="#price_terms-tab" aria-controls="price_terms-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('ad_section_price_terms'));?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'time':

                                    ?>
                                    <li role="presentation" data-tab="time" class="<?php echo esc_attr($time_class); ?>">
                                        <a href="#time-tab" aria-controls="time-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('ad_section_time'));?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'what_provided':
                                    ?>
                                    <li role="presentation" data-tab="what-is-provided" class="<?php echo esc_attr($what_provided_class); ?>">
                                        <a href="#what-is-provided-tab" aria-controls="what-is-provided-tab-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('ad_section_what_is_provided'));?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'information':
                                    ?>
                                    <li role="presentation" data-tab="information" class="<?php echo esc_attr($info_class); ?>">
                                        <a href="#information-tab" aria-controls="information-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_section_info'));?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'pricing':
                                    ?>
                                    <li role="presentation" data-tab="pricing" class="<?php echo esc_attr($pricing_class); ?>">
                                        <a href="#pricing-tab" aria-controls="pricing-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_pricing_label'));?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'media':
                                    ?>
                                    <li role="presentation" data-tab="media" class="smb-media <?php echo esc_attr($media_class); ?>">
                                        <a href="#media-tab" aria-controls="media-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_section_media')); ?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'features':
                                    ?>
                                    <li role="presentation" data-tab="features" class="<?php echo esc_attr($features_class); ?>">
                                        <a href="#features-tab" aria-controls="features-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_features')); ?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'location':
                                    ?>
                                    <li role="presentation" data-tab="location" class="<?php echo esc_attr($location_class); ?> homey_find_address_osm">
                                        <a href="#location-tab" aria-controls="location-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_location')); ?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'bedrooms':
                                    ?>
                                    <li role="presentation" data-tab="bedrooms" class="<?php echo esc_attr($bedrooms_class); ?>">
                                        <a href="#bedrooms-tab" aria-controls="bedrooms-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('ad_bedrooms_text')); ?></a>
                                    </li>
                                    <?php
                                    break;

                                case 'term_rules':
                                    ?>
                                    <li role="presentation" data-tab="rules" class="<?php echo esc_attr($rules_class); ?>">
                                        <a href="#rules-tab" aria-controls="rules-tab" role="tab" data-toggle="tab"><?php echo esc_attr(homey_option('experience_ad_terms_rules')); ?></a>
                                    </li>
                                    <?php
                                    break;
                            }
                        }
                    }
                    ?>
                    <li role="presentation" data-tab="calendar" class="<?php echo esc_attr($cal_class); ?> calendar-js">
                        <a href="#calendar-tab" aria-controls="calendar-tab" role="tab" data-toggle="tab"><?php echo esc_attr($homey_local['cal_label']); ?></a>
                    </li>
                </ul>
            </div>

            <div class="block">
                <div class="tab-content">
                    <?php
                    if ($layout_order) {
                        foreach ($layout_order as $key=>$value) {

                            switch($key) {
                                case 'price_terms':
                                    get_template_part('template-parts/dashboard/edit-experience/price-terms');
                                    break;

                                 case 'time':
                                    get_template_part('template-parts/dashboard/edit-experience/time');
                                    break;

                                case 'what_provided':
                                    get_template_part('template-parts/dashboard/edit-experience/what-is-provided');
                                    break;

                                case 'terms':
                                    get_template_part('template-parts/dashboard/edit-experience/experience-terms');
                                    break;

                                case 'information':
                                    get_template_part('template-parts/dashboard/edit-experience/information');
                                    break;

                                case 'pricing':
                                    get_template_part('template-parts/dashboard/edit-experience/pricing');
                                    break;

                                case 'features':
                                    get_template_part('template-parts/dashboard/edit-experience/features');
                                    break;

                                case 'location':
                                    get_template_part('template-parts/dashboard/edit-experience/location');
                                    break;

                                case 'services':
                                    get_template_part('template-parts/dashboard/edit-experience/services');
                                    break;

                                case 'term_rules':
                                    get_template_part('template-parts/dashboard/edit-experience/terms');
                                    break;

                                case 'media':
                                    get_template_part('template-parts/dashboard/edit-experience/media');
                                    break;
                            }
                        }
                    }

                    if($homey_booking_type == 'per_hour') {
                        get_template_part('template-parts/dashboard/edit-experience/calendar-hourly');
                    } else {
                        get_template_part('template-parts/dashboard/edit-experience/calendar');
                    }
                    ?>
                </div>
            </div>


            <?php wp_nonce_field('submit_experience', 'homey_add_experience_nonce'); ?>

            <input type="hidden" name="action" value="update_experience"/>
            <input type="hidden" name="post_author_id" value="<?php echo intval($experience_data->post_author)?>"/>
            <input type="hidden" name="current_tab" id="current_tab" value="<?php echo esc_attr($experience_tab); ?>"/>
            <input type="hidden" name="booking_type" value="<?php echo esc_attr($homey_booking_type); ?>"/>
            <input type="hidden" name="experience_id" value="<?php echo intval( $experience_data->ID ); ?>"/>

        </form><!-- #add-property-form -->

        <?php

    } else {
        esc_html_e('You are not logged in or This experience does not belong to you.', 'homey');
    }

} else {
    esc_html_e('This is not a valid request', 'homey');
}
?>
