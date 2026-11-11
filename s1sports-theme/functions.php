<?php
defined('ABSPATH') || exit;
require_once get_template_directory() . '/inc/snapshot.php';
require_once get_template_directory() . '/inc/athletes.php';
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => __('Primary menu', 's1sports')]);
});
add_action('wp_enqueue_scripts', function () {
    if (s1_snapshot_details()) return;
    wp_enqueue_style('s1sports', get_stylesheet_uri(), [], '0.1.0');
    wp_enqueue_script('s1sports', get_template_directory_uri() . '/assets/theme.js', [], '0.1.0', true);
});
function s1_lang(): string {
    if (has_filter('wpml_current_language')) return (string) apply_filters('wpml_current_language', null);
    $slug = get_post_field('post_name', get_queried_object_id());
    return in_array($slug, ['home', 'junior-development', 'about-us', 'pro-consulting', 'our-services', 'our-athletes', 'contact', 'college-recruiting', 'book-a-meeting'], true) ? 'en' : 'sv';
}
function s1_text(string $sv, string $en): string { return s1_lang() === 'en' ? $en : $sv; }
function s1_url(string $sv, string $en): string { return esc_url(s1_lang() === 'en' ? home_url($en) : home_url($sv)); }
function s1_switcher(): void {
    if (has_filter('wpml_active_languages')) {
        $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
        if (is_array($languages)) {
            echo '<nav class="lang-switch" aria-label="' . esc_attr__('Languages', 's1sports') . '">';
            foreach (['sv', 'en'] as $code) {
                if (!isset($languages[$code])) continue;
                $item = $languages[$code];
                $url = !empty($item['url']) ? $item['url'] : ($code === 'sv' ? 'https://s1sports.se/' : 'https://s1sports.co/');
                echo '<a href="' . esc_url($url) . '" lang="' . esc_attr($code) . '"' . (!empty($item['active']) ? ' aria-current="page"' : '') . '>' . esc_html(strtoupper($code)) . '</a>';
            }
            echo '</nav>';
            return;
        }
    }
    echo '<nav class="lang-switch" aria-label="Languages"><a href="https://s1sports.se/">SV</a><a href="https://s1sports.co/">EN</a></nav>';
}
function s1_asset(string $name): string {
    static $media = null;
    if ($media === null) {
        $file = get_template_directory() . '/data/media-urls.json';
        $media = is_readable($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    }
    return esc_url($media[$name] ?? get_template_directory_uri() . '/assets/' . $name);
}

