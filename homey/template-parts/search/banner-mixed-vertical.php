<?php
global $post, $homey_local, $homey_prefix;
$homey_search_type = homey_search_type();

$search_type = get_post_meta( $post->ID, 'homey_banner_search_hourly', true );
if( $search_type ) {
	$homey_search_type = 'per_hour';
}
?>

<div class="mixed-search-panel mixed-search-side-banner">
    <ul class="nav nav-tabs">
        <li class="active">
            <a data-target="#banner-mixed-vertical-daily" role="tab" data-toggle="tab" aria-expanded="true"><?php echo esc_html__('Stay', 'homey');?></a>
        </li>
        <li class="">
            <a data-target="#banner-mixed-vertical-daily-exp" role="tab" data-toggle="tab" aria-expanded="false"><?php echo esc_html__('Experiences', 'homey');?></a>
        </li>
    </ul>
    <div class="tab-content">
        <?php
        if($homey_search_type == "per_hour") {
            get_template_part('template-parts/search/banner-mixed-vertical', 'hourly');
        } else {
            get_template_part('template-parts/search/banner-mixed-vertical', 'daily');
            get_template_part('template-parts/search/banner-mixed-vertical', 'daily-exp');
        }
        ?>
    </div>
</div>
