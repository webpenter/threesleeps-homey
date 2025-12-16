<?php
$mobile_logo = homey_option( 'mobile_logo', false, 'url' );
$splash_logo = homey_option( 'custom_logo_mobile_splash', false, 'url' );
if(homey_is_transparent_logo()) {
    $mobile_logo = $splash_logo;
}

$menu_sticky = homey_option('menu-sticky');
?>
<header id="homey_nav_sticky_mobile" class="header-nav header-mobile hidden-md hidden-lg no-cache-<?php echo strtotime("now"); ?>" data-sticky="<?php echo esc_attr( $menu_sticky ); ?>">
    <div class="header-mobile-wrap no-cache-<?php echo strtotime("now"); ?>">
        <div class="container">
            <div class="row">
                <div class="col-xs-3">
                    <button type="button" class="btn btn-mobile-nav mobile-main-nav" data-toggle="collapse" data-target="#mobile-nav" aria-expanded="false">
                        <i class="homey-icon homey-icon-navigation-menu" aria-hidden="true"></i>
                    </button><!-- btn-mobile-nav -->
                </div>
                <div class="col-xs-6">
                    <div class="mobile-logo text-center">
                        
                        <a href="<?php echo esc_url(site_url('/')); ?>">
                            <?php if( !empty( $mobile_logo ) ) { ?>
                                <img src="<?php echo esc_url( $mobile_logo ); ?>" alt="<?php bloginfo( 'name' );?>" title="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
                            <?php } else {
                                    bloginfo( 'name' );
                                } ?>
                        </a>
                        
                    </div><!-- mobile-logo -->
                </div>
                <div class="col-xs-3">
                    <?php if(homey_is_login_register()) { ?>
                    <div class="user-menu text-right">
                        <button type="button" class="btn btn-mobile-nav user-mobile-nav" data-toggle="collapse" data-target="#user-nav" aria-expanded="false">
                            <!-- <i class="homey-icon homey-icon-single-neutral-circle" aria-hidden="true"></i> -->
                             <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="none"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path stroke="#535358" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8h15M5 16h22M5 24h22M5 11l3-3-3-3"></path> </g></svg><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12.12 12.78C12.05 12.77 11.96 12.77 11.88 12.78C10.12 12.72 8.71997 11.28 8.71997 9.50998C8.71997 7.69998 10.18 6.22998 12 6.22998C13.81 6.22998 15.28 7.69998 15.28 9.50998C15.27 11.28 13.88 12.72 12.12 12.78Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M18.74 19.3801C16.96 21.0101 14.6 22.0001 12 22.0001C9.40001 22.0001 7.04001 21.0101 5.26001 19.3801C5.36001 18.4401 5.96001 17.5201 7.03001 16.8001C9.77001 14.9801 14.25 14.9801 16.97 16.8001C18.04 17.5201 18.64 18.4401 18.74 19.3801Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> 
                            <?php echo homey_messages_notification( 'user-alert' ); ?>
                        </button>
                    </div><!-- user-menu -->
                    <?php } ?>
                </div>
            </div><!-- row -->
        </div><!-- container -->
    </div><!-- header-mobile-wrap -->
    
    <div class="container no-cache-<?php echo strtotime("now"); ?>">
        <div class="row">
            <div class="mobile-nav-wrap">
                <?php get_template_part ('template-parts/header/mobile-menu'); ?>
            </div><!-- mobile-nav-wrap -->    
        </div>        
    </div><!-- container -->
    <div class="container no-cache-<?php echo strtotime("now"); ?>">
        <div class="row">
            <div class="user-nav-wrap">
                <?php if( class_exists('Homey_login_register') ): ?>
            
                    <?php 
                    if( is_user_logged_in() ) { 
                        get_template_part ('template-parts/header/mobile-user-menu');
                    } else {
                        get_template_part ('template-parts/header/mobile-user-menu-not-logged-in');
                    }
                    ?>
                
                <?php endif; ?>
            </div><!-- mobile-nav-wrap -->
        </div>
    </div><!-- container -->
</header><!-- header-nav header-mobile hidden-md hidden-lg -->