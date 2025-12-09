<?php
global $post;
$zoom_level = homey_option('googlemap_zoom_level');
?>
<section class="top-banner-wrap <?php homey_banner_fullscreen(); ?>">    

    <div id="banner-map-experiences"
    data-zoomlevel="<?php echo intval($zoom_level); ?>"
    >	
    </div>

    <?php get_template_part('template-parts/map-controls-exp'); ?>

</section><!-- header-parallax -->