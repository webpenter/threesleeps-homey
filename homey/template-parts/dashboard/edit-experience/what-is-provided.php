<?php
global $homey_local, $experience_data, $homey_prefix, $experience_meta_data;
$hide_fields = homey_option('experience_add_hide_fields');

$what_to_provided = get_post_meta($experience_data->ID, $homey_prefix.'what_to_provided', true);
$what_to_provided_btn = get_post_meta($experience_data->ID, $homey_prefix.'nothing_provided_btn', true);
$what_to_provided_hideShow = '';

if ($what_to_provided_btn > 0) {
    $what_to_provided_btn = 'checked="checked"';
    $what_to_provided_hideShow = 'display:none';
}

$what_to_bring = get_post_meta($experience_data->ID, $homey_prefix.'what_to_bring', true);
$what_to_bring_btn = get_post_meta($experience_data->ID, $homey_prefix.'nothing_bring_btn', true);
$what_to_bring_hideShow = '';

if ($what_to_bring_btn > 0) {
    $what_to_bring_btn = 'checked="checked"';
    $what_to_bring_hideShow = 'display:none';
}

$ttab = isset($_GET['tab']) ? $_GET['tab'] : '';
$class = '';
if ((isset($_GET['tab']) && $_GET['tab'] == 'what_provided') ) {
    $class = 'in active';
}

?>

<div id="what-is-provided-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
<!-- what will provide-->
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__("I will provide", "homey"); ?></h2>
        </div><!-- block-left -->
        <div class="block-right">
            <label class="control control--checkbox margin-0">
                <input <?php echo $what_to_provided_btn; ?> type="checkbox" name="nothing_provided_btn" class="nothing_provided_btn" id="nothing_provided_btn">
                <span class="contro-text"><?php echo esc_html__("Nothing to provide", "homey"); ?></span>
                <span class="control__indicator"></span>
            </label>
        </div><!-- block-right -->
    </div>
</div>

<div style="<?php echo $what_to_provided_hideShow; ?>" class="what_is_provided">
        <?php
        $count = 0;
        if(!empty($what_to_provided)) {
            foreach($what_to_provided as $item_provide):
                $item_provide_name = isset($item_provide['wwbp_name']) ? $item_provide['wwbp_name'] : '';
//                    dd($item_provide_desc, 0);
                ?>
                <div class="more_what_to_provided_wrap  block-body" id="more_what_to_provided_wrap">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="what_to_bring_name<?php echo $count; ?>"><?php echo esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name')), 'homey'); //. homey_req('experience_what_provides'); ?> </label>
                                <input value="<?php echo $item_provide_name; ?>" id="what_to_bring_name<?php echo $count; ?>" type="text" name="what_to_provided[<?php echo $count; ?>][wwbp_name]" class="form-control" placeholder="<?php echo esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name_plac')), 'homey');?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <button type="button" data-remove="<?php echo $count; ?>" class="remove-what-to-provided btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
                        </div>
                    </div>
                </div>
                <?php  $count++;
            endforeach;
        } ?>
    </div>

<div style="<?php echo $what_to_provided_hideShow; ?>" class="block what_is_provided">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__("Add an item", "homey"); ?></h2>
        </div><!-- block-left -->
    </div>
    <div class="block-body">
        <div class="more_what_to_provided_main" id="more_what_to_provided_main">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="experience_ad_what_provides_text"><?php echo esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name')), 'homey'); //. homey_req('experience_what_provides'); ?> </label>
                        <input id="what_to_bring_name<?php echo $count+1; ?>" type="text" name="what_to_provided[<?php echo $count+1; ?>][wwbp_name]" class="form-control" placeholder="<?php echo esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name_plac')), 'homey');?>" />
                    </div>
                </div>
<!--                <div class="col-sm-12">-->
<!--                    <div class="form-group">-->
<!--                        <label>--><?php //echo esc_html__("Description", "homey"); ?><!--</label>-->
<!--                        <textarea name="what_to_provided[--><?php //echo $count+1; ?><!--][wwbp_desc]" class="form-control" rows="3" placeholder="--><?php //echo esc_html__("Type description here.", "homey"); ?><!--"></textarea>-->
<!--                    </div>-->
<!--                </div>-->
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 text-right">
                <button type="button" id="add_more_what_will_provided" data-increment="0" class="btn btn-primary btn-slim"><i class="homey-icon homey-icon-add"></i> <?php echo esc_html__('Add More', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- what to bring-->
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__("Guests have to bring with them", "homey"); ?></h2>
        </div><!-- block-left -->
        <div class="block-right">
            <label class="control control--checkbox margin-0">
                <input type="checkbox" <?php echo $what_to_bring_btn; ?> name="nothing_bring_btn" class="nothing_bring_btn" id="nothing_bring_btn">
                <span class="contro-text"><?php echo esc_html__("Nothing to bring", "homey"); ?></span>
                <span class="control__indicator"></span>
            </label>
        </div><!-- block-right -->
    </div>

    <div style="<?php echo $what_to_bring_hideShow; ?>" class="what_to_bring">
        <?php
        $count = 0;
        if(!empty($what_to_bring)) {
            foreach($what_to_bring as $item_bring):

                $item_bring_name = isset($item_bring['wbit_name']) ? $item_bring['wbit_name'] : '';
                ?>
                <div class="more_what_to_bring_wrap  block-body" id="more_what_to_bring_wrap">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="what_to_bring_name<?php echo $count; ?>"><?php echo esc_html__(esc_attr(homey_option('experience_what_bring_name')), 'homey'); //. homey_req('experience_what_brings'); ?> </label>
                                <textarea id="what_to_bring_name<?php echo $count; ?>" type="text" name="what_to_bring[<?php echo $count; ?>][wbit_name]" class="form-control" placeholder="<?php echo esc_html__(esc_attr(homey_option('experience_what_bring_name_plac')), 'homey');?>" ><?php echo $item_bring_name; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <button type="button" data-remove="<?php echo $count; ?>" class="remove-what-to-bring btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
                        </div>
                    </div>
                </div>
                <?php  $count++;
            endforeach;
        } ?>
    </div>
</div>

<div style="<?php echo $what_to_bring_hideShow; ?>" class="block what_to_bring">
    <div class="block-body">
        <h2 class="title mb-30"><?php echo esc_html__("Add an item", "homey"); ?></h2>

        <div class="more_what_to_bring_main" id="more_what_to_bring_main">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="what_to_bring_name<?php echo $count+1; ?>"><?php echo esc_html__(esc_attr(homey_option('experience_what_bring_name')), 'homey'); ?> </label>
                        <textarea id="what_to_bring_name<?php echo $count+1; ?>" type="text" name="what_to_bring[<?php echo $count+1; ?>][wbit_name]" class="form-control" placeholder="<?php echo esc_html__(esc_attr(homey_option('experience_what_bring_name_plac')), 'homey');?>" ></textarea>
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 text-right">
                <button type="button" id="add_more_what_will_bring" data-increment="0" class="btn btn-primary btn-slim"><i class="homey-icon homey-icon-add"></i> <?php echo esc_html__('Add More', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div>
</div>
