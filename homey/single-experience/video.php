<?php
global $post, $homey_prefix, $homey_local;
$video_url = homey_get_experience_data('experience_video_url');

if (!empty($video_url)) {
    ?>
    <div id="video-section" class="video-section">
        <div class="block">
            <div class="block-section">
                <div class="block-body">
                    <div class="block-left">
                        <h3 class="title"><?php echo esc_attr(homey_option('experience_sn_video_heading')); ?></h3>
                    </div><!-- block-left -->
                    <?php $embed_code = wp_oembed_get($video_url);
                    if ($embed_code) { ?>
                        <div class="block-right">
                            <div class="block-video">
                                <?php echo $embed_code; ?>
                            </div>
                        </div><!-- block-right -->

                    <?php } else {
                        if (strpos($video_url, '.mp4') !== false) { ?>
                            <video id="homey-no-embed-experience-video" class="homey-no-embed-experience-video" src="<?php echo $video_url; ?>" controls>
                                <p>Your browser doesn't support HTML5 video. Here is a <a
                                            href="<?php echo $video_url; ?>">link to the video</a> instead.</p>
                            </video>
                        <?php }
                    }
                    ?>
                </div><!-- block-body -->
            </div><!-- block-section -->
        </div><!-- block -->
    </div>
<?php } ?>