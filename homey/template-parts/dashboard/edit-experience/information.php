<?php
global $homey_local, $experience_data, $experience_meta_data;
$hide_fields = homey_option('experience_add_hide_fields');
$experience_show_hide_labels = homey_option('experience_show_hide_labels');

$ttab = isset($_GET['tab']) ? $_GET['tab'] : '';
$class = '';
if ((isset($_GET['tab']) && $_GET['tab'] == 'information') || ($ttab == '')) {
    $class = 'in active';
}

$openning_hours_list = homey_option('openning_hours_list');
$openning_hours_list_array = explode(',', $openning_hours_list);
?>

<div id="information-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('experience_ad_section_info')); ?></h3>
        <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
    </div>
    <div class="block-body">
        <div class="row">
            <?php if ($hide_fields['experience_title'] != 1) { ?>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="experience_title"><?php echo esc_attr(homey_option('experience_ad_title')) . homey_req('experience_title'); ?></label>
                        <input type="text"
                               data-input-title="<?php echo esc_html__(esc_attr(homey_option('experience_ad_title')), 'homey'); ?>"
                               name="experience_title" id="experience_title"
                               value="<?php print sanitize_text_field($experience_data->post_title); ?>"
                               class="form-control" <?php homey_required('experience_title'); ?>
                               placeholder="<?php echo esc_attr(homey_option('experience_ad_title_plac')); ?>">
                    </div>
                </div>
            <?php } ?>

            <?php if ($hide_fields['experience_type'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="experience_type"> <?php echo esc_attr(homey_option('experience_ad_experience_type')) . homey_req('experience_type'); ?> </label>
                        <select name="experience_type" class="selectpicker"
                                id="experience_type" <?php homey_required('experience_type'); ?>
                                data-live-search="false" data-live-search-style="begins">
                            <?php homey_get_taxonomies_for_edit_experience($experience_data->ID, 'experience_type'); ?>
                        </select>
                    </div>
                </div>
            <?php } ?>

            <?php if ($hide_fields['experience_language'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="experience_language"> <?php echo esc_attr(homey_option('experience_language')) . homey_req('experience_language'); ?> </label>
                        <select name="experience_language[]" class="selectpicker" multiple
                                title="<?php echo esc_attr(homey_option('experience_language')); ?>"
                                id="experience_language" <?php homey_required('experience_language'); ?>
                                data-live-search="false" data-live-search-style="begins">
                            <?php //homey_get_taxonomies_for_edit_experience($experience_data->ID, 'experience_language'); ?>
                            <?php homey_get_taxonomies_options($experience_data->ID, 'experience_language'); ?>
                        </select>
                    </div>
                </div>
            <?php } ?>

            <?php if ($hide_fields['experience_describe_yourself'] != 1) { ?>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="experience_describe_yourself"><?php echo esc_attr(homey_option('experience_describe_yourself')); ?></label>
                        <?php
                        // default settings - Kv_front_editor.php
                        $content = isset($experience_meta_data['homey_experience_describe_yourself'][0]) ? $experience_meta_data['homey_experience_describe_yourself'][0] : '';

                        $editor_id = 'experience_describe_yourself';
                        $settings = array(
                            'wpautop' => true, // use wpautop?
                            'media_buttons' => false, // show insert/upload button(s)
                            'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                            'textarea_rows' => '10', // rows="..."
                            'tabindex' => '',
                            'editor_css' => '', //  extra styles for both visual and HTML editors buttons,
                            'editor_class' => '', // add extra class(es) to the editor textarea
                            'teeny' => false, // output the minimal editor config used in Press This
                            'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                            'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                        );
                        wp_editor($content, $editor_id, $settings); ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ($hide_fields['experience_description'] != 1) { ?>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="experience_description"><?php echo esc_attr(homey_option('experience_ad_des')); ?></label>
                        <?php
                        // default settings - Kv_front_editor.php
//                        $content = sanitize_text_field($experience_data->post_content);
                        $content = $experience_data->post_content;
                        $editor_id = 'experience_description';
                        $settings = array(
                            'wpautop' => true, // use wpautop?
                            'media_buttons' => false, // show insert/upload button(s)
                            'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                            'textarea_rows' => '10', // rows="..."
                            'tabindex' => '',
                            'editor_css' => '', //  extra styles for both visual and HTML editors buttons,
                            'editor_class' => '', // add extra class(es) to the editor textarea
                            'teeny' => false, // output the minimal editor config used in Press This
                            'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                            'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                        );
                        wp_editor($content, $editor_id, $settings); ?>
                    </div>
                </div>
            <?php } ?>

            <?php
            if (class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();

                if (!empty($fields_array)) {
                    foreach ($fields_array as $value) {
                        $field_title = $value->label;
                        $field_name = $value->field_id;
                        $field_type = $value->type;
                        $field_placeholder = $value->placeholder;

                        $field_title = homey_wpml_translate_single_string($field_title);

                        $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                        if ($field_type == 'textarea') {

                            if ($hide_fields[$field_name] != 1) {

                                echo '<div class="col-sm-12">';
                                echo '<div class="form-group">';
                                echo '<label for="' . $field_name . '">' . $field_title . homey_req($field_name) . '</label>';
                                // default settings - Kv_front_editor.php
                                $content1 = isset($experience_meta_data['homey_' . $field_name][0]) ? $experience_meta_data['homey_' . $field_name][0] : '';
                                $editor_id1 = $field_name;
                                $settings1 = array(
                                    'wpautop' => true, // use wpautop?
                                    'media_buttons' => false, // show insert/upload button(s)
                                    'textarea_name' => $editor_id1, // set the textarea name to something different, square brackets [] can be used here
                                    'textarea_rows' => '5', // rows="..."
                                    'tabindex' => '',
                                    'editor_css' => '', //  extra styles for both visual and HTML editors buttons,
                                    'editor_class' => '', // add extra class(es) to the editor textarea
                                    'teeny' => false, // output the minimal editor config used in Press This
                                    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                    'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                );
                                wp_editor($content1, $editor_id1, $settings1);
                                echo '</div>';
                                echo '</div>';
                            }

                        }
                    }
                }
            }
            ?>

        </div>
        <div class="row">

            <?php if ($hide_fields['experience_guests'] != 1) { ?>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="experience_guests"> <?php echo esc_attr(homey_option('experience_ad_no_of_guests')) . homey_req('experience_guests'); ?> </label>
                        <input type="number"
                               min="1"
                               data-input-title="<?php echo esc_html__(esc_attr(homey_option('experience_ad_no_of_guests')), 'homey'); ?>"
                               name="guests"
                               value="<?php echo isset($experience_meta_data['homey_guests'][0]) ? sanitize_text_field($experience_meta_data['homey_guests'][0]) : ''; ?>"
                               id="guests" <?php homey_required('experience_guests'); ?>
                               class="form-control"
                               placeholder="<?php echo esc_attr(homey_option('experience_ad_no_of_guests_plac')); ?>">
                    </div>
                </div>
            <?php } ?>

            <?php if (1 == 2 && @$hide_fields['experience_affiliate_booking_link'] != 1) {
                echo '<pre>';
                print_r($experience_meta_data) ?>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="experience_affiliate_booking_link"> <?php echo esc_attr(homey_option('experience_ad_affiliate_booking_link', 'Experience Affiliate Booking Link')); ?> </label>
                        <input type="text" class="form-control"
                               name="experience_affiliate_booking_link"
                               value="<?php echo $experience_meta_data['experience_affiliate_booking_link'][0] ?>"
                               id="experience_affiliate_booking_link"
                               placeholder="<?php echo esc_attr(homey_option('experience_ad_affiliate_booking_link_plac', 'Enter Experience Affiliate Booking Link')); ?>">
                    </div>
                </div>
            <?php } ?>

            <?php
            if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();

                if(!empty($fields_array)) {
                    foreach ( $fields_array as $value ) {
                        $field_title = $value->label;
                        $field_name = $value->field_id;
                        $field_type = $value->type;
                        $field_placeholder = $value->placeholder;

                        $field_title = homey_wpml_translate_single_string($field_title);
                        $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                        if($field_type == 'select') {

                            $options = unserialize($value->fvalues);
                            $options_option = '';
                            foreach ($options as $key => $val) {

                                $val = homey_wpml_translate_single_string($val);
                                if(isset($experience_meta_data['homey_'.$field_name][0])){
                                    $options_option .= '<option '.selected( $experience_meta_data['homey_'.$field_name][0], $key, false ).' value="'.esc_attr($key).'">'.esc_attr($val).'</option>';
                                }
                            }

                            if($hide_fields[$field_name] != 1) {
                                echo '<div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="'.esc_attr($field_name).'">'.esc_attr($field_title).homey_req($field_name).'</label>
                                        <select '.homey_get_required($field_name).' name="'.esc_attr($field_name).'" class="selectpicker" data-live-search="true" data-live-search-style="begins">
                                                <option selected="selected" value="-1">'.esc_html__('None', 'homey').'</option>
                                            '.$options_option.'
                                        </select>
                                    </div>
                                </div>';
                            }

                        } elseif($field_type == 'text') {

                            $input_field = '<input '.homey_get_required($field_name).' class="form-control" name="'.esc_attr($field_name).'"';

                            if( isset( $experience_meta_data['homey_'.$field_name] ) ) {
                                $input_field .= 'value="'.sanitize_text_field( $experience_meta_data['homey_'.$field_name][0] ).'"';
                            }
                            $input_field .= 'placeholder="'.$field_placeholder.'">';

                            if($hide_fields[$field_name] != 1) {
                                echo '<div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>
                                        '.$input_field.'
                                    </div>
                                </div>';
                            }
                        }
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
