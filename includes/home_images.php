<?php
// Helper for homepage images configuration and admin checks

function ensure_home_image_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function get_home_images_file() {
    return __DIR__ . '/home_images.json';
}

function default_home_images() {
    return [
        'hero_background' => 'assets/images/chess_background.webp',
        'hero_main' => 'assets/images/black_chess_coach.webp',
        'gallery1' => 'assets/images/gallery-1.jpeg',
        'gallery2' => 'assets/images/gallery-2.jpeg',
        'gallery3' => 'assets/images/gallery-3.jpeg',
        'gallery4' => 'assets/images/gallery-4.jpeg',
        'gallery5' => 'assets/images/gallery-5.jpeg',
        'gallery6' => 'assets/images/gallery-6.jpeg',
        'gallery7' => 'assets/images/gallery-7.jpeg',
    ];
}

function load_home_images() {
    $file = get_home_images_file();
    if (!file_exists($file)) {
        $defaults = default_home_images();
        file_put_contents($file, json_encode($defaults, JSON_PRETTY_PRINT));
        return $defaults;
    }
    $json = @file_get_contents($file);
    $data = @json_decode($json, true);
    if (!is_array($data)) {
        $data = default_home_images();
    }
    return array_merge(default_home_images(), $data);
}

function save_home_images($data) {
    $file = get_home_images_file();
    return (bool) file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_home_image($key) {
    $images = load_home_images();
    if (isset($images[$key]) && !empty($images[$key])) {
        return $images[$key];
    }
    return default_home_images()[$key] ?? '';
}

function is_admin_user() {
    ensure_home_image_session();

    // Basic checks: session role or username
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') return true;
    if (isset($_SESSION['username']) && in_array($_SESSION['username'], ['admin', 'administrator'])) return true;
    return false;
}

function get_gallery_images() {
    $data = load_home_images();
    if (isset($data['gallery']) && is_array($data['gallery'])) {
        return $data['gallery'];
    }
    // Return defaults
    return [
        ['image' => get_home_image('gallery1'), 'alt' => 'Chess Tournament Scene',   'caption' => 'Major Championships'],
        ['image' => get_home_image('gallery2'), 'alt' => 'Player Focus',            'caption' => 'Deep Concentration'],
        ['image' => get_home_image('gallery3'), 'alt' => 'Academy Session',         'caption' => 'Academy Training'],
        ['image' => get_home_image('gallery4'), 'alt' => 'Chess Pieces Close-up',   'caption' => 'The Royal Game'],
        ['image' => get_home_image('gallery5'), 'alt' => 'Winning Moments',         'caption' => 'Winning Moments'],
        ['image' => get_home_image('gallery6'), 'alt' => 'Community Gathering',     'caption' => 'Community Spirit'],
        ['image' => get_home_image('gallery7'), 'alt' => 'Future Grandmasters',     'caption' => 'Our Rising Stars'],
    ];
}

function save_gallery_images($gallery) {
    $images = load_home_images();
    $images['gallery'] = $gallery;
    return save_home_images($images);
}

?>