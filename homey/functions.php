<?php

/**
 * Homey functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Homey
 * @since Homey 1.0.0
 * @author Waqas Riaz
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
global $wp_version;

/**
 *    ---------------------------------------------------------------
 *    Define constants
 *    ---------------------------------------------------------------
 */
define('HOMEY_THEME_NAME', 'Homey');
define('HOMEY_THEME_SLUG', 'homey');
define('HOMEY_THEME_VERSION', '2.4.6');
define('HOMEY_CSS_DIR_URI', get_template_directory_uri() . '/css/');
define('HOMEY_JS_DIR_URI', get_template_directory_uri() . '/js/');
/**
 *    ----------------------------------------------------------------------------------
 *    Set up theme default and register various supported features.
 *    ----------------------------------------------------------------------------------
 */
if (!function_exists('homey_setup')) {
    function homey_setup()
    {

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        //Let WordPress manage the document title.
        add_theme_support('title-tag');

        //Add support for post thumbnails.
        add_theme_support('post-thumbnails');

        /* Load child theme languages */
        load_theme_textdomain('homey', get_stylesheet_directory() . '/languages');

        /* load theme languages */
        load_theme_textdomain('homey', get_template_directory() . '/languages');

        add_image_size('homey-variable-slider-img1-570_570', 570, 570, true);
        add_image_size('homey-variable-slider-4-images-285_285', 285, 285, true);

        add_image_size('homey-listing-thumb', 450, 300, true);
        add_image_size('homey-gallery-thumb', 250, 250, true);
        add_image_size('homey-gallery', 1140, 760, true);
        add_image_size('homey-gallery-thumb2', 120, 80, true);
        add_image_size('homey-variable-slider', 0, 500, true);

        add_image_size('homey_thumb_555_360', 555, 360, true);
        add_image_size('homey_thumb_555_262', 555, 262, true);
        add_image_size('homey_thumb_360_360', 360, 360, true);
        add_image_size('homey_thumb_360_120', 360, 120, true);


        /**
         *    Register nav menus.
         */
        register_nav_menus(
            array(
                'main-menu' => esc_html__('Main Menu', 'homey'),
                'top-menu' => esc_html__('Top Menu', 'homey'),
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        /*
         * Enable support for Post Formats.
         * See https://developer.wordpress.org/themes/functionality/post-formats/
         */
        add_theme_support('post-formats', array());

        homey_update_guests_meta();
        update_option('homey_map_db_version', '1.0');

        //remove gallery style css
        add_filter('use_default_gallery_style', '__return_false');
    }

    add_action('after_setup_theme', 'homey_setup');
}

/**
 *    -------------------------------------------------------------------------
 *    Set up the content width value based on the theme's design.
 *    -------------------------------------------------------------------------
 */
if (!function_exists('homey_content_width')) {
    function homey_content_width()
    {
        $GLOBALS['content_width'] = apply_filters('homey_content_width', 1170);
    }

    add_action('after_setup_theme', 'homey_content_width', 0);
}

function homey_update_guests_meta()
{
    global $wpdb;


    if (!get_option('homey_guests_meta', false)) {

        $prefix = $wpdb->prefix;

        $delete_query = 'delete from ' . $prefix . 'postmeta where meta_key = "homey_total_guests_plus_additional_guests"';

        $qry = 'INSERT INTO ' . $prefix . 'postmeta ( post_id, meta_key, meta_value) 
		select  p1.ID ,  "homey_total_guests_plus_additional_guests" , (select sum(pm2.meta_value) as sleepsTotal from ' . $prefix . 'postmeta pm2
		 	where pm2.post_id = p1.ID
		 	and pm2.meta_key in ("homey_guests", "homey_num_additional_guests")) 
		from ' . $prefix . 'posts p1
		where p1.post_type = "listing"';


        $wpdb->query($delete_query);
        $wpdb->query($qry);

        update_option('homey_guests_meta', true);
    }
}


/**
 *    -------------------------------------------------------------------
 *    Visual Composer
 *    -------------------------------------------------------------------
 */
if (class_exists('Vc_Manager') && class_exists('Homey')) {

    if (!function_exists('homey_include_composer')) {
        function homey_include_composer()
        {
            require_once(get_template_directory() . '/framework/vc_extend.php');
        }

        add_action('init', 'homey_include_composer', 9999);
    }
}

if (!function_exists('homey_or_custom_posts')) {
    function homey_or_custom_posts($query)
    {
        if ($query->is_admin) {
            $post_type = $query->get('post_type');

            if ($post_type == 'homey_reservation' || $post_type == 'homey_review' || $post_type == 'homey_invoice') {

                $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';

                if (empty($orderby)) {
                    $query->set('orderby', 'date');
                    $query->set('order', 'DESC');
                }
            }
        }
        return $query;
    }

    add_filter('pre_get_posts', 'homey_or_custom_posts');
}


/**
 *    -----------------------------------------------------------------------------------------
 *    Enqueue scripts and styles.
 *    -----------------------------------------------------------------------------------------
 */
require_once(get_template_directory() . '/inc/register-scripts.php');


/**
 *    -----------------------------------------------------------------------------------------
 *    Include files
 *    -----------------------------------------------------------------------------------------
 */
require_once(get_template_directory() . '/framework/functions/helper.php');
require_once(get_template_directory() . '/framework/functions/wallet.php');
require_once(get_template_directory() . '/framework/functions/profile.php');
require_once(get_template_directory() . '/framework/functions/price.php');
require_once(get_template_directory() . '/framework/functions/experiences.php');
require_once(get_template_directory() . '/framework/functions/listings.php');
require_once(get_template_directory() . '/framework/functions/reservation.php');
require_once(get_template_directory() . '/framework/functions/experiences-reservation.php');
require_once(get_template_directory() . '/framework/functions/reservation-hourly.php');
require_once(get_template_directory() . '/framework/functions/calendar.php');
require_once(get_template_directory() . '/framework/functions/calendar-hourly.php');
require_once(get_template_directory() . '/framework/functions/calendar-daily-date.php');
require_once(get_template_directory() . '/framework/functions/review.php');
require_once(get_template_directory() . '/framework/functions/review-exp.php');
require_once(get_template_directory() . '/framework/functions/search.php');

require_once(get_template_directory() . '/framework/functions/search-experiences.php');

require_once(get_template_directory() . '/framework/functions/messages.php');
require_once(get_template_directory() . '/framework/functions/cron.php');
require_once(get_template_directory() . '/framework/functions/icalendar.php');
require_once(get_template_directory() . '/framework/functions/icalendar-exp.php');
require_once(get_template_directory() . '/framework/functions/v13-db.php');
require_once(get_template_directory() . '/framework/ics-parser/class.iCalReader.php');
require_once(get_template_directory() . '/template-parts/header/favicons.php');

require_once(get_template_directory() . '/framework/thumbnails/better-jpgs.php');


if (class_exists('WooCommerce', false)) {
    require_once(get_template_directory() . '/framework/functions/woocommerce.php');
}

/**
 *    -----------------------------------------------------------------------------------------
 *    Localizations
 *    -----------------------------------------------------------------------------------------
 */
require_once(get_theme_file_path('localization.php'));

/**
 *    -----------------------------------------------------------------------------------------
 *    Include hooks and filters
 *    -----------------------------------------------------------------------------------------
 */
require_once(get_template_directory() . '/framework/homey-hooks.php');

/**
 *    -----------------------------------------------------------------------------------------
 *    Styling
 *    -----------------------------------------------------------------------------------------
 */
if (class_exists('ReduxFramework')) {
    require_once(get_template_directory() . '/inc/styling-options.php');
}

if (1 == 1 || is_admin()) {
    /**
     *    -----------------------------------------------------------------------------------------
     *    TMG plugin activation
     *    -----------------------------------------------------------------------------------------
     */
    require_once(get_template_directory() . '/framework/class-tgm-plugin-activation.php');
    require_once(get_template_directory() . '/framework/register-plugins.php');



    if (class_exists('Homey')) {
        require_once(get_template_directory() . '/framework/functions/demo-importer.php');
    }

    /**
     * ----------------------------------------------------------------------------------------
     *  Experiences Meta Boxes
     * ----------------------------------------------------------------------------------------
     */


    require_once(get_template_directory() . '/framework/metaboxes/homey-meta-boxes-exp.php');
    require_once(get_template_directory() . '/framework/metaboxes/experience-state-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/experience-cities-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/experience-area-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/experience-type-meta.php');

    /**
     *    ---------------------------------------------------------------------------------------
     *    Meta Boxes
     *    ---------------------------------------------------------------------------------------
     */
    require_once(get_template_directory() . '/framework/metaboxes/homey-meta-boxes.php');
    require_once(get_template_directory() . '/framework/metaboxes/listing-state-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/listing-cities-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/listing-area-meta.php');
    require_once(get_template_directory() . '/framework/metaboxes/listing-type-meta.php');

    /**
     *    ---------------------------------------------------------------------------------------
     *    Options Admin Panel
     *    ---------------------------------------------------------------------------------------
     */
    require_once(get_template_directory() . '/framework/options/remove-tracking-class.php'); // Remove tracking

    /*-----------------------------------------------------------------------------------*/
    /*	Register blog sidebar, footer and custom sidebar
    /*-----------------------------------------------------------------------------------*/
    if (!function_exists('homey_widgets_init')) {
        add_action('widgets_init', 'homey_widgets_init');
        function homey_widgets_init()
        {
            register_sidebar(array(
                'name' => esc_html__('Default Sidebar', 'homey'),
                'id' => 'default-sidebar',
                'description' => esc_html__('Widgets in this area will be shown in the default sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Page Sidebar', 'homey'),
                'id' => 'page-sidebar',
                'description' => esc_html__('Widgets in this area will be shown in the page sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Listings Sidebar', 'homey'),
                'id' => 'listing-sidebar',
                'description' => esc_html__('Widgets in this area will be shown in listings sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Experiences Sidebar', 'homey'),
                'id' => 'experience-sidebar',
                'description' => esc_html__('Widgets in this area will be shown in experiences sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Blog Sidebar', 'homey'),
                'id' => 'blog-sidebar',
                'description' => esc_html__('Widgets in this area will be shown in the blog sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Single Listing', 'homey'),
                'id' => 'single-listing',
                'description' => esc_html__('Widgets in this area will be shown in the single listing sidebar.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Custom Sidebar 1', 'homey'),
                'id' => 'custom-sidebar-1',
                'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Custom Sidebar 2', 'homey'),
                'id' => 'custom-sidebar-2',
                'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Custom Sidebar 3', 'homey'),
                'id' => 'custom-sidebar-3',
                'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));

            register_sidebar(array(
                'name' => esc_html__('Footer Area 1', 'homey'),
                'id' => 'footer-sidebar-1',
                'description' => esc_html__('Widgets in this area will be show in footer column one', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Footer Area 2', 'homey'),
                'id' => 'footer-sidebar-2',
                'description' => esc_html__('Widgets in this area will be show in footer column two', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Footer Area 3', 'homey'),
                'id' => 'footer-sidebar-3',
                'description' => esc_html__('Widgets in this area will be show in footer column three', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
            register_sidebar(array(
                'name' => esc_html__('Footer Area 4', 'homey'),
                'id' => 'footer-sidebar-4',
                'description' => esc_html__('Widgets in this area will be show in footer column four', 'homey'),
                'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-top"><h3 class="widget-title">',
                'after_title' => '</h3></div>',
            ));
        }
    }
} // include only if it is admin panel()

if (!current_user_can('administrator') && !is_admin()) {
    add_filter('show_admin_bar', '__return_false');
}

if (!function_exists('homey_block_users')) :

    add_action('init', 'homey_block_users');

    function homey_block_users()
    {
        $users_admin_access = homey_option('users_admin_access');

        if (is_user_logged_in()) {
            if ($users_admin_access != 0) {
                if (is_admin() && !current_user_can('administrator') && isset($_GET['action']) != 'delete' && !(defined('DOING_AJAX') && DOING_AJAX)) {
                    wp_die(esc_html("You don't have permission to access this page.", "homey"));
                    exit;
                }
            }
        }
    }

endif;

require_once(get_template_directory() . '/framework/options/homey-options.php');
require_once(get_template_directory() . '/framework/options/homey-option.php');

function homey_stop_image_remove_while_listing_delete()
{
    if (isset($_GET['image_delete']) && $_GET['image_delete'] != '') {
        update_option('homey_not_delete_for_demo', $_GET['image_delete']);
    }
}

homey_stop_image_remove_while_listing_delete();


//Delete property attachments when delete property
add_action('before_delete_post', 'homey_delete_property_attachments');
if (!function_exists('homey_delete_property_attachments')) {
    function homey_delete_property_attachments($postid)
    {

        // We check if the global post type isn't ours and just return
        global $post_type;

        if ($post_type == 'homey_review') {
            $review_listing_id = get_post_meta($postid, 'reservation_listing_id', true);
            homey_adjust_listing_rating_on_delete($review_listing_id, $postid);
        }

        if (get_option('homey_not_delete_for_demo') == 1) {
            return;
        }
        if ($post_type == 'listing') {
            $media = get_children(array(
                'post_parent' => $postid,
                'post_type' => 'attachment'
            ));
            if (!empty($media)) {
                foreach ($media as $file) {
                    // pick what you want to do
                    //unlink(get_attached_file($file->ID));
                    wp_delete_attachment($file->ID);
                }
            }
            $attachment_ids = get_post_meta($postid, 'homey_listing_images', false);
            if (!empty($attachment_ids)) {
                foreach ($attachment_ids as $id) {
                    wp_delete_attachment($id);
                }
            }
        }
        return;
    }
}

function homey_delete_property_attachments_frontend($postid)
{

    // We check if the global post type isn't ours and just return
    global $post_type;


    if (get_option('homey_not_delete_for_demo') == 1) {
        return;
    }
    $media = get_children(array(
        'post_parent' => $postid,
        'post_type' => 'attachment'
    ));
    if (!empty($media)) {
        foreach ($media as $file) {
            // pick what you want to do
            //unlink(get_attached_file($file->ID));
            wp_delete_attachment($file->ID);
        }
    }
    $attachment_ids = get_post_meta($postid, 'homey_listing_images', false);
    if (!empty($attachment_ids)) {
        foreach ($attachment_ids as $id) {
            wp_delete_attachment($id);
        }
    }
    return;
}


function homey_pre_get_posts($query)
{

    if (is_admin())
        return;

    if (is_search() && $query->is_main_query()) {
        $query->set('post_type', 'post');
    }
}

add_action('pre_get_posts', 'homey_pre_get_posts');

/*
 * For Meta Tags
 * */

//Adding the Open Graph in the Language Attributes
function add_opengraph_doctype($output)
{
    return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
}

add_filter('language_attributes', 'add_opengraph_doctype');

//Lets add Open Graph Meta Info

function insert_fb_in_head()
{
    global $post;
    if (!is_singular()) //if it is not a post or a page
        return;
    echo '<meta property="og:title" content="' . get_the_title() . '"/>';
    echo '<meta property="og:type" content="article"/>';
    echo '<meta property="og:url" content="' . get_permalink() . '"/>';
    echo '<meta property="og:site_name" content="' . get_bloginfo('', 'string') . '"/>';
    if (!has_post_thumbnail($post->ID)) { //the post does not have featured image, use a default image
        $default_image = "http://example.com/image.jpg"; //replace this with a default image on your server or an image in your media library
        echo '<meta property="og:image" content="' . $default_image . '"/>';
    } else {
        $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
        if (is_array($thumbnail_src) && $thumbnail_src[0]) {
            echo '<meta property="og:image" content="' . esc_attr($thumbnail_src[0]) . '"/>';
        }
    }
    echo "";
}

add_action('wp_head', 'insert_fb_in_head', 5);

add_action('wp_head', 'show_template');
function show_template()
{
    global $template;
    if (isset($_GET['whichtemp']) || $_SERVER['HTTP_HOST'] == "localhost") {
        echo ' current template: ' . basename($template);
    }
}

//extending search for CPT listing
//add_filter( 'posts_join', 'extending_listing_admin_search_join' );
function extending_listing_admin_search_join($join)
{
    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "listing".
    if (is_admin() && 'edit.php' === $pagenow && 'listing' === @$_GET['post_type'] && !empty($_GET['s'])) {
        $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
}

add_filter('posts_where', 'extending_listing_search_where');
function extending_listing_search_where($where)
{
    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "listing".
    if (is_admin() && 'edit.php' === $pagenow && 'listing' === @$_GET['post_type'] && !empty(@$_GET['s'])) {
        $post_status = isset($_GET['post_status']) ? $_GET['post_status'] : 'any';
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            " post_status = '" . $post_status . "' OR (" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->posts . ".ID LIKE $1) ",
            $where
        );
    }
    return $where;
}

add_filter('posts_orderby', 'extend_listing_orderby');
function extend_listing_orderby($orderby_statement)
{
    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "listing".
    if (is_admin() && 'edit.php' === $pagenow && 'listing' === @$_GET['post_type']) {
        if (!isset($_REQUEST['orderby'])) {
            $orderby_statement = $wpdb->posts . ".ID DESC";
        }
    }
    return $orderby_statement;
}

function update_homey_membership_plan($post_ID, $post_after, $post_before)
{
    if ($post_after->post_type == 'hm_homey_memberships') {
        $hm_options = get_option('hm_memberships_options');

        $currency = isset($hm_options['currency']) ? $hm_options['currency'] : 'USD';

        delete_option($post_ID . '_' . $hm_options['paypal_client_id']); // to delete plan for paypal
        delete_option('hm_prod_id_' . $hm_options['paypal_client_id']); // to delete plan for paypal

        delete_option($post_ID . '_' . $hm_options['stripe_pk'] . '_' . $currency); //to delete plan for stripe
        delete_option('hmStripePid_' . $hm_options['stripe_pk']); //to delete plan for stripe
    }
}

add_action('post_updated', 'update_homey_membership_plan', 10, 3);

function homey_listing_image_dimension($file)
{

    $img = getimagesize($file['tmp_name']);
    $dimensions = explode('x', homey_option('upload_image_min_dimensions'));
    $width = isset($dimensions[0]) ? (int)$dimensions[0] : 1200;
    $heigth = isset($dimensions[1]) ? (int)$dimensions[1] : 640;

    $minimum = array('width' => $width, 'height' => $heigth);
    $width = $img[0];
    $height = $img[1];

    if ($width < $minimum['width'] || $height < $minimum['height']) {
        return -1;
    }

    return 1;
}

function homey_experience_image_dimension($file)
{

    $img = getimagesize($file['tmp_name']);
    $dimensions = explode('x', homey_option('upload_image_min_dimensions'));
    $width = isset($dimensions[0]) ? (int)$dimensions[0] : 1200;
    $heigth = isset($dimensions[1]) ? (int)$dimensions[1] : 640;

    $minimum = array('width' => $width, 'height' => $heigth);
    $width = $img[0];
    $height = $img[1];

    if ($width < $minimum['width'] || $height < $minimum['height']) {
        return -1;
    }

    return 1;
}

if (!function_exists('fancybox_gallery_html')) {
    function fancybox_gallery_html($images = null, $gallery_class = null)
    {
        $html = '';
        foreach ($images as $image) {
            $html .= '<a style="display:none;" href="' . esc_url($image['full_url']) . '" class="' . $gallery_class . '">
                <img class="img-responsive" data-lazy="' . esc_url($image['url']) . '" src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">
            </a>';
        }
        echo $html;
    }
}

if (isset($_GET['all_export_ics'])) {
    $args = array(
        'post_type' => 'listing',
    );
    $urls_html = '';
    $listing_qry = new WP_Query($args);

    while ($listing_qry->have_posts()) {
        $listing_qry->the_post();
        $listing_id = get_the_ID();

        $iCalendar = "BEGIN:VCALENDAR\r\n";
        $iCalendar .= "PRODID:-//Booking Calendar//EN\r\n";
        $iCalendar .= "VERSION:2.0";
        $iCalendar .= homey_get_booked_dates_for_icalendar($listing_id);
        $iCalendar .= homey_get_unavailable_dates_for_icalendar($listing_id);
        $iCalendar .= "\r\n";
        $iCalendar .= "END:VCALENDAR";

        $base_folder_path = WP_CONTENT_DIR . "/uploads/listings-calendars/";
        $upload_folder = $base_folder_path;

        if (!file_exists($upload_folder)) {
            mkdir($upload_folder, 0777, true);
        }

        $filename_to_be_saved = $listing_id . '-' . date("Y") . '-' . date("m") . '-' . date("d") . ".ics";
        $upload_url = content_url() . "/uploads/listings-calendars/{$filename_to_be_saved}";

        file_put_contents($upload_folder . $filename_to_be_saved, $iCalendar);

        echo $upload_url . '<br>';

        $ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        $urls_html = '';
        foreach ($ical_feeds_meta as $key => $value) {
            $urls_html .= $value['feed_name'] . ' - ' . $value['feed_url'];
            $urls_html .= "<br>";
        }
        $filename_to_be_saved = 'feeds-urls-' . $listing_id . '-' . date("Y") . '-' . date("m") . '-' . date("d") . ".html";
        $upload_url = content_url() . "/uploads/listings-calendars/{$filename_to_be_saved}";

        file_put_contents($upload_folder . $filename_to_be_saved, $urls_html);

        echo 'listing ID# ' . $listing_id . ' feeds urls in - > ' . $upload_url . '<br>';
    }

    echo 'all listings exported in /uploads/listings-calendars';
    exit();
}

add_action('wp_ajax_nopriv_homey_booking_notification', 'homey_booking_notification');
add_action('wp_ajax_homey_booking_notification', 'homey_booking_notification');

if (!function_exists('homey_booking_notification')) {
    function homey_booking_notification($html = 0)
    {
        global $wpdb;

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;

        $notification_data = array(
            'success' => true,
            'notification' => false
        );

        $tabel = $wpdb->prefix . 'posts';
        $tabel2 = $wpdb->prefix . 'postmeta';

        $new_bookings = $wpdb->get_results(
            "
			SELECT *, count(*) as new_bookings 
			FROM $tabel as t1
			INNER JOIN $tabel2 as t2 ON t2.post_id = t1.ID 
			INNER JOIN $tabel2 as t3 ON t3.post_id = t1.ID 
			WHERE 
			      t1.post_type = 'homey_reservation' 
			      AND t1.ID = t2.post_id  
			      AND (t2.meta_key = 'listing_owner' AND t2.meta_value = '$userID')
			      AND (t3.meta_key = 'reservation_status' AND t3.meta_value = 'under_review')
		  "
        );

        if (isset($new_bookings[0]->new_bookings)) {
            if ($new_bookings[0]->new_bookings > 0) {

                if ($html > 0) {
                    return $new_bookings[0]->new_bookings;
                }

                $notification_data = array(
                    'success' => true,
                    'notification' => true
                );
            }
        }

        if ($html > 0) {
            return 0;
        } else {
            echo json_encode($notification_data);
            wp_die();
        }
    }
}

if (!function_exists('wc_get_invoice_id_using_wc_orderNum')) {
    function wc_get_invoice_id_using_wc_orderNum($wc_order_id)
    {
        global $wpdb;
        $tbl = $wpdb->prefix . 'postmeta';
        $prepare_guery = $wpdb->prepare("SELECT post_id FROM $tbl where meta_key ='wc_reference_order_id' and meta_value = '%s'", $wc_order_id);

        $get_values = $wpdb->get_col($prepare_guery);
        $invoice_id = -1;

        error_log(print_r($get_values, true));

        if (isset($get_values[0])) {
            $lastIndex = count($get_values) - 1;
            $invoice_id = $get_values[$lastIndex];
        }

        return $invoice_id;
    }
}

if (!function_exists('change_invoice_view_link')) {
    add_filter('post_row_actions', 'change_invoice_view_link', 10, 1);
    function change_invoice_view_link($actions)
    {
        if (get_post_type() === 'homey_invoice') {
            global $post;

            $dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');
            $actions['view'] = '<a href="' . $dashboard_invoices . '?invoice_id=' . $post->ID . '">View</a>';

            return $actions;
        }
        return $actions;
    }
}

if (isset($_GET['debugme'])) {
    homey_import_icalendar_feeds(16640);
}

function translate_month_names($translated)
{
    $text = array(
        'January' => esc_html__('January', 'homey'),
        'February' => esc_html__('February', 'homey'),
        'March' => esc_html__('March', 'homey'),
        'April' => esc_html__('April', 'homey'),
        'May' => esc_html__('May', 'homey'),
        'June' => esc_html__('June', 'homey'),
        'July' => esc_html__('July', 'homey'),
        'August' => esc_html__('August', 'homey'),
        'Septmber' => esc_html__('Septmebr', 'homey'),
        'October' => esc_html__('October', 'homey'),
        'November' => esc_html__('November', 'homey'),
        'Decemeber' => esc_html__('December', 'homey'),

    );
    return str_ireplace(array_keys($text), $text, $translated);
}


// zk. added to add translation in titles of wordpress

add_action(
    'admin_head-edit.php',
    'homey_custom_invoice_translate_title'
);


function homey_custom_invoice_translate_title($columns)
{
    add_filter(
        'the_title',
        'homey_custom_invoice_translate_title_do',
        100,
        2
    );
}

function homey_custom_invoice_translate_title_do($title, $id = '')
{
    $title_words_array = explode(' ', $title);

    $title_new = '';
    foreach ($title_words_array as $word) {
        $title_new .= esc_html__(trim($word), 'homey') . ' ';
    }

    return $title_new;
}

if (!function_exists('for_reservation_nop_auto_login')) {
    function for_reservation_nop_auto_login($user)
    {
        wp_set_current_user($user->ID, $user->data->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->data->user_login, $user);

        // remove filter to work proper with other login.
        remove_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
    }
}
// / zk. added to add translation in titles of wordpress

add_action('redux/options/homey_options/saved', 'homey_save_custom_options_for_cron');
if (!function_exists('homey_save_custom_options_for_cron')) {
    function homey_save_custom_options_for_cron()
    {
        $email_content = homey_option('email_footer_content');
        $email_head_bg_color = homey_option('email_head_bg_color');;
        $email_foot_bg_color = homey_option('email_foot_bg_color');;
        $email_head_logo = homey_option('email_head_logo', false, 'url');

        update_option('homey_email_footer_content', $email_content);
        update_option('homey_email_head_logo', $email_head_logo);
        update_option('homey_email_head_bg_color', $email_head_bg_color);
        update_option('homey_email_foot_bg_color', $email_foot_bg_color);
    }
}


if (!function_exists('is_invoice_paid_for_reservation')) {
    function is_invoice_paid_for_reservation($reserveration_id, $return_invoice_id = false)
    {
        global $wpdb;
        $tbl = $wpdb->prefix . 'postmeta';
        $prepare_guery = $wpdb->prepare("SELECT post_id FROM $tbl where meta_key ='homey_invoice_item_id' and meta_value = '%s'", $reserveration_id);

        $get_values = $wpdb->get_col($prepare_guery);

        if (isset($get_values[0])) {
            if ($return_invoice_id != false) {
                return $get_values[0];
            }
            return get_post_meta($get_values[0], 'invoice_payment_status', true);
        }

        return 0;
    }
}


if (!function_exists('dd')) {
    function dd($data, $exit = 1)
    {
        echo '<pre>';
        print_r($data);

        if ($exit) {
            exit;
        }
    }
}

if (isset($_GET['localtest'])) {
    homey_reservation_declined_callback();
}

if (!function_exists('manager_author_editor')) {
    function manager_author_editor()
    {
        $users = get_users(['role__in' => ['homey_host'], 'role__not_in' => ['editor'], 'blog_id' => get_current_blog_id()]);
        foreach ($users as $user) {
            $user->add_role('editor');
        }
    }

    add_action('admin_init', 'manager_author_editor');
}

if (!function_exists('homey_default_feature_post_meta_for_listings')) {
    function homey_default_feature_post_meta_for_listings()
    {
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1
        );

        $listings_qry = new WP_Query($args);
        if ($listings_qry->have_posts()) {
            while ($listings_qry->have_posts()): $listings_qry->the_post();

                $post = get_post();
                $post_meta = get_post_meta($post->ID, 'homey_featured', true);

                //var_dump($post_meta);
                // echo 'above is the post meta data_value <br>';

                if ($post_meta != 1) { // to get in if only not set or it is null
                    echo 'already done ' . get_post_meta($post->ID, 'homey_featured', true);
                    update_post_meta($post->ID, 'homey_featured', 0);
                    echo ' the post id which was orphan, but now it is okay => , ' . $post->ID . '<pre>';
                    // print_r($post_meta);
                } else {
                    update_post_meta($post->ID, 'homey_featured', 1);
                    echo ' Good was => , ' . $post->ID . '<pre>';
                }

            endwhile;
        }
        exit('That should be okay for featured reset for listings.');
    }
}

if (!function_exists('homey_default_pet_post_meta_for_listings')) {
    function homey_default_pet_post_meta_for_listings()
    {
        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1
        );

        $listings_qry = new WP_Query($args);
        if ($listings_qry->have_posts()) {
            while ($listings_qry->have_posts()): $listings_qry->the_post();

                $post = get_post();
                $post_meta_pet = get_post_meta($post->ID, 'homey_pets', true);
                if ($post_meta_pet != 1) { // to get in if only not set or it is null
                    echo 'already done ' . get_post_meta($post->ID, 'homey_pets', true);
                    update_post_meta($post->ID, 'homey_pets', 0);
                    echo ' the post id which was orphan, but now it is okay => , ' . $post->ID . '<pre>';
                    // print_r($post_meta);
                } else {
                    update_post_meta($post->ID, 'homey_pets', 1);
                    echo ' Good was => , ' . $post->ID . '<pre>';
                }

            endwhile;
        }
        exit('That should be okay for pet reset for listings.');
    }
}

if (isset($_GET['neutral_featured_listings'])) {
    homey_default_feature_post_meta_for_listings();
    //exit;
}

if (isset($_GET['neutral_pet_listings'])) {
    homey_default_pet_post_meta_for_listings();
    //exit;
}

if (!function_exists('homey_clear_orphan_postmeta_records')) {
    function homey_clear_orphan_postmeta_records()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $delete_query = 'DELETE pm FROM ' . $prefix . 'postmeta pm LEFT JOIN ' . $prefix . 'posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL';
        $wpdb->query($delete_query);
    }
}

if (!function_exists('homey_delete_all_records')) {
    function homey_delete_all_records()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        echo $delete_query = 'DELETE pm FROM ' . $prefix . 'postmeta pm LEFT JOIN ' . $prefix . 'posts wp ON wp.ID = pm.post_id WHERE wp.post_type IN ("homey_invoice", "homey_reservation")';
        $wpdb->query($delete_query);
    }
}

if (isset($_GET['clear_orphan_postmeta'])) {
    homey_clear_orphan_postmeta_records();
}

if (isset($_GET['homey_delete_all_records'])) {
    homey_delete_all_records();
}

homey_insert_icalendar_feeds('', '', '');

if (!function_exists('remainingAttendeeSlots')) {
    function remainingAttendeeSlots($total_no_of_attendee, $check_in_unix, $reservation_booked_array = array(), $reservation_pending_array = array())
    {
        return $total_no_of_attendee - (isset($reservation_booked_array[$check_in_unix]) ? $reservation_booked_array[$check_in_unix]['no_of_attendee'] : 0) - (isset($reservation_pending_array[$check_in_unix]) ? $reservation_pending_array[$check_in_unix]['no_of_attendee'] : 0);
    }
}

if (!function_exists('add_exp_column')) {
    function add_exp_column()
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "homey_threads ADD experience_id INT(11) NOT NULL DEFAULT 0");
        return 1;
    }
}

if (isset($_GET['add_column_exp'])) {
    add_exp_column();
}

add_action('woocommerce_after_cart_item_quantity_update', 'update_cart_items_quantities', 10, 4);
function update_cart_items_quantities($cart_item_key, $quantity, $old_quantity, $cart)
{
    $cart_data = $cart->get_cart();
    $cart_item = $cart_data[$cart_item_key];
    $manage_stock = $cart_item['data']->get_manage_stock();
    $product_stock = $cart_item['data']->get_stock_quantity();

    // Zero or negative stock (remove the product)
    if ($product_stock <= 0 && $manage_stock) {
        unset($cart->cart_contents[$cart_item_key]);
    }
    if ($quantity > $product_stock && $manage_stock) {
        $cart->cart_contents[$cart_item_key]['quantity'] = $product_stock;
    }
    return $product_stock;
}

function check_values($post_ID, $post_after, $post_before)
{
    if ($post_after->post_type == 'listing') {
    }
}

add_action('post_updated', 'check_values', 10, 3);

if (!function_exists('homey_write_log')) {
    function homey_write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }

            global $current_user;
            $current_user = wp_get_current_user();
            $userID = $current_user->ID;
            error_log("Bonus user check: " . $userID);
        }
    }
}

// delete reservation dates button click
if (isset($_GET['delete_reservation_id'])) {
    if (isset($_GET['edit_listing'])) {
        $show_error = isset($_GET['show_error']) ? 1 : 0;
        $resID = $_GET['delete_reservation_id'];
        $listing_id = $_GET['edit_listing'];
        // reservation dates
        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);
        if (!is_array($booked_dates_array) || empty($booked_dates_array)) {
            $booked_dates_array = array();
        }

        $removed_booked_dates_reservation = [];
        if (count($booked_dates_array) > 0) {
            foreach ($booked_dates_array as $key => $currentResId) {

                if ($resID == $currentResId) {
                    $removed_booked_dates_reservation[] = $currentResId;
                    unset($booked_dates_array[$key]);
                }
            }

            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);
        }
        //end of reservation dates

        // reservation pending dates
        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if (!is_array($pending_dates_array) || empty($pending_dates_array)) {
            $pending_dates_array = array();
        }

        $removed_pending_dates_reservation = [];
        if (count($pending_dates_array) > 0) {
            foreach ($pending_dates_array as $key => $currentResId) {

                if ($resID == $currentResId) {
                    $removed_pending_dates_reservation[] = $currentResId;
                    unset($pending_dates_array[$key]);
                }
            }

            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);
        }
        // end of reservation pending dates

        $notification_data = array(
            'success' => true,
            'removed_booked_dates_reservation' => $removed_booked_dates_reservation,
            'removed_pending_dates_reservation' => $removed_pending_dates_reservation
        );

        // delete reservation record from post and its postmeta records
        global $wpdb;
        $prefix = $wpdb->prefix;
        $delete_query = 'delete from ' . $prefix . 'posts where ID = ' . $resID;
        $delete_meta_query = 'delete from ' . $prefix . 'postmeta where post_id =' . $resID;
        $wpdb->query($delete_query);
        $wpdb->query($delete_meta_query);

        if ($show_error == 1) {
            echo json_encode($notification_data);
            wp_die();
        }

        $url_to_back = wp_get_referer();
        echo esc_html__('Redirecting to back to, ' . $url_to_back, 'homey');
        wp_redirect($url_to_back);
        //wp_die();

    }

    if (isset($_GET['edit_experience']) && isset($_GET['delete_reservation_id'])) {
        $show_error = isset($_GET['show_error']) ? 1 : 0;
        $resID = $_GET['delete_reservation_id'];
        $experience_id = $_GET['edit_experience'];
        // reservation dates
        $booked_dates = get_post_meta($experience_id, 'reservation_dates', true);
        if (is_array($booked_dates)) {
            foreach ($booked_dates as $key => $booked_date) {
                $booked_dates_array[$key]['reservation_ids'] = str_replace([$resID . ',', $resID], '', $booked_dates[$key]['reservation_ids']);

                $reservation_guests = get_post_meta($resID, 'reservation_guests', true); // get reservation slots from database

                $booked_dates_array[$key]['no_of_attendee'] = $booked_dates[$key]['no_of_attendee'] - (int)$reservation_guests;

                if (trim($booked_dates_array[$key]['reservation_ids']) == '') {
                    $booked_dates_array = '';
                }

                update_post_meta($experience_id, 'reservation_dates', $booked_dates_array);
            }
        }

        // delete reservation record from post and its postmeta records
        global $wpdb;
        $prefix = $wpdb->prefix;
        $delete_query = 'delete from ' . $prefix . 'posts where ID = ' . $resID;
        $delete_meta_query = 'delete from ' . $prefix . 'postmeta where post_id =' . $resID;
        $wpdb->query($delete_query);
        $wpdb->query($delete_meta_query);

        if ($show_error == 1) {
            echo json_encode($notification_data);
            wp_die();
        }

        $url_to_back = wp_get_referer();
        echo esc_html__('Redirecting to back to, ' . $url_to_back, 'homey');
        wp_redirect($url_to_back);
        //wp_die();

    }
}
// delete reservation dates button click

if (!function_exists('homey_postmeta_min_max')) {
    function homey_postmeta_min_max($post_meta_key = '', $size = '', $listing_id = 0, $force_update_min_value = 0, $force_update_max_value = 0)
    { //beds, baths, guests
        global $wpdb;
        $min = $max = 1;

        if (get_option($size, -1) != -1 && $force_update_min_value != 0 && $force_update_max_value != 0) {
            update_option($size, $force_update_min_value);
            update_option($size, $force_update_max_value);
        }

        if ($listing_id == 0) {
            $query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", $post_meta_key);
        } else {
            $query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d", $post_meta_key, $listing_id);
        }

        $min_maxs = $wpdb->get_results($query);

        if (is_array($min_maxs) || is_object($min_maxs)) {
            foreach ($min_maxs as $min_max) {
                $current_min_max_num = $min_max->meta_value < 1 ? 1 : $min_max->meta_value;
                if ($min > $current_min_max_num) {
                    $min = $current_min_max_num;
                }

                if ($max < $current_min_max_num) {
                    $max = $current_min_max_num;
                }
            }

            update_option($size . '_min', $min);
            update_option($size . '_max', $max);
        } else {
            return 'no data found';
        }
    }
}

if (!function_exists('reset_min_max_postmeta')) {
    function reset_min_max_postmeta()
    {
        homey_postmeta_min_max('homey_night_price', "listing_night_price");
        homey_postmeta_min_max('homey_bedrooms', "listing_homey_bedrooms");
        homey_postmeta_min_max('homey_beds', "listing_homey_beds");
        homey_postmeta_min_max('homey_guests', "listing_homey_guests");
        homey_postmeta_min_max('homey_baths', "listing_homey_baths");

        homey_postmeta_min_max('homey_night_price', "experience_night_price");
        homey_postmeta_min_max('homey_guests', "experience_homey_guests");
    }
}

// this could be cron job to reset min and max or on new listing/experiences addition it could be run
if (isset($_GET['reset_min_max_postmeta'])) {
    reset_min_max_postmeta();
}

if (isset($_GET['ical_test'])) {
    ical_test();
}

// to not use the new widgets for homey theme, till the code is compatiable for new widgets.

add_filter('use_widgets_block_editor', '__return_false');

if (isset($_GET['wp_homey_map_exp'])) {
    global $wpdb;

    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "homey_map ADD COLUMN experience_id INT(11);");
}

// verification from admin side
add_filter('manage_users_columns', 'homey_usr_custom_column');
function homey_usr_custom_column($column)
{
    $column['verified'] = __('Status', 'userswp');
    return $column;
}

add_filter('manage_users_custom_column', 'homey_usr_custom_column_value', 10, 3);
function homey_usr_custom_column_value($val, $column_name, $user_id)
{
    switch ($column_name) {
        case 'verified':
            $verification_id = get_user_meta($user_id, 'verification_id', true);
            return !empty($verification_id) ? '<a style="cursor:pointer;" class="admin_verify_user_code_manually" data-user-id="' . $user_id . '" data-hash="' . $verification_id . '" href="javascript:void(0);">' . esc_html__('Click to verify', 'homey') . '</a>' : esc_html__('Verified', 'homey');
            break;
        default:
    }
    return $val;
}

// add column into users list

//homey_verify_user_manually
add_action('wp_ajax_homey_verify_user_manually', 'homey_verify_user_manually');
if (! function_exists('homey_verify_user_manually')) {
    function homey_verify_user_manually()
    {

        // Check if current user has admin rights
        if (! current_user_can('manage_options')) {
            echo json_encode(array(
                'success' => false,
                'reason'  => esc_html__('Not authorized!', 'homey')
            ));
            wp_die();
        }

        $nonce = $_REQUEST['security'];

        if (! wp_verify_nonce($nonce, 'manually_user_approve_nonce')) {
            echo json_encode(array(
                'success' => false,
                'reason'  => esc_html__('Security check failed!', 'homey')
            ));
            wp_die();
        }

        $notification_data = array(
            'success' => false,
            'user_id' => $_POST['user_id'],
            'text'    => esc_html__('Something went wrong! Try again.', 'homey')
        );

        if (isset($_POST['user_id'])) {
            // Optionally, you can also use your custom homey_is_admin() check if needed.
            if (homey_is_admin()) {
                update_user_meta($_POST['user_id'], 'verification_id', '');
                update_user_meta($_POST['user_id'], 'is_email_verified', 1);

                $notification_data = array(
                    'success' => true,
                    'text'    => esc_html__('Verified', 'homey')
                );
            }
        }

        echo json_encode($notification_data);
        wp_die();
    }
}

//homey_verify_user_manually

// verification from admin side
// add column into users list

if (!function_exists('cancel_subscriptions_of_user')) {
    //cancel_subscriptions_of_user
    add_action('wp_ajax_cancel_subscriptions_of_user', 'cancel_subscriptions_of_user');
    function cancel_subscriptions_of_user()
    {
        if (homey_is_admin() && isset($_POST['user_id']) && isset($_POST['subscriptionId'])) {

            require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');

            try {
                if (str_contains($_POST['subscriptionId'], 'free-pkg-')) {
                    // expire this as no need to go to stripe, it is already free subscription
                    post_actions_cancel_subscription($_POST['subscriptionId']);
                } else {
                    $hm_options = get_option('hm_memberships_options');

                    if (!empty($hm_options['stripe_sk'])) {
                        $stripe = new \Stripe\StripeClient(
                            $hm_options['stripe_sk']
                        );
                        $stripe->subscriptions->cancel($_POST['subscriptionId']);
                    }

                    post_actions_cancel_subscription($_POST['subscriptionId']);
                }

                $notification_data = array(
                    'success' => true,
                    'text' => esc_html__('Canceled', 'homey')
                );

                echo json_encode($notification_data);
                wp_die();
            } catch (Exception $e) {

                $notification_data = array(
                    'success' => false,
                    'text' => esc_html__('Not able to cancel.', 'homey')
                );

                echo json_encode($notification_data);
                wp_die();
            }
        }

        $notification_data = array(
            'success' => false,
            'text' => esc_html__('Not able to cancel.', 'homey')
        );

        echo json_encode($notification_data);
        wp_die();
    }
}

if (!function_exists('homey_cancel_memb_subscription')) {
    //cancel_subscriptions_of_user
    add_action('wp_ajax_nopriv_homey_cancel_memb_subscription', 'homey_cancel_memb_subscription');
    add_action('wp_ajax_homey_cancel_memb_subscription', 'homey_cancel_memb_subscription');
    function homey_cancel_memb_subscription()
    {
        if (homey_is_host()) {
            $users_subscriptions = homey_get_user_subscription(1, null, 'active');

            $currently_subscribed_plan = $currently_subscribed_id = -1;
            foreach ($users_subscriptions as $sub) {
                if (isset($sub['planID'])) {
                    $currently_subscribed_id = $sub['stripe_subscriptionID'];
                }
            }

            if ($currently_subscribed_id != -1) {
                require_once(HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php');

                try {
                    if (str_contains($currently_subscribed_id, 'free-pkg-')) {
                        // expire this as no need to go to stripe, it is already free subscription
                        post_actions_cancel_subscription($currently_subscribed_id);
                    } else {
                        $hm_options = get_option('hm_memberships_options');

                        if (!empty($hm_options['stripe_sk'])) {
                            $stripe = new \Stripe\StripeClient(
                                $hm_options['stripe_sk']
                            );
                            $stripe->subscriptions->cancel($currently_subscribed_id);
                        }

                        post_actions_cancel_subscription($currently_subscribed_id);
                    }

                    $notification_data = array(
                        'success' => true,
                        'text' => esc_html__('Canceled', 'homey')
                    );

                    echo json_encode($notification_data);
                    wp_die();
                } catch (Exception $e) {

                    $notification_data = array(
                        'success' => true,
                        'text' => esc_html__('Not able to cancel.', 'homey')
                    );

                    echo json_encode($notification_data);
                    wp_die();
                }
            }
        }

        $notification_data = array(
            'success' => false,
            'text' => esc_html__('Not able to cancel.', 'homey')
        );

        echo json_encode($notification_data);
        wp_die();
    }
}

if (!function_exists('post_actions_cancel_subscription')) {
    function post_actions_cancel_subscription($subscription_id = null)
    {
        if ($subscription_id != null) {
            global $wpdb;
            $tbl = $wpdb->prefix . 'postmeta';
            $prepare_guery = $wpdb->prepare("SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_sub_id' and meta_value = '%s'", $subscription_id);
            $posts = $wpdb->get_col($prepare_guery);
            clearance_membership_plan();

            $totalIndex = '';
            foreach ($posts as $k => $postId) {

                update_post_meta($postId, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s'));
                update_post_meta($postId, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s'));
                update_post_meta($postId, 'hm_subscription_detail_status', 'expired');
                $totalIndex .= $subscription_id . ' <> ' . $postId . ', ' . $k;
            }
        }
    }
}

//end of cancel_subscriptions_of_user
if (function_exists('membership_currency')) {
    function membership_currency($currency, $price, $separator = '')
    {
        if (!empty($separator)) {
            $price = number_format($price, 2, $separator, $separator);
            echo $currency . ' ' . $price;
        }
    }
}

function membership_currency_table($currency, $price, $separator = '')
{
    if (!empty($separator)) {
        $price = number_format((float) $price, 2, $separator, $separator);
        echo '<span class="price-table-currency">' . $currency . '</span>
             <span class="price-table-price">' . $price . '</span>';
    }
}


// Class in body for template v5 and v6
function template_version_class($classes)
{
    $detail_layout = homey_option('detail_layout');
    if (isset($_GET['detail_layout'])) {
        $detail_layout = $_GET['detail_layout'];
    }
    if ($detail_layout == 'v5' || $detail_layout == 'v6') {
        $classes[] = 'homey-listing-detail-' . $detail_layout;
    }

    return $classes;
}

add_filter('body_class', 'template_version_class');
// Class in body for template v5 and v6

if (!function_exists('homey_update_homey_review_custom_field')) {
    function homey_update_homey_review_custom_field()
    {
        // Check if the script has already been executed
        if (get_option('homey_review_custom_field_updated')) {
            return;
        }

        // Define the custom post type and custom field
        $post_type = 'homey_review';
        $custom_field_key = 'homey_where_to_display';
        $new_value = 'listing_detail_page';

        // Fetch all posts in the custom post type
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);

        // Loop through the posts and update the custom field
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                update_post_meta(get_the_ID(), $custom_field_key, $new_value);
            }
        }

        // Clean up after the loop
        wp_reset_postdata();

        // Set an option to indicate the script has been executed
        update_option('homey_review_custom_field_updated', 1);
    }

    // Execute the function
    add_action('init', 'homey_update_homey_review_custom_field');
}


//only_reservation_correction
if (isset($_GET['only_reservation_correction'])) {
    //http://www.localhost?only_reservation_correction=1&del_res_id=28367&list_id=9718
    echo '<pre> res meta => ';
    print_r(get_post_meta(28367));
    echo '<h1>this list</h1><pre> res meta => ';
    print_r(get_post_meta(9718));

    if (isset($_GET['del_res_id']) && $_GET['list_id']) {
        $resID = $_GET['del_res_id']; //28367 ;
        $listing_id = $_GET['list_id']; //9718;
        // reservation dates
        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);
        if (!is_array($booked_dates_array) || empty($booked_dates_array)) {
            $booked_dates_array = array();
        }

        $removed_booked_dates_reservation = [];
        if (count($booked_dates_array) > 0) {
            foreach ($booked_dates_array as $key => $currentResId) {

                if ($resID == $currentResId) {
                    $removed_booked_dates_reservation[] = $currentResId;
                    unset($booked_dates_array[$key]);
                }
            }

            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);
        }

        echo ' deleted itds => ';
        print_r($removed_booked_dates_reservation);
        //end of reservation dates
    }
}
//only_reservation_correction
if (!function_exists('is_listing_reservation')) {
    function is_listing_reservation($reservation_id = 0)
    {
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        if ($listing_id < 1) { // because reseravtion_experience_id so we can assume if this is reservation for experience or listing
            return 0;
        }
        return 1;
    }
}
// This was due to prevent spam, but it is creating other issues.
function prevent_user_registration($login, $email, $errors)
{
    // Always return false to prevent user registration using normal wordpress method.
    return false;
}

//add_filter('register_new_user', 'prevent_user_registration', 10, 3);

if (!function_exists('from_any_gmt_to_wp_setting_gmt')) {
    function from_any_gmt_to_wp_setting_gmt($dateWithFromDifferentGmt = '')
    {

        // Create a DateTime object for the Stripe datetime in GMT-0 timezone
        $stripeDateTimeObj = new DateTime($dateWithFromDifferentGmt);

        // Get the WordPress timezone
        $wordpressTimezone = get_option('timezone_string');

        // Create a DateTimeZone object for the WordPress timezone
        $wordpressTimezoneObj = new DateTimeZone($wordpressTimezone);

        // Set the timezone for the Stripe datetime to the WordPress timezone
        $stripeDateTimeObj->setTimezone($wordpressTimezoneObj);

        // Format the converted datetime as per your requirements
        $convertedDateTime = $stripeDateTimeObj->format('Y-m-d H:i:s');

        return $convertedDateTime;
    }
}

if (isset($_GET['actionEnable'])) {
    homey_ical_sync_callback();
}

/* To allow arabic username other than english */
add_filter('sanitize_user', 'non_strict_login', 10, 3);
function non_strict_login($username, $raw_username, $strict)
{

    if (!$strict)
        return $username;

    return sanitize_user(stripslashes($raw_username), false);
}

if (!function_exists('add_column_earnings')) {
    function add_column_earnings()
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE $wpdb->prefix.homey_earnings ADD experience_id INT(11) NOT NULL DEFAULT 0");
    }
}

if (isset($_GET['add_column_earnings'])) {
    add_column_earnings();
}

//new cron job for deleting spam users in 24 hours

$clear_unverified_users = (int) homey_option('homey_delete_spam_users', 0);

if (!function_exists('homey_delete_spam_users')) {
    function homey_delete_spam_users()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $spam_user_ids = $wpdb->get_results("SELECT " . $prefix . "users.ID  FROM " . $prefix . "users LEFT JOIN " . $prefix . "usermeta ON " . $prefix . "users.ID = " . $prefix . "usermeta.user_id WHERE (" . $prefix . "usermeta.meta_key = 'is_email_verified' AND " . $prefix . "usermeta.meta_value != 1) OR (" . $prefix . "usermeta.meta_key = 'verification_id' AND " . $prefix . "usermeta.meta_value != '')  AND " . $prefix . "users.user_registered < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $ids = array_map(function ($item) {
            return $item->ID;
        }, $spam_user_ids);

        $ids = implode(",", $ids);
        if (!empty($ids)) {
            $spam_user_ids = $wpdb->get_results("DELETE FROM " . $prefix . "users WHERE ID in (" . $ids . ");");
            print_r($ids);
        } else {
            echo esc_html__('All users are updated', 'homey');
        }
    }
}

if (! wp_next_scheduled('homey_delete_spam_users') && $clear_unverified_users != 0) {
    add_action('homey_delete_spam_users', 'homey_delete_spam_users');
    wp_schedule_event(time(), 'daily', 'homey_delete_spam_users');
}

if (isset($_GET['clear_spam_or_not_verified_users'])) {
    homey_delete_spam_users();
}
//new cron job for deleting spam users in 24 hours
if (!function_exists('recaptcha_check_for_no_ajax')) {
    function recaptcha_check_for_no_ajax()
    {
        $recaptha_secret_key = homey_option('recaptha_secret_key');
        $enable_reCaptcha = homey_option('enable_reCaptcha');

        if ($enable_reCaptcha != 1) {
            return true;
        }

        // include library https://github.com/google/recaptcha
        include_once(HOMEY_PLUGIN_PATH . '/includes/recaptcha/src/autoload.php');

        // If the form submission includes the "g-captcha-response" field
        // Create an instance of the service using your secret

        $recaptcha = new \ReCaptcha\ReCaptcha($recaptha_secret_key, new \ReCaptcha\RequestMethod\CurlPost());

        // If file_get_contents() is locked down on your PHP installation to disallow
        // its use with URLs, then you can use the alternative request method instead.
        // This makes use of fsockopen() instead.

        // Make the call to verify the response and also pass the user's IP address
        $resp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            return true;
        }
        return false;
    }
}
// homey login no ajax
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'homey_login_no_ajax') {
    $login_redirect_type = homey_option('login_redirect');
    $login_redirect = homey_after_login_redirect_page();
    $wp_http_referer_value = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

    $username = $_REQUEST['username'];
    $check_user_active = get_user_by('login', $username);

    if (isset($check_user_active->ID)) {
        $user_is_verified = get_user_meta($check_user_active->ID, 'verification_id', 1);
        if ($user_is_verified != '') {
            $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
            header("Location: $wp_http_referer_value" . "$separator" . 'n=' . time() . '&not-verified=1&no-login-err=1');
            exit;
        }
    }

    // Check if the user is already logged in
    if (is_user_logged_in()) {
        // User is already logged in
        $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
        $separator = str_replace('&no-login-err=1', '', $separator);

        header("Location: $wp_http_referer_value" . "$separator" . 'n=' . time());
        exit;
    } else {
        if (recaptcha_check_for_no_ajax()) {
            wp_clear_auth_cookie();

            // User credentials
            $creds = array(
                'user_login' => $_REQUEST['username'],    // replace with the username
                'user_password' => $_REQUEST['password'],    // replace with the password
                'remember' => isset($_REQUEST['remember']) && $_REQUEST['remember'] == 'on' ? true : false,
            );

            // Sign on the user
            $user = wp_signon($creds, false);

            // Check if the login was successful
            if (is_wp_error($user)) {
                $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
                header("Location: $wp_http_referer_value" . "$separator" . 'n=' . time() . '&no-login-err=1');
                exit;
            } else {
                // Login successful
                if ($wp_http_referer_value !== '/' && $login_redirect_type == 'same_page') {
                    $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
                    $separator = str_replace('&no-login-err=1', '', $separator);
                    header("Location: $wp_http_referer_value" . "$separator" . 'n=' . time());
                    exit;
                }

                if ($login_redirect_type == 'same_page') {
                    $url_to_redirect = str_replace('&no-login-err=1', '', $_SERVER['REQUEST_URI']);
                    header("Location: " . $url_to_redirect);
                    exit;
                } else {
                    $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
                    $redirect_separator = (strpos($login_redirect, "?") !== false) ? '&' : '?';
                    $redirect_separator = str_replace('&no-login-err=1', '', $redirect_separator);

                    header("Location: $login_redirect" . "$redirect_separator" . 'n=' . time());
                    exit;
                }
            }
        } else {
            $separator = (strpos($wp_http_referer_value, "?") !== false) ? '&' : '?';
            header("Location: $wp_http_referer_value" . "$separator" . 'n=' . time() . '&re-captcha-error=1&no-login-err=1');
            exit;
        }
    }
}

if (isset($_GET['no-login-err'])) {
    if (!function_exists('homey_no_ajax_login_modal_show')) {
        function homey_no_ajax_login_modal_show()
        {
            if (!is_user_logged_in()) {
                $error_msg = esc_html__('Please check your credentials or contact the admin.', 'homey');
                if (isset($_GET['not-verified'])) {
                    $error_msg = __('Your profile is not activated. Please check your email inbox or spam to find the activation link.', 'homey-login-register');
                }

                if (isset($_GET['re-captcha-error'])) {
                    $error_msg = __('Please make sure reCaptcha is correct..', 'homey-login-register');
                }
?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var loginLink = document.querySelector('[data-target="#modal-login"]');
                        var msg_elements = document.getElementsByClassName('homey_login_messages');
                        for (var i = 0; i < msg_elements.length; i++) {
                            var currentElement = msg_elements[i];
                            currentElement.innerText = '<?php echo $error_msg; ?>'; // Or use innerHTML if you want to set HTML content
                        }

                        if (loginLink) {
                            // Trigger a click on the link
                            loginLink.click();
                        }
                    });
                </script>
<?php }
        }
    }

    add_action('wp_footer', 'homey_no_ajax_login_modal_show');
}

//homey login no ajax

if (isset($_GET['homey_move_dates_to_book_res_id'])) {
    homey_move_dates_to_book($_GET['homey_move_dates_to_book_res_id']);
}

// Redirect non-admin users after login
function redirect_non_admin_login($redirect_to, $request, $user)
{
    // Check if the user is not an administrator
    if (isset($user->roles) && ! in_array('administrator', $user->roles)) {
        // Redirect non-admin users to the homepage
        return home_url();
    }
    // Admins will go to the default admin dashboard
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_non_admin_login', 10, 3);

if (!function_exists('deHashNoUserId')) {
    function deHashNoUserId($userId = 0)
    {
        if ($userId > 0) {
            $userId = $userId - 1111;
        }

        return $userId;
    }
}

if (!function_exists('hashNoUserId')) {
    function hashNoUserId($userId)
    {
        if ($userId > 0) {
            $userId = $userId + 1111;
        }

        return $userId;
    }
}

if (isset($_GET['ical'])) {

    ical_test();
}

function homey_get_current_url()
{
    global $wp;

    // Get the protocol (http or https)
    $protocol = (is_ssl() ? 'https' : 'http') . '://';

    // Get the host name (e.g., localhost or www.example.com)
    $host = $_SERVER['HTTP_HOST'];

    // Get the current request (e.g., /homey-2-5-1/reservations/page/2/)
    $request = $_SERVER['REQUEST_URI'];

    // Construct the full URL
    $current_url = $protocol . $host . $request;

    return $current_url;
}

//homey-images-for-listing using wp-all-import
function import_external_image($image_url, $post_id)
{
    // Check if the URL is valid
    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
        // Download the image
        $image_data = file_get_contents($image_url);
        $filename = basename(parse_url($image_url, PHP_URL_PATH));

        // Check if the image download was successful
        if ($image_data !== false) {
            // Create an attachment
            $upload_dir = wp_upload_dir();
            $image_file = $upload_dir['path'] . '/' . $filename;
            file_put_contents($image_file, $image_data);

            $attachment = array(
                'post_mime_type' => wp_check_filetype($filename)['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $image_file, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Return the attachment ID
            return $attach_id;
        }
    }
    return '';
}

if (!function_exists('wpai_import_custom_images_saved_post')) {
    // Register the function for use in WP All Import
    //add_action('pmxi_after_post_import', 'wpai_import_custom_images_after_import', 10, 3);
    add_action('pmxi_saved_post', 'wpai_import_custom_images_saved_post', 10, 3);

    function wpai_import_custom_images_saved_post($post_id, $xml, $is_update)
    {
        // Assuming the XML node is <image_urls> and URLs are comma-separated
        $image_urls = (string)$xml->image_urls;
        $image_urls_array = array_map('trim', explode(',', $image_urls));

        $attachment_ids = array();

        foreach ($image_urls_array as $image_url) {
            if (!empty($image_url)) {
                // Import the image and get the attachment ID
                $attachment_id = import_external_image($image_url, $post_id);
                if ($attachment_id) {
                    $attachment_ids[] = $attachment_id;
                }
            }
        }

        // Update the custom field with the attachment IDs
        if (!empty($attachment_ids)) {
            update_post_meta($post_id, 'homey_listing_images', $attachment_ids);
        }
    }
}

if (!function_exists('add_reservation_listing_meta_reservation_dates_manual')) {
    function add_reservation_listing_meta_reservation_dates_manual($listing_id = 0, $reservation_id = 0)
    {
        //Book dates
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id, 1);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);
        echo '<pre>Reservatin meta: ';
        print_r(get_post_meta($reservation_id));
        echo '<pre>dates data';
        print_r($booked_days_array);
        dd('Reservation dates updated, please remove parameters and refresh the detail page of listing.');
    }
}

if (isset($_GET['manualAddDatesInListingMetaListId'])) {
    add_reservation_listing_meta_reservation_dates_manual($_GET['manualAddDatesInListingMetaListId'], $_GET['manualAddDatesInListingMetaResId']);
}

if (!function_exists('homey_remove_spam_user_filter_wp_mail')) {
    function homey_remove_spam_user_filter_wp_mail($args)
    {
        $homey_spam_filters = homey_option('spam_keywords_to_stop_fake_users', 'blogspot, telegram');
        $homey_spam_filters = explode(',', $homey_spam_filters);
        $homey_subject_for_new_user = homey_option('homey_subject_new_user_register');
        $homey_subject_for_new_user = str_replace('{site_title}', get_bloginfo('name'), $homey_subject_for_new_user);

        if (strpos($args['subject'], $homey_subject_for_new_user) !== false) { // Only to new user register emails

            // Extract the 'to' field
            $to_emails = is_array($args['to']) ? $args['to'] : explode(',', $args['to']);

            error_log("To: " . implode(', ', $to_emails));

            $is_spam = false;

            if (strtolower(trim($args['subject'])) !== strtolower(trim($homey_subject_for_new_user))) {
                $is_spam = true;
            }

            // Check subject and to email for spam keywords
            foreach ($homey_spam_filters as $text_to_search) {
                $text_to_search = trim($text_to_search); // Trim any extra whitespace
                foreach ($to_emails as $to_email) {
                    error_log($to_email . ' >> ' . $text_to_search);
                    if (strpos($args['subject'], $text_to_search) !== false || strpos($to_email, $text_to_search) !== false) {
                        $is_spam = true;
                        break 2; // No need to check other filters if one is matched
                    }
                }
            }

            // Additional checks for spam
            if (!$is_spam) {
                // Check the body for spam keywords
                foreach ($homey_spam_filters as $text_to_search) {
                    $text_to_search = trim($text_to_search); // Trim any extra whitespace
                    if (strpos($args['message'], $text_to_search) !== false) {
                        $is_spam = true;
                        break;
                    }
                }
            }

            // If spam is detected, stop sending the email and delete the user
            if ($is_spam) {
                $args['to'] = '';

                foreach ($to_emails as $to_email) {
                    $user = get_user_by('email', $to_email);
                    if ($to_email && $user) {
                        error_log('Spam detected and user deleted: ' . $to_email);
                        wp_delete_user($user->ID);
                    }
                }
            }
        }

        return $args;
    }
}
add_filter('wp_mail', 'homey_remove_spam_user_filter_wp_mail');

if (!function_exists('homey_get_all_users_select')) {
    function homey_get_all_users_select($selected_user_id = 0)
    {
        $users = get_users();
        $output = '<select name="user_dropdown" id="user_dropdown">';
        $output .= '<option value="">Select Renter</option>';

        foreach ($users as $user) {
            $selected = ($user->ID == $selected_user_id) ? 'selected="selected"' : '';
            $output .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
        }

        $output .= '</select>';
        return $output;
    }
}

if (!function_exists('homey_get_all_listings_select')) {
    function homey_get_all_listings_select($selected_listing_id = 0)
    {
        $args = array(
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        $posts = get_posts($args);
        $output = '<select name="posts_dropdown" id="posts_dropdown">';
        $output .= '<option value="">Select a listing</option>';

        foreach ($posts as $post) {
            $selected = ($post->ID == $selected_listing_id) ? 'selected="selected"' : '';
            $output .= '<option value="' . esc_attr($post->ID) . '" ' . $selected . '>' . esc_html($post->post_title) . '</option>';
        }

        $output .= '</select>';

        return $output;
    }
}

if (!function_exists('homey_is_listing_owner')) {
    function homey_is_listing_owner($listing_id = 0, $current_user_id = 0)
    {
        if ($current_user_id == 0) {
            $current_user = wp_get_current_user();
            $current_user_id = $current_user->ID;
        }

        $listing_owner_id = get_post_field('post_author', $listing_id);
        if (homey_is_admin() || $listing_owner_id == $current_user_id) {
            return 1;
        }

        return 0;
    }
}

if (!function_exists('homey_search_wp_users')) {
    function homey_search_wp_users($search_term, $role = '')
    {
        global $wpdb;

        // Prepare the search term for SQL
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';

        // Base SQL query to search in wp_users and wp_usermeta tables and include roles
        $query = "
        SELECT DISTINCT u.ID, u.user_login, u.display_name, u.user_email, um_roles.meta_value AS roles
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
        LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'nickname'
        LEFT JOIN {$wpdb->usermeta} um_roles ON u.ID = um_roles.user_id AND um_roles.meta_key = '{$wpdb->prefix}capabilities'
        WHERE (
            u.user_login LIKE %s OR
            u.display_name LIKE %s OR
            u.user_email LIKE %s OR
            um1.meta_value LIKE %s OR
            um2.meta_value LIKE %s OR
            um3.meta_value LIKE %s
        )
    ";

        // If a role is specified, add a condition to the query
        if (!empty($role)) {
            $query .= " AND um_roles.meta_value LIKE %s";
            $query = $wpdb->prepare($query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, '%"' . $role . '"%');
        } else {
            $query = $wpdb->prepare($query, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
        }

        // Execute the query
        $results = $wpdb->get_results($query);

        // Process roles and unserialize them
        foreach ($results as $user) {
            $user->roles = maybe_unserialize($user->roles);
        }

        return $results;
    }
}

// All listings for PINS
if (!function_exists('all_listings_for_pin')) {
    function all_listings_for_pin()
    {
        $query_args = array(
            'post_type'      => 'listing', // Specify the custom post type
            'posts_per_page' => -1,        // Retrieve all posts
            'post_status'    => 'publish', // Only published posts
        );

        global $homey_prefix, $homey_local, $template_args;

        $homey_prefix = 'homey_';
        $homey_local = homey_get_localization();

        $cgl_beds = homey_option('cgl_beds');
        $cgl_baths = homey_option('cgl_baths');
        $cgl_guests = homey_option('cgl_guests');
        $cgl_types = homey_option('cgl_types');
        $price_separator = homey_option('currency_separator');

        $arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
        $depart = isset($_POST['depart']) ? $_POST['depart'] : '';
        $query_args = new WP_Query($query_args);
        $all_listings_for_pins = [];

        while ($query_args->have_posts()): $query_args->the_post();

            $listing_id = get_the_ID();
            $address        = get_post_meta(get_the_ID(), $homey_prefix . 'listing_address', true);
            $bedrooms       = get_post_meta(get_the_ID(), $homey_prefix . 'listing_bedrooms', true);

            $guests         = get_post_meta(get_the_ID(), $homey_prefix . 'guests', true);
            $additional_guests         = get_post_meta(get_the_ID(), $homey_prefix . 'num_additional_guests', true);
            $guests = (int) $guests + (int) $additional_guests;

            $beds           = get_post_meta(get_the_ID(), $homey_prefix . 'beds', true);
            $baths          = get_post_meta(get_the_ID(), $homey_prefix . 'baths', true);
            $night_price    = get_post_meta(get_the_ID(), $homey_prefix . 'night_price', true);
            $location = get_post_meta(get_the_ID(), $homey_prefix . 'listing_location', true);
            $lat_long = explode(',', $location);

            if (empty($arrive) && empty($depart) || homey_option('show_unit_or_dates_price', 0) < 1) {
                $listing_price = homey_get_price_by_id($listing_id);
                $template_args['listing_price_from_search_results'] = $listing_price;
            } else {
                if (empty($depart)) {
                    $depart = $arrive;
                }

                $listing_price_arr = homey_get_prices($arrive, $depart, $listing_id, $guests);
                $listing_price = $listing_price_arr['total_price'];
                $template_args['listing_price_from_search_results'] = $listing_price;
            }

            $listing_type = wp_get_post_terms(get_the_ID(), 'listing_type', array("fields" => "ids"));

            if ($cgl_beds != 1) {
                $bedrooms = '';
            }

            if ($cgl_baths != 1) {
                $baths = '';
            }

            if ($cgl_guests != 1) {
                $guests = '';
            }

            $lat = $long = '';
            if (!empty($lat_long[0])) {
                $lat = $lat_long[0];
            }

            if (!empty($lat_long[1])) {
                $long = $lat_long[1];
            }

            $listing = new stdClass();

            $listing->id = $listing_id;
            $listing->title = get_the_title();
            $listing->lat = $lat;
            $listing->long = $long;
            $listing->price = homey_formatted_price($listing_price, false, false) . '<sub>' . esc_attr($price_separator) . homey_get_price_label_by_id($listing_id) . '</sub>';
            $listing->address = $address;
            $listing->bedrooms = $bedrooms;
            $listing->guests = $guests;
            $listing->beds = $beds;
            $listing->baths = $baths;

            $listing->arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
            $listing->depart = isset($_POST['depart']) ? $_POST['depart'] : '';

            if ($cgl_types != 1) {
                $listing->listing_type = '';
            } else {
                $listing->listing_type = homey_taxonomy_simple('listing_type');
            }
            $listing->thumbnail = get_the_post_thumbnail($listing_id, 'homey-listing-thumb',  array('class' => 'img-responsive'));
            $listing->url = get_permalink();
            $listing->icon = get_template_directory_uri() . '/images/custom-marker.png';
            $listing->retinaIcon = get_template_directory_uri() . '/images/custom-marker.png';
            if (!empty($listing_type)) {
                foreach ($listing_type as $term_id) {
                    $listing->term_id = $term_id;
                    $icon_id = get_term_meta($term_id, 'homey_marker_icon', true);
                    $retinaIcon_id = get_term_meta($term_id, 'homey_marker_retina_icon', true);
                    $icon = wp_get_attachment_image_src($icon_id, 'full');
                    $retinaIcon = wp_get_attachment_image_src($retinaIcon_id, 'full');
                    if (!empty($icon['0'])) {
                        $listing->icon = $icon['0'];
                    }
                    if (!empty($retinaIcon['0'])) {
                        $listing->retinaIcon = $retinaIcon['0'];
                    }
                }
            }

            array_push($all_listings_for_pins, $listing);
        endwhile;

        wp_reset_postdata();

        return $all_listings_for_pins;
    }
}


function homey_include_templates_for_elementor_pages_only()
{
    if (is_admin() || wp_doing_ajax() || !is_singular()) {
        return;
    }

    global $post;
    if (!isset($post->ID)) {
        return;
    }
    // Check if current page uses Elementor
    $uses_elementor = get_post_meta($post->ID, '_elementor_edit_mode', true);

    if ($uses_elementor !== 'builder') {
        return; // Not an Elementor page
    }

    // Now include the templates
    $nav_login = function_exists('homey_option') ? homey_option('nav_login') : true;
    if ($nav_login) {
        get_template_part('template-parts/modal-window-login');
        get_template_part('template-parts/modal-window-forgot-password');
    }

    $nav_register = function_exists('homey_option') ? homey_option('nav_register') : true;
    if ($nav_register) {
        get_template_part('template-parts/modal-window-register');
    }

    $compare_favorite = function_exists('homey_option') ? homey_option('compare_favorite') : true;
    if ($compare_favorite) {
        get_template_part('template-parts/listing/compare');
    }
}
add_action('wp_footer', 'homey_include_templates_for_elementor_pages_only', 100);
