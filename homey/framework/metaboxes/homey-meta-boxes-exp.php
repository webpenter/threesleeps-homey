<?php
/**
 * Registering meta boxes
 *
 * All the definitions of meta boxes are listed below with comments.
 * Please read them CAREFULLY.
 *
 * You also should read the changelog to know what has been changed before updating.
 *
 * For more information, please visit:
 * @link http://www.deluxeblogtips.com/meta-box/docs/define-meta-boxes
 */

/********************* META BOX DEFINITIONS ***********************/

add_filter( 'rwmb_meta_boxes_exp', 'homey_register_metaboxes_exp' );

if( !function_exists( 'homey_register_metaboxes_exp' ) ) {
    function homey_register_metaboxes_exp() {

        if (!class_exists('RW_Meta_Box')) {
            return;
        }

        global $meta_boxes, $wpdb;

        $prefix = 'homey_';
        $homey_local = homey_get_localization();

        $open_time_label = $homey_local['open_time_label'];
        $close_time_label = $homey_local['close_time_label'];
        $closed_label = homey_option('ad_close');

        $openning_hours_list = homey_option('openning_hours_list');
        $openning_hours_list_array = explode( ',', $openning_hours_list );
        $open_hours_array = array("" => $open_time_label);
        $close_hours_array = array("" => $close_time_label);
        if (!empty($openning_hours_list)) {
            foreach ($openning_hours_list_array as $hour) {
                $hour = trim($hour);
                $open_hours_array[$hour] = $hour;
                $close_hours_array[$hour] = $hour;
            }
        }

        $checkin_after_before_list = homey_option('checkin_after_before');
        $checkin_after_before_list = explode( ',', $checkin_after_before_list );
        $checkin_after_before_array = array("" => homey_option('ad_text_select'));
        if (!empty($checkin_after_before_list)) {
            foreach ($checkin_after_before_list as $hour) {
                $hour = trim($hour);
                $checkin_after_before_array[$hour] = $hour;
            }
        }

        $meta_boxes = array();
        $experience_city = array();

        homey_get_terms_array( 'experience_city', $experience_city );

        $homey_site_mode = homey_option('homey_site_mode');

        $dummy_array = array();

        $start_end_hour_array = array();

        $start_hour = strtotime('1:00');
        $end_hour = strtotime('24:00');
        for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
            $start_end_hour_array[date('H:i',$halfhour)] = date(homey_time_format(),$halfhour);
        }

        /* ===========================================================================================
        *   Experiences Custom Post Type Meta
        * ============================================================================================*/

        $meta_boxes[] = array(
            'id' => 'experience-meta-box',
            'title' => esc_html__('Experience Details', 'homey'),
            'pages' => array('experience'),
            'tabs' => array(

                'experience_details' => array(
                    'label' => homey_option('experience_ad_section_info'),
                    'icon' => 'dashicons-admin-home',
                ),
                'experience_price' => array(
                    'label' => homey_option('experience_ad_pricing_label'),
                    'icon' => 'dashicons-money',
                ),
                'experience_gallery' => array(
                    'label' => $homey_local['gallery_heading'],
                    'icon' => 'dashicons-format-gallery',
                ),
                'experience_location' => array(
                    'label' => homey_option('experience_ad_location'),
                    'icon' => 'dashicons-location',
                ),
                'experience_time' => array(
                    'label' => homey_option('experience_ad_section_openning'),
                    'icon' => 'dashicons-admin-home',
                ),
                'experience_providing' => array(
                    'label' => esc_html__('What Will Be Provided', 'homey'),
                    'icon' => 'dashicons-admin-settings',
                ),
                'experience_bring' => array(
                    'label' => esc_html__('What Have To Bring', 'homey'),
                    'icon' => 'dashicons-admin-settings',
                ),
                'home_slider_exp' => array(
                    'label' => esc_html__('Slider', 'homey'),
                    'icon' => 'dashicons-images-alt',
                ),
                'experience_terms_rules' => array(
                    'label' => esc_html__('Terms', 'homey'),
                    'icon' => 'dashicons-admin-settings',
                ),
                'settings_exp' => array(
                    'label' => esc_html__('Settings', 'homey'),
                    'icon' => 'dashicons-admin-settings',
                ),

            ),
            'tab_style' => 'left',
            'fields' => array(
                array(
                    'id' => "{$prefix}experience_describe_yourself",
                    'name' => homey_option('experience_describe_yourself'),
                    'placeholder' => homey_option('experience_describe_yourself'),
                    'type' => 'textarea',
                    'std' => "",
                    'columns' => 6,
                    'tab' => 'experience_details',
                ),
                
                array(
                    'id' => "{$prefix}guests",
                    'name' => homey_option('experience_ad_no_of_guests'),
                    'placeholder' => homey_option('experience_ad_no_of_guests_plac'),
                    'type' => 'text',
                    'std' => "",
                    'columns' => 6,
                    'tab' => 'experience_details',
                ),
                array(
                    'name' => homey_option('ad_is_featured_label'),
                    'id' => "{$prefix}featured",
                    'desc' => '',
                    'type' => 'radio',
                    'std' => 0,
                    'options' => array(
                        1 => homey_option('ad_text_yes'),
                        0 => homey_option('ad_text_no')
                    ),
                    'columns' => 6,
                    'tab' => 'experience_details',
                ),

                array(
                    'id' => "{$prefix}exp_affiliate_link",
                    'name' => esc_html__('Affiliate Link', 'homey'),
                    'placeholder' => esc_html__('Enter affiliate link here', 'homey'),
                    'type' => 'url',
                    'std' => "",
                    'columns' => 6,
                    'tab' => 'experience_details',
                ),

                array(
                    'id' => "{$prefix}instant_booking",
                    'name' => homey_option('experience_ad_ins_booking_label'),
                    'placeholder' => homey_option('experience_ad_ins_booking_des'),
                    'desc' => homey_option('experience_ad_ins_booking_des'),
                    'type' => 'checkbox',
                    'std' => '',
                    'columns' => 12,
                    'tab' => 'experience_price',
                ),
                array(
                    'id' => "{$prefix}night_price",
                    'name' => homey_option('ad_price_label'),
                    'placeholder' => homey_option('ad_price_plac'),
                    'desc' => homey_option('ad_price_plac'),
                    'type' => 'text',
                    'columns' => 6,
                    'tab' => 'experience_price',
                ),
                array(
                    'id' => "{$prefix}price_postfix",
                    'name' => homey_option('experience_ad_price_postfix_label'),
                    'placeholder' => homey_option('experience_ad_price_postfix_plac'),
                    'desc' => homey_option('experience_ad_price_postfix_plac'),
                    'type' => 'text',
                    'columns' => 6,
                    'tab' => 'experience_price',
                ),


                /*--------------------------------------------------------------------------------
                * Media
                **-------------------------------------------------------------------------------*/
                array(
                    'name' => $homey_local['gallery_heading'],
                    'id' => "{$prefix}experience_images",
                    'desc' => $homey_local['image_size_text'],
                    'type' => 'image_advanced',
                    'max_file_uploads' => 50,
                    'columns' => 12,
                    'tab' => 'experience_gallery',
                ),
                array(
                    'name' => esc_html__('Experience Video', 'homey'),
                    'id' => "{$prefix}exp_video_url",
                    'desc' => esc_html__('Put experience video link.', 'homey'),
                    'type' => 'text',
                    'columns' => 12,
                    'tab' => 'experience_gallery',
                ),

                /*--------------------------------------------------------------------------------
                * Location
                **-------------------------------------------------------------------------------*/
                array(
                    'name' => $homey_local['experience_map_label'],
                    'id' => "{$prefix}show_map",
                    'type' => 'radio',
                    'std' => 1,
                    'options' => array(
                        1 => $homey_local['text_show'],
                        0 => $homey_local['text_hide']
                    ),
                    'columns' => 12,
                    'tab' => 'experience_location',
                ),
                array(
                    'name' => homey_option('experience_ad_aptSuit'),
                    'id' => "{$prefix}aptSuit",
                    'type' => 'text',
                    'placeholder' => homey_option('experience_ad_aptSuit_placeholder'),
                    'columns' => 6,
                    'tab' => 'experience_location',
                ),
                array(
                    'name' => homey_option('experience_ad_zipcode'),
                    'id' => "{$prefix}zip",
                    'type' => 'text',
                    'placeholder' => homey_option('experience_ad_zipcode_placeholder'),
                    'columns' => 6,
                    'tab' => 'experience_location',
                ),
                array(
                    'id' => "{$prefix}experience_address",
                    'name' => homey_option('experience_ad_address'),
                    'placeholder' => homey_option('experience_ad_address_placeholder'),
                    'desc' => $homey_local['address_experience_des'],
                    'type' => 'text',
                    'std' => '',
                    'columns' => 12,
                    'tab' => 'experience_location',
                ),
                array(
                    'id' => "{$prefix}experience_location",
                    'name' => homey_option('experience_ad_drag_pin'),
                    'desc' => $homey_local['drag_pin_des'],
                    'api_key' => homey_map_api_key(),
                    'type' => homey_metabox_map_type(),
                    'std' => homey_option('default_lat').','.homey_option('default_lng').',15',//'25.686540,-80.431345,15',
                    'style' => 'width: 100%; height: 410px',
                    'address_field' => "{$prefix}experience_address",
                    'columns' => 12,
                    'language' => get_locale(),
                    'tab' => 'experience_location',
                ),

                /*--------------------------------------------------------------------------------
                * Terms & Rules
                **-------------------------------------------------------------------------------*/
                array(
                    'id' => "{$prefix}cancellation_policy",
                    'name' => homey_option('experience_ad_cancel_policy'),
                    'placeholder' => homey_option('experience_ad_cancel_policy_plac'),
                    'type' => 'select',
                    "options" => homey_get_cancel_policy_options(1),
                    'columns' => 12,
                    'tab' => 'experience_terms_rules',
                ),
                array(
                    'name' => homey_option('experience_ad_add_rules_info_optional'),
                    'id' => "{$prefix}additional_rules",
                    'type' => 'textarea',
                    'placeholder' => '',
                    'columns' => 12,
                    'tab' => 'experience_terms_rules',
                ),

                array(
                    'name' => homey_option('experience_ad_smoking_allowed'),
                    'id' => "{$prefix}experience_smoke",
                    'type' => 'radio',
                    'std' => 0,
                    'options' => array(
                        1 => homey_option('experience_ad_text_yes'),
                        0 => homey_option('experience_ad_text_no'),
                    ),
                    'columns' => 6,
                    'tab' => 'experience_terms_rules',
                ),

                array(
                    'name' => homey_option('experience_ad_pets_allowed'),
                    'id' => "{$prefix}experience_pets",
                    'type' => 'radio',
                    'std' => 1,
                    'options' => array(
                        1 => homey_option('experience_ad_text_yes'),
                        0 => homey_option('experience_ad_text_no'),
                    ),
                    'columns' => 6,
                    'tab' => 'experience_terms_rules',
                ),

                array(
                    'name' => homey_option('experience_ad_party_allowed'),
                    'id' => "{$prefix}experience_party",
                    'type' => 'radio',
                    'std' => 0,
                    'options' => array(
                        1 => homey_option('experience_ad_text_yes'),
                        0 => homey_option('experience_ad_text_no'),
                    ),
                    'columns' => 6,
                    'tab' => 'experience_terms_rules',
                ),

                array(
                    'name' => homey_option('experience_ad_children_allowed'),
                    'id' => "{$prefix}experience_children",
                    'type' => 'radio',
                    'std' => 1,
                    'options' => array(
                        1 => homey_option('experience_ad_text_yes'),
                        0 => homey_option('experience_ad_text_no'),
                    ),
                    'columns' => 6,
                    'tab' => 'experience_terms_rules',
                ),

                /*--------------------------------------------------------------------------------
               * Time
               **-------------------------------------------------------------------------------*/

                array(
                    'name' => esc_html__('Start', 'homey'),
                    'id' => "{$prefix}start_end_open",
                    'type' => 'select',
                    'options' => $open_hours_array,
                    'columns' => 3,
                    'tab' => 'experience_time',
                ),
                array(
                    'name' => esc_html__('End', 'homey'),
                    'id' => "{$prefix}start_end_close",
                    'type' => 'select',
                    'options' => $open_hours_array,
                    'columns' => 3,
                    'tab' => 'experience_time',
                ),

                /*--------------------------------------------------------------------------------
                * Settings
                **-------------------------------------------------------------------------------*/
                array(
                    'name' => esc_html__('What to display in the sidebar?', 'homey'),
                    'id' => "{$prefix}booking_or_contact_exp",
                    'desc' => esc_html__('Select what to display in the sidebar of experience detail page', 'homey'),
                    'type' => 'select',
                    'std' => '',
                    'options' => array(
                        '' => esc_html__('Default (Same settings as theme options)', 'homey'),
                        'booking_form' => esc_html__('Booking Form', 'homey'),
                        'contact_form' => esc_html__('Contact Form', 'homey'),
                        'contact_form_to_guest' => esc_html__('Contact Form To Guest and Booking To User', 'homey'),
                    ),
                    'columns' => 12,
                    'tab' => 'settings_exp',
                ),

                array(
                    'id' => "nothing_provided_btn",
                    'tab' => 'experience_providing',
                    'name' => esc_html__('What Will Be Provided?', 'homey'),
                    'type' => 'checkbox',
                    'default' => '0',
                    'desc' => esc_html__('Nothing to provide', 'homey'),
                ),// poroviding btn

                /*--------------------------------------------------------------------------------
                * Provided items
                **-------------------------------------------------------------------------------*/
                array(
                    'id'     => "{$prefix}what_to_provided",
                    // Group field
                    'type'   => 'group',
                    // Clone whole group?
                    'clone'  => true,
                    'sort_clone' => true,
                    'tab' => 'experience_providing',
                    'hidden' => array( 'nothing_provided_btn', '=', '1' ),
                    'fields' => array(
                        array(
                            'name' => esc_html__('Name', 'homey'),
                            'id'   => "wwbp_name",
                            'placeholder'   => esc_html__('Enter item name', 'homey'),
                            'type' => 'text',
                            'columns' => 12,

                            )
                        
                        )
                ),

                /*--------------------------------------------------------------------------------
                * Bring items
                **-------------------------------------------------------------------------------*/
                array(
                    'id'     => "{$prefix}nothing_bring_btn",
                    'type' => 'checkbox',
                    'tab' => 'experience_bring',
                    'name' => esc_html__('What Have To Bring?', 'homey'),
                    'std' => '',
                    'columns' => 12,
                    'default' => '0',
                    'desc' => esc_html__('Nothing to bring', 'homey'),

                ),// bring btn
                array(
                    'id'     => "{$prefix}what_to_bring",
                    // Group field
                    'type'   => 'group',
                    // Clone whole group?
                    'clone'  => true,
                    'sort_clone' => true,
                    'tab' => 'experience_bring',
                    'hidden' => array( 'nothing_bring_btn', '=', '1' ),
                    // Sub-fields
                    'fields' => array(
                        array(
                            'name' => esc_html__((homey_option('experience_ad_what_bring_item_type')), 'homey'),
                            'id'   => "wbit_name",
                            'placeholder'   => esc_html__((homey_option('experience_ad_what_bring_item_type')), 'homey'),
                            'type' => 'text',
                            'columns' => 12,
                        )
                    ),
                ),// bring

                /*--------------------------------------------------------------------------------
                * Homepage Slider
                **-------------------------------------------------------------------------------*/
                array(
                    'name' => esc_html__('Do you want to display this property in the slider?', 'homey'),
                    'id' => "{$prefix}homeslider_exp",
                    'desc' => esc_html__('Upload an image below if you selected yes.', 'homey'),
                    'type' => 'radio',
                    'std' => 'no',
                    'options' => array(
                        'yes' => esc_html__('Yes', 'homey'),
                        'no'  => esc_html__('No', 'homey'),
                    ),
                    'columns' => 12,
                    'tab' => 'home_slider_exp',
                ),
                array(
                    'name' => esc_html__('Slider Image', 'homey'),
                    'id' => "{$prefix}slider_image_exp",
                    'desc' => esc_html__('Suggested size 1920 x 600', 'homey'),
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'columns' => 12,
                    'tab' => 'home_slider_exp',
                ),

                /*--------------------------------------------------------------------------------
               * Settings
               **-------------------------------------------------------------------------------*/
                array(
                    'name' => esc_html__('What to display in the sidebar?', 'homey'),
                    'id' => "{$prefix}booking_or_contact_exp",
                    'desc' => esc_html__('Select what to display in the sidebar of listing detail page', 'homey'),
                    'type' => 'select',
                    'std' => '',
                    'options' => array(
                        '' => esc_html__('Default (Same settings as theme options)', 'homey'),
                        'booking_form' => esc_html__('Booking Form', 'homey'),
                        'contact_form' => esc_html__('Contact Form', 'homey'),
                        'contact_form_to_guest' => esc_html__('Contact Form To Guest and Booking To User', 'homey'),
                    ),
                    'columns' => 12,
                    'tab' => 'settings',
                ),
            )
        );

        /* ===========================================================================================
       *   Experience Template
       * ============================================================================================*/
        $experience_types = array();
        $experience_amenity = array();
        $experience_facility = array();
        $experience_country = array();
        $experience_state = array();
        $experience_city = array();
        $experience_area = array();
        homey_get_terms_array( 'experience_type', $experience_types );
        homey_get_terms_array( 'experience_amenity', $experience_amenity );
        homey_get_terms_array( 'experience_facility', $experience_facility );
        homey_get_terms_array( 'experience_country', $experience_country );
        homey_get_terms_array( 'experience_state', $experience_state );
        homey_get_terms_array( 'experience_city', $experience_city );
        homey_get_terms_array( 'experience_area', $experience_area );

        $meta_boxes[] = array(
            'id'        => 'homey_experience_template',
            'title'     => esc_html__('Experience Advanced Options', 'homey'),
            'pages'     => array( 'page' ),
            'context' => 'normal',
            'show'       => array(
                'template' => array(
                    'template/template-experience-list.php',
                    'template/template-experience-list-v2.php',
                    'template/template-experience-grid.php',
                    'template/template-experience-grid-v2.php',
                    'template/template-experience-card.php',
                    'template/template-half-map-exp.php',
                    'template/template-experience-sticky-map.php'
                ),
            ),
            'fields'    => array(
                array(
                    'name'      => esc_html__('Order By', 'homey'),
                    'id'        => $prefix . 'experiences_sort',
                    'type'      => 'select',
                    'options'   => array(
                        'd_date'  => esc_html__('Date New to Old', 'homey'),
                        'a_date'  => esc_html__('Date Old to New', 'homey'),
                        'd_price' => esc_html__('Price (High to Low)', 'homey'),
                        'a_price' => esc_html__('Price (Low to High)', 'homey'),
                        'd_rating' => esc_html__('Rating', 'homey'),
                        'featured_top' => esc_html__('Show Featured on Top', 'homey'),
                    ),
                    'std'       => array( 'd_date' ),
                    'desc'      => '',
                    'columns' => 6,
                ),
                array(
                    'id' => $prefix."experiences_num",
                    'name' => esc_html__('Number of experiences to show', 'homey'),
                    'desc' => "",
                    'type' => 'number',
                    'std' => "9",
                    'columns' => 6
                ),

                array(
                    'name'      => homey_option('ad_experience_type'),
                    'id'        => $prefix . 'types_exp',
                    'type'      => 'select',
                    'options'   => $experience_types,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => homey_option('experience_ad_country'),
                    'id'        => $prefix . 'countries_exp',
                    'type'      => 'select',
                    'options'   => $experience_country,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => homey_option('experience_ad_state'),
                    'id'        => $prefix . 'states_exp',
                    'type'      => 'select',
                    'options'   => $experience_state,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => homey_option('experience_ad_city'),
                    'id'        => $prefix . 'cities_exp',
                    'type'      => 'select',
                    'options'   => $experience_city,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => homey_option('experience_ad_area'),
                    'id'        => $prefix . 'areas_exp',
                    'type'      => 'select',
                    'options'   => $experience_area,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => esc_html__('Amenities', 'homey'),
                    'id'        => $prefix . 'amenities_exp',
                    'type'      => 'select',
                    'options'   => $experience_amenity,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),
                array(
                    'name'      => esc_html__('Facilities', 'homey'),
                    'id'        => $prefix . 'facilities_exp',
                    'type'      => 'select',
                    'options'   => $experience_facility,
                    'desc'      => '',
                    'columns' => 6,
                    'multiple' => true
                ),

            )
        );



        /* ===========================================================================================
        *   Taxonomies
        * ============================================================================================*/
        $meta_boxes[] = array(
            'id'        => 'homey_taxonomies_exp',
            'title'     => esc_html__('Other Settings', 'homey' ),
            'taxonomies' => array( 'experience_type', 'experience_city', 'experience_country', 'experience_state', 'experience_area' ),

            'fields'    => array(
                array(
                    'name'      => esc_html__('Image', 'homey' ),
                    'id'        => $prefix . 'taxonomy_img_exp',
                    'type'      => 'image_advanced',
                    'max_file_uploads' => 1,
                ),
                
            )
        );

        $meta_boxes[] = array(
            'id'        => 'homey_exp_taxonomies_marker',
            'title'     => '',
            'taxonomies' => array( 'experience_type' ),

            'fields'    => array(
                array(
                    'name'      => esc_html__('Google Map Marker Icon', 'homey' ),
                    'id'        => $prefix . 'exp_marker_icon',
                    'type'      => 'image_advanced',
                    'class'      => 'homey_full_width',
                    'max_file_uploads' => 1,
                ),
                array(
                    'name'      => esc_html__('Google Map Marker Retina Icon', 'homey' ),
                    'id'        => $prefix . 'exp_marker_retina_icon',
                    'type'      => 'image_advanced',
                    'class'      => 'homey_full_width',
                    'max_file_uploads' => 1,
                )
            )
        );

        $meta_boxes = apply_filters('homey_theme_meta_exp', $meta_boxes);
        return $meta_boxes;

    }
} // End Meta boxes

/*-----------------------------------------------------------------------------------*/
// Get terms array
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_terms_array' ) ) {
    function homey_get_terms_array( $tax_name, &$terms_array ) {
        $tax_terms = get_terms( $tax_name, array(
            'hide_empty' => false,
        ) );
        homey_add_term_children( 0, $tax_terms, $terms_array );
    }
}


if ( ! function_exists( 'homey_add_term_children' ) ) :
    function homey_add_term_children( $parent_id, $tax_terms, &$terms_array, $prefix = '' ) {
        if ( ! empty( $tax_terms ) && ! is_wp_error( $tax_terms ) ) {
            foreach ( $tax_terms as $term ) {
                if ( $term->parent == $parent_id ) {
                    $terms_array[ $term->slug ] = $prefix . $term->name;
                    homey_add_term_children( $term->term_id, $tax_terms, $terms_array, $prefix . '- ' );
                }
            }
        }
    }
endif;
?>
