<?php
global $homey_local, $hide_fields;
?>
<div class="form-step">
    <!--step 360 virtual-tour-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo homey_option('ad_virtual_tour', '360° Virtual Tour'); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div id="virtual-tour" class="virtual-tour-wrap">
                <div class="virtual-tour-block">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <textarea class="form-control" name="virtual_tour" rows="7"
                                          placeholder="<?php echo homey_option('cl_virtual_plac', 'Enter virtual tour iframe/embeded code'); ?>"></textarea>
                            </div>
                        </div>
                    </div>
                </div><!-- dashboard-content-block -->
            </div><!-- dashboard-content-block-wrap -->
        </div>
    </div>
</div>
