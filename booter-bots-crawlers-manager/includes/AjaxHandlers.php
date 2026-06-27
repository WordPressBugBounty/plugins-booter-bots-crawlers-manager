<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class AjaxHandlers {
    private static $instance;

    /**
     * @return AjaxHandlers
     */
    public static function initialize() {
        if ( ! self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_booter_disable_404_plugins', [ $this, 'disable_404_plugins' ] );
        add_action( 'wp_ajax_booter_download_disavow_list', [ $this, 'download_disavow_list' ] );
        add_action( 'wp_ajax_booter_get_bad_robots_list', [ $this, 'ajax_get_bad_robots_list' ] );
    }

    function disable_404_plugins() {
        check_ajax_referer( 'booter-notices' );

        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json_error( esc_html__( 'Sorry, you are not allowed to access this page.', 'booter-bots-crawlers-manager' ) );
        }

        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $slugs = isset( $_POST['slugs'] ) && is_array( $_POST['slugs'] ) ? wp_unslash( $_POST['slugs'] ) : [];
        $slugs = array_map( 'sanitize_text_field', $slugs );
        $slugs = array_filter( $slugs );

        $allowed_slugs = [
            'all-404-redirect-to-homepage/all-404-redirect-to-homepage.php',
            'redirect-404-error-page-to-homepage-or-custom-page/redirect-404-error-page-to-homepage-or-custom-page.php',
            '404-solution/404-solution.php',
            '404-to-301/404-to-301.php',
            'wp-404-auto-redirect-to-similar-post/wp-404-auto-redirect-similar-post.php',
            'redirect-404-error-page-to-homepage/redirect-404-error-page-to-homepage.php',
            'redirect-404-to-parent/moove-redirect.php',
        ];

        $active_slugs = array_map( 'plugin_basename', wp_get_active_and_valid_plugins() );
        $slugs        = array_values( array_intersect( $slugs, $allowed_slugs, $active_slugs ) );

        if ( count( $slugs ) <= 0 ) {
            wp_send_json_error( [ 'error' => 'nothing to disable' ] );
        }

        deactivate_plugins( $slugs );

        wp_send_json_success();
    }

    function download_disavow_list() {
        if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'download-disavow' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'booter-bots-crawlers-manager' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'booter-bots-crawlers-manager' ) );
        }

        set_transient( 'booter_disavow_list_downloaded_at', time() );

        $referers = Utilities::get_bad_referers();
        $referers = array_unique( $referers );
        $referers = array_filter( $referers );
        $referers = array_map( function( $r ) {
            return "domain:" . trim( $r );
        }, $referers );
        $referers = implode( "\r\n", $referers );

        header('Content-Encoding: UTF-8');
        header( 'Content-Type: application/octet-stream; charset=UTF-8' );
        header( 'Content-Transfer-Encoding: Binary' );
        header( 'Content-disposition: attachment; filename="booter-disavow-links-' . time() . '.txt"' );
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        // תוקן: שימוש ב-esc_textarea כדי לשמור על הפורמט של שורות חדשות, והחלפת ה-die ב-wp_die נוקשה
        echo esc_textarea( $referers );
        wp_die();
    }

    function ajax_get_bad_robots_list() {
        check_ajax_referer( 'booter-options' );

        if ( ! current_user_can( 'manage_options' ) ) {
            // תוקן ל-esc_html__()
            wp_send_json_error( esc_html__( 'Sorry, you are not allowed to access this page.', 'booter-bots-crawlers-manager' ) );
        }

        wp_send_json( Utilities::get_bad_robots() );
    }

}
