<?php
global $homey_local;
$hide_fields = homey_option('experience_add_hide_fields');
$openning_hours_list = homey_option('experience_openning_hours_list');
$openning_hours_list_array = explode(',', $openning_hours_list);
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html__(homey_option('experience_ad_section_info')); ?></h2>
                <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">
                <?php if (isset($hide_fields['experience_title']) && $hide_fields['experience_title'] != 1) { ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="experience_title"><?php echo esc_attr(homey_option('experience_ad_title')) . homey_req('experience_title'); ?></label>
                            <input type="text"
                                   data-input-title="<?php echo esc_html__(esc_attr(homey_option('experience_ad_title')), 'homey'); ?>"
                                   name="experience_title" id="experience_title"
                                   class="form-control" <?php homey_required('experience_title'); ?>
                                   placeholder="<?php echo esc_attr(homey_option('experience_ad_title_plac')); ?>">
                        </div>
                    </div>
                <?php } ?>

                <?php if (isset($hide_fields['experience_type']) && $hide_fields['experience_type'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="experience_type"> <?php echo esc_attr(homey_option('experience_ad_experience_type')) . homey_req('experience_type'); ?> </label>
                            <select name="experience_type" class="selectpicker"
                                    id="experience_type" <?php homey_required('experience_type'); ?>
                                    data-live-search="false" data-live-search-style="begins">
                                <option selected="selected"
                                        value=""><?php echo esc_attr(homey_option('experience_ad_experience_type_plac')); ?></option>
                                <?php
                                $experience_type = get_terms(
                                    array(
                                        "experience_type"
                                    ),
                                    array(
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'hide_empty' => false,
                                        'parent' => 0
                                    )
                                );

                                homey_get_taxonomies_with_id_value('experience_type', $experience_type, -1);
                                ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>

                <?php if (isset($hide_fields['experience_language']) && $hide_fields['experience_language'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="experience_language"> <?php echo esc_attr(homey_option('experience_language')) . homey_req('experience_language'); ?> </label>
                            <select name="experience_language[]" class="selectpicker" multiple
                                    title="<?php echo esc_attr(homey_option('experience_language')); ?>"
                                    id="experience_language" <?php homey_required('experience_language'); ?>
                                    data-live-search="false" data-live-search-style="begins" placeholder="<?php echo esc_attr(homey_option('experience_language_plac')); ?>">

<!--                                <option value="">--><?php //echo esc_attr(homey_option('experience_language_plac')); ?><!--</option>-->
                                <?php
                                $experience_language = get_terms(
                                    array(
                                        "experience_language"
                                    ),
                                    array(
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'hide_empty' => false,
                                        'parent' => 0
                                    )
                                );

                                homey_get_taxonomies_with_id_value('experience_language', $experience_language, -1);
                                ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>

                <?php if (isset($hide_fields['experience_describe_yourself']) && $hide_fields['experience_describe_yourself'] != 1) { ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="experience_describe_yourself"><?php echo esc_attr(homey_option('experience_describe_yourself')); ?></label>
                            <?php
                            // default settings - Kv_front_editor.php
                            $content = '';
                            $editor_id = 'experience_describe_yourself';
                            $settings = array(
                                'wpautop' => true, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '5', // rows="..."
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

                <?php if (isset($hide_fields['experience_description']) && $hide_fields['experience_description'] != 1) { ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="experience_description"><?php echo esc_attr(homey_option('experience_ad_des')); ?></label>
                            <?php
                            // default settings - Kv_front_editor.php
                            $content = '';
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

                                if (isset($hide_fields[$field_name]) && $hide_fields[$field_name] != 1) {

                                    echo '<div class="col-sm-12">';
                                    echo '<div class="form-group">';
                                    echo '<label for="experience_' . $field_name . '">' . $field_title . homey_req($field_name) . '</label>';
                                    // default settings - Kv_front_editor.php
                                    $content1 = '';
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

                <?php if (isset($hide_fields['experience_guests']) && $hide_fields['experience_guests'] != 1) { ?>
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="experience_guests"> <?php echo esc_attr(homey_option('experience_ad_no_of_guests')) . homey_req('experience_guests'); ?> </label>
                            <input type="number"
                                   min="1"
                                   data-input-title="<?php echo esc_html__(esc_attr(homey_option('experience_ad_no_of_guests')), 'homey'); ?>"
                                   name="guests" id="guests" <?php homey_required('experience_guests'); ?>
                                   class="form-control"
                                   placeholder="<?php echo esc_attr(homey_option('experience_ad_no_of_guests_plac')); ?>">
                        </div>
                    </div>
                <?php } ?>

                <?php if (1 == 2 && @$hide_fields['experience_affiliate_booking_link'] != 1) { ?>
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="experience_affiliate_booking_link"> <?php echo esc_attr(homey_option('experience_ad_affiliate_booking_link', 'Experience Affiliate Booking Link')); ?> </label>
                            <input type="text" class="form-control" name="experience_affiliate_booking_link"
                                   id="experience_affiliate_booking_link"
                                   placeholder="<?php echo esc_attr(homey_option('experience_ad_affiliate_booking_link_plac', 'Enter Experience Affiliate Booking Link')); ?>">
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

                            if ($field_type == 'select') {

                                $options = unserialize($value->fvalues);
                                $options_option = '';
                                foreach ($options as $key => $val) {
                                    $val = homey_wpml_translate_single_string($val);
                                    $options_option .= '<option value="' . $key . '">' . $val . '</option>';
                                }

                                if (isset($hide_fields[$field_name]) && $hide_fields[$field_name] != 1) {
                                    echo '<div class="col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="experience_' . $field_name . '">' . $field_title . homey_req($field_name) . '</label>
                                            <select ' . homey_get_required($field_name) . ' name="' . $field_name . '" class="selectpicker" data-live-search="true" data-live-search-style="begins">
                                                <option selected="selected" value="">' . esc_html__('None', 'homey') . '</option>
                                                ' . $options_option . '
                                            </select>
                                        </div>
                                    </div>';
                                }

                            } elseif ($field_type == 'text') {

                                if (isset($hide_fields[$field_name]) && $hide_fields[$field_name] != 1) {
                                    echo '<div class="col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="experience_' . $field_name . '">' . $field_title . homey_req($field_name) . '</label>
                                            <input ' . homey_get_required($field_name) . ' class="form-control" name="' . $field_name . '" placeholder="' . $field_placeholder . '">
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
</div>
