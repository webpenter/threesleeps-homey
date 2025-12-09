<?php
$experience_detail_layout = homey_option('experience_detail_layout');
if( $experience_detail_layout == 'v5' || $experience_detail_layout == 'v6' ) {
    get_template_part('single-experience/about', 'v2');
} else {
    get_template_part('single-experience/about', 'v1');
}