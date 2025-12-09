<?php
global $homey_local, $homey_prefix, $template;
$homey_local = homey_get_localization();
$homey_prefix = 'homey_';
/**
 * @package Homey
 * @since Homey 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php if(basename($template) == "dashboard-reservations.php"
        || basename($template) == "dashboard-reservations2.php"
        || basename($template) == "dashboard-reservations-experiences.php"
        || basename($template) == "dashboard-reservations2-experiences.php"
    ){ ?>
        <meta name="robots" content="noindex,nofollow">
    <?php } ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <?php if( homey_get_map_system() == 'mapbox' ) { ?>
        <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js'></script>
        <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css' rel='stylesheet' />
    <?php } ?>
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
<?php wp_body_open();
$header_type = homey_option('header_type');
if( empty($header_type)) {
    $header_type = '1';
}

if(homey_is_dashboard()) {
    $header_type = '1';
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
    get_template_part( 'template-parts/header/header', $header_type );
}
?>


<div id="section-body">
<?php 
if(homey_banner_needed()) {
    get_template_part('template-parts/banner/main'); 
}
?>	

<?php
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

if( !homey_is_dashboard() ) { 

    if(homey_search_needed()) { 

        if (!is_home() && !is_singular('post')) {
            if (($enable_search == 'on' || $enable_search == 1) && $search_position == 'under_banner') {
                if ( $search_pages == 'only_home' && is_front_page() ) {
                    get_template_part ('template-parts/search/main-search');
                    
                } elseif ($search_pages == 'all_pages' && ! homey_is_experiences_taxonomy() && ! homey_is_experiences_page() && ! homey_check_halfmap_header_search_needed() ) {
                    get_template_part ('template-parts/search/main-search');

                } elseif ($search_pages == 'only_innerpages' && !is_front_page() && ( homey_is_homey_page() || homey_is_listing_page() || homey_is_listing_taxonomy() ) ) {
                    get_template_part ('template-parts/search/main-search');
                    
                } else if( $search_pages == 'specific_pages' && is_page( $search_selected_pages ) ) { 
                    
                    get_template_part ('template-parts/search/main-search');
    
                } else if( $search_pages == 'only_taxonomy_pages' && homey_is_listing_taxonomy() ) { 
                    
                    get_template_part ('template-parts/search/main-search');
                    
                }
            }// enable_search for property

            if (($enable_search_exp == 'on' || $enable_search_exp == 1) && $search_position_exp == 'under_banner' ) {
                if ($search_pages_exp == 'only_home' && is_front_page() ) {
                    get_template_part ('template-parts/search/main-search-exp');
                    
                } elseif ($search_pages_exp == 'all_pages' && ! homey_is_listing_taxonomy() && ! homey_is_listing_page() && ! homey_check_halfmap_header_search_needed() ) { 
                        get_template_part ('template-parts/search/main-search-exp');

                } elseif ($search_pages_exp == 'only_innerpages' && !is_front_page() && ( homey_is_homey_page() || homey_is_experiences_page() || homey_is_experiences_taxonomy() ) ) {
                    get_template_part ('template-parts/search/main-search-exp');
                    
                } else if( $search_pages_exp == 'specific_pages' && is_page( $search_selected_pages_exp ) ) {
                    get_template_part ('template-parts/search/mairen-search-exp');
                    
                } else if( $search_pages_exp == 'only_taxonomy_pages' && homey_is_experiences_taxonomy() ) {
                    
                    get_template_part ('template-parts/search/main-search-exp');
                    
                }
            }// enable_search for property experience 

        }
    } //homey_search_needed
} //homey_is_dashboard

$already_signed_up = isset($_GET["already_signed_up"]) ? $_GET["already_signed_up"] : '';
?>
<div id="has_social_account" style="display: none" data-has-social-account="<?php echo esc_attr($already_signed_up);?>"></div>

