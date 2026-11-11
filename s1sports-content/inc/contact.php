<?php
defined('ABSPATH') || exit;

add_action('init', function (): void {
    register_post_type('s1_inquiry', [
        'labels' => [
            'name' => 'Mötesförfrågningar',
            'singular_name' => 'Mötesförfrågan',
            'menu_name' => 'Mötesförfrågningar',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'exclude_from_search' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-email-alt',
    ]);
});

function s1_handle_contact(): void {
    if (!check_ajax_referer('s1_contact', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid request.'], 403);
    }
    $fields = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : [];
    if (!empty($fields['hpname'])) wp_send_json_success(['saved' => false, 'mail_sent' => false]);

    $name = sanitize_text_field($fields['name'] ?? '');
    $sport = sanitize_text_field($fields['osycwv'] ?? '');
    $email = sanitize_email($fields['email'] ?? '');
    $phone = sanitize_text_field($fields['ahegzq'] ?? '');
    $message = sanitize_textarea_field($fields['message'] ?? '');
    if (!$name || !$sport || !is_email($email) || !$phone || !$message) {
        wp_send_json_error(['message' => 'Please complete all fields.'], 400);
    }
    $rate_key = 's1_inquiry_' . substr(hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown')), 0, 32);
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 10) {
        wp_send_json_error(['message' => 'Too many requests. Please try again later.'], 429);
    }

    $post_id = wp_insert_post([
        'post_type' => 's1_inquiry',
        'post_status' => 'private',
        'post_title' => $name . ' — ' . current_time('Y-m-d H:i'),
        'post_content' => $message,
    ], true);
    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Could not save the request.'], 500);
    }
    set_transient($rate_key, $attempts + 1, 10 * MINUTE_IN_SECONDS);

    update_post_meta($post_id, 's1_name', $name);
    update_post_meta($post_id, 's1_sport', $sport);
    update_post_meta($post_id, 's1_email', $email);
    update_post_meta($post_id, 's1_phone', $phone);
    update_post_meta($post_id, 's1_language', sanitize_key($_POST['language'] ?? 'sv'));

    $recipient = defined('S1_CONTACT_RECIPIENT') ? S1_CONTACT_RECIPIENT : 'max@arvinge.se';
    $body = "Name: $name\nSport: $sport\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $sent = wp_mail($recipient, 'S1 Sports — meeting request', $body, ['Reply-To: ' . $email]);
    update_post_meta($post_id, 's1_mail_sent', $sent ? '1' : '0');
    update_post_meta($post_id, 's1_mail_recipient', $recipient);
    wp_send_json_success(['saved' => true, 'mail_sent' => (bool) $sent]);
}
add_action('wp_ajax_s1_contact', 's1_handle_contact');
add_action('wp_ajax_nopriv_s1_contact', 's1_handle_contact');

add_filter('manage_s1_inquiry_posts_columns', function (array $columns): array {
    return [
        'cb' => $columns['cb'] ?? '',
        'title' => 'Namn',
        's1_email' => 'E-post',
        's1_sport' => 'Sport',
        's1_mail_sent' => 'Mejl',
        'date' => 'Datum',
    ];
});
add_action('manage_s1_inquiry_posts_custom_column', function (string $column, int $post_id): void {
    if ($column === 's1_mail_sent') {
        echo get_post_meta($post_id, 's1_mail_sent', true) === '1' ? 'Skickat' : 'Ej skickat';
    } elseif (in_array($column, ['s1_email', 's1_sport'], true)) {
        echo esc_html((string) get_post_meta($post_id, $column, true));
    }
}, 10, 2);
