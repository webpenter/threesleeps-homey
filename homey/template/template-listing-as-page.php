<?php
/**
 * Template Name: Listing Template As Page
 *
 */

// Get the post ID
$post_id = get_the_ID();

// Get the value of the custom field 'redirect_listing_url' for the current post
$redirect_url = get_post_meta($post_id, 'redirect_listing_url', true);
header("Location: $redirect_url");
exit;