<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Exception;

class Updater {
    protected static $current_version;
    protected static $dbname;
    protected static $collate;
    protected static $wpdb;

    /**
     * Run any necessary db updates, file upgrades etc.
     */
    public static function upgrade() {
        global $wpdb;

        self::$wpdb = $wpdb;
        self::$current_version = get_option( 'booter_version' );
        self::$dbname          = self::$wpdb->prefix . BOOTER_404_DB_TABLE;
        self::$collate = self::$wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        try {
            if ( ! self::$current_version || version_compare( self::$current_version, '1.0', '<' ) ) {
                self::update_1_0();
            }

            if ( version_compare( self::$current_version, '1.2', '<' ) ) {
                self::update_1_2();
            }

            if ( version_compare( self::$current_version, '1.3', '<' ) ) {
                self::update_1_3();
            }

            if ( version_compare( self::$current_version, '1.6.0', '<' ) ) {
                self::update_1_6_0();
            }

            // make sure we update the version in the database so we can run upgrades at later times
            update_option( 'booter_version', BOOTER_VERSION );
        } catch ( Exception $ex ) {
            error_log( $ex );
            // תוקן: עטיפת הודעת השגיאה ב-esc_html()
            wp_die( esc_html( $ex->getMessage() ) );
        }
    }

    /**
     * Update to the 1.0 version
     * create the 404 database
     * @throws Exception
     */
    protected static function update_1_0() {
        global $wpdb;

        $safe_dbname  = esc_sql( $wpdb->prefix . BOOTER_404_DB_TABLE );
        $safe_collate = esc_sql( $wpdb->get_charset_collate() );

        $query = sprintf(
            "CREATE TABLE %s (
        id bigint(20) unsigned NOT NULL auto_increment,
        uid varchar(191) NOT NULL DEFAULT '',
        url varchar(2048) NOT NULL DEFAULT '',
        hits int(11) unsigned NOT NULL DEFAULT 0,
        created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id),
        UNIQUE KEY uid (uid)
    ) %s;",
            $safe_dbname,
            $safe_collate
        );

        dbDelta( $query );

        if ( ! empty( $wpdb->last_error ) ) {
            throw new Exception( esc_html( $wpdb->last_error ) );
        }
    }

    protected static function update_1_6_0() {
        self::update_1_0();
    }

    /**
     * update to 1.2
     * rename mu plugin file
     */
    protected static function update_1_2() {
        $mu_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
        $mu_dir = untrailingslashit( $mu_dir );

        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if ( ! WP_Filesystem() ) {
            error_log( 'Booter: Failed to initialize WP_Filesystem during 1.2 update.' );
            return;
        }

        $old_file = $mu_dir . '/booter-crawlers-manager.php';
        $new_file = $mu_dir . '/booter-crawlers-manager-mu.php';

        if ( $wp_filesystem->exists( $old_file ) ) {
            $wp_filesystem->move( $old_file, $new_file, true );
        }
    }

    /**
     * Update to 1.3
     * Make sure the woocommerce blocks are disabled if woocommerce is not installed
     */
    protected static function update_1_3() {
        $settings = get_option( BOOTER_SETTINGS_KEY );

        if ( 'yes' === $settings['block']['enabled_woocommerce'] ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';

            $woo_active = is_plugin_active( 'woocommerce/woocommerce.php' );
            $settings['block']['enabled_woocommerce'] = $woo_active ? 'yes' : 'no';

            update_option( BOOTER_SETTINGS_KEY, $settings );
        }
    }

}
