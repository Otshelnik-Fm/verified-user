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
add_filter( 'rcl_custom_field_options', 'vrfd_exclude_variations', 10, 3 );
function vrfd_exclude_variations( $options, $field, $post_type ) {
    // это не страница "поля профиля"
    if ( $post_type !== 'profile' )
        return $options;

    // это не наше поле
    if ( isset( $field['slug'] ) && ( $field['slug'] !== 'vrfd_profile' ) )
        return $options;

    // что нам не нужно - удалим
    foreach ( $options as $option ) {
        // первое значение
        if ( $option['slug'] == 'empty-first' )
            continue;

        // подпись к полю
        if ( $option['slug'] == 'notice' )
            continue;

        // добавляемые значения
        if ( $option['slug'] == 'values' )
            continue;

        // отображать для других пользователей
        if ( $option['slug'] == 'req' )
            continue;

        // редактируется администрацией
        if ( $option['slug'] == 'admin' ) {
            $option['values'] = array( 'Да' );
        }

        // отображать в заказе
        if ( $option['slug'] == 'order' )
            continue;

        // обязательное поле
        if ( $option['slug'] == 'required' )
            continue;

        // Макс. кол-во знаков
        if ( $option['slug'] == 'maxlength' )
            continue;

        // отображать в форме регистрации
        if ( $option['slug'] == 'register' )
            continue;

        // Фильтровать пользователей по значению этого поля
        if ( $option['slug'] == 'filter' )
            continue;

        $opt[] = $option;
    }

    return $opt;
}

// добавим в профиль
add_filter( 'rcl_profile_fields', 'vrfd_add_form', 10 );
function vrfd_add_form( $fields ) {
    foreach ( $fields as $field ) {
        if ( $field['slug'] === 'vrfd_profile' ) {
            $field['values'] = [ 'Не подтверждён', 'Это подтверждённый профиль' ];
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
        return false;

    global $user_LK;

    $is_verified = get_user_meta( $user_LK, 'vrfd_profile', true );

    if ( ! $is_verified || $is_verified === 'Не подтверждён' )
        return;

    $div = '.tcl_name_left,.office-title > h2,.cab_ln_title > h2,.office-content-top > h2,.ao_name_author_lk > h2,.cab_lt_title > h2, .cab_title > h2';

    $blk = '<sup title="Это подтверждённый профиль" class="vrfd_block" style="align-self:center;margin:0 6px;text-shadow:none;">';
    $blk .= '<i class="rcli fa-check-circle-o" style="font-size:20px;color:#71f25e;display:inline-block !important;line-height:1;vertical-align:middle;text-shadow:none;"></i>';
    $blk .= '</sup>';

    // Поместим блок после имени
    $out = "<script>
jQuery(document).ready(function(){
jQuery('$div').append('$blk');
});
</script>";
    echo $out;
}

// отдельно вырежем данные в фронте
add_action( 'wp_footer', 'vrfd_hide_data', 5 );
function vrfd_hide_data() {
    global $user_ID;

    if ( ! rcl_is_office( $user_ID ) )
        return false;

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
    if ( ! rcl_is_office() )
        return $styles;

    $styles .= '
#rcl-office #profile-field-vrfd_profile {
    display: none;
}
';
    return $styles;
}
