<?php
if( !function_exists('homey_get_experience_data')) {
    function homey_get_experience_data($field, $id=0) {
        $prefix = 'homey_';
        $post_id = get_the_ID();
        if($id > 0){
            $post_id = $id;
        }
        $data = get_post_meta($post_id, $prefix.$field, true);

        if($data != '') {
            return $data;
        }
        return '';
    }
}

if( !function_exists('homey_experience_data')) {
    function homey_experience_data($field) {
        echo homey_get_experience_data($field);
    }
}

if( !function_exists('homey_get_experience_data_by_id')) {
    function homey_get_experience_data_by_id($field, $ID) {
        $prefix = 'homey_';
        $data = get_post_meta($ID, $prefix.$field, true);

        if($data != '') {
            return $data;
        }
        return '';
    }
}

if( !function_exists('homey_experience_data_by_id')) {
    function homey_experience_data_by_id($field, $ID) {
        echo homey_get_experience_data_by_id($field, $ID);
    }
}

if( !function_exists('homey_experience_permalink')) {
    function homey_experience_permalink($experience_id = null) {

        $link = esc_url(get_permalink());

        if( isset($_REQUEST['arrive']) ){

            $guest = isset($_REQUEST['guest']) ? $_REQUEST['guest'] : '';
            $check_in = isset($_REQUEST['arrive']) ? $_REQUEST['arrive'] : '';

            $check_in  =   sanitize_text_field ( $check_in );
            $guest   =   sanitize_text_field ( $guest );
            if(!empty(trim($check_in))){
                $link       =   add_query_arg( 'arrive', (trim($check_in)), $link);
            }
            if(!empty(trim($guest))){
                $link       =   add_query_arg( 'guest',(trim($guest )), $link);
            }
        }

        return $link;
    }
}

if( !function_exists('homey_get_experience_dates_for_booking')) {
    function homey_get_experience_dates_for_booking() {
        $dates_array = array();
        $dates_array['arrive'] = isset($_GET['arrive']) ? $_GET['arrive'] : '';
        $dates_array['guest'] = isset($_GET['guest']) ? $_GET['guest'] : '';
        $dates_array['new_reser_exp_request_user_email'] = isset($_GET['new_reser_exp_request_user_email']) ? $_GET['new_reser_exp_request_user_email'] : '';

        return $dates_array;
    }
}

if( !function_exists('homey_exp_field_meta')) {
    function homey_exp_field_meta($field_name) {
        global $experience_meta_data;

        $prefix = 'homey_';
        $field_name = $prefix.$field_name;

        if (isset($experience_meta_data[$field_name])) {
           echo sanitize_text_field($experience_meta_data[$field_name][0]);
        } else {
            return;
        }
    }
}

if( !function_exists('homey_get_exp_field_meta')) {
    function homey_get_exp_field_meta($field_name) {
        global $experience_meta_data;

        $prefix = 'homey_';
        $field_name = $prefix.$field_name;

        if (isset($experience_meta_data[$field_name])) {
           return sanitize_text_field($experience_meta_data[$field_name][0]);
        } else {
            return;
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Experience filter
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_experience_filter_callback') ) {
    function homey_experience_filter_callback( $lsiting_qry ) {
        global $paged;
        $prefix = 'homey_';

        $page_id = get_the_ID();

        $tax_query = array();
        $meta_query = array();


        $types = get_post_meta( $page_id, $prefix.'types_exp', false );
        if ( ! empty( $types ) && is_array( $types ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_type',
                'field' => 'slug',
                'terms' => $types
            );
        }

        $amenities = get_post_meta( $page_id, $prefix.'amenities_exp', false );
        if ( ! empty( $amenities ) && is_array( $amenities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_amenity',
                'field' => 'slug',
                'terms' => $amenities
            );
        }

        $facilities = get_post_meta( $page_id, $prefix.'facilities_exp', false );
        if ( ! empty( $facilities ) && is_array( $facilities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_facility',
                'field' => 'slug',
                'terms' => $facilities
            );
        }

        $countries = get_post_meta( $page_id, $prefix.'countries_exp', false );
        if ( ! empty( $countries ) && is_array( $countries ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_country',
                'field' => 'slug',
                'terms' => $countries
            );
        }

        $states = get_post_meta( $page_id, $prefix.'states_exp', false );
        if ( ! empty( $states ) && is_array( $states ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_state',
                'field' => 'slug',
                'terms' => $states
            );
        }

        $cities = get_post_meta( $page_id, $prefix.'cities_exp', false );
        if ( ! empty( $cities ) && is_array( $cities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_city',
                'field' => 'slug',
                'terms' => $cities
            );
        }

        $areas = get_post_meta( $page_id, $prefix.'areas_exp', false );
        if ( ! empty( $areas ) && is_array( $areas ) ) {
            $tax_query[] = array(
                'taxonomy' => 'experience_area',
                'field' => 'slug',
                'terms' => $areas
            );
        }


        $tax_count = count( $tax_query );
        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ) {
            $lsiting_qry['tax_query'] = $tax_query;
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

        if( $meta_count > 0 ) {
            $lsiting_qry['meta_query'] = $meta_query;
        }

        //print_r($lsiting_qry);
        return $lsiting_qry;
    }
}
add_filter('homey_experience_filter', 'homey_experience_filter_callback');

/* -----------------------------------------------------------------------------------------------------------
*  Stripe upgrade to featured payment
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_exp_stripe_payment_for_featured') ) {
    function homey_exp_stripe_payment_for_featured() {

        $userID = get_current_user_id();
        $experience_id     = isset($_GET['upgrade_id']) ? $_GET['upgrade_id'] : '';
        $amount = floatval( homey_option('price_featured_experience') );

        if(get_post_type( $experience_id ) == 'experience'){
            require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );
            $stripe_payments = new Homey_Stripe($userID);
            $description = esc_html__( 'Upgrade to Featured, Experience ID','homey').' '.$experience_id;

            print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
            $metadata=array(
                'experience_id'        =>  $experience_id,
                'userID'            =>  $userID,
                'payment_type'      =>  'featured_fee',
                'message'           =>  esc_html__( 'Featured Fee','homey')
            );
            $stripe_payments->homey_stripe_form($amount, $metadata, $description, 1);
            print'
        </div>';
        }

    }
}

/* --------------------------------------------------------------------------
* Experiences load more
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_homey_loadmore_experiences', 'homey_loadmore_experiences' );
add_action( 'wp_ajax_homey_loadmore_experiences', 'homey_loadmore_experiences' );

if ( !function_exists( 'homey_loadmore_experiences' ) ) {
    function homey_loadmore_experiences() {
        global $post, $homey_prefix, $homey_local;
        $homey_prefix = 'homey_';
        $homey_local = homey_get_localization();

        $fake_loop_offset = 0;
        $tax_query = array();
        $meta_query = array();

        $type = sanitize_text_field($_POST['type']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $city = sanitize_text_field($_POST['city']);
        $area = sanitize_text_field($_POST['area']);

        $experience_style = sanitize_text_field($_POST['style']);
        $experience_type = homey_traverse_comma_string($type);
        $experience_country = homey_traverse_comma_string($country);
        $experience_state = homey_traverse_comma_string($state);
        $experience_city = homey_traverse_comma_string($city);
        $experience_area = homey_traverse_comma_string($area);
        $featured = sanitize_text_field($_POST['featured']);
        $posts_limit = sanitize_text_field($_POST['limit']);
        $sort_by = sanitize_text_field($_POST['sort_by']);
        $offset = sanitize_text_field($_POST['offset']);
        $paged = sanitize_text_field($_POST['paged']);

        $multiple_factor = 1;
        if($paged > 1){
            $multiple_factor = $paged - 1;
        }

        //originally commented, but zahid removed after client bug report
        $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
        $authorid = isset($_POST['authorid']) ? sanitize_text_field($_POST['authorid']) : '';
        //originally commented, but zahid removed after client bug report
        $wp_query_args = array(
            'ignore_sticky_posts' => 1
        );

        if (!empty($experience_type)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_type',
                'field' => 'slug',
                'terms' => $experience_type
            );
        }

        if (!empty($experience_country)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_country',
                'field' => 'slug',
                'terms' => $experience_country
            );
        }
        if (!empty($experience_state)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_state',
                'field' => 'slug',
                'terms' => $experience_state
            );
        }
        if (!empty($experience_city)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_city',
                'field' => 'slug',
                'terms' => $experience_city
            );
        }
        if (!empty($experience_area)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_area',
                'field' => 'slug',
                'terms' => $experience_area
            );
        }

        //originally commented, but zahid removed after client bug report
        if($author == 'yes') {
            $wp_query_args['author'] = $authorid;
        }
        //originally commented, but zahid removed after client bug report

        if ( $sort_by == 'a_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'a_rating' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'experience_total_rating';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_rating' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'experience_total_rating';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $wp_query_args['orderby'] = 'meta_value date';
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['order'] = 'DESC';
            $is_featured_posts_on_top = 1;
        }

        if (!empty($featured)) {

            if( $featured == "yes" ) {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '1';
            } else {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '0';
            }
        }

        $tax_count = count( $tax_query );


        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $wp_query_args['tax_query'] = $tax_query;
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

        if( $meta_count > 0 ) {
            $wp_query_args['meta_query'] = $meta_query;
        }

        $wp_query_args['post_status'] = 'publish';

        if (empty($posts_limit)) {
            $posts_limit = get_option('posts_per_page');
        }
        $wp_query_args['posts_per_page'] = $posts_limit;

        if (!empty($paged)) {
            $wp_query_args['paged'] = $paged;
        } else {
            $wp_query_args['paged'] = 1;
        }

        //if (!empty($offset) and $paged > 1) {
        if (empty($offset) and $paged > 1) {
           $wp_query_args['offset'] = $multiple_factor * $posts_limit;
        } else {
            if($paged == 1) $offset = $posts_limit;
            $wp_query_args['offset'] = $offset ;
        }

        $fake_loop_offset = $offset;
        $wp_query_args['post_type'] = 'experience';

        // this to group by featured meta key
        if($is_featured_posts_on_top > 0){
            $the_query = Homey_Exp_Query::get_wp_query($atts, $paged);
        }else{
            $the_query = new WP_Query($wp_query_args);
        }

        if ($the_query->have_posts()) :
            while ($the_query->have_posts()) : $the_query->the_post();

                if($experience_style == 'card') {
                    get_template_part('template-parts/experience/experience-card');
                }elseif($experience_style == 'grid-v2' || $experience_style == 'list-v2') {
                    get_template_part('template-parts/experience/experience-item-v2');
                } else {
                    get_template_part('template-parts/experience/experience-item');
                }

            endwhile;
            wp_reset_postdata();
        else:
            echo 'no_result';
        endif;

        wp_die();
    }
}

add_filter('homey_optimized_filter', 'homey_optimized_filter_callback', 10, 5);
if( !function_exists('homey_exp_optimized_filter_callback') ) {
    function homey_exp_optimized_filter_callback( $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng ) {

        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        if ( ! ( $north_east_lat && $north_east_lng && $south_west_lat && $south_west_lng ) ) {
            return $query_args;
        }

        $sql = $wpdb->prepare(
            "
            SELECT experience_id 
            FROM $table_name 
            WHERE latitude <= %s
            AND latitude >= %s
            AND longitude <= %s
            AND longitude >= %s
            ",
            $north_east_lat,
            $south_west_lat,
            $south_west_lng,
            $north_east_lng
        );

        $post_ids = $wpdb->get_results( $sql, OBJECT_K );

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        $query_args[ 'post__in' ] = array_keys( (array) $post_ids );
        return $query_args;
    }
}

add_action( 'wp_ajax_nopriv_homey_header_exp_map', 'homey_header_exp_map' );
add_action( 'wp_ajax_homey_header_exp_map', 'homey_header_exp_map' );
if( !function_exists('homey_header_exp_map') ) {
    function homey_header_exp_map() {
        $local = homey_get_localization();
        $tax_query = array();
        $meta_query = array();
        $experiences = array();

        $cgl_types = homey_option('experience_cgl_types');
        $price_separator = homey_option('currency_separator');

        //check_ajax_referer('homey_map_ajax_nonce', 'security');

        $prefix = 'homey_';
        $query_args = array(
            'post_type' => 'experience',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        if( !empty( $_POST["optimized_loading"] ) ) {
            $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
            $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
            $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
            $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

            $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
        }

        $map_cities = isset($_POST['map_cities']) ? $_POST['map_cities'] : '';

        if (!empty($map_cities)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_city',
                'field' => 'slug',
                'terms' => $map_cities
            );
        }

        $tax_count = count($tax_query);

        $tax_query['relation'] = 'AND';

        if ($tax_count > 0) {
            $query_args['tax_query'] = $tax_query;
        }

        $query_args = new WP_Query( $query_args );

        while( $query_args->have_posts() ): $query_args->the_post();

            $experience_id = get_the_ID();
            $address        = get_post_meta( get_the_ID(), $prefix.'experience_address', true );
            $guests         = get_post_meta( get_the_ID(), $prefix.'guests', true );
            $baths          = get_post_meta( get_the_ID(), $prefix.'baths', true );
            $night_price          = get_post_meta( get_the_ID(), $prefix.'night_price', true );
            $location = get_post_meta( get_the_ID(), $prefix.'experience_location',true);
            $lat_long = explode(',', $location);

            $experience_type = wp_get_post_terms( get_the_ID(), 'experience_type', array("fields" => "ids") );

            $experience_price = homey_exp_get_price_by_id($experience_id);

            $lat = $long = '';
            if(!empty($lat_long[0])) {
                $lat = $lat_long[0];
            }

            if(!empty($lat_long[1])) {
                $long = $lat_long[1];
            }

            $experience = new stdClass();

            $experience->id = $experience_id;
            $experience->title = get_the_title();
            $experience->lat = $lat;
            $experience->long = $long;
            $experience->price = homey_formatted_price($experience_price, false, true).'<sub>'.homey_exp_get_price_label_by_id($experience_id).'</sub>';
            $experience->address = $address;
            $experience->guests = $guests;
            if($cgl_types != 1) {
                $experience->experience_type = '';
            } else {
                $experience->experience_type = homey_taxonomy_simple('experience_type');
            }

            if( has_post_thumbnail( $experience_id ) ) {
                $experience->thumbnail = get_the_post_thumbnail( $experience_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
            }else{
                $experience->thumbnail = homey_get_image_placeholder( 'homey-listing-thumb' );
            }

            $experience->url = get_permalink();

            $experience->icon = get_template_directory_uri() . '/images/custom-marker.png';

            $experience->retinaIcon = get_template_directory_uri() . '/images/custom-marker.png';

            if(!empty($experience_type)) {
                foreach( $experience_type as $term_id ) {

                    $experience->term_id = $term_id;

                    $icon_id = get_term_meta($term_id, 'homey_exp_marker_icon', true);
                    $retinaIcon_id = get_term_meta($term_id, 'homey_exp_marker_retina_icon', true);

                    $icon = wp_get_attachment_image_src( $icon_id, 'full' );
                    $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

                    if( !empty($icon['0']) ) {
                        $experience->icon = $icon['0'];
                    }
                    if( !empty($retinaIcon['0']) ) {
                        $experience->retinaIcon = $retinaIcon['0'];
                    }
                }
            }

            array_push($experiences, $experience);

        endwhile;

        wp_reset_postdata();

        if( count($experiences) > 0 ) {
            echo json_encode( array( 'getExperiences' => true, 'experiences' => $experiences ) );
            exit();
        } else {
            echo json_encode( array( 'getExperiences' => false ) );
            exit();
        }
        die();
    }
}

add_action( 'wp_ajax_nopriv_homey_sticky_map_exp', 'homey_sticky_map_exp' );
add_action( 'wp_ajax_homey_sticky_map_exp', 'homey_sticky_map_exp' );
if( !function_exists('homey_sticky_map_exp') ) {
    function homey_sticky_map_exp() {
        $local = homey_get_localization();
        $tax_query = array();
        $meta_query = array();
        $experiences = array();

        $cgl_types = homey_option('experience_cgl_types');
        $price_separator = homey_option('currency_separator');

        check_ajax_referer('homey_map_ajax_nonce', 'security');

        $prefix = 'homey_';
        $query_args = array(
            'post_type' => 'experience',
            'posts_per_page' => homey_option('sticky_map_num_posts'),
            'post_status' => 'publish'
        );

        $tax_count = count($tax_query);

        $tax_query['relation'] = 'AND';

        if ($tax_count > 0) {
            $query_args['tax_query'] = $tax_query;
        }

        $paged = sanitize_text_field($_POST['paged']);
        if (!empty($paged)) {
            $query_args['paged'] = $paged;
        } else {
            $query_args['paged'] = 1;
        }

        $query_args = new WP_Query( $query_args );

        while( $query_args->have_posts() ): $query_args->the_post();

            $experience_id = get_the_ID();
            $address        = get_post_meta( get_the_ID(), $prefix.'experience_address', true );
            $guests         = get_post_meta( get_the_ID(), $prefix.'guests', true );
            $night_price    = get_post_meta( get_the_ID(), $prefix.'night_price', true );
            $location = get_post_meta( get_the_ID(), $prefix.'experience_location',true);
            $lat_long = explode(',', $location);

            $experience_type = wp_get_post_terms( get_the_ID(), 'experience_type', array("fields" => "ids") );

            $experience_price = homey_exp_get_price_by_id($experience_id);

            $lat = $long = '';
            if(!empty($lat_long[0])) {
                $lat = $lat_long[0];
            }

            if(!empty($lat_long[1])) {
                $long = $lat_long[1];
            }

            $experience = new stdClass();

            $experience->id = $experience_id;
            $experience->title = get_the_title();
            $experience->lat = $lat;
            $experience->long = $long;
            $experience->price = homey_formatted_price($experience_price, false, true).'<sub>'.homey_exp_get_price_label_by_id($experience_id).'</sub>';
            $experience->address = $address;
            $experience->guests = $guests;

            if($cgl_types != 1) {
                $experience->experience_type = '';
            } else {
                $experience->experience_type = homey_taxonomy_simple('experience_type');
            }

            $experience->thumbnail = get_the_post_thumbnail( $experience_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
            $experience->url = get_permalink();

            $experience->icon = get_template_directory_uri() . '/images/custom-marker.png';

            $experience->retinaIcon = get_template_directory_uri() . '/images/custom-marker.png';

            if(!empty($experience_type)) {
                foreach( $experience_type as $term_id ) {

                    $experience->term_id = $term_id;

                    $icon_id = get_term_meta($term_id, 'homey_exp_marker_icon', true);
                    $retinaIcon_id = get_term_meta($term_id, 'homey_exp_marker_retina_icon', true);

                    $icon = wp_get_attachment_image_src( $icon_id, 'full' );
                    $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

                    if( !empty($icon['0']) ) {
                        $experience->icon = $icon['0'];
                    }
                    if( !empty($retinaIcon['0']) ) {
                        $experience->retinaIcon = $retinaIcon['0'];
                    }
                }
            }

            array_push($experiences, $experience);

        endwhile;

        wp_reset_postdata();

        if( count($experiences) > 0 ) {
            echo json_encode( array( 'getExperiences' => true, 'experiences' => $experiences ) );
            exit();
        } else {
            echo json_encode( array( 'getExperiences' => false ) );
            exit();
        }
        die();
    }
}

if( !function_exists('experience_submission_filter')) {
    function experience_submission_filter($new_experience) {
        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;
        $user_email   =   $current_user->user_email;
        $admin_email  =  get_bloginfo('admin_email');
        $totalGuestsPlusAddtionalGuests = 1;

        $experiences_admin_approved = homey_option('experiences_admin_approved');
        $edit_experiences_admin_approved = homey_option('edit_experiences_admin_approved');

        // Title
        if( isset( $_POST['experience_title']) ) {
            $new_experience['post_title'] = sanitize_text_field( $_POST['experience_title'] );
        }

        // Description
        if( isset( $_POST['experience_description'] ) ) {
            $new_experience['post_content'] = wp_kses_post( $_POST['experience_description'] );
        }


        if(isset($_POST['post_author_id']) && !empty($_POST['post_author_id']) ) {
            $new_experience['post_author'] = intval($_POST['post_author_id']);
        } else {
            $new_experience['post_author'] = $userID;
        }

        $submission_action = sanitize_text_field($_POST['action']);
        $experience_id = 0;

        $draft_experience_id = isset($_POST['draft_experience_id']) ? $_POST['draft_experience_id'] : '';
        $draft_experience_id = intval($draft_experience_id);

        if(!empty($draft_experience_id)) {
            $submission_action = 'update_experience';
        }

        $first_owner_userID = 0;
        if( $submission_action == 'homey_add_experience' || isset($_GET['duplication']) ) {
            $first_owner_userID = $current_user->ID;

            if( ($experiences_admin_approved != 0 && !homey_is_admin())) {
                $new_experience['post_status'] = 'pending';
            } else {
                $new_experience['post_status'] = 'publish';
            }

            /*
             * Filter submission arguments before insert into database.
             */
            $new_experience = apply_filters( 'homey_before_submit_experience', $new_experience );

            do_action( 'homey_before_experience_submit', $new_experience);

            $experience_id = wp_insert_post( $new_experience );

            //mandatory post metas that should not be null
            update_post_meta( $experience_id, 'homey_featured', 0 );

            if(isset($_GET['dup_id'])){
                //duplication of custom pricing
                homey_exp_addCustomPeriodDuplicated($experience_id, $_GET['dup_id']);

                // copy images when duplication script is there
                homey_exp_copy_images_for_duplicated_post($experience_id);
            }

        } else if( $submission_action == 'update_experience' ) {
            if(!empty($draft_experience_id)) {
                $new_experience['ID'] = $draft_experience_id;
            } else {
                $new_experience['ID'] = intval( $_POST['experience_id'] );
            }

            $check_is_approved = get_post_meta( $new_experience['ID'], 'homey_firsttime_is_admin_approved', true );
//            that is removed because was not standard but one clients request.
//             $check_is_approved = 1;

//            if(($check_is_approved == 0 || $edit_experiences_admin_approved != 0) && !homey_is_admin()) {
             if(($edit_experiences_admin_approved != 0) && !homey_is_admin()) {
                $new_experience['post_status'] = 'pending';
             } else {
                 $new_experience['post_status'] = get_post_status($new_experience['ID']);
                 if($experiences_admin_approved != 0 && $new_experience['post_status'] != 'publish' && !homey_is_admin()) {// to check
                     $new_experience['post_status'] = 'pending';
                 }else{
                     $new_experience['post_status'] = 'publish';
                     // is in need to be first time approved from admin?
                     update_post_meta( $new_experience['ID'], 'homey_firsttime_is_admin_approved', 1 );
                 }
             }

            /*
             * Filter submission arguments before update into database.
             */
            $new_experience = apply_filters( 'homey_before_update_experience', $new_experience );

            do_action( 'homey_before_experience_update');

            $experience_id = wp_update_post( $new_experience );

        }

        if( $experience_id > 0 ) {

            $prefix = 'homey_';

            //Custom Fields
            if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;
                        $field_type = $value->type;

                        if( isset( $_POST[$field_name] ) ) {
                            if($field_type=='textarea') {
                                update_post_meta( $experience_id, 'homey_'.$field_name, $_POST[$field_name] );
                            } else {
                                update_post_meta( $experience_id, 'homey_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                            }

                        }

                    endforeach; endif;
            }

            $experience_total_rating = get_post_meta( $experience_id, 'experience_total_rating', true );
            if( $experience_total_rating === '') {
                update_post_meta($experience_id, 'experience_total_rating', '0');
            }

            // experience_describe_yourself
            if( isset( $_POST['experience_describe_yourself'] ) ) {
//                update_post_meta( $experience_id, $prefix.'experience_describe_yourself', sanitize_text_field( $_POST['experience_describe_yourself'] ) );
                update_post_meta( $experience_id, $prefix.'experience_describe_yourself', $_POST['experience_describe_yourself'] );
            }

            // start_end_open
            if( isset( $_POST['start_end_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'start_end_open', sanitize_text_field( $_POST['start_end_open'] ) );
                update_post_meta( $experience_id, $prefix.'start_end_open_24hrs', date("Hi", strtotime(sanitize_text_field( $_POST['start_end_open'] ))) );
            }

            // start_end_close
            if( isset( $_POST['start_end_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'start_end_close', sanitize_text_field( $_POST['start_end_close'] ) );
                update_post_meta( $experience_id, $prefix.'start_end_close_24hrs', date("Hi", strtotime(sanitize_text_field( $_POST['start_end_close'] ))) );
            }


            // First owner field
            if( $first_owner_userID > 0 ) {
                update_post_meta( $experience_id, $prefix.'first_owner_user_id', sanitize_text_field($first_owner_userID) );
            }

            // Booking type
            if( isset( $_POST['booking_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'booking_type', sanitize_text_field( $_POST['booking_type'] ) );
            }

            // Instance
            if( isset( $_POST['instant_booking'] ) ) {
                $instance_bk = $_POST['instant_booking'];
                if($instance_bk == 'on') {
                    $instance_bk = 1;
                }
                update_post_meta( $experience_id, $prefix.'instant_booking', sanitize_text_field( $instance_bk ) );
            } else {
                update_post_meta( $experience_id, $prefix.'instant_booking', 0 );
            }

            // Guests
            if( isset( $_POST['guests'] ) ) {
                update_post_meta( $experience_id, $prefix.'guests', sanitize_text_field( $_POST['guests'] ) );

                $totalGuestsPlusAddtionalGuests =  $_POST['guests'];
            }

            // affiliate_booking_link
            if( isset( $_POST['experience_affiliate_booking_link'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_affiliate_booking_link', sanitize_text_field( $_POST['experience_affiliate_booking_link'] ) );
            }

            // Day Date Price
            if( isset( $_POST['day_date_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'day_date_price', sanitize_text_field( $_POST['day_date_price'] ) );
            }

            // Day Date Weekend Price
            if( isset( $_POST['day_date_weekends_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'day_date_weekends_price', sanitize_text_field( $_POST['day_date_weekends_price'] ) );
            }

            // Night Price
            if( isset( $_POST['night_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'night_price', sanitize_text_field( $_POST['night_price'] ) );
            }

            // Weekend Price
            if( isset( $_POST['weekends_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'weekends_price', sanitize_text_field( $_POST['weekends_price'] ) );
            }

            // Hourly Price
            if( isset( $_POST['hour_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'hour_price', sanitize_text_field( $_POST['hour_price'] ) );
            }

            // After Price label
            if( isset( $_POST['price_postfix'] ) ) {
                update_post_meta( $experience_id, $prefix.'price_postfix', sanitize_text_field( $_POST['price_postfix'] ) );
            }

            // Hourly Weekend Price
            if( isset( $_POST['hourly_weekends_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'hourly_weekends_price', sanitize_text_field( $_POST['hourly_weekends_price'] ) );
            }

            // Min book Hours
            if( isset( $_POST['min_book_hours'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_hours', sanitize_text_field( $_POST['min_book_hours'] ) );
            }

            // Start Hours
            if( isset( $_POST['start_hour'] ) ) {
                update_post_meta( $experience_id, $prefix.'start_hour', sanitize_text_field( $_POST['start_hour'] ) );
            }

            // End Hours
            if( isset( $_POST['end_hour'] ) ) {
                update_post_meta( $experience_id, $prefix.'end_hour', sanitize_text_field( $_POST['end_hour'] ) );
            }

            if( isset( $_POST['weekends_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'weekends_days', sanitize_text_field( $_POST['weekends_days'] ) );
            }

            // Week( 7 Nights ) Price
            if( isset( $_POST['priceWeek'] ) ) {
                update_post_meta( $experience_id, $prefix.'priceWeek', sanitize_text_field( $_POST['priceWeek'] ) );
            }

            // Monthly ( 30 Nights ) Price
            if( isset( $_POST['priceMonthly'] ) ) {
                update_post_meta( $experience_id, $prefix.'priceMonthly', sanitize_text_field( $_POST['priceMonthly'] ) );
            }

            // Additional Guests price
            if( isset( $_POST['additional_guests_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'additional_guests_price', sanitize_text_field( $_POST['additional_guests_price'] ) );
            }

            // Additional Guests allowed
            if( isset( $_POST['num_additional_guests'] ) ) {
                update_post_meta( $experience_id, $prefix.'num_additional_guests', sanitize_text_field( $_POST['num_additional_guests'] ) );

                //If additional guests set lets add them to the total count
                $totalGuestsPlusAddtionalGuests += (int) $_POST['num_additional_guests'];
            }

            //Now update the meta data with the total guest count
            update_post_meta( $experience_id, $prefix.'total_guests_plus_additional_guests', $totalGuestsPlusAddtionalGuests );

            // Security Deposit
            if( isset( $_POST['allow_additional_guests'] ) ) {
                update_post_meta( $experience_id, $prefix.'allow_additional_guests', sanitize_text_field( $_POST['allow_additional_guests'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee'] ) ) {
                update_post_meta( $experience_id, $prefix.'cleaning_fee', sanitize_text_field( $_POST['cleaning_fee'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'cleaning_fee_type', sanitize_text_field( $_POST['cleaning_fee_type'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee'] ) ) {
                update_post_meta( $experience_id, $prefix.'city_fee', sanitize_text_field( $_POST['city_fee'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'city_fee_type', sanitize_text_field( $_POST['city_fee_type'] ) );
            }

            // securityDeposit
            if( isset( $_POST['security_deposit'] ) ) {
                update_post_meta( $experience_id, $prefix.'security_deposit', sanitize_text_field( $_POST['security_deposit'] ) );
            }

            // securityDeposit
            if( isset( $_POST['tax_rate'] ) ) {
                update_post_meta( $experience_id, $prefix.'tax_rate', sanitize_text_field( $_POST['tax_rate'] ) );
            }

            // Experience size
            if( isset( $_POST['experience_size'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_size', sanitize_text_field( $_POST['experience_size'] ) );
            }

            // Experience size
            if( isset( $_POST['experience_size_unit'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_size_unit', sanitize_text_field( $_POST['experience_size_unit'] ) );
            }

            $full_address_text = '';
            // Address
            if( isset( $_POST['experience_address'] ) ) {
                $full_address_text .= sanitize_text_field( $_POST['experience_address'] );

                update_post_meta( $experience_id, $prefix.'experience_address', sanitize_text_field( $_POST['experience_address'] ) );
            }

            //AptSuit
            if( isset( $_POST['aptSuit'] ) ) {
                $full_address_text .= !empty($full_address_text) ? ', '.sanitize_text_field( $_POST['aptSuit'] ) : sanitize_text_field( $_POST['aptSuit'] );

                update_post_meta( $experience_id, $prefix.'aptSuit', sanitize_text_field( $_POST['aptSuit'] ) );
            }


            // Cancellation Policy
            if( isset( $_POST['cancellation_policy'] ) ) {
                update_post_meta( $experience_id, $prefix.'cancellation_policy', sanitize_textarea_field( $_POST['cancellation_policy'] ) );
            }

            // Minimum Stay
            if( isset( $_POST['min_book_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_days', sanitize_text_field( $_POST['min_book_days'] ) );
            }

            if( isset( $_POST['min_book_weeks'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_weeks', sanitize_text_field( $_POST['min_book_weeks'] ) );
            }

            if( isset( $_POST['min_book_months'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_months', sanitize_text_field( $_POST['min_book_months'] ) );
            }

            // Maximum Stay
            if( isset( $_POST['max_book_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'max_book_days', sanitize_text_field( $_POST['max_book_days'] ) );
            }
            if( isset( $_POST['max_book_weeks'] ) ) {
                update_post_meta( $experience_id, $prefix.'max_book_weeks', sanitize_text_field( $_POST['max_book_weeks'] ) );
            }
            if( isset( $_POST['max_book_months'] ) ) {
                update_post_meta( $experience_id, $prefix.'max_book_months', sanitize_text_field( $_POST['max_book_months'] ) );
            }

            // Check in After
            if( isset( $_POST['checkin_after'] ) ) {
                update_post_meta( $experience_id, $prefix.'checkin_after', sanitize_text_field( $_POST['checkin_after'] ) );
            }

            // Check Out After
            if( isset( $_POST['checkout_before'] ) ) {
                update_post_meta( $experience_id, $prefix.'checkout_before', sanitize_text_field( $_POST['checkout_before'] ) );
            }

            // Allow Smoke
            if( isset( $_POST['smoke'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_smoke', sanitize_text_field( $_POST['smoke'] ) );
            }

            // Allow Pets
            if( isset( $_POST['pets'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_pets', sanitize_text_field( $_POST['pets'] ) );
            }

            // Allow Party
            if( isset( $_POST['party'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_party', sanitize_text_field( $_POST['party'] ) );
            }

            // Allow Childred
            if( isset( $_POST['children'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_children', sanitize_text_field( $_POST['children'] ) );
            }

            // Additional Rules
            if( isset( $_POST['additional_rules'] ) ) {
                update_post_meta( $experience_id, $prefix.'additional_rules',  $_POST['additional_rules'] );
            }

            if( isset( $_POST['homey_accomodation'] ) ) {
                $homey_accomodation = $_POST['homey_accomodation'];
                if( ! empty( $homey_accomodation ) ) {
                    update_post_meta( $experience_id, $prefix.'accomodation', $homey_accomodation );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'accomodation', '' );
            }

            // Nothing provided btn for yes or no
            if( isset( $_POST['nothing_provided_btn'] ) ) {
                $nothing_provided_btn = $_POST['nothing_provided_btn'];
                if($nothing_provided_btn == 'on') {
                    $nothing_provided_btn = 1;
                }
                update_post_meta( $experience_id, $prefix.'nothing_provided_btn', sanitize_text_field( $nothing_provided_btn ) );
            } else {
                update_post_meta( $experience_id, $prefix.'nothing_provided_btn', 0 );
            }

            // Nothing bring btn for yes or no
            if( isset( $_POST['nothing_bring_btn'] ) ) {
                $nothing_bring_btn = $_POST['nothing_bring_btn'];
                if($nothing_bring_btn == 'on') {
                    $nothing_bring_btn = 1;
                }
                update_post_meta( $experience_id, $prefix.'nothing_bring_btn', sanitize_text_field( $nothing_bring_btn ) );
            } else {
                update_post_meta( $experience_id, $prefix.'nothing_bring_btn', 0 );
            }

            if( isset( $_POST['what_to_provided'] ) ) {

                $what_to_provided = $_POST['what_to_provided'];
                $what_to_provided = array_filter($what_to_provided, function($v){
                    return !empty($v['wwbp_name']);
                });

                if( !empty( $_POST['what_to_provided'][1]['wwbp_name'] ) ) {
                    update_post_meta( $experience_id, $prefix.'what_to_provided', $what_to_provided );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'what_to_provided', '' );
            }

            if( isset( $_POST['what_to_bring'] ) ) {
                $what_to_bring = $_POST['what_to_bring'];
                $what_to_bring = array_filter($what_to_bring, function($v){
                    return !empty($v['wbit_name']);
                });

                if( !empty( $_POST['what_to_bring'][1]['wbit_name'] ) ) {
                    update_post_meta( $experience_id, $prefix.'what_to_bring', $what_to_bring );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'what_to_bring', '' );
            }

            if( isset( $_POST['homey_services'] ) ) {
                $homey_services = $_POST['homey_services'];
                if( ! empty( $homey_services ) ) {
                    update_post_meta( $experience_id, $prefix.'services', $homey_services );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'services', '' );
            }

            if( isset( $_POST['extra_price'] ) ) {
                $extra_price = $_POST['extra_price'];
                if( ! empty( $extra_price ) ) {
                    update_post_meta( $experience_id, $prefix.'extra_prices', $extra_price );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'extra_prices', '' );
            }

            // Openning Hours
            if( isset( $_POST['mon_fri_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_open', sanitize_text_field( $_POST['mon_fri_open'] ) );
            }
            if( isset( $_POST['mon_fri_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_close', sanitize_text_field( $_POST['mon_fri_close'] ) );
            }
            if( isset( $_POST['mon_fri_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_closed', sanitize_text_field( $_POST['mon_fri_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'mon_fri_closed', 0 );
            }

            if( isset( $_POST['sat_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_open', sanitize_text_field( $_POST['sat_open'] ) );
            }
            if( isset( $_POST['sat_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_close', sanitize_text_field( $_POST['sat_close'] ) );
            }
            if( isset( $_POST['sat_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_closed', sanitize_text_field( $_POST['sat_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'sat_closed', 0 );
            }


            if( isset( $_POST['sun_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_open', sanitize_text_field( $_POST['sun_open'] ) );
            }
            if( isset( $_POST['sun_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_close', sanitize_text_field( $_POST['sun_close'] ) );
            }
            if( isset( $_POST['sun_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_closed', sanitize_text_field( $_POST['sun_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'sun_closed', 0 );
            }


            // Postal Code
            if( isset( $_POST['zip'] ) ) {
                $full_address_text .= !empty($_POST['aptSuit']) ? ', '.sanitize_text_field( $_POST['zip'] ) : sanitize_text_field( $_POST['zip'] );

                update_post_meta( $experience_id, $prefix.'zip', sanitize_text_field( $_POST['zip'] ) );
            }

            // Country
            if( isset( $_POST['country'] ) ) {
                $full_address_text .= !empty($_POST['zip'] ) ? ', '.sanitize_text_field( $_POST['country'] ) : sanitize_text_field( $_POST['country'] );

                $experience_country = sanitize_text_field( $_POST['country'] );
                $country_id = wp_set_object_terms( $experience_id, $experience_country, 'experience_country' );
            }

            // State
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $full_address_text .= !empty($_POST['country']) ? ', '.sanitize_text_field( $_POST['administrative_area_level_1'] ) : sanitize_text_field( $_POST['administrative_area_level_1'] );

                $experience_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $experience_id, $experience_state, 'experience_state' );

                $homey_meta = array();
                $homey_meta['parent_country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
                if( !empty( $state_id) ) {
                    update_option('_homey_experience_state_' . $state_id[0], $homey_meta);
                }
            }

            // City
            if( isset( $_POST['locality'] ) ) {
                $full_address_text .= !empty($_POST['administrative_area_level_1']) ? ', '.sanitize_text_field( $_POST['locality'] ) : sanitize_text_field( $_POST['locality'] );

                $experience_city = sanitize_text_field( $_POST['locality'] );
                $city_id = wp_set_object_terms( $experience_id, $experience_city, 'experience_city' );

                $homey_meta = array();
                $homey_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                if( !empty( $city_id) ) {
                    update_option('_homey_experience_city_' . $city_id[0], $homey_meta);
                }
            }

            // Area
            if( isset( $_POST['neighborhood'] ) ) {
                $full_address_text .= !empty($_POST['locality']) ? ', '.sanitize_text_field( $_POST['neighborhood'] ) : sanitize_text_field( $_POST['neighborhood'] );

                $experience_area = sanitize_text_field( $_POST['neighborhood'] );
                $area_id = wp_set_object_terms( $experience_id, $experience_area, 'experience_area' );

                $homey_meta = array();
                $homey_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                if( !empty( $area_id) ) {
                    update_option('_homey_experience_area_' . $area_id[0], $homey_meta);
                }
            }

            // Make featured
            if( isset( $_POST['experience_featured'] ) ) {
                $featured = intval( $_POST['experience_featured'] );
                update_post_meta( $experience_id, 'homey_featured', $featured );
            }


            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $experience_id, $prefix.'geolocation_lat', $lat );
                update_post_meta( $experience_id, $prefix.'geolocation_long', $lng );
                update_post_meta( $experience_id, $prefix.'experience_location', $lat_lng );
                update_post_meta( $experience_id, $prefix.'experience_map', '1' );
                update_post_meta( $experience_id, $prefix.'show_map', 1 );


                if( $submission_action == 'homey_add_experience' ) {
                    homey_insert_lat_long($lat, $lng, $experience_id);
                } elseif ( $submission_action == 'update_experience' ) {
                    homey_update_lat_long($lat, $lng, $experience_id);
                }
            }

            // Room Type
            wp_set_object_terms( $experience_id, intval( 0 ), 'room_type' );
            if( isset( $_POST['room_type'] ) && ( $_POST['room_type'] != '-1' ) ) {
                wp_set_object_terms( $experience_id, intval( $_POST['room_type'] ), 'room_type' );
            }

            // Experience Type
            wp_set_object_terms( $experience_id, intval( 0 ), 'experience_type' );
            if( isset( $_POST['experience_type'] ) && ( $_POST['experience_type'] != '-1' ) ) {
                wp_set_object_terms( $experience_id, intval( $_POST['experience_type'] ), 'experience_type' );
            }

            // Experience Language
            if( isset( $_POST['experience_language'] ) && ( $_POST['experience_language'] != '-1' ) ) {
                if (is_array($_POST['experience_language'])) {

                    if ($_POST['experience_language'][0] == -1) {// remove none
                        unset($_POST['experience_language'][0]);
                    }

                    $lang_ids = array_map('intval', $_POST['experience_language']);
                    $lang_ids = array_unique($lang_ids);

                    wp_set_object_terms($experience_id, $lang_ids, 'experience_language');

                }
            }

            // Amenities
            if( isset( $_POST['experience_amenity'] ) ) {
                $amenities_array = array();
                foreach( $_POST['experience_amenity'] as $amenity_id ) {
                    $amenities_array[] = intval( $amenity_id );
                }
                wp_set_object_terms( $experience_id, $amenities_array, 'experience_amenity' );
            }

            // Facilities
            if( isset( $_POST['experience_facility'] ) ) {
                $facilities_array = array();
                foreach( $_POST['experience_facility'] as $facility_id ) {
                    $facilities_array[] = intval( $facility_id );
                }
                wp_set_object_terms( $experience_id, $facilities_array, 'experience_facility' );
            }


            // clean up the old meta information related to images when experience update
            if( $submission_action == "update_experience" ){
                delete_post_meta( $experience_id, 'homey_experience_images' );
                delete_post_meta( $experience_id, '_thumbnail_id' );
            }

            if( isset( $_POST['experience_video_url'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_video_url', sanitize_text_field( $_POST['experience_video_url'] ) );
            }

            // Experience Images
            if( isset( $_POST['experience_image_ids'] ) ) {
                if (!empty($_POST['experience_image_ids']) && is_array($_POST['experience_image_ids'])) {
                    $experience_image_ids = array();
                    foreach ($_POST['experience_image_ids'] as $img_id ) {
                        $experience_image_ids[] = intval( $img_id );
                        add_post_meta($experience_id, 'homey_experience_images', $img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $experience_image_ids ) ) {
                            update_post_meta( $experience_id, '_thumbnail_id', $featured_image_id );
                        }
                    } elseif ( ! empty ( $experience_image_ids ) ) {
                        update_post_meta( $experience_id, '_thumbnail_id', $experience_image_ids[0] );
                    }
                }
            }

            apply_filters('experience_submission_filter_filter', $experience_id);

            if( $submission_action == 'homey_add_experience' ) {
                $post_status_text_user = esc_html__("Your experience status is published", 'homey');
                $post_status_text_admin = esc_html__("This experience status is published", 'homey');

                if( ($experiences_admin_approved != 0 && !homey_is_admin())) {
                    $post_status_text_user = esc_html__("Your experience status is pending for admin approval", 'homey');
                    $post_status_text_admin = esc_html__("This experience status is in need to be approved from you.", 'homey');
                }

                $args = array(
                    'experience_title'  =>  get_the_title($experience_id),
                    'experience_id'     =>  $experience_id,
                    'post_status_user' =>  $post_status_text_user,
                    'post_status_admin' =>  $post_status_text_admin,
                );
                /*
                 * Send email
                 * */
                if( ($experiences_admin_approved != 0 && !homey_is_admin())) {
                    homey_email_composer( $user_email, 'new_submission_experience', $args );
                }

                homey_email_composer( $admin_email, 'admin_new_submission_experience', $args );

                do_action( 'homey_after_experience_submit', $experience_id );

            } else if ( $submission_action == 'update_experience' ) {

                $post_status_text_user = esc_html__("Your experience status is published", 'homey');
                $post_status_text_admin = esc_html__("This experience status is published", 'homey');

                if($edit_experiences_admin_approved != 0 && !homey_is_admin()) {
                    if($experiences_admin_approved != 0 && $new_experience['post_status'] == 'pending' && !homey_is_admin()) {
                        $post_status_text_user = esc_html__("Your experience status is pending for admin approval", 'homey');
                        $post_status_text_admin = esc_html__("This experience status is in need to be approved from you.", 'homey');
                    }
                }

                $args = array(
                    'experience_title'  =>  get_the_title($experience_id),
                    'experience_id'     =>  $experience_id,
                    'post_status_user' =>  $post_status_text_user,
                    'post_status_admin' =>  $post_status_text_admin,
                );
                /*
                 * Send email
                 * */
                if($edit_experiences_admin_approved != 0 && !homey_is_admin()) {
                    homey_email_composer( $user_email, 'update_submission_experience', $args );
                }

                homey_email_composer( $admin_email, 'admin_update_submission_experience', $args );


                do_action( 'houmey_after_experience_update', $experience_id );
            }

            // full Address
            update_post_meta( $experience_id, $prefix.'experience_full_address', $full_address_text );


            return $experience_id;
        }

    } //experience_submission_filter

    add_filter('experience_submission_filter', 'experience_submission_filter');
}

/*-----------------------------------------------------------------------------------*/
// validate Email
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_save_as_draft_exp', 'save_experience_as_draft');
if( !function_exists('save_experience_as_draft') ) {
    function save_experience_as_draft() {

        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;

        $new_experience = array(
            'post_type' => 'experience'
        );

        $submission_action = $_POST['action'];
        $experiences_admin_approved = homey_option('experiences_admin_approved');
        $edit_experiences_admin_approved = homey_option('edit_experiences_admin_approved');

        // Title
        if( isset( $_POST['experience_title']) ) {
            $new_experience['post_title'] = sanitize_text_field( $_POST['experience_title'] );
        }

        // Description
        if( isset( $_POST['experience_description'] ) ) {
            $new_experience['post_content'] = wp_kses_post( $_POST['experience_description'] );
        }

        $new_experience['post_author'] = $userID;

        $experience_id = 0;
        $new_experience['post_status'] = 'draft';

        if( isset($_POST['draft_experience_id']) && !empty( $_POST['draft_experience_id'] ) ) {
            $new_experience['ID'] = $_POST['draft_experience_id'];
            $experience_id = wp_update_post( $new_experience );
        } else {
            $experience_id = wp_insert_post( $new_experience );
        }

        if( $experience_id > 0 ) {

            $prefix = 'homey_';

            //Custom Fields
            if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;
                        $field_type = $value->type;

                        if( isset( $_POST[$field_name] ) ) {
                            if($field_type=='textarea') {
                                update_post_meta( $experience_id, 'homey_'.$field_name, $_POST[$field_name] );
                            } else {
                                update_post_meta( $experience_id, 'homey_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                            }

                        }

                    endforeach; endif;
            }

            $experience_total_rating = get_post_meta( $experience_id, 'experience_total_rating', true );
            if( $experience_total_rating === '') {
                update_post_meta($experience_id, 'experience_total_rating', '0');
            }

            // About Description
            if( isset( $_POST['describe_yourself'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_describe_yourself', sanitize_text_field( $_POST['describe_yourself'] ) );
            }

            // Booking type
            if( isset( $_POST['booking_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'booking_type', sanitize_text_field( $_POST['booking_type'] ) );
            }

            // Instance
            if( isset( $_POST['instant_booking'] ) ) {
                $instance_bk = $_POST['instant_booking'];
                if($instance_bk == 'on') {
                    $instance_bk = 1;
                }
                update_post_meta( $experience_id, $prefix.'instant_booking', sanitize_text_field( $instance_bk ) );
            } else {
                update_post_meta( $experience_id, $prefix.'instant_booking', 0 );
            }

            // Guests
            if( isset( $_POST['guests'] ) ) {
                update_post_meta( $experience_id, $prefix.'guests', sanitize_text_field( $_POST['guests'] ) );
            }

            // Rooms
            if( isset( $_POST['experience_rooms'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_rooms', sanitize_text_field( $_POST['experience_rooms'] ) );
            }

            // Night Price
            if( isset( $_POST['night_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'night_price', sanitize_text_field( $_POST['night_price'] ) );
            }

            // Weekend Price
            if( isset( $_POST['weekends_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'weekends_price', sanitize_text_field( $_POST['weekends_price'] ) );
            }

            // Hourly Price
            if( isset( $_POST['hour_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'hour_price', sanitize_text_field( $_POST['hour_price'] ) );
            }

            // After Price label
            if( isset( $_POST['price_postfix'] ) ) {
                update_post_meta( $experience_id, $prefix.'price_postfix', sanitize_text_field( $_POST['price_postfix'] ) );
            }

            // Hourly Weekend Price
            if( isset( $_POST['hourly_weekends_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'hourly_weekends_price', sanitize_text_field( $_POST['hourly_weekends_price'] ) );
            }

            // Min book Hours
            if( isset( $_POST['min_book_hours'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_hours', sanitize_text_field( $_POST['min_book_hours'] ) );
            }

            // Start Hours
            if( isset( $_POST['start_hour'] ) ) {
                update_post_meta( $experience_id, $prefix.'start_hour', sanitize_text_field( $_POST['start_hour'] ) );
            }

            // End Hours
            if( isset( $_POST['end_hour'] ) ) {
                update_post_meta( $experience_id, $prefix.'end_hour', sanitize_text_field( $_POST['end_hour'] ) );
            }

            if( isset( $_POST['weekends_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'weekends_days', sanitize_text_field( $_POST['weekends_days'] ) );
            }

            // Week( 7 Nights ) Price
            if( isset( $_POST['priceWeek'] ) ) {
                update_post_meta( $experience_id, $prefix.'priceWeek', sanitize_text_field( $_POST['priceWeek'] ) );
            }

            // Monthly ( 30 Nights ) Price
            if( isset( $_POST['priceMonthly'] ) ) {
                update_post_meta( $experience_id, $prefix.'priceMonthly', sanitize_text_field( $_POST['priceMonthly'] ) );
            }

            // Additional Guests price
            if( isset( $_POST['additional_guests_price'] ) ) {
                update_post_meta( $experience_id, $prefix.'additional_guests_price', sanitize_text_field( $_POST['additional_guests_price'] ) );
            }

            // Security Deposit
            if( isset( $_POST['allow_additional_guests'] ) ) {
                update_post_meta( $experience_id, $prefix.'allow_additional_guests', sanitize_text_field( $_POST['allow_additional_guests'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee'] ) ) {
                update_post_meta( $experience_id, $prefix.'cleaning_fee', sanitize_text_field( $_POST['cleaning_fee'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'cleaning_fee_type', sanitize_text_field( $_POST['cleaning_fee_type'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee'] ) ) {
                update_post_meta( $experience_id, $prefix.'city_fee', sanitize_text_field( $_POST['city_fee'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee_type'] ) ) {
                update_post_meta( $experience_id, $prefix.'city_fee_type', sanitize_text_field( $_POST['city_fee_type'] ) );
            }

            // securityDeposit
            if( isset( $_POST['security_deposit'] ) ) {
                update_post_meta( $experience_id, $prefix.'security_deposit', sanitize_text_field( $_POST['security_deposit'] ) );
            }

            // securityDeposit
            if( isset( $_POST['tax_rate'] ) ) {
                update_post_meta( $experience_id, $prefix.'tax_rate', sanitize_text_field( $_POST['tax_rate'] ) );
            }

            // Experience size
            if( isset( $_POST['experience_size'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_size', sanitize_text_field( $_POST['experience_size'] ) );
            }

            // Experience size
            if( isset( $_POST['experience_size_unit'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_size_unit', sanitize_text_field( $_POST['experience_size_unit'] ) );
            }

            // Address
            if( isset( $_POST['experience_address'] ) ) {
                update_post_meta( $experience_id, $prefix.'experience_address', sanitize_text_field( $_POST['experience_address'] ) );
            }

            //AptSuit
            if( isset( $_POST['aptSuit'] ) ) {
                update_post_meta( $experience_id, $prefix.'aptSuit', sanitize_text_field( $_POST['aptSuit'] ) );
            }


            // Cancellation Policy
            if( isset( $_POST['cancellation_policy'] ) ) {
                update_post_meta( $experience_id, $prefix.'cancellation_policy', sanitize_text_field( $_POST['cancellation_policy'] ) );
            }

            // Minimum Stay
            if( isset( $_POST['min_book_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'min_book_days', sanitize_text_field( $_POST['min_book_days'] ) );
            }

            // Maximum Stay
            if( isset( $_POST['max_book_days'] ) ) {
                update_post_meta( $experience_id, $prefix.'max_book_days', sanitize_text_field( $_POST['max_book_days'] ) );
            }

            // Check in After
            if( isset( $_POST['checkin_after'] ) ) {
                update_post_meta( $experience_id, $prefix.'checkin_after', sanitize_text_field( $_POST['checkin_after'] ) );
            }

            // Check Out After
            if( isset( $_POST['checkout_before'] ) ) {
                update_post_meta( $experience_id, $prefix.'checkout_before', sanitize_text_field( $_POST['checkout_before'] ) );
            }

            // Allow Smoke
            if( isset( $_POST['smoke'] ) ) {
                update_post_meta( $experience_id, $prefix.'smoke', sanitize_text_field( $_POST['smoke'] ) );
            }

            // Allow Pets
            if( isset( $_POST['pets'] ) ) {
                update_post_meta( $experience_id, $prefix.'pets', sanitize_text_field( $_POST['pets'] ) );
            }

            // Allow Party
            if( isset( $_POST['party'] ) ) {
                update_post_meta( $experience_id, $prefix.'party', sanitize_text_field( $_POST['party'] ) );
            }

            // Allow Childred
            if( isset( $_POST['children'] ) ) {
                update_post_meta( $experience_id, $prefix.'children', sanitize_text_field( $_POST['children'] ) );
            }

            // Additional Rules
            if( isset( $_POST['additional_rules'] ) ) {
                update_post_meta( $experience_id, $prefix.'additional_rules', $_POST['additional_rules'] );
            }

            if( isset( $_POST['homey_accomodation'] ) ) {
                $homey_accomodation = $_POST['homey_accomodation'];
                if( ! empty( $homey_accomodation ) ) {
                    update_post_meta( $experience_id, $prefix.'accomodation', $homey_accomodation );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'accomodation', '' );
            }

            if( isset( $_POST['homey_services'] ) ) {
                $homey_services = $_POST['homey_services'];
                if( ! empty( $homey_services ) ) {
                    update_post_meta( $experience_id, $prefix.'services', $homey_services );
                }
            } else {
                update_post_meta( $experience_id, $prefix.'services', '' );
            }

            // Openning Hours
            if( isset( $_POST['mon_fri_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_open', sanitize_text_field( $_POST['mon_fri_open'] ) );
            }
            if( isset( $_POST['mon_fri_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_close', sanitize_text_field( $_POST['mon_fri_close'] ) );
            }
            if( isset( $_POST['mon_fri_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'mon_fri_closed', sanitize_text_field( $_POST['mon_fri_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'mon_fri_closed', 0 );
            }

            if( isset( $_POST['sat_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_open', sanitize_text_field( $_POST['sat_open'] ) );
            }
            if( isset( $_POST['sat_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_close', sanitize_text_field( $_POST['sat_close'] ) );
            }
            if( isset( $_POST['sat_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'sat_closed', sanitize_text_field( $_POST['sat_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'sat_closed', 0 );
            }


            if( isset( $_POST['sun_open'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_open', sanitize_text_field( $_POST['sun_open'] ) );
            }
            if( isset( $_POST['sun_close'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_close', sanitize_text_field( $_POST['sun_close'] ) );
            }
            if( isset( $_POST['sun_closed'] ) ) {
                update_post_meta( $experience_id, $prefix.'sun_closed', sanitize_text_field( $_POST['sun_closed'] ) );
            } else {
                update_post_meta( $experience_id, $prefix.'sun_closed', 0 );
            }


            // Postal Code
            if( isset( $_POST['zip'] ) ) {
                update_post_meta( $experience_id, $prefix.'zip', sanitize_text_field( $_POST['zip'] ) );
            }

            // Country
            if( isset( $_POST['country'] ) ) {
                $experience_country = sanitize_text_field( $_POST['country'] );
                $country_id = wp_set_object_terms( $experience_id, $experience_country, 'experience_country' );
            }

            // State
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $experience_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $experience_id, $experience_state, 'experience_state' );

                $homey_meta = array();
                $homey_meta['parent_country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
                if( !empty( $state_id) ) {
                    update_option('_homey_experience_state_' . $state_id[0], $homey_meta);
                }
            }

            // City
            if( isset( $_POST['locality'] ) ) {
                $experience_city = sanitize_text_field( $_POST['locality'] );
                $city_id = wp_set_object_terms( $experience_id, $experience_city, 'experience_city' );

                $homey_meta = array();
                $homey_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                if( !empty( $city_id) ) {
                    update_option('_homey_experience_city_' . $city_id[0], $homey_meta);
                }
            }

            // Area
            if( isset( $_POST['neighborhood'] ) ) {
                $experience_area = sanitize_text_field( $_POST['neighborhood'] );
                $area_id = wp_set_object_terms( $experience_id, $experience_area, 'experience_area' );

                $homey_meta = array();
                $homey_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                if( !empty( $area_id) ) {
                    update_option('_homey_experience_area_' . $area_id[0], $homey_meta);
                }
            }

            // Make featured
            if( isset( $_POST['experience_featured'] ) ) {
                $featured = intval( $_POST['experience_featured'] );
                update_post_meta( $experience_id, 'homey_featured', $featured );
            }


            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $experience_id, $prefix.'geolocation_lat', $lat );
                update_post_meta( $experience_id, $prefix.'geolocation_long', $lng );
                update_post_meta( $experience_id, $prefix.'experience_location', $lat_lng );
                update_post_meta( $experience_id, $prefix.'experience_map', '1' );


                if( $submission_action == 'homey_add_experience' || ($submission_action == 'save_as_draft_exp' && isset($_POST['draft_experience_id']) && $_POST['draft_experience_id']  < 1 )) {
                    homey_insert_lat_long($lat, $lng, $experience_id);
                } elseif ( $submission_action == 'update_experience' || ($submission_action == 'save_as_draft_exp' && isset($_POST['draft_experience_id']) && $_POST['draft_experience_id']  > 0 )) {
                    homey_update_lat_long($lat, $lng, $experience_id);
                }



            }

            // Room Type
            wp_set_object_terms( $experience_id, intval( 0 ), 'room_type' );
            if( isset( $_POST['room_type'] ) && ( $_POST['room_type'] != '-1' ) ) {
                wp_set_object_terms( $experience_id, intval( $_POST['room_type'] ), 'room_type' );
            }

            // Experience Type
            wp_set_object_terms( $experience_id, intval( 0 ), 'experience_type' );
            if( isset( $_POST['experience_type'] ) && ( $_POST['experience_type'] != '-1' ) ) {
                wp_set_object_terms( $experience_id, intval( $_POST['experience_type'] ), 'experience_type' );
            }

            // Amenities
            if( isset( $_POST['experience_amenity'] ) ) {
                $amenities_array = array();
                foreach( $_POST['experience_amenity'] as $amenity_id ) {
                    $amenities_array[] = intval( $amenity_id );
                }
                wp_set_object_terms( $experience_id, $amenities_array, 'experience_amenity' );
            }

            // Facilities
            if( isset( $_POST['experience_facility'] ) ) {
                $facilities_array = array();
                foreach( $_POST['experience_facility'] as $facility_id ) {
                    $facilities_array[] = intval( $facility_id );
                }
                wp_set_object_terms( $experience_id, $facilities_array, 'experience_facility' );
            }


            // clean up the old meta information related to images when experience update
            if( $submission_action == "update_experience" && !isset($_GET['duplication'])) {
                delete_post_meta( $experience_id, 'homey_experience_images' );
                delete_post_meta( $experience_id, '_thumbnail_id' );
            }

            if( isset( $_POST['video_url'] ) ) {
                update_post_meta( $experience_id, $prefix.'video_url', sanitize_text_field( $_POST['video_url'] ) );
            }

            // Experience Images
            if( isset( $_POST['experience_image_ids'] ) && !isset($_GET['duplication']) ) {
                if (!empty($_POST['experience_image_ids']) && is_array($_POST['experience_image_ids'])) {
                    $experience_image_ids = array();
                    foreach ($_POST['experience_image_ids'] as $img_id ) {
                        $experience_image_ids[] = intval( $img_id );
                        add_post_meta($experience_id, 'homey_experience_images', $img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $experience_image_ids ) ) {
                            update_post_meta( $experience_id, '_thumbnail_id', $featured_image_id );
                        }
                    } elseif ( ! empty ( $experience_image_ids ) ) {
                        update_post_meta( $experience_id, '_thumbnail_id', $experience_image_ids[0] );
                    }
                }
            }
        }

        echo json_encode( array( 'success' => true, 'experience_id' => $experience_id, 'msg' => esc_html__('Successfull', 'homey') ) );
        wp_die();
    }
}

if(!function_exists('homey_exp_insert_lat_long')) {
    function homey_exp_insert_lat_long($lat, $long, $list_id) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $wpdb->insert(
            $table_name,
            array(
                'latitude' => $lat,
                'longitude' => $long,
                'experience_id' => $list_id
            ),
            array(
                '%s',
                '%s',
                '%s'
            )
        );
        return true;
    }
}

if(!function_exists('homey_exp_update_lat_long')) {
    function homey_exp_update_lat_long($lat, $long, $list_id) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $wpdb->update(
            $table_name,
            array(
                'latitude' => $lat,  // string
                'longitude' => $long   // integer (number)
            ),
            array( 'experience_id' => $list_id ),
            array(
                '%s',   // value1
                '%s'    // value2
            ),
            array( '%d' )
        );
        return true;
    }
}

/* --------------------------------------------------------------------------
* Make experience featured for membership
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_homey_membership_featured_experience', 'homey_membership_featured_experience' );
if( ! function_exists( 'homey_membership_featured_experience' ) ) {
    function homey_membership_featured_experience() {
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'featured_experience_nonce' ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Security check failed!', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( !isset( $_REQUEST['experience_id'] ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'No experience ID found', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        $experience_id = $_REQUEST['experience_id'];
        $post_author = get_post_field( 'post_author', $experience_id );

        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;
        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $subscription_info = get_active_membership_plan();
        $membership_package_meta = isset($subscription_info['subscriptionObj']->ID) ?  get_post_meta($subscription_info['subscriptionObj']->ID): array();

        $total_available_featured_experiences = isset($membership_package_meta['hm_settings_featured_experiences'][0]) ? $membership_package_meta['hm_settings_featured_experiences'][0] : 0;

        $total_used_featured_experiences = homey_featured_experience_count($userID);

        if( ! homey_check_membershop_status() ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( "You do not have any package or your package expired.", 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if( $total_available_featured_experiences ==  $total_used_featured_experiences ) {
          $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'You have used all your featured experiences.', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( ($post_author == $userID) || homey_is_admin() ) {

            update_post_meta( $experience_id, 'homey_featured', 1 );
            update_post_meta( $experience_id, 'homey_featured_datetime', $date );

            $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'experience Deleted', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        } else {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Permission denied', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

/* --------------------------------------------------------------------------
* Experience delete ajax
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_homey_delete_experience', 'homey_delete_experience' );
add_action( 'wp_ajax_homey_delete_experience', 'homey_delete_experience' );

if ( !function_exists( 'homey_delete_experience' ) ) {

    function homey_delete_experience()
    {

        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'delete_experience_nonce' ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Security check failed!', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( !isset( $_REQUEST['experience_id'] ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'No experience ID found', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        $experience_id = $_REQUEST['experience_id'];
        $post_author = get_post_field( 'post_author', $experience_id );

        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;

        if ( ($post_author == $userID) || homey_is_admin() ) {
            homey_delete_attachments_for_frontend_experience_delete($experience_id);
            wp_delete_post( $experience_id );
            $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'experience Deleted', 'homey' ) );
            echo json_encode( $ajax_response );
            homey_delete_property_attachments_frontend($experience_id);
            die;
        } else {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Permission denied', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

if(!function_exists('homey_delete_attachments_for_frontend_experience_delete')) {
    function homey_delete_attachments_for_frontend_experience_delete($experience_id) {
        $media = get_children(array(
            'post_parent' => $experience_id,
            'post_type' => 'attachment'
        ));

        if (!empty($media)) {
            foreach ($media as $file) {
                // pick what you want to do
                //unlink(get_attached_file($file->ID));
                wp_delete_attachment($file->ID);
            }
        }
        $attachment_ids = get_post_meta($experience_id, 'homey_experience_images', false);

        if (!empty($attachment_ids)) {
            foreach ($attachment_ids as $id) {
                wp_delete_attachment($id);
            }
        }
    }
}

if(!function_exists('homey_get_experience_featured')) {
    function homey_get_experience_featured($experience_id) {
        $homey_local = homey_get_localization();
        $featured = get_post_meta($experience_id, 'homey_featured', true);
        $html_output = '';

        if($featured == 1) {

            if(is_singular('experience')) {
                $html_output = '<span class="label label-success label-featured">'.$homey_local['featured_label'].'</span>';
            } else {
                $html_output = '<span class="label-wrap top-left">
                    <span class="label label-success label-featured">'.$homey_local['featured_label'].'</span>
                </span>';
            }
        }
        return $html_output;
    }
}

if(!function_exists('homey_experience_featured')) {
    function homey_experience_featured($experience_id) {
        echo homey_get_experience_featured($experience_id);
    }
}


if( !function_exists('homey_experience_sort')) {
    function homey_experience_sort($query_args) {
        $sort_by = '';

        if ( isset( $_GET['sortby'] ) ) {
            $sort_by = $_GET['sortby'];
        } else {

            if ( is_page_template( array( 'template/template-experience-list.php', 'template/template-experience-list-v2.php', 'template/template-experience-grid.php', 'template/template-experience-grid-v2.php', 'template/template-experience-card.php', 'template/template-experience-sticky-map.php', 'template/template-exp-search.php' ))) {
                $sort_by = get_post_meta( get_the_ID(), 'homey_experiences_sort', true );

            } else if( is_page_template( array( 'template/template-exp-search.php' )) ) {

                $sort_by = homey_option('search_default_order_exp');

            } else if ( is_tax() ) {
                $sort_by = homey_option('taxonomy_exp_default_order');
            }

            if(empty($sort_by)){
                $sort_by = homey_option('taxonomy_exp_default_order');
            }
        }

        if ( $sort_by == 'a_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'a_rating' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'experience_total_rating';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_rating' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'experience_total_rating';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $query_args['meta_key'] = 'homey_featured';
            $query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $query_args['orderby'] = 'meta_value date';
            $query_args['meta_key'] = 'homey_featured';
            $query_args['order'] = 'DESC';
        }

        return $query_args;
    }
}

/*-----------------------------------------------------------------------------------*/
/*   Experience gallery images upload
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_experience_gallery_upload', 'homey_experience_gallery_upload' );
add_action( 'wp_ajax_homey_experience_gallery_upload', 'homey_experience_gallery_upload' );    // only for logged in user
if( !function_exists( 'homey_experience_gallery_upload' ) ) {
    function homey_experience_gallery_upload( ) {

        // Check security Nonce
        $verify_nonce = $_REQUEST['verify_experience_gallery_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'verify_experience_gallery_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid nonce!' ) );
            die;
        }

        $submitted_file = $_FILES['experience_upload_file'];
        $is_dimension_valid = homey_experience_image_dimension($submitted_file);
        $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

        if ( isset( $uploaded_image['file'] ) && $is_dimension_valid != -1 ) {
            $file_name          =   basename( $submitted_file['name'] );
            $file_type          =   wp_check_filetype( $uploaded_image['file'] );

            // Prepare an array of post data for the attachment.
            $attachment_details = array(
                'guid'           => $uploaded_image['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
            $attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
            $experience_thumb = wp_get_attachment_image_src( $attach_id, 'homey-listing-thumb' );
            $feat_image_url = wp_get_attachment_url( $attach_id );

            $ajax_response = array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $attach_id,
                'full_image'    => $feat_image_url,
                'thumb'    => $experience_thumb[0],
            );

            echo json_encode( $ajax_response );
            die;

        } else {
            $reason = esc_html__('Image upload failed!','homey');
            if($is_dimension_valid == -1){
               $reason = esc_html__('Image Dimensions Error','homey');
            }

            $ajax_response = array( 'success' => false, 'reason' => $reason );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

/*-----------------------------------------------------------------------------------*/
// Remove experience attachments
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_remove_experience_thumbnail', 'homey_remove_experience_thumbnail' );
add_action( 'wp_ajax_nopriv_homey_remove_experience_thumbnail', 'homey_remove_experience_thumbnail' );
if( !function_exists('homey_remove_experience_thumbnail') ) {
    function homey_remove_experience_thumbnail() {

        $nonce = sanitize_text_field($_POST['removeNonce']);

        $remove_attachment = false;
        if (!wp_verify_nonce($nonce, 'verify_experience_gallery_nonce')) {

            echo json_encode(array(
                'remove_attachment' => false,
                'reason' => esc_html__('Invalid Nonce', 'homey')
            ));
            wp_die();
        }

        if (isset($_POST['thumb_id']) && isset($_POST['experience_id'])) {
            $thumb_id = intval($_POST['thumb_id']);
            $experience_id = intval($_POST['experience_id']);

            if ( $thumb_id > 0 && $experience_id > 0 ) {
                delete_post_meta($experience_id, 'homey_experience_images', $thumb_id);
                $remove_attachment = wp_delete_attachment($thumb_id);
            } elseif ($thumb_id > 0) {
                if( false == wp_delete_attachment( $thumb_id )) {
                    $remove_attachment = false;
                } else {
                    $remove_attachment = true;
                }
            }
        }

        echo json_encode(array(
            'remove_attachment' => $remove_attachment,
        ));
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Experience upgrade paypal payment
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_homey_experience_paypal_payment', 'homey_experience_paypal_payment');
if( !function_exists('homey_experience_paypal_payment') ) {
    function homey_experience_paypal_payment() {
        global $current_user;
        $experience_id        =   intval($_POST['experience_id']);
        $is_upgrade    =   intval($_POST['is_upgrade']);
        $price_featured_submission = homey_option('price_featured_experience');
        $currency = homey_option('payment_currency');

        $blogInfo = esc_url( home_url('/') );

        wp_get_current_user();
        $userID =   $current_user->ID;
        $post   =   get_post($experience_id);

        if( $post->post_author != $userID && !homey_is_admin()){
            wp_die('Are you kidding?');
        }

        $is_paypal_live             =   homey_option('paypal_api');
        $host                       =   'https://api.sandbox.paypal.com';
        $price_featured_submission  =   floatval( $price_featured_submission );
        $submission_curency         =   esc_html( $currency );
        $payment_description        =   esc_html__('Experience payment on ','homey').$blogInfo;

        if ( $is_upgrade == 1 ) {
            $total_price     =  number_format($price_featured_submission, 2, '.','');
            $payment_description =   esc_html__('Upgrade to featured experience on ','homey').$blogInfo;
        }

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';

        $dashboard     =   homey_get_template_link('template/dashboard.php');
        $cancel_link   =   $dashboard;

        $return_link  = add_query_arg( array(
            'dpage' => 'featured_success_exp',
         ), $dashboard );

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__('Upgrade to Featured Experience','homey'),
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Upgrade Experience',
        );

        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['experience_id']          = $experience_id;
        $output['is_experience_upgrade']  = $is_upgrade;

        $save_output[$current_user->ID]   =   $output;
        update_option('homey_featured_paypal_transfer',$save_output);

        print ''.$payment_approval_url;

        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Add to favorites
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_add_to_exp_favorite', 'homey_add_to_exp_favorite' );
if( !function_exists( 'homey_add_to_exp_favorite' ) ) {
    // a:1:{i:0;i:543;}
    function homey_add_to_exp_favorite () {
        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;
        $fav_option = 'homey_favorites-'.$userID;
        $experience_id = intval( $_POST['experience_id'] );
        $current_prop_fav = get_option( 'homey_favorites-'.$userID );

        $local = homey_get_localization();

        // Check if empty or not
        if( empty( $current_prop_fav ) ) {
            $prop_fav = array();
            $prop_fav['1'] = $experience_id;
            update_option( $fav_option, $prop_fav );
            $arr = array( 'added' => true, 'response' => $local['remove_favorite'] );
            echo json_encode($arr);
            wp_die();
        } else {
            if(  ! in_array ( $experience_id, $current_prop_fav )  ) {
                $current_prop_fav[] = $experience_id;
                update_option( $fav_option,  $current_prop_fav );
                $arr = array( 'added' => true, 'response' => $local['remove_favorite'] );
                echo json_encode($arr);
                wp_die();
            } else {
                $key = array_search( $experience_id, $current_prop_fav );

                if( $key != false ) {
                    unset( $current_prop_fav[$key] );
                }

                update_option( $fav_option, $current_prop_fav );
                $arr = array( 'added' => false, 'response' => $local['add_favorite'] );
                echo json_encode($arr);
                wp_die();
            }
        }
        wp_die();
    }
}

/* --------------------------------------------------------------------------
 * Get invoice post type meta with default values
 ---------------------------------------------------------------------------*/
if ( !function_exists( 'homey_exp_get_invoice_meta' ) ):
    function homey_exp_get_invoice_meta( $post_id, $field = false ) {

        $defaults = array(
            'invoice_billion_for' => '',
            'invoice_billing_type' => '',
            'invoice_item_id' => '',
            'invoice_item_price' => '',
            'invoice_payment_method' => '',
            'invoice_purchase_date' => '',
            'invoice_for_experience' => '',
            'invoice_buyer_id' => ''
        );

        $meta = get_post_meta( $post_id, '_homey_invoice_meta', true );
        $meta = wp_parse_args( (array) $meta, $defaults );

        if ( $field ) {
            if ( isset( $meta[$field] ) ) {
                return $meta[$field];
            } else {
                return false;
            }
        }
        return $meta;
    }
endif;

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_exp_generate_invoice') ):
    function homey_exp_generate_invoice( $billingFor, $billionType, $list_pack_resv_ID, $invoiceDate, $userID, $featured, $upgrade, $paypalTaxID, $paymentMethod ) {
        $total_price = 0;
        $experience_owner = '';
        $local = homey_get_localization();

        $price_featured_submission = homey_option('price_featured_experience');
        $price_featured_submission = floatval( $price_featured_submission );

        $args = array(
            'post_title'    => 'Invoice ',
            'post_status'   => 'publish',
            'post_type'     => 'homey_invoice'
        );
        $inserted_post_id =  wp_insert_post( $args );

        if( $billionType != 'one_time' ) {
            $billionType = $local['recurring_text'];;
        } else {
            $billionType = $local['one_time_text'];
        }

        // reservation || package || experience || upgrade_featured
        if($billingFor == 'reservation') {
            $total_price = get_post_meta($list_pack_resv_ID, 'reservation_upfront', true);
            $experience_owner = get_post_meta($list_pack_resv_ID, 'experience_owner', true);

        } elseif($billingFor == 'experience') {
            if( $upgrade == 1 ) {
                $total_price = $price_featured_submission;

            }
        } elseif($billingFor == 'upgrade_featured') {
            $total_price = $price_featured_submission;

        } elseif($billingFor == 'package') {

        }


        $fave_meta = array();

        $fave_meta['invoice_billion_for'] = $billingFor;
        $fave_meta['invoice_for_experience'] = 1;
        $fave_meta['invoice_billing_type'] = $billionType;
        $fave_meta['invoice_item_id'] = $list_pack_resv_ID;
        $fave_meta['invoice_item_price'] = $total_price;
        $fave_meta['invoice_purchase_date'] = $invoiceDate;
        $fave_meta['invoice_buyer_id'] = $userID;
        $fave_meta['invoice_resv_owner'] = $experience_owner;
        $fave_meta['upgrade'] = $upgrade;
        $fave_meta['paypal_txn_id'] = $paypalTaxID;
        $fave_meta['invoice_payment_method'] = $paymentMethod;

        update_post_meta( $inserted_post_id, 'homey_invoice_buyer', $userID );
        update_post_meta( $inserted_post_id, 'invoice_resv_owner', $experience_owner );
        update_post_meta( $inserted_post_id, 'homey_invoice_type', $billionType );
        update_post_meta( $inserted_post_id, 'homey_invoice_for', $billingFor );
        update_post_meta( $inserted_post_id, 'homey_invoice_item_id', $list_pack_resv_ID );
        //check if experience_renter == 0
        $experience_renter = get_post_meta( $list_pack_resv_ID, 'experience_renter', true );

        if($experience_renter < 1){
            update_post_meta( $list_pack_resv_ID, 'experience_renter', $userID);
        }
        //end of check if experience renter == 0
        update_post_meta( $inserted_post_id, 'homey_invoice_item_id', $list_pack_resv_ID );

        update_post_meta( $inserted_post_id, 'homey_invoice_price', $total_price );
        update_post_meta( $inserted_post_id, 'homey_invoice_date', $invoiceDate );
        update_post_meta( $inserted_post_id, 'homey_paypal_txn_id', $paypalTaxID );
        update_post_meta( $inserted_post_id, 'homey_invoice_payment_method', $paymentMethod );

        update_post_meta( $inserted_post_id, '_homey_invoice_meta', $fave_meta );

        // Update post title
        $update_post = array(
            'ID'         => $inserted_post_id,
            'post_title' => 'Invoice '.$inserted_post_id,
        );
        wp_update_post( $update_post );
        return $inserted_post_id;
    }
endif;

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice Filter
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_exp_invoices_ajax_search', 'homey_exp_invoices_ajax_search' );
add_action( 'wp_ajax_homey_exp_invoices_ajax_search', 'homey_exp_invoices_ajax_search' );

if( !function_exists('homey_exp_invoices_ajax_search') ){
    function homey_exp_invoices_ajax_search() {
        global $current_user, $homey_local;
        wp_get_current_user();
        $userID = $current_user->ID;

        $homey_local = homey_get_localization();

        $meta_query = array();
        $date_query = array();

        if( isset($_POST['invoice_status']) &&  $_POST['invoice_status'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'invoice_payment_status';
            $temp_array['value'] = sanitize_text_field( $_POST['invoice_status'] );
            $temp_array['compare'] = '=';
            $temp_array['type'] = 'NUMERIC';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['invoice_type']) &&  $_POST['invoice_type'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'homey_invoice_for';
            $temp_array['value'] = sanitize_text_field( $_POST['invoice_type'] );
            $temp_array['compare'] = 'LIKE';
            $temp_array['type'] = 'CHAR';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['startDate']) &&  $_POST['startDate'] !='' ){
            $temp_array = array();
            $temp_array['after'] = sanitize_text_field( $_POST['startDate'] );
            $date_query[] = $temp_array;
        }

        if( isset($_POST['endDate']) &&  $_POST['endDate'] !='' ){
            $temp_array = array();
            $temp_array['before'] = sanitize_text_field( $_POST['endDate'] );
            $date_query[] = $temp_array;
        }

        if(homey_is_renter()) {
            $meta_query[] = array(
                'key' => 'homey_invoice_buyer',
                'value' => $userID,
                'compare' => '='
            );
        } else {

            $_meta_query[] = array(
                'key' => 'homey_invoice_buyer',
                'value' => $userID,
                'compare' => '='
            );
            $_meta_query[] = array(
                'key' => 'invoice_resv_owner',
                'value' => $userID,
                'compare' => '='
            );
            $_meta_query['relation'] = 'OR';

            $meta_query[] = $_meta_query;
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }


        $invoices_args = array(
            'post_type' => 'homey_invoice',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'date_query' => $date_query,
            'order' => 'ID ASC',

        );

        add_filter( 'posts_orderby', 'filter_post_sort_query' );
        $invoices = new WP_Query( $invoices_args );
        remove_filter( 'posts_orderby', 'filter_post_sort_query' );

        $total_price = 0;

        ob_start();

        while ( $invoices->have_posts()): $invoices->the_post();
            $fave_meta = homey_exp_get_invoice_meta( get_the_ID() );
            get_template_part('template-parts/dashboard/invoices/item');

            $total_price += $fave_meta['invoice_item_price'];
        endwhile;

        $result = ob_get_contents();
        ob_end_clean();

        echo json_encode( array( 'success' => true, 'result' => $result, 'total_price' => '' ) );
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Save experience custom periods
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_exp_add_custom_period', 'homey_exp_add_custom_period' );
if(!function_exists('homey_exp_add_custom_period')) {
    function homey_exp_add_custom_period() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();
        $experience_id     = intval(isset($_POST['experience_id']) ? $_POST['experience_id'] : 0);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $night_price  =  floatval ( isset($_POST['night_price']) ? $_POST['night_price'] : 0 );
        $guest_price  =  floatval ( isset($_POST['additional_guest_price']) ? $_POST['additional_guest_price'] : 0 );
        $weekend_price  =  floatval ( isset($_POST['weekend_price']) ? $_POST['weekend_price'] : 0 );
        $the_post= get_post( $experience_id);
        $period_meta['night_price'] = $night_price;
        $period_meta['weekend_price'] = $weekend_price;
        $period_meta['guest_price'] = $guest_price;
        $current_period_meta_array = get_post_meta($experience_id, 'homey_custom_period', true);
        if(empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }
        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }
        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        $start_date     =  date('d-m-Y', custom_strtotime($start_date));
        $end_date       =  date('d-m-Y', custom_strtotime($end_date));

        $start_date      =   new DateTime($start_date);
        $start_date_unix =   $start_date->getTimestamp();

        $end_date        =   new DateTime($end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        $current_period_meta_array[$start_date_unix] = $period_meta;

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {
            $current_period_meta_array[$start_date_unix] = $period_meta;
            //print 'memx '.memory_get_usage ().' </br>/';
            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($experience_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

if (!function_exists('homey_exp_copy_images_for_duplicated_post')) {
    function homey_exp_copy_images_for_duplicated_post($experience_id = 0)
    {
        if (!empty($_POST['experience_image_ids']) && is_array($_POST['experience_image_ids'])) {
            //require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
            require ABSPATH . 'wp-admin/includes/image.php';
            $upload_dir = wp_upload_dir();
            $path = $upload_dir['path'];
            $subdir = $upload_dir['subdir'];

            $to_copy = count($_POST['experience_image_ids']); // test
            $copied = 0; // test
            $experience_image_ids = array();

            foreach ($_POST['experience_image_ids'] as $img_id) {
                // Check if we have a valid image/attachment.
                if ($img_id && $file = get_attached_file($img_id)) {
                    $filename = basename($file);
                    $file2 = $path . '/' . wp_unique_filename($path, $filename);

                    // Copy the image file to $path.
                    if (@copy($file, $file2)) {
                        // Copy the attachment (post) data.
                        $att = get_post($img_id, ARRAY_A);
                        unset($att['ID']);
                        $img_id2 = wp_insert_attachment($att, $file2);
                        // Then add the meta data `homey_experience_images`.
                        add_post_meta($experience_id, 'homey_experience_images', $img_id2);
                        $experience_image_ids[] = intval($img_id);

                        // Copy the attachment's meta data.
                        $attach_data = wp_generate_attachment_metadata($img_id2, $file2);
                        wp_update_attachment_metadata($img_id2, $attach_data);

                        $copied++; // test
                    }else{
                        //no, not file copied
                    }
                } else {
                    $to_copy--; // test
                }
            }

            // featured image
            update_post_meta($experience_id, '_thumbnail_id', $experience_image_ids[0]);

            return 1;
        }
    }
}

if(!function_exists('homey_exp_addCustomPeriodDuplicated')) {
    function homey_exp_addCustomPeriodDuplicated($experience_id=0, $dup_experience_id=0) {
        if($experience_id == 0 || $dup_experience_id == 0){
            return false;
        }

        $current_period_meta_array = get_post_meta($dup_experience_id, 'homey_custom_period', true);

        update_post_meta($experience_id, 'homey_custom_period', $current_period_meta_array );

        return 1;
    }
}


if(!function_exists('homey_exp_add_custom_period_old')) {
    function homey_exp_add_custom_period_old() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();


        $experience_id     = intval($_POST['experience_id']);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $night_price    =  floatval ( $_POST['night_price']);
        $guest_price    =  floatval ( $_POST['additional_guest_price'] );
        $weekend_price  =  floatval ( $_POST['weekend_price'] );
        $the_post= get_post( $experience_id);

        $period_meta['night_price'] = $night_price;
        $period_meta['weekend_price'] = $weekend_price;
        $period_meta['guest_price'] = $guest_price;

        $current_period_meta_array = get_post_meta($experience_id, 'homey_custom_period', true);

        if(empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }

        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }


        $start_date      =   new DateTime($start_date);
        $start_date_unix =   $start_date->getTimestamp();

        $end_date        =   new DateTime($end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        $current_period_meta_array[$start_date_unix] = $period_meta;

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {

            $current_period_meta_array[$start_date_unix] = $period_meta;
            //print 'memx '.memory_get_usage ().' </br>/';
            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($experience_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Delete experience custom periods
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_exp_delete_custom_period', 'homey_exp_delete_custom_period' );
if(!function_exists('homey_exp_delete_custom_period')) {
    function homey_exp_delete_custom_period() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();


        $experience_id     = intval($_POST['experience_id']);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $the_post= get_post( $experience_id);

        $current_period_meta_array = get_post_meta($experience_id, 'homey_custom_period', true);


        if( !is_array($current_period_meta_array)) {
            $current_period_meta_array = array();
        }

        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }



        $start_date      =   new DateTime("@".$start_date);
        $start_date_unix =   $start_date->getTimestamp();
        $end_date        =   new DateTime("@".$end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        unset($current_period_meta_array[$start_date_unix]);

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {

            if($current_period_meta_array[$start_date_unix]){
                unset($current_period_meta_array[$start_date_unix]);
            }

            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($experience_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Get Custom periods
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_exp_get_custom_period')) {
    function homey_exp_get_custom_period($experience_id, $actions = true ) {
        if(empty($experience_id)) {
            return;
        }

        $homey_date_format = homey_option('homey_date_format');

        if($homey_date_format == 'yy-mm-dd') {
            $h_date_format = 'Y-m-d';

        } elseif($homey_date_format == 'yy-dd-mm') {
            $h_date_format = 'Y-d-m';

        } elseif($homey_date_format == 'mm-yy-dd') {
            $h_date_format = 'm-Y-d';

        } elseif($homey_date_format == 'dd-yy-mm') {
            $h_date_format = 'd-Y-m';

        } elseif($homey_date_format == 'mm-dd-yy') {
            $h_date_format = 'm-d-Y';

        } elseif($homey_date_format == 'dd-mm-yy') {
            $h_date_format = 'd-m-Y';

        }elseif($homey_date_format == 'dd.mm.yy') {
            $h_date_format = 'd.m.Y';

        } else {
            $h_date_format = 'Y-m-d';
        }

        $output = '';
        $i = 0;
        $night_price = '';
        $weekend_price = '';
        $guest_price = '';

        $local = homey_get_localization();

        $hide_fields = homey_option('add_hide_fields');
        $custom_weekend_price = isset($hide_fields['custom_weekend_price']) ? $hide_fields['custom_weekend_price'] : 0;

        $period_array = get_post_meta($experience_id, 'homey_custom_period', true);

        if(empty($period_array)) {
            return;
        }

        if(is_array($period_array)) {
            ksort($period_array);
        }

        foreach ($period_array as $timestamp => $data) {

            $is_consecutive_day = 0;
            $from_date          = new DateTime("@".$timestamp);
            $to_date            = new DateTime("@".$timestamp);
            $tomorrrow_date     = new DateTime("@".$timestamp);

            $tomorrrow_date->modify('tomorrow');
            $tomorrrow_date = $tomorrrow_date->getTimestamp();


            if ( $i == 0 ) {
                $i = 1;


                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $guest_price   = $data['guest_price'];

                $from_date_unix = $from_date->getTimestamp();

                echo '<tr>';

                echo '<td dfgdgd data-label="'.esc_attr($local['start_date']).'">
                    '.$from_date->format($h_date_format).'
                </td>';
            }

            if ( !array_key_exists ($tomorrrow_date, $period_array) ) {
                $is_consecutive_day = 1;

            } else {

                if( $period_array[$tomorrrow_date]['night_price']   !=  $night_price ||
                    $period_array[$tomorrrow_date]['weekend_price'] !=  $weekend_price ||
                    $period_array[$tomorrrow_date]['guest_price']   !=  $guest_price ) {
                        $is_consecutive_day = 1;
                }
            }

            if( $is_consecutive_day == 1 ) {

                if( $i == 1 ) {

                    $to_date_unix = $from_date->getTimestamp();
                    echo '<td data-label="'.esc_attr($local['end_date']).'">
                        '.$from_date->format($h_date_format).'
                    </td>';


                    echo '<td data-label="'.esc_attr($local['nightly_label']).'">
                        <strong>'.homey_formatted_price($night_price, false).'</strong>
                    </td>';

                    if($custom_weekend_price != 1) {
                        echo '<td data-label="'.esc_attr($local['weekends_label']).'">
                            <strong>'.homey_formatted_price($weekend_price, false).'</strong>
                        </td>';
                    }

                    $booking_hide_fields = homey_option('booking_hide_fields');
                    if ( $booking_hide_fields['guests'] != 1 ) {
                        echo '<td data-label="' . esc_attr($local['addinal_guests_label']) . '">
                        <strong>' . homey_formatted_price($guest_price, false) . '</strong>
                    </td>';
                    }

                    if($actions) {
                    echo '
                    <td data-label="'.esc_html__('Actions', 'homey').'">
                    <div class="custom-actions">
                        <button class="homey_delete_period btn btn-primary" data-experienceid="'.$experience_id.'" data-startdate="'.$from_date_unix.'" data-enddate="'.$to_date_unix.'">'.$local['delete_btn'].'</button>
                    </div>
                    </td>';
                    }

                    echo '</tr>';
                }
                $i = 0;
                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $guest_price   = $data['guest_price'];


            }

        } // End foreach

    }
}

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice Print experience
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_exp_create_invoice_print', 'homey_exp_create_invoice_print' );
add_action( 'wp_ajax_homey_exp_create_invoice_print', 'homey_exp_create_invoice_print' );

if ( !function_exists( 'homey_exp_create_invoice_print' ) ) {
    function homey_exp_create_invoice_print() {

        if(!isset($_POST['invoice_id'])|| !is_numeric($_POST['invoice_id'])){
            exit();
        }

        $invoice_id = intval($_POST['invoice_id']);
        $the_post= get_post( $invoice_id );

        if( $the_post->post_type != 'homey_invoice' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/bootstrap.min.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<body>';

        global $homey_local;
        wp_get_current_user();

        $homey_invoice_buyer = get_post_meta( $invoice_id, 'homey_invoice_buyer', true );
        $user_info      = get_userdata($homey_invoice_buyer);
        $user_email     = $user_info->user_email;
        $first_name     = $user_info->first_name;
        $last_name      = $user_info->last_name;

        if( !empty($first_name) && !empty($last_name) ) {
            $fullname = $first_name.' '.$last_name;
        } else {
            $fullname = $user_info->display_name;
        }

        $post = get_post( $invoice_id );
        $invoice_data = homey_exp_get_invoice_meta( $invoice_id );
        $invoice_item_id = $invoice_data['invoice_item_id'];

        $publish_date = $post->post_date;
        $publish_date = date_i18n( get_option('date_format'), strtotime( $publish_date ) );
        $invoice_logo = homey_option( 'invoice_logo', false, 'url' );
        $invoice_company_name = homey_option( 'invoice_company_name' );
        $invoice_address = homey_option( 'invoice_address' );
        $invoice_additional_info = homey_option( 'invoice_additional_info' );

        $is_reservation_invoice = false;
        if($invoice_data['invoice_billion_for'] == 'reservation') {
            $is_reservation_invoice = true;
        }

        if($invoice_data['invoice_billion_for'] == 'reservation') {

            $billing_for_text = $homey_local['resv_fee_text'];

        } elseif($invoice_data['invoice_billion_for'] == 'experience') {
            if( $invoice_data['upgrade'] == 1 ) {
                $billing_for_text =  $homey_local['upgrade_text'];

            } else {
                $billing_for_text =  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
            }
        } elseif($invoice_data['invoice_billion_for'] == 'package') {
            $billing_for_text =  $homey_local['inv_package'];
        }
        ?>
        <div class="invoice-detail block">
            <div class="invoice-header clearfix">
                <div class="block-left">
                    <div class="invoice-logo">
                        <?php if( !empty($invoice_logo) ) { ?>
                            <img src="<?php echo esc_url($invoice_logo); ?>" alt="<?php esc_attr_e('logo', 'homey');?>">
                        <?php } ?>
                    </div>
                    <ul class="list-unstyled">
                        <?php if( !empty($invoice_company_name) ) { ?>
                            <li><strong><?php echo esc_attr($invoice_company_name); ?></strong></li>
                        <?php } ?>
                        <li><?php echo homey_option( 'invoice_address' ); ?></li>
                    </ul>
                </div>
                <div class="block-right">
                    <ul class="list-unstyled">
                        <li><strong><?php esc_html_e('Invoice:', 'homey'); ?></strong> <?php echo esc_attr($invoice_id); ?></li>
                        <li><strong><?php esc_html_e('Date:', 'homey'); ?></strong> <?php echo esc_attr($publish_date); ?></li>
                        <?php if($is_reservation_invoice) { ?>
                            <li><strong><?php esc_html_e('Reservation ID:', 'homey'); ?></strong> <?php echo esc_attr($invoice_item_id); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div><!-- invoice-header -->

            <div class="invoice-body clearfix">
                <ul class="list-unstyled">
                    <li><strong><?php echo esc_html__('To:', 'homey'); ?></strong></li>
                    <li><?php echo esc_attr($fullname); ?></li>
                    <li><?php echo esc_html__('Email:', 'homey'); ?> <?php echo esc_attr($user_email);?></li>
                </ul>
                <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>

                <?php
                if($is_reservation_invoice) {
                    $resv_id = $invoice_item_id;
                    echo homey_calculate_exp_reservation_cost($resv_id);
                } else {
                    echo '<div class="payment-list"><ul>';
                        echo '<li>'.esc_attr($homey_local['billing_for']).' <span>'.esc_attr($billing_for_text).'</span></li>';
                        echo '<li>'.esc_attr($homey_local['billing_type']).' <span>'.esc_html( $invoice_data['invoice_billing_type'] ).'</span></li>';
                        echo '<li>'.esc_attr($homey_local['inv_pay_method']).' <span>'.esc_html($invoice_data['invoice_payment_method']).'</span></li>';
                        echo '<li class="payment-due">'.esc_attr($homey_local['inv_total']).' <span>'.homey_formatted_price( $invoice_data['invoice_item_price'] ).'</span></li>';
                        echo '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$invoice_data['invoice_item_price'].'">';
                    echo '</ul></div>';
                }
                ?>

            </div><!-- invoice-body -->

            <?php if( !empty($invoice_additional_info)) { ?>
            <div class="invoice-footer clearfix">
                <dl>
                    <dt><?php echo esc_html__('Additional Information:', 'homey'); ?></dt>
                    <dd><?php echo homey_option( 'invoice_additional_info' ); ?></dd>
                </dl>
            </div><!-- invoice-footer -->
            <?php } ?>

        </div>
        <?php

        print '</body></html>';
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Homey Print Property
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_create_experience_print', 'homey_create_experience_print' );
add_action( 'wp_ajax_homey_create_experience_print', 'homey_create_experience_print' );

if( !function_exists('homey_create_experience_print')) {
    function homey_create_experience_print () {
        global $homey_prefix;
        $homey_prefix = 'homey_';
        if(!isset($_POST['experience_id'])|| !is_numeric($_POST['experience_id'])){
            exit();
        }

        $experience_id = intval($_POST['experience_id']);
        $the_post= get_post( $experience_id );

        if( $the_post->post_type != 'experience' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/bootstrap.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/font-awesome-min.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/icons.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/styling-options.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<body class="print-page">';

        $homey_local = homey_get_localization();
        $print_logo = homey_option( 'print_page_logo', false, 'url' );

        $image_id           = get_post_thumbnail_id( $experience_id );
        $full_img           = wp_get_attachment_image_src($image_id, 'homey-gallery');
        $full_img           = $full_img [0];

        $title              = get_the_title( $experience_id );
        $prop_excerpt       = $the_post->post_content;
        $prop_excerpt       = apply_filters('the_content', $prop_excerpt);
        $author_id          = $the_post->post_author;

        $rating = homey_option('rating');
        $total_rating = get_post_meta( $experience_id, 'experience_total_rating', true );

        $address = '';//homey_get_experience_data_by_id('experience_address', $experience_id);
        $night_price = homey_get_experience_data_by_id('night_price', $experience_id);
        $hour_price = homey_get_experience_data_by_id('hour_price', $experience_id);

        $experience_author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $author_id);
        $reviews = homey_get_host_reviews($author_id);
        $booking_type = homey_booking_type_by_id($experience_id);

        $doc_verified = $experience_author['doc_verified'];
        $verified = false;
        if($doc_verified) {
            $verified = true;
        }

        $guests     = homey_get_experience_data_by_id('guests', $experience_id);
        $size       = homey_get_experience_data_by_id('experience_size', $experience_id);
        $size_unit       = homey_get_experience_data_by_id('experience_size_unit', $experience_id);
        $checkin_after   = homey_get_experience_data_by_id('checkin_after', $experience_id);
        $checkout_before = homey_get_experience_data_by_id('checkout_before', $experience_id);
        $room_type       = homey_taxonomy_simple_by_ID('room_type', $experience_id);
        $experience_type    = homey_taxonomy_simple_by_ID('experience_type', $experience_id);

        $weekends_price = homey_get_experience_data_by_id('weekends_price', $experience_id);


        $weekends_days = homey_get_experience_data_by_id('weekends_days', $experience_id);
        $priceWeekly = homey_get_experience_data_by_id('priceWeek', $experience_id);
        $priceMonthly = homey_get_experience_data_by_id('priceMonthly', $experience_id);
        $min_stay_days = homey_get_experience_data_by_id('min_book_days', $experience_id);
        $max_stay_days = homey_get_experience_data_by_id('max_book_days', $experience_id);
        $min_stay_hours = homey_get_experience_data_by_id('min_book_hours', $experience_id);
        $security_deposit = homey_get_experience_data_by_id('security_deposit', $experience_id);
        $cleaning_fee = homey_get_experience_data_by_id('cleaning_fee', $experience_id);
        $cleaning_fee_type = homey_get_experience_data_by_id('cleaning_fee_type', $experience_id);
        $city_fee = homey_get_experience_data_by_id('city_fee', $experience_id);
        $city_fee_type = homey_get_experience_data_by_id('city_fee_type', $experience_id);
        $additional_guests_price = homey_get_experience_data_by_id('additional_guests_price', $experience_id);
        $allow_additional_guests = homey_get_experience_data_by_id('allow_additional_guests', $experience_id);

        $smoke            = homey_get_experience_data_by_id('smoke', $experience_id);
        $pets             = homey_get_experience_data_by_id('pets', $experience_id);
        $party            = homey_get_experience_data_by_id('party', $experience_id);
        $children         = homey_get_experience_data_by_id('children', $experience_id);
        $additional_rules = homey_get_experience_data_by_id('additional_rules', $experience_id);

        $min_book_days  = homey_get_experience_data_by_id('min_book_days', $experience_id);
        $max_book_days  = homey_get_experience_data_by_id('max_book_days', $experience_id);

        $sn_text_yes = esc_html__(homey_option('experience_sn_text_yes'), 'homey');
        $sn_text_no = esc_html__(homey_option('experience_sn_text_no'), 'homey');

        if($smoke != 1) {
            $smoke_allow = 'homey-icon homey-icon-close';
            $smoke_text = $sn_text_no;
        } else {
            $smoke_allow = 'homey-icon homey-icon-check-circle-1';
            $smoke_text = $sn_text_yes;
        }

        if($pets != 1) {
            $pets_allow = 'homey-icon homey-icon-close';
            $pets_text = $sn_text_no;
        } else {
            $pets_allow = 'homey-icon homey-icon-check-circle-1';
            $pets_text = $sn_text_yes;
        }

        if($party != 1) {
            $party_allow = 'homey-icon homey-icon-close';
            $party_text = $sn_text_no;
        } else {
            $party_allow = 'homey-icon homey-icon-check-circle-1';
            $party_text = $sn_text_yes;
        }

        if($children != 1) {
            $children_allow = 'homey-icon homey-icon-close';
            $children_text = $sn_text_no;
        } else {
            $children_allow = 'homey-icon homey-icon-check-circle-1';
            $children_text = $sn_text_yes;
        }

        $hide_labels = homey_option('show_hide_labels');

        $cleaning_fee_period = $city_fee_period = '';


        if($weekends_days == 'sat_sun') {
            $weekendDays = esc_html__('Sat & Sun', 'homey');

        } elseif($weekends_days == 'fri_sat') {
            $weekendDays = esc_html__('Fri & Sat', 'homey');

        } elseif($weekends_days == 'thurs_fri_sat') {
            $weekendDays = esc_html__('Thurs, Fri & Sat', 'homey');
        } elseif($weekends_days == 'fri_sat_sun') {
            $weekendDays = esc_html__('Fri, Sat & Sun', 'homey');
        }

        $slash = '';
        if(!empty($room_type) && !empty($experience_type)) {
            $slash = '/';
        }
        ?>

        <div class="print-main-wrap">
            <div class="print-wrap">

                <div class="print-header">
                    <h1><img src="<?php echo esc_url($print_logo); ?>" width="128" height="30" alt="<?php bloginfo( 'name' ); ?>"></h1>
                    <?php if(homey_option('experience_print_tagline')) { ?>
                    <span class="tag-line"><?php bloginfo( 'description' ); ?></span>
                    <?php } ?>
                </div>
                <div class="top-section">
                    <div class="block">
                        <div class="block-head">
                            <div class="block-left">
                                <h2 class="title"><?php echo esc_attr($title); ?></h2>
                                <div class="property-accomodation-detals-v5">
                                    <div class="property-accomodation-detals-v5">
        <?php if(!empty($guests) && $hide_labels['sn_guests_label'] != 1) { ?> <?php } ?> <?php echo esc_attr($guests); ?> <?php echo homey_option('experience_sn_guests_label'); ?> <span>•</span> <?php echo esc_html__('From', 'homey'); ?> <?php echo get_post_meta( $experience_id, $homey_prefix.'start_end_open', true );?> to <?php echo get_post_meta( $experience_id, $homey_prefix.'start_end_close', true );?> <span>•</span> <?php echo homey_taxonomy_simple('experience_language', $experience_id); ?>
                                    </div>
                                </div>
                                <div class="rating">
                                    <span class="star-text-right"><?php if($rating && ($total_rating != '' && $total_rating != 0 ) && homey_option('print_rating')) { ?>
                                            <?php echo homey_get_review_stars_v2($total_rating, true, true); ?>
            <?php } ?></span>
                                </div>
                            </div><!-- block-left -->
                            <div class="block-right">
                            <span class="item-price">
                                <?php echo homey_formatted_price($night_price, false, true); ?><sub>/<?php echo homey_option('glc_day_night_label');?></sub>
                            </span>
                            </div><!-- block-right -->
                        </div><!-- block-head -->

                        <?php if( !empty($full_img) ) { ?>
                            <img class="img-responsive" src="<?php echo esc_url( $full_img ); ?>" alt="<?php echo esc_attr($title); ?>">

                            <?php if(homey_option('experience_print_qr_code')) {?>
                            <img class="qr-code img-responsive" src="https://chart.googleapis.com/chart?chs=105x104&cht=qr&chl=<?php echo esc_url( get_permalink($experience_id) ); ?>&choe=UTF-8" title="<?php echo esc_attr($title); ?>" />
                            <?php } ?>
                        <?php } ?>

                    </div><!-- block -->
                </div>

                <?php if(homey_option('experience_print_host')) { ?>
                <div class="host-section">
                    <div class="block">
                        <div class="block-head">
                            <div class="media">
                                <div class="media-left">
                                    <?php echo ''.$experience_author['photo']; ?>
                                </div>
                                <div class="media-body">
                                    <h2 class="title"><?php echo homey_option('experience_sn_hosted_by'); ?> <span><?php echo esc_attr($experience_author['name']); ?></span></h2>

                                    <?php if(!empty($experience_author['city'])) { ?>
                                    <address><i class="homey-icon homey-icon-style-two-pin-marker" aria-hidden="true"></i> <?php echo esc_attr($experience_author['city']); ?></address>
                                    <?php } ?>

                                    <div class="block-body">
                                        <div class="row">
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                                <dl>
                                                    <dt><?php echo homey_option('experience_sn_pr_lang'); ?></dt>
                                                    <dd><?php echo esc_attr($experience_author['languages']); ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">


                                                <dl>

                                                    <dt><?php echo homey_option('experience_sn_pr_profile_status'); ?> </dt>


                                                    <?php
                                                    $current_page_user = homey_user_role_by_user_id($author_id);

                                                    if($current_page_user == 'administrator') { ?>

                                                        <dd class="text-success">
                                                            <i class="homey-icon homey-icon-check-circle-1"></i>
                                                            <?php echo homey_option('experience_sn_pr_verified'); ?>
                                                        </dd>

                                                    <?php
                                                    } else {
                                                    if($verified) { ?>
                                                        <dd class="text-success"><i class="homey-icon homey-icon-check-circle-1"></i> <?php esc_html_e('Verified', 'homey'); ?></dd>
                                                        <?php } else { ?>
                                                            <dd class="text-danger"><i class="homey-icon homey-icon-close"></i> <?php esc_html_e('Not Verified', 'homey'); ?></dd>
                                                        <?php }
                                                    }?>
                                                </dl>

                                            </div>

                                            <?php if($reviews['is_host_have_reviews']) { ?>
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                                <dl>
                                                    <dt><?php echo homey_option('experience_sn_pr_h_rating'); ?></dt>
                                                    <dd>
                                                        <div class="rating">
                                                            <?php echo ''.$reviews['host_rating']; ?>
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div><!-- block-body -->
                                </div>
                            </div>
                        </div><!-- block-head -->

                    </div><!-- block -->
                </div><!-- host-section -->
                <?php } ?>

                <?php if(homey_option('experience_print_description')) { ?>
                <div id="about-section" class="about-section">
                    <div class="block">
                        <div class="block-body">
                            <h2><?php echo homey_option('sn_about_experience_title'); ?></h2>
                            <?php echo ''.$prop_excerpt; ?>
                        </div>
                    </div><!-- block-body -->
                </div>
                <?php } ?>

                <?php if(homey_option('experience_print_details')) { ?>
                    <div id="details-section" class="details-section">
                        <div class="block">
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h3 class="title"><?php echo esc_html__('Providing', 'homey'); ?></h3>
                                    </div>
                                    <!-- block-left -->
                                    <div class="block-right">
                                        <div class="block-col block-col-100 block-accomodation">
                                            <dl class="detail-list">
                                                <dt><?php echo esc_html__('The host will provide:', 'homey'); ?></dt>
                                                <?php
                                                $what_to_provided = get_post_meta($experience_id, $homey_prefix.'what_to_provided', true);
                                                $what_to_provided_btn = get_post_meta($experience_id, $homey_prefix.'nothing_provided_btn', true);

                                                $count = 0;
                                                if(!empty($what_to_provided)) {
                                                    foreach($what_to_provided as $item_provide):
                                                        $item_provide_name = isset($item_provide['wwbp_name']) ? $item_provide['wwbp_name'] : '';
                                                        //                    dd($item_provide_desc, 0);
                                                        ?>
                                                        <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_html__($item_provide_name, 'homey'); ?> <!--<p><?php echo esc_html__($item_provide_desc, 'homey'); ?></p>--></dd>
                                                        <?php  $count++;
                                                    endforeach;
                                                }else{ ?>
                                                        <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <p><?php echo esc_html__('Nothing to provide', 'homey'); ?></p></dd>
                                                    <?php } ?>
                                            </dl>
                                        </div>
                                        <div class="spacer clearfix"></div>
                                        <div class="block-col block-col-100 block-accomodation">
                                            <dl class="detail-list">
                                                <dt><?php echo esc_html__('Bring with you:', 'homey'); ?></dt>
                                                <?php
                                                $what_to_bring = get_post_meta($experience_id, $homey_prefix.'what_to_bring', true);
                                                $what_to_bring_btn = get_post_meta($experience_id, $homey_prefix.'nothing_bring_btn', true);
                                                $count = 0;
                                                if(!empty($what_to_bring)) {
                                                    foreach($what_to_bring as $item_bring):
                                                        $item_bring_name = isset($item_bring['wbit_name']) ? $item_bring['wbit_name'] : '';
                                                        //                    dd($item_provide_desc, 0);
                                                        ?>
                                                        <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_html__($item_bring_name, 'homey'); ?> <!--<p><?php echo esc_html__($item_provide_desc, 'homey'); ?></p>--></dd>
                                                        <?php  $count++;
                                                    endforeach;
                                                }else{ ?>
                                                  <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <p><?php echo esc_html__('Nothing to provide', 'homey'); ?></p></dd>
                                                <?php } ?>
                                            </dl>
                                        </div>
                                    </div>
                                    <!-- block-right -->
                                </div>
                            </div><!-- block-section -->
                        </div><!-- block -->
                        <div id="extwaiokist" style="display:none" v="mlgef" q="4bfc5181" c="241.9" i="248" u="22.50" s="04072304" sg="svr_04152317-ga_04072304-bai_04042310" d="1" w="false" e="" a="2" m="BMe=" vn="9spdf"><div id="extwaigglbit" style="display:none" v="mlgef" q="4bfc5181" c="241.9" i="248" u="22.50" s="04072304" sg="svr_04152317-ga_04072304-bai_04042310" d="1" w="false" e="" a="2" m="BMe="></div></div></div>
                <?php } ?>

                <?php
                $amenities   = wp_get_post_terms( $experience_id, 'experience_amenity', array("fields" => "all"));
                $facilities  = wp_get_post_terms( $experience_id, 'experience_facility', array("fields" => "all"));
                ?>
                <?php if(homey_option('experience_print_features')) { ?>
                <div id="features-section" class="features-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('experience_sn_features'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <?php if(!empty($amenities)) { ?>
                                    <p><strong><?php echo esc_attr(homey_option('experience_sn_amenities')); ?></strong></p>
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php foreach($amenities as $amenity): ?>
                                            <li><i class="homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_attr($amenity->name); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php } ?>

                                    <?php if(!empty($facilities)) { ?>
                                    <p><strong><?php echo homey_option('experience_sn_facilities'); ?></strong></p>
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php foreach($facilities as $facility): ?>
                                            <li><i class="homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_attr($facility->name); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php } ?>

                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>

                <?php if(homey_option('experience_print_rules')) { ?>
                <div id="rules-section" class="rules-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('experience_sn_terms_rules'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <ul class="rules_list detail-list">
                                        <?php if($hide_labels['sn_smoking_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($smoke_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('experience_sn_smoking_allowed'); ?>:
                                            <strong><?php echo esc_attr($smoke_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_pets_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($pets_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('experience_sn_pets_allowed'); ?>:
                                            <strong><?php echo esc_attr($pets_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_party_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($party_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('experience_sn_party_allowed'); ?>:
                                            <strong><?php echo esc_attr($party_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_children_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($children_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('experience_sn_children_allowed'); ?>:
                                            <strong><?php echo esc_attr($children_text); ?></strong>
                                        </li>
                                        <?php } ?>
                                    </ul>

                                    <?php if( !empty($additional_rules)) { ?>
                                    <ul class="detail-list">
                                        <li><strong><?php echo homey_option('experience_sn_add_rules_info'); ?></strong></li>
                                        <li><?php echo esc_attr($additional_rules); ?></li>
                                    </ul>
                                    <?php } ?>

                                    <?php
                                    $cancellation_policy = homey_get_experience_data_by_id('cancellation_policy', $experience_id);
                                    $cancellation_policy   = get_the_content( '', '',  $cancellation_policy ); // Where $cancellation_policy is the ID
                                    if( !empty($cancellation_policy)) { ?>
                                    <ul class="detail-list">
                                        <li><strong><?php echo esc_html__('Cancellation policy', 'homey'); ?></strong></li>
                                        <li><?php echo $cancellation_policy; ?></li>
                                    </ul>
                                    <?php } ?>

                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>


                <?php $prop_images = get_post_meta( $experience_id, 'homey_experience_images', false ); ?>
                <?php $print_gallery = homey_option('experience_print_gallery'); ?>
                <?php if( !empty( $prop_images ) && $print_gallery) { ?>
                <div class="image-section">
                    <div class="block">
                        <div class="block-body gallery-block">
                            <?php foreach( $prop_images as $img_id ): ?>
                            <div class="block-left">
                                <?php echo wp_get_attachment_image( $img_id, 'homey-gallery', array( "class" => "img-responsive" ) ); ?>
                            </div><!-- block-left -->
                            <?php endforeach; ?>
                        </div><!-- block-body -->
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>


<?php
        print '<script>window.print();</script></body></html>';
        wp_die();
    }
}

add_action( 'wp_ajax_homey_put_hold_experience', 'homey_put_hold_experience' );

if( !function_exists('homey_put_hold_experience') ) {

    function homey_put_hold_experience() {
        $current_user = wp_get_current_user();
        $userID       =   $current_user->ID;
        $user_login   =   $current_user->user_login;

        if ( !is_user_logged_in() ) {
            exit('ko');
        }

        $experience_id=intval($_POST['experience_id']);
        if(!is_numeric($experience_id)) {
            exit();
        }

        $the_post= get_post($experience_id);

        if($the_post->post_status=='disabled'){
            $new_status = 'publish';
        }else{
            $new_status = 'disabled';
            update_post_meta($experience_id, 'homey_featured', 0);
        }
        $my_post = array(
            'ID'           => $experience_id,
            'post_status'  => $new_status
        );

        wp_update_post( $my_post );

        $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'Success', 'homey' ) );
        echo json_encode( $ajax_response );
        wp_die();

    }
}

add_action( 'init', 'homey_exp_my_custom_post_status' );
if( !function_exists('homey_exp_my_custom_post_status') ):
    function homey_exp_my_custom_post_status(){

        register_post_status( 'disabled', array(
                    'label'                     => esc_html__(  'disabled', 'homey' ),
                    'public'                    => false,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Disabled by user <span class="count">(%s)</span>', 'Disabled by user <span class="count">(%s)</span>','homey' ),
            ) );

    }
endif;


add_action('wp_ajax_homey_approve_experience', 'homey_approve_experience');
if (!function_exists('homey_approve_experience')) {
    function homey_approve_experience()
    {
        if (!is_user_logged_in()) {
            exit('ko');
        }

        $experience_id = intval($_POST['experience_id']);
        if (!is_numeric($experience_id)) {
            exit();
        }

        $the_post = get_post($experience_id);
        $reason_text = esc_html__('Status not changed', 'homey');

        if ($the_post->post_status != 'publish') {
            $new_status = 'publish';
            $reason_text = esc_html__('Success', 'homey');

            $my_post = array(
                'ID' => $experience_id,
                'post_status' => $new_status
            );

            wp_update_post($my_post);

            $ajax_response = array('success' => true, 'reason' => $reason_text);

            //email send to owner for confirmation
            $experience_current_owner = get_post_meta($experience_id, 'experience_owner', true);
            $experience_first_owner = get_post_meta($experience_id, 'homey_first_owner_user_id', true);

            $experience_owner = $experience_current_owner > 0 ? $experience_current_owner : $experience_first_owner;

            $user_info = get_userdata($experience_owner);

            if (isset($user_info->user_email)) {
                $owner_email = $user_info->user_email;
                $args = array(
                    'experience_title' => get_the_title($experience_id),
                    'experience_id' => $experience_id,
                    'experience_url' => get_permalink($experience_id),
                    'post_status_user' => esc_html__('Your experience is published.', 'homey'),
                );

                homey_email_composer($owner_email, 'experience_approved', $args);
            }

            //email send to owner for confirmation
            echo json_encode($ajax_response);
            wp_die();
        }

        $ajax_response = array('success' => false, 'reason' => $reason_text);
        echo json_encode($ajax_response);
        wp_die();

    }
}
