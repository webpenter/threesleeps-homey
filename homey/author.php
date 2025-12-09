<?php
get_header();
global $wp_query, $homey_local, $homey_prefix;
$queried_object = get_queried_object();

$role = '';
if(isset($queried_object->roles[0])){
    $role = $queried_object->roles[0];
}

if($role == 'homey_renter') {
    get_template_part('template-parts/profile/guest');
} else {
    get_template_part('template-parts/profile/host');
}

get_footer(); 
?>
