<?php 
global $homey_local;

$enable_search = homey_option('enable_search');
$search_position = homey_option('search_position');
$search_pages = homey_option('search_pages');
$search_selected_pages = homey_option('search_selected_pages');

$enable_search_exp = homey_option('enable_search_exp');
$search_position_exp = homey_option('search_position_exp');
$search_pages_exp = homey_option('search_pages_exp');
$search_selected_pages_exp = homey_option('search_selected_pages_exp');

if(isset($_GET['search_position'])) {
    $search_position = $_GET['search_position'];
}

if(isset($_GET['search_position_exp'])) {
    $search_position_exp = $_GET['search_position_exp'];
}

$splash_page_nav = is_splash_nav_enable();
$menu_sticky = homey_option('menu-sticky');

if(homey_is_dashboard()) { $menu_sticky = 0; ?>
<div class="header-dashboard no-cache-<?php echo strtotime("now"); ?>">
<?php } ?>

<div class="nav-area header-type-1 <?php homey_transparent(); ?> no-cache-<?php echo strtotime("now"); ?>">
    <!-- top bar -->
    <?php 
    if(homey_topbar_needed()) {
        get_template_part('template-parts/header/top-bar'); 
    }
    ?>

    <!-- desktop nav -->
    <header id="homey_nav_sticky" class="header-nav hidden-sm hidden-xs no-cache-<?php echo strtotime("now"); ?>" data-sticky="<?php echo esc_attr( $menu_sticky ); ?>">
        <div class="<?php homey_header_container(); ?>">
            <div class="header-inner table-block">
                <div class="header-comp-logo">
                    <?php get_template_part('template-parts/header/logo'); ?>
                </div>

                <div class="header-comp-nav <?php homey_header_menu_align(); ?>">
                <?php if(!homey_is_splash() || $splash_page_nav != 0 ) { ?>
                    <?php get_template_part('template-parts/header/main-nav'); ?>
                <?php } ?>
                </div>
            
                <?php if( class_exists('Homey_login_register') ): ?>
                <div class="header-comp-right no-cache-<?php echo strtotime("now"); ?>">
                    <?php
                    if(homey_is_login_register()) { 
                        if( is_user_logged_in() ) { 
                            get_template_part ('template-parts/header/account');
                        } else {
                            get_template_part ('template-parts/header/login-register-v1');
                        }
                    } else {
                        get_template_part ('template-parts/header/social');
                    }
                    ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </header>
    <!-- mobile header -->
    <?php get_template_part ('template-parts/header/header-mobile'); ?>

    <?php 
    if( !homey_is_dashboard() ) {

        if(homey_search_needed()) {
            if (!is_home() && !is_singular('post')) {

                if (($enable_search == 'on' || $enable_search == 1) && $search_position == 'under_nav') {
                    if ( $search_pages == 'only_home' && is_front_page() ) {
                        get_template_part ('template-parts/search/main-search');
                        
                    } elseif ($search_pages == 'all_pages' && ! homey_is_experiences_taxonomy() && ! homey_is_experiences_page() ) {
                        get_template_part ('template-parts/search/main-search');

                    } elseif ($search_pages == 'only_innerpages' && !is_front_page() && ! homey_is_experiences_page() && ( homey_is_homey_page() || homey_is_listing_page() || homey_is_listing_taxonomy() ) ) {
                        get_template_part ('template-parts/search/main-search');
                        
                    } else if( $search_pages == 'specific_pages' && is_page( $search_selected_pages ) && ! homey_is_experiences_page() ) {
                        
                        get_template_part ('template-parts/search/main-search');
        
                    } else if( $search_pages == 'only_taxonomy_pages' && homey_is_listing_taxonomy() && ! homey_is_experiences_page() ) {
                        
                        get_template_part ('template-parts/search/main-search');
                        
                    }
                }// enable_search for property

                if (($enable_search_exp == 'on' || $enable_search_exp == 1) && $search_position_exp == 'under_nav' ) {
                    if ($search_pages_exp == 'only_home' && is_front_page() ) {
                        get_template_part ('template-parts/search/main-search-exp');
                        
                    } elseif ($search_pages_exp == 'all_pages' && ! homey_is_listing_taxonomy() && ! homey_is_listing_page() ) {
                            get_template_part ('template-parts/search/main-search-exp');

                    } elseif ($search_pages_exp == 'only_innerpages' && !is_front_page() && ( homey_is_homey_page() || homey_is_experiences_page() || homey_is_experiences_taxonomy() ) ) {
                        get_template_part ('template-parts/search/main-search-exp');
                        
                    } else if( $search_pages_exp == 'specific_pages' && is_page( $search_selected_pages_exp ) ) {
                        get_template_part ('template-parts/search/main-search-exp');
                        
                    } else if( $search_pages_exp == 'only_taxonomy_pages' && homey_is_experiences_taxonomy() ) {
                        
                        get_template_part ('template-parts/search/main-search-exp');
                        
                    }
                }// enable_search for property experience 
            }
        } //homey_search_needed
    } //homey_is_dashboard
    ?>
</div>

<?php if(homey_is_dashboard()) { ?>
</div>
<?php } ?>
