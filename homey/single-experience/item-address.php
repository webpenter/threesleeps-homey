<?php
$edit_experience_id = isset($args['edit_experience_id']) ? $args['edit_experience_id'] : 0;
$address_composer = homey_option('experience_address_composer');
$enabled_data = isset($address_composer['enabled']) ? $address_composer['enabled'] : 0;
$temp_array = array();

$address_class = isset($args['address_tag_class']) ? $args['address_tag_class'] : 'item-address';

echo '<address class="'.$address_class.'">';

if ($enabled_data) {
    unset($enabled_data['placebo']);
    foreach ($enabled_data as $key=>$value) {

        if( $key == 'address' ) {
            $experience_address = homey_get_experience_data('experience_address', $edit_experience_id);
            if(!empty($experience_address))
                $temp_array[] = $experience_address;
        }

        if( $key == 'streat-address' ) {
            $experience_address = homey_get_experience_data('experience_address', $edit_experience_id);
            if(!empty($experience_address))
                $temp_array[] = $experience_address;
        }

        if( $key == 'country' ) {
            $experience_country = homey_taxonomy_simple('experience_country', $edit_experience_id);
            if(!empty($experience_country))
                $temp_array[] = $experience_country;
        }

        if( $key == 'state' ) {
            $experience_state = homey_taxonomy_simple('experience_state', $edit_experience_id);
            if(!empty($experience_state))
                $temp_array[] = $experience_state;
        }

        if( $key == 'city' ) {
            $experience_city = homey_taxonomy_simple('experience_city', $edit_experience_id);
            if (!empty($experience_city))
                $temp_array[] = $experience_city;
        }

        if( $key == 'area' ) {
            $experience_area = homey_taxonomy_simple('experience_area', $edit_experience_id);
            if (!empty($experience_area))
                $temp_array[] = $experience_area;
        }

    }

    $prefix_address  = isset($args['prefix_address'])  ? $args['prefix_address'] : '';
    $postfix_address = isset($args['postfix_address']) ? $args['postfix_address'] : '';

    $result = join( ", ", $temp_array );
    echo $prefix_address . $result . $postfix_address;
}

echo '</address>';