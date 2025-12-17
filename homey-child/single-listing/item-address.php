<?php
$edit_listing_id = isset($args['edit_listing_id']) ? $args['edit_listing_id'] : 0;
$address_composer = homey_option('listing_address_composer');
$enabled_data = isset($address_composer['enabled']) ? $address_composer['enabled'] : 0;
$temp_array = array();

$address_class = isset($args['address_tag_class']) ? $args['address_tag_class'] : 'item-address';

echo '<address class="'.$address_class.'">';

if ($enabled_data) {
	unset($enabled_data['placebo']);
	foreach ($enabled_data as $key=>$value) {

		if( $key == 'address' ) {
            $listing_address = homey_get_listing_data('listing_address', $edit_listing_id);
            if(!empty($listing_address))
			    $temp_array[] = $listing_address;
		}

		if( $key == 'streat-address' ) {
			$listing_address = homey_get_listing_data('listing_address', $edit_listing_id);
            if(!empty($listing_address))
			    $temp_array[] = $listing_address;
		}

		if( $key == 'country' ) {
			$listing_country = homey_taxonomy_simple('listing_country');
            if(!empty($listing_country))
			    $temp_array[] = $listing_country;
		}

		if( $key == 'state' ) {
			$listing_state = homey_taxonomy_simple('listing_state');
            if(!empty($listing_state))
			    $temp_array[] = $listing_state;
		}

		if( $key == 'city' ) {
			$listing_city = homey_taxonomy_simple('listing_city');
            if (!empty($listing_city))
			    $temp_array[] = $listing_city;
		}

		if( $key == 'area' ) {
			$listing_area = homey_taxonomy_simple('listing_area');
            if (!empty($listing_area))
			    $temp_array[] = $listing_area;
		}

	}

    $prefix_address  = isset($args['prefix_address'])  ? $args['prefix_address'] : '';
    $postfix_address = isset($args['postfix_address']) ? $args['postfix_address'] : '';

    $result = join( ", ", $temp_array );
    echo $prefix_address . $result . $postfix_address;
}

echo '</address>';