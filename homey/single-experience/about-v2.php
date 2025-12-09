<?php
global $post, $homey_prefix, $homey_local, $hide_labels, $experience_author;
$is_superhost = $experience_author['is_superhost'];

$homey_start_end_open = get_post_meta(get_the_ID(), 'homey_start_end_open', true);
$homey_start_end_close = get_post_meta(get_the_ID(), 'homey_start_end_close', true);

$guests = homey_get_listing_data('guests');

$allow_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'allow_additional_guests', true);
$num_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'num_additional_guests', true);

if ($allow_additional_guests == 'yes' && !empty($num_additional_guests)) {
    $guests = (int)$guests + (int)$num_additional_guests;
}

$num_additional_guests = homey_get_field_meta('num_additional_guests');

$experience_language = homey_taxonomy_simple('experience_language');

$guests = homey_get_experience_data('guests');

$hide_labels = homey_option('experience_show_hide_labels');

$allow_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'allow_additional_guests', true);
$num_additional_guests = get_post_meta(get_the_ID(), $homey_prefix . 'num_additional_guests', true);

if ($allow_additional_guests == 'yes' && !empty($num_additional_guests)) {
    $guests = (int)$guests + (int)$num_additional_guests;
}

$num_additional_guests = homey_get_field_meta('num_additional_guests');

$experience_type = homey_taxonomy_simple('experience_type');

$type_icon = $acco_icon = '';

$slash = '';
if (!empty($experience_type)) {
    $slash = '/';
}
$icon_type = homey_option('experience_detail_icon_type');

$type_icon = '<i class="homey-icon homey-icon-house-2"></i>';
$acco_icon = '<i class="homey-icon homey-icon-multiple-man-woman-2"></i>';
$hours_icon = '<i class="homey-icon homey-icon-hotel-double-bed"></i>';
$language_icon = '<i class="homey-icon homey-icon-bathroom-shower-1"></i>';

if ($icon_type == 'fontawesome_icon') {
    $type_icon = '<i class="' . esc_attr(homey_option('experience_de_type_icon')) . '"></i>';
    $acco_icon = '<i class="' . esc_attr(homey_option('experience_de_acco_icon')) . '"></i>';
    $hours_icon = '<i class="' . esc_attr(homey_option('experience_de_calendar_icon')) . '"></i>';
    $language_icon = '<i class="' . esc_attr(homey_option('experience_de_language_icon')) . '"></i>';

} elseif ($icon_type == 'custom_icon') {
    $type_icon = '<img src="' . esc_url(homey_option('experience_cus_type_icon', false, 'url')) . '" alt="' . esc_attr__('type_icon', 'homey') . '">';
    $acco_icon = '<img src="' . esc_url(homey_option('experience_cus_acco_icon', false, 'url')) . '" alt="' . esc_attr__('acco_icon', 'homey') . '">';
    $hours_icon = '<img src="' . esc_url(homey_option('experience_cus_calendar_icon', false, 'url')) . '" alt="' . esc_attr__('calendar_icon', 'homey') . '">';
    $language_icon = '<img src="' . esc_url(homey_option('experience_cus_language_icon', false, 'url')) . '" alt="' . esc_attr__('language_icon', 'homey') . '">';
}
?>

<div id="about-section" class="about-section-v5">
    <div class="block block-v5">
        <div class="block-body-v5">
            <h2 class="title-v5"><?php echo esc_attr($experience_type); echo ' ';
                echo esc_html__("Hosted by", "homey"); ?><?php echo ' '; echo $experience_author['name']; ?></h2>
            <div class="property-accomodation-detals-v5">
                <div class="property-accomodation-detals-v5"><?php echo esc_attr($guests);
                    echo ' ' . esc_attr(homey_option('experience_sn_guests_label')); ?>
                    <span>•</span> <?php echo esc_html__('From', 'homey'); ?> <?php echo $homey_start_end_open; ?>
                    to <?php echo $homey_start_end_close; ?>
                    <span>•</span> <?php echo esc_attr($experience_language); ?>
                </div>

                <div class="host-avatar-wrap avatar">
                    <?php if ($is_superhost) { ?>
                        <span class="super-host-icon">
                    <i class="homey-icon homey-icon-award-badge-1"></i>
                </span>
                    <?php } ?>
                    <?php echo '' . $experience_author['photo']; ?>
                    <!--<img src="img/70x70.png" class="img-circle media-object " alt="Image" width="70" height="70">-->
                </div>
            </div>
        </div><!-- block-v5 -->
        <div class="block block-v5">
            <div class="block-body-v5">
                <h2><?php echo esc_attr(homey_option('experience_sn_about_title')); ?></h2>
                <p><?php the_content(); ?></p>
            </div>
        </div><!-- block-body -->
    </div>

    <?php
    //Custom Fields
    if (class_exists('Homey_Fields_Builder')) { ?>
        <div class="block">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_detail_heading')); ?></h3>
                </div><!-- block-left -->

                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <li>
                            <i class="homey-icon homey-icon-common-file-text-files-folders"></i>
                            <?php echo esc_attr(homey_option('sn_id_label')); ?>: <strong><?php echo esc_attr($post->ID); ?></strong>
                        </li>
                        <?php
                        //Custom Fields
                        if (class_exists('Homey_Fields_Builder')) {
                            $fields_array = Homey_Fields_Builder::get_form_fields();

                            if (!empty($fields_array)) {
                                foreach ($fields_array as $value) {
                                    $data_value = get_post_meta(get_the_ID(), 'homey_' . $value->field_id, true);
                                    $field_title = $value->label;
                                    $field_type = $value->type;

                                    $field_title = homey_wpml_translate_single_string($field_title);
                                    $data_value = homey_wpml_translate_single_string($data_value);

                                    if ($field_type != 'textarea') {
                                        if (!empty($data_value) && @$hide_labels[$value->field_id] != 1) {
                                            echo '<li class="' . esc_attr($value->field_id) . '"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i>' . esc_attr($field_title) . ': <strong>' . esc_attr($data_value) . '</strong></li>';
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } ?>
</div>