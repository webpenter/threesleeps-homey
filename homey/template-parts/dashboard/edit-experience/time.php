<?php
global $homey_local, $experience_data, $experience_meta_data;
$hide_fields = homey_option('experience_add_hide_fields');

$ttab = isset($_GET['tab']) ? $_GET['tab'] : '';
$class = '';
if ((isset($_GET['tab']) && $_GET['tab'] == 'time')) {
    $class = 'in active';
}

$openning_hours_list = homey_option('openning_hours_list');
$openning_hours_list_array = explode(',', $openning_hours_list);
?>

<div id="time-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_section_time')); ?></h3>
        <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
    </div>
    <div class="block-body">
        <div class="row">
            <?php if ($hide_fields['experience_section_openning'] != 1) {
                $homey_start_end_open = $experience_meta_data['homey_start_end_open'][0];
                $homey_start_end_close = $experience_meta_data['homey_start_end_close'][0];

                ?>
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
                                            $isSelected = '';
                                            if (trim($homey_start_end_open) == trim($hour)) {
                                                $isSelected = 'selected="selected"';
                                            }
                                            echo '<option ' . $isSelected . ' value="' . trim($hour) . '">' . trim($hour) . '</option>';
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
                                            $isSelected = '';
                                            if (trim($homey_start_end_close) == trim($hour)) {
                                                $isSelected = 'selected="selected"';
                                            }
                                            echo '<option ' . $isSelected . ' value="' . trim($hour) . '">' . trim($hour) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }else{ // End openning Hours ?>
            <div class="block-body">
                <div class="row"><p class="alert alert-info"><?php echo esc_html__("The time tab is hidden from admin homey option, but you added this in layout manager.", "homey"); ?></p></div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
