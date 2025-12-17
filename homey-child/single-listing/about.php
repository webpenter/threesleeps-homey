<?php
$detail_layout = homey_option('detail_layout');
if( isset( $_GET['detail_layout'] ) ) {
    $detail_layout = $_GET['detail_layout'];
}

if( $detail_layout == 'v5' || $detail_layout == 'v6' ) {
    get_template_part('single-listing/about', 'v2');
} else {
    get_template_part('single-listing/about', 'v1');
}