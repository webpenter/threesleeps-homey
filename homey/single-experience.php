<?php get_header();

global $post, $experience_author, $layout_order;
$current_user = wp_get_current_user();
$all_meta_for_user = get_user_meta( $current_user->ID );
$experience_layout = homey_option('experience_detail_layout');
$hide_labels = homey_option('experience_show_hide_labels');
$experience_nav = homey_option('experience-detail-nav');
$homey_booking_type = homey_booking_type();
if(isset($_GET['experience_detail_layout'])) {
    $experience_layout = $_GET['experience_detail_layout'];
}

$layout_order = homey_option('experience_blocks');
$layout_order = $layout_order['enabled'];

$already_blocked_dates = homey_get_booked_hours($post->ID);
$already_blocked_dates = homey_get_booked_hours($post->ID);
if( have_posts() ):

    while( have_posts() ): the_post();
    

        $experience_author = homey_get_author('70', '70', 'img-circle media-object');

        if( $experience_layout == 'v1' ) {
            get_template_part( 'single-experience/single-experience', 'v1');

        } else if( $experience_layout == 'v2' ) {
            get_template_part( 'single-experience/single-experience', 'v2');
            
        } else if( $experience_layout == 'v3' ) {
            get_template_part( 'single-experience/single-experience', 'v3');

        }  else if( $experience_layout == 'v4' ) {
            get_template_part( 'single-experience/single-experience', 'v4');

        } else if( $experience_layout == 'v5' ) {
            get_template_part( 'single-experience/single-experience', 'v5');

        } else if( $experience_layout == 'v6' ) {
            get_template_part( 'single-experience/single-experience', 'v6');

        } else {
            get_template_part( 'single-experience/single-experience', 'v1');
        }
        
        get_template_part('single-experience/contact-host');

        get_template_part('single-experience/booking/overlay-booking');

        if($experience_nav) {
            get_template_part('single-experience/experience-nav');
        }

    endwhile; 
endif; 
?>
<div id="post_already_bookd_dates">
    <?php foreach($already_blocked_dates as $datetimestr => $b){ ?>
        <span data-datetime="<?php echo date("d-m-Y H:i:s", $datetimestr); ?>"></span>
    <?php } ?>
</div>
<?php get_footer(); ?>
