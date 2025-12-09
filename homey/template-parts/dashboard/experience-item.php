<?php
global $post,
       $edit_link,
       $homey_local,
       $homey_prefix,
       $prop_address,
       $prop_featured,
       $payment_status;

$post_id    = get_the_ID();
$experience_images = get_post_meta( get_the_ID(), $homey_prefix.'experience_images', false );
$address        = '';//get_post_meta( get_the_ID(), $homey_prefix.'experience_address', true );
$bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'experience_bedrooms', true );

$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
$guests         = $guests > 0 ? $guests : 0;

$num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );
$num_additional_guests = $num_additional_guests > 0 ? $num_additional_guests : 0;

$total_guests   = (int) $num_additional_guests + (int) $guests;

$beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
$baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$featured       = get_post_meta( get_the_ID(), $homey_prefix.'featured', true );

$experience_price = homey_exp_get_price_by_id($post_id);

$dashboard_experiences = homey_get_template_link('template/dashboard-experience.php');
$edit_link  = add_query_arg( 'edit_experience', $post_id, $edit_link ) ;
$delete_link  = add_query_arg( 'experience_id', $post_id, $dashboard_experiences ) ;
$property_status = get_post_status ( $post->ID );
$check_experience_status = $property_status;
$dashboard = homey_get_template_link('template/dashboard.php');
$price_separator = homey_option('currency_separator');
$make_featured = homey_option('experience_make_featured');
$approval_form = '';

if($property_status == 'publish') {
    $property_status = esc_html__('Published', 'homey');
    $status_class = "label-success";
} elseif($property_status == 'pending') {
    $status_class = "label-warning";
    $property_status = esc_html__('Waiting for Approval', 'homey');
    $approval_form = 1;
} elseif($property_status == 'draft') {
    $status_class = 'label-default';
    $property_status = esc_html__('Draft', 'homey');
} elseif($property_status == 'disabled') {
    $status_class = 'label-danger';
    $property_status = esc_html__('Disabled', 'homey');
} else {
    $status_class = "label-success";
    $property_status = esc_html__(strtoupper($property_status), 'homey');

}

if($check_experience_status == 'publish') {
    $disable_list_text = esc_html__('Disable', 'homey');
    $icon = 'fa-pause';
    $list_current_status = 'enabled';
} elseif($check_experience_status == 'disabled') {
    $disable_list_text = esc_html__('Enable', 'homey');
    $list_current_status = 'disabled';
    $icon = 'fa-play';
}

$upgrade_link  = add_query_arg( array(
    'dpage' => 'upgrade_featured_exp',
    'upgrade_id' => $post_id,
), $dashboard );
?>

<tr>
    <td data-label="<?php echo esc_attr($homey_local['thumb_label']); ?>">
        <a href="<?php the_permalink(); ?>">
            <?php
            if( has_post_thumbnail( $post->ID ) ) {
                the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive dashboard-experience-thumbnail' ) );
            }else{
                homey_image_placeholder( 'homey-listing-thumb' );
            }
            ?>
        </a>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['address']); ?>">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php get_template_part('single-experience/item-address'); ?>

        <?php if(!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>
    <!-- <td data-label="ID">HY01</td> -->
    <td data-label="<?php echo homey_option('experience_sn_type_label'); ?>"><?php echo homey_taxonomy_simple('experience_type'); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['price_label']); ?>">
        <?php if(!empty($experience_price)) { ?>
            <strong><?php echo homey_formatted_price($experience_price, false); ?><?php echo homey_exp_get_price_label_by_id($post_id); ?></strong><br>
        <?php } ?>
    </td>
   <!--<td data-label="<?php echo homey_option('experience_glc_guests_label');?>"><?php echo esc_attr($guests); ?></td>-->
    <td data-label="<?php echo homey_option('experience_glc_guests_label');?>"><?php echo $total_guests; //echo esc_attr($guests) .'+'. esc_attr($num_additional_guests) .'='. $total_guests ?></td>
    <td data-label="<?php echo homey_option('experience_sn_id_label');?>"><?php echo get_the_ID(); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['status_label']); ?>">
        <span class="label <?php echo esc_attr($status_class); ?>"><?php echo esc_html($property_status); ?></span>
        <?php if($approval_form == 1 && homey_is_admin()){ ?>
            <span style="cursor:pointer;" class="label label-success approve_experience" data-approval-experience-id="<?php echo $post->ID;?>"><?php echo esc_html__('Click to approve', 'homey'); ?></span>
        <?php } ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['actions_label']); ?>">
        <div class="custom-actions">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <?php echo esc_html__('Actions', 'homey');?>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php the_permalink(); ?>" target="_blank"><?php echo esc_html__('View', 'homey');?></a></li>
                    <li><a href="javascript:void(0);" onclick="location.href='<?php echo esc_url($edit_link);?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['edit_btn']); ?>"><?php echo esc_html__('Edit', 'homey');?></a></li>
                    <li><a href="javascript:void(0);" onclick="location.href='<?php echo esc_url($edit_link.'&duplication=1&dup_id='.intval($post->ID));?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr('Duplicate', 'homey'); ?>"><?php echo esc_html__('Duplicate', 'homey');?></a></li>
                    <?php
                    if($make_featured != 0) { ?>
                    <li>
                        <?php if($featured != 1) { ?>
                            <?php if( homey_is_woocommerce() ) { ?>
                                <a href="javascript:void(0);" data-experience-id="<?php echo intval($post_id); ?>" data-featured="1" class="homey-woocommerce-featured-pay-exp" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><?php echo esc_html__('Upgrade to feature', 'homey');?></a>
                                <?php
                            } else if( in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
                                <a href="<?php echo esc_url($upgrade_link); ?>" class="membership-experience-featured-js" data-id="<?php echo intval($post->ID); ?>" data-nonce="<?php echo wp_create_nonce('featured_experience_nonce') ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><?php echo esc_html__('Upgrade to feature', 'homey');?></a>
                                <?php
                            } else { ?>
                                <a href="<?php echo esc_url($upgrade_link); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><?php echo esc_html__('Upgrade to feature', 'homey');?></a>
                                <?php
                            }
                            ?>
                        <?php } else { ?>
                            <a href="javascript:void(0);" class="" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_html__('It is featured.', 'homey'); ?>"><?php echo esc_html__('Already Featured', 'homey');?></a>
                        <?php }  ?>
                        </li>
                    <?php } ?>
                    <?php if($check_experience_status == 'publish' || $check_experience_status == 'disabled') { ?>
                        <li><a href="javascript:void(0);" class="put_on_hold" data-id="<?php echo intval($post->ID); ?>" data-current="<?php echo esc_attr($list_current_status);?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($disable_list_text);?>"><?php echo $disable_list_text;?></a></li>
                    <?php } ?>
                    <li role="separator" class="divider"></li>
                    <li><a href="javascript:void(0);" class="delete-experience" data-id="<?php echo intval($post->ID); ?>" data-nonce="<?php echo wp_create_nonce('delete_experience_nonce') ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['delete_btn']);?>"><?php echo esc_html__('Delete', 'homey');?></a></li>
                </ul>
            </div>
        </div>
    </td>
</tr>