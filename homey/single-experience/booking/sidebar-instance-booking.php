<?php
global $post, $homey_local;

$experience_id = isset($_GET['experience_id']) ? $_GET['experience_id'] : '';
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

$check_in_unix = strtotime($check_in);
$check_out_unix = strtotime($check_out);

$booking_hide_fields = homey_option('booking_hide_fields');

$check_in_date = date(get_homey_to_std_date_format(), $check_in_unix);
$check_out_date = date(get_homey_to_std_date_format(), $check_out_unix);

$room_type = homey_taxonomy_simple_by_ID('room_type', $experience_id);
$experience_type = homey_taxonomy_simple_by_ID('experience_type', $experience_id);
$slash = '';
if(!empty($room_type) && !empty($experience_type)) {
    $slash = '/';
}

$guests_label = homey_option('srh_guest_label');
if($guests > 1) {
    $guests_label = homey_option('srh_guests_label');
}

$rating = homey_option('rating');
$total_rating = get_post_meta( $experience_id, 'experience_total_rating', true );
?>
<div class="booking-sidebar">

    <div class="block">

        <div class="booking-property clearfix">
            <div class="booking-property-info">
                <h3><?php echo get_the_title($experience_id); ?></h3>
                <div><?php echo esc_attr($room_type).' '.$slash.' '.esc_attr($experience_type); ?></div>

                <?php 
                if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                <div class="list-inline rating">
                    <?php echo homey_get_review_stars($total_rating, false, true); ?>
                </div>
                <?php } ?>

            </div>
            <div class="booking-property-img">
                <?php
                if( has_post_thumbnail( $experience_id ) ) {
                    echo get_the_post_thumbnail( $experience_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                }else{
                    homey_image_placeholder( 'homey-listing-thumb' );
                }
                ?>  
            </div>
        </div>

        <div class="block-body">

            <div class="booking-data clearfix">
                <div class="booking-data-block booking-data-arrive">
                    <div class="booking-data-top">
                        <i class="homey-icon homey-icon-navigation-down-circle-interface-essential" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_arrive_label_exp')); ?>
                    </div>
                    <div class="booking-data-bottom">
                        <?php if(!empty($check_in_date)) { echo esc_attr($check_in_date); } ?>
                    </div>
                </div>

                <?php if('currently-no-need' == 'starting-experiences-feature') { ?>
                    <div class="booking-data-block booking-data-depart">
                        <div class="booking-data-top">
                            <i class="homey-icon homey-icon-navigation-up-circle" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_depart_label')); ?>
                        </div>
                        <div class="booking-data-bottom">
                            <?php if(!empty($check_out_date)) { echo esc_attr($check_out_date); } ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if($booking_hide_fields['guests'] != 1) {?>
                <div class="booking-data-block booking-data-guests">
                    <div class="booking-data-top">
                        <i class="homey-icon homey-icon-multiple-man-woman-2" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_guests_label')); ?>
                    </div>
                    <div class="booking-data-bottom">
                        <?php echo esc_attr($guests); ?> <?php echo esc_attr($guests_label); ?>
                    </div>
                </div>
                <?php } ?>
            </div> 

            <?php  get_template_part('single-experience/booking/payment-list-collapse'); ?>
        </div><!-- block-body -->
    </div><!-- block -->
</div><!-- booking-sidebar -->
