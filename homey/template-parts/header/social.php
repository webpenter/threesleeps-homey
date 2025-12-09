<?php if( homey_option('social-header') != '0' ) {
 if( homey_option('hs-facebook') != '' || homey_option('hs-twitter') != '' || homey_option('hs-linkedin') != '' || homey_option('hs-googleplus') != '' || homey_option('hs-instagram') != '' || homey_option('hs-pinterest') != '' ) { ?>

    <div class="social-icons social-round">
        
        <?php if( homey_option('hs-facebook') != '' ){ ?>
        	<a class="btn-bg-facebook" target="_blank" href="<?php echo esc_url(homey_option('hs-facebook')); ?>"><i class="homey-icon homey-icon-social-media-facebook"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-twitter') != '' ){ ?>
            <a class="btn-bg-twitter" target="_blank" href="<?php echo esc_url(homey_option('hs-twitter')); ?>"><i class="homey-icon homey-icon-social-media-twitter"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-linkedin') != '' ){ ?>
            <a class="btn-bg-linkedin" target="_blank" href="<?php echo esc_url(homey_option('hs-linkedin')); ?>"><i class="homey-icon homey-icon-professional-network-linkedin"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-googleplus') != '' ){ ?>
            <a class="btn-bg-google" target="_blank" href="<?php echo esc_url(homey_option('hs-googleplus')); ?>"><i class="homey-icon homey-icon-social-media-google-plus-1"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-instagram') != '' ){ ?>
            <a class="btn-bg-instagram" target="_blank" href="<?php echo esc_url(homey_option('hs-instagram')); ?>"><i class="homey-icon homey-icon-social-instagram"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-pinterest') != '' ){ ?>
            <a class="btn-bg-pinterest" target="_blank" href="<?php echo esc_url(homey_option('hs-pinterest')); ?>"><i class="homey-icon homey-icon-social-pinterest"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-yelp') != '' ){ ?>
            <a class="btn-bg-yelp" target="_blank" href="<?php echo esc_url(homey_option('hs-yelp')); ?>"><i class="homey-icon homey-icon-social-media-yelp"></i></a>
        <?php } ?>
        <?php if( homey_option('hs-youtube') != '' ){ ?>
            <a class="btn-bg-youtube" target="_blank" href="<?php echo esc_url(homey_option('hs-youtube')); ?>"><i class="homey-icon homey-icon-social-video-youtube"></i></a>
        <?php } ?>
        
    </div>
<?php }
} ?>