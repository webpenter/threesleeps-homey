<?php
global $homey_prefix, $homey_local, $listing_data;
$virtual_tour = get_post_meta($listing_data->ID, $homey_prefix.'virtual_tour', true);
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'virtual_tour') {
    $class = 'in active';
}
?>
<!--step 360 virtual-tour-->
<div id="virtual_tour-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_virtual_tour')); ?></h3>
    </div>
    <div class="block-body">
        <div id="virtual-tour" class="virtual-tour-wrap">
            <div class="virtual-tour-block">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="service_name"><?php echo esc_html__('Enter virtual tour embed code.', 'homey'); ?></label>
                            <textarea class="form-control" name="virtual_tour" rows="7"
                                      placeholder="<?php echo homey_option('cl_virtual_plac', 'Enter virtual tour iframe/embeded code'); ?>"><?php echo $virtual_tour; ?></textarea>
                        </div>
                    </div>
                </div>
            </div><!-- dashboard-content-block -->
        </div><!-- dashboard-content-block-wrap -->
    </div>
</div>
