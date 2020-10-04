<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */



// добавим в админку
add_filter( 'rcl_default_profile_fields', 'vrfd_admin_profile_field', 10, 2 );
function vrfd_admin_profile_field( $fields ) {
    $fields[] = array(
        'type'   => 'select',
        'slug'   => 'vrfd_profile',
        'values' => [ 'Не подтверждён', 'Это подтверждённый профиль' ],
        'title'  => 'Подтверждение профиля'
    );

    return $fields;
}

// и немного обрежем там что не надо
add_filter( 'rcl_field_options', 'vrfd_exclude_variations', 20, 3 );
function vrfd_exclude_variations( $options, $field, $manager_id ) {
    // это не страница "поля профиля"
    if ( $manager_id !== 'profile' )
        return $options;

    // это не наше поле
    if ( isset( $field->slug ) && ( $field->slug !== 'vrfd_profile' ) )
        return $options;

    // оставим "редактируется только администрацией сайта"
    foreach ( $options as $k => $option ) {
        if ( $option['slug'] != 'admin' ) {
            unset( $options[$k] );
        }
    }

    $options['admin']['values'] = array( 'Да' );

    return $options;
}

/**
 * Это верифицированный пользователь?
 *
 * @since 1.3
 *
 * @param int $user_id  id проверяемого.
 *
 * @return bool         'true' - верифицирован.
 *                      'false' - не верифицирован.
 */
function vrfd_is_verified( $user_id ) {
    if ( ! $user_id )
        return false;

    $is_verified = get_user_meta( $user_id, 'vrfd_profile', true );

    if ( ! $is_verified ) {
        return false;
    }

    if ( $is_verified === 'Это подтверждённый профиль' ) {
        return true;
    } else {
        return false;
    }
}

// добавим в профиль
add_filter( 'rcl_profile_fields', 'vrfd_add_form', 10, 2 );
function vrfd_add_form( $fields, $args ) {
    $very = [ 'Не подтверждён', 'Это подтверждённый профиль' ];
    $type = 'select';

    // чтоб сам юзер не менял себе значение
    if ( ! current_user_can( 'manage_options' ) ) {
        $very = ( ! vrfd_is_verified( $args['user_id'] ) ) ? 'Не подтверждён' : 'Это подтверждённый профиль';

        $type = 'hidden';
    }

    foreach ( $fields as $field ) {
        if ( $field['slug'] === 'vrfd_profile' ) {
            $field['type'] = $type;

            if ( $type == 'select' ) {
                $field['values'] = $very;
            } else if ( $type == 'hidden' ) {
                $field['value'] = $very;
            }
        }

        $opt[] = $field;
    }

    return $opt;
}

// запрет удалением реколл данных если вырезали их во фронте
add_filter( 'rcl_pre_update_profile_field', 'vrfd_skip_delete_field_in_core' );
function vrfd_skip_delete_field_in_core( $field ) {
    if ( is_admin() )
        return $field;

    if ( $field['slug'] === 'vrfd_profile' ) {
        return false;
    }

    return $field;
}

// выведем после имени
add_action( 'wp_footer', 'vrfd_after_title', 5 );
function vrfd_after_title() {
    if ( ! rcl_is_office() )
        return;

    // у этого допа есть хук
    if ( rcl_exist_addon( 'theme-control' ) )
        return;

    global $user_LK;

    if ( ! vrfd_is_verified( $user_LK ) )
        return;

    $div = '.office-title > h2,.cab_ln_title > h2,.office-content-top > h2,.ao_name_author_lk > h2,.cab_lt_title > h2, .cab_title > h2';

    $blk = vrfd_get_icon();

    // Поместим блок после имени
    echo "<script>jQuery(document).ready(function(){jQuery('$div').append('$blk');});</script>";
}

// у Theme Control есть хук у имени - выведем там
add_filter( 'tcl_name', 'vrfd_after_title_theme_control', 20 );
function vrfd_after_title_theme_control( $data ) {
    global $user_LK;

    if ( ! vrfd_is_verified( $user_LK ) )
        return $data;

    return $data . vrfd_get_icon();
}

function vrfd_get_icon() {
    $blk = '<sup title="Профиль подтверждён" class="vrfd_block" style="align-self:center;margin:0 6px;text-shadow:none;">';
    $blk .= '<i class="rcli fa-check-circle-o" style="font-size:20px;color:#63bd56;display:inline-block !important;line-height:1;vertical-align:middle;text-shadow:none;"></i>';
    $blk .= '</sup>';

    return $blk;
}

// отдельно вырежем данные в фронте
add_action( 'wp_footer', 'vrfd_hide_data', 5 );
function vrfd_hide_data() {
    global $user_ID;

    if ( ! rcl_is_office( $user_ID ) )
        return;

    $out = "<script>
rcl_add_action('rcl_footer','vrfd_hide');
rcl_add_action('rcl_upload_tab','vrfd_hide');
function vrfd_hide(){jQuery('#rcl-office #profile-field-vrfd_profile').remove();}
</script>";
    echo $out;
}

// стили
add_filter( 'rcl_inline_styles', 'vrfd_inline_styles', 10 );
function vrfd_inline_styles( $styles ) {
    global $user_ID;

    if ( ! rcl_is_office( $user_ID ) )
        return $styles;

    $styles .= '#rcl-office #profile-field-vrfd_profile{display:none;}';

    return $styles;
}
