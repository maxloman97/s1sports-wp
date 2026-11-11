<?php
/**
 * Plugin Name: S1 Sports Content
 * Description: Athlete content model for the S1 Sports theme.
 * Version: 0.4.0
 * Requires PHP: 8.0
 * Text Domain: s1sports-content
 */
defined('ABSPATH') || exit;

require_once __DIR__ . '/inc/contact.php';

add_action('init', function () {
    // The production site already owns this post type. Register it only in fresh local installs.
    if (!post_type_exists('atleter')) {
        register_post_type('atleter', [
            'labels' => ['name' => 'Atleter', 'singular_name' => 'Atlet'],
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
            'rewrite' => ['slug' => 'atleter'],
            'menu_icon' => 'dashicons-groups',
        ]);
    }
}, 20);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;
    acf_add_local_field_group([
        'key' => 'group_s1_athlete',
        'title' => 'S1 Sports — Visning',
        'fields' => [
            ['key' => 'field_s1_featured', 'label' => 'Visa på startsidan', 'name' => 's1_featured', 'type' => 'true_false', 'default_value' => 0],
            ['key' => 'field_s1_display_order', 'label' => 'Visningsordning', 'name' => 's1_display_order', 'type' => 'number', 'default_value' => 0],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'atleter']]],
        'position' => 'normal',
        'style' => 'default',
        'active' => true,
    ]);
});
