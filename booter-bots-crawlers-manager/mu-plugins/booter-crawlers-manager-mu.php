<?php
/*
  Description: Booter - Bots & Crawlers Manager
*/

if ( ! defined( 'ABSPATH' ) ) {
    die( 'NO direct access!' );
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

$booter_plugin_candidates = [
    'booter-bots-crawlers-manager/booter-crawlers-manager.php',
    'booter/booter-crawlers-manager.php',
];

$booter_plugin_basename = '';

foreach ( $booter_plugin_candidates as $plugin_basename ) {
    if ( is_plugin_active( $plugin_basename ) && is_readable( WP_PLUGIN_DIR . '/' . $plugin_basename ) ) {
        $booter_plugin_basename = $plugin_basename;
        break;
    }
}

if ( '' === $booter_plugin_basename ) {
    wp_delete_file( __FILE__ );
    return;
}

$booter_plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $booter_plugin_basename );

require_once $booter_plugin_dir . '/booter-contstants.php';
require_once $booter_plugin_dir . '/includes/Logger.php';
require_once $booter_plugin_dir . '/includes/Utilities.php';
require_once $booter_plugin_dir . '/includes/RequestBlocker.php';
require_once $booter_plugin_dir . '/includes/RateLimiter.php';

\Upress\Booter\RequestBlocker::initialize();
\Upress\Booter\RateLimiter::initialize();
