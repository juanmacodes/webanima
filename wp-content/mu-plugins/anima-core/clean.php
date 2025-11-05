<?php
/**
 * Performance cleanups and head optimisations.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'anima_core_disable_emojis' );
add_action( 'init', 'anima_core_disable_embeds' );
add_action( 'init', 'anima_core_disable_feeds' );

/**
 * Disable emoji scripts and styles.
 */
function anima_core_disable_emojis(): void {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

/**
 * Disable oEmbed discovery links and scripts.
 */
function anima_core_disable_embeds(): void {
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    remove_action( 'rest_api_init', 'wp_oembed_register_route' );
    add_filter( 'embed_oembed_discover', '__return_false' );
}

/**
 * Disable feeds and shortlinks when not needed.
 */
function anima_core_disable_feeds(): void {
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    remove_action( 'wp_head', 'feed_links', 2 );
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
    remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
    add_filter( 'feed_links_show_posts_feed', '__return_false' );
    add_filter( 'feed_links_show_comments_feed', '__return_false' );
}
