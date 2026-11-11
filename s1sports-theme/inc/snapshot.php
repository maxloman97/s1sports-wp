<?php
defined('ABSPATH') || exit;

function s1_snapshot_details(): ?array {
    if (!is_page() && !is_front_page()) return null;
    $locale = s1_lang() === 'en' ? 'en' : 'sv';
    $slug = is_front_page() ? 'home' : get_post_field('post_name', get_queried_object_id());
    $slug = sanitize_file_name((string) $slug);
    $file = get_template_directory() . '/snapshots/' . $locale . '-' . $slug . '.html';
    if (!is_readable($file)) return null;
    $manifest_file = get_template_directory() . '/snapshots/manifest.json';
    $manifest = json_decode((string) file_get_contents($manifest_file), true);
    foreach ((array) $manifest as $item) {
        if ($item['locale'] === $locale && $item['slug'] === $slug) {
            return ['file' => $file, 'css' => $item['css'], 'locale' => $locale];
        }
    }
    return null;
}

function s1_mirror_switcher_html(): string {
    $current = s1_lang() === 'en' ? 'en' : 'sv';
    $other = $current === 'sv' ? 'en' : 'sv';
    $languages = has_filter('wpml_active_languages') ? apply_filters('wpml_active_languages', null, ['skip_missing' => 0]) : [];
    if (!is_array($languages)) $languages = [];
    $current_name = $current === 'sv' ? 'Svenska' : 'English';
    $other_name = $other === 'sv' ? 'Svenska' : 'English';
    $other_url = $languages[$other]['url'] ?? s1_local_translation_url($other);
    $flag = $current === 'sv' ? get_template_directory_uri() . '/assets/mirror/sv.svg' : 'https://s1sports.se/wp-content/uploads/flags/Flag_of_the_United_States.svg';
    $other_flag = $other === 'sv' ? get_template_directory_uri() . '/assets/mirror/sv.svg' : 'https://s1sports.se/wp-content/uploads/flags/Flag_of_the_United_States.svg';
    $switcher_label = $current === 'sv' ? 'Språkväljare' : 'Language Switcher';
    $toggle_label = $current === 'sv' ? 'Svenska – Språkväljare, klicka för att öppna och använd sedan tab för att navigera' : 'English – Language switcher, click to open then tab to navigate';
    return '<div role="navigation" aria-label="' . esc_attr($switcher_label) . '" class="wpml-ls-statics-shortcode_actions wpml-ls wpml-ls-legacy-dropdown-click js-wpml-ls-legacy-dropdown-click s1-mirror-language"><ul><li class="wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-' . esc_attr($current) . ' wpml-ls-current-language wpml-ls-first-item wpml-ls-item-legacy-dropdown-click"><a href="#" hreflang="' . esc_attr($current) . '" lang="' . esc_attr($current) . '" class="js-wpml-ls-item-toggle wpml-ls-item-toggle" aria-current="page" aria-expanded="false" aria-controls="wpml-ls-submenu-click-default" aria-haspopup="true" aria-label="' . esc_attr($toggle_label) . '" tabindex="0" role="button"><img class="wpml-ls-flag" src="' . esc_url($flag) . '" alt="" loading="lazy" width="24" height="24"><span class="wpml-ls-native">' . esc_html($current_name) . '</span></a><ul id="wpml-ls-submenu-click-default" class="js-wpml-ls-sub-menu wpml-ls-sub-menu"><li class="wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-' . esc_attr($other) . '"><a href="' . esc_url($other_url) . '" hreflang="' . esc_attr($other) . '" lang="' . esc_attr($other) . '" class="wpml-ls-link"><img class="wpml-ls-flag" src="' . esc_url($other_flag) . '" alt="" loading="lazy" width="24" height="24"><span class="wpml-ls-native">' . esc_html($other_name) . '</span></a></li></ul></li></ul></div>';
}

function s1_local_translation_url(string $target): string {
    $pairs = [
        'start' => 'home', 'junior-utveckling' => 'junior-development',
        'om-oss' => 'about-us', 'professionell-radgivning' => 'pro-consulting',
        'vara-tjanster' => 'our-services', 'vara-idrottare' => 'our-athletes',
        'kontakt' => 'contact', 'college-rekrytering' => 'college-recruiting',
        'boka-mote' => 'book-a-meeting',
    ];
    $slug = (string) get_post_field('post_name', get_queried_object_id());
    if ($target === 'en') $translated = $pairs[$slug] ?? 'home';
    else $translated = array_search($slug, $pairs, true) ?: 'start';
    return $translated === 'start' ? home_url('/') : home_url('/' . $translated . '/');
}

function s1_render_snapshot_if_available(): bool {
    $details = s1_snapshot_details();
    if (!$details) return false;
    $html = (string) file_get_contents($details['file']);
    $html = str_replace('{{THEME}}', get_template_directory_uri(), $html);
    $html = s1_render_dynamic_athletes($html);
    $social_div = $details['locale'] === 'en' ? '143' : '138';
    $social_divider = $details['locale'] === 'en' ? '159' : '127';
    $html = preg_replace('~<div class="bde-div-108-' . $social_div . ' bde-div">[\s\S]*?(?=<div class="bde-fancy-divider-108-' . $social_divider . ' bde-fancy-divider">)~', '', $html, 1);
    $html = preg_replace('/<div role="navigation" aria-label="(?:Språkväljare|Language Switcher|Language switcher)" class="wpml-ls-statics-shortcode_actions[\s\S]*?<\/div>/i', '<!--S1_LANGUAGE_SWITCHER-->', $html, 1);
    $html = str_replace('<!--S1_LANGUAGE_SWITCHER-->', s1_mirror_switcher_html(), $html);
    $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $html);
    $html = str_replace('https://s1sports.se/junior-development/', 'https://s1sports.se/junior-utveckling/', $html);
    $html = str_replace('https://s1sports.co/college/recruiting/', 'https://s1sports.co/college-recruiting/', $html);
    $html = str_replace(['href="https://s1sports.se/om-oss"', 'href="https://s1sports.se/kontakt"'], ['href="https://s1sports.se/om-oss/"', 'href="https://s1sports.se/kontakt/"'], $html);
    $html = str_replace(['href="/om-oss"', 'href="/kontakt"', 'href="/college-rekrytering"'], ['href="/om-oss/"', 'href="/kontakt/"', 'href="/college-rekrytering/"'], $html);
    if (!has_filter('wpml_current_language')) {
        $html = preg_replace_callback('~href=(\x22|\x27)https://s1sports\.(se|co)(/[^\x22\x27]*)?\1~i', function ($match) {
            $path = $match[3] ?? '/';
            if ($path === '/' && $match[2] === 'co') $path = '/home/';
            return 'href=' . $match[1] . esc_url(home_url($path)) . $match[1];
        }, $html);
    }
    if (in_array(get_post_field('post_name', get_queried_object_id()), ['boka-mote', 'book-a-meeting'], true)) {
        $form_attributes = ' data-s1-contact="1" data-s1-endpoint="' . esc_url(admin_url('admin-ajax.php')) . '" data-s1-nonce="' . esc_attr(wp_create_nonce('s1_contact')) . '"';
        $html = preg_replace('/<form\b/i', '<form' . $form_attributes, $html, 1);
    }
    ?><!doctype html><html <?php if (has_filter('wpml_current_language')) language_attributes(); else echo 'lang="' . esc_attr($details['locale'] === 'en' ? 'en-US' : 'sv-SE') . '"'; ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php
    foreach ($details['css'] as $css) {
        echo '<link rel="stylesheet" href="' . esc_url(get_template_directory_uri() . '/' . $css) . '">';
    }
    echo '<link rel="stylesheet" href="' . esc_url(get_template_directory_uri() . '/assets/mirror.css?ver=0.9.8') . '">';
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wdth,wght@0,75..100,100..900;1,75..100,100..900&amp;display=swap">';
    wp_head();
    ?></head><body <?php body_class('breakdance'); ?>><?php wp_body_open(); echo $html; wp_footer(); ?><script src="<?php echo esc_url(get_template_directory_uri() . '/assets/swiper-bundle.min.js'); ?>"></script><script src="<?php echo esc_url(get_template_directory_uri() . '/assets/mirror.js?ver=0.9.0'); ?>"></script></body></html><?php
    return true;
}
