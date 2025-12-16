<?php
global $post, $homey_prefix, $homey_local;
$accomodation = homey_get_listing_data('accomodation');
$guests = homey_option('sn_acc_guest_label');

$icon_type = homey_option('detail_icon_type');
$size = 'homey-variable-slider';

$type_icon = '<i class="homey-icon homey-icon-house-2"></i>';
$acco_icon = '<i class="homey-icon homey-icon-multiple-man-woman-2"></i>';
$bedroom_icon = '<i class="homey-icon homey-icon-hotel-double-bed"></i>';
$bathroom_icon = '<i class="homey-icon homey-icon-bathroom-shower-1"></i>';

if($icon_type == 'fontawesome_icon') {
    $type_icon = '<i class="'.esc_attr(homey_option('de_type_icon')).'"></i>';
    $acco_icon = '<i class="'.esc_attr(homey_option('de_acco_icon')).'"></i>';
    $bedroom_icon = '<i class="'.esc_attr(homey_option('de_bedroom_icon')).'"></i>';
    $bathroom_icon = '<i class="'.esc_attr(homey_option('de_bathroom_icon')).'"></i>';
} elseif($icon_type == 'custom_icon') {
    $type_icon = '<img src="'.esc_url(homey_option( 'de_cus_type_icon', false, 'url' )).'" alt="'.esc_attr__('type_icon', 'homey').'">';
    $acco_icon = '<img src="'.esc_url(homey_option( 'de_cus_acco_icon', false, 'url' )).'" alt="'.esc_attr__('acco_icon', 'homey').'">';
    $bedroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bedroom_icon', false, 'url' )).'" alt="'.esc_attr__('bedroom_icon', 'homey').'">';
    $bathroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bathroom_icon', false, 'url' )).'" alt="'.esc_attr__('bathroom_icon', 'homey').'">';
}

if(!empty($accomodation)) {
?>

<?php } ?>

<div id="accomodation-section" data-path="single-listing-to-accomodation-file" class="accommodation-section">
    <div class="accommodation-container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html__('Available Rooms','homey'); ?></h2>
            <p class="section-subtitle"><?php echo esc_html__('Choose date from booking form and see available rooms','homey'); ?></p>
        </div>
        
        <div class="rooms-grid">
            <?php
            $count = 0;
            foreach($accomodation as $index=>$acc):
                if(isset($acc['select_gallery_images_room'])) {
                    $listing_images = $acc['select_gallery_images_room'];
                } else {
                    $listing_images = array();
                }
                $listing_images = array_unique($listing_images);
                
                // Extract accommodation data
                $acc_bedroom_name = isset($acc['acc_bedroom_name']) ? $acc['acc_bedroom_name'] : '';
                $acc_guests = isset($acc['acc_guests']) ? $acc['acc_guests'] : '';
                $acc_no_of_beds = isset($acc['acc_no_of_beds']) ? $acc['acc_no_of_beds'] : '';
                $night_price = isset($acc['night_price']) ? $acc['night_price'] : '';
                $listing_size = isset($acc['listing_size']) ? $acc['listing_size'] : '';
                $listing_size_unit = isset($acc['listing_size_unit']) ? $acc['listing_size_unit'] : '';
                $cleaning_fee = isset($acc['cleaning_fee']) ? $acc['cleaning_fee'] : '';
                $cleaning_fee_type = isset($acc['cleaning_fee_type']) ? $acc['cleaning_fee_type'] : '';
                $city_fee = isset($acc['city_fee']) ? $acc['city_fee'] : '';
                $city_fee_type = isset($acc['city_fee_type']) ? $acc['city_fee_type'] : '';
                $listing_facilities = isset($acc['listing_facilities']) ? $acc['listing_facilities'] : '';
                $listing_amenities = isset($acc['listing_amenities']) ? $acc['listing_amenities'] : '';
                $acc_bedroom_description = isset($acc['acc_bedroom_description']) ? $acc['acc_bedroom_description'] : '';
                
                // Get first image for card
                $first_image = '';
                if (!empty($listing_images)) {
                    $first_image_id = $listing_images[0];
                    $img = wp_get_attachment_image_src($first_image_id, $size);
                    if ($img) {
                        $first_image = $img[0];
                    }
                }
            ?>
            
            <div class="room-card" data-toggle="modal" data-target="#roomModal<?php echo $index; ?>">
                <!-- Image Section - Single Image -->
                <div class="room-image-container">
                    <div class="room-image-wrapper">
                        <?php if($first_image): ?>
                            <img src="<?php echo esc_url($first_image); ?>" alt="<?php echo esc_attr($acc_bedroom_name); ?>" class="room-image">
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Room Details Section -->
                <div class="room-content">
                    <div class="room-content-inner">
                        <div class="room-info">
                            <?php if(!empty($acc_bedroom_name)): ?>
                                <h3 class="room-title"><?php echo esc_html($acc_bedroom_name); ?></h3>
                            <?php endif; ?>
                            
                            <!-- Room Features -->
                            <div class="room-features">
                                <?php if(!empty($acc_no_of_beds)): ?>
                                    <div class="feature-item">
                                        <?php echo $bedroom_icon; ?>
                                        <span><?php echo esc_html($acc_no_of_beds); ?> <?php echo esc_html__("Bed's", 'homey'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($acc_guests)): ?>
                                    <div class="feature-item">
                                        <?php echo $acco_icon; ?>
                                        <span><?php echo esc_html($acc_guests); ?> <?php echo esc_html__("Guest's", 'homey'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($listing_size)): ?>
                                    <div class="feature-item">
                                        <i class="homey-icon homey-icon-real-estate-dimensions-block"></i>
                                        <span><?php echo esc_html($listing_size . ' ' . $listing_size_unit); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Price and Booking -->
                        <div class="room-booking">
                            <div class="price-section">
                                <span class="price-amount"><?php echo homey_formatted_price($night_price, false, true); ?></span>
                                <span class="price-period">/<?php echo homey_get_price_label(); ?></span>
                            </div>
                            <button type="button" class="select-room-btn">
                                <?php echo esc_html__('View Details', 'homey'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap 3 Modal for Each Room -->
            <div class="modal fade room-modal" id="roomModal<?php echo $index; ?>" tabindex="-1" role="dialog" aria-labelledby="roomModalLabel<?php echo $index; ?>">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            
                            <div class="modal-layout">
                                <!-- Modal Left - Image Slider -->
                                <div class="modal-left">
                                    <div id="modalCarousel<?php echo $index; ?>" class="modal-carousel">
                                        <?php $i = 0; ?>
                                        <?php if (!empty($listing_images)) : ?>
                                            <?php foreach ($listing_images as $image_id) : ?>
                                                <?php
                                                    $img = wp_get_attachment_image_src($image_id, 'full');
                                                    $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                                ?>
                                                <?php if ($img) : ?>
                                                    <div class="carousel-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                                                        <img src="<?php echo esc_url($img[0]); ?>" alt="<?php echo esc_attr($alt); ?>" class="room-image">
                                                    </div>
                                                    <?php $i++; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if ($i > 1) : ?>
                                            <button class="carousel-btn carousel-prev" onclick="modalPrevSlide('modalCarousel<?php echo $index; ?>')"></button>
                                            <button class="carousel-btn carousel-next" onclick="modalNextSlide('modalCarousel<?php echo $index; ?>')"></button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Modal Right - Details -->
                                <div class="modal-right">
                                    <div class="modal-right-scrol">
                                        <?php if(!empty($acc_bedroom_name)): ?>
                                            <h2 class="modal-room-title"><?php echo esc_html($acc_bedroom_name); ?></h2>
                                        <?php endif; ?>
                                        
                                        <div class="modal-features">
                                            <?php if(!empty($acc_no_of_beds)): ?>
                                                <div class="feature-item">
                                                    <?php echo $bedroom_icon; ?>
                                                    <span><?php echo esc_html($acc_no_of_beds); ?> <?php echo esc_html__("Bed's", 'homey'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($acc_guests)): ?>
                                                <div class="feature-item">
                                                    <?php echo $acco_icon; ?>
                                                    <span><?php echo esc_html($acc_guests); ?> <?php echo esc_html__("Guest's", 'homey'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($listing_size)): ?>
                                                <div class="feature-item">
                                                    <i class="homey-icon homey-icon-real-estate-dimensions-block"></i>
                                                    <span><?php echo esc_html($listing_size . ' ' . $listing_size_unit); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Description -->
                                        <?php if(!empty($acc_bedroom_description)): ?>
                                            <div class="room-description">
                                                <h4 class="amenities-title"><?php echo esc_html__('Description', 'homey'); ?></h4>
                                                <p><?php echo esc_html($acc_bedroom_description); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Amenities -->
                                        <?php if(!empty($listing_amenities) || !empty($listing_facilities)): ?>
                                            <div class="room-amenities">
                                                <h4 class="amenities-title"><?php echo esc_html__('What this room offers', 'homey'); ?></h4>
                                                <div class="amenities-list">
                                                    <?php
                                                    $all_amenities = array_filter(array_merge((array) $listing_amenities,(array) $listing_facilities));
                                                    foreach ($all_amenities as $i => $amenity): ?>
                                                        <span class="amenity-tag <?php echo $i >= 8 ? 'amenity-extra amenity-hidden' : ''; ?>">
                                                            <i class="homey-icon homey-icon-check-circle-1"></i>
                                                            <?php echo esc_html($amenity); ?>
                                                        </span>
                                                    <?php endforeach; ?>

                                                    <?php if(count($all_amenities) > 8): ?>
                                                        <button class="amenity-toggle" onclick="toggleAmenities(this)">
                                                            <span class="toggle-text"><?php echo esc_html__('Show all', 'homey'); ?> <?php echo count($all_amenities); ?> <?php echo esc_html__('amenities', 'homey'); ?></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Extra Fees -->
                                        <?php if(!empty($cleaning_fee) || !empty($city_fee)): ?>
                                            <div class="extra-fees">
                                                <h4 class="fees-title"><?php echo esc_html__('Additional Fees', 'homey'); ?></h4>
                                                <div class="fees-list">
                                                    <?php if(!empty($city_fee)): ?>
                                                        <div class="fee-item">
                                                            <span class="fee-label"><?php echo esc_html__('City Tax:', 'homey'); ?></span>
                                                            <span class="fee-amount"><?php echo homey_formatted_price($city_fee); ?>/<?php if($city_fee_type == 'per_stay') {
                                                                echo esc_html__('Per Stay', 'homey');
                                                            } elseif($city_fee_type == 'daily') {
                                                                echo esc_html__('Hourly', 'homey');
                                                            } ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if(!empty($cleaning_fee)): ?>
                                                        <div class="fee-item">
                                                            <span class="fee-label"><?php echo esc_html__('Cleaning:', 'homey'); ?></span>
                                                            <span class="fee-amount"><?php echo homey_formatted_price($cleaning_fee); ?>/<?php echo esc_html($cleaning_fee_type, 'homey'); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Price and Booking -->
                                        <div class="modal-booking">
                                            <div class="price-section">
                                                <span class="price-amount"><?php echo homey_formatted_price($night_price, false, true); ?></span>
                                                <span class="price-period">/<?php echo homey_get_price_label(); ?></span>
                                            </div>
                                            <button type="button"
                                                    class="select-room-btn"
                                                    data-listid="<?php echo get_the_id(); ?>"
                                                    data-room-id="<?php echo $index; ?>">
                                                <?php echo esc_html__('Check Availability', 'homey'); ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Sticky Footer -->
                                    <div class="modal-sticky-footer">
                                        <div class="sticky-footer-content">
                                            <div class="sticky-price-row">
                                                <div class="sticky-price-section">
                                                    <span class="sticky-price-amount"><?php echo homey_formatted_price($night_price, false, true); ?></span>
                                                    <span class="sticky-price-period">/<?php echo homey_get_price_label(); ?></span>
                                                </div>
                                                <div class="sticky-availability-text">
                                                    <?php echo esc_html__('Select date and check availability', 'homey'); ?>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    class="sticky-footer-btn"
                                                    data-listid="<?php echo get_the_id(); ?>"
                                                    data-room-id="<?php echo $index; ?>"
                                                    data-dismiss="modal">
                                                <?php echo esc_html__('Go back', 'homey'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php $count++; endforeach; ?>
        </div>
    </div>
</div>

<script>
// Modal Carousel Navigation
function modalNextSlide(carouselId) {
    var carousel = document.getElementById(carouselId);
    var slides = carousel.querySelectorAll('.carousel-slide');
    var current = -1;

    for (var i = 0; i < slides.length; i++) {
        if (slides[i].classList.contains('active')) {
            current = i;
            break;
        }
    }

    if (current !== -1) {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    }
}

function modalPrevSlide(carouselId) {
    var carousel = document.getElementById(carouselId);
    var slides = carousel.querySelectorAll('.carousel-slide');
    var current = -1;

    for (var i = 0; i < slides.length; i++) {
        if (slides[i].classList.contains('active')) {
            current = i;
            break;
        }
    }

    if (current !== -1) {
        slides[current].classList.remove('active');
        current = (current - 1 + slides.length) % slides.length;
        slides[current].classList.add('active');
    }
}

// Toggle Amenities
function toggleAmenities(btn) {
    const extras = btn.parentElement.querySelectorAll('.amenity-extra');
    const isHidden = extras[0].classList.contains('amenity-hidden');
    const allAmenities = btn.parentElement.querySelectorAll('.amenity-tag');

    extras.forEach(el => {
        if (isHidden) {
            el.classList.remove('amenity-hidden');
        } else {
            el.classList.add('amenity-hidden');
        }
    });

    const toggleText = btn.querySelector('.toggle-text');
    const totalCount = allAmenities.length;
    toggleText.textContent = isHidden ? '<?php echo esc_html__('Show less', 'homey'); ?>' : '<?php echo esc_html__('Show all', 'homey'); ?> ' + totalCount + ' <?php echo esc_html__('amenities', 'homey'); ?>';
}

// Add keyboard navigation for carousel
jQuery(document).ready(function($) {
    $('.room-modal').on('shown.bs.modal', function() {
        var modalId = $(this).attr('id');
        var carouselId = $(this).find('.modal-carousel').attr('id');

        // Remove any existing keydown handlers
        $(document).off('keydown.carousel');

        // Add new keydown handler
        $(document).on('keydown.carousel', function(e) {
            if ($(modalId).hasClass('in')) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    modalPrevSlide(carouselId);
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    modalNextSlide(carouselId);
                }
            }
        });
    });

    $('.room-modal').on('hidden.bs.modal', function() {
        $(document).off('keydown.carousel');
    });
});
</script>