<?php
global $post, $homey_prefix, $homey_local;

$what_to_provided = get_post_meta($post->ID, $homey_prefix.'what_to_provided', true);
$what_to_provided_btn = get_post_meta($post->ID, $homey_prefix.'nothing_provided_btn', true);
$what_to_provided_hideShow = '';

if ($what_to_provided_btn > 0) {
    $what_to_provided_btn = 'checked="checked"';
    $what_to_provided_hideShow = 'display:none';
}

$what_to_bring = get_post_meta($post->ID, $homey_prefix.'what_to_bring', true);
$what_to_bring_btn = get_post_meta($post->ID, $homey_prefix.'nothing_bring_btn', true);
$what_to_bring_hideShow = '';

if ($what_to_bring_btn > 0) {
    $what_to_bring_btn = 'checked="checked"';
    $what_to_bring_hideShow = 'display:none';
}
?>
<div id="providing-section" class="providing-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_html__('Providing', 'homey'); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <div class="block-col block-col-100 block-accomodation">
                        <dl class="detail-list">
                            <dt><?php echo esc_html__('The host will provide:', 'homey'); ?></dt>
                            <?php
                            $count = 0;
                            if(!empty($what_to_provided)) {
                            foreach($what_to_provided as $item_provide):
                            $item_provide_name = isset($item_provide['wwbp_name']) ? $item_provide['wwbp_name'] : '';
                            //                    dd($item_provide_desc, 0);
                            ?>
                            <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_html__($item_provide_name, 'homey'); ?> <!--<p><?php echo esc_html__($item_provide_desc, 'homey'); ?></p>--></dd>
                                <?php  $count++;
                            endforeach;
                            } ?>
                        </dl>
                    </div>

                    <div class="spacer clearfix"></div>

                    <div class="block-col block-col-100 block-accomodation">
                        <dl class="detail-list">
                            <dt><?php echo esc_html__('Bring with you:', 'homey'); ?></dt>
                            <?php
                            $count = 0;
                            if(!empty($what_to_bring)) {
                                foreach($what_to_bring as $item_bring):
                                    $item_bring_name = isset($item_bring['wbit_name']) ? $item_bring['wbit_name'] : '';
                                    //                    dd($item_provide_desc, 0);
                                    ?>
                                    <dd><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i> <?php echo esc_html__($item_bring_name, 'homey'); ?></dd>
                                    <?php  $count++;
                                endforeach;
                            } ?>
                        </dl>
                    </div>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
