<?php
global $post, $homey_local, $experience_data;

$hide_fields = homey_option('experience_show_hide_labels');

$video_url = get_post_meta($experience_data->ID, 'homey_experience_video_url', true);
$video_url = !empty($video_url) ? $video_url : '';

$class = $class2 = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'media') {
    $class = 'in active';
    $style = 'display: block;';
} else {
    $style = 'display: block; position: absolute;';
}

?>

<div id="media-tab" style="<?php echo ''.$style; ?>" class="fade <?php echo esc_attr($class).' '.$class2; ?>">
    <div class="block-title visible-xs">
        <h2 class="title"><?php echo esc_attr(homey_option('experience_ad_section_media')); ?></h2>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <div class="upload-property-media">
                    <div id="homey_gallery_dragDrop" class="media-drag-drop">
                        <div class="upload-icon">
                            <i class="homey-icon homey-icon-picture-landscape-images-photography" aria-hidden="true"></i>
                        </div>
                        <h4>
                            <?php echo homey_option('experience_ad_drag_drop_img'); ?><br>
                            <span><?php echo esc_attr(homey_option('experience_ad_image_size_text')); ?></span>
                        </h4>
                        <button id="select_gallery_images" href="javascript:;" class="btn btn-secondary"><i class="homey-icon homey-icon-social-media-yelp"></i> <?php echo esc_attr(homey_option('experience_ad_upload_btn')); ?></button>
                    </div>
                    <div id="plupload-container"></div>
                    <div id="homey_errors"></div>

                    <div class="upload-media-gallery">
                        <div id="homey_gallery_container" class="row">
                            <?php
                            $experience_images = get_post_meta( $experience_data->ID, 'homey_experience_images', false );

                            $featured_image_id = get_post_thumbnail_id( $experience_data->ID );
                            $experience_images[] = $featured_image_id;
                            $experience_images = array_unique($experience_images);

                            if( !empty($experience_images[0])) {
                                foreach ($experience_images as $experience_image_id) {

                                    $is_featured_image = ($featured_image_id == $experience_image_id);
                                    $featured_icon = ($is_featured_image) ? 'homey-icon-rating-star-full' : 'homey-icon-rating-star';

                                    $experience_thumb = wp_get_attachment_image_src( $experience_image_id, 'homey-listing-thumb' );

                                    $img_available = wp_get_attachment_image($experience_image_id, 'thumbnail');

                                    if( !empty($img_available)) {

                                        echo '<div class="col-sm-2 col-xs-4 experience-thumb">';
                                        echo '<figure class="upload-gallery-thumb">';
                                        echo wp_get_attachment_image($experience_image_id, 'thumbnail');
                                        echo '</figure>';
                                        echo '<div class="upload-gallery-thumb-buttons">';
                                            echo '<a class="icon-featured" data-thumb="'.$experience_thumb[0].'" data-experience-id="' . intval($experience_data->ID) . '"  data-attachment-id="' . intval($experience_image_id) . '"><i class="homey-icon '.$featured_icon.'"></i></a>';
                                            echo '<button class="icon-delete" data-experience-id="' . intval($experience_data->ID) . '"  data-attachment-id="' . intval($experience_image_id) . '"><i class="homey-icon homey-icon-bin-1-interface-essential"></i></button>';
                                            echo '<input type="hidden" class="experience-image-id" name="experience_image_ids[]" value="' . intval($experience_image_id) . '"/>';
                                            if ($is_featured_image) {
                                                echo '<input type="hidden" class="featured_image_id" name="featured_image_id" value="' . intval($experience_image_id) . '">';
                                            }
                                        echo '</div>';
                                        echo '<span style="display: none;" class="icon icon-loader"><i class="homey-icon homey-icon-loading-half fa-spin"></i></span>';
                                        echo '</div>';

                                    }
                                }
                            }
                            ?>
                        </div>

                        <div id="upload-progress-images"></div>

                        <?php if($hide_fields['experience_sn_video_url'] != 1) { ?>
                            <hr class="row-separator">
                            <h3 class="sub-title"><?php echo esc_attr(homey_option('ad_video_heading')); ?></h3>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="video_url"><?php echo esc_attr(homey_option('experience_ad_video_url')); ?></label>
                                        <input type="text" class="form-control" name="experience_video_url" value="<?php echo esc_url($video_url); ?>" placeholder="<?php echo esc_attr(homey_option('experience_ad_video_placeholder')); ?>">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
