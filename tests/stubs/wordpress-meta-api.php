<?php

/**
 * Minimal stand-ins for WordPress's meta API: they record calls instead of writing.
 */

function wp_slash($value)
{
    return is_string($value) ? addslashes($value) : $value;
}

function update_metadata($type, $id, $key, $value)
{
    $GLOBALS['colt_meta_api_calls'][] = ['update', $type, $id, $key, $value];

    return ($GLOBALS['colt_meta_api_stored'] ?? null) === $value ? false : true;
}

function add_metadata($type, $id, $key, $value)
{
    $GLOBALS['colt_meta_api_calls'][] = ['add', $type, $id, $key, $value];

    return 1;
}

function get_metadata($type, $id, $key, $single)
{
    return $GLOBALS['colt_meta_api_stored'] ?? '';
}
