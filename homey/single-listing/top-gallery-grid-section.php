<?php
global $post;
$size = 'homey-variable-slider';
$listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $post->ID );
$i = 0;

if(!empty($listing_images)) {
    ?>
    <div class="top-gallery-section">
        <div class="gallery-grid-wrap">
            <div class="gallery-grid-left-wrap">
                <?php foreach( $listing_images as  $indexNum => $image ) {
                    $i++;
                    if($i > 5){continue;}
                    if($i > 1 && $i < 3){ ?>
                        </div>
                        <div class="gallery-grid-right-wrap">
                    <?php } ?>
                    <div class="gallery-grid-item">
                        <?php if($i > 4){ ?><a data-fancy-image-index="<?php echo $i-1; ?>" class="btn gallery-grid-button fanboxTopGalleryVar-item"><?php echo esc_html__("View More Photos");?></a><?php } ?>

                        <a href="<?php echo esc_url($image['full_url']);?>" class="swipebox">
                            <img data-fancy-image-index="<?php echo $i-1; ?>" class="img-responsive fanboxTopGalleryVar-item" data-lazy="<?php echo esc_url($image['url']); ?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                        </a>
                    </div>
                <?php  } ?>
            </div>
        </div>
    </div><!-- top-gallery-section -->
    <?php fancybox_gallery_html($listing_images, "fanboxTopGalleryVar");//hidden images for gallery fancybox 3 ?>
<?php } ?>