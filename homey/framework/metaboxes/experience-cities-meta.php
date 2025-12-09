<?php
/* Add metaboxes to experience city */

if ( !function_exists( 'homey_experience_city_add_meta_fields' ) ) :
    function homey_experience_city_add_meta_fields() {
        $homey_meta = homey_get_experience_city_meta();
        ?>

        <div class="form-field">
            <label><?php esc_html_e('Which state has this city?', 'homey' ); ?></label>
            <select name="homey[parent_state]" class="widefat">
                <option value=""><?php esc_html_e('Select State', 'homey'); ?></option>
                <?php echo homey_get_exp_all_states(); ?>
            </select>
            <p class="description"><?php esc_html_e('Select state which has this city.', 'homey' ); ?></p>
        </div>



        <?php
    }
endif;

add_action( 'experience_city_add_form_fields', 'homey_experience_city_add_meta_fields', 10, 2 );


/**
 *   ------------------------------------------------------------------------------
 *   2.0 - Edit meta field
 *   ------------------------------------------------------------------------------
 */

if ( !function_exists( 'homey_experience_city_edit_meta_fields' ) ) :
    function homey_experience_city_edit_meta_fields( $term ) {
        $homey_meta = homey_get_experience_city_meta();

        if(is_object ($term)) {
            $term_id      =  $term->term_id;
            $term_meta    =  get_option( "_homey_experience_city_$term_id" );
            $parent_state  =  $term_meta['parent_state'] ? $term_meta['parent_state'] : '';
            $all_states   =  homey_get_exp_all_states($parent_state);

        } else {
            $all_states   =  homey_get_exp_all_states();
        }
        ?>

        <tr class="form-field">
            <th scope="row" valign="top"><label><?php esc_html_e('Which state has this city?', 'homey' ); ?></label></th>
            <td>
                <select name="homey[parent_state]" class="widefat">
                    <option value=""><?php esc_html_e('Select State', 'homey'); ?></option>
                    <?php echo ''.$all_states; ?>
                </select>
                <p class="description"><?php esc_html_e('Select state which has this city.', 'homey' ); ?></p>
            </td>
        </tr>

        <?php
    }
endif;

add_action( 'experience_city_edit_form_fields', 'homey_experience_city_edit_meta_fields', 10, 2 );


if ( !function_exists( 'homey_save_experience_city_meta_fields' ) ) :
    function homey_save_experience_city_meta_fields( $term_id ) {

        if ( isset( $_POST['homey'] ) ) {

            $homey_meta = array();

            $homey_meta['parent_state'] = isset( $_POST['homey']['parent_state'] ) ? $_POST['homey']['parent_state'] : '';

            update_option( '_homey_experience_city_'.$term_id, $homey_meta );
        }

    }
endif;

add_action( 'edited_experience_city', 'homey_save_experience_city_meta_fields', 10, 2 );
add_action( 'create_experience_city', 'homey_save_experience_city_meta_fields', 10, 2 );
?>