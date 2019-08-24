<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// при удалении аддона удаляем из wp_usermeta ключи vrfd_profile

$meta_type  = 'user';
$user_id    = 0;                // у всех пользователей.
$meta_key   = 'vrfd_profile';
$meta_value = '';               // любые значения.
$delete_all = true;             // удаляем все

delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );
