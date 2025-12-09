<?php
 if( homey_option('tps-facebook') != '' || homey_option('tps-twitter') != '' || homey_option('tps-linkedin') != '' || homey_option('tps-googleplus') != '' || homey_option('tps-instagram') != '' || homey_option('tps-pinterest') != '' ) { ?>

    <div class="social-icons">
        
        <?php if( homey_option('tps-facebook') != '' ){ ?>
        	<a class="btn-bg-facebook" target="_blank" href="<?php echo esc_url(homey_option('tps-facebook')); ?>"><i class="homey-icon homey-icon-social-media-facebook"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-twitter') != '' ){ ?>
            <a class="btn-bg-twitter" target="_blank" href="<?php echo esc_url(homey_option('tps-twitter')); ?>"><i class="homey-icon homey-icon-social-media-twitter"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-linkedin') != '' ){ ?>
            <a class="btn-bg-linkedin" target="_blank" href="<?php echo esc_url(homey_option('tps-linkedin')); ?>"><i class="homey-icon homey-icon-professional-network-linkedin"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-googleplus') != '' ){ ?>
            <a class="btn-bg-google" target="_blank" href="<?php echo esc_url(homey_option('tps-googleplus')); ?>"><i class="homey-icon homey-icon-social-media-google-plus-1"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-instagram') != '' ){ ?>
            <a class="btn-bg-instagram" target="_blank" href="<?php echo esc_url(homey_option('tps-instagram')); ?>"><i class="homey-icon homey-icon-social-instagram"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-pinterest') != '' ){ ?>
            <a class="btn-bg-pinterest" target="_blank" href="<?php echo esc_url(homey_option('tps-pinterest')); ?>"><i class="homey-icon homey-icon-social-pinterest"></i></a>
        <?php } ?>

        <?php if( homey_option('tps-yelp') != '' ){ ?>
            <a class="btn-bg-yelp" target="_blank" href="<?php echo esc_url(homey_option('tps-yelp')); ?>"><i class="homey-icon homey-icon-social-media-yelp"></i></a>
        <?php } ?>
        <?php if( homey_option('tps-youtube') != '' ){ ?>
            <a class="btn-bg-youtube" target="_blank" href="<?php echo esc_url(homey_option('tps-youtube')); ?>"><i class="homey-icon homey-icon-social-video-youtube"></i></a>
        <?php } ?>
        
    </div>
<?php } ?>