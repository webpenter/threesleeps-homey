<?php
global $post, $homey_prefix, $homey_local, $hide_labels, $listing_author;
$is_superhost = $listing_author['is_superhost'];

$guests     = homey_get_listing_data('guests');

$allow_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'allow_additional_guests', true );
$num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );

if( $allow_additional_guests == 'yes' && ! empty( $num_additional_guests ) ) {
    $guests = (int) $guests + (int) $num_additional_guests;
}

$num_additional_guests = homey_get_field_meta('num_additional_guests');

$bedrooms   = homey_get_listing_data('listing_bedrooms');
$beds       = homey_get_listing_data('beds');
$bathrooms      = homey_get_listing_data('baths');
$room_type  = homey_taxonomy_simple('room_type');
$listing_type = homey_taxonomy_simple('listing_type');

$full_bath = $half_bath = $type_icon = $acco_icon = $bedroom_icon = $bathroom_icon = '';
if($bathrooms != '' && $bathrooms != '0') {
    $baths = explode('.', $bathrooms);
    $full_bath = $baths[0].' '.homey_option('sn_fullbath_label'); 
    if(!empty($baths[1]) && $baths[1] == '5') {
        $half_bath = '1'.' '.homey_option('sn_halfbath_label');
    }
} else {
    $full_bath = $bathrooms;
}

$slash = '';
if(!empty($room_type) && !empty($listing_type)) {
    $slash = '/';
}
$icon_type = homey_option('detail_icon_type');

$type_icon = '<i class="homey-icon homey-icon-house-2"></i>';
$acco_icon = '<i class="homey-icon homey-icon-multiple-man-woman-2"></i>';
$bedroom_icon = '<i class="homey-icon homey-icon-hotel-double-bed"></i>';
$bathroom_icon = '<i class="homey-icon homey-icon-bathroom-shower-1"></i>';

if($icon_type == 'fontawesome_icon') {
    $type_icon = '<i class="'.esc_attr(homey_option('de_type_icon')).'"></i>';
    $acco_icon = '<i class="'.esc_attr(homey_option('de_acco_icon')).'"></i>';
    $bedroom_icon = '<i class="'.esc_attr(homey_option('de_bedroom_icon')).'"></i>';
    $bathroom_icon = '<i class="'.esc_attr(homey_option('de_bathroom_icon')).'"></i>';

} elseif($icon_type == 'custom_icon') {
    $type_icon = '<img src="'.esc_url(homey_option( 'de_cus_type_icon', false, 'url' )).'" alt="'.esc_attr__('type_icon', 'homey').'">';
    $acco_icon = '<img src="'.esc_url(homey_option( 'de_cus_acco_icon', false, 'url' )).'" alt="'.esc_attr__('acco_icon', 'homey').'">';
    $bedroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bedroom_icon', false, 'url' )).'" alt="'.esc_attr__('bedroom_icon', 'homey').'">';
    $bathroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bathroom_icon', false, 'url' )).'" alt="'.esc_attr__('bathroom_icon', 'homey').'">';
}
?>
<div id="about-section" class="about-section-v5 about-v2">
    <div class="block block-v5">
        <div class="block-body-v5">
            <h2 class="title-v5"><?php echo esc_attr($room_type); echo ' '; echo esc_html__("Hosted by", "homey"); echo ' '; ?> <?php echo $listing_author['name']; ?></h2>
            <?php $withText = ' '; $withText .= esc_html__("with", "homey"); $withText .= ' '; ?>
            <div class="property-accomodation-detals-v5"><?php echo esc_attr($guests); echo ' '.esc_attr(homey_option('sn_guests_label'));?> <span>•</span> <?php echo esc_attr($beds); echo ' '.esc_attr(homey_option('sn_beds_label'));  echo $withText; echo esc_attr($bedrooms); echo ' '.esc_attr(homey_option('sn_bedrooms_label'));?> <span>•</span> <?php echo esc_attr($full_bath); if($half_bath != ''){echo esc_attr($half_bath);}?></div>
            <div class="host-avatar-wrap avatar">
                <?php if($is_superhost) { ?>
                <span class="super-host-icon">
                    <i class="homey-icon homey-icon-award-badge-1"></i>
                </span>
                <?php } ?>
                <?php echo ''.$listing_author['photo']; ?>
                <!--<img src="img/70x70.png" class="img-circle media-object " alt="Image" width="70" height="70">-->
            </div>
        </div>
    </div><!-- block-v5 -->
    <div class="block block-v5">
        <div class="block-body-v5">
            <h2><?php echo esc_attr(homey_option('sn_about_listing_title')); ?></h2>
            <p><?php  the_content(); ?></p>
        </div>
    </div><!-- block-body -->

    <?php
    //Custom Fields
    if(class_exists('Homey_Fields_Builder')) {
        $fields_array = Homey_Fields_Builder::get_form_fields();

        if(!empty($fields_array)) {
            foreach ( $fields_array as $value ) {
                $data_value = get_post_meta( get_the_ID(), 'homey_'.$value->field_id, true );
                $field_title = $value->label;
                $field_type = $value->type;

                $field_title = homey_wpml_translate_single_string($field_title);
                $data_value = homey_wpml_translate_single_string($data_value);

                if($field_type == 'textarea') {
                    if(!empty($data_value) && $hide_labels[$value->field_id] != 1) {
                        echo '
                        <div class="block">
                            <div class="block-body">
                                <h2>'.esc_attr($field_title).'</h2>
                                '.$data_value.'
                            </div>
                        </div>
                        ';
                    }
                }
            }
        }
    }
    ?>
</div>