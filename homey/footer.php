<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content.
 *
 * @package Homey
 * @since Homey 1.0
 */
 ?>
</div> <!-- End #section-body -->
<?php
if( !homey_is_dashboard_footer() && !homey_is_halfmap_page() && !homey_is_search_page()) {
	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
		get_template_part('template-parts/footer/footer');
	}
} elseif(homey_is_splash()) {
	get_template_part('template-parts/footer/dashboard-footer');
}
?>

<?php 
get_template_part('template-parts/search/overlay-mobile-search');

get_template_part('template-parts/search/overlay-mobile-search-exp');
?>

<?php if (isset($_GET['auth_message'])){
    if( $_GET['auth_message'] == 'your-profile-is-activated') {
        get_template_part('template-parts/modal-window-profile-activated');

    }
}  ?>

<?php
$nav_login = homey_option('nav_login');
if($nav_login) {
    get_template_part('template-parts/modal-window-login');
    get_template_part('template-parts/modal-window-forgot-password');
}

$nav_register = homey_option('nav_register');
if($nav_register) {
    get_template_part('template-parts/modal-window-register');
}
?>
<?php $compare_favorite = homey_option('compare_favorite');
if($compare_favorite){
    get_template_part('template-parts/listing/compare');
} ?>

<?php wp_footer(); ?>

<?php if(isset($_POST['is_login'])){ ?>
        <input type="hidden" id="reservation_login_required" name="reservation_login_required" value="1">
    <style>
        .gsi-material-button-icon svg{
            top: 160px !important;
        }
    </style>
<script>
    jQuery("input[name='_wp_http_referer']").val('<?php echo $_POST["referer_link"]; ?>');
    jQuery(".homey_login_messages").html('<p class="error text-danger"><?php echo esc_html__("Please login to visit the link.", 'homey') ?> <?php echo $_POST["referer_link"]; ?></p>');
    jQuery('#modal-login').modal('show');
</script>
<?php }else{ ?>
    <input type="hidden" id="reservation_login_required" name="reservation_login_required" value="0">
<?php } ?>

</body>
</html>