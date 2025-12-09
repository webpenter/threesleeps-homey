<?php
global $post, $layout_order, $hide_labels;
$booking_or_contact_theme_options = homey_option('experience_what_to_show');
$booking_or_contact = homey_get_experience_data('booking_or_contact_exp');
if(empty($booking_or_contact)) {
    $what_to_show = $booking_or_contact_theme_options;
} else {
    $what_to_show = $booking_or_contact;
}
?>
<section class="detail-property-page-header-area detail-property-page-header-area-v4">

    <?php get_template_part('single-experience/top-gallery-variable-width'); ?>

</section><!-- header-area -->

<section class="main-content-area detail-property-page detail-property-page-v1">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                <div class="content-area">
                    <?php 
                    get_template_part('single-experience/title');
                    
                    if ($layout_order) { 
                        foreach ($layout_order as $key => $value) {

                            switch($key) {
                                case 'providing':
                                    get_template_part('single-experience/providing');
                                    break;

                                case 'about':
                                    get_template_part('single-experience/about');
                                break;

                                case 'about_host':
                                    get_template_part('single-experience/about-host');
                                    break;

                                case 'about_commercial':
                                    get_template_part('single-experience/about', 'commercial');
                                break;

                                case 'services':
                                    get_template_part('single-experience/services');
                                break;
                                
                                case 'details':
                                    get_template_part('single-experience/details');
                                break;

                                case 'gallery':
                                    get_template_part('single-experience/gallery');
                                break;

                                case 'prices':
                                    get_template_part('single-experience/prices');
                                break;

                                case 'accomodation':
                                    get_template_part('single-experience/accomodation');
                                break;

                                case 'map':
                                    get_template_part('single-experience/map');
                                break;

                                case 'nearby':
                                    get_template_part('single-experience/what-nearby');
                                break;

                                case 'features':
                                    get_template_part('single-experience/features');
                                break;

                                case 'video':
                                    get_template_part('single-experience/video');
                                break;

                                case 'rules':
                                    get_template_part('single-experience/rules');
                                break;

                                case 'custom-periods':
                                    get_template_part('single-experience/custom-periods');
                                break;

                                case 'availability':
                                    get_template_part('single-experience/availability');
                                break;

                                case 'host':
                                    get_template_part('single-experience/host');
                                break;

                                case 'reviews':
                                    get_template_part('single-experience/reviews');
                                break;

                                case 'services':
                                    get_template_part('single-experience/services');
                                    break;

                                case 'similar-experience':
                                    get_template_part('single-experience/similar-experience');
                                break;
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 homey_sticky">
                <div class="sidebar right-sidebar">
                <?php
                if( homey_exp_affiliate_booking_link() ) { ?>

                    <a href="<?php echo homey_exp_affiliate_booking_link(); ?>" target="_blank" class="btn btn-full-width btn-primary"><?php echo esc_html__('Book Now', 'homey'); ?></a>

                    <?php
                }elseif($what_to_show == 'booking_form') {
                    get_template_part('single-experience/booking/sidebar-booking-module');
                } elseif($what_to_show == 'contact_form') {
                    get_template_part('single-experience/contact-form');
                }elseif($what_to_show == 'contact_form_to_guest') {
                    if(is_user_logged_in()){
                        get_template_part('single-experience/booking/sidebar-booking-module');
                    }else{
                        get_template_part('single-experience/contact-form');
                    }
                }
                ?>

                <?php get_sidebar('experience'); ?>
                
                </div>
            </div>
        </div>
    </div>
</section><!-- main-content-area -->