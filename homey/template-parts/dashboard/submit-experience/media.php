<?php
global $post, $hide_fields, $homey_local;
$hide_fields_for_experience = homey_option('experience_add_hide_fields');

$layout_order = homey_option('experience_form_sections');
$layout_order = $layout_order['enabled'];
$i = 0;
$style = 'display: none;';
if ($layout_order) { 
    foreach ($layout_order as $key=>$value) {
        $i++;
        if($i == 2 && $key == 'media') {
            $style = 'display: block;';
        }
    }
}
?>
<div class="form-step form-step-gal1" style="<?php echo esc_attr($style); ?>">
    <!--step information-->
    <div class="block">
        <div class="block-title visible-xs">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('experience_ad_section_media')); ?></h2>
            </div><!-- block-left -->
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
                            <div id="homey_gallery_container" class="row"></div>

                            <div id="upload-progress-images"></div>
                            <?php if($hide_fields_for_experience['experience_video_url'] != 1) { ?>
                                <hr class="row-separator">
                                <h3 class="sub-title"><?php echo esc_attr(homey_option('ad_video_heading')); ?></h3>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="video_url"><?php echo esc_attr(homey_option('experience_ad_video_url')); ?></label>
                                            <input type="text" class="form-control" name="experience_video_url" value="" placeholder="<?php echo esc_attr(homey_option('experience_ad_video_placeholder')); ?>">
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
</div>
