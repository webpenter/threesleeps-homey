<?php
global $post, $hide_fields, $homey_local;
$layout_order = homey_option('experience_form_sections');
$layout_order = $layout_order['enabled'];
$i = 0;
$style = 'visibility: hidden; height: 0;';

$openning_hours_list = homey_option('openning_hours_list');
$openning_hours_list_array = explode(',', $openning_hours_list);
?>

<div class="form-step">
    <div class="block-title visible-xs">
        <h3 class="title">Time<?php echo esc_attr(homey_option('ad_section_time')); ?></h3>
        <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
    </div>
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('experience_ad_section_openning')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="experience_start_end_open"><?php echo esc_html__('Start', 'homey'); ?></label>
                        <select id="experience_start_end_open" name="start_end_open" class="selectpicker"
                                data-live-search="false" data-live-search-style="begins"
                                title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                            <?php
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option value="' . trim($hour) . '">' . trim($hour) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="experience_start_end_close"><?php echo esc_html__('End', 'homey'); ?></label>
                        <select id="experience_start_end_close" name="start_end_close" class="selectpicker"
                                data-live-search="false" data-live-search-style="begins"
                                title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                            <?php
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option value="' . trim($hour) . '">' . trim($hour) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
