<?php
global $post, $homey_prefix, $homey_local;

$hide_labels = homey_option('experience_show_hide_labels');
$experience_describe_yourself = get_post_meta( get_the_ID(), $homey_prefix.'experience_describe_yourself', true );
?>
<div id="about-host-section" class="about-host-section">

    <?php if($hide_labels['experience_sn_about_host_title'] != 1) { ?>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h2><?php echo esc_attr(homey_option('experience_sn_about_host_title')); ?></h2>
                </div>
                <div class="block-right">
                    <?php echo $experience_describe_yourself; ?>
                </div>
            </div>
        </div>

    </div><!-- block-body -->   
    <?php } ?> 
</div>
