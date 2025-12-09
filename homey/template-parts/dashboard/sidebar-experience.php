<?php
global $current_user, $homey_local, $homey_prefix;
wp_get_current_user();
$userID  =  $current_user->ID;
$homey_author = homey_get_author_by_id('36', '36', 'img-circle', $userID);
$author_pic = $homey_author['photo'];
$price_postfix = '';

$cgl_types = homey_option('experience_cgl_types');
$price_separator = homey_option('currency_separator');

$guests_icon = homey_option('experience_lgc_guests_icon');
$price_separator = homey_option('currency_separator');

if(!empty($guests_icon)) {
    $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
}

$total_guests = 0;
$experience_id = $title = $address = $image = $experience_address = $night_price = $experience_bedrooms = $guests = $permalink = '';
if((isset($_GET['edit_experience']) && $_GET['edit_experience'] != '') || (isset($_GET['upgrade_id']) && $_GET['upgrade_id'] != '') || (isset($_GET['experience_id']) && $_GET['experience_id'] != '')) {

    if(isset($_GET['edit_experience']) && $_GET['edit_experience'] != '') {
        $experience_id = $_GET['edit_experience'];

    } elseif(isset($_GET['upgrade_id']) && $_GET['upgrade_id'] != '') {
        $experience_id = $_GET['upgrade_id'];

    } elseif(isset($_GET['experience_id']) && $_GET['experience_id'] != '') {
        $experience_id = $_GET['experience_id'];

    }

    $title = get_the_title($experience_id);

    $experience_address = ''; //homey_get_experience_data_by_id('experience_address', $experience_id);
    $night_price = homey_get_experience_data_by_id('night_price', $experience_id);
    $experience_bedrooms = homey_get_experience_data_by_id('experience_bedrooms', $experience_id);
    $guests = homey_get_experience_data_by_id('guests', $experience_id);

    $guests         = $guests > 0 ? $guests : 0;

    $allow_num_additional_guests = get_post_meta($experience_id, 'homey_allow_additional_guests', true );

    $num_additional_guests = get_post_meta($experience_id, 'homey_num_additional_guests', true );
    $num_additional_guests = $num_additional_guests > 0 && $allow_num_additional_guests != 'no' ? $num_additional_guests : 0;

    $total_guests   = (int) $num_additional_guests + (int) $guests;

    $permalink = get_permalink($experience_id);

    $experience_price = homey_exp_get_price_by_id($experience_id);

    $price_postfix = homey_exp_get_price_label_by_id($experience_id);
}


?>
<div class="item-grid-view">
    <div class="add-new-item item-wrap">
        <div class="upload-view-media item-media-thumb">
            <div class="media-image">
                <?php
                echo '<a class="hover-effect" href="'.esc_url($permalink).'">';
                if(!empty($experience_id)) {
                    if( has_post_thumbnail( $experience_id ) ) {
                        $post_thumbnail_id = get_post_thumbnail_id( $experience_id );
                        $experience_thumb = wp_get_attachment_image_src( $post_thumbnail_id, 'homey-listing-thumb' );?>
                        <img src="<?php echo esc_url($experience_thumb[0]); ?>">
                    <?php    
                    }else{
                        homey_image_placeholder( 'homey-listing-thumb' );
                    }   
                } else {
                ?>
                <img src="http://place-hold.it/370x250" alt="<?php esc_attr_e('Image', 'homey'); ?>">
                <?php } ?>
                <?php echo '</a>'; ?>
            </div>
            <div class="item-media-price">
                <span class="item-price">
                    <sup><?php echo homey_get_currency(false); ?></sup>
                    <span class="price-count" id="price-place">
                        <?php 
                        if(!empty($experience_price)) {
                            echo esc_html($experience_price);
                        } else {
                            echo '0'; 
                        }?> 
                    </span>
                    <sub><?php echo esc_attr($price_separator); ?><span class="price-postfix" id="price-postfix"><?php echo $price_postfix;?></span></sub>
                </span>
            </div>

            <?php if(!empty($author_pic)) { ?>
            <div class="item-user-image">
                <?php echo ''.$author_pic; ?>
            </div>
            <?php } ?>
        </div>
        <div class="upload-view-body item-body">
            <div class="item-title-head">
                <h3 class="title">
                    <span id="title-place">
                        <a href="<?php echo esc_url($permalink);?>">
                        <?php 
                        if(!empty($title)) {
                             echo esc_html($title);
                        } else {
                            esc_html_e('Title', 'homey');
                        }?>
                        </a>        
                    </span>
                </h3>
                <?php get_template_part('single-experience/item-address', null, array('address_tag_class' => 'item-address', 'prefix_address' => '<span id="address-place">', 'postfix_address' => '</span>')); ?>
                <!--<address class="item-address">
                    <span id="address-place"> 
                        <?php 
                        if(!empty($experience_address)) {
                            echo esc_html($experience_address);
                        } else {
                            esc_html_e('Address', 'homey'); 
                        }?> 
                    </span>
                </address>-->
            </div>

            <ul class="item-amenities">
                <?php if($cgl_types != 0) { ?>
                        <li class="item-type">
                            <span id="experience-type-view">
                                <?php
                                if(!empty($experience_id)) {
                                    echo homey_taxonomy_simple_by_ID('experience_type', $experience_id);
                                } else {
                                    echo homey_option('experience_sn_type_label');
                                }
                                ?>
                            </span>
                        </li>
                    <?php }  ?>

                <div class="item-footer">
                    <div class="footer-left">
                        <div class="stars">
                            <?php echo homey_get_review_stars_v2(); ?>
                        </div>
                    </div>
                    <div class="footer-right">

                    </div>
                </div>
            </ul>

        </div>
    </div>
</div>