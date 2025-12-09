<?php
/*-----------------------------------------------------------------------------------*/
// Availability Search Filter
/*-----------------------------------------------------------------------------------*/
add_filter('homey_check_search_availability_filter_exp', 'homey_check_search_availability_exp_callback', 10, 3);
if( !function_exists('homey_check_search_availability_exp_callback') ) {
    function homey_check_search_availability_exp_callback( $query_args, $arrive ) {

        global $post;
        $allowed_html =  array();
        $post_ids = array();

        if ( empty($arrive) ) {
            
            if( homey_option('enable_radius_exp') ) {
                return '';
            } else {
                return $query_args;
            }
        }

        $check_in_date = sanitize_text_field ( wp_kses ( $arrive, $allowed_html) );

        $args = array(
            'post_type' => 'experience',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        $wpQry = new WP_Query( $args );

        if ( $wpQry->have_posts() ) :
            while ( $wpQry->have_posts() ) : $wpQry->the_post();
                $experience_id = $post->ID;
                $check = check_experience_availability_for_search($check_in_date, $experience_id);
                if($check) {
                    $post_ids[] = $experience_id;
                }

            endwhile;
        endif;

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        if( homey_option('enable_radius_exp') ) {
            return $post_ids;

        } else {
            $query_args[ 'post__in' ] = $post_ids;
            return $query_args;
        }
    }
}

if(!function_exists('homey_search_date_format')) {
    function homey_search_date_format($gdate) {

        $homey_date_format = homey_option('homey_date_format');

        if(empty($gdate)) {
            return '';
        }

        if($homey_date_format == 'yy-mm-dd') {
            $get_date = explode('-', $gdate);
            $year = $get_date[0];
            $month = $get_date[1];
            $day = $get_date[2];

        } elseif($homey_date_format == 'yy-dd-mm') {
            $get_date = explode('-', $gdate);
            $year = $get_date[0];
            $month = $get_date[2];
            $day = $get_date[1];

        } elseif($homey_date_format == 'mm-yy-dd') {
            $get_date = explode('-', $gdate);
            $year = $get_date[1];
            $month = $get_date[0];
            $day = $get_date[2];
            
        } elseif($homey_date_format == 'dd-yy-mm') {
            $get_date = explode('-', $gdate);
            $year = $get_date[1];
            $month = $get_date[2];
            $day = $get_date[0];
            
        } elseif($homey_date_format == 'mm-dd-yy') {
            $get_date = explode('-', $gdate);
            $year = $get_date[2];
            $month = $get_date[0];
            $day = $get_date[1];
            
        } elseif($homey_date_format == 'dd-mm-yy') {
            $get_date = explode('-', $gdate);
            $year = $get_date[2];
            $month = $get_date[1];
            $day = $get_date[0];
            
        } elseif($homey_date_format == 'dd.mm.yy') {
            $get_date = explode('.', $gdate);
            $year = $get_date[2];
            $month = $get_date[1];
            $day = $get_date[0];

        } else {
            $return_date = $gdate;
        }

        $return_date = $year.'-'.$month.'-'.$day;
        return $return_date;
    }
}

add_filter('homey_radius_filter_exp', 'homey_radius_filter_exp_callback', 10, 4);
if( !function_exists('homey_radius_filter_exp_callback') ) {
    function homey_radius_filter_exp_callback( $query_args, $search_lat, $search_long, $search_radius ) {

        global $wpdb;

        if ( ! ( $search_lat && $search_long && $search_radius ) ) {
            return '';
        }

        $radius_unit = homey_option('radius_unit_exp');
        if( $radius_unit == 'km' ) {
            $earth_radius = 6371;
        } elseif ( $radius_unit == 'mi' ) {
            $earth_radius = 3959;
        } else {
            $earth_radius = 6371;
        }

        $sql = $wpdb->prepare( "SELECT $wpdb->posts.ID,
                ( %s * acos(
                    cos( radians(%s) ) *
                    cos( radians( latitude.meta_value ) ) *
                    cos( radians( longitude.meta_value ) - radians(%s) ) +
                    sin( radians(%s) ) *
                    sin( radians( latitude.meta_value ) )
                ) )
                AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
                FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta
                    AS latitude
                    ON $wpdb->posts.ID = latitude.post_id
                INNER JOIN $wpdb->postmeta
                    AS longitude
                    ON $wpdb->posts.ID = longitude.post_id
                WHERE 1=1
                    AND ($wpdb->posts.post_status = 'publish' )
                    AND latitude.meta_key='homey_geolocation_lat'
                    AND longitude.meta_key='homey_geolocation_long'
                HAVING distance < %s
                ORDER BY $wpdb->posts.menu_order ASC, distance ASC",
            $earth_radius,
            $search_lat,
            $search_long,
            $search_lat,
            $search_radius
        );

        $post_ids = $wpdb->get_results( $sql, OBJECT_K );

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        $post_ids = array_keys( (array) $post_ids );
        return $post_ids;
    }
}

/*-----------------------------------------------------------------------------------*/
// Experience Search filter
/*-----------------------------------------------------------------------------------*/
add_filter('homey_search_filter_exp', 'homey_experience_search');
if( !function_exists('homey_experience_search') ) {
    function homey_experience_search($search_query)
    {

        $tax_query = array();
        $meta_query = array();
        $allowed_html = array();
        $query_ids = '';
        $homey_search_type = homey_search_type();

        $arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
        $guests = isset($_GET['guest']) ? $_GET['guest'] : '';
        $search_country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
        $search_state = isset($_GET['search_state']) ? $_GET['search_state'] : '';
        $search_city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
        $search_area = isset($_GET['search_area']) ? $_GET['search_area'] : '';
        $experience_type = isset($_GET['experience_type']) ? $_GET['experience_type'] : '';
        $lat = isset($_GET['lat']) ? $_GET['lat'] : '';
        $lng = isset($_GET['lng']) ? $_GET['lng'] : '';
        $search_radius = isset($_GET['radius']) ? $_GET['radius'] : 20;

        $country = isset($_GET['country']) ? $_GET['country'] : '';
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $city = isset($_GET['city']) ? $_GET['city'] : '';
        $area = isset($_GET['area']) ? $_GET['area'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        $arrive = homey_search_date_format($arrive);

        if( homey_option('enable_radius_exp') ) {

            $available_experiences_ids = apply_filters('homey_check_search_availability_filter_exp', $search_query, $arrive);
            $radius_ids = apply_filters('homey_radius_filter_exp', $search_query, $lat, $lng, $search_radius);

            if(!empty($available_experiences_ids) && !empty($radius_ids)) {
                $query_ids =  array_intersect($available_experiences_ids, $radius_ids);

                if(empty($query_ids)) {
                    $query_ids = array(0);
                }

            } elseif(empty($available_experiences_ids)) {
                $query_ids = $radius_ids;

            } elseif(empty($radius_ids)) {
                $query_ids = $available_experiences_ids;
            }

            if(!empty($query_ids)) {
                $search_query['post__in'] = $query_ids;
            }

        }

        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $search_query['s'] = $keyword;
        }

        if(!empty($experience_type)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($experience_type)
            );
        }

        if(!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_country',
                'field' => 'slug',
                'terms' => sanitize_text_field($country)
            );
        }

        if(!empty($state)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_state',
                'field' => 'slug',
                'terms' => sanitize_text_field($state)
            );
        }

        if(!empty($city)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_city',
                'field' => 'slug',
                'terms' => sanitize_text_field($city)
            );
        }

        if(!empty($area)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_area',
                'field' => 'slug',
                'terms' => sanitize_text_field($area)
            );
        }

        // min and max price logic
        if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any' && isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $min_price = doubleval(homey_clean($_GET['min-price']));
            $max_price = doubleval(homey_clean($_GET['max-price']));

            if ($min_price > 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any') {
            $min_price = doubleval(homey_clean($_GET['min-price']));
            if ($min_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $max_price = doubleval(homey_clean($_GET['max-price']));
            if ($max_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        if(!empty($guests)) {
            $meta_query[] = array(
                'key' => 'homey_guests',
                'value' => intval($guests),
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }


        if (isset($_GET['area']) && !empty($_GET['area'])) {
            if (is_array($_GET['area'])) {
                $areas = sanitize_text_field($_GET['area']);

                foreach ($areas as $area):
                    $tax_query[] = array(
                        'taxonomy' => 'experience_area',
                        'field' => 'slug',
                        'terms' => $area
                    );
                endforeach;
            }
        }

        if (isset($_GET['amenity']) && !empty($_GET['amenity'])) {
            if (is_array($_GET['amenity'])) {
                $amenities = $_GET['amenity'];

                foreach ($amenities as $amenity):
                    $tax_query[] = array(
                        'taxonomy' => 'experience_amenity',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($amenity)
                    );
                endforeach;
            }
        }

        if (isset($_GET['facility']) && !empty($_GET['facility'])) {
            if (is_array($_GET['facility'])) {
                $facilities = $_GET['facility'];

                foreach ($facilities as $facility):
                    $tax_query[] = array(
                        'taxonomy' => 'experience_facility',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($facility)
                    );
                endforeach;
            }
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if( $meta_count > 0 ){
            $search_query['meta_query'] = $meta_query;
        }


        $tax_count = count( $tax_query );

        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $search_query['tax_query'] = $tax_query;
        }
        
        return $search_query;
    }
}

if(!function_exists('check_experience_availability_for_search')) {
    function check_experience_availability_for_search($check_in_date, $experience_id) {

        $reservation_booked_array = get_post_meta($experience_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($experience_id);
        }

        $reservation_pending_array = get_post_meta($experience_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($experience_id);
        }

        $reservation_unavailable_array = get_post_meta($experience_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {
            return false; //dates are not available
        }

        return true; //dates are available
        
    }
}

if(!function_exists('check_experience_availability_for_hourly_search')) {

    function check_experience_availability_for_hourly_search($check_in_hour, $experience_id) {

        $reservation_booked_array = get_post_meta($experience_id, 'reservation_booked_hours', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_hours($experience_id);
        }

        $reservation_pending_array = get_post_meta($experience_id, 'reservation_pending_hours', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_hours($experience_id);
        }

        $check_in_hour      = new DateTime($check_in_hour);
        $check_in_hour_unix = $check_in_hour->getTimestamp();

        if( array_key_exists($check_in_hour_unix, $reservation_booked_array)  || array_key_exists($check_in_hour_unix, $reservation_pending_array) ) {
                return false; //dates are not available
        }

        return true; //dates are available
    }
}

add_action( 'wp_ajax_nopriv_homey_half_exp_map', 'homey_half_exp_map' );
add_action( 'wp_ajax_homey_half_exp_map', 'homey_half_exp_map' );
if( !function_exists('homey_half_exp_map') ) {
    function homey_half_exp_map() {

        global $homey_prefix, $homey_local, $template_args;

        $homey_prefix = 'homey_';
        $homey_local = homey_get_localization();

        $experience_text = $homey_local['experience_label'];
        
        check_ajax_referer('homey_map_ajax_nonce', 'security');

        $tax_query = array();
        $meta_query = array();
        $allowed_html = array();
        $query_ids = '';

        $cgl_types = homey_option('experience_cgl_types');
        $price_separator = homey_option('currency_separator');

        $arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
        $guests = isset($_POST['guest']) ? $_POST['guest'] : '';

        $search_country = isset($_POST['search_country']) ? $_POST['search_country'] : '';
        $search_state = isset($_POST['search_state']) ? $_POST['search_state'] : '';
        $search_city = isset($_POST['search_city']) ? $_POST['search_city'] : '';
        $search_area = isset($_POST['search_area']) ? $_POST['search_area'] : '';
        $experience_type = isset($_POST['experience_type']) ? $_POST['experience_type'] : '';
        $search_lat = isset($_POST['search_lat']) ? $_POST['search_lat'] : '';
        $search_lng = isset($_POST['search_lng']) ? $_POST['search_lng'] : '';
        $search_radius = isset($_POST['radius']) ? $_POST['radius'] : 20;

        $paged = isset($_POST['paged']) ? ($_POST['paged']) : '';
        $sort_by = isset($_POST['sort_by']) ? ($_POST['sort_by']) : '';
        $layout = isset($_POST['layout']) ? ($_POST['layout']) : 'list';
        $num_posts = isset($_POST['num_posts']) ? ($_POST['num_posts']) : '9';

        $country = isset($_POST['country']) ? $_POST['country'] : '';
        $state = isset($_POST['state']) ? $_POST['state'] : '';
        $city = isset($_POST['city']) ? $_POST['city'] : '';
        $area = isset($_POST['area']) ? $_POST['area'] : '';
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

        $arrive = homey_search_date_format($arrive);

        $template_args = array( 'listing-item-view' => 'item-grid-view' );

        if ( $layout == 'list' || $layout == 'list-v2' ) {
            $template_args = array( 'listing-item-view' => 'item-list-view' );
        } elseif ( $layout == 'card' ) {
            $template_args = array( 'listing-item-view' => 'item-card-view' );
        }

        $query_args = array(
            'post_type' => 'experience',
            'posts_per_page' => $num_posts,
            'post_status' => 'publish',
            'paged' => $paged,
        );

        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $query_args['s'] = $keyword;
        }

        if( !empty( $_POST["optimized_loading"] ) ) {
            $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
            $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
            $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
            $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

            $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
        }

        if( homey_option('enable_radius_exp') ) {
            $available_experiences_ids = apply_filters('homey_check_search_availability_filter_exp', $query_args, $arrive);

            $radius_ids = apply_filters('homey_radius_filter_exp', $query_args, $search_lat, $search_lng, $search_radius);

            if(!empty($available_experiences_ids) && !empty($radius_ids)) {
                $query_ids =  array_intersect($available_experiences_ids, $radius_ids);

                if(empty($query_ids)) {
                    $query_ids = array(0);
                }

            } elseif(empty($available_experiences_ids)) {
                $query_ids = $radius_ids;

            } elseif(empty($radius_ids)) {
                $query_ids = $available_experiences_ids;
            }

            if(!empty($query_ids)) {
                $query_args['post__in'] = $query_ids;
            }
        } else {
            $query_args = apply_filters('homey_check_search_availability_filter_exp', $query_args, $arrive);

            if(!empty($search_city) || !empty($search_area)) {
                $_tax_query = Array();

                if(!empty($search_city) && !empty($search_area)) {
                    $_tax_query['relation'] = 'AND';
                }

                if(!empty($search_city)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'experience_city',
                        'field' => 'slug',
                        'terms' => $search_city
                    );
                }

                if(!empty($search_area)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'experience_area',
                        'field' => 'slug',
                        'terms' => $search_area
                    );
                }

                $tax_query[] = $_tax_query;
            }

            if(!empty($search_country)) {
                $tax_query[] = array(
                    'taxonomy' => 'experience_country',
                    'field' => 'slug',
                    'terms' => homey_traverse_comma_string($search_country)
                );
            }

            if(!empty($search_state)) {
                $tax_query[] = array(
                    'taxonomy' => 'experience_state',
                    'field' => 'slug',
                    'terms' => homey_traverse_comma_string($search_state)
                );
            }
        }

        if(!empty($experience_type)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_type',
                'field' => 'slug',
                'terms' => homey_traverse_comma_string($experience_type)
            );
        }

        if(!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_country',
                'field' => 'slug',
                'terms' => $country
            );
        }

        if(!empty($state)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_state',
                'field' => 'slug',
                'terms' => $state
            );
        }

        if(!empty($city)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_city',
                'field' => 'slug',
                'terms' => $city
            );
        }

        if(!empty($area)) {
            $tax_query[] = array(
                'taxonomy' => 'experience_area',
                'field' => 'slug',
                'terms' => $area
            );
        }

        // min and max price logic
        if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any' && isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
            $min_price = doubleval(homey_clean($_POST['min-price']));
            $max_price = doubleval(homey_clean($_POST['max-price']));

            if ($min_price > 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any') {
            $min_price = doubleval(homey_clean($_POST['min-price']));
            if ($min_price > 0) {
                $meta_query[] = array(
                    'key' => array('homey_day_date_price', 'homey_night_price'),
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
            $max_price = doubleval(homey_clean($_POST['max-price']));

            if ($max_price > 0) {
                $meta_query[] = array(
                    'key' => array('homey_day_date_price', 'homey_night_price'),
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }


        if(!empty($guests)) {
            $meta_query[] = array(
                'key' => 'homey_guests',
                'value' => intval($guests),
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }

        if (isset($_POST['area']) && !empty($_POST['area'])) {
            if (is_array($_POST['area'])) {
                $areas = $_POST['area'];

                foreach ($areas as $area):
                    $tax_query[] = array(
                        'taxonomy' => 'experience_area',
                        'field' => 'slug',
                        'terms' => homey_traverse_comma_string($area)
                    );
                endforeach;
            }
        }

        if (isset($_POST['amenity']) && !empty($_POST['amenity'])) {
            $amenities = $_POST['amenity'];
            if(!is_array($amenities)){
                $amenities = array($_POST['amenity']);
            }

            $tax_query[] = array(
                'taxonomy' => 'experience_amenity',
                'field' => 'slug',
                'terms' => $amenities
            );
        }

        if (isset($_POST['facility']) && !empty($_POST['facility'])) {
            $facilities = $_POST['facility'];
            if(!is_array($facilities)){
                $facilities = array($_POST['facility']);
            }

            $tax_query[] = array(
                'taxonomy' => 'experience_facility',
                'field' => 'slug',
                'terms' => $facilities
            );
        }

        if (isset($_POST['language']) && !empty($_POST['language'])) {
            $languages = $_POST['language'];
            if(!is_array($languages)){
                $languages = array($_POST['language']);
            }

            $tax_query[] = array(
                'taxonomy' => 'experience_language',
                'field' => 'slug',
                'terms' => $languages
            );
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
            $query_args['orderby'] = 'date meta_value_num';
            $query_args['meta_key'] = 'homey_featured';
            $query_args['order'] = 'DESC';
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

        if( $meta_count > 0 ){
            $query_args['meta_query'] = $meta_query;
        }

        $tax_count = count( $tax_query );

        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        if( $tax_count > 0 ){
            $query_args['tax_query'] = $tax_query;
        }

        $query_args = new WP_Query( $query_args );

        $experiences = array();
        //to print the query
//        echo $query_args->request;
//        exit;
        ob_start();

        $total_experiences = $query_args->found_posts;

        if($total_experiences > 1) {
            $experience_text = $homey_local['experiences_label'];
        }

        while( $query_args->have_posts() ): $query_args->the_post();

            $experience_id = get_the_ID();
            $guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
            $location = get_post_meta( get_the_ID(), $homey_prefix.'experience_location',true);
            $lat_long = explode(',', $location);
            $experience_price = homey_exp_get_price_by_id($experience_id);
            $experience_type = wp_get_post_terms( get_the_ID(), 'experience_type', array("fields" => "ids") );

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
            $experience->address = '';
            $experience->guests = $guests;

            $experience->arrive = $arrive;

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

                    if( !empty(trim($icon['0'])) ) {
                        $experience->icon = $icon['0'];
                    }
                    if( !empty(trim($retinaIcon['0'])) ) {
                        $experience->retinaIcon = $retinaIcon['0'];
                    }
                }
            }

            array_push($experiences, $experience);

            if($layout == 'card') {
                get_template_part('template-parts/experience/experience', 'card', $template_args );
            }elseif($layout == 'grid-v2' || $layout == 'list-v2') {
                get_template_part('template-parts/experience/experience', 'item-v2', $template_args );
            } else {
                get_template_part('template-parts/experience/experience', 'item', $template_args );
            }

        endwhile;

        wp_reset_postdata();

        homey_pagination_halfmap( $query_args->max_num_pages, $paged, $range = 2 );

        $experiences_html = ob_get_contents();
        ob_end_clean();

        if( count($experiences) > 0 ) {
            echo json_encode( array( 'getExperiences' => true, 'experiences' => $experiences, 'total_results' => $total_experiences.' '.$experience_text, 'experienceHtml' => $experiences_html ) );
            exit();
        } else {
            echo json_encode( array( 'getExperiences' => false, 'total_results' => $total_experiences.' '.$experience_text ) );
            exit();
        }
        die();
    }
}
