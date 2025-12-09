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
$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$experience_price  = homey_exp_get_price();

$dashboard_experiences = homey_get_template_link('template/dashboard-experience.php');
$edit_link  = add_query_arg( 'edit_experience', $post_id, $edit_link ) ;
$delete_link  = add_query_arg( 'experience_id', $post_id, $dashboard_experiences ) ;
$property_status = get_post_status ( $post->ID );
$price_separator = homey_option('currency_separator');

$cgl_guests = homey_option('cgl_guests');

?>

<tr>
    <td data-label="<?php echo esc_attr($homey_local['thumb_label']) ;?>">
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
    <td data-label="<?php echo esc_attr($homey_local['address']) ;?>">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php get_template_part('single-experience/item-address'); ?>

        <?php if(!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>

    <td data-label="<?php echo homey_option('experience_sn_type_label') ;?>"><?php echo homey_taxonomy_simple('experience_type'); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['price_label']) ;?>">
        <?php if(!empty($experience_price)) { ?>
        <strong><?php echo homey_formatted_price($experience_price, false); ?><?php echo homey_exp_get_price_label(); ?></strong><br>
        <?php } ?>
    </td>

    <?php if($cgl_guests != 0) { ?>
        <td data-label="<?php echo homey_option('experience_glc_guests_label') ;?>"><?php echo esc_attr($guests); ?></td>
    <?php } ?>

    <td data-label="<?php echo homey_option('actions_label') ;?>">
        <div class="custom-actions">
            <button data-listid="<?php echo intval( $post->ID ); ?>" class="remove_fav btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['delete_btn']); ?>"><i class="homey-icon homey-icon-bin-1-interface-essential"></i></button>
            <a href="<?php the_permalink(); ?>" target="_blank" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['view_btn']); ?>"><i class="homey-icon homey-icon-arrow-right-1"></i></a>
        </div>
    </td>
</tr>