<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$smoke            = homey_get_listing_data('smoke');
$pets             = homey_get_listing_data('pets');
$party            = homey_get_listing_data('party');
$children         = homey_get_listing_data('children');
$additional_rules = homey_get_listing_data('additional_rules');

$cancellation_policy = homey_get_listing_data('cancellation_policy');
if(!empty($cancellation_policy)){
    $cancellation_policy   = get_the_content( '', '',  $cancellation_policy ); // Where $cancellation_policy is the ID
}else{
    $cancellation_policy = '';
}

if($smoke != 1) {
    $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
    $smoke_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
    $smoke_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if($pets != 1) {
    $pets_allow = 'homey-icon homey-icon-arrow-right-1';
    $pets_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $pets_allow = 'homey-icon homey-icon-arrow-right-1';
    $pets_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if($party != 1) {
    $party_allow = 'homey-icon homey-icon-arrow-right-1';
    $party_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $party_allow = 'homey-icon homey-icon-arrow-right-1';
    $party_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}

if($children != 1) {
    $children_allow = 'homey-icon homey-icon-arrow-right-1';
    $children_text = esc_html__(homey_option('sn_text_no'), 'homey');
} else {
    $children_allow = 'homey-icon homey-icon-arrow-right-1';
    $children_text = esc_html__(homey_option('sn_text_yes'), 'homey');
}
?>
<div id="rules-section" class="rules-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_terms_rules')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="rules_list detail-list">
                        <?php if(@$hide_labels['sn_smoking_allowed'] != 1) { ?>
                        <li>
                            <i class="<?php echo esc_attr($smoke_allow); ?>" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_smoking_allowed')); ?>:
                            <strong><?php echo esc_attr($smoke_text); ?></strong>
                        </li> 
                        <?php } ?>

                        <?php if(@$hide_labels['sn_pets_allowed'] != 1) { ?>
                        <li>
                            <i class="<?php echo esc_attr($pets_allow); ?>" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_pets_allowed')); ?>:
                            <strong><?php echo esc_attr($pets_text); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(@$hide_labels['sn_party_allowed'] != 1) { ?>
                        <li>
                            <i class="<?php echo esc_attr($party_allow); ?>" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_party_allowed')); ?>:
                            <strong><?php echo esc_attr($party_text); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(@$hide_labels['sn_children_allowed'] != 1) { ?>
                        <li>
                            <i class="<?php echo esc_attr($children_allow); ?>" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_children_allowed')); ?>:
                            <strong><?php echo esc_attr($children_text); ?></strong>
                        </li>
                        <?php } ?>
                    </ul>

                    <?php if( (!empty($additional_rules) && @$hide_labels['sn_add_rules_info'] != 1) || !empty($cancellation_policy)) { ?>
                    <div class="detail-list">

                        <?php if(!empty($cancellation_policy)) { ?>
                            <div><strong><?php echo esc_attr($homey_local['cancel_policy']); ?></strong></div>
                            <div><?php echo $cancellation_policy; ?></div>
                        <?php } ?>

                        <?php if(!empty($additional_rules) && @$hide_labels['sn_add_rules_info'] != 1) { ?>
                        <div><strong><?php echo esc_attr(homey_option('sn_add_rules_info')); ?></strong></div>
                        <div><?php echo ''.($additional_rules); ?></div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
