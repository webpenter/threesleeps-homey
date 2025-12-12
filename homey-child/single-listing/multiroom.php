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

<style>
    * {
        box-sizing: border-box;
    }

    /* Main Container */
    .accommodation-section {
        max-width: 1280px;
        margin: 0 auto;
        padding: 48px 24px;
    }

    .section-header {
        margin-bottom: 32px;
    }

    .section-title {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 0;
        color: #222;
        letter-spacing: -0.3px;
        line-height: 1.3;
    }

    .section-subtitle {
        display: none;
    }

    /* Room Grid */
    .rooms-grid {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Room Card - Horizontal Layout */
    .room-card {
        display: table;
        width: 100%;
        border: 1px solid #e8e8e8;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        overflow: visible;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        padding: 16px;
    }

    .room-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px);
        border-color: #d8d8d8;
    }

    .room-card:focus {
        outline: none;
        border-color: #222;
    }

    /* Image Section - Single Image */
    .room-image-container {
        display: table-cell;
        width: 230px;
        vertical-align: middle;
        position: relative;
    }

    .room-image-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 70%;
        overflow: hidden;
        border-radius: 12px;
    }

    .room-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
    }

    .room-card:hover .room-image {
        transform: scale(1.03);
    }

    /* Room Content - Right Side */
    .room-content {
        display: table-cell;
        vertical-align: middle;
        padding: 8px 24px;
    }

    .room-content-inner {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .room-info {
        flex: 1;
    }

    .room-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #222;
        letter-spacing: -0.2px;
        line-height: 1.4;
    }

    /* Room Features */
    .room-features {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 0;
        flex-wrap: wrap;
        font-size: 16px;
        color: #6a6a6a;
        line-height: 1.5;
    }

    .feature-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 16px;
        color: #6a6a6a;
        padding: 0;
    }

    .feature-item:not(:last-child)::after {
        content: "·";
        margin-left: 6px;
        color: #6a6a6a;
    }

    .feature-item i,
    .feature-item img {
        display: none;
    }

    .feature-item span {
        font-weight: 400;
    }

    /* Price and Button - Hidden on Card */
    .room-card .room-booking {
        display: none;
    }

    .price-amount {
        font-size: 24px;
        font-weight: 700;
        color: #222;
        letter-spacing: -0.3px;
    }

    .price-period {
        font-size: 15px;
        color: #717171;
        font-weight: 400;
    }

    .select-room-btn {
        background: #FF385C;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(255, 56, 92, 0.2);
    }

    .select-room-btn:hover {
        background: #E31C5F;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 56, 92, 0.3);
    }

    .select-room-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(255, 56, 92, 0.2);
    }

    .select-room-btn:focus {
        outline: 2px solid #FF385C;
        outline-offset: 2px;
    }

    /* Bootstrap 3 Modal Custom Styles */
    .room-modal .modal-dialog {
        width: 75%;
        max-width: 1400px;
        margin: 60px auto;
        display: flex;
        align-items: center;
        min-height: calc(100vh - 120px);
    }

    .room-modal .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        width: 100%;
    }

    .room-modal .modal-body {
        padding: 0;
    }

    .room-modal.fade .modal-dialog {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    }

    .room-modal .modal-backdrop {
        background-color: rgba(0,0,0,0.6);
    }

    .modal.fade.in .modal-dialog {
        transform: translate(0, 0);
    }

    .room-modal .close {
        position: absolute;
        top: 20px;
        right: 24px;
        z-index: 1050;
        font-size: 28px;
        font-weight: 400;
        color: #222;
        opacity: 1;
        transition: all 0.2s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: transparent;
        text-shadow: none;
    }

    .room-modal .close:hover {
        opacity: 0.7;
        color: #222;
        background: #f0f0f0;
        transform: none;
    }

    .room-modal .close:focus {
        outline: none;
    }

    .modal-layout {
        display: flex;
        width: 100%;
        height: 650px;
        max-height: 80vh;
        position: relative;
    }

    /* Modal Left - Image Slider */
    .modal-left {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .modal-carousel {
        width: 100%;
        height: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-carousel .carousel-slide {
        display: none;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-carousel .carousel-slide.active {
        display: flex;
        opacity: 1;
    }

    .modal-carousel .room-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        padding: 15px;
        border-radius: 40px;
    }

    /* Carousel Navigation Arrows */
    .modal-carousel .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(0,0,0,0.08);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 16px rgba(0,0,0,0.12);
        opacity: 0;
    }

    .modal-carousel:hover .carousel-btn {
        opacity: 1;
    }

    .modal-carousel .carousel-btn:hover {
        background: white;
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.18);
    }

    .modal-carousel .carousel-btn:active {
        transform: translateY(-50%) scale(1);
    }

    .modal-carousel .carousel-prev {
        left: 24px;
    }

    .modal-carousel .carousel-prev::before {
        content: '←';
        font-size: 26px;
        color: #222;
        font-weight: bold;
        line-height: 1;
    }

    .modal-carousel .carousel-next {
        right: 24px;
    }

    .modal-carousel .carousel-next::before {
        content: '→';
        font-size: 26px;
        color: #222;
        font-weight: bold;
        line-height: 1;
    }

    .multi_room_select {
        margin-bottom: 10px;
    }

    /* Modal Right - Details */
    .modal-right {
        width: 500px;
        padding: 48px 40px 0px 40px;
        background: #fff;
        flex-shrink: 0;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .modal-right-scrol {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        scroll-behavior: smooth;
        padding-right: 10px;
        padding-bottom: 20px;
    }

    .modal-right-scrol::-webkit-scrollbar {
        width: 6px;
    }

    .modal-right-scrol::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-right-scrol::-webkit-scrollbar-thumb {
        background: #d0d0d0;
        border-radius: 10px;
        transition: background 0.2s ease;
    }

    .modal-right-scrol::-webkit-scrollbar-thumb:hover {
        background: #b0b0b0;
    }

    /* Sticky Footer */
    .modal-sticky-footer {
        padding: 24px 0;
        border-top: 1px solid #e0e0e0;
        background: #fff;
        flex-shrink: 0;
    }

    .sticky-footer-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .sticky-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sticky-price-section {
        display: flex;
        align-items: baseline;
        gap: 8px;
    }

    .sticky-price-amount {
        font-size: 24px;
        font-weight: 700;
        color: #222;
    }

    .sticky-price-period {
        font-size: 16px;
        color: #717171;
    }

    .sticky-availability-text {
        font-size: 14px;
        color: #717171;
        text-align: right;
    }

    .sticky-footer-btn {
        width: 100%;
        padding: 16px;
        font-size: 16px;
        background: #FF385C;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(255, 56, 92, 0.2);
    }

    .sticky-footer-btn:hover {
        background: #E31C5F;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 56, 92, 0.3);
    }

    .sticky-footer-btn:active {
        transform: translateY(0);
    }

    .modal-room-title {
        font-size: 32px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #222;
        letter-spacing: -0.5px;
        line-height: 1.2;
        text-align: center;
    }
    .item-price.multi_room{
        font-size: 16px;
    }
    .modal-features {
        display: flex;
        align-items: center;
        gap: 6px;
        padding-bottom: 28px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 32px;
        flex-wrap: wrap;
        font-size: 17px;
        color: #6a6a6a;
        line-height: 1.5;
        justify-content: center;
    }

    .modal-features .feature-item {
        font-size: 17px;
        font-weight: 400;
        color: #6a6a6a;
    }

    .modal-features .feature-item:not(:last-child)::after {
        content: "·";
        margin-left: 6px;
        color: #6a6a6a;
    }

    .modal-features .feature-item i,
    .modal-features .feature-item img {
        display: none !important;
    }

    .room-description {
        margin-bottom: 32px;
    }

    .room-description h4 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #222;
        letter-spacing: -0.3px;
    }

    .room-description p {
        color: #484848;
        line-height: 1.7;
        margin: 0;
        font-size: 16px;
    }

    .amenities-title,
    .fees-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 24px;
        color: #222;
        letter-spacing: -0.3px;
    }

    .room-amenities {
        margin-bottom: 40px;
    }

    .amenities-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px 24px;
    }

    .amenity-tag {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 16px;
        padding: 0;
        width: 100%;
        transition: all 0.2s ease;
        opacity: 1;
        color: #222;
    }

    .amenities-list .amenity-tag i {
        color: #222;
        font-size: 24px;
        width: 24px;
        height: 24px;
        transition: color 0.2s ease;
        flex-shrink: 0;
        display: inline-block !important;
    }

    .amenities-list .amenity-tag img {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        display: inline-block !important;
    }

    .amenity-hidden {
        display: none !important;
    }

    .amenity-tag.amenity-extra {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .amenity-toggle {
        width: 100%;
        background: none;
        border: none;
        color: #222;
        text-decoration: underline;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        padding: 16px 0 0 0;
        text-align: left;
        transition: color 0.25s ease;
        margin-top: 8px;
    }

    .amenity-toggle:hover {
        color: #717171;
    }

    .amenity-toggle:focus {
        outline: none;
    }

    .extra-fees {
        margin-bottom: 32px;
        padding: 24px;
        background: linear-gradient(135deg, #f9f9f9 0%, #f5f5f5 100%);
        border-radius: 12px;
        border: 1px solid #e8e8e8;
    }

    .fees-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .fee-item {
        display: flex;
        justify-content: space-between;
        font-size: 16px;
        padding: 8px 0;
    }

    .fee-label {
        color: #484848;
        font-weight: 400;
    }

    .fee-amount {
        font-weight: 600;
        color: #222;
        white-space: nowrap;
    }

    .modal-booking {
        display: none !important;
    }

    .modal-booking .price-section {
        display: flex;
        align-items: baseline;
        gap: 8px;
    }

    .modal-booking .price-amount {
        font-size: 28px;
        font-weight: 700;
        color: #222;
    }

    .modal-booking .price-period {
        font-size: 16px;
        color: #717171;
    }

    .modal-booking-text {
        display: none;
    }

    .modal-booking .select-room-btn {
        width: 100%;
        padding: 16px;
        font-size: 16px;
        background: #FF385C;
        border-radius: 8px;
        font-weight: 600;
    }

    .modal-booking .select-room-btn:hover {
        background: #E31C5F;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .room-card {
            display: block;
            padding: 12px;
        }

        .room-image-container {
            display: block;
            width: 100%;
            height: auto;
        }

        .room-image-wrapper {
            padding-bottom: 56%;
            height: auto;
        }

        .room-content {
            display: block;
            padding: 16px 0 0 0;
        }

        .room-content-inner {
            gap: 8px;
        }

        .room-title {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .feature-item {
            font-size: 15px;
        }

        .room-modal .modal-dialog {
            margin: 30px auto;
            min-height: calc(100vh - 60px);
        }

        .modal-layout {
            display: flex;
            flex-direction: column;
            height: auto;
        }

        .modal-left {
            width: 100%;
            height: 400px;
            flex-shrink: 0;
        }

        .modal-carousel {
            height: 400px;
        }

        .modal-carousel .carousel-btn {
            opacity: 1;
        }

        .modal-carousel .carousel-prev {
            left: 12px;
        }

        .modal-carousel .carousel-next {
            right: 12px;
        }

        .modal-right {
            width: 100%;
            height: auto;
            max-height: 500px;
            padding: 32px 24px 0 24px;
            display: flex;
            flex-direction: column;
        }

        .modal-right-scrol {
            flex: 1;
            padding-bottom: 20px;
        }

        .modal-sticky-footer {
            padding: 20px 0;
        }

        .sticky-price-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .sticky-price-amount {
            font-size: 20px;
        }

        .sticky-availability-text {
            font-size: 13px;
            text-align: left;
        }

        .amenities-list {
            grid-template-columns: 1fr;
        }

        .amenity-tag {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .accommodation-section {
            padding: 24px 16px;
        }

        .section-header {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 24px;
        }

        .rooms-grid {
            gap: 20px;
        }

        .room-card {
            padding: 10px;
        }

        .room-title {
            font-size: 17px;
        }

        .feature-item {
            font-size: 14px;
        }

        .room-modal .modal-dialog {
            width: 100%;
            margin: 0;
            min-height: 100vh;
        }

        .room-modal .modal-content {
            border-radius: 0;
            height: 100vh;
        }

        .modal-layout {
            height: 100vh;
        }

        .modal-left {
            height: 300px;
            flex-shrink: 0;
        }

        .modal-carousel {
            height: 300px;
        }

        .modal-carousel .carousel-btn {
            width: 36px;
            height: 36px;
            opacity: 1;
        }

        .modal-carousel .carousel-prev {
            left: 8px;
        }

        .modal-carousel .carousel-next {
            right: 8px;
        }

        .modal-carousel .carousel-prev::before,
        .modal-carousel .carousel-next::before {
            font-size: 20px;
        }

        .modal-right {
            height: calc(100vh - 300px);
            max-height: none;
            padding: 24px 20px 0 20px;
            display: flex;
            flex-direction: column;
        }

        .modal-right-scrol {
            flex: 1;
            padding-bottom: 20px;
        }

        .modal-sticky-footer {
            padding: 16px 0;
        }

        .sticky-price-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .sticky-price-amount {
            font-size: 22px;
        }

        .sticky-availability-text {
            font-size: 13px;
            text-align: left;
        }

        .sticky-footer-btn {
            padding: 14px;
        }

        .modal-room-title {
            font-size: 24px;
        }

        .modal-features {
            font-size: 15px;
        }

        .amenities-title,
        .fees-title {
            font-size: 20px;
        }

        .amenity-tag {
            font-size: 15px;
        }

        .room-modal .close {
            top: 12px;
            right: 12px;
            font-size: 24px;
        }
    }
</style>

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