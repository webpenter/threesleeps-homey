<?php
global $post, $homey_prefix, $homey_local;
$virtual_tour_embed = homey_get_listing_data('virtual_tour');

if(!empty($virtual_tour_embed)) {
?>
<div id="virtual_tour-section" class="virtual_tour-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_virtual_tour_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <div class="block-virtual_tour">
                        <?php echo $virtual_tour_embed; ?>
                    </div>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
<?php } ?>