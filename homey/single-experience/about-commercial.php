<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$mon_fri_final = $sat_final = $sun_final = '';

$mon_fri_open     = homey_get_experience_data('mon_fri_open');
$mon_fri_close     = homey_get_experience_data('mon_fri_close');
$mon_fri_closed     = homey_get_experience_data('mon_fri_closed');
$sat_open     = homey_get_experience_data('sat_open');
$sat_close     = homey_get_experience_data('sat_close');
$sat_closed     = homey_get_experience_data('sat_closed');
$sun_open     = homey_get_experience_data('sun_open');
$sun_close     = homey_get_experience_data('sun_close');
$sun_closed     = homey_get_experience_data('sun_closed');
$bedrooms   = homey_get_experience_data('experience_bedrooms');
$experience_type = homey_taxonomy_simple('experience_type');
$size       = homey_get_experience_data('experience_size');
$size_unit       = homey_get_experience_data('experience_size_unit');
$guests     = homey_get_experience_data('guests');

if($mon_fri_closed == 1) {
    $mon_fri_final = homey_option('experience_sn_closed_label');
} else {
    $mon_fri_final = $mon_fri_open.' - '.$mon_fri_close;
}
if($sat_closed == 1) {
    $sat_final = homey_option('experience_sn_closed_label');
} else {
    $sat_final = $sat_open.' - '.$sat_close;
}
if($sun_closed == 1) {
    $sun_final = homey_option('experience_sn_closed_label');
} else {
    $sun_final = $sun_open.' - '.$sun_close;
}
?>
<div id="details-section" class="details-section">
    <?php if(@$hide_labels['sn_about_experience_title'] != 1) { ?>
    <div class="block">
        <div class="block-body">    
            <h2><?php echo esc_attr(homey_option('sn_about_experience_title')); ?></h2>
            <?php the_content(); ?>
        </div>
    </div><!-- block-body --> 
    <?php } ?> 

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
                    if(!empty($data_value) && @$hide_labels[$value->field_id] != 1) {
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