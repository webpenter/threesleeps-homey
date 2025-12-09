<div id="homey-map-loading">
 	<div class="mapPlaceholder">
        <div class="loader-ripple spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
</div>
<?php wp_nonce_field('homey_map_ajax_nonce', 'securityhomeyMap', true); ?>

<?php if( homey_get_map_system() == 'google') { ?>
<div  class="map-arrows-actions">
    <button id="listing-mapzoomin" class="map-btn"><i class="homey-icon homey-icon-add"></i> </button>
    <button id="listing-mapzoomout" class="map-btn"><i class="homey-icon homey-icon-subtract"></i></button>
    <input type="text" id="google-map-search" placeholder="<?php esc_attr_e('Google Map Search', 'homey'); ?>" class="map-search">
    
</div>
<?php } ?>
<div class="map-next-prev-actions">
    <?php if( homey_get_map_system() == 'google') { ?>
    <ul class="dropdown-menu" aria-labelledby="homey-gmap-view">
        <li><a href="" class="homeyMapType" data-maptype="roadmap"><span><?php esc_html_e( 'Roadmap', 'homey' ); ?></span></a></li>
        <li><a href="" class="homeyMapType" data-maptype="satellite"><span><?php esc_html_e( 'Satelite', 'homey' ); ?></span></a></li>
        <li><a href="" class="homeyMapType" data-maptype="hybrid"><span><?php esc_html_e( 'Hybrid', 'homey' ); ?></span></a></li>
        <li><a href="" class="homeyMapType" data-maptype="terrain"><span><?php esc_html_e( 'Terrain', 'homey' ); ?></span></a></li>
    </ul>
    
    <button id="homey-gmap-view" class="map-btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="homey-icon homey-icon-earth-3-maps-navigation"></i> <span><?php esc_html_e( 'View', 'homey' ); ?></span></button>
    <?php } ?>
    <button id="homey-gmap-prev" class="map-btn"><i class="homey-icon homey-icon-arrow-left-1"></i> <span><?php esc_html_e('Prev', 'homey'); ?></span></button>
    <button id="homey-gmap-next" class="map-btn"><span><?php esc_html_e('Next', 'homey'); ?></span> <i class="homey-icon homey-icon-arrow-right-1"></i></button>
</div>
<?php if( homey_get_map_system() == 'google') { ?>
<div  class="map-zoom-actions">
    <div id="homey-gmap-full"  class="map-btn"><i class="homey-icon homey-icon-expand-5"></i> <span><?php esc_html_e('Fullscreen', 'homey'); ?></span></div>
</div>
<?php } ?>