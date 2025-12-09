<?php
global $homey_local, $homey_prefix;

$get_languages = array();
$get_languages = isset ( $_GET['language'] ) ? $_GET['language'] : $get_languages;


if( taxonomy_exists('experience_language') ) {
    $languages = get_terms(
        array(
            "experience_language"
        ),
        array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        )
    );
    $languages_count = count($get_languages);
    $checked_language = '';
    $count = 0;
    if (!empty($languages)) { ?>

        <div class="filters-wrap">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                    <div class="filters">
                        <strong><?php echo esc_attr(homey_option('srh_host_language')); ?></strong>
                    </div>
                </div>
                <div class="languages-list col-xs-12 col-sm-12 col-md-9 col-lg-9">

                    <?php
                    $total_languages = count($languages);
                    $id_conflict_resolver = random_int(0, 999);

                    foreach ($languages as $language):
                        $count++;

                        if (in_array($language->slug, $get_languages)) {
                            $checked_language = $language->slug;
                        }

                        if($count == 1) {
                            echo '<div class="filters">';
                        }

                        if($count == 7) {
                            echo '<div class="collapse" id="collapseLanguages'.$id_conflict_resolver.'">
                                    <div class="filters">';
                        }
                            echo '<label class="control control--checkbox">';
                                echo '<input name="language[]" type="checkbox" '.checked( $checked_language, $language->slug, false ).' value="' . esc_attr( $language->slug ) . '">';
                                echo '<span class="contro-text">'.esc_attr( $language->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';

                        if( ($count == 6) || ($count < 6 && $count == $total_languages) ) {
                            echo '</div>';
                        }

                        if( ($count > 6) && ($count == $total_languages) ) {
                            echo '</div></div>';
                        }

                    endforeach;
                    ?>
                </div>

                <?php if($total_languages > 6 ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
                    <div class="filters">
                        <a role="button" data-toggle="collapse" data-target="#collapseLanguages<?php echo $id_conflict_resolver;?>" aria-expanded="false" aria-controls="collapseLanguages<?php echo $id_conflict_resolver;?>">
                            <span class="filter-more-link"><?php echo esc_attr($homey_local['search_more']); ?></span> 
                            <i class="homey-icon homey-icon-navigation-menu-vertical" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <?php } ?>

            </div><!-- featues row -->
        </div><!-- .filters-wrap -->

    <?php    
    }
}
?>