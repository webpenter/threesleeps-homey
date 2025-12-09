<?php
global $post;

$size_class = 'homey-variable-slider-4-images-285_285';
$experience_images = rwmb_meta( 'homey_experience_images', 'type=plupload_image&size='.$size_class, $post->ID );

$i = 0;
if(!empty($experience_images)) { ?>
    <div class="top-gallery-section">
        <div class="gallery-grid-wrap">
            <div class="gallery-grid-left-wrap">
                <?php foreach( $experience_images as  $indexNum => $image ) {
                    $i++;
                    if($i > 5){continue;}
                    if($i > 1 && $i < 3){ ?>
                        </div>
                        <div class="gallery-grid-right-wrap">
                    <?php } ?>
                    <div data-val="<?php echo $indexNum; ?>" class="gallery-grid-item">
                        <?php if($i > 4){ ?><a data-fancy-image-index="<?php echo $i-1; ?>" class="btn gallery-grid-button fanboxTopGalleryVar-item"><?php echo esc_html__("View More Photos");?></a><?php } ?>
                        <?php if($i == 1 ){  $size_class = 'homey-variable-slider-img1-570_570'; }else{ $size_class = 'homey-variable-slider-4-images-285_285';} ?>

                        <a href="<?php echo esc_url($image['full_url']);?>" class="swipebox">
                            <img data-fancy-image-index="<?php echo $i-1; ?>" class="img-responsive fanboxTopGalleryVar-item" data-lazy="<?php echo esc_url($image['sizes'][$size_class]['url']); ?>" src="<?php echo esc_url($image['sizes'][$size_class]['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                        </a>
                    </div>
                <?php  } ?>
            </div>
        </div>
    </div><!-- top-gallery-section -->
    <?php fancybox_gallery_html($experience_images, "fanboxTopGalleryVar");//hidden images for gallery fancybox 3 ?>
<?php } ?>