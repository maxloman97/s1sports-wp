<?php
defined('ABSPATH') || exit;

function s1_athlete_seed(): array {
    static $seed = null;
    if ($seed !== null) return $seed;
    $file = get_template_directory() . '/data/athletes.json';
    $rows = is_readable($file) ? json_decode((string) file_get_contents($file), true) : [];
    $seed = [];
    foreach ((array) $rows as $row) $seed[sanitize_title($row['name'])] = $row;
    return $seed;
}

function s1_athlete_field(string $name, int $post_id): mixed {
    if (function_exists('get_field')) {
        $value = get_field($name, $post_id);
        if ($value !== null && $value !== false && $value !== '') return $value;
    }
    return get_post_meta($post_id, $name, true);
}

function s1_athlete_posts(): array {
    if (!post_type_exists('atleter')) return [];
    $posts = get_posts(['post_type' => 'atleter', 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => false]);
    if (has_filter('wpml_current_language')) {
        // Use a translated profile when available, then fill gaps with the source profile.
        $all = get_posts(['post_type' => 'atleter', 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => true]);
        $seen = [];
        $merged = [];
        foreach (array_merge($posts, $all) as $post) {
            $key = sanitize_title($post->post_title);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $merged[] = $post;
        }
        $posts = $merged;
    }
    $seed = s1_athlete_seed();
    usort($posts, static function ($a, $b) use ($seed) {
        $oa = (int) s1_athlete_field('s1_display_order', $a->ID) ?: (int) ($seed[sanitize_title($a->post_title)]['order'] ?? 999);
        $ob = (int) s1_athlete_field('s1_display_order', $b->ID) ?: (int) ($seed[sanitize_title($b->post_title)]['order'] ?? 999);
        return $oa <=> $ob ?: strcmp($a->post_title, $b->post_title);
    });
    return $posts;
}

function s1_athlete_card(WP_Post $post, bool $slide): string {
    $seed = s1_athlete_seed()[sanitize_title($post->post_title)] ?? [];
    $name = (string) (s1_athlete_field('namn', $post->ID) ?: $post->post_title);
    $sport = (string) (s1_athlete_field('sport', $post->ID) ?: ($seed['sport'] ?? ''));
    $college = (string) (s1_athlete_field('skola', $post->ID) ?: ($seed['college'] ?? ''));
    $profile = s1_athlete_field('profilbild', $post->ID);
    $image = '';
    if (is_array($profile)) $image = (string) ($profile['sizes']['large'] ?? $profile['url'] ?? '');
    elseif (is_numeric($profile)) $image = (string) wp_get_attachment_image_url((int) $profile, 'large');
    elseif (is_string($profile) && filter_var($profile, FILTER_VALIDATE_URL)) $image = $profile;
    if (!$image) $image = get_the_post_thumbnail_url($post->ID, 'large');
    if (!$image && !empty($seed['local_image'])) {
        $fallback = (string) $seed['local_image'];
        $image = str_starts_with($fallback, 'https://') ? $fallback : get_template_directory_uri() . '/' . $fallback;
    }
    $sport_key = sanitize_title($sport);
    $wrapper = $slide ? '<article class="bde-loop-item ee-post swiper-slide" data-sport="' . esc_attr($sport_key) . '">' : '<div class="athlete-card-wrapper" data-sport="' . esc_attr($sport_key) . '">';
    $end = $slide ? '</article>' : '</div>';
    $style = $image ? ' style="background-image:url(' . esc_url($image) . ')"' : '';
    return $wrapper . '<div class="bde-div flip-card bde-div-62-104"><div id="flip-card__inner" class="bde-div bde-div-62-105"><div class="bde-div bde-div-62-106"' . $style . '><h3 class="bde-heading bde-heading-62-119">' . esc_html($name) . '</h3><div class="bde-text bde-text-62-108">' . esc_html($sport) . '</div></div><div class="bde-div bde-div-62-107"><div class="bde-text bde-text-62-109">' . esc_html($name) . '</div><div class="bde-text bde-text-62-110">' . esc_html($college) . '</div><div class="bde-text bde-text-62-120"></div></div></div></div>' . $end;
}

function s1_render_dynamic_athletes(string $html): string {
    $posts = s1_athlete_posts();
    if (!$posts) return $html;
    $slug = (string) get_post_field('post_name', get_queried_object_id());
    if (in_array($slug, ['vara-idrottare', 'our-athletes'], true)) {
        $cards = implode('', array_map(static fn($post) => s1_athlete_card($post, false), $posts));
        return preg_replace('/(<div id="my-ajax-loop" class="[^"]*">)[\s\S]*?(<\/div>)/', '$1' . $cards . '$2', $html, 1);
    }
    $seed = s1_athlete_seed();
    $featured = array_values(array_filter($posts, static function ($post) use ($seed) {
        $field = s1_athlete_field('s1_featured', $post->ID);
        return $field !== '' ? (bool) $field : !empty($seed[sanitize_title($post->post_title)]['featured']);
    }));
    $featured = array_slice($featured, 0, 8);
    if (!$featured) return $html;
    $cards = implode('', array_map(static fn($post) => s1_athlete_card($post, true), $featured));
    return preg_replace_callback('/(?:<article class="bde-loop-item ee-post swiper-slide">[\s\S]*?<\/article>)+/', static function ($match) use ($cards) {
        return str_contains($match[0], 'flip-card') ? $cards : $match[0];
    }, $html, 1);
}
