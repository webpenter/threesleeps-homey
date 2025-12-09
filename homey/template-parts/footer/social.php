<?php if( homey_option('social-footer') != '0' ) {
 if( homey_option('fs-facebook') != '' || homey_option('fs-twitter') != '' || homey_option('fs-linkedin') != '' || homey_option('fs-googleplus') != '' || homey_option('fs-instagram') != '' || homey_option('fs-pinterest') != '' ) { ?>

    <div class="social-icons social-round">
        
        <?php if( homey_option('fs-facebook') != '' ){ ?>
        	<a class="btn-bg-facebook" target="_blank" href="<?php echo esc_url(homey_option('fs-facebook')); ?>"><i class="homey-icon homey-icon-social-media-facebook"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-twitter') != '' ){ ?>
            <a class="btn-bg-twitter" target="_blank" href="<?php echo esc_url(homey_option('fs-twitter')); ?>"><i class="homey-icon homey-icon-social-media-twitter"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-linkedin') != '' ){ ?>
            <a class="btn-bg-linkedin" target="_blank" href="<?php echo esc_url(homey_option('fs-linkedin')); ?>"><i class="homey-icon homey-icon-professional-network-linkedin"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-googleplus') != '' ){ ?>
            <a class="btn-bg-google" target="_blank" href="<?php echo esc_url(homey_option('fs-googleplus')); ?>"><i class="homey-icon homey-icon-social-media-google-plus-1"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-instagram') != '' ){ ?>
            <a class="btn-bg-instagram" target="_blank" href="<?php echo esc_url(homey_option('fs-instagram')); ?>"><i class="homey-icon homey-icon-social-instagram"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-pinterest') != '' ){ ?>
            <a class="btn-bg-pinterest" target="_blank" href="<?php echo esc_url(homey_option('fs-pinterest')); ?>"><i class="homey-icon homey-icon-social-pinterest"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-yelp') != '' ){ ?>
            <a class="btn-bg-yelp" target="_blank" href="<?php echo esc_url(homey_option('fs-yelp')); ?>"><i class="homey-icon homey-icon-social-media-yelp"></i></a>
        <?php } ?>
        <?php if( homey_option('fs-youtube') != '' ){ ?>
            <a class="btn-bg-youtube" target="_blank" href="<?php echo esc_url(homey_option('fs-youtube')); ?>"><i class="homey-icon homey-icon-social-video-youtube"></i></a>
        <?php } ?>
        
    </div>
<?php }
} ?>