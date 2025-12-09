<?php
/**
 * Template Name: Compare Experiences
 */
get_header();
global $homey_local, $homey_prefix;


if(isset($_COOKIE['homey_compare_experiences'])){
    $ids = explode(',', $_COOKIE['homey_compare_experiences']);
}

if(!isset($_COOKIE['homey_compare_experiences'])){
    $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : '';
}

$basic_info_escaped = $exp_price = $experience_title = $exp_guests = $exp_type = '';
$counter   =  0;
$experiences  =  array();
$hide_labels = homey_option('show_hide_labels');
?>

<section class="main-content-area listing-page listing-page-full-width">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <h1 class="experience-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php
                if( !empty($ids) ) {
                $args = array(
                    'post_type' => 'experience',
                    'post__in' => $ids,
                    'post_status' => 'publish',
                    'order' => 'ASC',
                    'orderby' => 'post__in'
                );

                //do the query
                $the_query = New WP_Query($args);
                if( $the_query->have_posts() ): while( $the_query->have_posts() ): $the_query->the_post();

//                    if( homey_booking_type() == 'per_hour') {
//                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'hour_price', true );
//                    } else {
//                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
//                    }

                    $night_price = homey_exp_get_price();

                    $guests       = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
                    $experience_type = homey_taxonomy_simple('experience_type');

                    $basic_info_escaped .= '
                            <th><a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail( get_the_id(), 'homey-listing-thumb', array( 'class' => 'img-responsive' ) ).'</a></th>';

                    
                    $experience_title .= '<td>' . get_the_title() . '</td>';
                    
                    if( !empty($night_price) ) {

                        $exp_price .= '<td>'.homey_formatted_price($night_price, false, true).'/'. esc_html__('Person', 'homey').'</td>';
                    } else {
                        $exp_price .= '<td>---</td>';
                    }

                    if( !empty($guests) ) {
                        $exp_guests .= '<td>'.$guests.'</td>';
                    } else {
                        $exp_guests .= '<td>---</td>';
                    }

                    if( !empty($experience_type) ) {
                        $exp_type .= '<td>'.$experience_type.'</td>';
                    } else {
                        $exp_type .= '<td>---</td>';
                    }

                    $counter++;

                endwhile; endif;
                ?>

                <div class="compare-table">
                    <table class="table-striped table-hover">
                        <thead>
                            <tr>
                                <th><!-- empty --></th>
                                <?php 
                                // This variable has been safely escaped in the same file: line 54
                                echo ''.$basic_info_escaped; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php esc_html_e('Title', 'homey'); ?></strong></td>
                                <?php echo wp_kses($experience_title, homey_allowed_html()); ?>
                            </tr>
                            
                            <tr>
                                <td><strong><?php esc_html_e('Price', 'homey'); ?></strong></td>
                                <?php echo wp_kses($exp_price, homey_allowed_html()); ?>
                            </tr>
                            
                            <?php if($hide_labels['experience_sn_type_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('experience_sn_type_label'); ?></strong></td>
                                <?php echo wp_kses($exp_type, homey_allowed_html()); ?>
                            </tr>
                            <?php } ?>

                            <?php
                            $all_amenities = get_terms( array( 'taxonomy' => 'experience_amenity', 'fields' => 'names' ) );
                            $compare_terms = array();

                            foreach ( $ids as $post_ID ) :

                            $compare_terms[ $post_ID ] = wp_get_post_terms( $post_ID, 'experience_amenity', array( 'fields' => 'names' ) );

                            endforeach;

                            foreach ( $all_amenities as $data ) :

                            ?>
                            <tr>
                                <td><strong><?php echo wp_kses($data, homey_allowed_html()); ?></strong></td>
                                <?php

                                foreach ( $ids as $post_ID ) :

                                    if ( in_array( $data, $compare_terms[ $post_ID ] ) ) :

                                        echo '<td><i class="homey-icon homey-icon-check-circle-1 text-success"></i></td>';

                                    else :

                                        echo '<td><i class="homey-icon homey-icon-close text-danger"></i></td>';

                                    endif;

                                endforeach;

                                ?>
                            </tr>
                            <?php
                            endforeach;
                            ?>
                            
                        </tbody>
                    </table>
                </div>

                <?php } ?>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area experience-page grid-experience-page -->


<?php get_footer(); ?>
